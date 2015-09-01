<?php

namespace HeimrichHannot\EntityImport;

class TypoRefIndexModel extends \HeimrichHannot\Typort\TypoModel
{
    protected static $strTable = 'sys_refindex';

    public static function findByRecUidsAndTableAndField($arrRecUids, $strTable, $strField, array $arrOptions=array())
    {
        if (!is_array($arrRecUids) || empty($arrRecUids))
        {
            return null;
        }

        $t = static::$strTable;
        $arrColumns = array("$t.recuid IN(" . implode(',', array_map('intval', $arrRecUids)) . ")");

        $arrColumns[] = "($t.tablename = ?) AND ($t.field = ?)";

        if (!isset($arrOptions['order']))
        {
            $arrOptions['order']  = "$t.sorting";
        }

        return static::findBy($arrColumns, array($strTable, $strField), $arrOptions);
    }
}