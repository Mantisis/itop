<?php
// Copyright (C) 2013 Combodo SARL
//
//   This file is part of iTop.
//
//   iTop is free software; you can redistribute it and/or modify	
//   it under the terms of the GNU Affero General Public License as published by
//   the Free Software Foundation, either version 3 of the License, or
//   (at your option) any later version.
//
//   iTop is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU Affero General Public License for more details.
//
//   You should have received a copy of the GNU Affero General Public License
//   along with iTop. If not, see <http://www.gnu.org/licenses/>


/**
 * Launcher to interact with the ITSM designer
 * Called through pages/exec.php
 *
 * @copyright   Copyright (C) 2013 Combodo SARL
 * @license     http://opensource.org/licenses/AGPL-3.0
 */

if (!defined('__DIR__')) define('__DIR__', dirname(__FILE__));
require_once(APPROOT.'application/application.inc.php');
require_once(APPROOT.'application/itopwebpage.class.inc.php');
require_once(APPROOT.'setup/runtimeenv.class.inc.php');

// must be included explicitely to read the module installation data
require_once(APPROOT.'setup/moduleinstallation.class.inc.php');

require_once(APPROOT.'application/startup.inc.php');

require_once(APPROOT.'application/loginwebpage.class.inc.php');
LoginWebPage::DoLogin(true); // Check user rights and prompt if needed (must be admin)

$oP = new ITSMDesignerConnectorPage(Dict::S('ITSM-Designer:Launch-Title'));
// For the style sheet to work
$oP->set_base(utils::GetAbsoluteUrlAppRoot().'pages/');
// To compensate the effect of the above statement !!
$oP->add_ready_script("$('a[href=#]').each( function() { $(this).attr('href', window.location.href+'#'); } )");

$oP->add('<div class="centered_box">');
$oP->p(Dict::S('ITSM-Designer:Launch-Info'));
//$oP->p('<img src="../images/indicator.gif">&nbsp;'.Dict::S('ITSM-Designer:Launching'));
ITSMDesignerConnectorUtils::MakeLaunchForm($oP, Dict::S('ITSM-Designer:Launch-Button'));
$oP->add('</div>');
//		$oP->add_ready_script("$('#launcher').submit();"); // automatically submit the form
$oP->add_ready_script(
<<<EOF
$('button').button();
$('input[type="submit"]').button();
EOF
);

$oP->output();
