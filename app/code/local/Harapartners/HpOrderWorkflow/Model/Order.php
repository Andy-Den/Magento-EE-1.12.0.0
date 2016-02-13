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
class Harapartners_HpOrderWorkflow_Model_Order
{
    protected $_wmsModel = null;
    protected $_dropshipModel = null;
    protected $_backorderModel = null;
    
    protected $_errorMsg = '';

    protected function _getWMS()
    {
        if (! $this->_wmsModel) {
            $this->_wmsModel = Mage::getModel('hpintwms/order');
        }
        return $this->_wmsModel;
    
    }

    /**
     * Currently Unused
     *
     * @return unknown
     */
    protected function _getBackorder()
    {
        if (! $this->_backorderModel) {
            $this->_backorderModel = Mage::getModel('SmBackorder/order');
        }
        return $this->_backorderModel;
    }

    protected function _getDropship()
    {
        if (! $this->_dropshipModel) {
            $this->_dropshipModel = Mage::getModel('shoemartedi/order');
        }
        return $this->_dropshipModel;
    }

    // === End Get === //
    

    public function processOrder($order)
    {
        // Validation
        if (! $this->_isOrderValid($order)) {
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'cs_needed', 'OF: Parse Failed: ' . $this->_errorMsg);
            return false;
        }
        
        $isCustServiceNeeded = false;
        $qtys = array();
        $wmsModel = $this->_getWMS();
        $dropshipModel = $this->_getDropship();
        ////$backorderModel = $this->_getBackorder();
        $activeProcessModels = array();
        $processModels = array(
            'WMS' => $wmsModel , 
            'Dropship' => $dropshipModel
        );
        
        $items = $order->getAllItems();
        foreach ($items as $index => $item) {
            $items[$item->getId()] = $item;
            unset($items[$index]);
            
            if (! $this->_isItemValid($item)) {
                // Take Simple Product
                // TODO Handle bundle product
                continue;
            }
            
            $itemId = $item->getId();
            $shipableLeft[$itemId] = $item->getSimpleQtyToShip();
            foreach ($processModels as $name => $processModel) {
                $handleable = $processModel->isSuitable($item, $shipableLeft[$itemId]);
                if ($handleable > 0) {
                    $activeProcessModels[$name] = $processModel;
                    $qtys[$name][$itemId] = $handleable;
                    $shipableLeft[$itemId] -= $handleable;
                    if ($shipableLeft[$itemId] == 0) {
                        unset($shipableLeft[$itemId]);
                        break;
                    }
                } else {
                    $qtys[$name][$itemId] = 0;
                }
            }
        }
        
        // Actually call the handle routine
        $result = array();
        $resultByItemId = array();
        foreach ($activeProcessModels as $name => $processModel) {
            try {
                $result[$name] = $processModel->processOrder($order, $qtys[$name], $items);
                if ($result[$name]) {
                    foreach ($result[$name] as $itemId => $itemQty) {
                        $resultByItemId[$itemId] += $itemQty;
                    }
                }
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        
        // Handle extra stock
        $itemString = ' ';
        foreach ($shipableLeft as $itemId => $qty) {
            if ($qty > 0) {
                $isCustServiceNeeded = true;
                $itemString .= "ItemId: {$itemId} => {$qty}. PId: {$items[$itemId]->getProductId()}    " . PHP_EOL;
            }
        }
        
        // Write things places
        $resultString = 'WORKFLOW PROCESSED' . PHP_EOL . 'By Process:' . print_r($result, true) . PHP_EOL . 'By Item;' . print_r($resultByItemId, true) . PHP_EOL;
        if ($isCustServiceNeeded) {
            $csString = '    CustomerService; Items Not Handled: ' . $itemString;
            $outString = $resultString . $csString;
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'cs_needed', $outString);
        } else {
            $order->addStatusHistoryComment($resultString);
        }
        
        $order->save();
    }

    protected function _isOrderValid($order)
    {
        // Validate for an address
        if ($order->getBillingAddress() == null && $order->getShippingAddress() == null) {
            $this->_errorMsg = 'Missing All Address Info';
            return false;
        }
        
        return true;
    }

    protected function _isItemValid($item)
    {
        //        if ($item->getParentItem()->getProduct()->getId() && ! $item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
        //            return false;
        //        }
        if ($item->getProduct()->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_SIMPLE) {
            return true;
        } else {
            return false;
        }
    }
}
