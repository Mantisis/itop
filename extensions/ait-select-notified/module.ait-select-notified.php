<?php
/**
* Module ait-select-notified
*
* @author      Raphaël Saget <r.saget@axelit.fr>
* @author      David Bontoux <d.bontoux@axelit.fr>
*/
SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'ait-select-notified/1.0.0',
	array(
		// Identification
		//
		'label' => 'AIT Select Notified',
		'category' => 'bizmodel',

		// Setup
		//
		'dependencies' => array(
			'itop-attachments/1.0.0',
			'email-reply/1.0.0',
		),
		'mandatory' => false,
		'visible' => true,

		// Components
		//
		'datamodel' => array(
			'main.ait-select-notified.php'
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
			'classToApply' => 'UserRequest', //Which type of ticket you can chose who will get the mail
			'fieldForRegEx' => 'escalation_reason',
		),
	)
);


?>
