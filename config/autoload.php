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
ClassLoader::addNamespaces(
    [
        'HeimrichHannot',
    ]
);


/**
 * Register the classes
 */
ClassLoader::addClasses(
    [
        // Modules
        'HeimrichHannot\EntityImport\ModuleEntityImport'        => 'system/modules/entity_import/modules/ModuleEntityImport.php',

        // Classes
        'HeimrichHannot\EntityImport\Importer\External'         => 'system/modules/entity_import/classes/Importer/ExternalImporter.php',
        'HeimrichHannot\EntityImport\Importer\CsvImporter'      => 'system/modules/entity_import/classes/Importer/CsvImporter.php',
        'HeimrichHannot\Typort\TypoModel'                       => 'system/modules/entity_import/classes/TypoModel.php',
        'HeimrichHannot\EntityImport\Database'                  => 'system/modules/entity_import/classes/Database.php',
        'HeimrichHannot\EntityImport\Importer\NewsImporter'     => 'system/modules/entity_import/classes/Importer/NewsImporter.php',
        'HeimrichHannot\EntityImport\Importer\TypoNewsImporter' => 'system/modules/entity_import/classes/Importer/TypoNewsImporter.php',
        'HeimrichHannot\EntityImport\Importer\DatabaseImporter' => 'system/modules/entity_import/classes/Importer/DatabaseImporter.php',
        'HeimrichHannot\EntityImport\Importer\Importer'         => 'system/modules/entity_import/classes/Importer/Importer.php',
        'HeimrichHannot\EntityImport\Helper\ImporterHelper'     => 'system/modules/entity_import/classes/Helper/ImporterHelper.php',
        'HeimrichHannot\EntityImport\Helper\CronHelper'         => 'system/modules/entity_import/classes/Helper/CronHelper.php',
        'HeimrichHannot\EntityImport\EventListener\InserttagListener'    => 'system/modules/entity_import/classes/EventListener/InserttagListener.php',

        // Models
        'HeimrichHannot\EntityImport\EntityImportModel'         => 'system/modules/entity_import/models/EntityImportModel.php',
        'HeimrichHannot\EntityImport\TyportModel'               => 'system/modules/entity_import/models/TyportModel.php',
        'HeimrichHannot\EntityImport\EntityImportConfigModel'   => 'system/modules/entity_import/models/EntityImportConfigModel.php',
        'HeimrichHannot\EntityImport\TypoNewsModel'             => 'system/modules/entity_import/models/TypoNewsModel.php',
        'HeimrichHannot\EntityImport\TypoRefIndexModel'         => 'system/modules/entity_import/models/TypoRefIndexModel.php',
    ]
);


/**
 * Register the templates
 */
TemplateLoader::addFiles(
    [
        'dev_entity_import' => 'system/modules/entity_import/templates',
    ]
);
