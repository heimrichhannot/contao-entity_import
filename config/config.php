<?php

/**
 * Config
 */
// TODO Excel
$GLOBALS['TL_CONFIG']['validEntityImportTypes'] = 'csv,txt';

/**
 * Back end modules
 */
$GLOBALS['BE_MOD']['devtools']['entity_import'] = array
(
	'tables' => array('tl_entity_import', 'tl_entity_import_config'),
	'import' => array('HeimrichHannot\EntityImport\ModuleEntityImport', 'generate'),
	'icon'   => 'system/modules/entity_import/assets/icon.png'
);

/**
 * Models
 */
$GLOBALS['TL_MODELS']['tl_entity_import'] = 'HeimrichHannot\EntityImport\EntityImportModel';
$GLOBALS['TL_MODELS']['tl_entity_import_config'] = 'HeimrichHannot\EntityImport\EntityImportConfigModel';

/**
 * Hooks
 */
$GLOBALS['TL_HOOKS']['initEntityImportPalettes'] = array(
	'initNewsPalette' => array('tl_entity_import_config', 'initNewsPalette')
);

/**
 * Constants
 */
define('ENTITY_IMPORT_CONFIG_TYPE_DATABASE', 'db');
define('ENTITY_IMPORT_CONFIG_TYPE_FILE', 'file');