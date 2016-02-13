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
class Harapartners_HpIntWms_Model_Import_Rma
{
    protected $_numCreated;
    protected $_numOrderNotFound;
    protected $_numErrors;
    protected $_ordersCompleted = array();
    
    protected $_rmaSourceStatusModel = null;
    
    /**
     * array (
     *     rma_number_1 => array(
     *         rma_item_number => rma_info
     *             ...
     *             rma_item_n => rma_info_n
     *         ),
     *         ...
     *     ),
     *   ...
     * )
     *
     * @var array
     */
    protected $_processedRmaItems = array();

    /**
     * Updates Rma and associated info
     * Sends Emails
     *
     * @param unknown_type $rmaItems
     * @return int -1 if errors ( +numOrderNotFound) , numberCreated if no errors 
     */
    public function processRmaItems($rmaItems)
    {
        // Render Stuff
        $this->_processRmaItemInfoIntoArray($rmaItems);
        
        foreach ($this->_processedRmaItems as $rmaId => $rmaItemsInfo) {
            if ($this->_saveRmaItemUpdates($rmaId, $rmaItemsInfo)) {
                $rmaSyncBackIds[] = $rmaId;
            }
        }
        
        if ($this->_syncBackRmaItems($rmaSyncBackIds)) {
            $this->_createCreditMemos($this->_processedRmaItems);
        }
        return $rmaSyncBackIds;
    }

    protected function _processRmaItemInfoIntoArray($rmaItems)
    {
        foreach ($rmaItems as $rmaItem) {
            $this->_processedRmaItems[$rmaItem['OrderNumber']][$rmaItem['LineNumber']] = $rmaItem;
        }
    }

    protected function _saveRmaItemUpdates($rmaId, array $rmaInfoArray)
    {
        $rmaStatusModel = $this->_getRmaSourceStatusModel();
        $saveTransaction = Mage::getModel('core/resource_transaction');
        $rmaItemStatusArray = array();
        $rmaModel = Mage::getModel('enterprise_rma/rma')->load($rmaId, 'increment_id');
        $itemCollection = Mage::getResourceModel('enterprise_rma/item_collection')->addFieldToFilter('rma_entity_id', $rmaModel->getId());
        
        foreach ($itemCollection as $mageRmaItem) {
            if (isset($rmaInfoArray[$mageRmaItem->getOrderItemId()])) {
                $itemInfo = $rmaInfoArray[$mageRmaItem->getOrderItemId()];
                
                // Item Info -> If rejection is not possible
                $itemInfo['ApprovedQuantity'] = isset($itemInfo['ApprovedQuantity']) ? $itemInfo['ApprovedQuantity'] : $itemInfo['ActualQuantity'];
                
                // TODO this is gonna be complicated with the picture. maybe? Collect Into status comments
                $mageRmaItem->setQtyReturned($itemInfo['ActualQuantity']);
                $mageRmaItem->setQtyApproved($itemInfo['ApprovedQuantity']);
                if ($itemInfo['ApprovedQuantity'] > 0) {
                    // TODO Handle partial?? How?
                    $mageRmaItem->setStatus(Enterprise_Rma_Model_Rma_Source_Status::STATE_APPROVED);
                } elseif ($itemInfo['ActualQuantity'] > 0) {
                    $mageRmaItem->setStatus(Enterprise_Rma_Model_Rma_Source_Status::STATE_REJECTED);
                }
                
                $saveTransaction->addObject($mageRmaItem);
            }
            
            $rmaItemStatusArray[] = $mageRmaItem->getStatus();
        }
        
        $rmaModel->setStatus($rmaStatusModel->getStatusByItems($rmaItemStatusArray));
        
        try {
            $saveTransaction->addObject($rmaModel)->save();
            return true;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    
    }

    /**
     * Updates the header table in WMS to synced = 2
     *
     * @param array $rmaIds The rmaIDs (as WMS OrderID)
     * @return bool
     */
    protected function _syncBackRmaItems($rmaIds)
    {
        /* @connection Harapartners_HpIntWms_Model_Adapter */
        $conection = Mage::getSingleton('hpintwms/adapter');
        /* @var $requestGen Harapartners_HpIntWms_Model_Adapter_Request_Rma */
        $requestGen = $conection->getRequestGen('rma');
        return $conection->update($requestGen->syncBackRmaItemsFull($rmaIds));
    }

    protected function _createCreditMemos($processedRmaItems)
    {
        foreach ($processedRmaItems as $rmaId => $rmaItemsInfo) {
            foreach ($rmaItemsInfo as $rmaItemsInfo) {
                $data[qtys][$rmaItemsInfo['LineNumber']] = $rmaItemsInfo['ApprovedQty'];
            }
            
            // TODO Get Adjustment Data
            $data['adjustment_negative'] = 7.99; // TODO unhardCode
            

            $service = Mage::getModel('sales/service_order', Mage::getModel('enterprise_rma/rma')->load($rmaId, 'increment_id')->getOrder());
            $creditmemo = $service->prepareCreditmemo($data);
            try {
                $creditmemo->save();
                return true;
            } catch (Exception $e) {
                Mage::logException($e);
                return false;
            }
        }
    }

    protected function _getRmaSourceStatusModel()
    {
        if (! isset($this->_rmaSourceStatusModel)) {
            $this->_rmaSourceStatusModel = Mage::getModel('enterprise_rma/rma_source_status');
        }
        return $this->_rmaSourceStatusModel;
    }
    
//
//    /**
//     * @return int
//     */
//    public function get_numberCreated()
//    {
//        return $this->_numCreated;
//    }
//
//    /**
//     * @return int
//     */
//    public function get_numberError()
//    {
//        return $this->_numErrors;
//    }
//
//    /**
//     * @return int
//     */
//    public function get_numberOrderNotFound()
//    {
//        return $this->_numOrderNotFound;
//    }
//
//    /**
//     * @return unknown
//     */
//    public function get_ordersCompletedArray()
//    {
//        return $this->_ordersCompleted;
//    }
//
//    public function _isAvailible($item)
//    {
//        // Possible Check for later
//        return true;
//    }
}
