<?php

/**
 * @author      Raphaël Saget
 * @copyright   Copyright (C) 2017 AxelIT
 * @license     http://opensource.org/fdlicenses/AGPL-3.0
 */

//Bug with key 'nom_patient' impossible to get the element associated to this key that's why i've used current($aTicketData) to get the first element
//Add org_comp & org_aff to ticket if Capio is ok

class ImportCSVFromWebform implements iBackgroundProcess
{
    public function GetPeriodicity()
    {   
        return MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'periodicity', '');
    }

    public function Process($iTimeLimit)
    {
        $sPath = MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'dir', '');

        //Relative path
        if(strpos($sPath, '..') !== false) {
            if($sPath[0] !== '/') {
                $sPath = dirname(__FILE__).'/'.$sPath;
            }
            else
            {
                $sPath = dirname(__FILE__).$sPath;
            }

            if(substr($sPath, -1) !== '/') {
                $sPath = $sPath.'/';
            }
        }

        $aScanned_directory = array_diff(scandir($sPath), array('..','.','.DS_Store'));

        while(time() < $iTimeLimit && !empty($aScanned_directory)) {
            $iProcessed = 0;

            if (is_dir($sPath)) {
                if ($hDirHandler = opendir($sPath)) {
                    while (($sFile = readdir($hDirHandler)) !== false) {
                        $aPathParts = pathinfo($sPath.$sFile);

                        if($aPathParts['extension'] === 'csv') {
                            echo "Processing file : $sFile"."\n";
                            $iRow = 0;

                            if (($hFileHandler = fopen($sPath.$sFile, "r")) !== FALSE) {
                                $aTmpCSVArrays = array();

                                while (($aCSVData = fgetcsv($hFileHandler, 0, ",")) !== FALSE) {
                                    $iNum = count($aCSVData);
                                    for ($c=0; $c < $iNum; $c++) {
                                        $aCSVData[$c] = str_replace('"', '', $aCSVData[$c]);
                                    }
                                    $aTmpCSVArrays[$iRow] = $aCSVData;
                                    $iRow++;
                                }

                                if(count($aTmpCSVArrays) !== 2) {
                                    print("Error : file $sFile is wrongly formated.\n");
                                    fclose($hFileHandler);
                                } else {
                                    $aTicketData = array_combine($aTmpCSVArrays[0],$aTmpCSVArrays[1]);
                                    
                                    try {
                                        $this->CreateTicketFromCSV($aTicketData, $sPath);

                                        fclose($hFileHandler);

                                        $this->CleanAfterCreation($aTicketData, $sFile, $sPath);
                                    } catch(Exception $e) {
                                        print($e->GetMessage());
                                    } finally {
                                        fclose($hFileHandler);
                                    }
                                }
                            }
                            $iProcessed++; 
                        }
                        
                    }
                closedir($hDirHandler);
                }
            }

            return $iProcessed." CSV files processed.";
        }
    }

    public function CreatePersonFromCSV($aTicketData) {
        $oPerson = MetaModel::NewObject('Person');

        reset($aTicketData);

        $oPerson->Set('name', current($aTicketData));

        if (MetaModel::IsValidAttCode(get_class($oPerson), 'first_name'))
        {
            $oPerson->Set('first_name', $aTicketData[MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'first_name', '')]);
        }

        if (MetaModel::IsValidAttCode(get_class($oPerson), 'phone'))
        {
            $oPerson->Set('phone', $aTicketData[MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'phone', '')]);
        }

        if (MetaModel::IsValidAttCode(get_class($oPerson), 'email'))
        {
            $oPerson->Set('email', $aTicketData[MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'email', '')]);
        }

        if (MetaModel::IsValidAttCode(get_class($oPerson), 'birthdate'))
        {
            $oPerson->Set('birthdate', $aTicketData[MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'birthdate', '')]);
        }

        $oOrgRequestSet = new DBObjectSet(DBObjectSearch::FromOQL('SELECT Organization WHERE id = :id'),
              array(),
              array(
                'id' => MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'org_id', ''),
              ));

        if ($oOrgRequestSet->Count() < 1) {
            throw new Exception("Error : Invalid organization for user creation ! Check configuration file, 'org_id' attribute.\n");
        } 

        $oPerson->Set('org_id', MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'org_id', ''));

        $oPerson->DBInsert();
        print("Contact : ".$aTicketData[MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'email', '')]." created with ID ".$oPerson->GetKey()."\n");

        return $oPerson;
    }

    public function CreateTicketFromCSV($aTicketData, $sPath) {
        $oPerson = null;
        $oTicket = MetaModel::NewObject(MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'ticket_type', ''));
        
        $sPersonQuery = 'SELECT Person WHERE email=:mail';
        
        $oPersonRequestSet = new DBObjectSet(DBObjectSearch::FromOQL($sPersonQuery),
              array(),
              array(
                'mail' => $aTicketData[MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'email', '')],
              ));

        if ($oPersonRequestSet->Count() < 1) {
            print("Contact not found, trying to create it...\n");
            try {
                $oPerson = $this->CreatePersonFromCSV($aTicketData);
            } catch(Exception $e) {
                throw $e;
            }
        } else {
            $oPerson = $oPersonRequestSet->Fetch();
        }


        if (MetaModel::IsValidAttCode(get_class($oTicket), 'caller_id'))
        {
            $oTicket->Set('caller_id', $oPerson->GetKey());
        }

        if (MetaModel::IsValidAttCode(get_class($oTicket), 'case_number'))
        {
            $oTicket->Set('case_number', $aTicketData[MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'case_number', '')]);
        }

        if (MetaModel::IsValidAttCode(get_class($oTicket), 'ns_entree_via'))
        {
            $oTicket->Set('ns_entree_via', $aTicketData[MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'ns_entree_via', '')]);
        }

        if (MetaModel::IsValidAttCode(get_class($oTicket), 'ns_entree_via'))
        {
            $oTicket->Set('ns_entree_via', $aTicketData[MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'ns_entree_via', '')]);
        }

        if (MetaModel::IsValidAttCode(get_class($oTicket), 'ns_date_sortie'))
        {
            $oTicket->Set('ns_date_sortie', $aTicketData[MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'ns_date_sortie', '')]);
        }

        if (MetaModel::IsValidAttCode(get_class($oTicket), 'title'))
        {
            $oTicket->Set('title', $aTicketData[MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'title', '')]);
        }

        if (MetaModel::IsValidAttCode(get_class($oTicket), 'description'))
        {
            $oTicket->Set('description', $aTicketData[MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'description', '')]);
        }
        
        $oServiceSubRequestSet = new DBObjectSet(DBObjectSearch::FromOQL('SELECT ServiceSubcategory WHERE name=:name'),
              array(),
              array(
                'name' => $aTicketData[MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'servicesubcategory_name', '')],
              ));

        if ($oServiceSubRequestSet->Count() < 1) {
            throw new Exception("Error : Service ID not found !"); 
        }

        $oServiceSubCategory = $oServiceSubRequestSet->Fetch();

        if (MetaModel::IsValidAttCode(get_class($oTicket), 'service_id'))
        {
            $oTicket->Set('service_id', $oServiceSubCategory->Get('service_id'));
        }

        if (MetaModel::IsValidAttCode(get_class($oTicket), 'service_id'))
        {
            $oTicket->Set('servicesubcategory_id', $oServiceSubCategory->GetKey());
        }

        $oOrgRequestSet = new DBObjectSet(DBObjectSearch::FromOQL('SELECT Organization WHERE name = :name'),
              array(),
              array(
                'name' => $aTicketData[MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'org_name', '')],
              ));

        if ($oOrgRequestSet->Count() < 1) {
            throw new Exception("Error : Invalid organization name !\n");
        } 

        $oOrganization = $oOrgRequestSet->Fetch();

        $oTicket->Set('org_id', $oOrganization->GetKey());
        //No need to go else, if DBInsert fail it will throw an exception and it will be catched in parent try
        if($oTicket->DBInsert()) {
            print("Ticket n° ".$oTicket->GetKey()." created from CSV.\n");

            for($a=1;$a<6;$a++) {
                $sAttachment = $aTicketData["fichier_".$a];
                if(!empty($sAttachment)) {
                    $sFileName = substr($sAttachment, strrpos($sAttachment, '/') + 1);
                    $sNormalizedAttachPath = realpath($sPath.$sFileName);
                    if(!empty($sNormalizedAttachPath)) {
                        print("Processing attachment : $sFileName \n");

                        try {
                            $this->ProcessAttachment($sPath.$sFileName, $oTicket->GetKey(), $oTicket->Get('org_id'));
                        } catch (Exception $e) {
                            throw $e;
                        }
                    }
                }
            }
        } 
    }

    private function ProcessAttachment($FilePath, $sTicketID, $sTicketOrgID) {
        $aResult = array(
            'error' => '',
            'att_id' => 0,
            'preview' => 'false',
            'msg' => ''
        );
        $sObjClass = MetaModel::GetConfig()->GetModuleSetting('ait-import-webform-csv', 'ticket_type', 'UserRequest');
        $sTempId = rand();

        try
        {
            $oDoc = $this->ReadAttachedDocument($FilePath);
            $oAttachment = MetaModel::NewObject('Attachment');
            $oAttachment->Set('expire', time() + 3600); // one hour...
            $oAttachment->Set('temp_id', $sTempId);
            $oAttachment->Set('item_class', $sObjClass);
            $oAttachment->Set('item_id', $sTicketID);
            $oAttachment->Set('item_org_id', $sTicketOrgID);
            $oAttachment->Set('contents', $oDoc);
            $iAttId = $oAttachment->DBInsert();
            
            $aResult['msg'] = htmlentities($oDoc->GetFileName(), ENT_QUOTES, 'UTF-8');
            $aResult['icon'] = utils::GetAbsoluteUrlAppRoot().AttachmentPlugIn::GetFileIcon($oDoc->GetFileName());
            $aResult['att_id'] = $iAttId;
            $aResult['preview'] = $oDoc->IsPreviewAvailable() ? 'true' : 'false';
        }
        catch (FileUploadException $e)
        {
            throw $e;
        }
    }

    private function ReadAttachedDocument($sFilePath)
    {
        $aFileInfo = pathinfo($sFilePath);
        $oDocument = new ormDocument(); // an empty document
        $sMimeType = "";

        $doc_content = file_get_contents($sFilePath);
        if (function_exists('finfo_file'))
        {
            // as of PHP 5.3 the fileinfo extension is bundled within PHP
            // in which case we don't trust the mime type provided by the browser
            $rInfo = @finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
            if ($rInfo !== false)
            {
                $sType = @finfo_file($rInfo, $sFilePath);
                if ( ($sType !== false) && is_string($sType) && (strlen($sType)>0))
                {
                    $sMimeType = $sType;
                }
            }
            @finfo_close($rInfo);
        }
        $oDocument = new ormDocument($doc_content, $sMimeType, $aFileInfo['basename']);
        return $oDocument;
    }

    private function CleanAfterCreation($aTicketData, $sCSVName, $sPath) {
        $sNormalizedCSVPath = realpath($sPath.$sCSVName);
        if($sNormalizedCSVPath && is_writable($sNormalizedCSVPath)) {
            if(!unlink($sNormalizedCSVPath)) {
                print("Failed to delete CSV file, check permission or path !\n");
            }
        }

        for($a=1;$a<6;$a++) {
            $sAttachment = $aTicketData["fichier_".$a];
            if(!empty($sAttachment)) {
                $sFileName = substr($sAttachment, strrpos($sAttachment, '/') + 1);
                $sNormalizedAttachPath = realpath($sPath.$sFileName);
                if($sNormalizedAttachPath && is_writable($sNormalizedAttachPath)) {
                    if(!unlink($sNormalizedAttachPath)) {
                        print("Failed to delete attachment file, check permission or path !\n");
                    }
                }
            }
        }
    }
}
