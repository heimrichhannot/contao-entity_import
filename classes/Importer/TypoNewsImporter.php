<?php
/**
 * Contao Open Source CMS
 *
 * Copyright (c) 2017 Heimrich & Hannot GmbH
 *
 * @author  Rico Kaltofen <r.kaltofen@heimrich-hannot.de>
 * @license http://www.gnu.org/licences/lgpl-3.0.html LGPL
 */

namespace HeimrichHannot\EntityImport\Importer;


use HeimrichHannot\Haste\Model\Model;

class TypoNewsImporter extends NewsImporter
{

    protected function runAfterSaving(&$objItem, $objSourceItem)
    {
        parent::runAfterSaving($objItem, $objSourceItem);

        if ($objItem->tags) {
            $this->setTags($objItem, $objSourceItem);
        }

        if ($objItem->addImage) {
            $this->importArticleImage($objItem, $objSourceItem);
        }

        if ($objItem->writers) {
            $this->importWriter($objItem, $objSourceItem);
        }

        if ($objItem->start != '' || $objItem->start != '') {
            $this->setPublished($objItem, $objSourceItem);
        }

        $objItem->save();
    }

    protected function setPublished($objItem, $objSourceItem)
    {
        $objItem->start = !$objItem->start ? '' : $objItem->start;
        $objItem->stop  = !$objItem->stop ? '' : $objItem->stop;

    }

    protected function setTags($objItem, $objSourceItem)
    {
        $tags = $this->Database->prepare("SELECT * FROM tx_news_domain_model_news_tag_mm WHERE uid_local = ? ORDER by sorting")->execute(
            $objSourceItem->uid
        );

        if (!$tags->numRows) {
            $objItem->tags = null;

            return;
        }

        $arrTags = [];

        // always cleanup before
        \Database::getInstance()->prepare('DELETE FROM tl_news_tags WHERE news_id = ?')->execute($objItem->id);

        while ($tags->next()) {
            $arrTags[] = $tags->uid_foreign;
            \Database::getInstance()->prepare('INSERT INTO tl_news_tags (cfg_tag_id, news_id) VALUES (?,?)')->execute($tags->uid_foreign, $objItem->id);
        }

        $objItem->tags = empty($arrTags) ? null : $arrTags;
    }

    protected function setCategories(&$objItem, $objSourceItem)
    {
        $contaoCategories = deserialize($this->catContao);

        if (empty($contaoCategories)) {
            return false;
        }

        $categories = $this->Database->prepare("SELECT * FROM sys_category_record_mm WHERE tablenames = ? and uid_foreign = ? ORDER by sorting_foreign")->execute(
            'tx_news_domain_model_news',
            $objSourceItem->uid
        );

        if (!$categories->numRows) {
            $objItem->categories = null;

            return false;
        }


        $arrCategories = [];

        // always cleanup before
        \Database::getInstance()->prepare('DELETE FROM tl_news_categories WHERE news_id = ?')->execute($objItem->id);

        while ($categories->next()) {
            // category ids from typo3 must match category ids from contao
            if (!in_array($categories->uid_local, $contaoCategories)) {
                continue;
            }

            $arrCategories[] = $categories->uid_local;
            \Database::getInstance()->prepare('INSERT INTO tl_news_categories (category_id, news_id) VALUES (?,?)')->execute($categories->uid_local, $objItem->id);
        }


        $objItem->categories = empty($arrCategories) ? null : $arrCategories;
    }

    protected function importWriter($objItem, $objSourceItem)
    {
        if (($objGroup = \MemberGroupModel::findByName(ENTITY_IMPORT_NEWS_WRITERS_MEMBER_GROUP_NAME)) === null) {
            $objGroup         = Model::setDefaultsFromDca(new \MemberGroupModel());
            $objGroup->tstamp = time();
            $objGroup->name   = ENTITY_IMPORT_NEWS_WRITERS_MEMBER_GROUP_NAME;
            $objGroup->save();
        }

        if ($objSourceItem->author_email == '' || ($objModel = \MemberModel::findByEmail($objSourceItem->author_email)) === null) {
            if (($objModel = \MemberModel::findByLastname($objItem->writers)) !== null) {
                $groups = deserialize($objModel->groups, true);

                if (!in_array($objGroup->id, $groups)) {
                    $objModel = Model::setDefaultsFromDca(new \MemberModel());
                }
            } else {
                $objModel = Model::setDefaultsFromDca(new \MemberModel());
            }
        }

        $objModel->tstamp    = time();
        $objModel->dateAdded = time();
        $objModel->lastname  = $objItem->writers;
        $objModel->email     = $objSourceItem->author_email ?: '';
        $objModel->groups    = serialize([$objGroup->id]);
        $objModel->save();

        $objItem->writers = serialize([$objModel->id]);
    }

    protected function importArticleImage($objItem, $objSourceItem)
    {
        $imageReference = $this->Database->prepare(
            "SELECT * FROM sys_file_reference WHERE uid_foreign = ? AND tablenames=? AND deleted = 0 AND table_local = ? AND fieldname = ? ORDER by sorting_foreign"
        )->execute(
            $objSourceItem->uid,
            'tx_news_domain_model_news',
            'sys_file',
            'article_image'
        );

        if (!$imageReference->numRows) {
            $objItem->addImage = '';

            return;
        }

        $imageReference = $imageReference->first();

        $file = $this->Database->prepare("SELECT * FROM sys_file WHERE uid = ?")->execute($imageReference->uid_local);

        if (!$file->numRows) {
            $objItem->addImage = '';

            return;
        }

        $objModel = $this->copyFile($file->identifier);

        if (!$objModel instanceof \FilesModel) {
            $objItem->addImage = '';

            return;
        }

        $objModel->copyright = [$imageReference->title];

        if ($imageReference->title || $imageReference->alternative || $imageReference->link || $imageReference->description) {
            $arrMeta['de'] = [
                'title'   => $imageReference->title ?: '',
                'alt'     => $imageReference->alternative ?: '',
                'link'    => $imageReference->link ?: '',
                'caption' => $imageReference->description ?: ($imageReference->title ?: ''),
            ];

            $objModel->meta = $arrMeta;
        }

        $objItem->addImage  = 1;
        $objItem->singleSRC = $objModel->uuid;

        $objModel->save();
    }

    /**
     * Prepare typo3 html for contao
     * @param string $html
     * @return string The adjusted html
     */
    protected function prepareHtml($html)
    {
        $html = $this->convert_external_link_tags($html);
        return $html;
    }

    /**
     * Convert external typo 3 <link> tags to anchors <a>
     * @param $html
     * @return mixed
     */
    public function convert_external_link_tags($html)
    {
        $pattern     = '/<link\s(.+)\s-\s(.*)?\s(".*")?>(.+)<\/link>/U';
        $replacement = '<a href="$1" target="$2">$4</a>';
        $html        = preg_replace($pattern, $replacement, $html);

        $html = str_replace(' target="external-link"', ' target="_blank"', $html);
        $html = str_replace(' target="-"', '', $html);
        return $html;
    }

    /**
     * Convert internal typo 3 <link> tags to anchors <a>
     * @param $html
     * @return mixed
     */
    public function convert_internal_link_tags($html)
    {
        $pattern     = '/<link\s([0-9]+)>(.+)<\/link>/U';
        $replacement = '<a href="http://www.nnu.edu/index.php?id=$1">$2</a>';
        preg_match_all($pattern, $html, $matches, PREG_PATTERN_ORDER);
        return preg_replace($pattern, $replacement, $html);
    }
}