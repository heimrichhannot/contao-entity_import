<?php

namespace HeimrichHannot\EntityImport\Importer;

use Haste\Util\StringUtil;
use HeimrichHannot\EntityImport\Database;
use HeimrichHannot\EntityImport\EntityImportModel;
use HeimrichHannot\Haste\Model\Model;
use Wa72\HtmlPageDom\HtmlPageCrawler;

class Importer extends \Backend
{
    protected $objModel;

    protected $objParentModel;

    protected $objItems;

    protected $arrData = [];

    protected $arrRawFieldMapping = [];

    protected $arrRawFileMapping = [];

    protected $arrMapping = [];

    protected $arrFileMapping = [];

    protected $arrNamedMapping = [];

    protected $Database;

    protected $arrDbSourceFields = [];

    protected $arrDbTargetFields = [];

    protected $arrDbFileFields = [];

    protected $dryRun = false;

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

        parent::__construct();

        ini_set('max_execution_time', 0);

        if (!isset($GLOBALS['loadDataContainer'][$this->objModel->dbTargetTable]))
        {
            \Controller::loadDataContainer($this->objModel->dbTargetTable);
        }

        if ($objModel->purgeBeforeImport && !$this->dryRun)
        {
            $this->purgeBeforeImport($objModel);
        }

        $this->arrData           = $objModel->row();
        $this->objParentModel    = EntityImportModel::findByPk($this->objModel->pid);
        $this->Database          = Database::getInstance($this->objParentModel->row());
        $this->arrDbSourceFields = $this->Database->listFields($this->dbSourceTable);
        $this->arrDbTargetFields = \Database::getInstance()->listFields($this->dbTargetTable);
        $this->arrDbFileFields   = \Database::getInstance()->listFields('tl_files');

        $this->arrRawFieldMapping = deserialize($this->dbFieldMapping, true);
        $this->arrRawFileMapping  = deserialize($this->dbFieldFileMapping, true);
        $this->arrMapping         = $this->getFieldsMapping(deserialize($this->dbFieldMapping, true), $this->arrDbSourceFields, $this->arrDbTargetFields);
        $this->arrFileMapping     = $this->getFieldsMapping(deserialize($this->dbFieldFileMapping, true), $this->arrDbSourceFields, $this->arrDbFileFields);

        $arrNamedMapping = $this->arrMapping;

        // name fields
        array_walk(
            $arrNamedMapping,
            function (&$value, $index) {
                $value = $value . ' as ' . $index;
            }
        );

        $this->arrNamedMapping = $arrNamedMapping;
    }

    /**
     * deletes entries in given table by given reference column
     *
     * @param $objModel
     */
    protected function purgeAdditionalTablesBeforeImport($objModel)
    {
        $whereClausePurge = $objModel->whereClausePurge;

        $strQuery = 'SELECT id FROM ' . $objModel->dbTargetTable . ($whereClausePurge ? ' WHERE ' . html_entity_decode($whereClausePurge) : '');

        $result = \Database::getInstance()->execute($strQuery);

        if (!$result->next())
        {
            return;
        }

        $idsToRemove = [];
        while ($result->next())
        {
            $idsToRemove[] = $result->id;
        }

        $tables = unserialize($objModel->additionalTablesToPurge);

        if (!empty($tables) && !empty($idsToRemove))
        {
            foreach ($tables as $table)
            {
                \Database::getInstance()
                    ->prepare('DELETE FROM ' . $table['tableToPurge'] . ' WHERE ' . $table['referenceColumn'] . ' IN (' . implode(', ', $idsToRemove) . ')')
                    ->execute();
            }
        }
    }

    protected function purgeBeforeImport($objModel)
    {
        $whereClausePurge = $objModel->whereClausePurge;

        if ($objModel->purgeAdditionalTables)
        {
            $this->purgeAdditionalTablesBeforeImport($objModel);
        }


        $strQuery = 'DELETE FROM ' . $objModel->dbTargetTable . ($whereClausePurge ? ' WHERE ' . html_entity_decode($whereClausePurge) : '');

        \Database::getInstance()->execute($strQuery);

        $ctable = $GLOBALS['TL_DCA'][$objModel->dbTargetTable]['config']['ctable'];

        // Delete all records of the child table that are not related to the current table
        if (!empty($ctable) && is_array($ctable))
        {
            foreach ($ctable as $v)
            {
                if ($v != '')
                {
                    // Load the DCA configuration so we can check for "dynamicPtable"
                    if (!isset($GLOBALS['loadDataContainer'][$v]))
                    {
                        \Controller::loadDataContainer($v);
                    }

                    if ($GLOBALS['TL_DCA'][$v]['config']['dynamicPtable'])
                    {
                        \Database::getInstance()->execute(
                            "DELETE FROM $v WHERE ptable='" . $objModel->dbTargetTable . "' AND NOT EXISTS (SELECT * FROM " . $objModel->dbTargetTable . " WHERE $v.pid = "
                            . $objModel->dbTargetTable . ".id)"
                        );
                    }
                    else
                    {
                        \Database::getInstance()->execute(
                            "DELETE FROM $v WHERE NOT EXISTS (SELECT * FROM " . $objModel->dbTargetTable . " WHERE $v.pid = " . $objModel->dbTargetTable . ".id)"
                        );
                    }
                }
            }
        }
    }

    /**
     * @return array Array
     * Key = Field Name
     * Value = Contao Field Name
     */
    protected function getFieldsMapping(array $arrSourceMap, array $arrSourceFields, array $arrTargetFields)
    {
        $arrMap = [];

        foreach ($arrSourceMap as $arrConfig)
        {
            switch ($arrConfig['type'])
            {
                case 'source':
                case 'foreignKey':
                    $arrSrcDbConfig               = $this->getDbConfig($arrConfig['source'], $arrSourceFields);
                    $arrTargetDbConfig            = $this->getDbConfig($arrConfig['target'], $arrTargetFields);
                    $arrMap[$arrConfig['target']] = $this->getFieldMappingDbValue($arrSrcDbConfig, $arrTargetDbConfig, $arrConfig);
                    break;
                case 'sql':

                    $value = $arrConfig['value'];

                    // check for inserttags
                    if (strpos($value, '{{') !== false)
                    {
                        $value = \Controller::replaceInsertTags($value, false);
                    }

                    // check for field simple tokens
                    if (strpos($value, '##') !== false)
                    {
                        $arrTokens = [];

                        foreach ($arrSourceFields as $key => $field)
                        {
                            $arrTokens[$field['name']] = $this->dbSourceTable . '.' . $field['name'];
                        }

                        $value = \StringUtil::parseSimpleTokens($value, $arrTokens);
                    }

                    $arrMap[$arrConfig['target']] = '[[SQL::' . $value . ']]';
                    break;
                case 'value':
                    if (empty($arrConfig['value']))
                    {
                        break;
                    }

                    $value = $arrConfig['value'];

                    // check for inserttags
                    if (strpos($value, '{{') !== false)
                    {
                        $value = \Controller::replaceInsertTags($value, false);
                    }

                    // check for field simple tokens
                    if (strpos($value, '##') !== false)
                    {
                        $arrTokens = [];

                        foreach ($arrSourceFields as $key => $field)
                        {
                            $arrTokens[$field['name']] = $this->objModel->dbSourceTable . '.' . $field['name'];
                        }

                        $value = \StringUtil::parseSimpleTokens($value, $arrTokens);
                    }

                    // sql expression
                    if (strpos($value, $this->objModel->dbSourceTable) !== false)
                    {
                        $arrMap[$arrConfig['target']] = $value;
                        break;
                    }

                    if (is_numeric($value))
                    {
                        $value = $value;
                    }
                    else if (is_string($value))
                    {
                        $value = '"' . addslashes($value) . '"';
                    }

                    $arrMap[$arrConfig['target']] = $value;

                    break;
            }

        }

        return $arrMap;
    }

    protected function getDbConfig($strName, array $arrFields)
    {
        foreach ($arrFields as $arrField)
        {
            if ($strName == $arrField['name'])
            {
                return $arrField;
            }
        }

        return false;
    }

    protected function getFieldMappingDbValue($arrSourceConfig, $arrTargetConfig, $arrConfig)
    {
        $t = $this->dbSourceTable;

        $strValue = $arrSourceConfig['name'];

        switch ($arrSourceConfig['type'])
        {
            case 'timestamp':
                if ($arrTargetConfig['type'] == 'int')
                {
                    $strValue = "UNIX_TIMESTAMP($t.$strValue)";
                }
                break;
            default:
                $strValue = $this->dbSourceTable . '.' . $strValue;
        }

        $strForeignKey = $arrConfig['type'] == 'foreignKey' ? $arrConfig['value'] : '';

        if ($strForeignKey != ''
            && preg_match(
                '#(?<PK>.*)=(?<TABLE>.*)[.](?<COLUMN>.*)#',
                \StringUtil::decodeEntities($strForeignKey),
                $arrForeignKey
            ))
        {
            if (isset($arrForeignKey['PK']) && ($arrForeignKey['TABLE']) && ($arrForeignKey['COLUMN']))
            {
                $strValue = sprintf("(SELECT %s FROM %s WHERE %s=%s)", $arrForeignKey['COLUMN'], $arrForeignKey['TABLE'], $arrForeignKey['PK'], $strValue);
            }
        }

        return $strValue;
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

        if ($this->objItems === null)
        {
            return false;
        }

        $strClass = \Model::getClassFromTable($this->dbTargetTable);

        if (!class_exists($strClass))
        {
            return false;
        }

        while ($this->objItems->next())
        {
            $objItem = $this->createObjectFromMapping($this->objItems, $strClass);
            $this->createImportMessage($objItem);
        }

        // reset the iterator
        $this->objItems->reset();
        $this->runAfterComplete($this->objItems);

        return true;
    }

    protected function collectItems()
    {
        $t = $this->dbSourceTable;

        $strQuery = "SELECT *, " . implode(', ', $this->arrNamedMapping) . " FROM $t";

        if ($this->whereClause)
        {
            $strQuery .= " WHERE " . $this->whereClause;
        }

        if ($this->useTimeInterval)
        {
            $intStart = intval($this->start ? $this->start : 0);
            $intEnd   = intval($this->end ? $this->end : 2145913200);

            $strDateCol = $this->arrMapping['date'];
            $strQuery   .= html_entity_decode(
                ($this->whereClause ? " AND " : " WHERE ") . "(($strDateCol>=$intStart AND $strDateCol<$intEnd))"
            );
        }

        if (strpos($strQuery, '[[') !== false)
        {
            $tags = preg_split('~\[\[([\pL\pN][^\[\]]*)\]\]~u', $strQuery, -1, PREG_SPLIT_DELIM_CAPTURE);

            $strQuery = '';

            for ($_rit = 0, $_cnt = count($tags); $_rit < $_cnt; $_rit += 2)
            {
                $strQuery .= $tags[$_rit];
                $strTag   = $tags[$_rit + 1];

                // Skip empty tags
                if ($strTag == '')
                {
                    continue;
                }

                $flags    = explode('|', $strTag);
                $tag      = array_shift($flags);
                $elements = explode('::', $tag);

                // Replace the tag
                switch (strtolower($elements[0]))
                {
                    case 'sql':
                        $strQuery .= '(' . $elements[1] . ')';
                        break;
                }
            }
        }

        $objResult = $this->Database->prepare($strQuery)->execute();

        $this->objItems = $objResult;
    }

    protected function createObjectFromMapping($objSourceItem, $strClass = null)
    {
        if ($strClass === null)
        {
            return null;
        }

        $objItem = Model::setDefaultsFromDca(new $strClass());

        \Controller::loadDataContainer($this->dbTargetTable);

        $dca = $GLOBALS['TL_DCA'][$this->dbTargetTable];

        // update existing items
        if (in_array($objItem->getPk(), array_keys($this->arrMapping)))
        {
            $objUpdateItem = $strClass::findByPk($objSourceItem->{$objItem->getPk()});

            if ($objUpdateItem !== null)
            {
                $objItem = $objUpdateItem;
            }
        }

        foreach ($this->arrMapping as $key => $col)
        {
            if (strpos($col, '[[SQL::') !== false)
            {
                $value = $objSourceItem->{$key};
            }
            else
            {
                $value = $this->setValueByType($objSourceItem->{$key}, $dca['fields'][$key], $objItem, $objSourceItem);
            }

            $this->setObjectValueFromMapping($objItem, $value, $key);

            if ($value === null)
            {
                continue;
            }

            // do not save in dry run
            if ($this->dryRun)
            {
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
                    $objItem = $this->{$callback[0]}->{$callback[1]}($objItem, $objSourceItem, $this);
                }
            }
        }

        return $objItem;
    }

    protected function setValueByType($varValue, $arrData, $varItem, $objSourceItem)
    {
        switch ($arrData['inputType'])
        {
            case 'fileTree':
                if ($arrData['eval']['filesOnly'])
                {
                    if (!$this->dryRun)
                    {
                        $varValue = deserialize($varValue);

                        if (is_array($varValue))
                        {
                            $varValue = $this->createMultipleFiles($varValue, $arrData, $varItem, $objSourceItem);
                            break;
                        }

                        $varValue = $this->createSingleFile($varValue, $arrData, $varItem, $objSourceItem);
                    }
                }

                // TODO: multiple files
                break;
        }

        return $varValue;
    }

    protected function createMultipleFiles(array $arrFiles, $arrData, $varItem, $objSourceItem)
    {
        $arrReturn = [];

        foreach ($arrFiles as $varValue)
        {
            $uuid = $this->createSingleFile($varValue, $arrData, $varItem, $objSourceItem);

            if (!\Validator::isUuid($uuid))
            {
                continue;
            }

            $arrReturn[] = $uuid;
        }

        return $arrReturn;
    }

    protected function createSingleFile($varValue, $arrData, $varItem, $objSourceItem)
    {
        if ($varValue == '')
        {
            return false;
        }

        // contao 3.x files model support
        if (\Validator::isUuid($varValue))
        {
            $objRelFile = \FilesModel::findByUuid($varValue);
            $varValue   = $objRelFile->path;
        }

        $strRelFile = $varValue;

        // source dir is given, take file from there
        if ($this->sourceDir !== null)
        {
            $objSourceDir = \FilesModel::findByUuid($this->sourceDir);

            if ($objSourceDir !== null)
            {
                $strRelFile = $objSourceDir->path . '/' . ltrim($varValue, '/');
            }
        }

        // source file = target file
        $strTargetFile = $strRelFile;

        // target dir is set, move file to there
        if ($this->targetDir !== null)
        {
            $objTargetDir = \FilesModel::findByUuid($this->targetDir);

            if ($objTargetDir !== null)
            {
                $strTargetFile = $objTargetDir->path . '/' . basename($strRelFile);
            }
        }

        if (is_dir(TL_ROOT . '/' . $strRelFile) || !file_exists(TL_ROOT . '/' . $strRelFile))
        {
            return null;
        }

        $objFile = new \File($strRelFile, false);

        $blnCopy = true;

        if (file_exists(TL_ROOT . '/' . $strTargetFile))
        {
            $blnCopy = false;

            $objTargetFile = new \File($strTargetFile, true);

            $blnCopy = ($objTargetFile->size != $objFile->size || $objTargetFile->mtime < $objFile->mtime);

            if (!$blnCopy)
            {
                $objFile = $objTargetFile;
            }
        }

        if ($blnCopy)
        {
            $objFile->copyTo($strTargetFile);
        }

        $objModel = $objFile->getModel();

        if ($objModel !== null)
        {
            if ($blnCopy)
            {
                \Message::addConfirmation('Copied file from:<i>' . $strRelFile . '</i><br /> to: <i>' . $strTargetFile . '</i>');
            }
            else
            {
                \Message::addConfirmation('File <i>' . $strTargetFile . '</i> already exists, no copy needed.');
            }
        }

        if (!is_array($this->arrFileMapping) || empty($this->arrFileMapping))
        {
            return $objModel !== null ? $objModel->uuid : null;
        }

        // set additional file fields from source
        foreach ($this->arrFileMapping as $key => $col)
        {
            $col = str_replace($this->dbSourceTable . '.', '', $col);

            $value = $objSourceItem->{$col};

            $this->setObjectValueFromMapping($objModel, $value, $key);

            if ($value === null)
            {
                continue;
            }

            // do not save in dry run
            if ($this->dryRun)
            {
                continue;
            }

            $objModel->save();
        }


        return $objModel !== null ? $objModel->uuid : null;
    }

    protected function getFieldMapping() {
        return deserialize($this->dbFieldMapping, true);
    }

    protected function setObjectValueFromMapping(&$objItem, $value, $key)
    {
        $config = null;

        foreach ($this->getFieldMapping() as $mapping)
        {
            if ($mapping['target'] == $key)
            {
                $config = $mapping;
                break;
            }
        }

        if ($config === null)
        {
            return $objItem;
        }

        if ($config['transform'])
        {
            if (strpos($config['transform'], '##') !== false)
            {
                $config['transform'] = \StringUtil::parseSimpleTokens($config['transform'], ['value' => $value]);
            }

            $value = \Controller::replaceInsertTags($config['transform'], false);
        }

        $value = \Controller::replaceInsertTags($value, false);
        // negate the value
        if (substr($key, 0, 1) == '!')
        {
            $key = preg_replace('/!/', '', $key, 1);

            if (is_array($objItem))
            {
                $objItem[$key] = !$value;
            }
            else
            {
                $objItem->{$key} = !$value;
            }

            return $objItem;
        }

        // fill multiple fields with one value
        $multipleKeys = trimsplit(',', $key);
        if (!empty($multipleKeys))
        {
            foreach ($multipleKeys as $subKey)
            {
                $arrConfig = $GLOBALS['TL_DCA'][$this->objModel->dbTargetTable]['fields'][$subKey];

                if (is_array($objItem))
                {
                    $objItem[$subKey] = $value;
                }
                else
                {
                    // Values set on many-to-many relation fields have to be an array
                    if (is_array($arrConfig['relation']) && $arrConfig['relation']['type'] == 'haste-ManyToMany')
                    {
                        $objItem->{$subKey} = [$value];
                        continue;
                    }

                    $objItem->{$subKey} = $value;
                }
            }

            return $objItem;
        }

        if (is_array($objItem))
        {
            $objItem[$key] = $value;
        }
        else
        {
            $objItem->{$key} = $value;
        }
    }

    protected function copyFile($strFile)
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

        $strRelFile = $objSourceDir->path . '/' . ltrim($strFile, '/');

        if (is_dir(TL_ROOT . '/' . $strRelFile) || !file_exists(TL_ROOT . '/' . $strRelFile))
        {
            return false;
        }

        $objFile = new \File($strRelFile);
        $strDestination = $objTargetDir->path . '/' . $objFile->name;

        // if file was copied before (within another entity) return its model
        if (($objModel = \FilesModel::findMultipleByPaths([$strDestination])) !== null && file_exists(TL_ROOT . '/' . $strDestination))
        {
            return $objModel->current();
        }

        $objFile->copyTo($strDestination);
        $objModel = \FilesModel::findByPath($strDestination);

        return $objModel;
    }

    protected function runAfterSaving(&$objItem, $objSourceItem)
    {
    }

    protected function createImportMessage($objItem)
    {
    }

    protected function runAfterComplete($objItems)
    {
    }

    /**
     * Return an object property
     *
     * @param string
     *
     * @return mixed
     */
    public function __get($strKey)
    {
        if (isset($this->arrData[$strKey]))
        {
            return $this->arrData[$strKey];
        }

        return parent::__get($strKey);
    }

    /**
     * Set an object property
     *
     * @param string
     * @param mixed
     */
    public function __set($strKey, $varValue)
    {
        $this->arrData[$strKey] = $varValue;
    }

    /**
     * Check whether a property is set
     *
     * @param string
     *
     * @return boolean
     */
    public function __isset($strKey)
    {
        return isset($this->arrData[$strKey]);
    }

    /**
     * Return the model
     *
     * @return \Model
     */
    public function getModel()
    {
        return $this->objModel;
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
}
