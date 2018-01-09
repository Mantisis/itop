<?php
// Copyright (C) 2012-2017 Combodo SARL
//
//   This program is free software; you can redistribute it and/or modify
//   it under the terms of the GNU General Public License as published by
//   the Free Software Foundation; version 3 of the License.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of the GNU General Public License
//   along with this program; if not, write to the Free Software
//   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA

/**
 * Module combodo-autodispatch-ticket
 *
 * @author      Guillaume Lajarige <guillaume.lajarige@combodo.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */

class AutoDispatchPlugin implements iApplicationObjectExtension, iApplicationUIExtension
{
    static $aUpdateReentrance = array();

    //////////////////////////////////////////////////
    // Implementation of iApplicationUIExtension
    //////////////////////////////////////////////////

    public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
    {
        // Hiding transition buttons when there are dispatch rules for the current state
        $sState = $oObject->GetState();
        if (!empty($sState) && $bEditMode)
        {
            $aDispatchRules = static::GetDispatchRules($oObject, $sState);
            if(!empty($aDispatchRules))
            {
                $oPage->add_ready_script(
<<<EOF
    $('button.action[name="next_action"]').hide();
EOF
                );
            }
        }
    }

    public function OnDisplayRelations($oObject, WebPage $oPage, $bEditMode = false)
    {
        // TODO: Implement OnDisplayRelations() method.
    }

    public function OnFormSubmit($oObject, $sFormPrefix = '')
    {
        // TODO: Implement OnFormSubmit() method.
    }

    public function OnFormCancel($sTempId)
    {
        // TODO: Implement OnFormCancel() method.
    }

    public function EnumUsedAttributes($oObject)
    {
        // TODO: Implement EnumUsedAttributes() method.
        return array();
    }

    public function GetIcon($oObject)
    {
        // TODO: Implement GetIcon() method.
        return '';
    }

    public function GetHilightClass($oObject)
    {
        // TODO: Implement GetHilightClass() method.
        // Possible return values are:
        // HILIGHT_CLASS_CRITICAL, HILIGHT_CLASS_WARNING, HILIGHT_CLASS_OK, HILIGHT_CLASS_NONE
        return HILIGHT_CLASS_NONE;
    }

    public function EnumAllowedActions(DBObjectSet $oSet)
    {
        // TODO: Implement EnumAllowedActions() method.
        return array();
    }

	//////////////////////////////////////////////////
	// Implementation of iApplicationObjectExtension
	//////////////////////////////////////////////////

	public function OnIsModified($oObject)
	{
		return false;
	}

	public function OnCheckToWrite($oObject)
	{
	}

	public function OnCheckToDelete($oObject)
	{
	}

    public function OnDBInsert($oObject, $oChange = null)
    {
        // Protection against reentrance on either OnDBInsert or OnDBUpdate
        // Note: This is based on the fix made on r 3190 in DBObject::DBUpdate()
        $sKey = get_class($oObject).'::'.$oObject->GetKey();
        if(array_key_exists($sKey, static::$aUpdateReentrance))
        {
            return;
        }
        static::$aUpdateReentrance[$sKey] = true;

        $sReachingState = $oObject->GetState();
        if (!empty($sReachingState))
        {
            $this->OnReachingState($oObject, $sReachingState);
        }

        unset(static::$aUpdateReentrance[$sKey]);
    }

	public function OnDBUpdate($oObject, $oChange = null)
	{
        // Protection against reentrance on either OnDBInsert or OnDBUpdate
        // Note: This is based on the fix made on r 3190 in DBObject::DBUpdate()
        $sKey = get_class($oObject).'::'.$oObject->GetKey();
        if(array_key_exists($sKey, static::$aUpdateReentrance))
        {
            return;
        }
        static::$aUpdateReentrance[$sKey] = true;

        $sReachingState = $oObject->GetState();
		if (!empty($sReachingState))
		{
			$this->OnReachingState($oObject, $sReachingState);
		}

        unset(static::$aUpdateReentrance[$sKey]);
	}

	public function OnDBDelete($oObject, $oChange = null)
	{
	}

	//////////////////////////////////////////////////
	// Helpers
	//////////////////////////////////////////////////

    /**
     * @param DBObject $oObject
     * @param string $sReachingState
     * @param DateTime $oDate Date & time $oObject will reach $sReachingState. Default is null (= now)
     */
	public function OnReachingState(DBObject &$oObject, $sReachingState, $oDate = null, $bDryrun = false)
	{
		$sReachingStateLabel = MetaModel::GetStateLabel(get_class($oObject), $sReachingState);
        $aTargetRules = array();

	    // Retrieving DispatchRules
        $aDispatchRules = static::GetDispatchRules($oObject, $sReachingState);
        foreach($aDispatchRules as $oDispatchRule)
        {
            $sTargetAtt = $oDispatchRule->Get('target_att');
            $sExplainLogAtt = $oDispatchRule->Get('explain_log_att');

            // If dispatch rule is enabled in the current context, we skip it
            if(!$oDispatchRule->IsEnabledInContext())
            {
                continue;
            }

            // Retrieving best matching target rule
            foreach($oDispatchRule->Get('teamrules_list') as $oTargetRule)
            {
                $aTargetRules[$sTargetAtt][] = $oTargetRule;
            }
            list($oTargetObject, $aMatchingResults) = static::GetBestMatchingTargetObject($oObject, $aTargetRules[$sTargetAtt], $oDate);

            // Applying target rule
            if($oTargetObject !== null)
            {
                $oObject->Set($sTargetAtt, $oTargetObject);

                // Checking if stimulus to apply
                // - Preparing state rule search for current state
                $oSRSearch = DBObjectSearch::FromOQL('SELECT StateRule WHERE dispatchrule_id = :dispatchrule_id AND reaching_state_code = :reaching_state_code');
                $aSRParams = array(
                    'dispatchrule_id' => $oDispatchRule->GetKey(),
                    'reaching_state_code' => $oObject->GetState(),
                );
                $oSRSet = new DBObjectSet($oSRSearch, array(), $aSRParams);

                // - Retrieving state rule
                $oStateRule = $oSRSet->Fetch();
                if($oStateRule !== null && $oStateRule->Get('stimulus_code') !== '')
                {
                    // Adding a explaination text to explain which stimulus is applied
	                $aMatchingResults[] = array(
		                'matching_text' => "\n" . Dict::Format('AutoDispatch:StateRule:Explanation:HeaderText', $sReachingStateLabel)
	                );
	                $aMatchingResults[] = array(
		                'matching_text' => Dict::Format('AutoDispatch:StateRule:Explanation:StimulusApplied', MetaModel::GetName(get_class($oStateRule)), $oStateRule->GetKey(), $oStateRule->Get('friendlyname'), $oStateRule->Get('stimulus_code'))
	                );

                    // Applying stimulus only if not dryrun
                    if(!$bDryrun)
                    {
                        $oObject->ApplyStimulus($oStateRule->Get('stimulus_code'));
                    }
                }
            }

            // Logging event with explanation
            // - Preparing explanation text
            $sExplanation = static::PrepareMatchingExplanation($oObject, $sReachingState, $aMatchingResults);
            // - Creating event
            $oEvent = MetaModel::NewObject('EventOnObject');
            $oEvent->Set('userinfo', UserRights::GetUser());
            $oEvent->Set('obj_class', get_class($oObject));
            $oEvent->Set('obj_key', $oObject->GetKey());
            $oEvent->Set('message', $sExplanation);
            $oEvent->DBWrite();

            // Logging explanation
            if( !empty($sExplainLogAtt) )
            {
                // Checking if attribute supports HTML so we can add an hyperlink
                $oAttDef = MetaModel::GetAttributeDef(get_class($oObject), $sExplainLogAtt);
                if($oAttDef instanceof AttributeText && $oAttDef->GetFormat() === 'html')
                {
                    $sEvent = $oEvent::MakeHyperLink(get_class($oEvent), $oEvent->GetKey(), Dict::S('AutoDispatch:Explanation:ShortText:Here'), 'iTopStandardURLMaker');
                }
                else
                {
                    $sEvent = MetaModel::GetName(get_class($oEvent)).' #'.$oEvent->GetKey();
                }

                // Preparing short explanation
	            if($oTargetObject !== null)
	            {
		            $sExplanationShort = Dict::Format('AutoDispatch:Explanation:ShortTextWithObj', $sReachingStateLabel, $sEvent, $oTargetObject->Get('friendlyname'));
	            }
	            else
                {
	                $sExplanationShort = Dict::Format('AutoDispatch:Explanation:ShortText', $sReachingStateLabel, $sEvent);
	            }

                $oObject->Set($sExplainLogAtt, $sExplanationShort);
            }
        }

        if(!$bDryrun)
        {
            $oObject->DBWrite();
        }
	}

    /**
     * Returns the DispatchRules to apply on $oObject in $sReachingStateCode state on various target attributes.
     * If $sTargetAttCode is passed, will return only the first DispatchRule to apply on this attribute (should be only one !).
     *
     * @param DBObject $oObject
     * @param string $sReachingState Default is $oObject's current state
     * @param string $sTargetAttCode Default is null
     * @return array
     */
    static public function GetDispatchRules(DBObject $oObject, $sReachingState = null, $sTargetAttCode = null)
    {
        $aDispatchRules = array();
        $sObjectClass = get_class($oObject);

        // Retrieving current state if not provided
        if($sReachingState === null)
        {
            $sReachingState = $oObject->GetState();
        }

        // Preparing DispatchRule search
        $oDRSearch = DBObjectSearch::FromOQL('SELECT DR FROM DispatchRule AS DR JOIN StateRule AS SR ON SR.dispatchrule_id = DR.id JOIN TeamRule AS TR ON TR.dispatchrule_id = DR.id WHERE DR.class = :object_class AND SR.reaching_state_code = :reaching_state AND TR.active = \'yes\'');
        $aSearchParam = array(
            'reaching_state' => $sReachingState,
        );
        $aSearchColumns = array(
            'DR' => array('class', 'target_att', 'explain_log_att', 'disabled_contexts', 'teamrules_list', 'staterules_list'),
        );

        // Adding target attribute condition
        if($sTargetAttCode !== null)
        {
            $oDRSearch->AddConditionExpression(
                new BinaryExpression(
                    new FieldExpression('target_att', 'DR'),
                    '=',
                    new VariableExpression('object_target_att')
                )
            );
            $aSearchParam['object_target_att'] = $sTargetAttCode;
        }

        // Looking for DRs defined on the closest class to $sObjectClass (eg. For a UserRequest, we first check if there is a DR for UserRequest, then for Ticket)
        foreach( array_reverse(MetaModel::EnumParentClasses($sObjectClass, ENUM_PARENT_CLASSES_ALL)) as $sClass)
        {
            // Setting parent class as search class
            $aSearchParam['object_class'] = $sClass;
            $oDRSet = new DBObjectSet($oDRSearch, array(), $aSearchParam);
            $oDRSet->OptimizeColumnLoad($aSearchColumns);

            // Limiting results to 1 if target attribute is passed
            if($sTargetAttCode !== null)
            {
                $oDRSet->SetLimit(1);
            }
            $sOQL = $oDRSet->GetFilter()->ToOQL();
            $sOQLDev = $oDRSet->GetFilter()->ToOQL(true, $aSearchParam);
            if($oDRSet->Count() > 0)
            {
                while($oDispatchRule = $oDRSet->Fetch())
                {
                    $aDispatchRules[] = $oDispatchRule;
                }
                break;
            }
        }

        return $aDispatchRules;
    }

    /**
     * Returns the best matching object for $oObject->$sTargetAttCode in $sReachingState state, or null if none matches.
     *
     * @param DBObject $oObject
     * @param string $sTargetAttCode
     * @param string $sReachingState Default is $oObject's current state
     * @param DateTime $oDate Default is now
     * @return DBObject|null
     */
    static public function GetBestMatchForAttribute(DBObject $oObject, $sTargetAttCode, $sReachingState = null, DateTime $oDate = null)
    {
        $oMatch = null;
        $aResults = array();

        // Retrieving right DispatchRule
        $aDispatchRules = static::GetDispatchRules($oObject, $sReachingState, $sTargetAttCode);
        if(!empty($aDispatchRules))
        {
            $oDispatchRule = $aDispatchRules[0];
            list($oMatch, $aResults) = static::GetBestMatchingTargetObject($oObject, $oDispatchRule->Get('teamrules_list'), $oDate);
        }

        return $oMatch;
    }

    /**
     * Returns the best target object regarding the $aTargetRules or null if none matched.
     *
     * @param DBObject $oObject
     * @param array $aTargetRules Rules to match $oObject against
     * @return array list($oTargetObject, $aMatchingResults) $oTargetObject DBObject returned by the first matching rule or null if none matched, $aMatchingResults an array describing which rules matched or not.
     */
    static protected function GetBestMatchingTargetObject(DBObject $oObject, $aTargetRules, DateTime $oDate = null)
    {
        // Matching results against rules will be stored in this.
        $aMatchingResults = array();
        $oBestTargetObject = null;

        if($oDate === null)
        {
            $oDate = new DateTime();
        }

        // Sorting rules
        usort($aTargetRules, function($oTRA, $oTRB){
            $sRankA = $oTRA->Get('rank');
            $sRankB = $oTRB->Get('rank');

            if($sRankA === $sRankB){ return 0; }
            elseif($sRankA > $sRankB){ return 1; }
            else{ return -1; }
        });

        // Rule parameters
        $aSearchParams = array(
            'this' => $oObject,
        );

        // Looking for the best match
        foreach($aTargetRules as $iIndex => $oTargetRule)
        {
            $aMatchingResults[$iIndex] = array(
                'targetrule_id' => $oTargetRule->GetKey(),
                'matching_oql' => false,
                'matching_coveragewindow' => false,
                'matching_text' => '',
            );

            $oSearch = DBObjectSearch::FromOQL($oTargetRule->Get('oql'));
            $oSet = new DBObjectSet($oSearch, array(), $aSearchParams);
            $oSet->SetLimit(1);

            // Preparing labels for matching text
            // - Target rule
            $iTRNumber = $iIndex + 1;
            $sTRClass = get_class($oTargetRule);
            $sTRLabel = MetaModel::GetName($sTRClass);
            $sTRName = $oTargetRule->Get('friendlyname');
            $sTRId = $oTargetRule->GetKey();

            $oTargetObject = $oSet->Fetch();
            if($oTargetObject !== null)
            {
                // Preparing labels for matching text
                // - Target object
                $sTOLabel = MetaModel::GetName($oSearch->GetClass());
                $sTOName = $oTargetObject->Get('friendlyname');
                $sTOId = $oTargetObject->GetKey();

                $aMatchingResults[$iIndex]['matching_oql'] = true;

                // Testing coverage window condition
                // - No coverage window : Ok
                if($oTargetRule->Get('coveragewindow_id') == 0)
                {
                    $aMatchingResults[$iIndex]['matching_coveragewindow'] = true;
                    $aMatchingResults[$iIndex]['matching_text'] = Dict::Format('AutoDispatch:TargetRule:Explanation:OQLOK-CWOK', $iTRNumber, $sTRLabel, $sTRId, $sTRName, $sTOLabel, $sTOId, $sTOName);
                }
                else
                {
                    // Retrieving coverage window
                    $oCWAttDef = MetaModel::GetAttributeDef($sTRClass, 'coveragewindow_id');
                    $oCoverageWindow = MetaModel::GetObject($oCWAttDef->GetTargetClass(), $oTargetRule->Get('coveragewindow_id'));

                    // Preparing labels for matching text
                    // - Coverage window
                    $sCWLabel = MetaModel::GetName(get_class($oCoverageWindow));
                    $sCWName = $oCoverageWindow->Get('friendlyname');
                    $sCWId = $oCoverageWindow->GetKey();

                    // Checking coverage window condition
                    if($oCoverageWindow->IsInsideCoverage($oDate))
                    {
                        $aMatchingResults[$iIndex]['matching_coveragewindow'] = true;
                        $aMatchingResults[$iIndex]['matching_text'] = Dict::Format('AutoDispatch:TargetRule:Explanation:OQLOK-CWOK', $iTRNumber, $sTRLabel, $sTRId, $sTRName, $sTOLabel, $sTOId, $sTOName);
                    }
                    else
                    {
                        $aMatchingResults[$iIndex]['matching_text'] = Dict::Format('AutoDispatch:TargetRule:Explanation:OQLOK-CWKO', $iTRNumber, $sTRLabel, $sTRId, $sTRName, $sTOLabel, $sTOId, $sTOName, $sCWLabel, $sCWId, $sCWName);

                    }
                }
            }
            else
            {
                $aMatchingResults[$iIndex]['matching_text'] = Dict::Format('AutoDispatch:TargetRule:Explanation:OQLKO-CWOK', $iTRNumber, $sTRLabel, $sTRId, $sTRName);
            }

            // Stopping on the first matching rule
            if( ($aMatchingResults[$iIndex]['matching_oql'] === true) && ($aMatchingResults[$iIndex]['matching_coveragewindow'] === true) )
            {
                $oBestTargetObject = $oTargetObject;
                break;
            }
        }

        return array($oBestTargetObject, $aMatchingResults);
    }

    /**
     * Returns a formatted string containing an explaination about the applied rules and their matching status.
     *
     * @param DBObject $oObject
     * @param string $sReachingState
     * @param array $aMatchingResults
     * @return string
     */
    static protected function PrepareMatchingExplanation(DBObject $oObject, $sReachingState, $aMatchingResults)
    {
        $sExplanation = Dict::Format('AutoDispatch:TargetRule:Explanation:HeaderText', MetaModel::GetStateLabel(get_class($oObject), $sReachingState));

        foreach($aMatchingResults as $iIndex => $aResults)
        {
            $sExplanation .= "\n" . $aResults['matching_text'];
        }

        return $sExplanation;
    }
}
