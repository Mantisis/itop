<?php
Dict::Add('FR FR', 'French', 'Français', array(
    // DispatchRule
    'Class:DispatchRule/Name' => '%1$s',
    'Class:DispatchRule' => 'Règle d\'affectation',
    'Class:DispatchRule+' => '',
    'Class:DispatchRule/Attribute:name' => 'Nom',
    'Class:DispatchRule/Attribute:name+' => '',
    'Class:DispatchRule/Attribute:class' => 'Classe',
    'Class:DispatchRule/Attribute:class+' => '',
    'Class:DispatchRule/Attribute:target_att' => 'Attribut équipe',
    'Class:DispatchRule/Attribute:target_att+' => '',
    'Class:DispatchRule/Attribute:explain_log_att' => 'Attribut d\'explication',
    'Class:DispatchRule/Attribute:explain_log_att+' => '',
    'Class:DispatchRule/Attribute:disabled_contexts' => 'Contextes non actifs',
    'Class:DispatchRule/Attribute:disabled_contexts+' => 'Contextes dans lesquels la règle d\'affectation sera inactive',
    'Class:DispatchRule/Attribute:disabled_contexts?' => 'Liste CSV des contextes à desactiver. Les valeurs possibles sont GUI:Console, GUI:Portal, Portal:<PORTAL_ID>, CRON, REST/JSON, Synchro, ...',
    'Class:DispatchRule/Attribute:staterules_list' => 'Etats',
    'Class:DispatchRule/Attribute:staterules_list+' => '',
    'Class:DispatchRule/Attribute:teamrules_list' => 'Règles d\'équipes',
    'Class:DispatchRule/Attribute:teamrules_list+' => '',
    'Class:DispatchRule/Error:ClassNotValid' => 'La classe doit être exister dans le modèle de donnée, valeur donné : "%1$s"',
    'Class:DispatchRule/Error:AttributeNotValid' => '"%2$s" n\'est pas un attribut valide pour la class "%1$s"',

    // Team rule
    'Class:TeamRule/Name' => '%1$s',
    'Class:TeamRule' => 'Règle d\'équipe',
    'Class:TeamRule+' => '',
    'Class:TeamRule/Attribute:dispatchrule_id' => 'Règle d\'affectation',
    'Class:TeamRule/Attribute:dispatchrule_id+' => '',
    'Class:TeamRule/Attribute:name' => 'Nom',
    'Class:TeamRule/Attribute:name+' => '',
    'Class:TeamRule/Attribute:oql' => 'OQL',
    'Class:TeamRule/Attribute:oql+' => 'Requête OQL retournant l\'équipe correspondante',
    'Class:TeamRule/Attribute:coveragewindow_id' => 'Fenêtre de couverture',
    'Class:TeamRule/Attribute:coveragewindow_id+' => '',
    'Class:TeamRule/Attribute:rank' => 'Rang',
    'Class:TeamRule/Attribute:rank+' => '',
    'Class:TeamRule/Attribute:active' => 'Active',
    'Class:TeamRule/Attribute:active+' => '',
    'Class:TeamRule/Attribute:active/Value:yes' => 'Oui',
    'Class:TeamRule/Attribute:active/Value:yes+' => '',
    'Class:TeamRule/Attribute:active/Value:no' => 'Non',
    'Class:TeamRule/Attribute:active/Value:no+' => '',

    // lnkDispatchRuleToTeamRule
    'Class:lnkDispatchRuleToTeamRule/Name' => '%1$s - %2$s',
    'Class:lnkDispatchRuleToTeamRule' => 'Lien Affectation / Equipe',
    'Class:lnkDispatchRuleToTeamRule/Attribute:dispatchrule_id' => 'Règle d\'affectation',
    'Class:lnkDispatchRuleToTeamRule/Attribute:teamrule_id' => 'Règle d\'équipe',

    // StateRule
    'Class:StateRule/Name' => '%1$s / %2$s',
    'Class:StateRule' => 'Règle d\'état',
    'Class:StateRule+' => '',
    'Class:StateRule/Attribute:dispatchrule_id' => 'Règle d\'affectation',
    'Class:StateRule/Attribute:dispatchrule_id+' => '',
    'Class:StateRule/Attribute:reaching_state_code' => 'Etat d\'entré (code)',
    'Class:StateRule/Attribute:reaching_state_code+' => '',
    'Class:StateRule/Attribute:stimulus_code' => 'Code du stimulus',
    'Class:StateRule/Attribute:stimulus_code+' => 'Code du stimulus à appliquer lors de l\'entrée dans cet état',
    'Class:StateRule/Error:ObjectNotUnique' => 'Impossible de créer un objet %1$s car il y en a déjà un(e) pour %2$s dans l\'état %3$s',
    'Class:StateRule/Error:StateNotValid' => 'Impossible de créer un objet %1$s car l\'état %2$s n\'est pas valide pour la classe %3$s',
    'Class:StateRule/Error:StimulusNotValid' => 'Impossible de créer un objet %1$s car le stimulus %2$s n\'est pas valide dans l`état %3$s',

    // Menus
    'Menu:DispatchRule' => 'Règles d\'affectation',
    'Menu:DispatchRule+' => 'Règles d\'affectation',

    // Explanation texts
    'AutoDispatch:Explanation:ShortText' => 'Affecté(e) automatiquement lors de l\'entrée dans l\'état %1$s. (Pour plus d\'informations cliquer %2$s)',
	'AutoDispatch:Explanation:ShortTextWithObj' => 'Affecté(e) automatiquement à %3$s lors de l\'entrée dans l\'état %1$s. (Pour plus d\'informations cliquer %2$s)',
	'AutoDispatch:Explanation:ShortText:Here' => 'ici',
    'AutoDispatch:TargetRule:Explanation:HeaderText' => 'Règles d\'équipe appliquées lors de l\'entrée dans l\'état %1$s :',
    'AutoDispatch:TargetRule:Explanation:OQLOK-CWOK' => '%1$d - %2$s #%3$s (%4$s) correspond et a retourné(e) %5$s #%6$s (%7$s)',
    'AutoDispatch:TargetRule:Explanation:OQLKO-CWOK' => '%1$d - %2$s #%3$s (%4$s) ne correspond pas : Aucun résultat pour l\'OQL',
    'AutoDispatch:TargetRule:Explanation:OQLOK-CWKO' => '%1$d - %2$s #%3$s (%4$s) ne correspond pas : l\'OQL a retourné(e) %5$s #%6$s (%7$s) mais %8$s #%9$s (%10$s) n\'était pas applicable',
	'AutoDispatch:StateRule:Explanation:HeaderText' => 'Stimulus appliqué lors de l\'entrée dans l\'état %1$s :',
	'AutoDispatch:StateRule:Explanation:StimulusApplied' => '%1$s #%2$s (%3$s) a appliqué le stimulus %4$s',
	'AutoDispatch:Simulator:Tab:Title' => 'Simulateur',
));
