<?php

/**
 * Table tl_extension
 */

use HeimrichHannot\EntityImport\Importer\ExternalImporter;

$GLOBALS['TL_DCA']['tl_entity_import_config'] = [

    // Config
    'config'       => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'ptable'           => 'tl_entity_import',
        'sql'              => [
            'keys' => [
                'id'  => 'primary',
                'pid' => 'index',
            ],
        ],
        'onload_callback'  => [['tl_entity_import_config', 'initPalette']],
    ],
    // List
    'list'         => [
        'sorting'           => [
            'mode'                  => 4,
            'fields'                => ['title DESC'],
            'headerFields'          => ['title'],
            'panelLayout'           => 'filter;sort,search,limit',
            'child_record_callback' => ['tl_entity_import_config', 'listEntityImportConfig'],
            'child_record_class'    => 'no_padding',
            'disableGrouping'       => true,
        ],
        'label'             => [
            'fields'         => ['title', 'type'],
            'format'         => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
            'label_callback' => ['tl_entity_import_config', 'addDate'],
        ],
        'global_operations' => [
            'all' => [
                'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
                'href'       => 'act=select',
                'class'      => 'header_edit_all',
                'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"',
            ],
        ],
        'operations'        => [
            'edit'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['edit'],
                'href'  => 'act=edit',
                'icon'  => 'edit.gif',
            ],
            'copy'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete' => [
                'label'      => &$GLOBALS['TL_LANG']['tl_entity_import_config']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
            'show'   => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ],
            'import' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['import'],
                'href'  => 'key=import',
                'icon'  => version_compare(VERSION, '4.0', '<') ? 'system/modules/devtools/assets/apply.gif' : 'ok.svg',
            ],
        ],
    ],
    // Palettes
    'palettes'     => [
        '__selector__' => ['type', 'useTimeInterval', 'purgeBeforeImport', 'purgeAdditionalTables', 'addMerge', 'useCron'],
        'default'      => '{title_legend},title,description;',
    ],
    // Subpalettes
    'subpalettes'  => [
        'useTimeInterval'       => 'start,end',
        'purgeBeforeImport'     => 'whereClausePurge, purgeAdditionalTables',
        'purgeAdditionalTables' => 'additionalTablesToPurge',
        'addMerge'              => 'mergeIdentifierFields',
        'useCron'               => 'cronInterval'
    ],
    // type palettes
    'typepalettes' => [
        ENTITY_IMPORT_CONFIG_TYPE_DATABASE => '{config_legend},dbSourceTable,dbTargetTable,importerClass,purgeBeforeImport,addMerge,dbFieldMapping,useTimeInterval,whereClause,sourceDir,targetDir,dbFieldFileMapping;',
        ENTITY_IMPORT_CONFIG_TYPE_FILE     => '{config_legend},sourceFile,delimiter,enclosure,arrayDelimiter,dbTargetTable,importerClass,purgeBeforeImport,addMerge,fileFieldMapping,sourceDir,targetDir;',
        ENTITY_IMPORT_CONFIG_TYPE_EXTERNAL => '{config_legend},importerClass,purgeBeforeImport,addMerge,dbTargetTable,useCron,publishAfterImport;{external_legend_mapping},externalFieldMapping;{external_legend_exception},externalImportExceptions;{external_legend_exclusion},externalImportExclusions;',
    ],
    // Fields
    'fields'       => [
        'id'                      => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'pid'                     => [
            'foreignKey' => 'tl_entity_import.title',
            'sql'        => "int(10) unsigned NOT NULL default '0'",
            'relation'   => ['type' => 'belongsTo', 'load' => 'eager'],
        ],
        'tstamp'                  => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'                   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['title'],
            'search'    => true,
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'description'             => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['description'],
            'search'    => true,
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['maxlength' => 255, 'tl_class' => 'long clr'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'dbSourceTable'           => [
            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbSourceTable'],
            'search'           => true,
            'exclude'          => true,
            'inputType'        => 'select',
            'eval'             => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50', 'chosen' => true],
            'options_callback' => ['tl_entity_import_config', 'getSourceTables'],
            'sql'              => "varchar(255) NOT NULL default ''",
        ],
        'dbTargetTable'           => [
            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbTargetTable'],
            'search'           => true,
            'exclude'          => true,
            'inputType'        => 'select',
            'eval'             => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50', 'chosen' => true],
            'options_callback' => ['tl_entity_import_config', 'getTargetTables'],
            'sql'              => "varchar(255) NOT NULL default ''",
        ],
        'importerClass'           => [
            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['importerClass'],
            'inputType'        => 'select',
            'eval'             => ['mandatory' => true, 'tl_class' => 'w50 clr', 'decodeEntities' => true, 'submitOnChange' => true, 'chosen' => true],
            'options_callback' => ['tl_entity_import_config', 'getImporterClasses'],
            'sql'              => "varchar(255) NOT NULL default ''",
        ],
        'purgeBeforeImport'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['purgeBeforeImport'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'clr w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'whereClausePurge'        => [
            'label'       => &$GLOBALS['TL_LANG']['tl_entity_import_config']['whereClausePurge'],
            'inputType'   => 'textarea',
            'exclude'     => true,
            'eval'        => ['class' => 'monospace', 'rte' => 'ace', 'tl_class' => 'clr long'],
            'explanation' => 'insertTags',
            'sql'         => "text NULL",
        ],
        'purgeAdditionalTables'   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['purgeAdditionalTables'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'additionalTablesToPurge' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['additionalTablesToPurge'],
            'exclude'   => true,
            'inputType' => 'multiColumnEditor',
            'eval'      => [
                'multiColumnEditor' => [
                    'class'               => 'additionalTablesToPurge',
                    // set to 0 if it should also be possible to have *no* row (default: 1)
                    'minRowCount'         => 1,
                    // set to 0 if an infinite number of rows should be possible (default: 0)
                    'maxRowCount'         => 5,
                    // defaults to false
                    'skipCopyValuesOnAdd' => false,
                    'fields'              => [
                        'tableToPurge'    => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['tableToPurge'],
                            'search'           => true,
                            'exclude'          => true,
                            'inputType'        => 'select',
                            'eval'             => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50', 'groupStyle' => 'width: 180px', 'chosen' => true],
                            'options_callback' => ['tl_entity_import_config', 'getTargetTables'],
                        ],
                        'referenceColumn' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['referenceColumn'],
                            'search'    => true,
                            'exclude'   => true,
                            'inputType' => 'text',
                            'eval'      => ['mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50', 'groupStyle' => 'width: 180px', 'chosen' => true],
                        ],
                    ],
                ],
            ],
            'sql'       => 'blob NULL',
        ],
        'dbFieldMapping'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping'],
            'inputType' => 'multiColumnEditor',
            'exclude'   => true,
            'eval'      => [
                'tl_class'          => 'clr',
                'multiColumnEditor' => [
                    'fields' => [

                        'type'      => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping']['type'],
                            'inputType' => 'select',
                            'options'   => ['source', 'foreignKey', 'value', 'sql'],
                            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping']['type'],
                            'eval'      => [
                                'groupStyle' => 'width:200px'
                            ],
                        ],
                        'source'    => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping']['source'],
                            'inputType'        => 'select',
                            'options_callback' => ['tl_entity_import_config', 'getSourceFields'],
                            'eval'             => [
                                'groupStyle'              => 'width:300px',
                                'includeBlankOption' => true, 'chosen' => true
                            ],
                        ],
                        'value'     => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping']['value'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle'          => 'width:200px',
                                'decodeEntities' => true,
                            ],
                        ],
                        'target'    => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping']['target'],
                            'inputType'        => 'select',
                            'options_callback' => ['tl_entity_import_config', 'getTargetFields'],
                            'eval'             => [
                                'groupStyle' => 'width:300px', 'chosen' => true
                            ],
                        ],
                        'transform' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping']['transform'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle'          => 'width:200px',
                                'decodeEntities' => true,
                            ],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'addMerge' => [
            'label'                   => &$GLOBALS['TL_LANG']['tl_entity_import_config']['addMerge'],
            'exclude'                 => true,
            'inputType'               => 'checkbox',
            'eval'                    => ['tl_class' => 'clr w50', 'submitOnChange' => true],
            'sql'                     => "char(1) NOT NULL default ''"
        ],
        'mergeIdentifierFields'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['mergeIdentifierFields'],
            'inputType' => 'multiColumnEditor',
            'exclude'   => true,
            'eval'      => [
                'tl_class'          => 'clr',
                'multiColumnEditor' => [
                    'fields' => [
                        'source'    => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping']['source'],
                            'inputType'        => 'select',
                            'options_callback' => ['tl_entity_import_config', 'getSourceFields'],
                            'eval'             => [
                                'groupStyle'              => 'width:300px',
                                'includeBlankOption' => true, 'chosen' => true, 'mandatory' => true
                            ],
                        ],
                        'target'    => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping']['target'],
                            'inputType'        => 'select',
                            'options_callback' => ['tl_entity_import_config', 'getMergeTargetFields'],
                            'eval'             => [
                                'groupStyle' => 'width:300px', 'chosen' => true, 'mandatory' => true, 'includeBlankOption' => true
                            ],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'useTimeInterval'         => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['useTimeInterval'],
            'exclude'   => true,
            'inputType' => 'checkbox',
            'eval'      => ['submitOnChange' => true, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'start'                   => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['start'],
            'inputType' => 'text',
            'exclude'   => true,
            'eval'      => ['rgxp' => 'datim', 'tl_class' => 'w50', 'datepicker' => true],
            'sql'       => "int(10) unsigned NULL",
        ],
        'end'                     => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['end'],
            'inputType' => 'text',
            'exclude'   => true,
            'eval'      => ['rgxp' => 'datim', 'tl_class' => 'w50', 'datepicker' => true],
            'sql'       => "int(10) unsigned NULL",
        ],
        'whereClause'             => [
            'label'       => &$GLOBALS['TL_LANG']['tl_entity_import_config']['whereClause'],
            'inputType'   => 'textarea',
            'exclude'     => true,
            'eval'        => ['class' => 'monospace', 'rte' => 'ace', 'tl_class' => 'clr'],
            'explanation' => 'insertTags',
            'sql'         => "text NULL",
        ],
        'sourceDir'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['sourceDir'],
            'inputType' => 'fileTree',
            'exclude'   => true,
            'eval'      => ['files' => false, 'fieldType' => 'radio', 'tl_class' => 'clr'],
            'sql'       => "binary(16) NULL",
        ],
        'targetDir'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['targetDir'],
            'inputType' => 'fileTree',
            'exclude'   => true,
            'eval'      => ['files' => false, 'fieldType' => 'radio', 'tl_class' => 'clr'],
            'sql'       => "binary(16) NULL",
        ],
        'dbFieldFileMapping'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping'],
            'inputType' => 'multiColumnEditor',
            'exclude'   => true,
            'eval'      => [
                'tl_class'          => 'clr',
                'multiColumnEditor' => [
                    'fields' => [
                        'type'   => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping']['type'],
                            'inputType' => 'select',
                            'options'   => ['source', 'foreignKey', 'value'],
                            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping']['type'],
                            'eval'      => [
                                'chosen'     => true,
                                'groupStyle' => 'width:150px',
                            ],
                        ],
                        'source' => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping']['source'],
                            'inputType'        => 'select',
                            'options_callback' => ['tl_entity_import_config', 'getSourceFields'],
                            'eval'             => [
                                'chosen'             => true,
                                'groupStyle'         => 'width:150px',
                                'includeBlankOption' => true,
                            ],
                        ],
                        'value'  => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping']['value'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle'     => 'width:150px',
                                'decodeEntities' => true,
                            ],
                        ],
                        'target' => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping']['target'],
                            'inputType'        => 'select',
                            'options_callback' => ['tl_entity_import_config', 'getTargetFileFields'],
                            'eval'             => [
                                'chosen'     => true,
                                'groupStyle' => 'width:150px',
                            ],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'catTypo'                 => [
            'label'            => &$GLOBALS['TL_LANG']['tl_member']['catTypo'],
            'exclude'          => true,
            'inputType'        => 'checkboxWizard',
            'eval'             => ['multiple' => true, 'tl_class' => 'w50'],
            'options_callback' => ['tl_entity_import_config', 'getTypoCategories'],
            'sql'              => "blob NULL",
        ],
        'catContao'               => [
            'label'      => &$GLOBALS['TL_LANG']['tl_entity_import_config']['catContao'],
            'exclude'    => true,
            'inputType'  => 'treePicker',
            'foreignKey' => 'tl_news_category.title',
            'eval'       => [
                'multiple'     => true,
                'fieldType'    => 'checkbox',
                'foreignTable' => 'tl_news_category',
                'titleField'   => 'title',
                'searchField'  => 'title',
                'managerHref'  => 'do=news&table=tl_news_category',
            ],
            'sql'        => "blob NULL",
        ],
        'sourceFile'              => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['sourceFile'],
            'exclude'   => true,
            'inputType' => 'fileTree',
            'eval'      => ['filesOnly' => true, 'extensions' => Config::get('validEntityImportTypes'), 'fieldType' => 'radio', 'mandatory' => true, 'tl_class' => 'w50 autoheight'],
            'sql'       => "binary(16) NULL",
        ],
        'delimiter'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['delimiter'],
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => ',',
            'eval'      => ['mandatory' => true, 'maxlength' => 1, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'enclosure'               => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['enclosure'],
            'exclude'   => true,
            'default'   => '"',
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 1, 'tl_class' => 'w50'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'arrayDelimiter'          => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['arrayDelimiter'],
            'exclude'   => true,
            'inputType' => 'text',
            'default'   => ';',
            'eval'      => ['maxlength' => 1, 'tl_class' => 'w50 clr'],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'fileFieldMapping'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fileFieldMapping'],
            'inputType' => 'multiColumnEditor',
            'exclude'   => true,
            'eval'      => [
                'tl_class'          => 'clr',
                'multiColumnEditor' => [
                    'fields' => [
                        'type'             => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fileFieldMapping']['type'],
                            'inputType' => 'select',
                            'options'   => ['source', 'value'],
                            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fileFieldMapping']['type'],
                            'eval'      => [
                                'groupStyle' => 'width:220px',
                            ],
                        ],
                        'source'           => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fileFieldMapping']['source'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width:220px',
                                'rgxp'  => 'digit',
                            ],
                        ],
                        'value'            => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fileFieldMapping']['value'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width:220px',
                            ],
                        ],
                        'target'           => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fileFieldMapping']['target'],
                            'inputType'        => 'select',
                            'options_callback' => ['tl_entity_import_config', 'getTargetFields'],
                            'eval'             => [
                                'groupStyle' => 'width:220px',
                                'chosen' => true
                            ],
                        ],
                        'transformToArray' => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['fileFieldMapping']['transformToArray'],
                            'exclude'   => true,
                            'inputType' => 'checkbox',
                            'eval'      => [
                                'groupStyle' => 'width:100px',
                            ],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'useCron'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['useCron'],
            'inputType' => 'checkbox',
            'exclude'   => true,
            'eval'      => [
                'tl_class'          => 'clr',
                'submitOnChange'    => true
            ],
            'sql'       => "char(1) NOT NULL default ''",
        ],
        'cronInterval'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['cronInterval'],
            'exclude'   => true,
            'inputType' => 'select',
            'options'   => ['minutely', 'hourly', 'daily', 'weekly', 'monthly'],
            'eval'      => ['tl_class' => 'w50', 'includeBlankOption' => false],
            'sql'       => "varchar(12) NOT NULL default ''",
        ],
        'externalFieldMapping'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalFieldMapping'],
            'inputType' => 'multiColumnEditor',
            'exclude'   => true,
            'eval'      => [
                'tl_class'          => 'clr',
                'multiColumnEditor' => [
                    'fields' => [
                        'type'             => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalFieldMapping']['type'],
                            'inputType' => 'select',
                            'options'   => ['source', 'value'],
                            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalFieldMapping']['type'],
                            'eval'      => [
                                'groupStyle' => 'width:180px',
                            ],
                        ],
                        'source'           => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalFieldMapping']['source'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width:180px',
                            ],
                        ],
                        'value'            => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalFieldMapping']['value'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width:180px',
                            ],
                        ],
                        'target'           => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalFieldMapping']['target'],
                            'inputType'        => 'select',
                            'options_callback' => ['tl_entity_import_config', 'getTargetFields'],
                            'eval'             => [
                                'groupStyle' => 'width:180px',
                                'chosen' => true
                            ],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'externalImportExceptions'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalImportExceptions'],
            'inputType' => 'multiColumnEditor',
            'exclude'   => true,
            'eval'      => [
                'tl_class'          => 'clr',
                'multiColumnEditor' => [
                    'minRowCount' => 0,
                    'fields' => [
                        'externalField'             => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalImportExceptions']['externalField'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width:180px',
                            ],
                        ],
                        'operator'           => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalImportExceptions']['operator'],
                            'inputType' => 'select',
                            'options' => [
                                'equal',
                                'notequal',
                                'lower',
                                'greater',
                                'lowerequal',
                                'greaterequal',
                                'like'
                            ],
                            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['operators'],
                            'eval'      => [
                                'groupStyle' => 'width:75px',
                                'decodeEntities' => true
                            ],
                        ],
                        'externalValue'            => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalImportExceptions']['externalValue'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width:180px',
                            ],
                        ],
                        'importField'           => [
                            'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalImportExceptions']['importField'],
                            'inputType'        => 'select',
                            'options_callback' => ['tl_entity_import_config', 'getTargetFields'],
                            'eval'             => [
                                'groupStyle' => 'width:180px',
                                'chosen' => true
                            ],
                        ],
                        'importValue'            => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalImportExceptions']['importValue'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width:180px',
                            ],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'externalImportExclusions'        => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalImportExclusions'],
            'inputType' => 'multiColumnEditor',
            'exclude'   => true,
            'eval'      => [
                'tl_class'          => 'clr',
                'multiColumnEditor' => [
                    'minRowCount' => 0,
                    'fields' => [
                        'externalField'             => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalImportExclusions']['externalField'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width:180px',
                            ],
                        ],
                        'operator'           => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalImportExclusions']['operator'],
                            'inputType' => 'select',
                            'options' => [
                                'equal',
                                'notequal',
                                'lower',
                                'greater',
                                'lowerequal',
                                'greaterequal',
                                'like'
                            ],
                            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['operators'],
                            'eval'      => [
                                'groupStyle' => 'width:75px',
                                'decodeEntities' => true
                            ],
                        ],
                        'externalValue'            => [
                            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['externalImportExclusions']['externalValue'],
                            'inputType' => 'text',
                            'eval'      => [
                                'groupStyle' => 'width:180px',
                            ],
                        ],
                    ],
                ],
            ],
            'sql'       => "blob NULL",
        ],
        'publishAfterImport' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['publishAfterImport'],
            'inputType' => 'checkbox',
            'exclude'   => true,
            'eval'      => [
                'tl_class'          => 'clr w50',
            ],
            'sql'       => "char(1) NOT NULL default ''",
        ]
    ],
];


class tl_entity_import_config extends \Backend
{
    public static function initPalette()
    {
        $objEntityImportConfig = \HeimrichHannot\EntityImport\EntityImportConfigModel::findByPk(\Input::get('id'));
        $arrDca                = &$GLOBALS['TL_DCA']['tl_entity_import_config'];
        $strParentType         = \HeimrichHannot\EntityImport\EntityImportModel::findByPk($objEntityImportConfig->pid)->type;

        // add default palettes
        $arrDca['palettes']['default'] .= $arrDca['typepalettes'][$strParentType];

        switch ($strParentType)
        {
            case ENTITY_IMPORT_CONFIG_TYPE_DATABASE:
                break;
            default:
                $arrDca['fields']['mergeIdentifierFields']['eval']['multiColumnEditor']['fields']['source']['inputType'] = 'text';
                break;
        }

        // HOOK: add custom logic
        if (isset($GLOBALS['TL_HOOKS']['initEntityImportPalettes']) && is_array($GLOBALS['TL_HOOKS']['initEntityImportPalettes']))
        {
            foreach ($GLOBALS['TL_HOOKS']['initEntityImportPalettes'] as $arrCallback)
            {
                if (($objCallback = \Controller::importStatic($arrCallback[0])) !== null)
                {
                    $objCallback->{$arrCallback[1]}($objEntityImportConfig, $arrDca);
                }
            }
        }
    }

    public static function initNewsPalette($objEntityImportConfig, &$arrDca)
    {
        switch ($objEntityImportConfig->dbTargetTable)
        {
            case 'tl_news':
                $arrDca['palettes']['default'] .= '{category_legend},catContao';
                break;
        }
    }

    public static function getImporterClasses()
    {
        $arrOptions = [];

        $classes = $GLOBALS['ENTITY_IMPORTER'];

        foreach ($classes as $strClass => $strName)
        {
            if (!@class_exists($strClass))
            {
                continue;
            }

            $arrOptions[$strClass] = $strName;
        }

        asort($arrOptions);

        return $arrOptions;
    }

    public function getSourceFields($dc)
    {
        $arrOptions = [];

        $objModel = \HeimrichHannot\EntityImport\EntityImportConfigModel::findByPk($dc->id);

        if ($objModel === null || $objModel->dbSourceTable == null)
        {
            return $arrOptions;
        }

        $arrFields = \HeimrichHannot\EntityImport\Database::getInstance(
            \HeimrichHannot\EntityImport\EntityImportModel::findByPk($objModel->pid)->row()
        )->listFields($objModel->dbSourceTable);

        if (!is_array($arrFields) || empty($arrFields))
        {
            return $arrOptions;
        }

        foreach ($arrFields as $arrField)
        {
            if (in_array($arrField['type'], ['index']))
            {
                continue;
            }

            $arrOptions[$arrField['name']] = $arrField['name'] . ' [' . $arrField['origtype'] . ']';
        }

        return $arrOptions;
    }

    public function getTargetFields($dc)
    {
        $arrOptions = [];

        $objModel = \HeimrichHannot\EntityImport\EntityImportConfigModel::findByPk($dc->id);

        if ($objModel === null || !$objModel->dbTargetTable)
        {
            return $arrOptions;
        }

        $arrFields = \Database::getInstance()->listFields($objModel->dbTargetTable);

        if (!is_array($arrFields) || empty($arrFields))
        {
            return $arrOptions;
        }

        $arrOptions['tl_content'] = &$GLOBALS['TL_LANG']['tl_entity_import_config']['createNewContentElement'];

        foreach ($arrFields as $arrField)
        {
            if (in_array($arrField, ['index']))
            {
                continue;
            }

            $arrOptions[$arrField['name']] = $arrField['name'] . ' [' . $arrField['origtype'] . ']';
        }


        return $arrOptions;
    }

    public function getMergeTargetFields($dc)
    {
        $arrOptions = $this->getTargetFields($dc);

        unset($arrOptions['tl_content']);

        return $arrOptions;
    }

    public function getTargetFileFields($dc)
    {
        $arrOptions = [];

        $arrFields = \Database::getInstance()->listFields('tl_files');

        if (!is_array($arrFields) || empty($arrFields))
        {
            return $arrOptions;
        }

        foreach ($arrFields as $arrField)
        {
            if (in_array($arrField['type'], ['index']))
            {
                continue;
            }

            $arrOptions[$arrField['name']] = $arrField['name'] . ' [' . $arrField['origtype'] . ']';
        }


        return $arrOptions;
    }

    public function getSourceTables(\DataContainer $dc)
    {
        if(null === ($source = \HeimrichHannot\EntityImport\EntityImportModel::findByPk($dc->activeRecord->pid))){
            return [];
        }

        $arrTables = \HeimrichHannot\EntityImport\Database::getInstance(
            $source->row()
        )->listTables();

        return array_values($arrTables);
    }

    public function getTargetTables(\DataContainer $dc)
    {
        $arrTables = \HeimrichHannot\EntityImport\Database::getInstance()->listTables();

        return array_values($arrTables);
    }

    public function getContaoCategories(DataContainer $dc)
    {
        $arrOptions = [];

        if (!in_array('news_categories', \Config::getInstance()->getActiveModules()))
        {
            return $arrOptions;
        }

        $objCategories = \NewsCategories\NewsCategoryModel::findBy('published', 1);

        if ($objCategories === null)
        {
            return $arrOptions;
        }

        while ($objCategories->next())
        {
            $arrOptions[$objCategories->id] = $objCategories->title;
        }

        return $arrOptions;
    }

    public function getTypoCategories(DataContainer $dc)
    {
        $arrOptions = [];

        if (!in_array('news_categories', \Config::getInstance()->getActiveModules()))
        {
            return $arrOptions;
        }

        $objCategories = \HeimrichHannot\EntityImport\Database::getInstance()->prepare('SELECT * FROM tt_news_cat WHERE deleted = 0 AND hidden=0')->execute();

        if ($objCategories->count() < 1)
        {
            return $arrOptions;
        }

        while ($objCategories->next())
        {
            $arrOptions[$objCategories->uid] = $objCategories->title;
        }

        return $arrOptions;
    }

    public function getPidsFromTable(DataContainer $dc)
    {
        $arrArchives = [];

        $objArchives = \HeimrichHannot\Typort\Database::getInstance()->prepare(
            'SELECT p.title, p.uid, COUNT(n.uid) AS total FROM ' . $dc->activeRecord->type . ' n
			INNER JOIN pages p ON p.uid = n.pid
			WHERE n.deleted=0 AND p.deleted = 0 GROUP BY n.pid ORDER BY n.pid'
        )->execute();

        if ($objArchives === null)
        {
            return $arrArchives;
        }

        while ($objArchives->next())
        {
            $arrArchives[$objArchives->uid] = $objArchives->title . ' [Id: ' . $objArchives->uid . '] (Count:' . $objArchives->total . ')';
        }

        return $arrArchives;
    }

    public function listEntityImportConfig($arrRow)
    {
        $strText = $arrRow['description'] ? '<span style="color:#b3b3b3;padding-left:3px"> [' . $arrRow['description'] . '] </span>' : '';

        return '<div class="tl_content_left">' . $arrRow['title'] . $strText . '</div>';
    }


    public function getExternalImporterClasses()
    {
        $arrOptions = [];

        $classes = $GLOBALS['EXTERNAL_ENTITY_IMPORTER'];

        foreach ($classes as $strClass => $strName)
        {
            if (!@class_exists($strClass))
            {
                continue;
            }

            $arrOptions[$strClass] = $strName;
        }

        asort($arrOptions);

        return $arrOptions;
    }


}

