<?php

$arrLang = &$GLOBALS['TL_LANG']['tl_entity_import_config'];

/**
 * Backend Modules
 */
$arrLang['import'][0] = 'Import ausführen';
$arrLang['import'][1] = 'Import ID %s ausführen';
$arrLang['dryRun'][0] = 'Testlauf';
$arrLang['dryRun'][1] = 'Testlauf ID %s ausführen';
$arrLang['headline'] = 'Import ID %s';
$arrLang['label'] = 'Klicken Sie &quot;Import ausführen&quot;, um den Importprozess zu starten.';


/**
 * Buttons
 */
$arrLang['new']		= array('Neue Konfiguration', 'Einen neuen Konfiguration anlegen');
$arrLang['show']		= array('Konfiguration-Details', 'Details von Konfiguration ID %s anzeigen');
$arrLang['edit']		= array('Konfiguration bearbeiten', 'Konfiguration ID %s bearbeiten');
$arrLang['copy']		= array('Konfiguration kopieren', 'Konfiguration ID %s duplizieren');
$arrLang['delete']	= array('Konfiguration löschen', 'Konfiguration ID %s löschen');

/**
 * Fields
 */
$arrLang['title']									= array('Titel', 'Geben Sie hier den Titel des Imports ein.');
$arrLang['description']								= array('Beschreibung', 'Geben Sie hier eine Beschreibung für diese Konfiguration ein. Sie erscheint in der Listenansicht.');
$arrLang['dbSourceTable']							= array('Quelltabelle', 'Wählen Sie hier die Tabelle aus, die als Quelle des Imports dienen soll.');
$arrLang['dbTargetTable']							= array('Zieltabelle', 'Wählen Sie hier die Tabelle aus, die als Ziel des Imports dienen soll.');
$arrLang['importerClass']							= array('Importerklasse', 'Wählen Sie hier die PHP-Klasse, die als Importer fungieren soll. Die Klasse muss eine Subklasse von "\\HeimrichHannot\\EntityImport\\Importer" sein.');
$arrLang['purgeBeforeImport']						= array('Datensätze in der Zieltabelle vor dem Import löschen', 'Wählen Sie diese Option, wenn in der Zieltabelle vor jedem Import Datensätze gelöscht werden sollen.');
$arrLang['whereClausePurge']						= array('WHERE-Bedingungen für das Löschen', 'Geben Sie hier SQL-Bedingungen in der Form "pid=27 AND id=1" ein, die für das Löschen von Datensätzen vor jedem Import gelten sollen.');
$arrLang['dbFieldMapping']							= array('Felderabbildung', 'Wählen Sie hier aus, welche Felder der Quelltabelle auf welche der Zieltabelle abgebildet werden sollen.');
$arrLang['dbFieldMapping']['type']					= array('Typ', 'Wählen Sie \'Quellfeld\', um den Wert eines Feldes in der Quelltabelle in das entsprechende Feld der Zieltabelle zu schreiben. Für einfache Werte nutzen Sie \'Wert\'.');
$arrLang['dbFieldMapping']['type']['source']		= 'Quellfeld';
$arrLang['dbFieldMapping']['type']['foreignKey']	= 'Fremdschlüssel (Quelle)';
$arrLang['dbFieldMapping']['type']['value']			= 'Wert';
$arrLang['dbFieldMapping']['source']				= array('Quellfeld', 'Wählen Sie hier nur dann ein Feld aus, wenn Sie als Typ \'Quellfeld\' oder \'Fremdschlüssel\' gewählt haben.');
$arrLang['dbFieldMapping']['value']					= array('Wert / Fremdschlüssel', 'Geben Sie hier nur dann einen Wert oder Fremdschlüssel-Feld ein, wenn Sie als Typ \'Wert\' oder \'Fremdschlüssel\' gewählt haben.');
$arrLang['dbFieldMapping']['target']				= array('Zielfeld', 'Wählen Sie hier das Feld in der Zieltabelle aus, in das importiert werden soll.');
$arrLang['useTimeInterval']							= array('Zeitraum angeben', 'Geben Sie einen Zeitraum an.');
$arrLang['start']									= array('Startzeit', 'Wählen Sie hier die Startzeit eines temporalen Filters aus.');
$arrLang['end']										= array('Endzeit', 'Wählen Sie hier die Endzeit eines temporalen Filters aus.');
$arrLang['whereClause']								= array('WHERE-Bedingungen', 'Geben Sie hier Bedingungen für die WHERE-Klausel in der Form "pid=27 AND id=1" ein.');
$arrLang['sourceDir']								= array('Quellverzeichnis', 'Wählen Sie hier das Quellverzeichnis für Dateiimporte aus.');
$arrLang['targetDir']								= array('Zielverzeichnis', 'Wählen Sie hier das Zielverzeichnis für Dateiimporte aus.');
$arrLang['catContao']								= array('Nachrichten-Kategorien', 'Wählen Sie hier die Kategorien aus, die den importierten Nachrichten zugewiesen werden sollen.');
$arrLang['newsArchive']								= array('Nachrichtenarchiv', 'Wählen Sie hier das Nachrichtenarchiv aus, in das die Nachrichten importiert werden sollen.');
$arrLang['sourceFile']								= array('Quell-Datei', 'Wählen Sie hier die zu Quell-Datei für den Import aus.');
$arrLang['delimiter']								= array('Feld-Trennzeichen', 'Geben Sie hier das Feld-Trennzeichen ein.');
$arrLang['arrayDelimiter']							= array('Array-Trennzeichen', 'Geben Sie hier das Trennzeichen für die Umwandlung von trennzeichen-separierten Feldwerten ein. Wenn das entsprechende Häkchen in der Felderabbildung gesetzt ist, werden Werte wie "1;4;5" zu einem serialisierten Array transformiert.');
$arrLang['enclosure']								= array('Text-Trennzeichen', 'Geben Sie hier das Text-Trennzeichen ein.');
$arrLang['fileFieldMapping']						= array('Felderabbildung', 'Wählen Sie hier aus, welche Felder der Quelldatei auf welche der Zieltabelle abgebildet werden sollen.');
$arrLang['fileFieldMapping']['type']				= array('Typ', 'Wählen Sie \'Spalte\', um den Wert einer Spalte in der Quell in das entsprechende Feld der Zieltabelle zu schreiben. Für einfache Werte nutzen Sie \'Wert\'.');
$arrLang['fileFieldMapping']['type']['source']		= 'Spalte';
$arrLang['fileFieldMapping']['type']['value']		= 'Wert';
$arrLang['fileFieldMapping']['source']				= array('Quellspalte', 'Geben Sie hier nur die Position der Spalte ein, wenn Sie als Typ \'Quellspalte\' gewählt haben. Für die erste Spalte in der Datei geben Sie bspw. 1 ein.');
$arrLang['fileFieldMapping']['value']				= array('Wert', 'Geben Sie hier nur dann einen Wert ein, wenn Sie als Typ \'Wert\' gewählt haben.');
$arrLang['fileFieldMapping']['target']				= array('Zielfeld', 'Wählen Sie hier das Feld in der Zieltabelle aus, in das importiert werden soll.');
$arrLang['fileFieldMapping']['transformToArray']	= array('Zu Array<br>transformieren', 'Wählen Sie diese Option, um Werte wie \'1;4;5\' zu einem serialisierten Array zu transformieren.');

/**
 * Legends
 */
$arrLang['title_legend']	= 'Titel und Beschreibung';
$arrLang['config_legend']	= 'Konfiguration';
$arrLang['category_legend']	= 'Nachrichten-Kategorien';

/**
 * Misc
 */
$arrLang['createNewContentElement'] = '&lt;Neues Inhaltselement anlegen&gt;';

/**
 * Messages
 */
$arrLang['confirm'] = 'Der Import wurde erfolgreich abgeschlossen.';
$arrLang['confirmDry'] = 'Der Import wurde erfolgreich geprüft.';
$arrLang['importerInfo'] = 'Für das Importieren wird die Klasse "%s" verwendet.';
$arrLang['newsDry'] = 'Trockenlauf: Nachricht "%s" wird beim Import bearbeitet.';
$arrLang['newsImport'] = 'Nachricht "%s" wurde erfolgreich importiert.';