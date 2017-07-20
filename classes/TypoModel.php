<?php

namespace HeimrichHannot\Typort;

abstract class TypoModel extends \Model
{

    /**
     * Primary key
     * @var string
     */
    protected static $strPk = 'uid';

    /**
     * Find records and return the model or model collection
     *
     * Supported options:
     *
     * * column: the field name
     * * value:  the field value
     * * limit:  the maximum number of rows
     * * offset: the number of rows to skip
     * * order:  the sorting order
     * * eager:  load all related records eagerly
     *
     * @param array $arrOptions The options array
     *
     * @return \Model|\Model\Collection|null A model, model collection or null if the result is empty
     */
    protected static function find(array $arrOptions)
    {
        if (static::$strTable == '')
        {
            return null;
        }

        $arrOptions['table'] = static::$strTable;
        $strQuery = static::buildFindQuery($arrOptions);

        $objStatement = Database::getInstance()->prepare($strQuery);

        // Defaults for limit and offset
        if (!isset($arrOptions['limit']))
        {
            $arrOptions['limit'] = 0;
        }
        if (!isset($arrOptions['offset']))
        {
            $arrOptions['offset'] = 0;
        }

        // Limit
        if ($arrOptions['limit'] > 0 || $arrOptions['offset'] > 0)
        {
            $objStatement->limit($arrOptions['limit'], $arrOptions['offset']);
        }

        $objStatement = static::preFind($objStatement);
        $objResult = $objStatement->execute($arrOptions['value']);

        if ($objResult->numRows < 1)
        {
            return null;
        }

        $objResult = static::postFind($objResult);

        if ($arrOptions['return'] == 'Model')
        {
            $strPk = static::$strPk;
            $intPk = $objResult->{$strPk};

            // Try to load from the registry
            $objModel = \Model\Registry::getInstance()->fetch(static::$strTable, $intPk);

            if ($objModel !== null)
            {
                return $objModel->mergeRow($objResult->row());
            }

            return static::createModelFromDbResult($objResult);
        }
        else
        {
            // TODO: collection contains always the same item
            return static::createCollectionFromDbResult($objResult, static::$strTable);
        }
    }

    /**
     * Find records by various criteria
     *
     * @param mixed $strColumn  The property name
     * @param mixed $varValue   The property value
     * @param array $arrOptions An optional options array
     *
     * @return \Model\Collection|null The model collection or null if the result is empty
     */
    public static function findBy($strColumn, $varValue, array $arrOptions=array())
    {
        $arrOptions = array_merge
        (
            array
            (
                'column' => $strColumn,
                'value'  => $varValue,
                'return' => 'Collection'
            ),

            $arrOptions
        );

        return static::find($arrOptions);
    }
}