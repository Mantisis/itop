
<?php

/**
 * @author      David BONTOUX
 * @copyright   Copyright (C) 2017 AxelIT
 * @license     http://opensource.org/fdlicenses/AGPL-3.0
 */

class RelaunchClinic implements iBackgroundProcess
{
    
    public function GetPeriodicity()
    {   
        return MetaModel::GetConfig()->GetModuleSetting('ait-clinic-relaunch', 'periodicity', '');
    }
     
    public function GetDelay()
    {   
        return MetaModel::GetConfig()->GetModuleSetting('ait-clinic-relaunch', 'delay', '');
    }
    public function GetAction()
    {   
        return MetaModel::GetConfig()->GetModuleSetting('ait-clinic-relaunch', 'action', '');
    }
    
    public function GetActive()
    {   
        return MetaModel::GetConfig()->GetModuleSetting('ait-clinic-relaunch', 'active', '');
    }
    
    public function Process($iTimeLimit)
    {
       
        
          while(time() < $iTimeLimit) {
                $iProcessed = 0;
                print("Begin : relaunch clinic \n" );
                //
                $dDateday = new DateTime("now");
                //Query : get UserRequest in pending_clinical status
                $sTicketQuery = "SELECT UserRequest  WHERE status='pending_clinical'";
                $oTicketRequestSet = new DBObjectSet(DBObjectSearch::FromOQL($sTicketQuery),
                  array(),
                  array());
                //Get notification 
                $iActionId =$this->GetAction();
                print("Action id :". $iActionId." \n"); 
                $oAction = MetaModel::GetObject('Action', $iActionId);
                if ($oAction->IsActive())
		{
                    //For each ticket in pending_clinical status 

                    while($oTicket = $oTicketRequestSet->Fetch())
                    {

                        $dDatetpending = new DateTime($oTicket->Get('last_pending_date'));

                        print("last_pending_date :".$dDatetpending->format('Y-m-d')."\n");

                        if($oTicket->Get('ns_last_relaunch_date')){
                           $dDatetRelaunch = new DateTime($oTicket->Get('ns_last_relaunch_date')); 
                        }
                        else{
                           $dDatetRelaunch = new DateTime('1901-01-01 01:00:00 EDT');
                        }

                        print("last_relaunch_date :".$dDatetRelaunch->format('Y-m-d')."\n");
                        //number of days between last pending date and now
                        $iInterval = $dDatetpending->diff($dDateday);
                        $iInter= $iInterval->format('%a');
                        print("interval :".$iInter." delay ".$this->GetDelay()."\n");
                        //every 28 days and if relaunch date different from today
                        //send an email
                        $i=$iInter%$this->GetDelay();

                        if(($i==0) &&($dDatetRelaunch->format('Y-m-d')!=$dDateday->format('Y-m-d') )){	
                            //change date of the last relaunch
                             print(" maj ticket :\n");
                            if (MetaModel::IsValidAttCode(get_class($oTicket), 'ns_last_relaunch_date'))
                                {   
                                $oTicket->Set('ns_last_relaunch_date',$dDateday->format('Y-m-d'));
                                }

                            $sPreviousUrlMaker = ApplicationContext::SetUrlMakerClass();
                            try
                            {
                                print("Création mail :\n");       

                                $bRes = false; // until we do succeed in sending the email

                                //Get informations from ticket
                                $aContextArgs=$oTicket->ToArgs('this'); 

                                // Determine recicipients
                                //
                                $sTo = $this->FindRecipients($oAction,'to', $aContextArgs);

                                $sCC = $this->FindRecipients($oAction,'cc', $aContextArgs);
                                $sBCC = $this->FindRecipients($oAction,'bcc', $aContextArgs);

                                $sFrom = MetaModel::ApplyParams($oAction->Get('from'), $aContextArgs);
                                $sReplyTo = MetaModel::ApplyParams($oAction->Get('reply_to'), $aContextArgs);
                                $sSubject = MetaModel::ApplyParams($oAction->Get('subject'), $aContextArgs);
                                $sBody = MetaModel::ApplyParams($oAction->Get('body'), $aContextArgs);


                                $oObj = $aContextArgs['this->object()'];

                                $sMessageId = sprintf('iTop_%s_%d_%f@%s.openitop.org', get_class($oObj), $oObj->GetKey(), microtime(true /* get as float*/), MetaModel::GetEnvironmentId());

                                $sReference = '<'.$sMessageId.'>';

                            }
                            catch(Exception $e)
                            {
                                    ApplicationContext::SetUrlMakerClass($sPreviousUrlMaker);
                                    throw $e;
                            }
                            ApplicationContext::SetUrlMakerClass($sPreviousUrlMaker);

                            $sStyles = file_get_contents(APPROOT.'css/email.css');
                            $sStyles .= MetaModel::GetConfig()->Get('email_css');
                            //create email    
                            $oEmail = new EMail();

                            if ($oAction->IsBeingTested())
                            {
                                    $oEmail->SetSubject('TEST['.$sSubject.']');
                                    $sTestBody = $sBody;
                                    $sTestBody .= "<div style=\"border: dashed;\">\n";
                                    $sTestBody .= "<h1>Testing email notification ".$oAction->GetHyperlink()."</h1>\n";
                                    $sTestBody .= "<p>The email should be sent with the following properties\n";
                                    $sTestBody .= "<ul>\n";
                                    $sTestBody .= "<li>TO: $sTo</li>\n";
                                    $sTestBody .= "<li>CC: $sCC</li>\n";
                                    $sTestBody .= "<li>BCC: $sBCC</li>\n";
                                    $sTestBody .= "<li>From: $sFrom</li>\n";
                                    $sTestBody .= "<li>Reply-To: $sReplyTo</li>\n";
                                    $sTestBody .= "<li>References: $sReference</li>\n";
                                    $sTestBody .= "</ul>\n";
                                    $sTestBody .= "</p>\n";
                                    $sTestBody .= "</div>\n";
                                    $oEmail->SetBody($sTestBody, 'text/html', $sStyles);
                                    $oEmail->SetRecipientTO($oAction->Get('test_recipient'));
                                    $oEmail->SetRecipientFrom($oAction->Get('test_recipient'));
                                    $oEmail->SetReferences($sReference);
                                    $oEmail->SetMessageId($sMessageId);
                            }
                            else
                            {
                                    $oEmail->SetSubject($sSubject);
                                    $oEmail->SetBody($sBody, 'text/html', $sStyles);
                                    $oEmail->SetRecipientTO($sTo);
                                    $oEmail->SetRecipientCC($sCC);
                                    $oEmail->SetRecipientBCC($sBCC);
                                    $oEmail->SetRecipientFrom($sFrom);
                                    $oEmail->SetRecipientReplyTo($sReplyTo);
                                    $oEmail->SetReferences($sReference);
                                    $oEmail->SetMessageId($sMessageId);
                            }



                            $iRes = $oEmail->Send($aErrors, false, $oLog); // allow asynchronous mode
                            switch ($iRes)
                            {
                                    case EMAIL_SEND_OK:
                                            //update ticket with ns_last_relaunch_date
                                            $oTicket->DBUpdate();
                                            return "Sent";

                                    case EMAIL_SEND_PENDING:
                                            return "Pending";

                                    case EMAIL_SEND_ERROR:

                                            print_r("Ticket ID  no email send to ".$sTo."\n");
                                            return "Errors: ".implode(', ', $aErrors);
                            }






           ///////////////////////////////////////////////                 
                         print("Ticket ID ".$oTicket->GetKey()." in pending clinical for more than".  $this->GetDelay() ."days: email sent to clinic ! \n");

                        $iProcessed++;
                        }
                    }
                }
                else{
                    print("Notification desactivated \n");
                }
                return $iProcessed." emails sent to clinic  ".$dDateday->format('Y-m-d H:i:s').'\n';
            }   
        
        
           
    }
    
    // returns a the list of emails as a string, or a detailed error description
	protected function FindRecipients($oAction, $sRecipAttCode, $aArgs)
	{
		$sOQL = $oAction->Get($sRecipAttCode);
                
		if (strlen($sOQL) == '') return '';

		try
		{
			$oSearch = DBObjectSearch::FromOQL($sOQL);
			$oSearch->AllowAllData();
		}
		catch (OQLException $e)
		{
			
			return $e->getMessage();
		}

		$sClass = $oSearch->GetClass();
		// Determine the email attribute (the first one will be our choice)
		foreach (MetaModel::ListAttributeDefs($sClass) as $sAttCode => $oAttDef)
		{
			if ($oAttDef instanceof AttributeEmailAddress)
			{
				$sEmailAttCode = $sAttCode;
				// we've got one, exit the loop
				break;
			}
		}
		if (!isset($sEmailAttCode))
		{
			
			return "The objects of the class '$sClass' do not have any email attribute";
		}

		$oSet = new DBObjectSet($oSearch, array() /* order */, $aArgs);
                
		$aRecipients = array();
		while ($oObj = $oSet->Fetch())
		{
			$sAddress = trim($oObj->Get($sEmailAttCode));
			if (strlen($sAddress) > 0)
			{
				$aRecipients[] = $sAddress;
				
			}
		}
		return implode(', ', $aRecipients);
	}
}
