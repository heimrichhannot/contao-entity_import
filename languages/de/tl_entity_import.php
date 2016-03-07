<?php

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_entity_import']['title']	= array('Titel', 'Geben Sie hier den Titel des Imports ein.');
$GLOBALS['TL_LANG']['tl_entity_import']['type']	= array('Typ', 'Wählen Sie hier den Typ des Imports aus.');
$GLOBALS['TL_LANG']['tl_entity_import']['type'][ENTITY_IMPORT_CONFIG_TYPE_DATABASE] = 'Datenbank';
$GLOBALS['TL_LANG']['tl_entity_import']['type'][ENTITY_IMPORT_CONFIG_TYPE_FILE] = 'Datei';
$GLOBALS['TL_LANG']['tl_entity_import']['dbDriver']	= array('Treiber', 'Wählen Sie hier den Datenbanktreiber aus.');
$GLOBALS['TL_LANG']['tl_entity_import']['dbHost']	= array('Host', 'Geben Sie hier die Adresse des Datenbankhosts ein.');
$GLOBALS['TL_LANG']['tl_entity_import']['dbUser']	= array('Nutzer', 'Geben Sie hier einen berechtigten Datenbanknutzer ein.');
$GLOBALS['TL_LANG']['tl_entity_import']['dbPass']	= array('Passwort', 'Geben Sie hier das Passwort des berechtigten Datenbanknutzers ein.');
$GLOBALS['TL_LANG']['tl_entity_import']['dbDatabase']	= array('Datenbankname', 'Geben Sie hier den Namen der Datenbank ein.');
$GLOBALS['TL_LANG']['tl_entity_import']['dbPconnect']	= array('PConnect', 'Wählen Sie hier, ob Sie PConnect nutzen möchten.');
$GLOBALS['TL_LANG']['tl_entity_import']['dbCharset']	= array('Zeichensatz', 'Wählen Sie hier den gewünschten Zeichensatz aus.');
$GLOBALS['TL_LANG']['tl_entity_import']['dbSocket']	= array('Socket', 'Geben Sie hier einen Socket ein.');

/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_entity_import']['title_legend']	= 'Titel';
$GLOBALS['TL_LANG']['tl_entity_import']['db_legend']	= 'Datenbankeinstellungen';

/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_entity_import']['new']		= array('Neuer Import', 'Einen neuen Import anlegen');
$GLOBALS['TL_LANG']['tl_entity_import']['show']		= array('Import-Details', 'Details von Import ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_entity_import']['edit']		= array('Import bearbeiten', 'Import ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_entity_import']['copy']		= array('Import kopieren', 'Import ID %s duplizieren');
$GLOBALS['TL_LANG']['tl_entity_import']['delete']	= array('Import löschen', 'Import ID %s löschen');