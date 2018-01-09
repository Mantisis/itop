<?php
//
// iTop module definition file
//

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'ait-import-webform-csv/1.1.0',
	array(
		// Identification
		//
		'label' => 'Webform CSV Importer',
		'category' => 'business',

		// Setup
		//
		'dependencies' => array(
			
		),
		'mandatory' => false,
		'visible' => true,

		// Components
		//
		'datamodel' => array(
			'main.ait-import-webform-csv.php',
			'model.ait-import-webform-csv.php'
		),
		'webservice' => array(
			
		),
		'data.struct' => array(
			// add your 'structure' definition XML files here,
		),
		'data.sample' => array(
			// add your sample data XML files here,
		),
		
		// Documentation
		//
		'doc.manual_setup' => '', // hyperlink to manual setup documentation, if any
		'doc.more_information' => '', // hyperlink to more information, if any 

		// Default settings
		//
		'settings' => array(
			'dir' => '../csv_web_form', //Path to the folder containing CSV files
			'periodicity' => 10, //How often the task must run
			'ticket_type' => 'UserRequest',
			'name' => 'nom_patient',
			'first_name' => 'prenom_patient',
			'phone' => 'telephone',
			'email' => 'email',
			'birthdate' => 'date_naissance',
			'org_id' => '3', //Organization ID of customers
			'org_name' => 'clinique', //Organization name for ticket
			'case_number' => 'num_dossier',
			'ns_entree_via' => 'entree_via',
			'ns_date_sortie' => 'date_sortie',
			'servicesubcategory_name' => 'objet',
			'title' => 'sujet',
			'description' => 'description',
 			// Module specific settings go here, if any
		),
	)
);


?>
