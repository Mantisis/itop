<?php
//
// iTop module definition file
//

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'ait-ticket-autoaffectation/1.0.0',
	array(
		// Identification
		//
		'label' => 'UserRequest Auto Affectation',
		'category' => 'email',

		// Setup
		//
		'dependencies' => array(
			'combodo-email-synchro/3.0.0',
			'itop-standard-email-synchro/3.0.4'
		),
		'mandatory' => false,
		'visible' => true,

		// Components
		//
		'datamodel' => array(
			'model.ait-ticket-autoaffectation.php'
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
			// Module specific settings go here, if any
			'organisation_default' => 'Z_Patient',
			'organisation_prestataire' => 'Recouvrement',
		),
	)
);


?>
