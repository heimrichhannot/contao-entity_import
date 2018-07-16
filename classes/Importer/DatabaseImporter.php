<?php

namespace HeimrichHannot\EntityImport\Importer;

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

        if ($this->objItems === null)
        {
            return false;
        }

        // special handling for creating products via DcaManager
        while ($this->objItems->next())
        {
            $arrItem = $this->createObjectFromMapping($this->objItems);
            $this->createImportMessage($arrItem);
        }

        // reset the iterator
        $this->objItems->reset();
        $this->runAfterComplete($this->objItems);

        return true;
    }

    protected function createObjectFromMapping($objSourceItem, $strClass = null)
    {
        $objDatabase = \Database::getInstance();
        \Controller::loadDataContainer($this->dbTargetTable);

        $dca = $GLOBALS['TL_DCA'][$this->dbTargetTable];
        $t   = $this->dbTargetTable;

        $arrItem = [];

        foreach ($this->arrMapping as $key => $col)
        {
            $value = $this->setValueByType($objSourceItem->{$key}, $dca['fields'][$key], $arrItem, $objSourceItem);
            $this->setObjectValueFromMapping($arrItem, $value, $key);

            if ($value === null)
            {
                unset($arrItem[$key]);
                continue;
            }
        }

        if (!$this->dryRun)
        {
            if (!$this->skipInsertion)
            {
                if ($this->addMerge && null !== ($existingInstance = $this->findExistingModelInstanceForMerge($objSourceItem->row())))
                {
                    $queryValues = [];

                    foreach ($arrItem as $field => $value)
                    {
                        $queryValues[] = $field . "='" . $value . "'";
                    }

                    $objDatabase->execute("UPDATE $t SET " . implode(',', $queryValues) . " WHERE $t.id=" . $existingInstance->id);

                    $arrItem['id'] = $existingInstance->id;
                }
                else
                {
                    $strQuery = "INSERT INTO $t (" . implode(',', array_keys($arrItem)) . ") VALUES(" . implode(
                            ',',
                            array_map(function ($val) { return "'" . str_replace("'", "''", $val) . "'"; }, array_values($arrItem))
                        ) . ")";

                    $arrItem['id'] = $objDatabase->execute($strQuery)->insertId;
                }
            }

            // do after item has been created,
            $this->runAfterSaving($arrItem, $objSourceItem);

            // save updates
            if (!$this->skipUpdateAfterSave)
            {
                $arrTargetItemPrepared = [];
                foreach ($arrItem as $strKey => $strVal)
                {
                    if ($strKey == 'id')
                    {
                        continue;
                    }

                    $strVal = str_replace("'", "''", $strVal);

                    $arrTargetItemPrepared[] = "$this->dbTargetTable.$strKey='$strVal'";
                }

                // update all values to the db
                $strQuery = "UPDATE $this->dbTargetTable SET " . implode(',', $arrTargetItemPrepared) . " WHERE id=" . $arrItem['id'];
                \Database::getInstance()->execute($strQuery);
            }
        }

        return $arrItem;
    }

}

