AssetFinder
=============

1. AssetFinder website
2. Import ("Rawdata") script 

Version
=======
v0.1

Usage: Import ("Rawdata") script
=========
Create a rawdata file or a pair of rawdata files, for example:
1_txt-nl-NL
1_img_nl-NL(linked)
2_txt_nl-NL

The definition of name convention of these files is:
* A (manually incremented) number.
* The type (eg: txt, img, snd)
* The locale (eg: Currently nl-NL, en-US, nl-BE)
* Optionally the suffix "(linked)" to link a set of items.

The interior of a rawdata file:
* First line is for defining the tags: "tags: tag1,tag2,etc..."
* Use the subsequent lines to define the the items.

For example file "1_txt-nl_NL":
[line 1:] tags:cars
[line 2:] Ford
[line 3:] Toyota
[line 4:] Peugot

For example file "1_img-nl_NL(linked)":
[line 1:] tags:cars,jpg
[line 2:] /jpg/cars/ford.png
[line 3:] /jpg/cars/toyota.png
[line 4:] /jpg/cars/peugot.png

To inject files into the database, place them in the folder "/rawdata/1_to_process/"
Then run the script "/script/process_rawdata.rb"
To archive the rawdata files, (manually) move them to the folder "/rawdata/2_processed/"





