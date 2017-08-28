<?php

namespace HeimrichHannot\EntityImport\Importer;

use Wa72\HtmlPageDom\HtmlPageCrawler;

class NewsImporter extends Importer
{
    public function convert_external_link_tags($html)
    {
        $pattern     = '!<link\s(.+)\s_blank external-link-new-window\s(".*")?><img(.+) \/>(.+)<\/link>!U';
        $replacement = '<a href="$1" target="_blank" title=$2>$4</a>';
        preg_match_all($pattern, $html, $matches, PREG_PATTERN_ORDER);

        return preg_replace($pattern, $replacement, $html);
    }

    public function convert_internal_link_tags($html)
    {
        $pattern     = '!<link\s(\d+)\s-\sinternal-link\s(".*")?>((https?://|www\.)+[a-z0-9_./?=&-]+)<\/link>!U';
        $replacement = '<a href="http://$3" title=$2>$3</a>';
        preg_match_all($pattern, $html, $matches, PREG_PATTERN_ORDER);

        return preg_replace($pattern, $replacement, $html);
    }

    protected function runAfterSaving(&$objItem, $objSourceItem)
    {
        $objItem->alias    = $this->generateAlias($objItem->alias ? $objItem->alias : $objItem->headline, $objItem);
        $objItem->source   = 'default';
        $objItem->floating = 'above';

        $this->createContentElements($objItem);
        $this->createEnclosures($objItem);

        // news_categories module support
        if (in_array('news_categories', \ModuleLoader::getActive()))
        {
            $this->setCategories($objItem, $objSourceItem);
        }

	// wrap teaser inside paragraph
        if (preg_match('/^<p/', $objItem->teaser) === 0) {
            $objItem->teaser = "<p>" . $objItem->teaser . "</p>";
        }

        $objItem->save();
    }

    public function generateAlias($varValue, $objItem)
    {
        $t = $this->dbTargetTable;

        $varValue = standardize(\StringUtil::restoreBasicEntities($varValue));

        $objAlias = \Database::getInstance()->prepare("SELECT id FROM $t WHERE alias=? AND id != ?")->execute($varValue, $objItem->id);

        // Add ID to alias
        if ($objAlias->numRows > 0)
        {
            $varValue .= '-' . $objItem->id;
        }

        return $varValue;
    }

    protected function createContentElements(&$objItem)
    {
        if ($objItem->tl_content)
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

            $bodyText = '<!DOCTYPE html><head><title></title></head><body>' . $objItem->tl_content . '</body></html>';

//			$bodyText = $this->convert_external_link_tags($bodyText);
//			$bodyText = $this->convert_internal_link_tags($bodyText);
            $bodyText = $this->nl2p($bodyText);

            $tidy = new \tidy();
            $tidy->parseString($bodyText, $tidyConfig, $GLOBALS['TL_CONFIG']['dbCharset']);
            $body = $tidy->body();

            $objContent       = new \ContentModel();
            $objContent->text = urldecode($objContent->text); // decode, otherwise link and email regex wont work
            $objContent->text = trim(str_replace(['<body>', '</body>'], '', $body));
            $objContent->text = preg_replace("/<img[^>]+\>/i", "", $objContent->text); // strip images
            // remove inline styles
            $objContent->text = preg_replace('#(<[a-z ]*)(style=("|\')(.*?)("|\'))([a-z ]*>)#', '\\1\\6', $objContent->text);
            // remove white space from empty tags
            $objContent->text = preg_replace('#(<[a-z]*)(\s+)>#', '$1>', $objContent->text);
            // create links from text
            $objContent->text = preg_replace('!(\s|^)((https?://|www\.)+[a-z0-9_./?=&-]+)!i', ' <a href="http://$2" target="_blank">$2</a>', $objContent->text);
            // replace <b> by <strong>
            $objContent->text = preg_replace('!<b(.*?)>(.*?)</b>!i', '<strong>$2</strong>', $objContent->text);
            // replace plain email text with inserttags
            $objContent->text = preg_replace('/([A-Z0-9._%+-]+)@([A-Z0-9.-]+)\.([A-Z]{2,4})(\((.+?)\))?(?![^<]*>)(?![^>]*<)/i', "{{email::$1@$2.$3}}", $objContent->text);

            // replace email links with inserttags
            $objContent->text =
                preg_replace('/<a.*href=[\'|"]mailto:([A-Z0-9._%+-]+)@([A-Z0-9.-]+)\.([A-Z]{2,4})(\((.+?)\))?[\'|"].*>(.*)<\/a>/i', "{{email::$1@$2.$3}}", $objContent->text);

            // strip not allowed tags
            $objContent->text = strip_tags($objContent->text, \Config::get('allowedTags'));

            $objContent->text = $this->stripAttributes($objContent->text, ['style', 'class', 'id']);

            $objContent->ptable  = $this->dbTargetTable;
            $objContent->pid     = $objItem->id;
            $objContent->sorting = 16;
            $objContent->tstamp  = time();
            $objContent->type    = 'text';
            $objContent->save();
        }
    }

    public function nl2p($string)
    {
        $string = preg_replace('#<br\s*/?>#i', "\n", $string); // replace br with new line

        $paragraphs = '';

        foreach (explode("\n", $string) as $line)
        {
            if (trim($line))
            {
                $paragraphs .= '<p>' . $line . '</p>';
            }
        }

        return $paragraphs;
    }

    public function stripAttributes($html, $attribs)
    {
        $c = new HtmlPageCrawler($html);

        $c->filter('*')->each(
            function (HtmlPageCrawler $node) use ($attribs)
            {
                foreach ($attribs as $attrib)
                {
                    $node->removeAttr($attrib);
                }
            }
        );

        return $c->saveHTML();
    }

    protected function createEnclosures(&$objItem)
    {
        if ($this->sourceDir === null || $this->targetDir === null)
        {
            return false;
        }

        $objSourceDir = \FilesModel::findByUuid($this->sourceDir);

        if ($objSourceDir === null)
        {
            return false;
        }

        $objTargetDir = \FilesModel::findByUuid($this->targetDir);

        if ($objTargetDir === null)
        {
            return false;
        }

        $arrSource = deserialize($objItem->enclosure, true);
        $arrTarget = [];

        foreach ($arrSource as $strFile)
        {

            if (\Validator::isUuid($strFile))
            {
                continue;
            }

            $strRelFile = $objSourceDir->path . '/' . ltrim($strFile, '/');

            if (is_dir(TL_ROOT . '/' . $strRelFile) || !file_exists(TL_ROOT . '/' . $strRelFile))
            {
                continue;
            }

            $objFile = new \File($strRelFile);
            $objFile->copyTo($objTargetDir->path . '/' . $objFile->name);

            $objModel    = $objFile->getModel();
            $arrTarget[] = $objModel->uuid;
        }

        if (!empty($arrTarget))
        {
            $objItem->addEnclosure = true;
            $objItem->enclosure    = $arrTarget;
        }
    }

    protected function setCategories(&$objItem, $objSourceItem)
    {
        $arrCatContao = deserialize($this->catContao);

        if (empty($arrCatContao))
        {
            return false;
        }

        $arrCatContaoIds = array_values($arrCatContao);

        $arrCategories = [];

        foreach ($arrCatContao as $id)
        {
            \Database::getInstance()->prepare('INSERT INTO tl_news_categories (category_id, news_id) VALUES (?,?)')->execute($id, $objItem->id);
        }

        $objItem->categories = $this->catContao;

        return true;
    }

    protected function createImportMessage($objItem)
    {
        $strMessage = $GLOBALS['TL_LANG']['tl_entity_import_config']['newsImport'];

        if ($this->dryRun)
        {
            $strMessage = $GLOBALS['TL_LANG']['tl_entity_import_config']['newsDry'];
        }

        \Message::addConfirmation(sprintf($strMessage, $objItem->headline));
    }
}
