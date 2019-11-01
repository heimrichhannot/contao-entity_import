# Changelog
All notable changes to this project will be documented in this file.

## [1.4.0] - 2018-11-01

### Added
- import from external url source
- inserttag {{file_uuid::*}} and {{file_bin::*}}

## [1.3.2] - 2018-06-20

### Fixed
- decode entities in db password
- tl_content issues
- call to runAfterComplete() in DatabaseImporter

## [1.3.1] - 2018-06-20

### Fixed
- remove css in front end mode

## [1.3.0] - 2018-06-18

### Added
- support for merging (updating) existing entities based on equality of certain fields (id, email, ...)

## [1.2.23] - 2017-11-01

### Fixed
- file importer issues

## [1.2.22] - 2017-10-12

### Changed
- Moved functions from TypoNewsImporter into parent Importer class

## [1.2.21] - 2017-10-12

### Fixed
- `CsvImporter`, missing dependency

## [1.2.20] - 2017-09-28

### Fixed
- `TypoNewsImporter` strip tags for image alt, link, title and description

## [1.2.19] - 2017-09-07

### Fixed
- `Importer::copyFile` did overwrite files uuid if file was copied before within other entity

## [1.2.18] - 2017-09-07

### Fixed
- `Newsimporter` cleanHtml replaces now `<div>` with `<p>`

## [1.2.17] - 2017-09-05

### Fixed
- TypoNewsImporter external link conversion

## [1.2.16] - 2017-08-29

### Fixed
- clean html properly within NewsImporter

## [1.2.15] - 2017-08-29

### Fixed
- NewsImporter, removed non existing class

## [1.2.14] - 2017-08-28

### Added
- TypoNewsImporter, convert external <link> tags

## [1.2.13] - 2017-08-28

### Fixed
- updated to multi_column_editor 1.2.0, removed too globally defined styles

## [1.2.12] - 2017-08-28

### Changed
- TypoNewsImporter, import article image title also as caption

## [1.2.11] - 2017-08-28

### Changed
- wrap news teaser inside paragraphs

## [1.2.10] - 2017-08-25

### Fixed
- added if statement

## [1.2.9] - 2017-08-25

### Added
- translations

## [1.2.8] - 2017-08-25

### Added
- deletes entries in given table by reference column

## [1.2.7] - 2017-08-23

### Changed
- try to set `max_execution_time` to 0

## [1.2.6] - 2017-08-23

### Fixed
- `starttime`, `endtime` conversion from Typo3 to Contao `start`, `stop`

## [1.2.5] - 2017-08-22

### Added
- `starttime`, `endtime` conversion from Typo3 to Contao `start`, `stop`

## [1.2.4] - 2017-08-16

### Fixed
- `sql` type handling
- `Importer::copyFile` now returns the target model

## [1.2.3] - 2017-07-27

### Fixed
- remove old file models with same name, before copy new files

## [1.2.2] - 2017-07-27

### Added
- Contao 4 support & refactoring

## [1.2.1] - 2017-07-20

### Added
- PHP7 compatibility
