<?php


namespace HeimrichHannot\EntityImport\Importer;


use Contao\Backend;
use Contao\BackendUser;
use Contao\Controller;
use Contao\Message;
use Contao\Model;
use Contao\StringUtil;
use Contao\System;
use Contao\UserModel;
use HeimrichHannot\EntityImport\EntityImportModel;
use HeimrichHannot\EntityImport\Helper\ImporterHelper;
use HeimrichHannot\Haste\Util\Curl;
use Model\Collection;

class ExternalImporter extends Importer
{
    protected $importUrl = '';

    protected $externalFieldMapping = [];

    protected $externalImportExceptions = [];

    protected $externalImportExclusions = [];

    protected $isMerged = false;


    public function __construct($objModel)
    {
        if ($objModel instanceof Model) {
            $this->objModel = $objModel;
        } elseif ($objModel instanceof Collection) {
            $this->objModel = $objModel->current();
        }

        if (!isset($GLOBALS['loadDataContainer'][$this->objModel->dbTargetTable])) {
            Controller::loadDataContainer($this->objModel->dbTargetTable);
        }

        if ($objModel->purgeBeforeImport && !$this->dryRun) {
            $this->purgeBeforeImport($objModel);
        }

        Backend::__construct();

        $this->arrData                  = $objModel->row();
        $this->importUrl                = EntityImportModel::findByPk($objModel->pid)->externalUrl;
        $this->externalFieldMapping     = StringUtil::deserialize($objModel->externalFieldMapping);
        $this->externalImportExceptions = StringUtil::deserialize($objModel->externalImportExceptions);
        $this->externalImportExclusions = StringUtil::deserialize($objModel->externalImportExclusions);
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

        if ($this->objItems === null) {
            return false;
        }

        $strClass = Model::getClassFromTable($this->dbTargetTable);


        if (!class_exists($strClass)) {
            return false;
        }

        foreach ($this->objItems as $sourceItem) {

            if ($this->excludeItemFromImport($sourceItem)) {
                System::log('exclude: ' . $sourceItem->name, __METHOD__, TL_CRON);

                continue;
            }

            $item = $this->createObjectFromMapping($sourceItem, $strClass);
            $this->createImportMessage($item);
        }

        $this->runAfterComplete($this->objItems);

        return true;
    }

    /**
     *
     *
     * @return mixed|\SimpleXMLElement|null
     */
    protected function getExternalData()
    {
        $response = Curl::request($this->importUrl, [], true);
        $header   = $response[0];
        $data     = $response[1];

        switch ($header['Content-Type']) {
            case ImporterHelper::EXTERNAL_IMPORT_TYPE_JSON:
                return json_decode($data);
                break;
            case ImporterHelper::EXTERNAL_IMPORT_TYPE_XML:
                return simplexml_load_string($data);
                break;
            default:
                return null;
        }
    }


    protected function collectItems()
    {
        if (!$this->importUrl) {
            return null;
        }

        if (null === ($data = $this->getExternalData())) {
            return null;
        }

        $this->objItems = $data;
    }


    /**
     * @param $sourceItem
     * @param null $class
     * @return Model|null
     */
    protected function createObjectFromMapping($sourceItem, $class = null)
    {
        $item = null;

        if ($class === null) {
            return null;
        }

        if ($this->arrData['addMerge']) {
            $item           = $this->findExistingModelInstanceForMerge($sourceItem);
            $this->isMerged = true;
        }

        if (null === $item) {
            $item         = new $class();
            $this->setDefaults($item);
        }


        // HOOK: modify source item
        if (isset($GLOBALS['TL_HOOKS']['modifySourceItem']) && is_array($GLOBALS['TL_HOOKS']['modifySourceItem'])) {
            foreach ($GLOBALS['TL_HOOKS']['modifySourceItem'] as $callback) {
                $this->import($callback[0]);
                $sourceItem = $this->{$callback[0]}->{$callback[1]}($sourceItem);
            }
        }

        foreach ($this->externalFieldMapping as $mapping) {
            $item->{$mapping['target']} = 'source' == $mapping['type'] ? $sourceItem->{$mapping['source']} : Controller::replaceInsertTags($mapping['value']);
        }

        if (!empty($this->externalImportExceptions)) {
            $this->modifyItemDataByExceptions($item, $sourceItem);
        }

        if ($this->publishAfterImport) {
            $item->published = true;
        }

        // HOOK: modify item data before saving it
        if (isset($GLOBALS['TL_HOOKS']['modifyItemBeforeSave']) && is_array($GLOBALS['TL_HOOKS']['modifyItemBeforeSave'])) {
            foreach ($GLOBALS['TL_HOOKS']['modifyItemBeforeSave'] as $callback) {
                $this->import($callback[0]);
                $this->{$callback[0]}->{$callback[1]}($item, $sourceItem, $this->objModel);
            }
        }

        $item->save();
        return $item;
    }


    /**
     * apply the exceptions for the imported entity that have been set in config
     *
     * @param $item
     * @param $sourceItem
     */
    protected function modifyItemDataByExceptions(Model &$item, $sourceItem)
    {
        foreach ($this->externalImportExceptions as $exception) {
            if (!ImporterHelper::isValid($sourceItem->{$exception['externalField']}, $exception['operator'],
                Controller::replaceInsertTags($exception['externalValue']))) {
                continue;
            }

            $item->{$exception['importField']} = Controller::replaceInsertTags($exception['importValue']);
        }
    }


    /**
     * @param $sourceItem
     * @return Model|null
     */
    public function findExistingModelInstanceForMerge($sourceItem)
    {
        $identifierFields = StringUtil::deserialize($this->mergeIdentifierFields, true);

        if (empty($identifierFields)) {
            return null;
        }

        $columns = [];
        $values  = [];

        foreach ($identifierFields as $fieldData) {
            $columns[] = $this->dbTargetTable . '.' . $fieldData['target'] . '=?';
            $values[]  = $sourceItem->{$fieldData['source']};
        }

        $strItemClass = Model::getClassFromTable($this->dbTargetTable);

        if (!class_exists($strItemClass)) {
            return null;
        }

        return $strItemClass::findOneBy($columns, $values);
    }


    /**
     * check if source entity should be excluded from import
     *
     * @param $sourceItem
     * @return bool
     */
    protected function excludeItemFromImport($sourceItem)
    {
        foreach ($this->externalImportExclusions as $exclusion) {
            if (ImporterHelper::isValid($sourceItem->{$exclusion['externalField']}, $exclusion['operator'],Controller::replaceInsertTags($exclusion['externalValue']))) {
                return true;
            }
        }

        return false;

    }


    /**
     * create message on success
     *
     * @param $item
     */
    public function createImportMessage($item)
    {
        if ($this->isMerged) {
            $message = $GLOBALS['TL_LANG']['tl_entity_import_config']['externalImportMerged'];
            $method  = ImporterHelper::MESSAGE_METHOD_ADDINFO;
        } else {
            $message = $GLOBALS['TL_LANG']['tl_entity_import_config']['externalImport'];
            $method  = ImporterHelper::MESSAGE_METHOD_ADDCONFIRMATION;
        }

        if ($this->dryRun) {
            $message = $GLOBALS['TL_LANG']['tl_entity_import_config']['externalDry'];
        }

        Message::$method(sprintf($message, $item->title));
    }


    protected function setDefaults(&$item)
    {
        $item->dateAdded = time();
        $item->author = (null === ($user = BackendUser::getInstance())) ? UserModel::findByAdmin(true,['limit=1'])->id : $user->id;
    }
}