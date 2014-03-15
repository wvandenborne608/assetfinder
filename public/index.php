<?php

  $arSettings["main"]["assetslocation"]="http://localhost/assetfinder_content";
  $arSettings["main"]["locales"]["nl-NL"]="nl-NL";
  $arSettings["main"]["locales"]["nl-BE"]="nl-BE";
  $arSettings["main"]["locales"]["en-US"]="en-US";
  $arSettings["main"]["types"]["txt"]="Text";
  $arSettings["main"]["types"]["img"]="Images";
  $arSettings["main"]["types"]["snd"]="Audio";
  $arSettings["db"]["account"]["username"]="root";
  $arSettings["db"]["account"]["password"]="root";
  $arSettings["db"]["account"]["hostname"]="localhost";
  $arSettings["db"]["account"]["database"]="assetfinder";
  $arSettings["db"]["dbHandle"]   = mysql_connect($arSettings["db"]["account"]["hostname"], $arSettings["db"]["account"]["username"], $arSettings["db"]["account"]["password"]) or die("cannot connect to server");
  $arSettings["db"]["dbSelected"] = mysql_select_db($arSettings["db"]["account"]["database"], $arSettings["db"]["dbHandle"]) or die("cannot select db");


  function getItems($keyword, $locale, $type, $locale_output, $type_output, $tags_output) {
    global $arSettings;

    $sub_query_locale_output = array_key_exists($locale_output, $arSettings["main"]["locales"]) ? $sub_query_locale_output = " AND i.locale='" . $locale_output . "'" : "";
    $sub_query_type_output   = array_key_exists($type_output, $arSettings["main"]["types"]) ? $sub_query_type_output = " AND i.type='" . $type_output . "'" : "";

    if (strlen($tags_output)>1) {
      $tags = "AND tag IN (";
      $arTags = explode(',', $tags_output);
      $count=0;
      $total=count($arTags);
      foreach (explode(',', $tags_output) as $key => $val) {
        $tags.= "'" . trim($val) . "'";
        if (($total>1) and ($count<($total-1))) $tags .= ",";
        $count++;
      }
      $tags .= ")";
    } else $tags="";

    $arTemp = array();
    $query = "
            SELECT value, locale, type, group_concat(tag separator ', ')
              FROM item as i
                LEFT JOIN itemtags as it
                  ON i.id=it.item_id
                    LEFT JOIN tag as t
                      ON t.id=it.tag_id
              WHERE i.id IN
              (
                 SELECT item2_id FROM itemlinks as il1
                   LEFT JOIN item as i1 on i1.id=il1.item1_id";
    if ($_POST['nonstrict']==1) {
      $query .= "    WHERE i1.value LIKE '%" . $keyword . "%' and i1.locale='" . $locale . "' and i1.type='" . $type . "'";
    } else {
      $query .= "    WHERE i1.value = '" . $keyword . "' and i1.locale='" . $locale . "' and i1.type='" . $type . "'";
    }
    $query .= "UNION 
                 SELECT item1_id FROM itemlinks as il2
                   LEFT JOIN item as i2 on i2.id=il2.item2_id";
    if ($_POST['nonstrict']==1) {
      $query .= "    WHERE i2.value LIKE '%" . $keyword . "%' and i2.locale='" . $locale . "' and i2.type='" . $type . "'";
    } else {
      $query .= "    WHERE i2.value = '" . $keyword . "' and i2.locale='" . $locale . "' and i2.type='" . $type . "'";
    }
    $query .= ")
              $sub_query_locale_output
              $sub_query_type_output
              $tags
              GROUP BY i.value
        ";
      $result = mysql_query($query);
      while ($row = mysql_fetch_array($result, MYSQL_NUM)) {
        $arTemp[] = $row;
      }
      return $arTemp;
   }



  //add logo to HTML output
  $output ='
    <div class="row">
      <div class="col-lg-md" id="assetfinder_logo">
        <img src="img/assetfinder_logo.png">
      </div>
    </div>';

  function createListbox($select_id, $arElements,$postVar,$defaultoption) {
    $output = '<select name="'.$select_id.'" id="listbox_'.$select_id.'" class="form-control">';
    $output .= ($defaultoption!="none") ? '<option value="' . $defaultoption . '">' . $defaultoption . '</option>' : "";
    foreach ($arElements as $key => $value) {
      $selected = ($postVar == $key ) ? 'selected="selected"' : '';
      $output .= '<option value="' . $key . '" '.$selected.'>' . $value . '</option>';
    }
    $output .= '</select>';
    return $output;
  }

  //add search form to HTML output
  $keywords = ((isset($_POST['keywords'])) ? $_POST['keywords'] : "");
  if (!isset($_POST['locale'])) $_POST['locale']="";
  if (!isset($_POST['locale_output'])) $_POST['locale_output']="";
  if (!isset($_POST['type'])) $_POST['type']="";
  if (!isset($_POST['type_output'])) $_POST['type_output']="";
  if (!isset($_POST['tags_output'])) $_POST['tags_output']="";
  if (!isset($_POST['nonstrict'])) $_POST['nonstrict']=0;
  $output .='
    <div class="row">
      <div class="col-md-12" id="searchformrow">
        <form method="post" action="index.php">
          <div class="panel panel-default" id="searchform">
            <p><b>Input</b> &nbsp; ("Like" mode = <input type="checkbox" name="nonstrict" value="1" ' . ($_POST['nonstrict']==1 ? 'checked="checked"':'') . '>)</p>
            <textarea placeholder="keyword(s)" id="searchform_keywords" name="keywords" class="form-control" rows="2">' . $keywords . '</textarea>
            <div id="localeandtypesselection">' . createListbox("locale", $arSettings["main"]["locales"],$_POST['locale'],"none") . '' . createListbox("type", $arSettings["main"]["types"],$_POST['type'],"none") . '</div>
            <input type="submit" name="submit" value="Find!" id="searchform_submit" class="btn btn-default"/>
          </div>
          <div class="panel panel-default" id="searchformmustinclude">
            <p><b>Output</b></p>
            <div id="localeandtypesselection">' . createListbox("locale_output", $arSettings["main"]["locales"],$_POST['locale_output'],"(all locales)") . '' . createListbox("type_output", $arSettings["main"]["types"],$_POST['type_output'],"(all types)") . '</div>
            <input placeholder="tags" type="text" name="tags_output" value="'.$_POST['tags_output'].'" id="forminput_tags_output" class="form-control"/>
          </div>
        </form>
      </div>
    </div>
  ';


  function displayTags($tags) {
    $output="";
    if (strlen($tags)>0) {
      foreach (explode(',', $tags) as $key => $val) {
        $output.='<span class="label label-default tag">' . trim($val) . '</span> ';
      }
    }
    return $output;
  }

  function previewlink($locale, $type, $value) {
    $output="";
    if ($type=="img") $output='<a href="' . $value . '" data-itemlocale="'.$locale.'" data-itemtype="'.$type.'" data-toggle="modal" data-target=".bs-preview-modal" class="previewModalOpener"><span class="glyphicon glyphicon-picture"></span></a>';
    if ($type=="snd") $output='<a href="' . $value . '" data-itemlocale="'.$locale.'" data-itemtype="'.$type.'" data-toggle="modal" data-target=".bs-preview-modal" class="previewModalOpener"><span class="glyphicon glyphicon-headphones"></span></a>';
    return $output;
  }

  if (isset($_POST['keywords']) && (strlen($_POST['keywords'])>0) ) {
    $arKeywords = explode("\r", $_POST['keywords']);
    if (is_array($arKeywords)) {
      $output.='<hr>';
      foreach ($arKeywords as $key => $val) {
        $val             = trim($val);
        $arItems         = getItems($val, $_POST['locale'], $_POST['type'], $_POST['locale_output'], $_POST['type_output'], $_POST['tags_output']);
        $output.='
          <div class="row">
            <div class="col-xs-6 col-md-4">
              <div id="results_for">
                <h2>Results for:</h2>
                <blockquote> ' . $val . ' <span class="badge">'.count($arItems).'</span></blockquote>
              </div>
            </div>
            <div class="col-xs-12 col-md-8">
            </div>
          </div>      
          <div class="row">
            <div class="col-md-12">
              <div class="result_tablediv">
                <table class="table table-bordered">
                  <thead>
                    <tr>
                      <th class="result_tablediv_th_nr">#</th>
                      <th class="result_tablediv_th_type">Type</th>
                      <th class="result_tablediv_th_locale">Locale</th>
                      <th class="result_tablediv_th_preview">&nbsp;</th>
                      <th>Item</th>
                      <th class="result_tablediv_th_tags">Tag(s)</th>
                    </tr>
                  </thead>
                  <tbody>';

        if (is_array($arItems)) {
          $count=0;
          foreach ($arItems as $keyItem => $valItem) {
            $count++;
            $output.='
                    <tr>
                      <td>' . $count . '</td>
                      <td>' . $valItem[2] . '</td>
                      <td>' . $valItem[1] . '</td>
                      <td>' . previewlink($valItem[1], $valItem[2], $valItem[0]). '</td>
                      <td>' . $valItem[0] . '</td>
                      <td>' . displayTags($valItem[3]) . '</td>
                   </tr>';
          } //end foreach
        } //end if


        $output.='
                  </tbody>
                </table>
              </div>
            </div>
          </div>';

      }
    } //end if is_array
  }	//end if isset


  mysql_close($arSettings["db"]["dbHandle"]); //close database (see "config.php for initiation)
  include ("index_html.php");
?>
