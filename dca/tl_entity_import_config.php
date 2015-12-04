<?php

/**
 * Table tl_extension
 */
$GLOBALS['TL_DCA']['tl_entity_import_config'] = array
(

	// Config
	'config'      => array
	(
		'dataContainer'    => 'Table',
		'enableVersioning' => true,
		'ptable'           => 'tl_entity_import',
		'sql'              => array
		(
			'keys' => array
			(
				'id'  => 'primary',
				'pid' => 'index',
			),
		),
		'onload_callback'  => array(array('tl_entity_import_config', 'initPalette')),
	),
	// List
	'list'        => array
	(
		'sorting'           => array
		(
			'mode'                  => 4,
			'fields'                => array('title DESC'),
			'headerFields'          => array('title', 'dbHost', 'dbUser', 'dbDatabase'),
			'panelLayout'           => 'filter;sort,search,limit',
			'child_record_callback' => array('tl_entity_import_config', 'listEntityImportConfig'),
			'child_record_class'    => 'no_padding',
			'disableGrouping'       => true,
		),
		'label'             => array
		(
			'fields'         => array('title', 'type'),
			'format'         => '%s <span style="color:#b3b3b3; padding-left:3px;">[%s]</span>',
			'label_callback' => array('tl_entity_import_config', 'addDate'),
		),
		'global_operations' => array
		(
			'all' => array
			(
				'label'      => &$GLOBALS['TL_LANG']['MSC']['all'],
				'href'       => 'act=select',
				'class'      => 'header_edit_all',
				'attributes' => 'onclick="Backend.getScrollOffset();" accesskey="e"',
			),
		),
		'operations'        => array
		(
			'edit'   => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['edit'],
				'href'  => 'act=edit',
				'icon'  => 'edit.gif',
			),
			'copy'   => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['copy'],
				'href'  => 'act=copy',
				'icon'  => 'copy.gif',
			),
			'delete' => array
			(
				'label'      => &$GLOBALS['TL_LANG']['tl_entity_import_config']['delete'],
				'href'       => 'act=delete',
				'icon'       => 'delete.gif',
				'attributes' => 'onclick="if (!confirm(\'' . $GLOBALS['TL_LANG']['MSC']['deleteConfirm']
								. '\')) return false; Backend.getScrollOffset();"',
			),
			'show'   => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['show'],
				'href'  => 'act=show',
				'icon'  => 'show.gif',
			),
			'import' => array
			(
				'label' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['import'],
				'href'  => 'key=import',
				'icon'  => 'system/modules/devtools/assets/apply.gif',
			),
		),
	),
	// Palettes
	'palettes'    => array
	(
		'__selector__' => array('type', 'useTimeInterval', 'purgeBeforeImport'),
		'default'      => '{title_legend},title,description;{config_legend},dbSourceTable,dbTargetTable,importerClass,purgeBeforeImport,dbFieldMapping,useTimeInterval,whereClause,sourceDir,targetDir,dbFieldFileMapping;',
	),
	// Subpalettes
	'subpalettes' => array
	(
		'useTimeInterval' => 'start,end',
		'purgeBeforeImport' => 'whereClausePurge'
	),
	// Fields
	'fields'      => array
	(
		'id'              => array
		(
			'sql' => "int(10) unsigned NOT NULL auto_increment",
		),
		'pid'             => array
		(
			'foreignKey' => 'tl_entity_import.title',
			'sql'        => "int(10) unsigned NOT NULL default '0'",
			'relation'   => array('type' => 'belongsTo', 'load' => 'eager'),
		),
		'tstamp'          => array
		(
			'sql' => "int(10) unsigned NOT NULL default '0'",
		),
		'title'           => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['title'],
			'search'    => true,
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('mandatory' => true, 'maxlength' => 64, 'tl_class' => 'w50'),
			'sql'       => "varchar(64) NOT NULL default ''",
		),
		'description'     => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['description'],
			'search'    => true,
			'exclude'   => true,
			'inputType' => 'text',
			'eval'      => array('maxlength' => 255, 'tl_class' => 'long clr'),
			'sql'       => "varchar(255) NOT NULL default ''",
		),
		'dbSourceTable'   => array
		(
			'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbSourceTable'],
			'search'           => true,
			'exclude'          => true,
			'inputType'        => 'select',
			'eval'             => array('mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50'),
			'options_callback' => array('tl_entity_import_config', 'getSourceTables'),
			'sql'              => "varchar(255) NOT NULL default ''",
		),
		'dbTargetTable'   => array
		(
			'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbTargetTable'],
			'search'           => true,
			'exclude'          => true,
			'inputType'        => 'select',
			'eval'             => array('mandatory' => true, 'submitOnChange' => true, 'tl_class' => 'w50'),
			'options_callback' => array('tl_entity_import_config', 'getTargetTables'),
			'sql'              => "varchar(255) NOT NULL default ''",
		),
		'importerClass'   => array
		(
			'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['importerClass'],
			'inputType'        => 'select',
			'eval'             => array('mandatory' => true, 'tl_class' => 'w50', 'decodeEntities' => true),
			'options_callback' => array('tl_entity_import_config', 'getImporterClasses'),
			'sql'              => "varchar(255) NOT NULL default ''",
		),
		'purgeBeforeImport' => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['purgeBeforeImport'],
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => array('submitOnChange' => true, 'tl_class' => 'w50'),
			'sql'       => "char(1) NOT NULL default ''",
		),
		'whereClausePurge'     => array
		(
			'label'       => &$GLOBALS['TL_LANG']['tl_entity_import_config']['whereClausePurge'],
			'inputType'   => 'textarea',
			'exclude'     => true,
			'eval'        => array('class' => 'monospace', 'rte' => 'ace', 'tl_class' => 'clr long'),
			'explanation' => 'insertTags',
			'sql'         => "text NULL",
		),
		'dbFieldMapping'  => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping'],
			'inputType' => 'multiColumnWizard',
			'exclude'   => true,
			'eval'      => array
			(
				'tl_class'     => 'clr',
				'columnFields' => array
				(
					'type'   => array
					(
						'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['type'],
						'inputType' => 'select',
						'options'   => array('source', 'foreignKey', 'value'),
						'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMappingOptions'],
						'eval'      => array
						(
							'style' => 'width:150px',
						),
					),
					'source' => array
					(
						'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['source'],
						'inputType'        => 'select',
						'options_callback' => array('tl_entity_import_config', 'getSourceFields'),
						'eval'             => array
						(
							'style'              => 'width:150px',
							'includeBlankOption' => true,
						),
					),
					'value'  => array
					(
						'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['value'],
						'inputType' => 'text',
						'eval'      => array
						(
							'style' => 'width:150px',
						),
					),
					'target' => array
					(
						'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['target'],
						'inputType'        => 'select',
						'options_callback' => array('tl_entity_import_config', 'getTargetFields'),
						'eval'             => array
						(
							'style' => 'width:150px',
						),
					),
				),
			),
			'sql'       => "blob NULL",
		),
		//		'pids'           => array
		//		(
		//			'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['pids'],
		//			'inputType'        => 'checkbox',
		//			'exclude'          => true,
		//			'eval'             => array('mandatory' => true, 'submitOnChange' => true, 'multiple' => true),
		//			'options_callback' => array('tl_entity_import_config', 'getPidsFromTable'),
		//			'sql'              => "blob NULL",
		//		),
		'useTimeInterval' => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['useTimeInterval'],
			'exclude'   => true,
			'inputType' => 'checkbox',
			'eval'      => array('submitOnChange' => true),
			'sql'       => "char(1) NOT NULL default ''",
		),
		'start'           => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['start'],
			'inputType' => 'text',
			'exclude'   => true,
			'eval'      => array('rgxp' => 'datim', 'tl_class' => 'w50', 'datepicker' => true),
			'sql'       => "int(10) unsigned NULL",
		),
		'end'             => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['end'],
			'inputType' => 'text',
			'exclude'   => true,
			'eval'      => array('rgxp' => 'datim', 'tl_class' => 'w50', 'datepicker' => true),
			'sql'       => "int(10) unsigned NULL",
		),
		'whereClause'     => array
		(
			'label'       => &$GLOBALS['TL_LANG']['tl_entity_import_config']['whereClause'],
			'inputType'   => 'textarea',
			'exclude'     => true,
			'eval'        => array('class' => 'monospace', 'rte' => 'ace'),
			'explanation' => 'insertTags',
			'sql'         => "text NULL",
		),
		'sourceDir'       => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['sourceDir'],
			'inputType' => 'fileTree',
			'exclude'   => true,
			'eval'      => array('files' => false, 'fieldType' => 'radio', 'tl_class' => 'w50'),
			'sql'       => "binary(16) NULL",
		),
		'targetDir'       => array
		(
			'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['targetDir'],
			'inputType' => 'fileTree',
			'exclude'   => true,
			'eval'      => array('files' => false, 'fieldType' => 'radio', 'tl_class' => 'w50'),
			'sql'       => "binary(16) NULL",
		),
		'dbFieldFileMapping'  => array
		(
				'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldFileMapping'],
				'inputType' => 'multiColumnWizard',
				'exclude'   => true,
				'eval'      => array
				(
						'tl_class'     => 'clr',
						'columnFields' => array
						(
								'type'   => array
								(
										'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['type'],
										'inputType' => 'select',
										'options'   => array('source', 'foreignKey', 'value'),
										'reference' => &$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMappingOptions'],
										'eval'      => array
										(
												'style' => 'width:150px',
										),
								),
								'source' => array
								(
										'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['source'],
										'inputType'        => 'select',
										'options_callback' => array('tl_entity_import_config', 'getSourceFields'),
										'eval'             => array
										(
												'style'              => 'width:150px',
												'includeBlankOption' => true,
										),
								),
								'value'  => array
								(
										'label'     => &$GLOBALS['TL_LANG']['tl_entity_import_config']['value'],
										'inputType' => 'text',
										'eval'      => array
										(
												'style' => 'width:150px',
										),
								),
								'target' => array
								(
										'label'            => &$GLOBALS['TL_LANG']['tl_entity_import_config']['target'],
										'inputType'        => 'select',
										'options_callback' => array('tl_entity_import_config', 'getTargetFileFields'),
										'eval'             => array
										(
												'style' => 'width:150px',
										),
								),
						),
				),
				'sql'       => "blob NULL",
		),
		'catTypo'         => array
		(
			'label'            => &$GLOBALS['TL_LANG']['tl_member']['catTypo'],
			'exclude'          => true,
			'inputType'        => 'checkboxWizard',
			'eval'             => array('multiple' => true, 'tl_class' => 'w50'),
			'options_callback' => array('tl_entity_import_config', 'getTypoCategories'),
			'sql'              => "blob NULL",
		),
		'catContao'       => array
		(
			'label'      => &$GLOBALS['TL_LANG']['tl_entity_import_config']['catContao'],
			'exclude'    => true,
			'inputType'  => 'treePicker',
			'foreignKey' => 'tl_news_category.title',
			'eval'       => array(
				'multiple'     => true,
				'fieldType'    => 'checkbox',
				'foreignTable' => 'tl_news_category',
				'titleField'   => 'title',
				'searchField'  => 'title',
				'managerHref'  => 'do=news&table=tl_news_category',
			),
			'sql'        => "blob NULL",
		),
	),
);


class tl_entity_import_config extends \Backend
{
	public static function initPalette()
	{
		$objEntityImportConfig = \HeimrichHannot\EntityImport\EntityImportConfigModel::findByPk(\Input::get('id'));
		$arrDca                = &$GLOBALS['TL_DCA']['tl_entity_import_config'];

		// HOOK: add custom logic
		if (isset($GLOBALS['TL_HOOKS']['initEntityImportPalettes']) && is_array($GLOBALS['TL_HOOKS']['initEntityImportPalettes'])) {
			foreach ($GLOBALS['TL_HOOKS']['initEntityImportPalettes'] as $arrCallback) {
				if (($objCallback = \Controller::importStatic($arrCallback[0])) !== null) {
					$objCallback->{$arrCallback[1]}($objEntityImportConfig, $arrDca);
				}
			}
		}
	}

	public static function initNewsPalette($objEntityImportConfig, &$arrDca)
	{
		switch ($objEntityImportConfig->dbTargetTable) {
			case 'tl_news':
				$arrDca['palettes']['default'] .= '{category_legend},catContao';
				break;
		}
	}

	public function getSourceFields($dc)
	{
		$arrOptions = array();

		if ($dc->activeRecord->dbSourceTable == null) {
			return $arrOptions;
		}

		$arrFields = \HeimrichHannot\EntityImport\Database::getInstance(
			\HeimrichHannot\EntityImport\EntityImportModel::findByPk($dc->activeRecord->pid)->row()
		)->listFields($dc->activeRecord->dbSourceTable);

		if (!is_array($arrFields) || empty($arrFields)) {
			return $arrOptions;
		}

		foreach ($arrFields as $arrField) {
			if (in_array($arrField['type'], array('index'))) {
				continue;
			}

			$arrOptions[$arrField['name']] = $arrField['name'] . ' [' . $arrField['origtype'] . ']';
		}

		return $arrOptions;
	}


	public function getTargetFields($dc)
	{
		$arrOptions = array();

		if (!$dc->activeRecord->dbTargetTable) {
			return $arrOptions;
		}

		$arrFields = \Database::getInstance()->listFields($dc->activeRecord->dbTargetTable);

		if (!is_array($arrFields) || empty($arrFields)) {
			return $arrOptions;
		}

		$arrOptions['tl_content'] = &$GLOBALS['TL_LANG']['tl_entity_import_config']['createNewContentElement'];

		foreach ($arrFields as $arrField) {
			if (in_array($arrField['type'], array('index'))) {
				continue;
			}

			$arrOptions[$arrField['name']] = $arrField['name'] . ' [' . $arrField['origtype'] . ']';
		}


		return $arrOptions;
	}

	public function getTargetFileFields($dc)
	{
		$arrOptions = array();

		$arrFields = \Database::getInstance()->listFields('tl_files');

		if (!is_array($arrFields) || empty($arrFields)) {
			return $arrOptions;
		}

		foreach ($arrFields as $arrField) {
			if (in_array($arrField['type'], array('index'))) {
				continue;
			}

			$arrOptions[$arrField['name']] = $arrField['name'] . ' [' . $arrField['origtype'] . ']';
		}


		return $arrOptions;
	}

	public function getSourceTables(\DataContainer $dc)
	{
		$arrTables = \HeimrichHannot\EntityImport\Database::getInstance(
			\HeimrichHannot\EntityImport\EntityImportModel::findByPk($dc->activeRecord->pid)->row()
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
		$arrOptions = array();

		if (!in_array('news_categories', \Config::getInstance()->getActiveModules())) {
			return $arrOptions;
		}

		$objCategories = \NewsCategories\NewsCategoryModel::findBy('published', 1);

		if ($objCategories === null) {
			return $arrOptions;
		}

		while ($objCategories->next()) {
			$arrOptions[$objCategories->id] = $objCategories->title;
		}

		return $arrOptions;
	}

	public function getTypoCategories(DataContainer $dc)
	{
		$arrOptions = array();

		if (!in_array('news_categories', \Config::getInstance()->getActiveModules())) {
			return $arrOptions;
		}

		$objCategories =
			\HeimrichHannot\Typort\Database::getInstance()->prepare('SELECT * FROM tt_news_cat WHERE deleted = 0 AND hidden=0')->execute();

		if ($objCategories->count() < 1) {
			return $arrOptions;
		}

		while ($objCategories->next()) {
			$arrOptions[$objCategories->uid] = $objCategories->title;
		}

		return $arrOptions;
	}

	public function getPidsFromTable(DataContainer $dc)
	{
		$arrArchives = array();

		$objArchives = \HeimrichHannot\Typort\Database::getInstance()->prepare(
			'SELECT p.title, p.uid, COUNT(n.uid) AS total FROM ' . $dc->activeRecord->type . ' n
			INNER JOIN pages p ON p.uid = n.pid
			WHERE n.deleted=0 AND p.deleted = 0 GROUP BY n.pid ORDER BY n.pid'
		)
			->execute();

		if ($objArchives === null) {
			return $arrArchives;
		}

		while ($objArchives->next()) {
			$arrArchives[$objArchives->uid] = $objArchives->title . ' [Id: ' . $objArchives->uid . '] (Count:' . $objArchives->total . ')';
		}

		return $arrArchives;
	}


	public function listEntityImportConfig($arrRow)
	{
		$strText = $arrRow['description'] ? '<span style="color:#b3b3b3;padding-left:3px"> [' . $arrRow['description'] . '] </span>' : '';

		return '<div class="tl_content_left">' . $arrRow['title'] . $strText . '</div>';
	}

	public static function getImporterClasses()
	{
		$arrOptions = array();

		foreach (array_keys(\Contao\ClassLoader::getClasses()) as $strName) {
			if (strpos($strName, 'HeimrichHannot\\EntityImport\\') !== false
				&& ($strName == 'HeimrichHannot\EntityImport\Importer'
					|| in_array(
						'HeimrichHannot\EntityImport\Importer',
						\HeimrichHannot\HastePlus\Classes::getParentClasses($strName)
					))
			) {
				$arrOptions[$strName] = $strName;
			}
		}

		sort($arrOptions);

		return $arrOptions;
	}

}
