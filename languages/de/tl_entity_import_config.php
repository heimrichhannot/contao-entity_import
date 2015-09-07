<?php

/**
 * Backend Modules
 */
$GLOBALS['TL_LANG']['tl_entity_import_config']['import'][0] = 'Import ausführen';
$GLOBALS['TL_LANG']['tl_entity_import_config']['import'][1] = 'Import ID %s ausführen';
$GLOBALS['TL_LANG']['tl_entity_import_config']['dryRun'][0] = 'Testlauf';
$GLOBALS['TL_LANG']['tl_entity_import_config']['dryRun'][1] = 'Testlauf ID %s ausführen';
$GLOBALS['TL_LANG']['tl_entity_import_config']['headline'] = 'Import ID %s';
$GLOBALS['TL_LANG']['tl_entity_import_config']['label'] = 'Klicken Sie &quot;Import ausführen&quot;, um den Importprozess zu starten.';


/**
 * Buttons
 */
$GLOBALS['TL_LANG']['tl_entity_import_config']['new']		= array('Neue Konfiguration', 'Einen neuen Konfiguration anlegen');
$GLOBALS['TL_LANG']['tl_entity_import_config']['show']		= array('Konfiguration-Details', 'Details von Konfiguration ID %s anzeigen');
$GLOBALS['TL_LANG']['tl_entity_import_config']['edit']		= array('Konfiguration bearbeiten', 'Konfiguration ID %s bearbeiten');
$GLOBALS['TL_LANG']['tl_entity_import_config']['copy']		= array('Konfiguration kopieren', 'Konfiguration ID %s duplizieren');
$GLOBALS['TL_LANG']['tl_entity_import_config']['delete']	= array('Konfiguration löschen', 'Konfiguration ID %s löschen');

/**
 * Fields
 */
$GLOBALS['TL_LANG']['tl_entity_import_config']['title']				= array('Titel', 'Geben Sie hier den Titel des Imports ein.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['description']		= array('Beschreibung', 'Geben Sie hier eine Beschreibung für diese Konfiguration ein. Sie erscheint in der Listenansicht.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['dbSourceTable']		= array('Quelltabelle', 'Wählen Sie hier die Tabelle aus, die als Quelle des Imports dienen soll.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['dbTargetTable']		= array('Zieltabelle', 'Wählen Sie hier die Tabelle aus, die als Ziel des Imports dienen soll.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['importerClass']		= array('Importerklasse', 'Wählen Sie hier die PHP-Klasse, die als Importer fungieren soll. Die Klasse muss eine Subklasse von "\\HeimrichHannot\\EntityImport\\Importer" sein.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['purgeBeforeImport']	= array('Datensätze in der Zieltabelle vor dem Import löschen', 'Wählen Sie diese Option, wenn in der Zieltabelle vor jedem Import Datensätze gelöscht werden sollen.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['whereClausePurge']	= array('WHERE-Bedingungen für das Löschen', 'Geben Sie hier SQL-Bedingungen in der Form "pid=27 AND id=1" ein, die für das Löschen von Datensätzen vor jedem Import gelten sollen.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMapping']	= array('Felderabbildung', 'Wählen Sie hier aus, welche Felder der Quelltabelle auf welche der Zieltabelle abgebildet werden sollen.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['type']				= array('Typ', 'Wählen Sie \'Quellfeld\', um den Wert eines Feldes in der Quelltabelle in das entsprechende Feld der Zieltabelle zu schreiben. Für einfache Werte nutzen Sie \'Wert\'.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMappingOptions']['source']	= 'Quellfeld';
$GLOBALS['TL_LANG']['tl_entity_import_config']['dbFieldMappingOptions']['value']	= 'Wert';
$GLOBALS['TL_LANG']['tl_entity_import_config']['source']			= array('Quellfeld', 'Wählen Sie hier nur dann ein Feld aus, wenn Sie als Typ \'Quellfeld\' gewählt haben.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['value']				= array('Wert', 'Geben Sie hier nur dann einen Wert ein, wenn Sie als Typ \'Wert\' gewählt haben.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['target']			= array('Zielfeld', 'Wählen Sie hier das Feld in der Zieltabelle aus, in das importiert werden soll.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['useTimeInterval']	= array('Zeitraum angeben', 'Geben Sie einen Zeitraum an.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['start']				= array('Startzeit', 'Wählen Sie hier die Startzeit eines temporalen Filters aus.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['end']				= array('Endzeit', 'Wählen Sie hier die Endzeit eines temporalen Filters aus.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['whereClause']		= array('WHERE-Bedingungen', 'Geben Sie hier Bedingungen für die WHERE-Klausel in der Form "pid=27 AND id=1" ein.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['sourceDir']			= array('Quellverzeichnis', 'Wählen Sie hier das Quellverzeichnis für Dateiimporte aus.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['targetDir']			= array('Zielverzeichnis', 'Wählen Sie hier das Zielverzeichnis für Dateiimporte aus.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['catContao']			= array('Nachrichten-Kategorien', 'Wählen Sie hier die Kategorien aus, die den importierten Nachrichten zugewiesen werden sollen.');
$GLOBALS['TL_LANG']['tl_entity_import_config']['newsArchive']		= array('Nachrichtenarchiv', 'Wählen Sie hier das Nachrichtenarchiv aus, in das die Nachrichten importiert werden sollen.');


/**
 * Legends
 */
$GLOBALS['TL_LANG']['tl_entity_import_config']['title_legend']	= 'Titel und Beschreibung';
$GLOBALS['TL_LANG']['tl_entity_import_config']['config_legend']	= 'Konfiguration';
$GLOBALS['TL_LANG']['tl_entity_import_config']['category_legend']	= 'Nachrichten-Kategorien';

/**
 * Misc
 */
$GLOBALS['TL_LANG']['tl_entity_import_config']['createNewContentElement'] = '&lt;Neues Inhaltselement anlegen&gt;';

/**
 * Messages
 */
$GLOBALS['TL_LANG']['tl_entity_import_config']['confirm'] = 'Der Import wurde erfolgreich abgeschlossen.';
$GLOBALS['TL_LANG']['tl_entity_import_config']['confirmDry'] = 'Der Import wurde erfolgreich geprüft.';
$GLOBALS['TL_LANG']['tl_entity_import_config']['importerInfo'] = 'Für das Importieren wird die Klasse "%s" verwendet.';
$GLOBALS['TL_LANG']['tl_entity_import_config']['newsDry'] = 'Trockenlauf: Nachricht "%s" wird beim Import bearbeitet.';
$GLOBALS['TL_LANG']['tl_entity_import_config']['newsImport'] = 'Nachricht "%s" wurde erfolgreich importiert.';