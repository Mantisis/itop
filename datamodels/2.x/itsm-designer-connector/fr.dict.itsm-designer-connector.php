<?php
/**
 * Localized data
 *
 * @copyright   Copyright (C) 2013 XXXXX
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

Dict::Add('FR FR', 'French', 'Français', array(
	'ITSM-Designer:Launch-Title' => '▶ Designer ITSM',

	'ITSM-Designer:Launch-Info' => 'En allant dans le "Designer ITSM", des informations à propos de votre installation seront transmises. Si c\'est la première fois que vous vous connectez au Designer ITSM, alors il vous sera demandé votre clé de licence.',
	'ITSM-Designer:Launch-Button' => 'Aller dans le Designer ITSM ▶',
	//'ITSM-Designer:Launching' => 'Vous allez être redirigés vers le Designer ITSM. Veuillez patienter...',
 	'Menu:ITSMDesignerMenu' => 'Designer ITSM',

	'ITSM-Designer:TestEnv-Label' => 'Vous êtes dans le mode TEST',
	'ITSM-Designer:TestEnv-BackButton' => 'Revenir au Designer ITSM',

	'ITSM-Designer:Compile-Title' => 'ITSM Designer: votre installation',
	'ITSM-Designer:BackupFreeIn' => '%1$s libres dans %2$s',
	'ITSM-Designer:BackupOK' => 'Sauvegarde effectuée',
	'ITSM-Designer:CompiledOK' => 'Le code et le format de la base de données ont été mis à jour',
	'ITSM-Designer:CompiledKO-KeepCool' => 'La mise à jour a été interrompue : votre application n\'a pas été modifiée.',
	'ITSM-Designer:Compiling-MoveToProd' => 'Mettre à jour votre application',
	'ITSM-Designer:Compiling-MoveToProd+' => 'Le code de l\'application et le format de la base de données vont être mis à jour. Veuillez confirmer que vous souhaitez mettre en production cette révision. Au moindre doute, veuillez prendre le temps d\'utiliser la fonction de Test ou de tenter la mise à jour sur une instance dédiée au Test.',
	'ITSM-Designer:Compiling-Revision' => 'Révision',
	'ITSM-Designer:Compiling-RevLabel' => 'Label: %1$s',
	'ITSM-Designer:Compiling-RevDesc' => 'Description: %1$s',
	'ITSM-Designer:Compiling-UntaggedRev' => 'Révision #%1$s',
	'ITSM-Designer:Compiling-BackupTitle' => 'Sauvegarde',
	'ITSM-Designer:Compiling-CreateBackup' => 'Sauvegarder la base de données avant la mise à jour',
	'ITSM-Designer:Compiling-SaveBackupTo' => 'Enregistrer dans le fichier',
	'ITSM-Designer:Compiling-Confirm' => 'Mettre à jour iTop!',
	'ITSM-Designer:Compiling-Cancel' => '<< Annuler',
	'ITSM-Designer:Compiling-Execution' => 'La mise à jour de votre application est en cours',
	'ITSM-Designer:Compiling-Execution+' => '',
	'ITSM-Designer:JumpToProd' => 'Aller dans l\'application mise à jour',
	'ITSM-Designer:JumpToTest' => 'Aller dans l\'environnement de TEST',
	'ITSM-Designer:JumpToProd-Unchanged' => 'Aller dans l\'application (inchangée)',
	'ITSM-Designer:BackToDesigner' => 'Retourner au Designer ITSM',
	'ITSM-Designer:Compiling-ForTest' => 'Tester la révision',
	'ITSM-Designer:Compiling-ForTest+' => 'Le Designer ITSM prépare un environnement de TEST. Lorsque ce sera fait, vous serez redirigé vers cet environnement de TEST.',
//	'ITSM-Designer:Compiling-PleaseWait' => 'Cette opération va prendre quelques secondes pour s'exécuter. Veuillez patienter...',
	'ITSM-Designer:CompileIssue-WrongProduct' => 'Produit inconnu. Le Designer ITSM s\'attend à ce que votre installation soit pour le produit \'%1$s\' (votre produit est \'%2$s\')',
	'ITSM-Designer:CompileIssue-WrongVersion' => 'Version inconnue. Le Designer ITSM s\'attend à ce que votre installation soit dans la version \'%1$s\' (votre version est \'%2$s\')',
	'ITSM-Designer:CompileIssue-WrongSourceDir' => 'Répertoire d\'origine inconnu. Le Designer ITSM s\'attend à ce que votre installation utilise \'%1$s\' (votre installation utilise \'%2$s\')',
	'ITSM-Designer:CompileIssue-Modules' => 'Le Designer ITSM s\'attend à ce que votre installation soit faite d\'une liste de modules fixe. Veuillez exécuter de nouveau le programme d\'installation.',
	'ITSM-Designer:CompileIssue-ToInstall' => 'Modules à installer (mise à jour requise): %1$s',
	'ITSM-Designer:CompileIssue-ToRemove' => 'Modules indésirables (nouvelle installation requise): %1$s',

	'ITSM-Designer:IntegrityOK' => 'L\'intégrité de la base de données à été vérifiée avec succès',
	'ITSM-Designer:IntegrityKO' => 'La vérification d\'intégrité de la base de données a révélé des anomalies',
	'ITSM-Designer:IntegrityKO-SeeMore' => 'Voir le rapport détaillé',
	'ITSM-Designer:IntegrityIssue-Description' => 'Anomalie',
	'ITSM-Designer:IntegrityIssue-Count' => 'Occurrences',
	'ITSM-Designer:IntegrityIssue-Query' => 'Requêtes SQL pour vérifier plus tard',
	'ITSM-Designer:IntegrityIssue-ValueStats' => 'Valeur:&nbsp;%1$s Occurrences:&nbsp;%2$d',

	'DBAnalyzer-Integrity-OrphanRecord' => 'Enregistrement orphelin dans `%1$s`, il devrait avoir son pendant dans la table `%2$s`',
	'DBAnalyzer-Integrity-InvalidExtKey' => 'Clé externe %1$s invalide (colonne&nbsp;: `%2$s.%3$s`)',
	'DBAnalyzer-Integrity-MissingExtKey' => 'Clé externe %1$s manquante (colonne&nbsp;: `%2$s.%3$s`)',
	'DBAnalyzer-Integrity-InvalidValue' => 'Valeur incorrecte pour %1$s (colonne&nbsp;: `%2$s.%3$s`)',
	'DBAnalyzer-Integrity-UsersWithoutProfile' => 'Certains comptes utilisateurs n\'ont aucun profil.',
));
?>
