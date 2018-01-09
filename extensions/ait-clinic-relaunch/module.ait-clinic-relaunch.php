<?php
//
// iTop module definition file
//

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'ait-clinic-relaunch/1.0.0',
	array(
		// Identification
		//
		'label' => 'Automatic clinic relaunch by mail',
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
                        'main.ait-clinic-relaunch.php',
			'model.ait-clinic-relaunch.php'
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
                        'active'=>0,//0 = non active, 1 = active
                        'periodicity' => 86400, //How often the task must run
			'delay' => '28', //Delete ticket older than X seconds
                        'action' => '8', //id of notification
		),
	)
);


?>
