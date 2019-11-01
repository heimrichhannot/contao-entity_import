<?php


namespace HeimrichHannot\EntityImport\Helper;


class ImporterHelper
{
    const EXTERNAL_IMPORT_TYPE_JSON = 'application/json';
    const EXTERNAL_IMPORT_TYPE_XML  = 'text/xml';

    const OPERATOR_EQUAL        = 'equal';
    const OPERATOR_NOTEQUAL     = 'notequal';
    const OPERATOR_LOWER        = 'lower';
    const OPERATOR_GREATER      = 'greater';
    const OPERATOR_LOWEREQUAL   = 'lowerequal';
    const OPERATOR_GREATEREQUAL = 'greaterequal';
    const OPERATOR_LIKE         = 'like';

    const OPERATORS = [
        self::OPERATOR_EQUAL,
        self::OPERATOR_NOTEQUAL,
        self::OPERATOR_LOWER,
        self::OPERATOR_GREATER,
        self::OPERATOR_LOWEREQUAL,
        self::OPERATOR_GREATEREQUAL,
        self::OPERATOR_LIKE
    ];

    const MESSAGE_METHOD_ADDINFO         = 'addInfo';
    const MESSAGE_METHOD_ADDCONFIRMATION = 'addConfirmation';


    /**
     * @return array
     */
    public function getOperators()
    {
        return self::OPERATORS;
    }

    /**
     * @param $fieldValue
     * @param $operator
     * @param $compareValue
     * @return bool
     */
    public function isValid($fieldValue, $operator, $compareValue)
    {
        switch ($operator) {
            case self::OPERATOR_EQUAL:
                return $fieldValue == $compareValue;
                break;
            case self::OPERATOR_NOTEQUAL:
                return $fieldValue != $compareValue;
                break;
            case self::OPERATOR_LOWER:
                return $fieldValue < $compareValue;
                break;
            case self::OPERATOR_GREATER:
                return $fieldValue > $compareValue;
                break;
            case self::OPERATOR_LOWEREQUAL:
                return $fieldValue <= $compareValue;
                break;
            case self::OPERATOR_GREATEREQUAL:
                return $fieldValue >= $compareValue;
                break;
            case self::OPERATOR_LIKE:
                return false !== strpos($fieldValue, $compareValue);
                break;
            default:
                return false;
        }
    }
}