# Entity Import

A backend only module, to migrate items from one database into another (experts only).

## Features

- image support
- enclosure support
- tidy text replace (format to fit contao tinymce setting)
- foreignKey reference for values
- import from api

## Configuration

- Importing is done either through one of the supplied classes inheriting from Importer or through a custom class
  also inheriting from Importer
- The palettes of an entity import configuration can be modified by the Hook "initEntityImportPalettes"; one is already set
  and could be easily removed if necessary
  
## Usage

### Fieldmapping

#### Types
|Type      |Description|
|----------|-----------|
|source    |Copy of the source value|
|foreignKey|foreignKey reference for values (Set for example in value field : `id=tl_videobox.youtube_id` (id = foreign primary key, tl_videobox = foreign key table, youtube_id = column value that should return)           |
|value     |Result of entry in value row|
|sql       |           |


## Developers 

### Field explanation "External Source"

Field | Explanation
---- | ----------
fieldMapping | Map the fields from the source entity to the ones in the target entity. If your source has multilevel parameters enter the path to that parameter in the source field. The different levels are seperated by "->" (e.g. level_1->level_2->level_3). Otherwise you can modify the source data in the modifySourceItem-Hook. If you enter something in the value field the import will ignore anything you set in the source field. It will set the given value in the target field of the target entity.
externalImportExceptions | Configure exceptional rules for the import. E.g. source field value equals x, than set target field to target value. Each rule stands for it's own. They are not concatenated.
externalImportExclusions | Configure rules for which a source data will not be imported.
 

### Hooks

Name | Arguments | Expected return value | Description
---- | --------- | --------------------- | -----------
entityImportRunAfterSaving | $objItem, $objSourceItem, $this | $objItem | Triggered after saving entity
initEntityImportPalettes | $objEntityImportConfig, $arrDca | - | Modify the palettes of an entity import configuration
modifySourceItem | $sourceItem | $sourceItem | Modify the source data.
modifyItemBeforeSave | $item, $sourceItem, $this->objModel | - | Modify item attributes before imported item is saved

### Inserttags

Name | Usage
---- | -----
file_uuid | Converts uuid string to binary uuid string. This can be used when you want to set a default value for the singleSRC field of an entity.
file_bin | Converts binary uuid to uuid string.    



