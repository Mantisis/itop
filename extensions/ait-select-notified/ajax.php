<?php
/**
* Module ait-select-notified
*
* @author      Raphaël Saget <r.saget@axelit.fr>
* @author      David Bontoux <d.bontoux@axelit.fr>
*/

require_once('../../approot.inc.php');
require_once(APPROOT.'/application/application.inc.php');
require_once(APPROOT.'/application/webpage.class.inc.php');
require_once(APPROOT.'/application/ajaxwebpage.class.inc.php');
require_once(APPROOT.'/application/wizardhelper.class.inc.php');
require_once(APPROOT.'/application/ui.linkswidget.class.inc.php');
require_once(APPROOT.'/application/ui.extkeywidget.class.inc.php');

try
{
	require_once(APPROOT.'/application/startup.inc.php');
	require_once(APPROOT.'/application/user.preferences.class.inc.php');

	require_once(APPROOT.'/application/loginwebpage.class.inc.php');

	$sTicketID = utils::ReadParam('id', '');
	$sNotified = utils::ReadParam('notified', '');
	$aContact = array();
	$sHTML = "";
	if($sNotified == "Clinique") {
		$oTicketRequestSet = new DBObjectSet(DBObjectSearch::FromOQL('SELECT Ticket WHERE id = :id'),
              array(),
              array(
                'id' => $sTicketID,
              ));

		$oTicket = $oTicketRequestSet->Fetch();

		$oClinicalRequestSet = new DBObjectSet(DBObjectSearch::FromOQL('SELECT Contact WHERE org_id = :org_id'),
              array(),
              array(
                'org_id' => $oTicket->Get('org_id'),
              ));

		while($oContact = $oClinicalRequestSet->Fetch()) {
			if($oContact->Get('email') != "") {
				$sHTML = $sHTML."<label><input type=\"checkbox\" name=\"listmail[".$oContact->Get('email')."]\" checked value=\"yes\">".$oContact->Get('email')."</label>";
			}
		}
	}
	else if ($sNotified == "Patient") {
		$oTicketRequestSet = new DBObjectSet(DBObjectSearch::FromOQL('SELECT Ticket WHERE id = :id'),
              array(),
              array(
                'id' => $sTicketID,
              ));

		$oTicket = $oTicketRequestSet->Fetch();

		$oPersonRequestSet = new DBObjectSet(DBObjectSearch::FromOQL('SELECT Person WHERE id = :id'),
            array(),
            array(
                'id' => $oTicket->Get('caller_id'),
              ));
		$oPerson = $oPersonRequestSet->Fetch();
		$sCallerMail = $oPerson->Get('email');

		$sHTML = $sHTML."<label><input type=\"checkbox\" name=\"listmail[".$oPerson->Get('email')."]\" checked value=\"yes\">".$oPerson->Get('email')."</label>";
	}
	else if ($sNotified = "Prestataire") {
		$oTicketRequestSet = new DBObjectSet(DBObjectSearch::FromOQL('SELECT Ticket WHERE id = :id'),
              array(),
              array(
                'id' => $sTicketID,
              ));

		$oTicket = $oTicketRequestSet->Fetch();

		$oOrgRequestSet =  new DBObjectSet(DBObjectSearch::FromOQL('SELECT Organization WHERE id = :id'),
            array(),
            array(
                'id' => $oTicket->Get('org_id'),
              ));

		$oOrg = $oOrgRequestSet->Fetch();

		$oContactRequestSet  =  new DBObjectSet(DBObjectSearch::FromOQL('SELECT Person WHERE org_id = :id'),
            array(),
            array(
                'id' => $oOrg->Get('prestataire_id'),
              ));

		while($oContact = $oContactRequestSet->Fetch()) {
			if($oContact->Get('email') != "") {
				$sHTML = $sHTML."<label><input type=\"checkbox\" name=\"listmail[".$oContact->Get('email')."]\" checked value=\"yes\">".$oContact->Get('email')."</label>";
			}
		}
	}

	echo $sHTML;
}
catch (Exception $e)
{
	echo $e->GetMessage();
	IssueLog::Error($e->getMessage());
}
?>
