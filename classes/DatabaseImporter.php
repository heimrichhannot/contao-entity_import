<?php

namespace HeimrichHannot\EntityImport;

class DatabaseImporter extends Importer
{

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

		// special handling for creating products via DcaManager
		while ($this->objItems->next()) {
			$arrItem = $this->createObjectFromMapping($this->objItems);
			$this->createImportMessage($arrItem);
		}

		return true;
	}

	protected function createObjectFromMapping($objSourceItem)
	{
		$objDatabase = \Database::getInstance();
		\Controller::loadDataContainer($this->dbTargetTable);

		$dca = $GLOBALS['TL_DCA'][$this->dbTargetTable];
		$t = $this->dbTargetTable;

		$arrItem = array();

		foreach ($this->arrMapping as $key => $col) {
			$value = $this->setValueByType($objSourceItem->{$key}, $dca['fields'][$key]);
			$this->setObjectValueFromMapping($arrItem, $value, $key);

			if ($value === null) {
				unset($arrItem[$key]);
				continue;
			}
		}

		$strQuery = "INSERT INTO $t (" . implode(',', array_keys($arrItem)) . ") VALUES(" . implode(',', array_map(function($val) { return "'" . $val . "'"; }, array_values($arrItem))) . ")";
		$objDatabase->execute($strQuery);

		// do after item has been created,
		$this->runAfterSaving($arrItem, $objSourceItem);

		return $arrItem;
	}

}