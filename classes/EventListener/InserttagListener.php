<?php

namespace HeimrichHannot\EntityImport\EventListener;


use Contao\FilesModel;
use Contao\StringUtil;
use Contao\Validator;

class InserttagListener
{
    public function onReplaceInsertTags(string $tag)
    {
        $tag = trim($tag, '{}');
        $tag = explode('::', $tag);
        if (empty($tag)) {
            return false;
        }
        switch ($tag[0]) {
            case 'file_uuid':
                return $this->generateFileUuidFromBin($tag);
                break;
            case 'file_bin':
                return $this->generateFileBinFromUuid($tag);
                break;
        }
        return false;
    }

    /**
     * convert uuid string to binary uuid
     *
     * @param $tag
     * @return string|null
     */
    protected function generateFileUuidFromBin($tag)
    {
        $source = strip_tags($tag[1]); // remove <span> etc, otherwise Validator::isuuid fail

        $file = null;
        if (Validator::isUuid($source)) {
            /** @var FilesModel $file */
            $file = FilesModel::findByUuid($source);
        } elseif (false !== ($pos = strpos($source, '/'))) {
            if (0 === $pos) {
                $source = ltrim($source, '/');
            }
            /** @var FilesModel $file */
            $file = FilesModel::findByPath($source);
        }

        return $file ? $file->uuid : null;
    }

    /**
     * convert binary uuid to uuid string
     *
     * @param $tag
     * @return string|null
     */
    protected function generateFileBinFromUuid($tag)
    {
        return (null !== ($uuid = $this->generateFileUuidFromBin($tag))) ? StringUtil::binToUuid($uuid) : null;
    }
}