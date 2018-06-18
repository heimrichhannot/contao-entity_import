<?php

namespace HeimrichHannot\EntityImport\Importer;

use Haste\IO\Reader\CsvReader;
use HeimrichHannot\Haste\Util\Files;
use HeimrichHannot\EntityImport\EntityImportModel;

class CsvImporter extends DatabaseImporter
{
    protected $arrItems = [];

    public function __construct($objModel)
    {
        if ($objModel instanceof \Model)
        {
            $this->objModel = $objModel;
        }
        elseif ($objModel instanceof \Model\Collection)
        {
            $this->objModel = $objModel->current();
        }

        \Backend::__construct();

        if ($objModel->purgeBeforeImport && !$this->dryRun)
        {
            $this->purgeBeforeImport($objModel);
        }

        $this->arrData        = $objModel->row();
        $this->objParentModel = EntityImportModel::findByPk($this->objModel->pid);
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

        if (empty($this->arrItems))
        {
            return false;
        }

        foreach ($this->arrItems as $arrItem)
        {
            $arrItem = $this->createObjectFromMapping($arrItem);
            $this->createImportMessage($arrItem);
        }

        return true;
    }

    protected function collectItems()
    {
        if ($strSourceFile = Files::getPathFromUuid($this->sourceFile))
        {
            $objCsv = new CsvReader($strSourceFile);
            $objCsv->setDelimiter($this->delimiter);
            $objCsv->setEnclosure($this->enclosure);
            $objCsv->rewind();
            $objCsv->next();

            while ($arrCurrent = $objCsv->current())
            {
                $this->arrItems[] = $arrCurrent;
                $objCsv->next();
            }
        }
    }

    protected function getFieldMapping() {
        return deserialize($this->fileFieldMapping, true);
    }

    protected function createObjectFromMapping($arrSourceItem, $strClass = null)
    {
        $objDatabase = \Database::getInstance();
        \Controller::loadDataContainer($this->dbTargetTable);

        $t = $this->dbTargetTable;

        $arrItem = [];

        foreach (deserialize($this->fileFieldMapping, true) as $arrConfig)
        {
            if ($arrConfig['type'] == 'source')
            {
                $varValue = $arrSourceItem[$arrConfig['source'] - 1];
            }
            else
            {
                if ($arrConfig['type'] == 'value' && !empty($arrConfig['value']))
                {
                    $varValue = $arrConfig['value'];
                }
            }

            if ($varValue)
            {
                $varValue = $arrConfig['transformToArray'] ? serialize(explode($this->arrayDelimiter, $varValue)) : $varValue;
            }

            $this->setObjectValueFromMapping($arrItem, $varValue, $arrConfig['target']);

            if ($varValue === null)
            {
                unset($arrItem[$arrConfig['target']]);
                continue;
            }
        }

        if (!$this->dryRun)
        {
            if (!$this->skipInsertion)
            {
                if ($this->addMerge && null !== ($existingInstance = $this->findExistingModelInstanceForMerge($arrSourceItem)))
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
            $this->runAfterSaving($arrItem, $arrSourceItem);

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

