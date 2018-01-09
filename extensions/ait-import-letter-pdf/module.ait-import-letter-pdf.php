<?php
//
// iTop module definition file
//

SetupWebPage::AddModule(
	__FILE__, // Path to the current file, all other file names are relative to the directory containing this file
	'ait-import-letter-pdf/1.0.0',
	array(
		// Identification
		//
		'label' => 'Scanned PDF Letter',
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
			'main.ait-import-letter-pdf.php',
			'model.ait-import-letter-pdf.php'
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
			'dir' => '../../pdf_scanned_letter', //Path to the folder containing CSV files
			'periodicity' => 10, //How often the task must run
			'ticket_type' => 'UserRequest',
			'org_id' => 3,
			'organizations' => array(
				'ATL' => 'C_Atlantique',
				'MAI' => 'Demo',
				'SPI' => '',
				'SVB' => '',
				'XDS' => '',
				'BEA' => '',
				'CED' => '',
				'PCT' => '',
				'SJL' => '',
				'JLB' => '',
				'AGL' => '',
				'BEL' => '',
				'GCS' => '',
				'SOD' => '',
				'CSV' => '',
				'CGL' => '',
				'CDT' => '',
				'BOJ' => '',
				'PCO' => '',
				'FON' => '',
				'CCB' => '',
				'DOM' => '',
				'CDP' => '',
				'MLV' => '',
			)
 			// Module specific settings go here, if any
		),
	)
);


?>
