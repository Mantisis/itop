<?php
// Copyright (C) 2013-2016 Combodo SARL
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
 * Class to manage a given environment made from the designer
 *
 * @copyright   Copyright (C) 2013-2017 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

require_once(APPROOT."setup/runtimeenv.class.inc.php");

require_once(APPROOT.'application/utils.inc.php');
require_once(APPROOT.'core/cmdbsource.class.inc.php');

require_once(APPROOT.'setup/modulediscovery.class.inc.php');
require_once(APPROOT.'setup/modelfactory.class.inc.php');
require_once(APPROOT.'setup/compiler.class.inc.php');



class RunTimeEnvironmentDesignerConnector extends RunTimeEnvironment
{
	protected $iRevision;
	protected $sComment;
	protected $sDeltaFile;

	/**
	 * Toolset for building a run-time environment, from the ITSM Designer
	 *
	 * @param int $iRevision Revision id
	 * @param string $sComment User comment (if any)
	 * @param string $sEnvironment (e.g. 'test')
	 * @param bool $bAutoCommit (make the target environment directly, or build a temporary one)
	 */
	public function __construct($iRevision, $sComment, $sEnvironment = 'production', $bAutoCommit = true)
	{
		parent::__construct($sEnvironment, $bAutoCommit);

		$this->iRevision = $iRevision;
		$this->sComment = $sComment;
		$this->sDeltaFile = APPROOT.'data/designer/'.$this->sTargetEnv.'/'.$this->iRevision.'.xml';
	}


	public function Commit()
	{
		if ($this->sFinalEnv != $this->sTargetEnv)
		{
			$this->CommitFile(
				APPROOT.'data/designer/'.$this->sTargetEnv.'/'.$this->iRevision.'.xml',
				APPROOT.'data/designer/'.$this->sFinalEnv.'/'.$this->iRevision.'.xml'
			);

			SetupUtils::tidydir(APPROOT.'data/designer/'.$this->sTargetEnv);
		}
		parent::Commit();
	}

	public function PushDelta($sDelta)
	{
		if (!file_exists(APPROOT.'data/designer'))
		{
			mkdir(APPROOT.'data/designer');
		}
		if (!file_exists(APPROOT.'data/designer/'.$this->sTargetEnv))
		{
			mkdir(APPROOT.'data/designer/'.$this->sTargetEnv);
		}
		// This is a copy, for troubleshooting purposes
		file_put_contents($this->sDeltaFile, $sDelta);

		// This is the real standard, that will be taken into account by the compiler + backup/restore
		$sDeltaFile = APPROOT.'data/'.$this->sTargetEnv.'.delta.xml';
		$sPreviousDeltaFile = APPROOT.'data/'.$this->sTargetEnv.'.delta.prev.xml';
		if (file_exists($sDeltaFile))
		{
			// to be restored in case an issue is encountered later on
			copy($sDeltaFile, $sPreviousDeltaFile);
		}
		file_put_contents($sDeltaFile, $sDelta);
	}

	public function PushModules($sSourceDir)
	{
		$sModulesDir = APPROOT.'data/'.$this->sTargetEnv.'-modules/';
		self::MakeDirSafe($sModulesDir);
		SetupUtils::copydir($sSourceDir, $sModulesDir);
	}

	public function RestorePreviousDelta()
	{
		$sDeltaFile = APPROOT.'data/'.$this->sTargetEnv.'.delta.xml';
		$sPreviousDeltaFile = APPROOT.'data/'.$this->sTargetEnv.'.delta.prev.xml';
		unlink($sDeltaFile);
		if (file_exists($sPreviousDeltaFile))
		{
			rename($sPreviousDeltaFile, $sDeltaFile);
		}
	}

	//public function InitDataModel($oConfig, $bModelOnly = true, $bUseCache = false)
	//public function AnalyzeInstallation($oConfig, $sModulesRelativePath)
	//public function RecordInstallation(Config $oConfig, $aSelectedModules, $sModulesRelativePath)

	public function CreateDatabaseStructure(Config $oConfig, $sMode)
	{
		parent::CreateDatabaseStructure($oConfig, $sMode);

		// Have it work fine even if the DB has been set in read-only mode for the users
		// (fix copied from RunTimeEnvironment::RecordInstallation)
		$iPrevAccessMode = $oConfig->Get('access_mode');
		$oConfig->Set('access_mode', ACCESS_FULL);

		// Now that the Database is ready for usage, keep track of this update
		$oLog = new DesignerUpdate();
		$oLog->Set('revision_id', $this->iRevision);
		$oLog->Set('comment', $this->sComment);
		$oLog->Set('compilation_date', time());
		$oLog->DBInsertNoReload();

		// Restore the previous access mode
		$oConfig->Set('access_mode', $iPrevAccessMode);
	}


	public function IsInstalled()
	{
		$sConfig = APPCONF.$this->sTargetEnv.'/'.ITOP_CONFIG_FILE;
		if (file_exists($sConfig))
		{
			return true;
		}
		else
		{
			return false;
		}
		
	}
	
	public function GetInstalledModules($sSourceEnv, $sSourceDir)
	{
		return parent::GetMFModulesToCompile($sSourceEnv, $sSourceDir);
	}

	public function MakeConfigFile($sEnvironmentLabel = null)
	{
		$oConfig = $this->GetConfig();
		if (!is_null($oConfig))
		{
			// Return the existing one
			$oConfig->UpdateIncludes('env-'.$this->sTargetEnv);
		}
		else
		{
			// Clone the default 'production' config file
			//
			$oConfig = clone($this->GetConfig('production'));
	
			$oConfig->UpdateIncludes('env-'.$this->sTargetEnv);
				
			if (is_null($sEnvironmentLabel))
			{
				$sEnvironmentLabel = $this->sTargetEnv;
			}
			$oConfig->Set('app_env_label', $sEnvironmentLabel);
			if ($this->sFinalEnv !== 'production')
			{
				$oConfig->SetDBName($oConfig->GetDBName().'_'.$this->sFinalEnv);
			}
		}

		return $oConfig;
	}

	protected function GetConfig($sEnvironment = null)
	{
		if (is_null($sEnvironment))
		{
			$sEnvironment = $this->sTargetEnv;
		}
		$sFile = APPCONF.$sEnvironment.'/'.ITOP_CONFIG_FILE;
		if (file_exists($sFile))
		{
			$oConfig = new Config($sFile);
			return $oConfig;
		}
		else
		{
			return null;
		}
	}

	public function CloneDatabase($sSourceEnv = 'production')
	{
		if ($sSourceEnv == $this->sTargetEnv)
		{
			throw new Exception("Attempting to clone the DB from the environment '$sSourceEnv' into itself!"); 
		}
		$oSourceConfig = $this->GetConfig($sSourceEnv);
		// Copy the 'production' database to the target environment (new_db_name)
		//$oP = new ajax_page('');

		$sHost = $oSourceConfig->GetDBHost();
		$sUser = $oSourceConfig->GetDBuser();
		$sPwd = $oSourceConfig->GetDBPwd();
		$sOldDBName = $oSourceConfig->GetDBName();
		$sPrefix = $oSourceConfig->GetDBSubname();

		// No need to specify the DB to use, the name will be used in each command
		CMDBSource::Init($sHost, $sUser, $sPwd);

		$sNewDBName = $oSourceConfig->GetDBName().'_'.$this->sFinalEnv;
		try
		{
			CMDBSource::DropDB($sNewDBName);
		}
		catch(MySQLException $e)
		{
			// Database may not already exist, never mind...
			// at least it will be clean !!
		}
		
		try
		{
			CMDBSource::CreateDB($sNewDBName);
		}
		catch(MySQLException $e)
		{
			// Database may already exist, never mind...
		}

//Parcourir la liste des tables utilisées par iTop (charger le data model de la prod, ou considérer l'ensemble des tables préfixées ????, ou voir le XML)
		// MySQL 5.0.2 will support this:
		// "SHOW FULL TABLES FROM `$sOldDBName` LIKE '$sPrefix%' WHERE Table_type = 'BASE TABLE'"
		$aTables = CMDBSource::QueryToArray("SHOW TABLES FROM `$sOldDBName` LIKE '$sPrefix%'");
		$sViewPrefix = $sPrefix.'view_';
		foreach ($aTables as $aRow)
		{
			$sTableName = $aRow[0];
			if (substr($sTableName, 0, strlen($sViewPrefix)) == $sViewPrefix)
			{
				// Skip the views (or they are copied as tables!)
			}
			else
			{
				CMDBSource::Query("DROP TABLE IF EXISTS `$sNewDBName`.`$sTableName`");
				CMDBSource::Query("CREATE TABLE `$sNewDBName`.`$sTableName` ENGINE=".MYSQL_ENGINE." DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci SELECT * FROM `$sOldDBName`.`$sTableName`");
			}
		}
	}

	public function JumpInto($oPage = null)
	{
		$sTargetUrl = utils::GetAbsoluteUrlAppRoot().'pages/UI.php?switch_env='.$this->sFinalEnv;
		if (is_null($oPage))
		{
			header('Location: '.$sTargetUrl);
			exit;
		}
		else
		{
			$oPage->add_ready_script("window.location.href='$sTargetUrl';");
		}
	}

	public function CheckDirectories($sTargetEnv)
	{
		$sTargetDir = APPROOT.'env-'.$sTargetEnv;
		$sBuildDir = $sTargetDir.'-build';

		self::CheckDirectory($sTargetDir);
		self::CheckDirectory($sBuildDir);
	}

	/**
	 * @param $sDir
	 * @throws Exception
	 */
	public static function CheckDirectory($sDir)
	{
		if (!is_dir($sDir))
		{
			if (!@mkdir($sDir,0770))
			{
				throw new Exception('Creating directory '.$sDir.' is denied (Check access rights)');
			}
		}
		// Try create a file
		$sTempFile = $sDir.'/__itop_temp_file__';
		if (!@touch($sTempFile))
		{
			throw new Exception('Write access to '.$sDir.' is denied (Check access rights)');
		}
		@unlink($sTempFile);
	}
	/**
	 * Wrappers for logging	
	 */	
	protected $aLog = array();

	protected function log_error($sText)
	{
		$this->aLog[] = "Error: $sText";
	}
	protected function log_warning($sText)
	{
		$this->aLog[] = "Warning: $sText";
	}
	protected function log_info($sText)
	{
		$this->aLog[] = "Info: $sText";
	}
	protected function log_ok($sText)
	{
		$this->aLog[] = "OK: $sText";
	}
} // End of class
