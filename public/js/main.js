var assetslocation = "assets/";


var textarea = document.getElementById("searchform_keywords");
var limit = 6;
textarea.oninput = function() {
  textarea.style.height = "";
  textarea.style.height = Math.min(textarea.scrollHeight, 100) + "px";
};


$(document).ready(function() {

  //Add tag to input field form at on click
  $(".tag").click(function() {
    var cur_val = $('#forminput_tags_output').val();
    if(cur_val)
      $('#forminput_tags_output').val(cur_val + "," + $(this).html()); 
    else
      $('#forminput_tags_output').val($(this).html());
  });

  //Open preview window at on click preview button
  $(".previewModalOpener").click(function() {
  	$('#previewModalTitle').html("Preview: " + assetslocation + $(this).attr('href'));
	switch($(this).attr('data-itemtype'))
	{
	case "img":
      var re = /(?:\.([^.]+))?$/;
      var ext = re.exec($(this).attr('href'))[1];
  	  switch(ext)
      {
	    case "swf":
          $('#previewModalBody').html('<div id="previewModalBodySwf"><object width="480" height="320"><param name="movie" value="' + assetslocation + $(this).attr('href') + '"><embed src="' + assetslocation + $(this).attr('href') + '" width="480" height="320"></embed></object></div>');
	    break;
	    default:
          $('#previewModalBody').html('<div id="previewModalBodyImg"><img src="' + assetslocation + $(this).attr('href') + '"></div>');
	  }
	  break;
	case "snd":
  	  $('#previewModalBody').html('<div id="previewModalBodySnd"><audio src="' + assetslocation + $(this).attr('href') + '" preload="auto" controls><p>Your browser does not support the audio element </p></audio></div>');
	  break;
	default:
  	  $('#previewModalBody').html("<p>Preview not supported</p>");
	}
    $('#previewModalFooter').html('[<a href="' + assetslocation + $(this).attr('href') + '">Download link</a>]');
  	$('.bs-preview-modal').modal('show');
  });

});