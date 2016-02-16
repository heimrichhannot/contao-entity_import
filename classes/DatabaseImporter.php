<?php

namespace HeimrichHannot\EntityImport;

class DatabaseImporter extends Importer
{

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
			$value = $this->setValueByType($objSourceItem->{$key}, $dca['fields'][$key], $arrItem, $objSourceItem);
			$this->setObjectValueFromMapping($arrItem, $value, $key);

			if ($value === null) {
				unset($arrItem[$key]);
				continue;
			}
		}

		if (!$this->dryRun)
		{
			if (!$this->skipInsertion)
			{
				$strQuery = "INSERT INTO $t (" . implode(',', array_keys($arrItem)) . ") VALUES(" . implode(',', array_map(function($val) { return "'" . str_replace("'", "''", $val) . "'"; }, array_values($arrItem))) . ")";

				$arrItem['id'] = $objDatabase->execute($strQuery)->insertId;
			}

			// do after item has been created,
			$this->runAfterSaving($arrItem, $objSourceItem);
		}

		return $arrItem;
	}

}

