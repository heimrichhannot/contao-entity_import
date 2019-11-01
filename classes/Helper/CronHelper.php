<?php


namespace HeimrichHannot\EntityImport\Helper;

use Contao\Controller;
use Contao\System;
use HeimrichHannot\EntityImport\EntityImportConfigModel;
use HeimrichHannot\EntityImport\Importer\Importer;

class CronHelper
{
    public function minutely()
    {
        $this->import('minutely');
    }

    public function hourly()
    {
        $this->import('hourly');
    }

    public function daily()
    {
        $this->import('daily');
    }

    public function weekly()
    {
        $this->import('weekly');
    }

    public function monthly()
    {
        $this->import('monthly');
    }


    /**
     * launch import on cron
     *
     * @param string $interval
     * @return null
     */
    protected function import($interval = 'hourly')
    {
        if(null === ($importerConfigs =  EntityImportConfigModel::findBy(['useCron=?','cronInterval=?'], [1, $interval]))) {
            return null;
        }

        Controller::loadLanguageFile('tl_entity_import_config');

        foreach($importerConfigs as $config) {
            \ClassLoader::load($config->importerClass);
            if(null === ($importer = class_exists($config->importerClass) ? new $config->importerClass($config) : new Importer($config))) {
                System::log(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['externalImportCronMessageNoImporterClass'], $config->title),__METHOD__, TL_CRON);
                continue;
            }

            $importer->run();
            System::log(sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['externalImportCronMessage'], $config->title),__METHOD__, TL_CRON);
        }
    }
}