# Entity Import

A backend only module, to migrate items from one database into another (experts only).

## Features

- image support
- enclosure support
- tidy text replace (format to fit contao tinymce setting)
- foreignKey reference for values (Set for example in value field : "id=tl_videobox.youtube_id" (id = foreign primary key, tl_videobox = foreign key table, youtube_id = column value that should return)

## Configuration

- Importing is done either through one of the supplied classes inheriting from Importer or through a custom class
  also inheriting from Importer
- The palettes of an entity import configuration can be modified by the Hook "initEntityImportPalettes"; one is already set
  and could be easily removed if necessary
  
