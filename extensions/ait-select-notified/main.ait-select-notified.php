<?php
// Copyright (C) 2010 Combodo SARL
//
//   This program is free software; you can redistribute it and/or modify
//   it under the terms of the GNU General Public License as published by
//   the Free Software Foundation; version 3 of the License.
//
//   This program is distributed in the hope that it will be useful,
//   but WITHOUT ANY WARRANTY; without even the implied warranty of
//   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
//   GNU General Public License for more details.
//
//   You should have received a copy of the GNU General Public License
//   along with this program; if not, write to the Free Software
//   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA


/**
 * Module precanned-replies
 *
 * @author      Erwan Taloc <erwan.taloc@combodo.com>
 * @author      Romain Quetiez <romain.quetiez@combodo.com>
 * @author      Denis Flaven <denis.flaven@combodo.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */

/**
 * Pre-defined replies for fast answer to helpdesk tickets
 *
 * @author      Erwan Taloc <erwan.taloc@combodo.com>
 * @author      Romain Quetiez <romain.quetiez@combodo.com>
 * @author      Denis Flaven <denis.flaven@combodo.com>
 * @license     http://www.opensource.org/licenses/gpl-3.0.html LGPL
 */


// Declare a class that implements iBackgroundProcess (will be called by the CRON)
// Extend the class AsyncTask to create a queue of asynchronous tasks (process by the CRON)
// Declare a class that implements iApplicationUIExtension (to tune object display and edition form)
// Declare a class that implements iApplicationObjectExtension (to tune object read/write rules)

class SelectNotifiedPlugin implements iApplicationUIExtension, iApplicationObjectExtension
{
	public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
	{
		if ($bEditMode && !$oObject->IsNew())
		{
			$sModuleUrl = utils::GetAbsoluteUrlModulesRoot().'ait-select-notified/';
			$oPage->add_linked_script($sModuleUrl.'ait-select-notified.js');
			$oTicketRequestSet = new DBObjectSet(DBObjectSearch::FromOQL('SELECT Ticket WHERE id = :id'),
              array(),
              array(
                'id' => utils::ReadParam('id', ''),
              ));
			$oTicket = $oTicketRequestSet->Fetch();
			$oPersonRequestSet = new DBObjectSet(DBObjectSearch::FromOQL('SELECT Person WHERE id = :id'),
              array(),
              array(
                'id' => $oTicket->Get('caller_id'),
              ));
			$oPerson = $oPersonRequestSet->Fetch();

			$sCallerMail = $oPerson->Get('email');

			$oPage->add_ready_script("$('#field_2_public_log div.caselog_input_header').append('<div id=\"select_sender\"><label>Destinataire : <select onChange=\"OnSelectNotified(this.value,".utils::ReadParam('id', '').")\"><option value=\"Patient\">Patient</option><option value=\"Clinique\">Clinique</option><option value=\"Prestataire\">Prestataire</option></select></label><span id=\"v_notified\"><label><input type=\"checkbox\" checked value=\"yes\">".$sCallerMail."</label></span></div>');");
		}
	}

	public function OnDisplayRelations($oObject, WebPage $oPage, $bEditMode = false)
	{
	}

	public function OnFormSubmit($oObject, $sFormPrefix = '')
	{
		var_dump($oObject);
	}
	
	public function OnFormCancel($sTempId)
	{
	}

	public function EnumUsedAttributes($oObject)
	{
		return array();
	}

	public function GetIcon($oObject)
	{
		return '';
	}

	public function GetHilightClass($oObject)
	{
		// Possible return values are:
		// HILIGHT_CLASS_CRITICAL, HILIGHT_CLASS_WARNING, HILIGHT_CLASS_OK, HILIGHT_CLASS_NONE	
		return HILIGHT_CLASS_NONE;
	}

	public function EnumAllowedActions(DBObjectSet $oSet)
	{
		// No action
		return array();
    }

	public function OnIsModified($oObject)
	{
		return false;
	}

	public function OnCheckToWrite($oObject)
	{
		return array();
	}

	public function OnCheckToDelete($oObject)
	{
		return array();
	}

	public function OnDBUpdate($oObject, $oChange = null)
	{
	}
	
	public function OnDBInsert($oObject, $oChange = null)
	{
	}
	
	public function OnDBDelete($oObject, $oChange = null)
	{	
	}

}

