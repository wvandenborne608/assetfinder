#!/usr/bin/ruby

module AssetFinder
  class RawData
    @@dbConnection  = 0
    @@fileID        = 0
    @@type          = ""
    @@locale        = ""
    @@tags          = []
    @@items         = []
    @@linked_type   = ""
    @@linked_locale = ""
    @@linked_tags   = []
    @@linked_items  = []

    def initialize(filename, filename_linked,dbConnection)
      @@dbConnection = dbConnection
      @@tags          = []
      @@items         = []
      @@linked_tags   = []
      @@linked_items  = []      
      tmp      = filename.split(/_\$*?(\w+)-\$*?(.{2})_\$*?(.{2})\$*?(.*)/)
      @@fileID = tmp[0]
      @@type   = tmp[1]
      @@locale = tmp[2] + "-" + tmp[3];
      File.open(AssetFinder::Config::MAIN[:rawdataLocation] + "/" + filename, "r").each_with_index do |val, index|
          if index==0 
            @@tags = val.split(":")[1].split(",")
          else
            @@items << val
          end  
      end
      if filename_linked!=nil
        tmp = filename_linked.split(/_\$*?(\w+)-\$*?(.{2})_\$*?(.{2})\$*?(.*)/)
        @@linked_type = tmp[1];
        @@linked_locale = tmp[2] + "-" + tmp[3];
        File.open(AssetFinder::Config::MAIN[:rawdataLocation] + "/" + filename_linked, "r").each_with_index do |val, index|
          if index==0 
            @@linked_tags = val.split(":")[1].split(",")
          else
            @@linked_items << val
          end  
        end
      else
        @@linked_type = nil; # apparently I have to do this, other wise this value still has data in it from a previous initiation
        @@linked_locale = nil; #Same here...
      end
    end  
  

    def addOrUpdateItem (val,locale, type)
      val = quote(val)
      result = @@dbConnection.query("SELECT id FROM item WHERE value = '" + val + "' AND type = '" + type + "' AND locale = '" + locale + "'  ")
      record = result.fetch_hash
      if record==nil 
        result = @@dbConnection.query("INSERT INTO item (locale,type,value,count) VALUES ('" + locale + "', '" + type + "', '" + val + "', 0)")
        result = @@dbConnection.query("SELECT id FROM item WHERE value = '" + val + "' AND type = '" + type + "' AND locale = '" + locale + "'  ")
        record = result.fetch_hash
        puts "-- add item: " + val
      else
        result = @@dbConnection.query("UPDATE item SET count=count+1 where id='" + record["id"] + "'")
        puts "-- update item: " + val
      end
      return record["id"]
    end

    def addOrUpdateTag (tag)
      tag = quote(tag)
      result = @@dbConnection.query("SELECT id FROM tag WHERE tag = '" + tag + "'")
      record = result.fetch_hash
      if record==nil 
        result = @@dbConnection.query("INSERT INTO tag (tag, count) VALUES ('" + tag + "', 0)")
        result = @@dbConnection.query("SELECT id FROM tag WHERE tag = '" + tag + "'")
        record = result.fetch_hash
        puts "---- add tag: " + tag
      else
        result = @@dbConnection.query("UPDATE tag SET count=count+1 where id='" + record["id"] + "'")
        puts "---- update tag: " + tag
      end      
      return record["id"]
    end

    def addItemTagRelation (item_id, tag_id)
      result = @@dbConnection.query("SELECT id FROM itemtags WHERE item_id = '" + item_id + "' AND tag_id = '" + tag_id + "'")
      record = result.fetch_hash
      if record==nil 
        result = @@dbConnection.query("INSERT INTO itemtags (item_id, tag_id) VALUES ('" + item_id + "', '" + tag_id + "')")
      end 
    end

    def addItemItemRelation (item1_id, item2_id)
      result = @@dbConnection.query("SELECT id FROM itemlinks WHERE item1_id = '" + item1_id + "' AND item2_id = '" + item2_id + "'")
      record = result.fetch_hash
      if record==nil 
        result = @@dbConnection.query("INSERT INTO itemlinks (item1_id, item2_id) VALUES ('" + item1_id + "', '" + item2_id + "')")
        puts "---- add itemlink: " + item1_id + "=" + item2_id
      end 
    end


    def addToDataBase()
      line = [
        "**Process file:",
        @@fileID + "_" + @@type + "-" + @@locale,
        @@linked_type.nil? ? "" : "+ Linked file: " + @@fileID + "_" + @@linked_type + "-" + @@linked_locale,
        "**",
      ].compact.join(" ")
      puts line
      @@items.each_with_index do |val_item, index_item|
        itemID = addOrUpdateItem(val_item.strip,@@locale, @@type)
        @@tags.each do |val_tag|
          tag_id = addOrUpdateTag(val_tag.strip)
          addItemTagRelation(itemID, tag_id)
        end
        if @@linked_items[index_item]!=nil
          itemID_linked = addOrUpdateItem(@@linked_items[index_item].strip, @@linked_locale, @@linked_type)
          addItemItemRelation(itemID, itemID_linked)
          @@linked_tags.each do |val_linkedtag|
            linkedtag_id = addOrUpdateTag(val_linkedtag.strip)
            addItemTagRelation(itemID_linked, linkedtag_id)
          end
        end
      end
    end
  
  end

end