<?php

namespace HeimrichHannot\EntityImport;

abstract class Database extends \Database
{
    /**
     * Instantiate the Database object (Factory)
     *
     * @param array $arrCustom A configuration array
     *
     * @return \Database The Database object
     */
    public static function getInstance(array $arrCustom=null)
    {
        $arrConfig = array
        (
            'dbDriver'   => \Config::get('dbDriver'),
            'dbHost'     => \Config::get('dbHost'),
            'dbUser'     => \Config::get('dbUser'),
            'dbPass'     => \Config::get('dbPass'),
            'dbDatabase' => \Config::get('dbDatabase'),
            'dbPconnect' => \Config::get('dbPconnect'),
            'dbCharset'  => \Config::get('dbCharset'),
            'dbPort'     => \Config::get('dbPort'),
            'dbSocket'   => \Config::get('dbSocket'),
            'dbSqlMode'  => \Config::get('dbSqlMode')
        );

        if (is_array($arrCustom))
        {
            $arrConfig = array_merge($arrConfig, $arrCustom);
        }

        // Sort the array before generating the key
        ksort($arrConfig);
        $strKey = md5(implode('', $arrConfig));

        if (!isset(static::$arrInstances[$strKey]))
        {
            $strClass = 'Database\\' . str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($arrConfig['dbDriver']))));
            static::$arrInstances[$strKey] = new $strClass($arrConfig);
        }

        return static::$arrInstances[$strKey];
    }
}
