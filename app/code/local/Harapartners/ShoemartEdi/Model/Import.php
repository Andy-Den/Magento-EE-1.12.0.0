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
class Harapartners_ShoemartEdi_Model_Import
{
    
    protected $_result = array();
    protected $_resultMsg = '';

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * @return string
     */
    public function getResultMsg()
    {
        return $this->_resultMsg;
    }

    public function processEdiInventoryDocuments($isConfig = true, $dirLocation = '')
    {
        $dirLocation = $this->_getActuallDirLocation($isConfig, $dirLocation);
        
        foreach (glob($dirLocation . DS . '846*.txt') as $fileLocation) {
            /* @var $invImportModel Harapartners_ShoemartEdi_Model_Import_Inventory  */
            $invImportModel = Mage::getModel('shoemartedi/import_inventory');
            $results[$fileLocation] = $invImportModel->importInventoryEdi($fileLocation);
            if ($invImportModel->isComplete()) {
                $isMoved = $invImportModel->moveCompletedDocument();
            }
        }
        
        // Msg Results
        $msg = 'Results: <br>' . PHP_EOL;
        foreach ($results as $path => $result) {
            $found = $result[Harapartners_ShoemartEdi_Model_Import_Inventory::COUNT_UPDATED];
            $notFound = $result[Harapartners_ShoemartEdi_Model_Import_Inventory::COUNT_PRODUCT_NOT_FOUND];
            $msg .= basename($path) . " Synced: {$found} Skipped: {$notFound}" . '<br>' . PHP_EOL;
        }
        $this->_result = $result;
        $this->_resultMsg = $msg;
    
    }

    public function processSingleShipmentEdiDoc($fileLocation)
    {
        /* @var $trackingModel Harapartners_ShoemartEdi_Model_Edi_Tracking */
        $trackingModel = Mage::getModel('shoemartedi/edi_tracking');
        $handle = fopen($fileLocation, 'r');
        // Can Have mulitple shipments per document
        while ($parsedArray = $trackingModel->getNextParseEdiTrackingDocument($handle)) {
            $orderId = $parsedArray[Harapartners_ShoemartEdi_Model_Edi_Tracking::SECTION_ORDER][Harapartners_ShoemartEdi_Model_Edi_Tracking::FIELD_ORDER_ID];
            $shipemtId = $parsedArray[Harapartners_ShoemartEdi_Model_Edi_Tracking::SECTION_SHIPMENT]['BILL-OF-LADING'];
            $result[$orderId][$shipemtId] = $trackingModel->createShipment($parsedArray);
        }
        
        if ($trackingModel->getIsDone()) {
            $isMoved = $trackingModel->moveCompletedDocument($fileLocation);
        }
        
        return $result;
    }

    public function processEdiShipmentDocuments($isConfig, $dirLocation)
    {
        $dirLocation = $this->_getActuallDirLocation($isConfig, $dirLocation);
        
        /* @var $trackingModel Harapartners_ShoemartEdi_Model_Edi_Tracking  */
        foreach (glob($dirLocation . DS . '856*.txt') as $fileLocation) {
            $results[$fileLocation] = $this->processSingleShipmentEdiDoc($fileLocation);
        }
        
        // Msg Results
        $msg = 'Results: <br>' . PHP_EOL;
        foreach ($results as $path => $order) {
            $msg .= basename($path) . '<br>' . PHP_EOL;
            foreach ($order as $orderId => $shipments) {
                $msg .= "OrderId: {$orderId}" . '<br>' . PHP_EOL;
                foreach ($shipments as $shipmentId => $shipmetResult) {
                    $resultString = $result ? 'Created' : 'Failed';
                    $msg .= "Result for {$shipmentId}: " . $resultString . '<br>' . PHP_EOL;
                }
            }
        }
        $this->_result = $result;
        $this->_resultMsg = $msg;
    }

    protected function _getActuallDirLocation($isConfig, $dirLocation)
    {
        if ($isConfig) {
            $dirLocation = Mage::getStoreConfig('shoemart_edi/sync_setting/base_dir');
            $dirLocation = Mage::getBaseDir('base') . DS . $dirLocation;
            $dirLocation .= DS . 'in';
        } else {
            // Do nothing path is from params
        }
        
        return realpath($dirLocation);
    }
}
