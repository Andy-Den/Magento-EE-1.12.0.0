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
class Harapartners_HpIntWms_Model_Export
{
    protected $_wmsInventoryModel = null;
    
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

    /**
     * This is the entry point for Submitting a Single order to WMS
     *
     * @param Mage_Sales_Model_Order $order
     * @param array $qtys
     * @param bool $isWorkflow Is this useing OrderWorkflow
     * 
     * @return $qtys|false $qtys processed or false on failure
     */
    public function submitOrder(Mage_Sales_Model_Order $order, $qtys, $isWorkflow = false, $items = null)
    {
        $errors = 0;
        $affected = 0;
        $conection = Mage::getSingleton('hpintwms/adapter');
        $requestGen = $conection->getRequestGen('order');
        
        /* @var $orderSyncModel Harapartners_HpIntWms_Model_Export_Order  */
        $orderSyncModel = Mage::getModel('hpintwms/export_order');
        if (! $orderSyncModel->generateParse($order, $qtys)) {
            // Error state durring parse
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'cs_needed', 'WMS Parse Failed: ' . $orderSyncModel->getErrorString());
            return false;
        }
        
        try {
            $conection->startTransaction();
            $result = $conection->insert($requestGen->getPostOrderHeader($orderSyncModel->getHeader()));
            $result ? $affected += $result : $errors += 1;
            $result = $conection->insert($requestGen->getPostOrderBilling($orderSyncModel->getBillingAddress()));
            $result ? $affected += $result : $errors += 1;
            $result = $conection->insert($requestGen->getPostOrderShipping($orderSyncModel->getShippingAddress()));
            $result ? $affected += $result : $errors += 1;
            
            if (! $errors) {
                $requests = $requestGen->getPostOrderItems($orderSyncModel->getAllItems());
                $table_name = $requests['table'];
                foreach ($orderSyncModel->getAllItems() as $item) {
                    $result = $conection->insert($requestGen->getPostOrderItem($item));
                    if ($result) {
                        $returnQtys[$item['ItemNumber']] = $item['OrderedQuantity'];
                        $affected += $result;
                    } else {
                        $returnQtys[$item['ItemNumber']] = false;
                        $errors += 1;
                    }
                }
            }
            
            if ($errors) {
                // Rollback??
                $conection->transactionRollback();
                return false;
            } else {
                $conection->transactionCommit();
                if ($isWorkflow) {
                    foreach ($returnQtys as $productId => $qty) {
                        //$productId = $items[$itemNumber]->getProductId();
                        $product = Mage::getModel('catalog/product')->setId($productId);
                        $this->_getWMSInventory()->subtractWMSStock($product, $qty);
                    }
                }
                return $qtys; // If partial success is allowed change this
            }
        } catch (Exception $e) {
            $conection->transactionRollback();
            return false;
        }
    }

    /**
     * This is the emtry point for the place special order logic
     * @todo impliment
     * 
     * @param array $parsedOrderInfo This is the Order parsed into EDI format
     * @param string $vendorCode Vendor Code
     * 
     * @return unknown
     */
    public function placeSpecialRecievingOrder($parsedOrderInfo, $lineItems)
    {
        return false;
        
        // Check for Items
        if (count($lineItems) == 0) {
            return true;
        }
        
        // Generate QTY's Map && vendor code info
        $vendorCode = $lineItems[0]['vendorCode'];
        foreach ($lineItems as $item) {
            $qtys[$item['itemId']] = $item['qty'];
        }
        
        // Get stuff
        $conection = Mage::getSingleton('hpintwms/adapter');
        $requestGen = $conection->getRequestGen('order');
        
        /* @var $orderSyncModel Harapartners_HpIntWms_Model_Export_Order  */
        $orderSyncModel = Mage::getModel('hpintwms/export_order');
        $orderSyncModel->generateParse($order, $qtys);
        
        // Convert to this array?
        

        // Start transaction
        try {
            $conection->startTransaction();
            
            // Place Picking Order
            $result = $conection->insert($requestGen->getPostOrderHeader($orderSyncModel->getHeader()));
            $result ? $affected += $result : $errors += 1;
            $result = $conection->insert($requestGen->getPostOrderBilling($orderSyncModel->getBillingAddress()));
            $result ? $affected += $result : $errors += 1;
            $result = $conection->insert($requestGen->getPostOrderShipping($orderSyncModel->getShippingAddress()));
            $result ? $affected += $result : $errors += 1;
            
            $returnQtys = array();
            if (! $errors) {
                $requests = $requestGen->getPostOrderItems($orderSyncModel->getAllItems());
                $table_name = $requests['table'];
                foreach ($orderSyncModel->getAllItems() as $item) {
                    $result = $conection->insert($requestGen->getPostOrderItem($item));
                    if ($result) {
                        $returnQtys[$item['LineNumber']] = $item['OrderedQuantity'];
                        $affected += $result;
                    } else {
                        $returnQtys[$item['LineNumber']] = false;
                        $errors += 1;
                    }
                }
            }
            
            // Place Receiving Order based on Picking Order
            $result = $conection->insert($requestGen->getPostRecievingOrderHeader($orderSyncModel->getSpecialOrderHeader($vendorCode)));
            $result ? $affected += $result : $errors += 1;
            
            $returnRecQtys = array();
            if (! $errors) {
                $requests = $requestGen->getPostRecivingOrderItems($orderSyncModel->getAllItems());
                $table_name = $requests['table'];
                foreach ($orderSyncModel->getAllItems() as $item) {
                    $result = $conection->insert($requestGen->getPostRecivingOrderItem($item));
                    if ($result) {
                        $returnRecQtys[$item['LineNumber']] = $item['OrderedQuantity'];
                        $affected += $result;
                    } else {
                        $returnRecQtys[$item['LineNumber']] = false;
                        $errors += 1;
                    }
                }
            }
        } catch (Exception $e) {
            $errors ++;
        }
        
        // Transaction control, Return true|false 
        if ($errors) {
            // Rollback??
            $conection->transactionRollback();
            return false;
        } else {
            $conection->transactionCommit();
            return true;
        }
    
    }

    public function submitRma($rma)
    {
        $errors = 0;
        $affected = 0;
        $conection = Mage::getSingleton('hpintwms/adapter');
        $requestGen = $conection->getRequestGen('rma');
        
        // Check if RMA exists
        if (count($conection->fetchAll($requestGen->getCheckRmabyId($rma->getIncrementId()))) != 0) {
            return 0;
        }
        
        /* @var $rmaPlaceModel Harapartners_HpIntWms_Model_Export_Rma  */
        $rmaPlaceModel = Mage::getModel('hpintwms/export_rma');
        $rmaPlaceModel->generateParse($rma);
        // TODO it is possible to have empty Items ??
        try {
            $conection->startTransaction();
            $result = $conection->insert($requestGen->getPostOrderHeader($rmaPlaceModel->getHeader()));
            $result ? $affected += $result : $errors += 1;
            ////$result = $conection->insert($requestGen->getPostOrderShipping($rmaPlaceModel->getShippingAddress()));
            ////$result ? $affected += $result : $errors += 1;
            ////$result = $conection->insert($requestGen->getPostOrderBilling($rmaPlaceModel->getBillingAddress()));
            ////$result ? $affected += $result : $errors += 1;
            

            if (! $errors) {
                $requests = $requestGen->getPostOrderItems($rmaPlaceModel->getAllItems());
                $table_name = $requests['table'];
                foreach ($rmaPlaceModel->getAllItems() as $item) {
                    $result = $conection->insert($requestGen->getPostOrderItem($item));
                    if ($result) {
                        $returnQtys[$item['LineNumber']] = $item['ReturnQuantity'];
                        $affected += $result;
                    } else {
                        $returnQtys[$item['LineNumber']] = false;
                        $errors += 1;
                    }
                }
            }
            
            if ($errors) {
                // Rollback??
                $conection->transactionRollback();
                return false;
            } else {
                $conection->transactionCommit();
                return $returnQtys; // If partial success is allowed change this
            }
        } catch (Exception $e) {
            $conection->transactionRollback();
            return false;
        }
    }

    public function closeRma($rma)
    {
        $errors = 0;
        $affected = 0;
        $conection = Mage::getSingleton('hpintwms/adapter');
        $requestGen = $conection->getRequestGen('rma');
        
        if (count($conection->fetchAll($requestGen->getCheckRmabyId($rma->getIncrementId()))) == 0) {
            return 0;
        }
        
    // How to indicate a cancell ??
    }

    public function createItemBySkuArray($skuArray)
    {
        $errors = 0;
        $affected = 0;
        $conection = Mage::getSingleton('hpintwms/adapter');
        $requestGen = $conection->getRequestGen('inventory');
        
        /* @var $orderSyncModel Harapartners_HpIntWms_Model_Export_Inventory  */
        $invCreateModel = Mage::getModel('hpintwms/export_inventory');
        
        foreach ($skuArray as $count => $sku) {
            $info = $invCreateModel->generateItemInfo($sku);
            try {
                $result = $conection->upsort($requestGen->getCreateItemInfo($info), array(
                    'ItemNumber'
                ));
                //$result = $conection->insert($requestGen->getCreateItemInfo($info));
            } catch (Exception $e) {
                $result = 0;
                if ($e instanceof Zend_Db_Statement_Sqlsrv_Exception) {
                    if ($e->getCode() == - 28) {
                        // DO NOTHING THIS IS VALID
                        $result = 1;
                    }
                }
            }
            
            // Result Logging
            if ($result) {
                $affected += $result;
                $this->_result[$sku] = $result;
            } else {
                $errors ++;
                $this->_result[$sku] = $result;
            }
        }
        
        // Create Msg -> Return
        $this->_resultMsg = "Inventory Create Finished. There were: Created: {$affected} Errors: {$errors}";
        return $errors;
    }

    protected function _getWMSInventory()
    {
        if (! $this->_wmsInventoryModel) {
            $this->_wmsInventoryModel = Mage::getModel('hporderworkflow/inventory');
        }
        return $this->_wmsInventoryModel;
    }
}
