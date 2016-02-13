<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */
class Harapartners_ShoemartEdi_Model_Edi_Inventory
{
	// For some reason I need a 'EXTRA' it does nothing atm. Unless I mismapped
    protected $headerMap = array(
        'RECORD-ID' , 
        'TRADING-PARTNER-ID' , 
        'INTERNAL-ID' , 
        'DOCUMENT-ID' , 
        'DATE' , 
        'VENDOR-NAME' , 
        'EXTRA'
    );
    protected $detailMap = array(
        'RECORD-ID' , 
        'UPC' , 
        'QUANTITY' , 
        'BACKORDER-DATE' , 
        'PRODUCTION-DESCRIPTION' , 
        'VENDOR-SIZE-DESCRIPTION' , 
        'BUYER-COLOR-DESCRIPTION' , 
        'EXTRA'
    );
    protected $summaryMap = array(
        'RECORD-ID' , 
        'NUM-LINE-ITEMS' , 
        'EXTRA'
    );

    public function parseInventoryFeed($fileLocation)
    {
        $csvReader = new Varien_File_Csv();
        $csvReader->setDelimiter('|');
        try {
            $lines = $csvReader->getData($fileLocation);
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
        
        $outputArray = array();
        $isDetail = true;
        $count = 0;
        
        // === Get Header === //
        $header = array_shift($lines);
        if ($header == null || $header[0] != 'HDR') {
            return null;
        }
        $tempLine = array_combine($this->headerMap, $header);
        $outputArray['header'] = $tempLine;
        
        // === Get Item lvl === //
        while ($isDetail) {
            $line = array_shift($lines);
            if ($line[0] == 'DTL') {
                $tempLine = array_combine($this->detailMap, $line);
                $outputArray['detail'][] = $tempLine;
                $count ++;
            } else {
                // SUM Line
                $tempLine = array_combine($this->summaryMap, $line);
                $outputArray['summary'] = $tempLine;
                $isDetail = false;
            }
        }
        
        return $outputArray;
    }

    public function renderInventoryFeed($parsedDocument)
    {
        foreach ($parsedDocument['detail'] as $itemRow) {
            $items[] = array(
                'upc' => $itemRow['UPC'] , 
                'qty' => $itemRow['QUANTITY'] , 
                'ediDate' => $itemRow['BACKORDER-DATE']
            );
        }
        return $items;
    }
}
