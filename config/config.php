<?php

/**
 * Config
 */
// TODO Excel
$GLOBALS['TL_CONFIG']['validEntityImportTypes'] = 'csv,txt';

/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['system']['entity_import'] = [
    'tables' => ['tl_entity_import', 'tl_entity_import_config'],
    'import' => ['HeimrichHannot\EntityImport\ModuleEntityImport', 'generate'],
    'icon'   => 'system/modules/entity_import/assets/icon.png',
];

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_entity_import']        = 'HeimrichHannot\EntityImport\EntityImportModel';
$GLOBALS['TL_MODELS']['tl_entity_import_config'] = 'HeimrichHannot\EntityImport\EntityImportConfigModel';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['initEntityImportPalettes'] = [
    'initNewsPalette' => ['tl_entity_import_config', 'initNewsPalette'],
];

/**
 * Constants
 */
define('ENTITY_IMPORT_CONFIG_TYPE_DATABASE', 'db');
define('ENTITY_IMPORT_CONFIG_TYPE_FILE', 'file');
define('ENTITY_IMPORT_FILE_COL_SUFFIX', 'EColE_');
define('ENTITY_IMPORT_NEWS_WRITERS_MEMBER_GROUP_NAME', 'News writers');

/**
 * CSS
 */
if (TL_MODE == 'BE') {
    $GLOBALS['TL_CSS']['entity_import'] = 'system/modules/entity_import/assets/css/entity_import.css';
}

/**
 * Importers
 */
$GLOBALS['ENTITY_IMPORTER'] = array_merge(
    (is_array($GLOBALS['ENTITY_IMPORTER']) ? $GLOBALS['ENTITY_IMPORTER'] : []),
    [
        'HeimrichHannot\EntityImport\Importer\CsvImporter'      => 'CsvImporter',
        'HeimrichHannot\EntityImport\Importer\DatabaseImporter' => 'DatabaseImporter',
        'HeimrichHannot\EntityImport\Importer\Importer'         => 'DefaultImporter',
        'HeimrichHannot\EntityImport\Importer\NewsImporter'     => 'NewsImporter',
        'HeimrichHannot\EntityImport\Importer\TypoNewsImporter' => 'Typo3NewsImporter',
    ]
);
