<?php
// Copyright (C) 2014-2017 Combodo SARL
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
 * Analyze a database to catch and report the most common inconsistencies
 *
 * @copyright   Copyright (C) 2014 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

class DatabaseAnalyzer
{
	var $iTimeLimitPerOperation;
	public function __construct($iTimeLimitPerOperation = null)
	{
		$this->iTimeLimitPerOperation = $iTimeLimitPerOperation;
	}

	private function ExecQuery($sSelWrongRecs, $sErrorDesc, $sClass, &$aErrorsAndFixes, $bReportValues = false)
	{
		if (!is_null($this->iTimeLimitPerOperation))
		{
			set_time_limit($this->iTimeLimitPerOperation);
		}

		$aWrongRecords = CMDBSource::QueryToArray($sSelWrongRecs, "id");
		if (count($aWrongRecords) > 0)
		{
			//$sRootClass = MetaModel::GetRootClass($sClass);
			foreach ($aWrongRecords as $aRes)
			{
				//$iRecordId = $aRes['id'];

				if (!isset($aErrorsAndFixes[$sClass][$sErrorDesc]))
				{
					$aErrorsAndFixes[$sClass][$sErrorDesc] = array(
						'count' => 1,
						'query' => $sSelWrongRecs
					);
				}
				else
				{
					$aErrorsAndFixes[$sClass][$sErrorDesc]['count'] += 1;
				}

				if ($bReportValues && isset($aRes['value']))
				{
					$value = $aRes['value'];
					if (!isset($aErrorsAndFixes[$sClass][$sErrorDesc]['values'][$value]))
					{
						$aErrorsAndFixes[$sClass][$sErrorDesc]['values'][$value] = 1;
					}
					else
					{
						$aErrorsAndFixes[$sClass][$sErrorDesc]['values'][$value] += 1;
					}
				}
			}
		}
	}

	public function CheckIntegrity()
	{
		if (!is_null($this->iTimeLimitPerOperation))
		{
			// Getting and setting time limit are not symetric:
			// www.php.net/manual/fr/function.set-time-limit.php#72305
			$iPreviousTimeLimit = ini_get('max_execution_time');
		}

		$aErrorsAndFixes = array();
	
		foreach (MetaModel::GetClasses() as $sClass)
		{
			if (!MetaModel::HasTable($sClass)) continue;
			$sRootClass = MetaModel::GetRootClass($sClass);
			$sTable = MetaModel::DBGetTable($sClass);
			$sKeyField = MetaModel::DBGetKey($sClass);
	
			if (!MetaModel::IsStandaloneClass($sClass))
			{
				if (MetaModel::IsRootClass($sClass))
				{
					// Check that the final class field contains the name of a class which inherited from the current class
					//
					$sFinalClassField = MetaModel::DBGetClassField($sClass);
	
					$aAllowedValues = MetaModel::EnumChildClasses($sClass, ENUM_CHILD_CLASSES_ALL);
					$sAllowedValues = implode(",", CMDBSource::Quote($aAllowedValues, true));
	
					$sSelWrongRecs = "SELECT DISTINCT  `$sTable`.`$sKeyField` AS id, `$sFinalClassField` AS value FROM `$sTable` WHERE `$sFinalClassField` NOT IN ($sAllowedValues)";
			// already done below when checking the allowed values
			//		$this->ExecQuery($sSelWrongRecs, "final class (field `<em>$sFinalClassField</em>`) is wrong (expected a value in {".$sAllowedValues."})", $sClass, $aErrorsAndFixes, true);
				}
				else
				{
					$sRootTable = MetaModel::DBGetTable($sRootClass);
					$sRootKey = MetaModel::DBGetKey($sRootClass);
					$sFinalClassField = MetaModel::DBGetClassField($sRootClass);
	
					$aExpectedClasses = MetaModel::EnumChildClasses($sClass, ENUM_CHILD_CLASSES_ALL);
					$sExpectedClasses = implode(",", CMDBSource::Quote($aExpectedClasses, true));
	
					// Check that any record found here has its counterpart in the root table
					//
					$sSelWrongRecs = "SELECT DISTINCT `$sTable`.`$sKeyField` AS id FROM `$sTable` LEFT JOIN `$sRootTable` ON `$sTable`.`$sKeyField` = `$sRootTable`.`$sRootKey` WHERE `$sRootTable`.`$sRootKey` IS NULL";
					$this->ExecQuery($sSelWrongRecs, Dict::Format('DBAnalyzer-Integrity-OrphanRecord', $sTable, $sRootTable), $sClass, $aErrorsAndFixes);
	
					// Check that any record found in the root table and referring to a child class
					// has its counterpart here (detect orphan nodes -root or in the middle of the hierarchy)
					//
					$sSelWrongRecs = "SELECT DISTINCT `$sRootTable`.`$sRootKey` AS id FROM `$sRootTable` LEFT JOIN `$sTable` ON `$sRootTable`.`$sRootKey` = `$sTable`.`$sKeyField` WHERE `$sTable`.`$sKeyField` IS NULL AND `$sRootTable`.`$sFinalClassField` IN ($sExpectedClasses)";
					$this->ExecQuery($sSelWrongRecs, Dict::Format('DBAnalyzer-Integrity-OrphanRecord', $sRootTable, $sTable), $sRootClass, $aErrorsAndFixes);
				}
			}
	
			foreach(MetaModel::ListAttributeDefs($sClass) as $sAttCode=>$oAttDef)
			{
				// Skip this attribute if not defined in this table
				if (!MetaModel::IsAttributeOrigin($sClass, $sAttCode)) continue;
	
				if ($oAttDef->IsExternalKey())
				{
					// Check that any external field is pointing to an existing object
					//
					$sRemoteClass = $oAttDef->GetTargetClass();
					$sRemoteTable = MetaModel::DBGetTable($sRemoteClass);
					$sRemoteKey = MetaModel::DBGetKey($sRemoteClass);
	
					$aCols = $oAttDef->GetSQLExpressions(); // Workaround a PHP bug: sometimes issuing a Notice if invoking current(somefunc())
					$sExtKeyField = current($aCols); // get the first column for an external key
	
					// Note: a class/table may have an external key on itself
					$sSelBase = "SELECT DISTINCT `$sTable`.`$sKeyField` AS id, `$sTable`.`$sExtKeyField` AS value FROM `$sTable` LEFT JOIN `$sRemoteTable` AS `{$sRemoteTable}_1` ON `$sTable`.`$sExtKeyField` = `{$sRemoteTable}_1`.`$sRemoteKey`";
	
					$sSelWrongRecs = $sSelBase." WHERE `{$sRemoteTable}_1`.`$sRemoteKey` IS NULL";
					// Exclude the records pointing to 0/null from the errors (separate test below)
					$sSelWrongRecs .= " AND `$sTable`.`$sExtKeyField` IS NOT NULL";
					$sSelWrongRecs .= " AND `$sTable`.`$sExtKeyField` != 0";
					$this->ExecQuery($sSelWrongRecs, Dict::Format('DBAnalyzer-Integrity-InvalidExtKey', $sAttCode, $sTable, $sExtKeyField), $sClass, $aErrorsAndFixes);

					if (!$oAttDef->IsNullAllowed())
					{
						$sSelWrongRecs = "SELECT DISTINCT `$sTable`.`$sKeyField` AS id FROM `$sTable` WHERE `$sTable`.`$sExtKeyField` IS NULL OR `$sTable`.`$sExtKeyField` = 0";
						$this->ExecQuery($sSelWrongRecs, Dict::Format('DBAnalyzer-Integrity-MissingExtKey', $sAttCode, $sTable, $sExtKeyField), $sClass, $aErrorsAndFixes);
					}
				}
				else if ($oAttDef->IsDirectField())
				{
					// Check that the values fit the allowed values
					//
					$aAllowedValues = MetaModel::GetAllowedValues_att($sClass, $sAttCode);
					if (!is_null($aAllowedValues) && count($aAllowedValues) > 0)
					{
						$sExpectedValues = implode(",", CMDBSource::Quote(array_keys($aAllowedValues), true));
	
						$aCols = $oAttDef->GetSQLExpressions(); // Workaround a PHP bug: sometimes issuing a Notice if invoking current(somefunc())
						$sMyAttributeField = current($aCols); // get the first column for the moment
						$sSelWrongRecs = "SELECT DISTINCT `$sTable`.`$sKeyField`, `$sTable`.`$sMyAttributeField` AS value FROM `$sTable` WHERE `$sTable`.`$sMyAttributeField` NOT IN ($sExpectedValues)";
						$this->ExecQuery($sSelWrongRecs, Dict::Format('DBAnalyzer-Integrity-InvalidValue', $sAttCode, $sTable, $sMyAttributeField), $sClass, $aErrorsAndFixes, true);
					}
				}
			}
		}


		// Check user accounts without profile
		$sUserTable = MetaModel::DBGetTable('User');
		$sLinkTable = MetaModel::DBGetTable('URP_UserProfile');
		$sSelWrongRecs = "SELECT DISTINCT u.`login` FROM `$sUserTable` AS u LEFT JOIN `$sLinkTable` AS l ON l.userid = u.id WHERE l.id IS NULL";
		$this->ExecQuery($sSelWrongRecs, Dict::S('DBAnalyzer-Integrity-UsersWithoutProfile'), 'User', $aErrorsAndFixes, true);


		if (!is_null($this->iTimeLimitPerOperation))
		{
			set_time_limit($iPreviousTimeLimit);
		}
		return $aErrorsAndFixes;
	}
}
