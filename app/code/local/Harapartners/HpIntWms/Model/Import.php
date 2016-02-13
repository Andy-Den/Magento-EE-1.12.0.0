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
class Harapartners_HpIntWms_Model_Import
{

	protected $_result = array();
	protected $_resultMsg ='';

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

    public function syncInventory($isUseConfigDate = true, $date = null)
    {
    	$currentTime = strtotime('-2 minutes');
    	if ($isUseConfigDate) {
			$date = Mage::getStoreConfig ( 'hpintwms/sync_setting/inventory_date_last_sync' );
			if (empty ( $date )) {
				$date = 1; // This is 30 years ago
			}
		}

        if (! empty ( $date ) && is_string ( $date )) { // HMM
			$date = strtotime( $date );
        }
        
        $conection = Mage::getSingleton('hpintwms/adapter');
        $requestGen = $conection->getRequestGen('inventory');
        // $inventory = $conection->getInventory($date);
        $inventory = $conection->fetchAll($requestGen->getInventory($date)); 
        /* @var $inventoryModel Harapartners_HpIntWms_Model_Import_Inventory  */
        $inventoryModel = Mage::getModel('hpintwms/import_inventory');
        $returnArray = $inventoryModel->processInventoy($inventory);
        
        // Sync Save
        $dateString = date('c', $currentTime);
        $this->_saveSyncDate($dateString);
        
        // The return
        $found = $returnArray[Harapartners_HpIntWms_Model_Import_Inventory::COUNT_UPDATED];
        $notFound = $returnArray[Harapartners_HpIntWms_Model_Import_Inventory::COUNT_PRODUCT_NOT_FOUND];
        $this->_result = $returnArray;
        $this->_resultMsg = "Products Inventory Synced: {$found} Products Not Found: {$notFound}";
    }
    
    protected function _saveSyncDate ($dateString){
    	$mageConfig = Mage::getModel('core/config');
		$mageConfig->saveConfig('hpintwms/sync_setting/inventory_date_last_sync', $dateString);
    }

    public function processTrackings()
    {
        $now = time(); // TODO use better
        $isForceLog = false; // TODO get From config??
        $conection = Mage::getSingleton('hpintwms/adapter');
        $requestGen = $conection->getRequestGen('tracking');
        /* @var $trackingModel Harapartners_HpIntWms_Model_Import_Tracking  */
        $trackingModel = Mage::getModel('hpintwms/import_tracking');
        
        // Get the Open Trackings
        $trackings = $conection->fetchAll($requestGen->getOpenTrackings()); 
        //$trackings = $this->_getTrackingResultMockArray();
        
        if(!$trackings || count($trackings)==0){
        	// No Items so return
        	$_result = array();
        	$this->_resultMsg = 'No Trackings Found on WMS';
        	return;
        }
        
        // Process them ->
        $result = $trackingModel->processTrackings($trackings);
        
        // Update the WMS that they are completed
        $rowsAffected = $conection->update($requestGen->syncBackTrackingsQtyEach($trackingModel->get_ordersCompletedArray()));
        
        // Logging Portion
        $numberMageErrors = $trackingModel->get_numberError();
        $numberMageCreated = $trackingModel->get_numberCreated ();
		$numberMageOrderNotFound = $trackingModel->get_numberOrderNotFound ();
		if ($rowsAffected == $numberMageCreated) {
			$syncbackMsg = 'All Synced Back';
		} else if ($rowsAffected === - 1) {
			$syncbackMsg = 'Total Sync Failure';
		} else if ($rowsAffected < $numberMageCreated) {
			$syncbackMsg = 'Partail Sync Back: ' . $rowsAffected;
		} else {
			$syncbackMsg = 'Unknown Sync Result';
		}
		
		$logMsg = "Tracking Sync on {$now}, Errors: {$numberMageErrors}, Not Found: {$numberMageOrderNotFound}, Created: {$numberMageCreated}. " . $syncbackMsg;
        Mage::log($logMsg, Zend_Log::INFO, 'WMS_sync.txt', $isForceLog);
        
        // Set ending log
        $this->_result = array('outgoing'=>$rowsAffected,'incoming'=>$result);
        $this->_resultMsg = $logMsg;
    }

    public function syncRmas()
    {
        $errors = 0;
        $affected = 0;
        
        $conection = Mage::getSingleton('hpintwms/adapter');
        $requestGen = $conection->getRequestGen('rma');
        $rmaItems = $conection->fetchAll($requestGen->getRecievedRmaItemsByView('now'));
        
        if (! $rmaItems === false) {
            /* @var $rmaSyncModel Harapartners_HpIntWms_Model_Import_Rma  */
            $rmaSyncModel = Mage::getModel('hpintwms/import_rma');
            $results = $rmaSyncModel->processRmaItems($rmaItems);
        } else {
            Mage::log('Error with statement/conection', Zend_Log::INFO, 'WMS_sync.txt', $isForceLog);
        }
        
        // Log Msg
        $msgIds = implode(', ',$results);
        $this->_result = $results;
        $this->_resultMsg = "Rma's Synced Back: {$msgIds}";
    }
    
    protected function _getTrackingResultMockArray(){
    	return array(
            array(
                'Site' => 'HardCodedSite' , 
                'OrderNumber' => '123555' , 
                'ItemNumber' => '2222' , 
                'ActualQuantity' => '2' , 
                'ShipDate' => strtotime('-4 days') , 
                'TrackingNumber' => '1z123123123jscf2222' , 
                'Charges' => '5.00'
            ) , 
            array(
                'Site' => 'HardCodedSite' , 
                'OrderNumber' => '123123' , 
                'ItemNumber' => '3333' , 
                'ActualQuantity' => '2' , 
                'ShipDate' => strtotime('-5 days') , 
                'TrackingNumber' => '1z123123123jscf5555' , 
                'Charges' => '5.00'
            ) , 
            array(
                'Site' => 'HardCodedSite' , 
                'OrderNumber' => '123123' , 
                'ItemNumber' => '4444' , 
                'ActualQuantity' => '2' , 
                'ShipDate' => strtotime('-2 days') , 
                'TrackingNumber' => '1z123123123jscf7777' , 
                'Charges' => '5.00'
            )
        );
    }
}
