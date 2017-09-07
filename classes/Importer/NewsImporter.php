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

        if ($objItem->info_box_text_text) {
            $objItem->info_box_text_text = $this->cleanHtml($objItem->info_box_text_text, $objItem);
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

    protected function createContentElements(&$objItem)
    {
        if ($objItem->tl_content) {

            $objContent          = new \ContentModel();
            $objContent->text    = $this->cleanHtml($objItem->tl_content, $objItem);
            $objContent->ptable  = $this->dbTargetTable;
            $objContent->pid     = $objItem->id;
            $objContent->sorting = 16;
            $objContent->tstamp  = time();
            $objContent->type    = 'text';
            $objContent->save();
        }
    }

    /**
     * Parse typo 3 html
     * @param string $html The dirty html
     * @param \Model $objItem The current contao model
     * @return string $html The clean parsed html
     */
    protected function cleanHtml($html, $objItem)
    {
        // need to wrap <p> around text for contao
        $tidyConfig = [
            'enclose-text'                => true,
            'drop-font-tags'              => true,
            'drop-proprietary-attributes' => true,
            'quote-ampersand'             => true,
            'clean'                       => false,
            'wrap-attributes'             => false,
            'wrap'                        => 500,
        ];

        $bodyText = preg_replace("#<(\/?)(div>)#is", "<$1p>", $html); // div to p

        $bodyText = $this->nl2p($bodyText);

        $bodyText = '<!DOCTYPE html><head><title></title></head><body>' . $bodyText . '</body></html>';

        $bodyText = $this->prepareHtml($bodyText, $objItem);

        $tidy = new \tidy();
        $tidy->parseString($bodyText, $tidyConfig, $GLOBALS['TL_CONFIG']['dbCharset']);
        $body = $tidy->body();

        $html = trim(str_replace(['<body>', '</body>'], '', $body));
        $html = urldecode($html); // decode, otherwise link and email regex wont work
        $html = preg_replace("/<img[^>]+\>/i", "", $html); // strip images
        // remove inline styles
        $html = preg_replace('#(<[a-z ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $html);
        // remove white space from empty tags
        $html = preg_replace('#(<[a-z]*)(\s+)>#', '$1>', $html);
        // create links from text
        $html = preg_replace('!(\s|^)((https?://|www\.)+[a-z0-9_./?=&-]+)!i', ' <a href="http://$2" target="_blank">$2</a>', $html);
        // replace <b> by <strong>
        $html = preg_replace('!<b(.*?)>(.*?)</b>!i', '<strong>$2</strong>', $html);
        // replace plain email text with inserttags
        $html = preg_replace('/([A-Z0-9._%+-]+)@([A-Z0-9.-]+)\.([A-Z]{2,4})(\((.+?)\))?(?![^<]*>)(?![^>]*<)/i', "{{email::$1@$2.$3}}", $html);

        // replace email links with inserttags
        $html =
            preg_replace('/<a.*href=[\'|"]mailto:([A-Z0-9._%+-]+)@([A-Z0-9.-]+)\.([A-Z]{2,4})(\((.+?)\))?[\'|"].*>(.*)<\/a>/i', "{{email::$1@$2.$3}}", $html);

        // strip not allowed tags
        $html = strip_tags($html, \Config::get('allowedTags'));

        $html = $this->stripAttributes($html, ['style', 'class', 'id']);

        return $html;
    }

    /**
     * Prepare typo3 html for contao
     * @param string $html
     * @param \Model $objItem The current contao model
     * @return string The adjusted html
     */
    protected function prepareHtml($html, $objItem)
    {
        return $html;
    }

    public function nl2p($string)
    {
        $string = preg_replace('#<br\s*/?>#i', "\n", $string); // replace br with new line

        $paragraphs = '';

        foreach (explode("\n", $string) as $line) {
            if (trim($line)) {
                $paragraphs .= '<p>' . $line . '</p>';
            }
        }

        return $paragraphs;
    }

    public function stripAttributes($html, $attribs)
    {
        $c = new HtmlPageCrawler($html);

        $c->filter('*')->each(
            function (HtmlPageCrawler $node) use ($attribs) {
                foreach ($attribs as $attrib) {
                    $node->removeAttr($attrib);
                }
            }
        );

        return $c->saveHTML();
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