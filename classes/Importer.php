<?php

namespace HeimrichHannot\EntityImport;

class Importer extends \Backend
{
	protected $objModel;

	protected $objParentModel;

	protected $objItems;

	protected $arrData = array();

	protected $arrMapping = array();

	protected $arrNamedMapping = array();

	protected $Database;

	protected $arrDbSourceFields = array();

	protected $arrDbTargetFields = array();

	public function __construct($objModel)
	{
		if ($objModel instanceof \Model) {
			$this->objModel = $objModel;
		} elseif ($objModel instanceof \Model\Collection) {
			$this->objModel = $objModel->current();
		}

		parent::__construct();

		$this->arrData = $objModel->row();
		$this->objParentModel = EntityImportModel::findByPk($this->objModel->pid);
		$this->Database = Database::getInstance($this->objParentModel->row());
		$this->arrDbSourceFields = $this->Database->listFields($this->dbSourceTable);
		$this->arrDbTargetFields = \Database::getInstance()->listFields($this->dbTargetTable);

		$this->arrMapping = $this->getFieldsMapping();

		$arrNamedMapping = $this->arrMapping;

		// name fields
		array_walk($arrNamedMapping, function (&$value, $index) {
			$value = $value . ' as ' . $index;
		});

		$this->arrNamedMapping = $arrNamedMapping;
	}

	protected function getFieldMappingDbValue($arrSourceConfig, $arrTargetConfig)
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

		return $strValue;
	}

	protected function getTargetDbConfig($strName)
	{
		foreach ($this->arrDbTargetFields as $arrField) {
			if ($strName == $arrField['name']) {
				return $arrField;
			}
		}

		return false;
	}

	protected function getSourceDbConfig($strName)
	{
		foreach ($this->arrDbSourceFields as $arrField) {
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
	public function run()
	{
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

		return true;
	}

	protected function createObjectFromMapping($objSourceItem, $strClass)
	{
		$objItem = new $strClass();

		\Controller::loadDataContainer($this->dbTargetTable);

		$dca = $GLOBALS['TL_DCA'][$this->dbTargetTable];

		foreach ($this->arrMapping as $key => $col) {
			$value = $this->setValueByType($objSourceItem->{$key}, $dca['fields'][$key]);
			$arrCreateAfterSaving = array();
			$this->setObjectValueFromMapping($objItem, $value, $key, $arrCreateAfterSaving);

			if ($value === null) {
				continue;
			}

			$objItem->save();
		}

		// do after item has been created,
		$this->runAfterSaving($objItem, $objSourceItem);

		return $objItem;
	}

	protected function setValueByType($varValue, $arrData)
	{
		switch ($arrData['inputType']) {
			case 'fileTree':
				if ($arrData['eval']['filesOnly']) {
					$varValue = $this->createSingleFile($varValue);
				}

				// TODO: multiple files
				break;
		}

		return $varValue;
	}

	protected function createSingleFile($varValue)
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

		$objFile = new \File($strRelFile);
		$objFile->copyTo($objTargetDir->path . '/' . $objFile->name);

		$objModel = $objFile->getModel();
		return $objModel->uuid;
	}

	protected function setObjectValueFromMapping(&$objItem, $value, $key)
	{
		// negate the value
		if (substr($key, 0, 1) == '!') {
			$key = preg_replace('/!/', '', $key, 1);

			if (is_array($objItem))
				$objItem[$key] = !$value;
			else
				$objItem->{$key} = !$value;

			return $objItem;
		}

		// fill multiple fields with one value
		$multipleKeys = trimsplit(',', $key);
		if (!empty($multipleKeys)) {
			foreach ($multipleKeys as $subKey) {
				if (is_array($objItem))
					$objItem[$subKey] = $value;
				else
					$objItem->{$subKey} = $value;
			}
			return $objItem;
		}

		if (is_array($objItem))
			$objItem[$key] = $value;
		else
			$objItem->$key = $value;
	}

	protected function collectItems()
	{
		$t = $this->dbSourceTable;

		$strQuery = "SELECT " . implode(', ', $this->arrNamedMapping) . " FROM $t";

		if ($this->whereClause) {
			$strQuery .= " WHERE " . $this->whereClause;
		}

		if ($this->useTimeInterval)
		{
			$intStart = intval($this->start ? $this->start : 0);
			$intEnd = intval($this->end ? $this->end : 2145913200);

			$strDateCol = $this->arrMapping['date'];
			$strQuery .= ($this->whereClause ? " AND " : " WHERE ") . "(($strDateCol>=$intStart AND $strDateCol<=$intEnd) OR ($strDateCol>=$intStart AND $strDateCol<=$intEnd) OR ($strDateCol<=$intStart AND $strDateCol>=$intEnd))";
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
	protected function getFieldsMapping()
	{
		$arrMap = array();

		$this->dbFieldMapping = deserialize($this->dbFieldMapping, true);

		foreach ($this->dbFieldMapping as $arrConfig) {
			if ($arrConfig['type'] == 'source') {
				$arrSrcDbConfig               = $this->getSourceDbConfig($arrConfig['source']);
				$arrTargetDbConfig            = $this->getTargetDbConfig($arrConfig['target']);
				$arrMap[$arrConfig['target']] = $this->getFieldMappingDbValue($arrSrcDbConfig, $arrTargetDbConfig);
			} else {
				if ($arrConfig['type'] == 'value' && !empty($arrConfig['value'])) {
					$arrMap[$arrConfig['target']] = (is_string($arrConfig['value']) ? '"' . $arrConfig['value'] . '"' : $arrConfig['value']);
				}
			}
		}

		return $arrMap;
	}


	protected function runAfterSaving(&$objItem, $objTypoItem) {}

	protected function createImportMessage($objItem) {}
}