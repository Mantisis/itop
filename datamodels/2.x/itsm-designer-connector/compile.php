<?php
// Copyright (C) 2013-2017 Combodo SARL
//
//   This file is part of iTop.
//
//   iTop is free software; you can redistribute it and/or modify	
//   it under the terms of the GNU Affero General Public License as published by
//   the Free Software Foundation, either version 3 of the License, or
//   (at your option) any later version.
//
//   iTop is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU Affero General Public License for more details.
//
//   You should have received a copy of the GNU Affero General Public License
//   along with iTop. If not, see <http://www.gnu.org/licenses/>


/**
 * Compile the given delta into the given target environment
 * Called through pages/exec.php
 *
 * @copyright   Copyright (C) 2013-2017 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */


if (!defined('__DIR__')) define('__DIR__', dirname(__FILE__));
require_once(APPROOT.'application/application.inc.php');
require_once(APPROOT.'application/itopwebpage.class.inc.php');
require_once(APPROOT.'setup/runtimeenv.class.inc.php');

// Force the current environment to 'production'
$_REQUEST['switch_env'] = 'production';

require_once(APPROOT.'application/startup.inc.php');

require_once(APPROOT.'application/loginwebpage.class.inc.php');
LoginWebPage::DoLogin(true); // Check user rights and prompt if needed (must be admin)

$oAppContext = new ApplicationContext();

$oP = new ITSMDesignerConnectorPage(Dict::S('ITSM-Designer:Compile-Title'));
// For the style sheet to work
$oP->set_base(utils::GetAbsoluteUrlAppRoot().'pages/');
// To compensate the effect of the above statement !!
$oP->add_ready_script("$('a[href=#]').each( function() { $(this).attr('href', window.location.href+'#'); } )");

$sInstanceUUIDFile = APPROOT.'data/instance.txt';

$sProduct = utils::ReadParam('product', '', false, 'raw_data');
$sVersion = utils::ReadParam('version', '', false, 'raw_data');
$sInstanceUUID = utils::ReadParam('instance_uuid', '', false, 'raw_data');
$sSourceDir = utils::ReadParam('source_dir', '', false, 'raw_data');
$sTargetEnv = utils::ReadParam('target_env', 'test', false, 'raw_data');
$sOperation = utils::ReadParam('operation', 'test_design');
$aAssumedModules = utils::ReadParam('modules', array(), false, 'raw_data');
$bReloadProdDB = (utils::ReadParam('reload_prod_db', 'no') == 'yes');
$iRevision = utils::ReadParam('revision_id', 0);
$sComment = utils::ReadParam('comment', '', false, 'raw_data');
$sDelta = utils::ReadParam('delta_xml', '', false, 'raw_data');
$sBase64Components = utils::ReadParam('components', '', false, 'raw_data');
$sTagLabel = utils::ReadParam('tag_label', '', false, 'raw_data');
$sTagDescription = utils::ReadParam('tag_description', '', false, 'raw_data');

try
{
	// Check the consistency of the parameters: the provided delta assumes that the current installation has some characteristics
	//
	if ($sProduct != ITOP_APPLICATION)
	{
		throw new Exception(Dict::Format('ITSM-Designer:CompileIssue-WrongProduct', $sProduct, ITOP_APPLICATION));
	}
	if ($sVersion != ITOP_VERSION.'-'.ITOP_REVISION)
	{
		throw new Exception(Dict::Format('ITSM-Designer:CompileIssue-WrongVersion', $sVersion, ITOP_VERSION.'-'.ITOP_REVISION));
	}
	if (!file_exists($sInstanceUUIDFile))
	{
		throw new Exception('Missing file '.$sInstanceUUIDFile);
	}
	
	$sLocalInstanceUUID = file_get_contents($sInstanceUUIDFile);
	if ($sInstanceUUID != $sLocalInstanceUUID)
	{
		throw new Exception("Wrong instance id: the ITSM designer assumes $sInstanceUUID");
	}
	
	$sLocalSourceDir = MetaModel::GetConfig()->Get('source_dir'); // In which directory (datamodels/1.x, datamodels/2.x) are the installed modules
	// Safety net (old bug on Windows, fixed in SetupUtils::GetLatestDataModelDir)
	$sLocalSourceDir = str_replace('\\', '/', $sLocalSourceDir);
	if ($sSourceDir != $sLocalSourceDir)
	{
		throw new Exception(Dict::Format('ITSM-Designer:CompileIssue-WrongSourceDir', $sSourceDir, $sLocalSourceDir));
	}

	$oProductionEnv = new RunTimeEnvironment();
	$aAvailableModules = $oProductionEnv->AnalyzeInstallation(MetaModel::GetConfig(), array(APPROOT.'env-production'));
	$aInstalled = array();
	foreach($aAvailableModules as $sModuleId => $aModuleInfo)
	{
		if ($sModuleId != ROOT_MODULE)
		{
			if (array_key_exists('version_db', $aModuleInfo) && ($aModuleInfo['version_db'] != ''))
			{
				$aInstalled[] = $sModuleId.'/'.$aModuleInfo['version_db'];
			}
		}
	}
	$aToAdd = array();
	foreach ($aAssumedModules as $sModuleId)
	{
		if (!in_array($sModuleId, $aInstalled))
		{
			$aToAdd[] = $sModuleId;
		}
	}
	$aToRemove = array();
	foreach ($aInstalled as $sModuleId)
	{
		if (!in_array($sModuleId, $aAssumedModules))
		{
			$aToRemove[] = $sModuleId;
		}
	}
	if (count($aToAdd) || count($aToRemove))
	{
		$sMessage = Dict::Format('ITSM-Designer:CompileIssue-Modules');
		if (count($aToAdd))
		{
			$sToAdd = implode(', ', $aToAdd);
			$sMessage .= ' '.Dict::Format('ITSM-Designer:CompileIssue-ToInstall', $sToAdd);
		}
		if (count($aToRemove))
		{
			$sToRemove = implode(', ', $aToRemove);
			$sMessage .= ' '.Dict::Format('ITSM-Designer:CompileIssue-ToRemove', $sToRemove);
		}
		throw new Exception($sMessage);
	}

	// Transmit data to ajax.compile + ensure authentication
	// Reasons:
	// - ajax.compile cannot load the MetaModel to check that the user is logged in as administrator
	// - do not polute the list of loaded deltas with uncompiled ones (MTP not confirmed)
	//
	if (!file_exists(APPROOT.'data/designer'))
	{
		mkdir(APPROOT.'data/designer');
	}
	$sUID = uniqid($sTargetEnv.'-'.$iRevision, true);
	file_put_contents(APPROOT.'data/designer/compile_authent', $sUID);
	file_put_contents(APPROOT.'data/designer/compile_delta', $sDelta);
	if ($sBase64Components != '')
	{
		file_put_contents(APPROOT.'data/designer/compile_modules.zip', base64_decode($sBase64Components));
	}
	else
	{
		// Nothing posted: make sure that there is no 'compile_modules.zip' file
		@unlink(APPROOT.'data/designer/compile_modules.zip');
	}

	$oP->add_ready_script(
<<<EOF
$('button').button();
$('input[type="submit"]').button();
EOF
	);

	if ($sOperation == 'move_to_production')
	{
		$oP->add('<div id="mtp_step_1" class="centered_box">');
		$oP->add('<h1>'.Dict::S('ITSM-Designer:Compiling-MoveToProd').'</h1>');
		$oP->p(Dict::S('ITSM-Designer:Compiling-MoveToProd+'));

		if ($sComment != '')
		{
//			$oP->p(Dict::Format('ITSM-Designer:Compiling-Comment', htmlentities($sComment, ENT_QUOTES, 'UTF-8')));
		}
		$oP->add('<fieldset>');
		$oP->add('<legend>'.Dict::S('ITSM-Designer:Compiling-Revision').'</legend>');
		if ($sTagLabel != '')
		{
			$oP->p(Dict::Format('ITSM-Designer:Compiling-RevLabel', htmlentities($sTagLabel, ENT_QUOTES, 'UTF-8')));
			$oP->p(Dict::Format('ITSM-Designer:Compiling-RevDesc', htmlentities($sTagDescription, ENT_QUOTES, 'UTF-8')));
		}
		else
		{
			$oP->p(Dict::Format('ITSM-Designer:Compiling-UntaggedRev', $iRevision));
		}
		$oP->add('</fieldset>');

		// This code has been copied from the setup - WizStepInstallOrUpgrade::Display()
		//
		$sDBBackupPath = APPROOT.'data/backups/manual/'.strftime('before_mtp_%Y-%m-%d_%H_%M');
		$aBackupChecks = SetupUtils::CheckBackupPrerequisites($sDBBackupPath);
		$bCanBackup = true;
		$aMySQLDumpMessages = array();
		foreach($aBackupChecks as $oCheck)
		{
			if ($oCheck->iSeverity == CheckResult::ERROR)
			{
				$bCanBackup = false;
				$aMySQLDumpMessages[] = array(
					'class' => 'checkup_error',
					'message' => $oCheck->sLabel
				);
			}
			else
			{
				$aMySQLDumpMessages[] = array(
					'class' => 'checkup_info',
					'message' => $oCheck->sLabel
				);
			}
		}
		$sChecked = $bCanBackup ? ' checked ' : '';
		$sDisabled = $bCanBackup ? '' : ' disabled ';
		$oP->add('<fieldset>');
		$oP->add('<legend>'.Dict::S('ITSM-Designer:Compiling-BackupTitle').'</legend>');
		$oP->add('<div><input id="db_backup" type="checkbox" name="db_backup"'.$sChecked.$sDisabled.' value="1"/><label for="db_backup">&nbsp;'.Dict::S('ITSM-Designer:Compiling-CreateBackup').'</label></div>');
		$oP->p(Dict::S('ITSM-Designer:Compiling-SaveBackupTo').': <input id="db_backup_path" type="text" name="db_backup_path" '.$sDisabled.'value="'.htmlentities($sDBBackupPath, ENT_QUOTES, 'UTF-8').'"/>');
		$fFreeSpace = SetupUtils::CheckDiskSpace($sDBBackupPath);
		$sMessage = '';
		if ($fFreeSpace !== false)
		{
			$sMessage .= SetupUtils::HumanReadableSize($fFreeSpace).' free in '.dirname($sDBBackupPath);
		}
		$oP->add('<div id="backup_checkup">');
		foreach ($aMySQLDumpMessages as $aMsgData)
		{
			$oP->add('<p class="'.$aMsgData['class'].'">'.$aMsgData['message'].'</p>');
		}
		$oP->add('<p class="checkup_info"><span id="disk_space_info">'.$sMessage.'</span></p>');
		$oP->add('</div>');
		$oP->add('</fieldset>');
	
		$oP->add('<div id="wiz_buttons">');
		ITSMDesignerConnectorUtils::MakeLaunchForm($oP, Dict::S('ITSM-Designer:Compiling-Cancel'));
		$oP->add('<button id="confirm_mtp">'.Dict::S('ITSM-Designer:Compiling-Confirm').'</button>');
		$oP->add('</div>');

		$oP->add('</div>');
		$oP->add_ready_script(
<<<EOF
$('#db_backup').bind('change', function() {
	var bBackup = $(this).prop('checked');
	if (bBackup)
	{
		$('#db_backup_path').removeAttr('disabled');
	}
	else
	{
		$('#db_backup_path').attr('disabled', bBackup ? '' : 'disabled');
	}
});

$('#db_backup_path').bind('change keyup', function() {
	CheckBackupDirectory($('#db_backup_path').val());
});

$('#confirm_mtp').click(function (){
	$(this).attr("disabled", "disabled");
	var bBackup = $('#db_backup').prop('checked');
	if (bBackup)
	{
		DoBackupAndCompile($('#db_backup_path').val(), 'move_to_production');
	}
	else
	{
		DoCompileNow('move_to_production');
	}
});
EOF
		);
	}
	else
	{
		// Go right away
		$oP->add_ready_script("DoCompileNow('$sOperation', false);");
	}
	

	$oP->add('<div id="mtp_step_2" class="centered_box" style="display: none;">');

	if ($sOperation == 'move_to_production')
	{
		$oP->add('<h1>'.Dict::S('ITSM-Designer:Compiling-Execution').'</h1>');
	}
	else
	{
		$oP->add('<h1>'.Dict::S('ITSM-Designer:Compiling-ForTest').'</h1>');
	}

	$oP->add('<div id="backup_results" class="header_message" style="display: none;">');
	$oP->add('</div>');

	$oP->add('<div id="compile_results" class="header_message" style="display: none;">');
	$oP->add('</div>');

	$oP->add('<div id="checkintegrity_results" class="header_message" style="display: none;">');
	$oP->add('</div>');

	if ($sOperation == 'move_to_production')
	{
		$oP->add('<p id="compiling_message">'.Dict::S('ITSM-Designer:Compiling-Execution+').'</p>');
	}
	else
	{
		$oP->add('<p id="compiling_message">'.Dict::S('ITSM-Designer:Compiling-ForTest+').'</p>');
	}
	$oP->add('<div id="compiling_indicator" style="display: none;">');
	$oP->add('</div>');
	$oP->add_ready_script("$('#compiling_indicator').progressbar({ value: false });");

	if ($sOperation == 'move_to_production')
	{
		// Shown in case everything went well
		$oP->add('<div id="go_to_prod" style="display: none;">');
		$oP->add('<button onclick="window.location.href=\''.utils::GetAbsoluteUrlAppRoot().'\';">'.Dict::S('ITSM-Designer:JumpToProd').'</button>');
		$oP->add('</div>');
		// Shown in case an issue has been encountered during the compilation phase 
		$oP->add('<div id="go_to_prod_unchanged" style="display: none;">');
		$oP->add('<p>'.Dict::S('ITSM-Designer:CompiledKO-KeepCool').'</p>');
		$oP->add('<button onclick="window.location.href=\''.utils::GetAbsoluteUrlAppRoot().'\';">'.Dict::S('ITSM-Designer:JumpToProd-Unchanged').'</button>');
		$oP->add('</div>');
	}
	else
	{
		$oP->add('<div id="wiz_buttons_failure" style="display:none;">');
		ITSMDesignerConnectorUtils::MakeLaunchForm($oP, Dict::S('ITSM-Designer:BackToDesigner'));
		$oP->add('<button onclick="window.location.href=\''.utils::GetAbsoluteUrlAppRoot().'\';">'.Dict::S('ITSM-Designer:JumpToProd-Unchanged').'</button>');
		$oP->add('</div>');
		
		// Shown in case an issue has been encountered during the DB integrity check
		$oP->add('<div id="go_to_test" style="display: none;">');
		$oP->add('<button onclick="window.location.href=\''.utils::GetAbsoluteUrlAppRoot().'pages/UI.php?switch_env=test\'; return false;">'.Dict::S('ITSM-Designer:JumpToTest').'</button>');
		$oP->add('</div>');
	}

	$oP->add('</div>');
	
	$sReloadProdDB = $bReloadProdDB ? 'true' : 'false';
	$sAjax = utils::GetAbsoluteUrlModulePage('itsm-designer-connector', 'ajax.compile.php');
	$sAuthent = addslashes($sUID);
	$sEscapedComment = str_replace(array("\r\n", "\n", "\r"), '\\n', addslashes($sComment));
	$oP->add_dict_entry('ITSM-Designer:BackupOK');

	$sLanguage = addslashes(Dict::GetUserLanguage());

	$oP->add_script(
<<<EOF
function DoCheckDBIntegrity(sOperation)
{
	var oParams = {
		authent: '$sAuthent',
		language: '$sLanguage',
		operation: 'check_integrity',
		usage: sOperation,
		target_env: '$sTargetEnv'
	};
	var me = $(this);
	$.post('$sAjax', oParams, function(data) {
		$('#checkintegrity_results').append(data);
		if (/checked-with-success/i.test(data))
		{
			$('#checkintegrity_results').addClass('message_ok');
		}
		else
		{
			$('#checkintegrity_results').removeClass('message_ok');
			$('#checkintegrity_results').addClass('message_error');
		}
		$('#checkintegrity_results').slideToggle('normal', function() {
			$('#compiling_indicator').hide();
			$('#compiling_message').hide();
			$('#go_to_prod').show();
			$('#go_to_test').show();
		});
	});
}

function DoCompileNow(sOperation)
{
	var oParams = {
		authent: '$sAuthent',
		language: '$sLanguage',
		operation: sOperation,
		revision_id: $iRevision,
		comment: '$sEscapedComment',
		target_env: '$sTargetEnv',
		reload_prod_db: $sReloadProdDB
	};
	var me = $(this);
	$('#compiling_indicator').show();
	$('#mtp_step_1').hide();
	$('#mtp_step_2').show();
	$.post('$sAjax', oParams, function(data) {
		$('#compile_results').append(data);
		if (/compiled-with-success/i.test(data))
		{
			$('#compile_results').addClass('message_ok');
			DoCheckDBIntegrity(sOperation);
		}
		else
		{
			$('#compiling_indicator').hide();
			$('#compiling_message').hide();
			$('#compile_results').removeClass('message_ok');
			$('#compile_results').addClass('message_error');
			$('#go_to_prod_unchanged').show();
			$('#wiz_buttons_failure').show();
		}
		$('#compile_results').slideToggle('normal');
	});
}

function CheckBackupDirectory(sTargetFile)
{
	var oParams = {
		language: '$sLanguage',
		operation: 'check_backup',
		target_file: sTargetFile
	};
	var me = $(this);
	$.post('$sAjax', oParams, function(data) {
		$('#disk_space_info').html(data);
	});
}

function DoBackupAndCompile(sTargetFile, sOperation)
{
	var oParams = {
		language: '$sLanguage',
		operation: 'do_backup',
		target_file: sTargetFile
	};
	var me = $(this);
	$('#compiling_indicator').show();
	$('#mtp_step_1').hide();
	$('#mtp_step_2').show();
	$.post('$sAjax', oParams, function(data) {
		if (data == '*backup*OK*')
		{
			$('#backup_results').append(Dict.S('ITSM-Designer:BackupOK'));
			$('#backup_results').addClass('message_ok');
			$('#backup_results').slideToggle('normal');
			// Continue...
			DoCompileNow(sOperation);
		}
		else
		{
			$('#backup_results').append(data);
			$('#backup_results').addClass('message_error');
			$('#backup_results').slideToggle('normal');
			// Stop
			$('#compiling_indicator').hide();
			$('#compiling_message').hide();
			$('#confirm_mtp').removeAttr('disabled');
		}
	});
}
EOF
	);
}
catch (Exception $e)
{
	// note: transform to cope with XSS attacks
	$oP->p(htmlentities($e->GetMessage(), ENT_QUOTES, 'utf-8'));
	//$oP->p("Debug trace: <pre>".$e->getTraceAsString()."</pre>\n";
	IssueLog::Error($e->getMessage());
}

$oP->output();
