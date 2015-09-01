<?php

namespace HeimrichHannot\EntityImport;


class ModuleEntityImport extends \BackendModule
{

	/**
	 * Data container
	 *
	 * @var object
	 */
	protected $objDc;

	/**
	 * Template
	 *
	 * @var string
	 */
	protected $strTemplate = 'dev_entity_import';


	/**
	 * Generate the module
	 *
	 * @return string
	 */
	public function generate()
	{
		$this->objDc = func_get_arg(0);
		return parent::generate();
	}


	/**
	 * Generate module
	 */
	protected function compile()
	{
		// Create files
		if (\Input::post('FORM_SUBMIT') == 'tl_entity_import') {
			$objModel = EntityImportConfigModel::findByPk($this->objDc->id);

			if ($objModel === null) {
				return;
			}

			if (class_exists($objModel->importerClass))
			{
				// use a particular importer (e.g. NewsImporter)
				\Message::addInfo(
					sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['importerInfo'], $objModel->importerClass)
				);
				$importer = new $objModel->importerClass($objModel);
			}
			else
			{
				\Message::addInfo(
					sprintf($GLOBALS['TL_LANG']['tl_entity_import_config']['importerInfo'], 'Importer')
				);
				$importer = new Importer($objModel);
			}

			if ($importer->run()) {
				// Confirm and reload
				\Message::addConfirmation(
					$GLOBALS['TL_LANG']['tl_entity_import_config']['confirm']
				);
				\Controller::reload();
			}
		}

		$this->Template->base = \Environment::get('base');
		$this->Template->href = \Controller::getReferer(true);
		$this->Template->title = specialchars(
			$GLOBALS['TL_LANG']['MSC']['backBTTitle']
		);
		$this->Template->action = ampersand(\Environment::get('request'));
		$this->Template->selectAll = $GLOBALS['TL_LANG']['MSC']['selectAll'];
		$this->Template->button = $GLOBALS['TL_LANG']['MSC']['backBT'];
		$this->Template->message = \Message::generate();
		$this->Template->submit = specialchars(
			$GLOBALS['TL_LANG']['tl_entity_import_config']['import'][0]
		);
		$this->Template->headline = sprintf(
			$GLOBALS['TL_LANG']['tl_entity_import_config']['headline'],
			\Input::get('id')
		);
		$this->Template->explain
			= $GLOBALS['TL_LANG']['tl_entity_import_config']['make'][1];
		$this->Template->label
			= $GLOBALS['TL_LANG']['tl_entity_import_config']['label'];
	}


	/**
	 * Return a new template object
	 *
	 * @param string
	 * @param \Database\Result
	 *
	 * @return \BackendTemplate
	 */
	protected function newTemplate($strTemplate, \Database\Result $objModule)
	{
		$objTemplate = new \BackendTemplate($strTemplate);

		$objTemplate->folder = $objModule->folder;
		$objTemplate->author = str_replace(
			array('[', ']'), array('<', '>'), $objModule->author
		);
		$objTemplate->copyright = $objModule->copyright;
		$objTemplate->package = $objModule->package;
		$objTemplate->license = $objModule->license;

		return $objTemplate;
	}


	/**
	 * Try to guess the subfolder of a class depending on its name
	 *
	 * @param string
	 *
	 * @return string
	 */
	protected function guessSubfolder($strClassName)
	{
		if (strncmp($strClassName, 'DC_', 3) === 0) {
			return 'drivers';
		} elseif (strncmp($strClassName, 'Content', 7) === 0) {
			return 'elements';
		} elseif (strncmp($strClassName, 'Form', 4) === 0) {
			return 'forms';
		} elseif (strncmp($strClassName, 'Module', 6) === 0) {
			return 'modules';
		} elseif (strncmp($strClassName, 'Page', 4) === 0) {
			return 'pages';
		} else {
			return 'classes';
		}
	}


	/**
	 * Try to guess the parent class of a class depending on its name
	 *
	 * @param string
	 *
	 * @return string
	 */
	protected function guessParentClass($strClassName)
	{
		if (strncmp($strClassName, 'Content', 7) === 0) {
			return 'ContentElement';
		} elseif (strncmp($strClassName, 'Form', 4) === 0) {
			return 'Widget';
		} elseif (strncmp($strClassName, 'Page', 4) === 0) {
			return 'Frontend';
		} else {
			return 'Module';
		}
	}
}
