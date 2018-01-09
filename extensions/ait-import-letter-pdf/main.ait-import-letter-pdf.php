<?php

/**
 * @author      Raphaël Saget
 * @copyright   Copyright (C) 2017 AxelIT
 * @license     http://opensource.org/fdlicenses/AGPL-3.0
 */

//Bug with key 'nom_patient' impossible to get the element associated to this key that's why i've used current($aTicketData) to get the first element
//Add org_comp & org_aff to ticket if Capio is ok

class ImportPDFLetter implements iBackgroundProcess
{
    public function GetPeriodicity()
    {
        return MetaModel::GetConfig()->GetModuleSetting('ait-import-letter-pdf', 'periodicity', '');
    }

    public function Process($iTimeLimit)
    {
        $sPath = MetaModel::GetConfig()->GetModuleSetting('ait-import-letter-pdf', 'dir', '');

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

                        if($aPathParts['extension'] === 'pdf') {
                            echo "Processing file : $sFile"."\n";
                            try {

                                if($this->CreateTicketFromPDF($sFile, $sPath)) {
                                    $this->CleanAfterCreation($sFile, $sPath);
                                }
                            } catch (Exception $e) {
                                closedir($hDirHandler);
                                return $e->GetMessage();
                            }
                            $iProcessed++;
                        }
                    }
                closedir($hDirHandler);
                }
            }

            return $iProcessed." scanned letter imported !";
        }
    }

    public function CreatePersonFromPDF($sPersonName) {
        $oPerson = MetaModel::NewObject('Person');

        $oPerson->Set('name', $sPersonName);
        //For test purpose
        $oPerson->Set('first_name', " ");

        $oOrgRequestSet = new DBObjectSet(DBObjectSearch::FromOQL('SELECT Organization WHERE id = :id'),
              array(),
              array(
                'id' => MetaModel::GetConfig()->GetModuleSetting('ait-import-letter-pdf', 'org_id', ''),
              ));

        if ($oOrgRequestSet->Count() < 1) {
            throw new Exception("Error : Organization for contact not found ! Check configuration file.\n");
        }

        $oPerson->Set('org_id', MetaModel::GetConfig()->GetModuleSetting('ait-import-letter-pdf', 'org_id', ''));

        $oPerson->DBInsert();
        print("Contact : ".$sPersonName." created with ID ".$oPerson->GetKey()." \n");

        return $oPerson;
    }

    public function CreateTicketFromPDF($sFile, $sPath) {

        $oPerson = null;
        $oTicket = MetaModel::NewObject(MetaModel::GetConfig()->GetModuleSetting('ait-import-letter-pdf', 'ticket_type', ''));

        $aFileInfo = pathinfo($sFile);
        $aPdfInfo = explode("_", $aFileInfo['filename']);

        $oPersonRequestSet = new DBObjectSet(DBObjectSearch::FromOQL('SELECT Person WHERE name=:name'),
              array(),
              array(
                'name' => $aPdfInfo[2],
              ));

        if ($oPersonRequestSet->Count() < 1) {
            print("Contact not found, trying to create it...\n");

            try {
                $oPerson = $this->CreatePersonFromPDF($aPdfInfo[2]);
            }
            catch(Exception $e) {
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
            $oTicket->Set('case_number', $aPdfInfo[1]);
        }

        if (MetaModel::IsValidAttCode(get_class($oTicket), 'title'))
        {
            $oTicket->Set('title', $aPdfInfo[1]." - ".$aPdfInfo[2]);
        }

        if (MetaModel::IsValidAttCode(get_class($oTicket), 'description'))
        {
            $oTicket->Set('description', ' ');
        }

        if (MetaModel::IsValidAttCode(get_class($oTicket), 'origin'))
        {
            $oTicket->Set('origin', 'letter');
        }

        $oOrgRequestSet = new DBObjectSet(DBObjectSearch::FromOQL('SELECT Organization WHERE name = :name'),
              array(),
              array(
                'name' => MetaModel::GetConfig()->GetModuleSetting('ait-import-letter-pdf', 'organizations', '')[$aPdfInfo[0]],
              ));

        if ($oOrgRequestSet->Count() < 1) {
            throw new Exception("Error : Invalid organization for the ticket ! Check configuration file.");
        }

        $oOrganization = $oOrgRequestSet->Fetch();

        $oTicket->Set('org_id', $oOrganization->GetKey());

        if($oTicket->DBInsert()) {
            echo "Ticket n° ".$oTicket->GetKey()." created from PDF\n";

            $sNormalizedAttachPath = realpath($sPath.$sFile);
            if(!empty($sNormalizedAttachPath)) {
                print("Processing attachment : $sFile\n");
                $this->ProcessAttachment($sPath.$sFile, $oTicket->GetKey(), $oTicket->Get('org_id'));
            }
        } else {
            throw new Exception("Error : Ticket creation failed ! Some required fields may be missing.");
        }

        return true;
    }

    private function ProcessAttachment($FilePath, $sTicketID, $sTicketOrgID) {
        $aResult = array(
            'error' => '',
            'att_id' => 0,
            'preview' => 'false',
            'msg' => ''
        );
        $sObjClass = MetaModel::GetConfig()->GetModuleSetting('ait-import-letter-pdf', 'ticket_type', 'UserRequest');
        $sTempId = rand();

        $oDoc = $this->ReadAttachedDocument($FilePath);
        $oAttachment = MetaModel::NewObject('Attachment');
        $oAttachment->Set('expire', time() + 3600); // one hour...
        $oAttachment->Set('temp_id', $sTempId);
        $oAttachment->Set('item_class', $sObjClass);
        $oAttachment->Set('item_id', $sTicketID);
        $oAttachment->Set('item_org_id', $sTicketOrgID);
        $oAttachment->Set('contents', $oDoc);

        try {
            $iAttId = $oAttachment->DBInsert();
        } catch(Exception $e) {
            throw $e;
        }

        $aResult['msg'] = htmlentities($oDoc->GetFileName(), ENT_QUOTES, 'UTF-8');
        $aResult['icon'] = utils::GetAbsoluteUrlAppRoot().AttachmentPlugIn::GetFileIcon($oDoc->GetFileName());
        $aResult['att_id'] = $iAttId;
        $aResult['preview'] = $oDoc->IsPreviewAvailable() ? 'true' : 'false';
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

    private function CleanAfterCreation($sPDFName, $sPath) {
        $sNormalizedPDFPath = realpath($sPath.$sPDFName);
        if($sNormalizedPDFPath && is_writable($sNormalizedPDFPath)) {
            if(!unlink($sNormalizedPDFPath)) {
                echo "Failed to delete PDF file, check permission or path !\n";
            }
        }
    }
}
