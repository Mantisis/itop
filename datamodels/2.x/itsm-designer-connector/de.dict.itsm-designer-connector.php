<?php
/**
 * Localized data
 *
 * @copyright   Copyright (C) 2013 XXXXX
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

Dict::Add('DE DE', 'German', 'Deutsch', array(
	'ITSM-Designer:Launch-Title' => '▶ ITSM Designer',

	//'ITSM-Designer:Launch-Info' => 'As you enter the ITSM designer, some information about your installation will be transmitted. If this is the first time that this installation gets connected to the ITSM Designer, then you will be prompted for your licence key.',
	'ITSM-Designer:Launch-Info' => 'Beim Start des ITSM Designers werden einige Informationen über ihre Installation übertragen. Wird von ihrer Installation zum ersten Mal eine Verbindung mit dem ITSM Designer hergestellt, so wird der Lizenzschlüssel abgefragt.',
	'ITSM-Designer:Launch-Button' => 'ITSM Designer starten ▶',
	//'ITSM-Designer:Launching' => 'Your are being redirected to the ITSM Designer. Please wait...',
 	'Menu:ITSMDesignerMenu' => 'ITSM Designer',

	'ITSM-Designer:TestEnv-Label' => 'Sie befinden sich im TEST-Modus',
	'ITSM-Designer:TestEnv-BackButton' => 'Zurück zum Designer',

	'ITSM-Designer:Compile-Title' => 'ITSM Designer: Ihre Installation',
	'ITSM-Designer:BackupFreeIn' => '%1$s frei in %2$s',
	'ITSM-Designer:BackupOK' => 'Backup erfolgreich erstellt',
	'ITSM-Designer:CompiledOK' => 'Code und Datenbank wurden erfolgreich aktualisiert',
	'ITSM-Designer:CompiledKO-KeepCool' => 'Update abgebrochen: Die Applikation wurde NICHT verändert.',
	'ITSM-Designer:Compiling-MoveToProd' => 'Aktualisieren der Applikation mit ihrem Design',
	//'ITSM-Designer:Compiling-MoveToProd+' => 'The code of your installation and the format of the dabase will be updated. Please confirm that you would like to move a new revision into production. Should you have any doubt, please take some time to use the test feature or use another dedicated test instance.',
	'ITSM-Designer:Compiling-MoveToProd+' => 'Der Code ihrer Installation und das Format der Datenbank wird upgedated. Bitte bestätigen sie, dass sie eine neue Revision nach Produktiv ausrollen wollen. Sollten sie Zweifel haben, nehmen sie sich die Zeit das Test-Feature zunutzen oder verwenden sie eine andere dedizierte iTop Test-Instanz',
	'ITSM-Designer:Compiling-Revision' => 'Geplante Updates',
	'ITSM-Designer:Compiling-RevLabel' => 'Label: %1$s',
	'ITSM-Designer:Compiling-RevDesc' => 'Beschreibung: %1$s',
	'ITSM-Designer:Compiling-UntaggedRev' => 'Die Änderungen sind mit Revision #%1$s gelabelt',
	'ITSM-Designer:Compiling-BackupTitle' => 'Backup',
	'ITSM-Designer:Compiling-CreateBackup' => 'Backup der Datenbank bevor es weiter geht',
	'ITSM-Designer:Compiling-SaveBackupTo' => 'Speichern des Backups in',
	'ITSM-Designer:Compiling-Confirm' => 'iTop-Instanz jetzt aktualisieren!',
	'ITSM-Designer:Compiling-Cancel' => '<< Abbrechen',
	'ITSM-Designer:Compiling-Execution' => 'Ihre Installation aktualisieren',
	'ITSM-Designer:Compiling-Execution+' => '',
	'ITSM-Designer:JumpToProd' => 'Gehe zur aktualisierten Applikation',
	'ITSM-Designer:JumpToTest' => 'Gehe zur TEST-Umgebung',
	'ITSM-Designer:JumpToProd-Unchanged' => 'Gehe zur (unveränderten) Applikation',
	'ITSM-Designer:BackToDesigner' => 'Zurück zur ITSM Designer',
	'ITSM-Designer:Compiling-ForTest' => 'Test des Designs',
	//'ITSM-Designer:Compiling-ForTest+' => 'ITSM Designer is preparing a temporary TEST environment. When this is done you will be redirected into that TEST environment.',
	'ITSM-Designer:Compiling-ForTest+' => 'Der ITSM Designer bereites eine temporäre Testumgebung vor. Wenn dies erfolgt ist, werden sie in die Testumgebung weitergeleitet.',
	//'ITSM-Designer:Compiling-PleaseWait' => 'This operation requires a few seconds to complete. Please wait...',
	'ITSM-Designer:CompileIssue-WrongProduct' => 'Falsches Produkt. Der ITSM Designer geht davon aus, dass ihre Installation für das Produkt \'%1$s\' durchgeführt wurde (stattdessen wurde \'%2$s\' gefunden)',
	'ITSM-Designer:CompileIssue-WrongVersion' => 'Falsche Version. Der ITSM Designer geht davon aus, dass ihre Installation für Version \'%1$s\' durchgeführt wurde (stattdessen wurde \'%2$s\' gefunden)',
	'ITSM-Designer:CompileIssue-WrongSourceDir' => 'Falsches Quellverzeichniss. Der ITSM Designer geht davon aus, dass ihre Installation \'%1$s\' nutzt (stattdessen wurde \'%2$s\' gefunden)',
	'ITSM-Designer:CompileIssue-Modules' => 'Der ITSM Designer geht davon aus, dass ihre Installation zu einer vorgegebenen Menge von Modulen passt. Sie müssen das Setup erneut ausführen.',
	'ITSM-Designer:CompileIssue-ToInstall' => 'zu instalierende Module (Upgrade erforderlich): %1$s',
	'ITSM-Designer:CompileIssue-ToRemove' => 'Unerwartete Module (Neuinstallation erforderlich): %1$s',

	'ITSM-Designer:IntegrityOK' => 'Die Datenbankintegrität wurde erfolgreich verifiziert.',
	'ITSM-Designer:IntegrityKO' => 'Bei der Prüfung der Datenbankintegrität hat Probleme festgestellt.',
	'ITSM-Designer:IntegrityKO-SeeMore' => 'Detailierten Bericht ansehen',
	'ITSM-Designer:IntegrityIssue-Description' => 'Problem',
	'ITSM-Designer:IntegrityIssue-Count' => 'Anzahl',
	'ITSM-Designer:IntegrityIssue-Query' => 'SQL Abfrage für weitere Prüfung',
	'ITSM-Designer:IntegrityIssue-ValueStats' => 'Wert:&nbsp;%1$s Anzahl:&nbsp;%2$d',

	'DBAnalyzer-Integrity-OrphanRecord' => 'Verweister Eintrag `%1$s`, das Gegenstück sollte in Tabelle `%2$s` sein',
	'DBAnalyzer-Integrity-InvalidExtKey' => 'Ungültiger externer Schlüssel %1$s (Spalte: `%2$s.%3$s`)',
	'DBAnalyzer-Integrity-MissingExtKey' => 'Fehlender externer Schlüssel %1$s (Spalte: `%2$s.%3$s`)',
	'DBAnalyzer-Integrity-InvalidValue' => 'Ungültiger Wert für %1$s (Spalte: `%2$s.%3$s`)',
	'DBAnalyzer-Integrity-UsersWithoutProfile' => 'Einige Benutzerkonten haben keine Profil.',
));