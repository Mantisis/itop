<?php
// Copyright (C) 2010-2017 Combodo SARL
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
 * Handles various ajax requests - called through pages/exec.php
 *
 * @copyright   Copyright (C) 2010-2017 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

if (!defined('__DIR__')) define('__DIR__', dirname(__FILE__));
require_once(APPROOT.'application/webpage.class.inc.php');
require_once(APPROOT.'application/ajaxwebpage.class.inc.php');
require_once(APPROOT.'application/utils.inc.php');
require_once(APPROOT.'core/log.class.inc.php');
IssueLog::Enable(APPROOT.'log/error.log');

require_once(__DIR__.'/runtimeenvconnector.class.inc.php');
require_once(__DIR__.'/db_analyzer.class.inc.php');

require_once(APPROOT.'setup/backup.class.inc.php');
require_once(APPROOT.'core/mutex.class.inc.php');
require_once(APPROOT.'core/dict.class.inc.php');

/**
 * Overload of DBBackup to handle logging
 */
class DBBackupBeforeCompile extends DBBackup
{
	protected $aInfos = array();
	protected $aErrors = array();

	protected function LogInfo($sMsg)
	{
		$aInfos[] = $sMsg;
	}

	protected function LogError($sMsg)
	{
		IssueLog::Error($sMsg);
		$aErrors[] = $sMsg;
	}

	public function GetInfos()
	{
		return $this->aInfos;
	}

	public function GetErrors()
	{
		return $this->aErrors;
	}
}


function DoBackup($sTargetFile)
{
	// Make sure the target directory exists
	$sBackupDir = dirname($sTargetFile);
	SetupUtils::builddir($sBackupDir);

	$oBackup = new DBBackupBeforeCompile();
	$oBackup->SetMySQLBinDir(MetaModel::GetConfig()->GetModuleSetting('itop-backup', 'mysql_bindir', ''));
	$sSourceConfigFile = APPCONF.utils::GetCurrentEnvironment().'/'.ITOP_CONFIG_FILE;

	$oMutex = new iTopMutex('backup.'.utils::GetCurrentEnvironment());
	$oMutex->Lock();
	try
	{
		$oBackup->CreateCompressedBackup($sTargetFile, $sSourceConfigFile);
	}
	catch (Exception $e)
	{
		$oMutex->Unlock();
		throw $e;
	}
	$oMutex->Unlock();
}


try
{
	utils::PushArchiveMode(false);

	ini_set('max_execution_time', max(3600, ini_get('max_execution_time'))); // Under Windows SQL/backup operations are part of the timeout and require extra time
	ini_set('display_errors', 1); // Make sure that fatal errors remain visible from the end-user

	// Most of the ajax call are done without the MetaModel being loaded
	// Therefore, the language must be passed as an argument,
	// and the dictionnaries be loaded here
	$sLanguage = utils::ReadParam('language', '');
	if ($sLanguage != '')
	{
        // Pre 2.3.0 dictionaries
		foreach(glob(__DIR__.'/*.dict.*.php') as $sFilePath)
		{
			require_once($sFilePath);
		}
        // 2.3.0 an newer
        foreach(glob(APPROOT.'env-production/dictionaries/*.dict.php') as $sFilePath)
		{
			require_once($sFilePath);
		}

		
		$aLanguages = Dict::GetLanguages();
		if (array_key_exists($sLanguage, $aLanguages))
		{
			Dict::SetUserLanguage($sLanguage);
		}
	}
	$sOperation = utils::ReadParam('operation', '');
	switch($sOperation)
	{
		case 'check_backup':
		$oPage = new ajax_page("");
		$oPage->no_cache();
		$oPage->SetContentType('text/html');

		$sDBBackupPath = utils::ReadParam('target_file', '', false, 'raw_data');
		$fFreeSpace = SetupUtils::CheckDiskSpace($sDBBackupPath);
		$sMessage = '';
		if ($fFreeSpace !== false)
		{
			$sMessage = htmlentities(Dict::Format('ITSM-Designer:BackupFreeIn', SetupUtils::HumanReadableSize($fFreeSpace), dirname($sDBBackupPath)), ENT_QUOTES, 'UTF-8');
		}
		$oPage->add($sMessage);
		break;


		case 'do_backup':
		require_once(APPROOT.'/application/startup.inc.php');
		require_once(APPROOT.'/application/loginwebpage.class.inc.php');
		LoginWebPage::DoLogin(true); // Check user rights and prompt if needed (must be admin)

		$oPage = new ajax_page("");
		$oPage->no_cache();
		$oPage->SetContentType('text/html');
		try
		{
			set_time_limit(0);
			$sBackupFile = utils::ReadParam('target_file', '', false, 'raw_data');
			DoBackup($sBackupFile);
			$oPage->add('*backup*OK*');

		}
		catch (Exception $e)
		{
			$oPage->p('Error: '.$e->getMessage());
		}
		break;


		case 'test_design':
		case 'move_to_production':
		$oPage = new ajax_page("");
		$oPage->no_cache();
		$oPage->SetContentType('text/html');

		$iRevision = utils::ReadParam('revision_id', 0);
		$sComment = utils::ReadParam('comment', '', false, 'raw_data');
		$sTargetEnv = utils::ReadParam('target_env', 'test', false, 'raw_data');
		$bReloadProdDB = (utils::ReadParam('reload_prod_db', false) == 'true');

		$sAuthent = utils::ReadParam('authent', '', false, 'raw_data');
		if ($sAuthent != file_get_contents(APPROOT.'data/designer/compile_authent'))
		{
			throw new Exception('Wrong authentication token');
		}
		$sDelta = file_get_contents(APPROOT.'data/designer/compile_delta');
		if (strlen($sDelta) == 0)
		{
			throw new Exception('Empty or not readable delta file');
		}
		// Cleanup before expanding the zip archive
		if (is_dir(APPROOT.'data/'.$sTargetEnv.'-modules/'))
		{
			SetupUtils::rrmdir(APPROOT.'data/'.$sTargetEnv.'-modules/');
		}
		$sModules = APPROOT.'data/designer/compile-modules/';
		// Reset the list of modules (uninstall)
		SetupUtils::builddir($sModules);
		SetupUtils::tidydir($sModules);
		if (file_exists(APPROOT.'data/designer/compile_modules.zip'))
		{
			$oZip = new ZipArchive();
			if (!$oZip->open(APPROOT.'data/designer/compile_modules.zip'))
			{
				throw new Exception('Unable to open data/designer/compile_modules.zip for extraction');
			}
			$oZip->extractTo($sModules);
			$oZip->close();
		}

		$oRuntimeEnv = new RunTimeEnvironmentDesignerConnector($iRevision, $sComment, $sTargetEnv, false);
		$oRuntimeEnv->PushDelta($sDelta);
		$oRuntimeEnv->PushModules($sModules);

		try
		{
			$oRuntimeEnv->CheckDirectories($sTargetEnv);
			$oRuntimeEnv->CompileFrom('production');

			$oConfig = $oRuntimeEnv->MakeConfigFile('Test (built on '.date('Y-m-d').')');
			$oRuntimeEnv->WriteConfigFileSafe($oConfig);
			$oRuntimeEnv->InitDataModel($oConfig, true /* model only */);
	
			if ($sTargetEnv != 'production')
			{
				if ($bReloadProdDB || !MetaModel::DBExists(false))
				{
					$oRuntimeEnv->CloneDatabase('production');
				}
			}
			// Now I assume that there is a DB for this environment...
			$oRuntimeEnv->CreateDatabaseStructure($oConfig, 'upgrade');
	
			$oRuntimeEnv->UpdatePredefinedObjects();
			
			// Record the installation so that the about box (and the designer, if it's a MTP) know about the installed modules
			$aAvailableModules = $oRuntimeEnv->AnalyzeInstallation($oConfig, $oRuntimeEnv->GetBuildDir());
			$sDataModelVersion = $oRuntimeEnv->GetCurrentDataModelVersion();
			$aSelectedModules = array();
			foreach($aAvailableModules as $sModuleId => $aModule)
			{
				if (($sModuleId == ROOT_MODULE) || ($sModuleId == DATAMODEL_MODULE))
				{
					continue;
				}
				else
				{
					$aSelectedModules[] = $sModuleId;
				}
			}
			$oExtensionsMap = new iTopExtensionsMap();
			// Default choices = as before
			$oExtensionsMap->LoadChoicesFromDatabase($oConfig);
			foreach($oExtensionsMap->GetAllExtensions() as $oExtension)
			{
				// Plus all "remote" extensions
				if ($oExtension->sSource == iTopExtension::SOURCE_REMOTE)
				{
					$oExtensionsMap->MarkAsChosen($oExtension->sCode);
				}
			}
			$aSelectedExtensionCodes = array();
			foreach($oExtensionsMap->GetChoices() as $oExtension)
			{
				$aSelectedExtensionCodes[] = $oExtension->sCode;
			}
			$aSelectedExtensions = $oExtensionsMap->GetChoices();
			$oRuntimeEnv->RecordInstallation($oConfig, $sDataModelVersion, $aSelectedModules, $aSelectedExtensionCodes, 'Done by the iTop Designer Connector');

			$oRuntimeEnv->Commit();

			// Report the success in a way that will be detected by the ajax caller
			$oPage->add('<!-- compiled-with-success -->');

			// Note: at this point, the dictionnary has been loaded...
			$oPage->add(Dict::S('ITSM-Designer:CompiledOK'));
			
		}
		catch (Exception $e)
		{
			// Cleanup the files so that nobody could use them at a later time
			unlink(APPROOT.'data/designer/compile_authent');
			unlink(APPROOT.'data/designer/compile_delta');

			// Note: at this point, the dictionnary is not necessarily loaded
			$oPage->add(get_class($e).': '.htmlentities($e->GetMessage(), ENT_QUOTES, 'utf-8'));
			//echo "<p>Debug trace: <pre>".$e->getTraceAsString()."</pre></p>\n";
			IssueLog::Error(get_class($e).': '.$e->getMessage());
		}
		break;
		
		case 'check_integrity':
		$oPage = new ajax_page("");
		$oPage->no_cache();
		$oPage->SetContentType('text/html');

		$iRevision = utils::ReadParam('revision_id', 0);
		$sTargetEnv = utils::ReadParam('target_env', 'test', false, 'raw_data');
		$sUsage = utils::ReadParam('usage', 'test', false, 'raw_data');

		$sAuthent = utils::ReadParam('authent', '', false, 'raw_data');
		if ($sAuthent != file_get_contents(APPROOT.'data/designer/compile_authent'))
		{
			throw new Exception('Wrong authentication token');
		}
		// Cleanup the files so that nobody could use them at a later time
		unlink(APPROOT.'data/designer/compile_authent');
		unlink(APPROOT.'data/designer/compile_delta');

		$oRuntimeEnv = new RunTimeEnvironment($sTargetEnv);
		$oConfig = new Config(APPCONF.$sTargetEnv.'/'.ITOP_CONFIG_FILE);
		$oRuntimeEnv->InitDataModel($oConfig, false /* model and DB */);

		// By default, allow 300 seconds for each query
		$iTimeLimit = MetaModel::GetModuleSetting('itsm-designer-connector', 'check_integrity_time_limit', 300);
		$oAnalyzer = new DatabaseAnalyzer($iTimeLimit);
		$aErrorsAndFixes = $oAnalyzer->CheckIntegrity();
		if (count($aErrorsAndFixes) > 0)
		{
			$oPage->add(Dict::S('ITSM-Designer:IntegrityKO'));
			$oPage->add('<div><a id="see_integrity_issues" class="CollapsibleLabel" href="#">'.Dict::S('ITSM-Designer:IntegrityKO-SeeMore').'</a></div>');
			$oPage->add('<div id="integrity_issues">');

			$aDisplayData = array();
			foreach ($aErrorsAndFixes as $sClass => $aClassIssues)
			{
				foreach ($aClassIssues as $sIssue => $aIssueData)
				{
					if (isset($aIssueData['values']))
					{
						$aCounts = array();
						foreach($aIssueData['values'] as $sValue => $iCount)
						{
							$aCounts[] = Dict::Format('ITSM-Designer:IntegrityIssue-ValueStats', "'".htmlentities($sValue, ENT_QUOTES, 'UTF-8')."'", $iCount);
						}
						$sCount = implode('<br/>', $aCounts);
					}
					else
					{
						$sCount = $aIssueData['count'];
					}
					$aDisplayData[] = array(
						'desc' => $sIssue,
						'count' => $sCount,
						'query' => '<span class="query">'.$aIssueData['query'].'</span>',
					);
					IssueLog::Error(html_entity_decode($sIssue, ENT_QUOTES, 'UTF-8'));
					IssueLog::Error("Use the following SQL query to find the impacted record(s):\n".$aIssueData['query']);
				}
			}
		
			$aDisplayConfig = array();
			$aDisplayConfig['desc'] = array('label' => Dict::S('ITSM-Designer:IntegrityIssue-Description'), 'description' => '');
			$aDisplayConfig['count'] = array('label' => Dict::S('ITSM-Designer:IntegrityIssue-Count'), 'description' => '');
			$aDisplayConfig['query'] = array('label' => Dict::S('ITSM-Designer:IntegrityIssue-Query'), 'description' => '');
			$oPage->table($aDisplayConfig, $aDisplayData);

			$oPage->add('</div>');
			$oPage->add_ready_script(
<<<EOF
			// Make the table appear beautiful
			$("#integrity_issues table tbody tr:even").addClass('even');
			$("#integrity_issues table.listResults").tableHover(); // hover tables

			$('#integrity_issues').hide();
			$('#see_integrity_issues').click( function () {
				$('#integrity_issues').slideToggle('normal');
				$('#see_integrity_issues').toggleClass('open'); return false;
			});
EOF
			);
		}
		else
		{
			// Report success to the ajax caller
			$oPage->add('<!-- checked-with-success -->');
			// Report success to the end-user
			$oPage->add(Dict::S('ITSM-Designer:IntegrityOK'));

			if ($sUsage != 'move_to_production')
			{
				// Jump automatically into the test environment
				$oRuntimeEnv = new RunTimeEnvironmentDesignerConnector($iRevision, '', $sTargetEnv, false);
				$oRuntimeEnv->JumpInto($oPage);
			}
		}
		break;
		
		default:
		$oPage = new ajax_page("");
		$oPage->no_cache();
		$oPage->SetContentType('text/html');
		$oPage->p("Invalid operation: '$sOperation'");
	}

	$oPage->output();
}
catch (Exception $e)
{
	// note: transform to cope with XSS attacks
	echo htmlentities($e->GetMessage(), ENT_QUOTES, 'utf-8');
	echo "<p>Debug trace: <pre>".$e->getTraceAsString()."</pre></p>\n";
	IssueLog::Error($e->getMessage());

	utils::PopArchiveMode();
}
