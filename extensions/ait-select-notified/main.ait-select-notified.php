<?php
/**
* Module ait-select-notified
*
* @author      Raphaël Saget <r.saget@axelit.fr>
* @author      David Bontoux <d.bontoux@axelit.fr>
*/

class SelectNotifiedPlugin implements iApplicationUIExtension, iApplicationObjectExtension
{
	public function OnDisplayProperties($oObject, WebPage $oPage, $bEditMode = false)
	{
		if (get_class($oObject) == MetaModel::GetConfig()->GetModuleSetting('ait-select-notified', 'classToApply', '') && $bEditMode && !$oObject->IsNew())
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

			$oPage->add_ready_script("$('#field_2_public_log div.caselog_input_header').append('<div id=\"select_sender\"><label>Destinataire : <select onChange=\"OnSelectNotified(this.value,".utils::ReadParam('id', '').")\"><option value=\"Patient\">Patient</option><option value=\"Clinique\">Clinique</option><option value=\"Prestataire\">Prestataire</option></select></label><span id=\"v_notified\"><label><input type=\"checkbox\" name=\"listmail[".$sCallerMail."]\" checked value=\"yes\">".$sCallerMail."</label></span></div>');");
		}
	}

	public function OnDisplayRelations($oObject, WebPage $oPage, $bEditMode = false)
	{
	}

	public function OnFormSubmit($oObject, $sFormPrefix = '')
	{
		if(get_class($oObject) == MetaModel::GetConfig()->GetModuleSetting('ait-select-notified', 'classToApply', '')){
			//error_log(print_r($_POST,true));
			$aListmail = Utils::ReadParam('listmail','');
			$sRegExMail = '';
			$nCount = 0;

			foreach($aListmail as $mail =>  $key){
				if($key ==  "yes")  {
					$sRegExMail .= $mail."|";
					$nCount += 1;
				}
			}

			$sRegExMail = substr($sRegExMail,0,-1);

			if(MetaModel::IsValidAttCode(get_class($oObject), MetaModel::GetConfig()->GetModuleSetting('ait-select-notified', 'fieldForRegEx', ''))) {
				if($nCount > 0) {
					$oObject->set(MetaModel::GetConfig()->GetModuleSetting('ait-select-notified', 'fieldForRegEx', ''), $sRegExMail);
				} else {
					$oObject->set(MetaModel::GetConfig()->GetModuleSetting('ait-select-notified', 'fieldForRegEx', ''), "rand.mail@foo_bar.xyz");
				}
			}
		}
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
