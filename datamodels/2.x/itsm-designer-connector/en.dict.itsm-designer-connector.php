<?php
/**
 * Localized data
 *
 * @copyright   Copyright (C) 2013 XXXXX
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

Dict::Add('EN US', 'English', 'English', array(
	'ITSM-Designer:Launch-Title' => '▶ ITSM Designer',

	'ITSM-Designer:Launch-Info' => 'As you enter the ITSM designer, some information about your installation will be transmitted. If this is the first time that this installation gets connected to the ITSM Designer, then you will be prompted for your licence key.',
	'ITSM-Designer:Launch-Button' => 'Jump into the ITSM Designer ▶',
	//'ITSM-Designer:Launching' => 'Your are being redirected to the ITSM Designer. Please wait...',
 	'Menu:ITSMDesignerMenu' => 'ITSM Designer',

	'ITSM-Designer:TestEnv-Label' => 'You are currently in TEST mode',
	'ITSM-Designer:TestEnv-BackButton' => 'Return to the Designer',

	'ITSM-Designer:Compile-Title' => 'ITSM Designer: your installation',
	'ITSM-Designer:BackupFreeIn' => '%1$s free in %2$s',
	'ITSM-Designer:BackupOK' => 'Backup successfully created',
	'ITSM-Designer:CompiledOK' => 'The code and database structure have been updated with success',
	'ITSM-Designer:CompiledKO-KeepCool' => 'The update has been aborted: the application has been left unchanged.',
	'ITSM-Designer:Compiling-MoveToProd' => 'Update your installation with your design',
	'ITSM-Designer:Compiling-MoveToProd+' => 'The code of your installation and the format of the dabase will be updated. Please confirm that you would like to move a new revision into production. Should you have any doubt, please take some time to use the test feature or use another dedicated test instance.',
	'ITSM-Designer:Compiling-Revision' => 'Planned updates',
	'ITSM-Designer:Compiling-RevLabel' => 'Label: %1$s',
	'ITSM-Designer:Compiling-RevDesc' => 'Description: %1$s',
	'ITSM-Designer:Compiling-UntaggedRev' => 'The changes are labelled as Revision #%1$s',
	'ITSM-Designer:Compiling-BackupTitle' => 'Backup',
	'ITSM-Designer:Compiling-CreateBackup' => 'Backup the Database before proceeding',
	'ITSM-Designer:Compiling-SaveBackupTo' => 'Save the backup to',
	'ITSM-Designer:Compiling-Confirm' => 'Update iTop now!',
	'ITSM-Designer:Compiling-Cancel' => '<< Cancel',
	'ITSM-Designer:Compiling-Execution' => 'Updating your installation',
	'ITSM-Designer:Compiling-Execution+' => '',
	'ITSM-Designer:JumpToProd' => 'Go to the updated application',
	'ITSM-Designer:JumpToTest' => 'Go to the TEST environment',
	'ITSM-Designer:JumpToProd-Unchanged' => 'Go to the (unchanged) application',
	'ITSM-Designer:BackToDesigner' => 'Go back to ITSM Designer',
	'ITSM-Designer:Compiling-ForTest' => 'Test your design',
	'ITSM-Designer:Compiling-ForTest+' => 'ITSM Designer is preparing a temporary TEST environment. When this is done you will be redirected into that TEST environment.',
//	'ITSM-Designer:Compiling-PleaseWait' => 'This operation requires a few seconds to complete. Please wait...',
	'ITSM-Designer:CompileIssue-WrongProduct' => 'Wrong product. The ITSM Designer assumes that your installation is for the product \'%1$s\' (found \'%2$s\' instead)',
	'ITSM-Designer:CompileIssue-WrongVersion' => 'Wrong version. The ITSM Designer assumes that your installation has the version \'%1$s\' (found \'%2$s\' instead)',
	'ITSM-Designer:CompileIssue-WrongSourceDir' => 'Wrong source directory. The ITSM Designer assumes that your installation uses \'%1$s\' (found \'%2$s\' instead)',
	'ITSM-Designer:CompileIssue-Modules' => 'The ITSM Designer assumes that your installation matches a given list of modules. You will have to run the setup again.',
	'ITSM-Designer:CompileIssue-ToInstall' => 'Modules to install (upgrade required): %1$s',
	'ITSM-Designer:CompileIssue-ToRemove' => 'Unexpected modules (new installation required): %1$s',

	'ITSM-Designer:IntegrityOK' => 'The database integrity has been verified with success',
	'ITSM-Designer:IntegrityKO' => 'The database integrity check has detected issues',
	'ITSM-Designer:IntegrityKO-SeeMore' => 'See the detailed report',
	'ITSM-Designer:IntegrityIssue-Description' => 'Issue',
	'ITSM-Designer:IntegrityIssue-Count' => 'Count',
	'ITSM-Designer:IntegrityIssue-Query' => 'SQL Query to check again',
	'ITSM-Designer:IntegrityIssue-ValueStats' => 'Value:&nbsp;%1$s Count:&nbsp;%2$d',

	'DBAnalyzer-Integrity-OrphanRecord' => 'Orphan record in `%1$s`, it should have its counterpart in table `%2$s`',
	'DBAnalyzer-Integrity-InvalidExtKey' => 'Invalid external key %1$s (column: `%2$s.%3$s`)',
	'DBAnalyzer-Integrity-MissingExtKey' => 'Missing external key %1$s (column: `%2$s.%3$s`)',
	'DBAnalyzer-Integrity-InvalidValue' => 'Invalid value for %1$s (column: `%2$s.%3$s`)',
	'DBAnalyzer-Integrity-UsersWithoutProfile' => 'Some user accounts have no profile at all',
));
?>
