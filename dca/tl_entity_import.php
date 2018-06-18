<?php

/**
 * Table tl_extension
 */
$GLOBALS['TL_DCA']['tl_entity_import'] = [

    // Config
    'config'      => [
        'dataContainer'    => 'Table',
        'enableVersioning' => true,
        'ctable'           => ['tl_entity_import_config'],
        'sql'              => [
            'keys' => [
                'id' => 'primary',
            ],
        ],
    ],

    // List
    'list'        => [
        'sorting'           => [
            'mode'        => 2,
            'fields'      => ['title'],
            'flag'        => 1,
            'panelLayout' => 'search,limit',
        ],
        'label'             => [
            'fields'         => ['title', 'type'],
            'format'         => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
            'label_callback' => ['tl_entity_import', 'addDate'],
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
            'edit'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import']['edit'],
                'href'  => 'table=tl_entity_import_config',
                'icon'  => 'edit.gif',
            ],
            'editheader' => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import']['editheader'],
                'href'  => 'act=edit',
                'icon'  => 'header.gif',
            ],
            'copy'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import']['copy'],
                'href'  => 'act=copy',
                'icon'  => 'copy.gif',
            ],
            'delete'     => [
                'label'      => &$GLOBALS['TL_LANG']['tl_entity_import']['delete'],
                'href'       => 'act=delete',
                'icon'       => 'delete.gif',
                'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"',
            ],
            'show'       => [
                'label' => &$GLOBALS['TL_LANG']['tl_entity_import']['show'],
                'href'  => 'act=show',
                'icon'  => 'show.gif',
            ]
        ],
    ],

    // Palettes
    'palettes'    => [
        '__selector__' => ['type'],
        'default'      => '{title_legend},title,type;',
        'db'           => '{title_legend},title,type;{db_legend},dbDriver,dbHost,dbUser,dbPass,dbDatabase,dbPconnect,dbCharset,dbPort,dbSocket',
        'file'         => '{title_legend},title,type;',
    ],

    // Subpalettes
    'subpalettes' => [],
    // Fields
    'fields'      => [
        'id'         => [
            'sql' => "int(10) unsigned NOT NULL auto_increment",
        ],
        'tstamp'     => [
            'sql' => "int(10) unsigned NOT NULL default '0'",
        ],
        'title'      => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['title'],
            'search'    => true,
            'exclude'   => true,
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 128, 'tl_class' => 'w50'],
            'sql'       => "varchar(128) NOT NULL default ''",
        ],
        'type'       => [
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['type'],
            'exclude'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => [
                ENTITY_IMPORT_CONFIG_TYPE_DATABASE,
                ENTITY_IMPORT_CONFIG_TYPE_FILE,
            ],
            'reference' => &$GLOBALS['TL_LANG']['tl_entity_import']['type'],
            'eval'      => ['submitOnChange' => true, 'includeBlankOption' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(255) NOT NULL default ''",
        ],
        'dbDriver'   => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbDriver'],
            'inputType' => 'select',
            'default'   => version_compare(VERSION, '4.0', '<') ? \Config::get('dbDriver') : 'pdo_mysql',
            'options'   => version_compare(VERSION, '4.0', '<') ? ['MySQLi', 'MySQL'] : ['pdo_mysql'],
            'eval'      => ['mandatory' => true, 'tl_class' => 'w50'],
            'sql'       => "varchar(12) NOT NULL default ''",
        ],
        'dbHost'     => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbHost'],
            'inputType' => 'text',
            'default'   => \Config::get('dbHost'),
            'eval'      => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'dbUser'     => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbUser'],
            'inputType' => 'text',
            'default'   => \Config::get('dbUser'),
            'eval'      => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'dbPass'     => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbPass'],
            'inputType' => 'text',
            'eval'      => ['maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'dbDatabase' => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbDatabase'],
            'inputType' => 'text',
            'eval'      => ['mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
        'dbPconnect' => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbPconnect'],
            'inputType' => 'select',
            'default'   => 'false',
            'options'   => ['false', 'true'],
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(5) NOT NULL default ''",
        ],
        'dbCharset'  => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbCharset'],
            'inputType' => 'text',
            'default'   => \Config::get('dbCharset'),
            'eval'      => ['mandatory' => true, 'maxlength' => 32, 'tl_class' => 'w50'],
            'sql'       => "varchar(32) NOT NULL default ''",
        ],
        'dbPort'     => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbPort'],
            'inputType' => 'text',
            'default'   => \Config::get('dbPort'),
            'eval'      => ['maxlength' => 5, 'tl_class' => 'w50', 'rgxp' => 'digit'],
            'sql'       => "int(5) unsigned NOT NULL default '0'",
        ],
        'dbSocket'   => [
            'exclude'   => true,
            'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbSocket'],
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50'],
            'sql'       => "varchar(64) NOT NULL default ''",
        ],
    ],
];


class tl_entity_import extends Backend
{
    public function getContaoCategories(DataContainer $dc)
    {
        $arrOptions = [];

        if (!in_array('news_categories', \Config::getInstance()->getActiveModules()))
        {
            return $arrOptions;
        }

        $objCategories = NewsCategories\NewsCategoryModel::findBy('published', 1);

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

        $objCategories = HeimrichHannot\Typort\Database::getInstance()->prepare('SELECT * FROM tt_news_cat WHERE deleted = 0 AND hidden=0')->execute();

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

        $objArchives = HeimrichHannot\Typort\Database::getInstance()->prepare(
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


    public function addDate($row, $label)
    {

        if ($row['start'] || $row['end'])
        {
            $label .= '&nbsp;<strong>[';

            if ($row['start'])
            {
                $label .= $GLOBALS['TL_LANG']['tl_entity_import']['start'][0] . ': ' . \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['start']);

                if ($row['end'])
                {
                    $label .= '&nbsp;-&nbsp;';
                }
            }

            if ($row['end'])
            {
                $label .= $GLOBALS['TL_LANG']['tl_entity_import']['end'][0] . ': ' . \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['end']);
            }

            $label .= ']</strong>';
        }

        return $label;
    }

}
