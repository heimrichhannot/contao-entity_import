<?php

/**
 * Table tl_extension
 */
$GLOBALS['TL_DCA']['tl_entity_import'] = array
(

	// Config
	'config'      => array
	(
		'dataContainer'    => 'Table',
		'enableVersioning' => true,
		'ctable'           => array('tl_entity_import_config'),
		'sql'              => array
		(
			'keys' => array
			(
				'id' => 'primary'
			)
		)
	),

	// List
	'list'        => array
	(
		'sorting'           => array
		(
			'mode'        => 2,
			'fields'      => array('title'),
			'flag'        => 1,
			'panelLayout' => 'search,limit'
		),
		'label'             => array
		(
			'fields'         => array('title', 'type'),
			'format'         => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
			'label_callback' => array('tl_entity_import', 'addDate'),
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'       => 'act=select',
				'class'      => 'header_edit_all',
				'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"'
			)
		),
		'operations'        => array
		(
			'edit' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_entity_import']['edit'],
				'href'                => 'table=tl_entity_import_config',
				'icon'                => 'edit.gif'
			),
			'editheader' => array
			(
				'label'               => &$GLOBALS['TL_LANG']['tl_entity_import']['editheader'],
				'href'                => 'act=edit',
				'icon'                => 'header.gif',
			),
			'copy'   => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_entity_import']['copy'],
				'href'  => 'act=copy',
				'icon'  => 'copy.gif'
			),
			'delete' => array
			(
				'label'      => &$GLOBALS['TL_LANG']['tl_entity_import']['delete'],
				'href'       => 'act=delete',
				'icon'       => 'delete.gif',
				'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm'] . '\')) return false; Backend.getScrollOffset();"'
			),
			'show'   => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_entity_import']['show'],
				'href'  => 'act=show',
				'icon'  => 'show.gif'
			),
			// TODO: check if import all is possible?
//			'import' => array
//			(
//				'label' => &$GLOBALS['TL_LANG']['tl_entity_import']['import'],
//				'href'  => 'key=import',
//				'icon'  => 'system/modules/devtools/assets/apply.gif'
//			)
		)
	),

	// Palettes
	'palettes'    => array
	(
		'__selector__' => array('type'),
		'default'      => '{title_legend},title;{db_legend},dbDriver,dbHost,dbUser,dbPass,dbDatabase,dbPconnect,dbCharset,dbPort,dbSocket',
	),

	// Subpalettes
	'subpalettes' => array
	(),
	// Fields
	'fields'      => array
	(
		'id'         => array
		(
			'sql' => "int(10) unsigned NOT NULL auto_increment"
		),
		'tstamp'     => array
		(
			'sql' => "int(10) unsigned NOT NULL default '0'"
		),
		'title'      => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['title'],
			'search'    => true,
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('mandatory' => true, 'maxlength' => 128, 'tl_class' => 'w50'),
			'sql'       => "varchar(128) NOT NULL default ''"
		),
		'dbDriver'   => array
		(
			'exclude'   => true,
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbDriver'],
			'inputType' => 'select',
			'default'   => \Config::get('dbDriver'),
			'options'   => array('MySQLi', 'MySQL'),
			'eval'      => array('mandatory' => true, 'tl_class' => 'w50'),
			'sql'       => "varchar(6) NOT NULL default ''"
		),
		'dbHost'     => array
		(
			'exclude'   => true,
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbHost'],
			'inputType' => 'text',
			'default'   => \Config::get('dbHost'),
			'eval'      => array('mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'),
			'sql'       => "varchar(64) NOT NULL default ''"
		),
		'dbUser'     => array
		(
			'exclude'   => true,
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbUser'],
			'inputType' => 'text',
			'default'   => \Config::get('dbUser'),
			'eval'      => array('mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'),
			'sql'       => "varchar(64) NOT NULL default ''"
		),
		'dbPass'     => array
		(
			'exclude'   => true,
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbPass'],
			'inputType' => 'text',
			'eval'      => array('maxlength' => 64, 'tl_class' => 'w50'),
			'sql'       => "varchar(64) NOT NULL default ''"
		),
		'dbDatabase' => array
		(
			'exclude'   => true,
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbDatabase'],
			'inputType' => 'text',
			'eval'      => array('mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'),
			'sql'       => "varchar(64) NOT NULL default ''"
		),
		'dbPconnect' => array
		(
			'exclude'   => true,
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbPconnect'],
			'inputType' => 'select',
			'default'	=> 'false',
			'options'	=> array('false', 'true'),
			'eval'      => array('tl_class' => 'w50'),
			'sql'       => "varchar(5) NOT NULL default ''"
		),
		'dbCharset'  => array
		(
			'exclude'   => true,
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbCharset'],
			'inputType' => 'text',
			'default'	=> \Config::get('dbCharset'),
			'eval'      => array('mandatory' => true, 'maxlength' => 32, 'tl_class' => 'w50'),
			'sql'       => "varchar(32) NOT NULL default ''"
		),
		'dbPort'     => array
		(
			'exclude'   => true,
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbPort'],
			'inputType' => 'text',
			'default'	=> \Config::get('dbPort'),
			'eval'      => array('maxlength' => 5, 'tl_class' => 'w50', 'rgxp' => 'digit'),
			'sql'       => "int(5) unsigned NOT NULL default '0'"
		),
		'dbSocket'   => array
		(
			'exclude'   => true,
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import']['dbSocket'],
			'inputType' => 'text',
			'eval'      => array('tl_class' => 'w50'),
			'sql'       => "varchar(64) NOT NULL default ''"
		),
	)
);


class tl_entity_import extends Backend
{
	public function getContaoCategories(DataContainer $dc)
	{
		$arrOptions = array();

		if (!in_array('news_categories', \Config::getInstance()->getActiveModules())) return $arrOptions;

		$objCategories = NewsCategories\NewsCategoryModel::findBy('published', 1);

		if ($objCategories === null) return $arrOptions;

		while ($objCategories->next()) {
			$arrOptions[$objCategories->id] = $objCategories->title;
		}

		return $arrOptions;
	}

	public function getTypoCategories(DataContainer $dc)
	{
		$arrOptions = array();

		if (!in_array('news_categories', \Config::getInstance()->getActiveModules())) return $arrOptions;

		$objCategories = HeimrichHannot\Typort\Database::getInstance()->prepare('SELECT * FROM tt_news_cat WHERE deleted = 0 AND hidden=0')->execute();

		if ($objCategories->count() < 1) return $arrOptions;

		while ($objCategories->next()) {
			$arrOptions[$objCategories->uid] = $objCategories->title;
		}

		return $arrOptions;
	}

	public function getPidsFromTable(DataContainer $dc)
	{
		$arrArchives = array();

		$objArchives = HeimrichHannot\Typort\Database::getInstance()->prepare(
			'SELECT p.title, p.uid, COUNT(n.uid) AS total FROM ' . $dc->activeRecord->type . ' n
			INNER JOIN pages p ON p.uid = n.pid
			WHERE n.deleted=0 AND p.deleted = 0 GROUP BY n.pid ORDER BY n.pid')
			->execute();

		if ($objArchives === null) return $arrArchives;

		while ($objArchives->next()) {
			$arrArchives[$objArchives->uid] = $objArchives->title . ' [Id: ' . $objArchives->uid . '] (Count:' . $objArchives->total . ')';
		}

		return $arrArchives;
	}


	public function addDate($row, $label)
	{

		if ($row['start'] || $row['end']) {
			$label .= '&nbsp;<strong>[';

			if ($row['start']) {
				$label .= $GLOBALS['TL_LANG']['tl_entity_import']['start'][0] . ': ' . \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['start']);

				if ($row['end']) {
					$label .= '&nbsp;-&nbsp;';
				}
			}

			if ($row['end']) {
				$label .= $GLOBALS['TL_LANG']['tl_entity_import']['end'][0] . ': ' . \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['end']);
			}

			$label .= ']</strong>';
		}

		return $label;
	}

	/**
	 * Add the type of input field
	 * @param array
	 * @return string
	 */
	public function listEntityImportConfigs($arrRow)
	{
//		if ($row['start'] || $row['end']) {
//			$label .= '&nbsp;<strong>[';
//
//			if ($row['start']) {
//				$label .= $GLOBALS['TL_LANG']['tl_entity_import']['start'][0] . ': ' . \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['start']);
//
//				if ($row['end']) {
//					$label .= '&nbsp;-&nbsp;';
//				}
//			}
//
//			if ($row['end']) {
//				$label .= $GLOBALS['TL_LANG']['tl_entity_import']['end'][0] . ': ' . \Date::parse($GLOBALS['TL_CONFIG']['datimFormat'], $row['end']);
//			}
//
//			$label .= ']</strong>';
//		}
//
//		return $label;

		return '<div class="tl_content_left">' . $arrRow['title'] . ' <span style="color:#b3b3b3;padding-left:3px">[' . Date::parse(Config::get('datimFormat'), $arrRow['date']) . ']</span></div>';
	}

}
