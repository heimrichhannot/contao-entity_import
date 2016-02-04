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

		$strClass = \Model::getClassFromTable($this->dbTargetTable);

		if (!class_exists($strClass)) {
			return false;
		}

		// special handling for creating products via DcaManager
		while ($this->objItems->next()) {
			$arrItem = $this->createObjectFromMapping($this->objItems, $strClass);
			$this->createImportMessage($arrItem);
		}

		return true;
	}

	protected function createObjectFromMapping($objSourceItem, $strClass)
	{
		$objItem = new $strClass();

		\Controller::loadDataContainer($this->dbTargetTable);

		$dca = $GLOBALS['TL_DCA'][$this->dbTargetTable];

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


			// HOOK: run after saving callback
			if (isset($GLOBALS['TL_HOOKS']['entityImportRunAfterSaving']) && is_array($GLOBALS['TL_HOOKS']['entityImportRunAfterSaving']))
			{
				foreach ($GLOBALS['TL_HOOKS']['entityImportRunAfterSaving'] as $callback)
				{
					$this->import($callback[0]);
					$objItem = $this->$callback[0]->$callback[1]($objItem, $objSourceItem, $this);
				}
			}
		}

		return $objItem;
	}

}

