<?php
/**
 * Localized data
 *
 * @copyright   Copyright (C) 2013 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

Dict::Add('EN US', 'English', 'English', array(
    // DispatchRule
    'Class:DispatchRule/Name' => '%1$s',
    'Class:DispatchRule' => 'Dispatch rule',
    'Class:DispatchRule+' => '',
    'Class:DispatchRule/Attribute:name' => 'Name',
    'Class:DispatchRule/Attribute:name+' => '',
    'Class:DispatchRule/Attribute:class' => 'Class',
    'Class:DispatchRule/Attribute:class+' => '',
    'Class:DispatchRule/Attribute:target_att' => 'Team attribute',
    'Class:DispatchRule/Attribute:target_att+' => '',
    'Class:DispatchRule/Attribute:explain_log_att' => 'Explain log attribute',
    'Class:DispatchRule/Attribute:explain_log_att+' => '',
    'Class:DispatchRule/Attribute:disabled_contexts' => 'Disabled contexts',
    'Class:DispatchRule/Attribute:disabled_contexts+' => 'Contexts in which the dispatch rule will be inactive',
    'Class:DispatchRule/Attribute:disabled_contexts?' => 'CSV list of contexts to disable. Possible values can be GUI:Console, GUI:Portal, Portal:<PORTAL_ID>, CRON, REST/JSON, Synchro, ...',
    'Class:DispatchRule/Attribute:staterules_list' => 'States',
    'Class:DispatchRule/Attribute:staterules_list+' => '',
    'Class:DispatchRule/Attribute:teamrules_list' => 'Team rules',
    'Class:DispatchRule/Attribute:teamrules_list+' => '',
    'Class:DispatchRule/Error:ClassNotValid' => 'Class must be a valid class from datamodel, "%1$s" given',
    'Class:DispatchRule/Error:AttributeNotValid' => '"%2$s" is not a valid attribute for class "%1$s"',

    // Team rule
    'Class:TeamRule/Name' => '%1$s',
    'Class:TeamRule' => 'Team rule',
    'Class:TeamRule+' => '',
    'Class:TeamRule/Attribute:dispatchrule_id' => 'Dispatch rule',
    'Class:TeamRule/Attribute:dispatchrule_id+' => '',
    'Class:TeamRule/Attribute:name' => 'Name',
    'Class:TeamRule/Attribute:name+' => '',
    'Class:TeamRule/Attribute:oql' => 'OQL',
    'Class:TeamRule/Attribute:oql+' => 'OQL query to find a matching Team',
    'Class:TeamRule/Attribute:coveragewindow_id' => 'Coverage window',
    'Class:TeamRule/Attribute:coveragewindow_id+' => '',
    'Class:TeamRule/Attribute:rank' => 'Rank',
    'Class:TeamRule/Attribute:rank+' => '',
    'Class:TeamRule/Attribute:active' => 'Active',
    'Class:TeamRule/Attribute:active+' => '',
    'Class:TeamRule/Attribute:active/Value:yes' => 'Yes',
    'Class:TeamRule/Attribute:active/Value:yes+' => '',
    'Class:TeamRule/Attribute:active/Value:no' => 'no',
    'Class:TeamRule/Attribute:active/Value:no+' => '',

    // lnkDispatchRuleToTeamRule
    'Class:lnkDispatchRuleToTeamRule/Name' => '%1$s - %2$s',
    'Class:lnkDispatchRuleToTeamRule' => 'Dispatch / Team link',
    'Class:lnkDispatchRuleToTeamRule/Attribute:dispatchrule_id' => 'Dispatch rule',
    'Class:lnkDispatchRuleToTeamRule/Attribute:teamrule_id' => 'Team rule',

    // State rule
    'Class:StateRule/Name' => '%1$s / %2$s',
    'Class:StateRule' => 'State rule',
    'Class:StateRule+' => '',
    'Class:StateRule/Attribute:dispatchrule_id' => 'Dispatch rule',
    'Class:StateRule/Attribute:dispatchrule_id+' => '',
    'Class:StateRule/Attribute:reaching_state_code' => 'Reaching state code',
    'Class:StateRule/Attribute:reaching_state_code+' => '',
    'Class:StateRule/Attribute:stimulus_code' => 'Stimulus code',
    'Class:StateRule/Attribute:stimulus_code+' => 'Code of the stimulus to apply when reaching the state',
    'Class:StateRule/Error:ObjectNotUnique' => 'Could not create %1$s as there is already one for %2$s in state %3$s',
    'Class:StateRule/Error:StateNotValid' => 'Could not create %1$s as state %2$s does not exist for class %3$s',
    'Class:StateRule/Error:StimulusNotValid' => 'Could not create %1$s as stimulus %2$s does not exist in state %3$s',

    // Menus
    'Menu:DispatchRule' => 'Dispatch rules',
    'Menu:DispatchRule+' => 'Dispatch rules',

    // Explanation texts
	'AutoDispatch:Explanation:ShortText' => 'Automatically dispatched when reaching state %1$s. (for more informations see %2$s)',
	'AutoDispatch:Explanation:ShortTextWithObj' => 'Automatically dispatched to %3$s when reaching state %1$s. (for more informations see %2$s)',
	'AutoDispatch:Explanation:ShortText:Here' => 'here',
    'AutoDispatch:TargetRule:Explanation:HeaderText' => 'Team rules applied on reaching state %1$s:',
    'AutoDispatch:TargetRule:Explanation:OQLOK-CWOK' => '%1$d - %2$s #%3$s (%4$s) matched and returned %5$s #%6$s (%7$s)',
    'AutoDispatch:TargetRule:Explanation:OQLKO-CWOK' => '%1$d - %2$s #%3$s (%4$s) did not match: OQL returned no result',
	'AutoDispatch:TargetRule:Explanation:OQLOK-CWKO' => '%1$d - %2$s #%3$s (%4$s) did not match: OQL returned %5$s #%6$s (%7$s) but %8$s #%9$s (%10$s) was not applicable',
    'AutoDispatch:StateRule:Explanation:HeaderText' => 'Stimulus applied on reaching state %1$s:',
    'AutoDispatch:StateRule:Explanation:StimulusApplied' => '%1$s #%2$s (%3$s) applied stimulus %4$s',
    'AutoDispatch:Simulator:Tab:Title' => 'Simulator',
));
