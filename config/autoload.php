<?php

/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2005-2016 Leo Feyer
 *
 * @license LGPL-3.0+
 */


/**
 * Register the namespaces
 */
ClassLoader::addNamespaces(array
(
	'HeimrichHannot',
));


/**
 * Register the classes
 */
ClassLoader::addClasses(array
(
	// Modules
	'HeimrichHannot\EntityImport\ModuleEntityImport'      => 'system/modules/entity_import/modules/ModuleEntityImport.php',

	// Classes
	'HeimrichHannot\EntityImport\CsvImporter'             => 'system/modules/entity_import/classes/CsvImporter.php',
	'HeimrichHannot\Typort\TypoModel'                     => 'system/modules/entity_import/classes/TypoModel.php',
	'HeimrichHannot\EntityImport\Database'                => 'system/modules/entity_import/classes/Database.php',
	'HeimrichHannot\EntityImport\NewsImporter'            => 'system/modules/entity_import/classes/NewsImporter.php',
	'HeimrichHannot\EntityImport\DatabaseImporter'        => 'system/modules/entity_import/classes/DatabaseImporter.php',
	'HeimrichHannot\EntityImport\Importer'                => 'system/modules/entity_import/classes/Importer.php',

	// Models
	'HeimrichHannot\EntityImport\EntityImportModel'       => 'system/modules/entity_import/models/EntityImportModel.php',
	'HeimrichHannot\EntityImport\TyportModel'             => 'system/modules/entity_import/models/TyportModel.php',
	'HeimrichHannot\EntityImport\EntityImportConfigModel' => 'system/modules/entity_import/models/EntityImportConfigModel.php',
	'HeimrichHannot\EntityImport\TypoNewsModel'           => 'system/modules/entity_import/models/TypoNewsModel.php',
	'HeimrichHannot\EntityImport\TypoRefIndexModel'       => 'system/modules/entity_import/models/TypoRefIndexModel.php',
));


/**
 * Register the templates
 */
TemplateLoader::addFiles(array
(
	'dev_entity_import' => 'system/modules/entity_import/templates',
));
