<?php
//
// iTop module definition file
//

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'combodo-autodispatch-ticket/1.0.2',
	array(
		// Identification
		//
		'label' => 'Dispatch of Tickets',
		'category' => 'business',

		// Setup
		//
		// Needs CoverageWindow and optionally ApprovalRule
		'dependencies' => array(
			'combodo-sla-computation/2.1.8',
			'combodo-approval-extended/1.2.3||combodo-approval-light/1.1.2||itop-service-mgmt/2.0.0||itop-service-mgmt-provider/2.0.0',
		),
		'mandatory' => true,
		'visible' => false,

		// Components
		//
		'datamodel' => array(
            'model.combodo-autodispatch-ticket.php',
            'main.combodo-autodispatch-ticket.php',
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
		),
	)
);


?>
