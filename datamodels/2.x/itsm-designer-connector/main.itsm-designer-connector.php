<?php
require_once(APPROOT."application/nicewebpage.class.inc.php");

class ITSMDesignerConnectorMenus extends ModuleHandlerAPI
{
	public static function OnMenuCreation()
	{
		// Note: do not jump into the designer from the TEST environment because the instance history would be corrupted
		if (utils::GetCurrentEnvironment() == 'production')
		{
			// Add the admin menus
			if (UserRights::IsAdministrator())
			{
				$oAdminMenu = new MenuGroup('AdminTools', 80 /* fRank */);
				new WebPageMenuNode('ITSMDesignerMenu', utils::GetAbsoluteUrlModulePage('itsm-designer-connector', 'launch.php'), $oAdminMenu->GetIndex(), 9 /* fRank */);
			}
		}
	}
}

class ConnectorPageExtension implements iPageUIExtension
{
	/**
	 * Add content to the North pane
	 * @param iTopWebPage $oPage The page to insert stuff into.
	 * @return string The HTML content to add into the page
	 */
	public function GetNorthPaneHtml(iTopWebPage $oPage)
	{
		if (utils::GetCurrentEnvironment() != 'production')
		{
			$aArguments = array(
				'switch_env' => 'production'
			);
			$sLaunchUrl = utils::GetAbsoluteUrlModulePage('itsm-designer-connector', 'launch.php', $aArguments, 'production');
			$sStyle = 'background-color: #FFEEEE; padding: 8px; text-align: center; padding:5px;';
			$sHtml = '<div style="'.$sStyle.'">';
			$sHtml .= Dict::S('ITSM-Designer:TestEnv-Label');
			$sHtml .= '&nbsp;<button onclick="window;location.href=\''.addslashes($sLaunchUrl).'\'">'.Dict::S('ITSM-Designer:TestEnv-BackButton').'</button>';
			$sHtml .= '</div>';
		}
		else
		{
			$sHtml = '';
		}
		return $sHtml;
	}

	/**
	 * Add content to the South pane
	 * @param iTopWebPage $oPage The page to insert stuff into.
	 * @return string The HTML content to add into the page
	 */
	public function GetSouthPaneHtml(iTopWebPage $oPage)
	{
	}

	/**
	 * Add content to the "admin banner"
	 * @param iTopWebPage $oPage The page to insert stuff into.
	 * @return string The HTML content to add into the page
	 */
	public function GetBannerHtml(iTopWebPage $oPage)
	{
	}
}

class DesignerUpdate extends DBObject
{
	public static function Init()
	{
		$aParams = array
		(
			"category" => "designer",
			"key_type" => "autoincrement",
			"name_attcode" => "revision_id",
			"state_attcode" => "",
			"reconc_keys" => array('revision_id'),
			"db_table" => "priv_designer_update",
			"db_key_field" => "id",
			"db_finalclass_field" => "",
			"display_template" => "",
		);
		MetaModel::Init_Params($aParams);

		MetaModel::Init_AddAttribute(new AttributeInteger("revision_id", array("allowed_values"=>null, "sql"=>"revision_id", "default_value"=>null, "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeDateTime("compilation_date", array("allowed_values"=>null, "sql"=>"compilation_date", "default_value"=>null, "is_null_allowed"=>false, "depends_on"=>array())));
		MetaModel::Init_AddAttribute(new AttributeText("comment", array("allowed_values"=>null, "sql"=>"comment", "default_value"=>null, "is_null_allowed"=>true, "depends_on"=>array())));
	}
}

class ITSMDesignerConnectorUtils
{
	static protected function CreateUUID($namespace = '')
	{    
		static $guid = '';
		$uid = uniqid("", true);
		$data = $namespace;
		$data .= $_SERVER['REQUEST_TIME'];
		$data .= $_SERVER['HTTP_USER_AGENT'];
		$data .= isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : ''; //Missing in IIS
		$data .= $_SERVER['SERVER_PORT'];
		$data .= $_SERVER['REMOTE_ADDR'];
		$data .= $_SERVER['REMOTE_PORT'];
		$hash = strtoupper(hash('ripemd128', $uid . $guid . md5($data)));
		$guid = '{' .  
			substr($hash,  0,  8) .
			'-' .
			substr($hash,  8,  4) .
			'-' .
			substr($hash, 12,  4) .
			'-' .
			substr($hash, 16,  4) .
			'-' .
			substr($hash, 20, 12) .
		'}';
		return $guid;
	}
	
	static function CollectConfiguration()
	{
		$aConfiguration = array('php' => array(), 'mysql' => array(), 'apache' => array());
	
		// Database information
		$class = new ReflectionClass('CMDBSource');
		$m_oMysqli_property = $class->getProperty('m_oMysqli');
		$m_oMysqli_property->setAccessible(true);
		$m_oMysqli = $m_oMysqli_property->getValue();
	
	
		$aConfiguration['database_settings']['server'] = (string) $m_oMysqli->server_version;
		$aConfiguration['database_settings']['client'] = (string) $m_oMysqli->client_version;
	
	
		/** @var mysqli_result $resultSet */
		$result = CMDBSource::Query('SHOW VARIABLES LIKE "%max_allowed_packet%"');
		if ($result)
		{
			$row = $result->fetch_object();
			$aConfiguration['database_settings']['max_allowed_packet'] = (string) $row->Value;
		}
	
		/** @var mysqli_result $resultSet */
		$result = CMDBSource::Query('SHOW VARIABLES LIKE "%version_comment%"');
		if ($result)
		{
			$row = $result->fetch_object();
			if (preg_match('/mariadb/i', $row->Value))
			{
				$aConfiguration['database_name'] = 'MariaDB';
			}
		}
	
		// Web server information
		if (function_exists('apache_get_version'))
		{
			$aConfiguration['web_server_name'] = 'apache';
			$aConfiguration['web_server_version'] = apache_get_version();
		}
		else
		{
			$aConfiguration['web_server_name'] = substr($_SERVER["SERVER_SOFTWARE"], 0, strpos($_SERVER["SERVER_SOFTWARE"], '/'));
			$aConfiguration['web_server_version'] = substr($_SERVER["SERVER_SOFTWARE"], strpos($_SERVER["SERVER_SOFTWARE"], '/'), strpos($_SERVER["SERVER_SOFTWARE"], 'PHP'));
		}
	
		// PHP extensions
		if (! MetaModel::GetConfig()->GetModuleSetting('itop-hub-connector', 'php_extensions_enable', true))
		{
			$aConfiguration['php_extensions'] = array();
		}
		else
		{
			foreach (get_loaded_extensions() as $extension)
			{
				$aConfiguration['php_extensions'][$extension] = $extension;
			}
		}
	
		// Collect some PHP settings having a known impact on iTop
		$aIniGet = array('post_max_size', 'upload_max_filesize', 'apc.enabled', 'timezone', 'memory_limit', 'max_execution_time');
		$aConfiguration['php_settings'] = array();
		foreach ($aIniGet as $iniGet)
		{
			$aConfiguration['php_settings'][$iniGet] = (string)ini_get($iniGet);
		}
	
		// iTop modules
		$oConfig = MetaModel::GetConfig();
		$sLatestInstallationDate = CMDBSource::QueryToScalar("SELECT max(installed) FROM ".$oConfig->GetDBSubname()."priv_module_install");
		// Get the latest installed modules, without the "root" ones (iTop version and datamodel version)
		$aInstalledModules = CMDBSource::QueryToArray("SELECT * FROM ".$oConfig->GetDBSubname()."priv_module_install WHERE installed = '".$sLatestInstallationDate."' AND parent_id != 0");
	
		foreach($aInstalledModules as $aDBInfo)
		{
			$aConfiguration['itop_modules'][$aDBInfo['name']] = $aDBInfo['version'];
		}
	
		// iTop Installation Options, i.e. "Extensions"
		$oExtensionMap = new iTopExtensionsMap();
		$oExtensionMap->LoadChoicesFromDatabase($oConfig);
		$aConfiguration['itop_extensions'] = array();
		$aConfiguration['itsm_designer_components'] = array();
		$aConfiguration['itsm_designer_components'] = array();
		foreach($oExtensionMap->GetChoices() as $oExtension)
		{
			switch ($oExtension->sSource)
			{
				case iTopExtension::SOURCE_MANUAL:
					$aConfiguration['itop_extensions'][$oExtension->sCode] = array('label' => $oExtension->sLabel, 'value' => $oExtension->sInstalledVersion);
					break;
						
				case iTopExtension::SOURCE_REMOTE:
					$aConfiguration['itsm_designer_components'][$oExtension->sCode] = array('label' => $oExtension->sLabel, 'value' => $oExtension->sInstalledVersion);
					break;
					 
				default:
					$aConfiguration['itop_installation_options'][$oExtension->sCode] = array('label' => $oExtension->sLabel, 'value' => $oExtension->sInstalledVersion);
			}
		}
		return $aConfiguration;
	}
	
	/**
	 * Return a cleaned (i.e. properly truncated) versin number from
	 * a very long version number like "7.0.18-0unbuntu0-16.04.1"
	 * @param string $sString
	 * @return string
	 */
	static function CleanVersionNumber($sString)
	{
		$aMatches = array();
		if (preg_match("|^([0-9\\.]+)-|", $sString, $aMatches))
		{
			return $aMatches[1];
		}
		return $sString;
	}
	
	static public function MakeLaunchForm($oP, $sButtonText)
	{
		$sDesignerLandingPageURL = MetaModel::GetModuleSetting('itsm-designer-connector', 'designer_url', '');
		$sInstanceUUIDFile = APPROOT.'data/instance.txt';
		Setuputils::builddir(APPROOT.'data');
		
		$aDataToPost = array();
		
		$aDataToPost['product'] = ITOP_APPLICATION;
		$aDataToPost['version'] = ITOP_VERSION.'-'.ITOP_REVISION;
		
		if (file_exists($sInstanceUUIDFile))
		{
			$sIntanceUUID = file_get_contents($sInstanceUUIDFile);
		}
		else
		{
			$sIntanceUUID = self::CreateUUID($aDataToPost['product']);
			file_put_contents($sInstanceUUIDFile, $sIntanceUUID);
		}
		$aDataToPost['instance_uuid'] = $sIntanceUUID;
		$aDataToPost['os_version'] = php_uname('s').'-'.php_uname('r');
		if (isset($_SERVER['SERVER_ADDR']))
		{
			$aDataToPost['ip_address'] = $_SERVER['SERVER_ADDR'];
		}
		elseif (isset($_SERVER['LOCAL_ADDR']))
		{
			// Windows IIS
			$aDataToPost['ip_address'] = $_SERVER['LOCAL_ADDR'];
		}
		else
		{
			$aDataToPost['ip_address'] = '?';
		}
		$aDataToPost['source_dir'] = MetaModel::GetConfig()->Get('source_dir'); // In which directory (datamodels/1.x, datamodels/2.x) are the installed modules
		// Safety net (old bug on Windows, fixed in SetupUtils::GetLatestDataModelDir)
		$aDataToPost['source_dir'] = str_replace('\\', '/', $aDataToPost['source_dir']);
		$aDataToPost['return_url'] = utils::GetAbsoluteUrlAppRoot(); // Where to go back from the designer
		
		$oProductionEnv = new RunTimeEnvironment();
		$aAvailableModules = $oProductionEnv->AnalyzeInstallation(MetaModel::GetConfig(), array(APPROOT.'env-production'));
		
		foreach($aAvailableModules as $sModuleId => $aModuleInfo)
		{
			if ($sModuleId != ROOT_MODULE)
			{
				if (array_key_exists('version_db', $aModuleInfo) && ($aModuleInfo['version_db'] != ''))
				{
					$aDataToPost['modules'][] = $sModuleId.'/'.$aModuleInfo['version_db'];
				}
			}
		}
		//$oP->add('<pre>'.print_r($aAvailableModules, true).'</pre>');
		
		$sCurrentEnv = utils::GetCurrentEnvironment();
		$aDataToPost['launched_from_env'] = $sCurrentEnv;
		
		$sDeltaFile = APPROOT.'data/'.$sCurrentEnv.'.delta.xml';
		if (file_exists($sDeltaFile))
		{
			$oDoc = new DOMDocument();
			$oDoc->load($sDeltaFile);
			$oRootNode = $oDoc->documentElement;
			$iRevision = $oRootNode->getAttribute('revision_id');
		}
		else
		{
			$iRevision = 0;
		}
		$aDataToPost['revision_id'] = $iRevision;
		
		$aDesignerUpdates = array();
		$oUpdateSearch = new DBObjectSearch('DesignerUpdate');
		$oUpdateSet = new DBObjectSet($oUpdateSearch);
		while ($oUpdate = $oUpdateSet->Fetch())
		{
			$aDesignerUpdates[] = array(
				'revision_id' => $oUpdate->Get('revision_id'),
				'compilation_date' => $oUpdate->Get('compilation_date'),
				'comment' => $oUpdate->Get('comment'),
			);
		}
		$aDataToPost['designer_updates'] = json_encode($aDesignerUpdates);
		
		$aProductUpgrades = array();
		
		$oUpdateSearch = new DBObjectSearch('ModuleInstallation');
		$oUpdateSet = new DBObjectSet($oUpdateSearch);
		$aDatesToParentId = array();
		while ($oUpdate = $oUpdateSet->Fetch())
		{
			if ($oUpdate->get('parent_id') == 0)
			{
				if ($oUpdate->Get('name') != 'datamodel')
				{
					$aDatesToParentId[$oUpdate->Get('installed')] = $oUpdate->GetKey();
				}
			}
		}
		$oUpdateSet->Rewind();
		while ($oUpdate = $oUpdateSet->Fetch())
		{
			$sKey = $oUpdate->Get('parent_id');
			if (($sKey != 0) && !array_key_exists($sKey, $aProductUpgrades))
			{
				$aProductUpgrades[$sKey] = array(
					'installed_modules' => array()
				);
			}
			if ($oUpdate->get('parent_id') == 0)
			{
				switch($oUpdate->Get('name'))
				{
					case 'datamodel':
					$sKey = $aDatesToParentId[$oUpdate->Get('installed')];
					$aProductUpgrades[$sKey]['datamodel_version'] = $oUpdate->Get('version');
					$aProductUpgrades[$sKey]['datamodel_comment'] = $oUpdate->Get('comment');
					break;
					
					default:
					$sKey = $oUpdate->GetKey();
					$aProductUpgrades[$sKey]['product_name'] = $oUpdate->Get('name');
					$aProductUpgrades[$sKey]['product_version'] = $oUpdate->Get('version');
					$aProductUpgrades[$sKey]['installation_date'] = $oUpdate->Get('installed');
				}
			}
			else
			{
				$aProductUpgrades[$sKey]['installed_modules'][] = $oUpdate->Get('name').'/'.$oUpdate->Get('version');
			}
		}
		ksort($aProductUpgrades);
		// remove the hash keys, create a normal, zero based, array
		$aArrayData = array();
		foreach($aProductUpgrades as $sKey => $aData)
		{
			$aArrayData[] = $aData;
		}
		$aDataToPost['product_upgrades'] = json_encode($aArrayData);
		
		$aConfiguration = static::CollectConfiguration();
		$aDataToPost['itop_extensions'] = json_encode($aConfiguration['itop_extensions']);
		$aDataToPost['itop_installation_options'] = json_encode($aConfiguration['itop_installation_options']);
		$aDataToPost['itsm_designer_components'] = json_encode($aConfiguration['itsm_designer_components']);
		$aDataToPost['server_stack'] = json_encode( array(
				'os_name'               => (string) PHP_OS,
				'web_server_name'       => (string) $aConfiguration['web_server_name'],
				'web_server_version'    => (string) $aConfiguration['web_server_version'],
				'database_name'         => (string) isset($aConfiguration['database_name']) ? $aConfiguration['database_name'] : 'MySQL',//if we do not detect MariaDB, we assume this is mysql
				'database_version'      => (string) CMDBSource::GetDBVersion(),
				'database_settings'     => (object) $aConfiguration['database_settings'],
				'php_version'           => (string) static::CleanVersionNumber(phpversion()),
				'php_settings'          => (object) $aConfiguration['php_settings'],
				'php_extensions'        => (object) $aConfiguration['php_extensions'],
		));
		
		
		$oP->add('<form id="launcher" action="'.$sDesignerLandingPageURL.'" method="post">');
		foreach($aDataToPost as $sKey => $value)
		{
			if (is_array($value))
			{
				foreach($value as $sVal)
				{
					$oP->add('<input type="hidden" name="'.$sKey.'[]" value="'.htmlentities($sVal, ENT_QUOTES, 'UTF-8').'">');
				}	
			}
			else
			{
				$oP->add('<input type="hidden" name="'.$sKey.'" value="'.htmlentities($value, ENT_QUOTES, 'UTF-8').'">');		
			}
		}
		$oP->add('<input type="submit" value="'.htmlentities($sButtonText, ENT_QUOTES, 'UTF-8').'">');
		$oP->add('</form>');
	}
}


class ITSMDesignerConnectorPage extends NiceWebPage
{
	public function __construct($sTitle)
	{
		parent::__construct($sTitle);

		$this->add_header("Cache-control: no-cache");

		$sImagesDir = utils::GetAbsoluteUrlAppRoot().'images';
		$sModuleImagesDir = utils::GetAbsoluteUrlModulesRoot().'itsm-designer-connector/images';
		$this->add_style(
<<<EOF
body {
    background: none repeat scroll 0 0 #555555;
    color: #FFFFFF;
    font-size: 9pt;
    overflow: auto;
}
.centered_box {
    background: none repeat scroll 0 0 #333333;
    border-color: #000000;
    border-style: solid ;
    border-width: 1px;
    margin-left: auto;
    margin-right: auto;
    margin-top: 100px;
    padding: 20px;
    width: 600px;
}
h1 {
    color: #FFFFFF;
}
#wiz_buttons > button, #wiz_buttons > form{
	margin: 20px;
}
#launcher {
	display: inline-block;
}
.header_message {
	color: black;
	margin-top: 10px;
}
.checkup_info {
	background: url("$sImagesDir/info-mini.png") no-repeat left;
	padding-left: 2em;
}
.checkup_error {
	background: url("$sImagesDir/validation_error.png") no-repeat left;
	padding-left: 2em;
}
.ui-state-default, .ui-widget-content .ui-state-default, .ui-widget-header .ui-state-default {
	background: url("$sModuleImagesDir/ui-bg_flat_35_555555_40x100.png") repeat-x scroll 50% 50% #555555;
	border: 1px solid #555555;
	color: #EEEEEE;
	font-weight: bold;
}
.ui-state-hover, .ui-widget-content .ui-state-hover, .ui-widget-header .ui-state-hover,
 .ui-state-focus, .ui-widget-content .ui-state-focus, .ui-widget-focus .ui-state-focus {
	background: url("$sModuleImagesDir/ui-bg_flat_33_F58400_40x100.png") repeat-x scroll 50% 50% #F58400;
	border: 1px solid #F58400;
	color: #EEEEEE;
	font-weight: bold;
}
.ui-corner-all, .ui-corner-bottom, .ui-corner-right, .ui-corner-br {
	border-bottom-right-radius: 0;
}
.ui-corner-all, .ui-corner-bottom, .ui-corner-left, .ui-corner-bl {
	border-bottom-left-radius: 0;
}
.ui-corner-all, .ui-corner-top, .ui-corner-right, .ui-corner-tr {
	border-top-right-radius: 0;
}
.ui-corner-all, .ui-corner-top, .ui-corner-left, .ui-corner-tl {
	border-top-left-radius: 0;
}
.ui-widget {
	font-family: Verdana,Arial,sans-serif;
	font-size: 1.1em;
}
.ui-button, .ui-button:link, .ui-button:visited, .ui-button:hover, .ui-button:active {
	text-decoration: none;
}
.ui-button {
	cursor: pointer;
	display: inline-block;
	line-height: normal;
	margin-right: 0.1em;
	overflow: visible;
	padding: 0;
	position: relative;
	text-align: center;
	vertical-align: middle;
}
#db_backup_path {
	width: 99%;
}
div#integrity_issues {
	background-color: #FFFFFF;
}
div#integrity_issues .query {
	font-size: smaller;
}

EOF
		);
	}
}
