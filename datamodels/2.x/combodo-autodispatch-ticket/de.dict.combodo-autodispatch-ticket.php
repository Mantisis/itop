<?php
/**
 * Localized data
 *
 * @copyright   Copyright (C) 2017 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

Dict::Add('DE DE', 'German', 'Deutsch', array(
    // DispatchRule
    'Class:DispatchRule/Name' => '%1$s',
    'Class:DispatchRule' => 'Weiterleitungsregel an Team',  //'Dispatch rule',
    'Class:DispatchRule+' => '',
    'Class:DispatchRule/Attribute:name' => 'Name',
    'Class:DispatchRule/Attribute:name+' => '',
    'Class:DispatchRule/Attribute:class' => 'Klasse', //'Class',
    'Class:DispatchRule/Attribute:class+' => '',
    'Class:DispatchRule/Attribute:target_att' => 'Team Attribut', //'Team attribute',
    'Class:DispatchRule/Attribute:target_att+' => '',
    'Class:DispatchRule/Attribute:explain_log_att' => 'Log Attribut', //'Explain log attribute',
    'Class:DispatchRule/Attribute:explain_log_att+' => '',
    'Class:DispatchRule/Attribute:disabled_contexts' => 'Deaktivierungs Context', //'Disabled contexts',
    'Class:DispatchRule/Attribute:disabled_contexts+' => 'Context in dem die Weiterleitungsregel deaktiviert ist', //'Contexts in which the dispatch rule will be inactive',
    'Class:DispatchRule/Attribute:disabled_contexts?' => 'CSV Liste von Contexten, die deaktiviert werden sollen. Mögliche Werte können sein: GUI:Console, GUI:Portal, Portal:<PORTAL_ID>, CRON, REST/JSON, Synchro, ...',
                  //'CSV list of contexts to disable. Possible values can be GUI:Console, GUI:Portal, Portal:<PORTAL_ID>, CRON, REST/JSON, Synchro, ...',
    'Class:DispatchRule/Attribute:staterules_list' => 'Status Werte', //'States',
    'Class:DispatchRule/Attribute:staterules_list+' => '',
    'Class:DispatchRule/Attribute:teamrules_list' => 'Teamregeln', //'Team rules',
    'Class:DispatchRule/Attribute:teamrules_list+' => '',
    'Class:DispatchRule/Error:ClassNotValid' => 'Die Klasse muss im Datenmodell vorhanden sein, Klasse "%1$s" wurde ausgewählt',// 'Class must be a valid class from datamodel, "%1$s" given',
    'Class:DispatchRule/Error:AttributeNotValid' => '"%2$s" ist kein gültiges Attribut für die Klasse  "%1$s"', // '"%2$s" is not a valid attribute for class "%1$s"',

    // Team rule
    'Class:TeamRule/Name' => '%1$s',
    'Class:TeamRule' => 'Team Regel',  //'Team rule',
    'Class:TeamRule+' => '',
    'Class:TeamRule/Attribute:dispatchrule_id' => 'Weiterleitungsregel an Team', //'Dispatch rule',
    'Class:TeamRule/Attribute:dispatchrule_id+' => '',
    'Class:TeamRule/Attribute:name' => 'Name',
    'Class:TeamRule/Attribute:name+' => '',
    'Class:TeamRule/Attribute:oql' => 'OQL',
    'Class:TeamRule/Attribute:oql+' => 'OQL Abfrage mit der das passende Team gesucht wird', //'OQL query to find a matching Team',
    'Class:TeamRule/Attribute:coveragewindow_id' =>  'Zeitfenster', //'Coverage window',
    'Class:TeamRule/Attribute:coveragewindow_id+' => '',
    'Class:TeamRule/Attribute:rank' => 'Reihenfolge', //'Rank',
    'Class:TeamRule/Attribute:rank+' => '',
    'Class:TeamRule/Attribute:active' => 'Aktiv', //'Active',
    'Class:TeamRule/Attribute:active+' => '',
    'Class:TeamRule/Attribute:active/Value:yes' => 'Ja', //'Yes',
    'Class:TeamRule/Attribute:active/Value:yes+' => '',
    'Class:TeamRule/Attribute:active/Value:no' => 'Nein', //'no',
    'Class:TeamRule/Attribute:active/Value:no+' => '',

    // lnkDispatchRuleToTeamRule
    'Class:lnkDispatchRuleToTeamRule/Name' => '%1$s - %2$s',
    'Class:lnkDispatchRuleToTeamRule' => 'Verkn. Weiterleitung / Team', //'Dispatch / Team link',
    'Class:lnkDispatchRuleToTeamRule/Attribute:dispatchrule_id' => 'Weiterleitungsregel', //'Dispatch rule',
    'Class:lnkDispatchRuleToTeamRule/Attribute:teamrule_id' => 'Teamregel', //'Team rule',

    // State rule
    'Class:StateRule/Name' => '%1$s / %2$s',
    'Class:StateRule' => 'Statusregel', //'State rule',
    'Class:StateRule+' => '',
    'Class:StateRule/Attribute:dispatchrule_id' => 'Weiterleitungsregel an Team', //'Dispatch rule',
    'Class:StateRule/Attribute:dispatchrule_id+' => '',
    'Class:StateRule/Attribute:reaching_state_code' => 'Bei erreichen von Status (Code)', //'Reaching state code',
    'Class:StateRule/Attribute:reaching_state_code+' => '',
    'Class:StateRule/Attribute:stimulus_code' => 'Stimulus Code', //'Stimulus code',
    'Class:StateRule/Attribute:stimulus_code+' => 'Stimulus Code, der angewendet werden soll, wenn der Status erreicht wird', //'Code of the stimulus to apply when reaching the state',
    'Class:StateRule/Error:ObjectNotUnique' => '%1$s konnte nicht erstellt werden, weil es bereits einen %2$s im Status %3$s gibt', // 'Could not create %1$s as there is already one for %2$s in state %3$s',
    'Class:StateRule/Error:StateNotValid' => '%1$s konnte nicht erstellt werden, weil es den Status %2$s in der Klasse %3$s nicht gibt', //'Could not create %1$s as state %2$s does not exist for class %3$s',
    'Class:StateRule/Error:StimulusNotValid' => '%1$s konnte nicht erstellt werden, weil es den Stimulus %2$s im Status %3$s nicht gibt', // 'Could not create %1$s as stimulus %2$s does not exist in state %3$s',

    // Menus
    'Menu:DispatchRule' => 'Weiterleitungsregeln an Team', //'Dispatch rules',
    'Menu:DispatchRule+' => 'Weiterleitungsregeln an Team', //'Dispatch rules',

    // Explanation texts
    'AutoDispatch:Explanation:ShortText' => 'Automatisch weitergeleitet wenn der Status %1$s erreicht wird (für weitere Informationen siehe %2$s)', 
               // 'Automatically dispatched when reaching state %1$s. (for more informations see %2$s)',
    'AutoDispatch:Explanation:ShortText:Here' => 'Hier', //'here',
    'AutoDispatch:TargetRule:Explanation:HeaderText' => 'Regeln angewendet bei Erreichen des %1$s Status:', //'Rules applied on reaching state %1$s:',
    'AutoDispatch:TargetRule:Explanation:OQLOK-CWOK' => '%1$d - %2$s #%3$s (%4$s) passend und gaben zurück: %5$s #%6$s (%7$s)', //'%1$d - %2$s #%3$s (%4$s) matched and returned %5$s #%6$s (%7$s)',
    'AutoDispatch:TargetRule:Explanation:OQLKO-CWOK' => '%1$d - %2$s #%3$s (%4$s) nicht passend: OQL gab leeres Resultat', //'%1$d - %2$s #%3$s (%4$s) did not match: OQL returned no result',
	'AutoDispatch:TargetRule:Explanation:OQLOK-CWKO' => '%1$d - %2$s #%3$s (%4$s) nicht passend, OQL Resultat %5$s #%6$s (%7$s) aber %8$s #%9$s (%10$s) konnte nicht angewendet werden',
               //'%1$d - %2$s #%3$s (%4$s) did not match: OQL returned %5$s #%6$s (%7$s) but %8$s #%9$s (%10$s) was not applicable',
    'AutoDispatch:StateRule:Explanation:StimulusApplied' => '%1$s #%2$s (%3$s) Stimulus %4$s angewendet', //'%1$s #%2$s (%3$s) applied stimulus %4$s',
    'AutoDispatch:Simulator:Tab:Title' => 'Simulator',
));
