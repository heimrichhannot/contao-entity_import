<?php

namespace HeimrichHannot\EntityImport;

class Importer extends \Backend
{
	protected $objModel;

	protected $objParentModel;

	protected $objItems;

	protected $arrData = array();

	protected $arrMapping = array();

	protected $arrFileMapping = array();

	protected $arrNamedMapping = array();

	protected $Database;

	protected $arrDbSourceFields = array();

	protected $arrDbTargetFields = array();

	protected $arrDbFileFields = array();

	protected $dryRun = false;

	public function __construct($objModel)
	{
		if ($objModel instanceof \Model) {
			$this->objModel = $objModel;
		} elseif ($objModel instanceof \Model\Collection) {
			$this->objModel = $objModel->current();
		}

		parent::__construct();

		if ($objModel->purgeBeforeImport && !$this->dryRun) {
			$this->purgeBeforeImport($objModel);
		}

		$this->arrData           = $objModel->row();
		$this->objParentModel    = EntityImportModel::findByPk($this->objModel->pid);
		$this->Database          = Database::getInstance($this->objParentModel->row());
		$this->arrDbSourceFields = $this->Database->listFields($this->dbSourceTable);
		$this->arrDbTargetFields = \Database::getInstance()->listFields($this->dbTargetTable);
		$this->arrDbFileFields   = \Database::getInstance()->listFields('tl_files');

		$this->arrMapping     = $this->getFieldsMapping(deserialize($this->dbFieldMapping, true), $this->arrDbSourceFields, $this->arrDbTargetFields);
		$this->arrFileMapping =
			$this->getFieldsMapping(deserialize($this->dbFieldFileMapping, true), $this->arrDbSourceFields, $this->arrDbFileFields);
		
		$arrNamedMapping = $this->arrMapping;

		// name fields
		array_walk(
			$arrNamedMapping,
			function (&$value, $index) {
				$value = $value . ' as ' . $index;
			}
		);

		$this->arrNamedMapping = $arrNamedMapping;
	}

	protected function purgeBeforeImport($objModel)
	{
		$strQuery = 'DELETE FROM ' . $objModel->dbTargetTable .
					($objModel->whereClausePurge ? ' WHERE ' . $objModel->whereClausePurge : '');

		\Database::getInstance()->execute($strQuery);
	}

	protected function getFieldMappingDbValue($arrSourceConfig, $arrTargetConfig, $strForeignKey = '')
	{
		$t = $this->dbSourceTable;

		$strValue = $arrSourceConfig['name'];

		switch ($arrSourceConfig['type']) {
			case 'timestamp':
				if ($arrTargetConfig['type'] == 'int') {
					$strValue = "UNIX_TIMESTAMP($t.$strValue)";
				}
				break;
			default:
				$strValue = $this->dbSourceTable . '.' . $strValue;
		}
		
		if ($strForeignKey != ''
			&& preg_match(
				'#(?<PK>.*)=(?<TABLE>.*)[.](?<COLUMN>.*)#',
				\StringUtil::decodeEntities($strForeignKey),
				$arrForeignKey
			)
		) {
			if (isset($arrForeignKey['PK']) && ($arrForeignKey['TABLE']) && ($arrForeignKey['COLUMN'])) {
				$strValue =
					sprintf("(SELECT %s FROM %s WHERE %s=%s)", $arrForeignKey['COLUMN'], $arrForeignKey['TABLE'], $arrForeignKey['PK'], $strValue);
			}
		}
		
		return $strValue;
	}

	protected function getDbConfig($strName, array $arrFields)
	{
		foreach ($arrFields as $arrField) {
			if ($strName == $arrField['name']) {
				return $arrField;
			}
		}

		return false;
	}

	/**
	 * run the importer
	 *
	 * @return bool
	 */
	public function run($dry = false)
	{
		$this->dryRun = $dry;

		$this->collectItems();

		if ($this->objItems === null) {
			return false;
		}

		$strClass = \Model::getClassFromTable($this->dbTargetTable);

		if (!class_exists($strClass)) {
			return false;
		}

		while ($this->objItems->next()) {
			$objItem = $this->createObjectFromMapping($this->objItems, $strClass);
			$this->createImportMessage($objItem);
		}

		// reset the iterator
		$this->objItems->reset();
		$this->runAfterComplete($this->objItems);

		return true;
	}

	protected function createObjectFromMapping($objSourceItem, $strClass)
	{
		$objItem = new $strClass();

		\Controller::loadDataContainer($this->dbTargetTable);

		$dca = $GLOBALS['TL_DCA'][$this->dbTargetTable];
		
		// update existing items
		if (in_array($objItem->getPk(), array_keys($this->arrMapping)))
		{
			$objUpdateItem = $strClass::findByPk($objSourceItem->{$objItem->getPk()});

			if($objUpdateItem !== null)
			{
				$objItem = $objUpdateItem;
			}
		}
		
		foreach ($this->arrMapping as $key => $col)
		{
			$value                = $this->setValueByType($objSourceItem->{$key}, $dca['fields'][$key], $objItem, $objSourceItem);
			$this->setObjectValueFromMapping($objItem, $value, $key);

			if ($value === null) {
				continue;
			}

			// do not save in dry run
			if ($this->dryRun) {
				continue;
			}

			$objItem->save();
		}


		// do after item has been created, no in dry mode
		if (!$this->dryRun)
		{
			$this->runAfterSaving($objItem, $objSourceItem);
		}

		return $objItem;
	}

	protected function setValueByType($varValue, $arrData, $objItem, $objSourceItem)
	{
		switch ($arrData['inputType']) {
			case 'fileTree':
				if ($arrData['eval']['filesOnly']) {
					if(!$this->dryRun)
					{
						$varValue = deserialize($varValue);

						if(is_array($varValue))
						{
							$varValue = $this->createMultipleFiles($varValue, $arrData, $objItem, $objSourceItem);
							break;
						}

						$varValue = $this->createSingleFile($varValue, $arrData, $objItem, $objSourceItem);
					}
				}

				// TODO: multiple files
				break;
		}

		return $varValue;
	}

	protected function createMultipleFiles(array $arrFiles, $arrData, $objItem, $objSourceItem)
	{
		$arrReturn = array();

		foreach($arrFiles as $varValue)
		{
			$uuid = $this->createSingleFile($varValue, $arrData, $objItem, $objSourceItem);

			if(!\Validator::isUuid($uuid)) continue;

			$arrReturn[] = $uuid;
		}

		return $arrReturn;
	}

	protected function createSingleFile($varValue, $arrData, $objItem, $objSourceItem)
	{
		if ($this->sourceDir === null || $this->targetDir === null || $varValue == '') {
			return false;
		}

		$objSourceDir = \FilesModel::findByUuid($this->sourceDir);

		if ($objSourceDir === null) {
			return false;
		}

		$objTargetDir = \FilesModel::findByUuid($this->targetDir);

		if ($objTargetDir === null) {
			return false;
		}

		$strRelFile = $objSourceDir->path . '/' . ltrim($varValue, '/');
		
		if (is_dir(TL_ROOT . '/' . $strRelFile) || !file_exists(TL_ROOT . '/' . $strRelFile)) {
			return null;
		}

		$strTargetFile = $objTargetDir->path . '/' . basename($strRelFile);

		$objFile = new \File($strRelFile, false);

		$blnCopy = true;

		if(file_exists(TL_ROOT . '/' . $strTargetFile))
		{
			$blnCopy = false;

			$objTargetFile = new \File($strTargetFile, false);

			$blnCopy = ($objTargetFile->size != $objFile->size || $objTargetFile->mtime < $objFile->mtime);

			if(!$blnCopy)
			{
				$objFile = $objTargetFile;
			}
		}

		if($blnCopy)
		{
			$objFile->copyTo($strTargetFile);
		}

		$objModel = $objFile->getModel();

		if($objModel !== null)
		{
			\Message::addConfirmation('<strong>Copied file </strong><br/>from:' . $strRelFile . ' <br /> to: ' . $objTargetDir->path . '/' . $objFile->name);
		}

		if (!is_array($this->arrFileMapping) || empty($this->arrFileMapping))
		{
			return $objModel->uuid;
		}

		// set additional file fields from source
		foreach ($this->arrFileMapping as $key => $col)
		{
			$col = str_replace($this->dbSourceTable . '.', '', $col);

			$value = $objSourceItem->{$col};

			$this->setObjectValueFromMapping($objModel, $value, $key);

			if ($value === null) {
				continue;
			}

			// do not save in dry run
			if ($this->dryRun) {
				continue;
			}

			$objModel->save();
		}


		return $objModel->uuid;
	}

	protected function setObjectValueFromMapping(&$objItem, $value, $key)
	{
		// negate the value
		if (substr($key, 0, 1) == '!') {
			$key = preg_replace('/!/', '', $key, 1);

			if (is_array($objItem)) {
				$objItem[$key] = !$value;
			} else {
				$objItem->{$key} = !$value;
			}

			return $objItem;
		}

		// fill multiple fields with one value
		$multipleKeys = trimsplit(',', $key);
		if (!empty($multipleKeys)) {
			foreach ($multipleKeys as $subKey) {
				if (is_array($objItem)) {
					$objItem[$subKey] = $value;
				} else {
					$objItem->{$subKey} = $value;
				}
			}

			return $objItem;
		}

		if (is_array($objItem)) {
			$objItem[$key] = $value;
		} else {
			$objItem->$key = $value;
		}
	}

	protected function collectItems()
	{
		$t = $this->dbSourceTable;

		$strQuery = "SELECT *, " . implode(', ', $this->arrNamedMapping) . " FROM $t";

		if ($this->whereClause) {
			$strQuery .= " WHERE " . $this->whereClause;
		}

		if ($this->useTimeInterval) {
			$intStart = intval($this->start ? $this->start : 0);
			$intEnd   = intval($this->end ? $this->end : 2145913200);

			$strDateCol = $this->arrMapping['date'];
			$strQuery .= ($this->whereClause ? " AND " : " WHERE ")
						 . "(($strDateCol>=$intStart AND $strDateCol<=$intEnd) OR ($strDateCol>=$intStart AND $strDateCol<=$intEnd) OR ($strDateCol<=$intStart AND $strDateCol>=$intEnd))";
		}

		$objResult = $this->Database->prepare($strQuery)->execute();

		$this->objItems = $objResult;
	}

	/**
	 * Set an object property
	 *
	 * @param string
	 * @param mixed
	 */
	public function __set($strKey, $varValue)
	{
		$this->arrData[$strKey] = $varValue;
	}


	/**
	 * Return an object property
	 *
	 * @param string
	 *
	 * @return mixed
	 */
	public function __get($strKey)
	{
		if (isset($this->arrData[$strKey])) {
			return $this->arrData[$strKey];
		}

		return parent::__get($strKey);
	}


	/**
	 * Check whether a property is set
	 *
	 * @param string
	 *
	 * @return boolean
	 */
	public function __isset($strKey)
	{
		return isset($this->arrData[$strKey]);
	}


	/**
	 * Return the model
	 *
	 * @return \Model
	 */
	public function getModel()
	{
		return $this->objModel;
	}


	/**
	 * @return Associated Array
	 * Key = Field Name
	 * Value = Contao Field Name
	 */
	protected function getFieldsMapping(array $arrSourceMap, array $arrSourceFields, array $arrTargetFields)
	{
		$arrMap = array();

		foreach ($arrSourceMap as $arrConfig) {
			if ($arrConfig['type'] == 'source' || $arrConfig['type'] == 'foreignKey') {
				$arrSrcDbConfig               = $this->getDbConfig($arrConfig['source'], $arrSourceFields);
				$arrTargetDbConfig            = $this->getDbConfig($arrConfig['target'], $arrTargetFields);
				$arrMap[$arrConfig['target']] =
					$this->getFieldMappingDbValue($arrSrcDbConfig, $arrTargetDbConfig, $arrConfig['type'] == 'foreignKey' ? $arrConfig['value'] : '');
			} else {
				if ($arrConfig['type'] == 'value' && !empty($arrConfig['value'])) {
					$arrMap[$arrConfig['target']] =
						(is_string($arrConfig['value']) ? '"' . addslashes($arrConfig['value']) . '"' : $arrConfig['value']);
				}
			}
		}
		
		ob_start();
		print_r($arrMap);
		print "\n";
		file_put_contents(TL_ROOT . '/debug.txt', ob_get_contents(), FILE_APPEND);
		ob_end_clean();
		
		return $arrMap;
	}


	protected function runAfterSaving(&$objItem, $objTypoItem)
	{
	}

	protected function runAfterComplete($objItems)
	{
	}

	protected function createImportMessage($objItem)
	{
	}
}
