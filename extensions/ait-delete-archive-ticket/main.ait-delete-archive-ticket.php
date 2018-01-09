<?php

/**
 * @author      Raphaël Saget
 * @copyright   Copyright (C) 2017 AxelIT
 * @license     http://opensource.org/fdlicenses/AGPL-3.0
 */

class DeleteOldTicket implements iBackgroundProcess
{
    public function GetPeriodicity()
    {   
        return MetaModel::GetConfig()->GetModuleSetting('ait-delete-archive-ticket', 'periodicity', '');
    }

    public function Process($iTimeLimit)
    {
        while(time() < $iTimeLimit) {
            $iProcessed = 0;

            $oExpectedDate = new DateTime();
            $oInterval = new DateInterval('PT'.MetaModel::GetConfig()->GetModuleSetting('ait-delete-archive-ticket', 'older_than', '').'S');
            $oExpectedDate = $oExpectedDate->sub($oInterval);
            $sTicketQuery = 'SELECT Ticket WHERE close_date < :expected_date';
            $oTicketRequestSet = new DBObjectSet(DBObjectSearch::FromOQL($sTicketQuery),
              array(),
              array(
                'expected_date' => $oExpectedDate->format('Y-m-d H:i:s'),
              ));

            while($oTicket = $oTicketRequestSet->Fetch())
            {
                print("Ticket ID ".$oTicket->GetKey()." will be deleted !\n");
                $oTicket->DBDelete();
                $iProcessed++; 
            }
            
            return $iProcessed." tickets deleted before ".$oExpectedDate->format('Y-m-d H:i:s');
        }
    }
}
