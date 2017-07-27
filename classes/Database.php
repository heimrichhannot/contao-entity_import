<?php

namespace HeimrichHannot\EntityImport;

use Doctrine\DBAL\DriverManager;

class Database extends \Database
{
    /**
     * Establish the database connection
     *
     * @param array $arrConfig The configuration array
     *
     * @throws \Exception If a connection cannot be established
     */
    protected function __construct(array $arrConfig)
    {
        if (version_compare(VERSION, '4.0', '<'))
        {
            parent::__construct($arrConfig);
            return;
        }

        if (!empty($arrConfig))
        {
            $arrParams = [
                'driver'   => 'pdo_mysql',
                'host'     => $arrConfig['dbHost'],
                'port'     => $arrConfig['dbPort'],
                'user'     => $arrConfig['dbUser'],
                'password' => $arrConfig['dbPass'],
                'dbname'   => $arrConfig['dbDatabase'],
                'charset'  => $arrConfig['dbCharset'],

            ];

            $this->resConnection = DriverManager::getConnection($arrParams);
        }
        else
        {
            $this->resConnection = \System::getContainer()->get('database_connection');
        }

        if (!is_object($this->resConnection))
        {
            throw new \Exception(sprintf('Could not connect to database (%s)', $this->error));
        }
    }

    /**
     * Instantiate the Database object (Factory)
     *
     * @param array $arrCustom A configuration array
     *
     * @return \Database The Database object
     */
    public static function getInstance(array $arrCustom = null)
    {
        $arrConfig = [
            'dbDriver'   => \Config::get('dbDriver'),
            'dbHost'     => \Config::get('dbHost'),
            'dbUser'     => \Config::get('dbUser'),
            'dbPass'     => \Config::get('dbPass'),
            'dbDatabase' => \Config::get('dbDatabase'),
            'dbPconnect' => \Config::get('dbPconnect'),
            'dbCharset'  => \Config::get('dbCharset'),
            'dbPort'     => \Config::get('dbPort'),
            'dbSocket'   => \Config::get('dbSocket'),
            'dbSqlMode'  => \Config::get('dbSqlMode'),
        ];

        if (is_array($arrCustom))
        {
            $arrConfig = array_merge($arrConfig, $arrCustom);
        }

        // Sort the array before generating the key
        ksort($arrConfig);
        $strKey = md5(implode('', $arrConfig));

        if (!isset(static::$arrInstances[$strKey]))
        {
            if (version_compare(VERSION, '4.0', '<'))
            {
                $strClass = 'Database\\' . str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($arrConfig['dbDriver']))));
            }
            else
            {
                $strClass = 'Database';
            }

            static::$arrInstances[$strKey] = new static($arrConfig);
        }

        return static::$arrInstances[$strKey];
    }
}
