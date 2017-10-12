<?php

namespace HeimrichHannot\EntityImport\Importer;

use Wa72\HtmlPageDom\HtmlPageCrawler;

class NewsImporter extends Importer
{
    protected function runAfterSaving(&$objItem, $objSourceItem)
    {
        $objItem->alias    = $this->generateAlias($objItem->alias ? $objItem->alias : $objItem->headline, $objItem);
        $objItem->source   = 'default';
        $objItem->floating = 'above';

        $this->createContentElements($objItem);
        $this->createEnclosures($objItem);

        // news_categories module support
        if (in_array('news_categories', \ModuleLoader::getActive())) {
            $this->setCategories($objItem, $objSourceItem);
        }

        if ($objItem->teaser) {
            $objItem->teaser = $this->cleanHtml($objItem->teaser, $objItem);
        }

        if ($objItem->teaser_short) {
            $objItem->teaser_short = $this->cleanHtml($objItem->teaser_short, $objItem);
        }

        if ($objItem->infoBox_text) {
            $objItem->infoBox_text = $this->cleanHtml($objItem->infoBox_text, $objItem);
        }

        $objItem->save();
    }

    public function generateAlias($varValue, $objItem)
    {
        $t = $this->dbTargetTable;

        $varValue = standardize(\StringUtil::restoreBasicEntities($varValue));

        $objAlias = \Database::getInstance()->prepare("SELECT id FROM $t WHERE alias=? AND id != ?")->execute($varValue, $objItem->id);

        // Add ID to alias
        if ($objAlias->numRows > 0) {
            $varValue .= '-' . $objItem->id;
        }

        return $varValue;
    }


    protected function createEnclosures(&$objItem)
    {
        if ($this->sourceDir === null || $this->targetDir === null) {
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

        $arrSource = deserialize($objItem->enclosure, true);
        $arrTarget = [];

        foreach ($arrSource as $strFile) {

            if (\Validator::isUuid($strFile)) {
                continue;
            }

            $strRelFile = $objSourceDir->path . '/' . ltrim($strFile, '/');

            if (is_dir(TL_ROOT . '/' . $strRelFile) || !file_exists(TL_ROOT . '/' . $strRelFile)) {
                continue;
            }

            $objFile = new \File($strRelFile);
            $objFile->copyTo($objTargetDir->path . '/' . $objFile->name);

            $objModel    = $objFile->getModel();
            $arrTarget[] = $objModel->uuid;
        }

        if (!empty($arrTarget)) {
            $objItem->addEnclosure = true;
            $objItem->enclosure    = $arrTarget;
        }
    }

    protected function setCategories(&$objItem, $objSourceItem)
    {
        $arrCatContao = deserialize($this->catContao);

        if (empty($arrCatContao)) {
            return false;
        }

        $arrCatContaoIds = array_values($arrCatContao);

        $arrCategories = [];

        foreach ($arrCatContao as $id) {
            \Database::getInstance()->prepare('INSERT INTO tl_news_categories (category_id, news_id) VALUES (?,?)')->execute($id, $objItem->id);
        }

        $objItem->categories = $this->catContao;

        return true;
    }

    protected function createImportMessage($objItem)
    {
        $strMessage = $GLOBALS['TL_LANG']['tl_entity_import_config']['newsImport'];

        if ($this->dryRun) {
            $strMessage = $GLOBALS['TL_LANG']['tl_entity_import_config']['newsDry'];
        }

        \Message::addConfirmation(sprintf($strMessage, $objItem->headline));
    }
}