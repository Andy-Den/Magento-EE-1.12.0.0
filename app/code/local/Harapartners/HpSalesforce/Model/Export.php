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
class Harapartners_HpSalesforce_Model_Export
{
    protected $_result = array();
    protected $_resultMsg = '';
    
    protected $_order = null;
    protected $_shipment = null;
    
    protected $_nextSoObject = null;
    protected $_nextCallType = '';
    protected $_nextCallExternalFieldName = '';
    
    protected $_lastSoObject = null;
    protected $_lastResponceObject = null;

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

    public function orderMain($order)
    {
        try {
            if (! $this->_getConection()) {
                return false;
            }
            
            $isFailure = false;
            $this->_order = $order;
            
            // Save Customer
            $this->_createSoCustomer();
            $isFailure += ! $this->_callNext();
            $sfCustomerId = $this->_getLastResponceFirstId();
            
            // Save Address
            $this->_createSoBillingAddress($sfCustomerId);
            $isFailure += ! $this->_callNext();
            $sfBillingAddrId = $this->_getLastResponceFirstId();
            
            // Save Order
            $this->_createSoOrder($sfCustomerId, $sfBillingAddrId);
            $isFailure += ! $this->_callNext();
            $sfOrderId = $this->_getLastResponceFirstId();
            
            // Save Payment detail??
            // Not yet
            

            // Save Order Items
            // // Save Products
            $this->_createSoProducts();
            $isFailure += ! $this->_callNext();
            
            // // Save Order Items
            $sProducts = $this->_lastSoObject;
            $resultProducts = $this->_lastResponceObject;
            $this->_createSoLineItems($sProducts, $resultProducts, $sfOrderId, $sfCustomerId);
            $isFailure += ! $this->_callNext();
            
            // $isFailure can be postive value
            return $isFailure ? false : true;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

    public function shippingUpdate($shipment)
    {
        if (! $this->_getConection()) {
            return false;
        }
        
        if (is_array($shipment)) {
            $this->_order = $shipment[0]->getOrder();
        } elseif ($shipment instanceof Varien_Db_Collection) {
            $this->_order = $shipment->getFirstItem()->getOrder();
        } else {
            $this->_order = $shipment->getOrder();
            $shipment = array(
                $shipment
            );
        }
        
        $this->_shipment = $shipment;
        $isFailure = false;
        
        try {
            // Get Order Line Info
            $response = $this->_getCreateSfOrderLineInfoByMageOrderId();
            
            // Process Results => Ready data
            foreach ($response->records as $record) {
                $sLineItem = new SObject($record);
                $sfLineItemInfo[$sLineItem->fields->Name] = array(
                    'id' => $sLineItem->Id , 
                    'productId' => $sLineItem->fields->ECS__Product__c , 
                    'sku' => $sLineItem->fields->ECS__SKU__c
                );
            }
            
            // Only need to get once
            $sfCustomerId = $sLineItem->fields->ECS__Customer__c;
            $sfOrderId = $sLineItem->fields->ECS__Order__c;
            
            // Create Shipping Detail
            // // Create Shipping Address
            $this->_createSoShippingAddress($sfCustomerId);
            $isFailure += ! $this->_callNext();
            $sfShippingAddressId = $this->_getLastResponceFirstId();
            
            // // Create Shipping Detail
            $this->_createSoShippingDetail($sfOrderId, $sfCustomerId, $sfShippingAddressId);
            $isFailure += ! $this->_callNext();
            $sfShippingDetailIds = $this->_getLastResponceAllIds('ECS__Shipping_Detail_External_Id__c');
            
            // Create Shipping Lines
            $this->_createSoShippingLines($sfLineItemInfo, $sfShippingDetailIds);
            $isFailure += ! $this->_callNext(); // 'ECS__Order_Line__c', // Note there is no ExternalId or PK to use
            

            // Update Order Line => Set the shipping detail
            $sShippingLines = $this->_lastSoObject;
            $this->_createSoLineUpdateShipping($sShippingLines);
            $isFailure += ! $this->_callNext();
            
            // $isFailure can be postive value
            return $isFailure ? false : true;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

    public function statusHistoryForOrder(Mage_Sales_Model_Order_Status_History $statusHistory)
    {
        if (! $this->_getConection()) {
            return false;
        }
        
        $orderId = $statusHistory->getOrder()->getId();
        try {
            // Get sfOrderId
            if (! ($sfOrderId = $this->_getsfOrderIdbyMageOrderId($orderId))) {
                return false;
            }
            
            $this->_createSoOrderNote($statusHistory, $sfOrderId);
            $isSuccess = $this->_callNext();
            return $isSuccess;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

    public function orderCancel($order)
    {
        if (! $this->_getConection()) {
            return false;
        }
        
        $this->_order = $order;
        $orderId = $order->getId();
        
        if (! ($sfOrderId = $this->_getsfOrderIdbyMageOrderId($orderId))) {
            return false;
        }
        
        try {
            $this->_createSoOrderCancelUpdate($sfOrderId);
            $isSuccess = $this->_callNext();
            return $isSuccess;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

    public function rma($rma)
    {
        if (! $this->_getConection()) {
            return false;
        }
        
        $this->_order = $rma->getOrder();
        $rmaLines = $rma->getItemsCollection();
        if (! ($rmaLines && $rmaLines->count())) {
            $rmaLines = $this->_getRmaItemCollection($rma);
        }
        
        if (! ($rmaLines && $rmaLines->count())) {
            return false;
        }
        
        // Get Order Line Info
        $response = $this->_getCreateSfOrderLineInfoByMageOrderId();
        
        // Process Results => Ready data
        $sfLineItemInfoByLineItemId = array();
        foreach ($response->records as $record) {
            $sLineItem = new SObject($record);
            $sfLineItemInfoByLineItemId[$sLineItem->fields->Name] = array(
                'id' => $sLineItem->Id , 
                'productId' => $sLineItem->fields->ECS__Product__c , 
                'sku' => $sLineItem->fields->ECS__SKU__c , 
                'customerId' => $sLineItem->fields->ECS__Customer__c , 
                'orderId' => $sLineItem->fields->ECS__Order__c
            );
        }
        
        try {
            $this->_createSoRmaLines($rmaLines, $sfLineItemInfoByLineItemId);
            $isSuccess = $this->_callNext();
            return $isSuccess;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }

    protected function _callNext()
    {
        if (! $this->_getConection()) {
            return false;
        }
        
        $isValidObject = false;
        $isValidMethod = false;
        $isFailure = true;
        $responce = null;
        
        $object = $this->_nextSoObject;
        $method = $this->_nextCallType;
        $externalFieldName = $this->_nextCallExternalFieldName;
        
        if (! is_array($object)) {
            $isValidObject = false;
        } elseif (! $object[0] instanceof SObject) {
            $isValidObject = false;
        } else {
            $isValidObject = true;
        }
        
        try {
            if ($isValidObject) {
                switch ($method) {
                    case 'upsert':
                        $isValidMethod = true;
                        $responce = $this->_getConection()->upsert($externalFieldName, $object);
                        break;
                    case 'update':
                        $isValidMethod = true;
                        $responce = $this->_getConection()->update($object);
                        break;
                    case 'create':
                        $isValidMethod = true;
                        $responce = $this->_getConection()->create($object);
                        break;
                    default:
                        $isValidMethod = false;
                        $responce = null;
                        break;
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $responce = null;
            $isFailure = true;
        }
        
        if ($isValidMethod) {
            $isFailure = $this->_isFailureInResponce($responce);
        }
        
        $this->_lastResponceObject = $responce;
        $this->_lastSoObject = $object;
        
        $this->_nextSoObject = null;
        $this->_nextCallExternalFieldName = '';
        $this->_nextCallType = '';
        return $isFailure ? false : true;
    }

    protected function _setNextSoObject(SObject $object)
    {
        $this->_nextSoObject = $object;
        return $this;
    }

    protected function _createSoCustomer()
    {
        $address = $this->_order->getBillingAddress();
        
        /* @var $model Harapartners_HpSalesforce_Model_Export_Customer */
        $model = Mage::getModel('hpsalesforce/export_customer');
        $sObject = array(
            $model->createSfCustomerObject($address)
        );
        
        $this->_setNextCall($sObject, 'upsert', 'Email');
        return $this;
    }

    protected function _createSoAddress($sfCustomerId)
    {
        $billingAddress = $this->_order->getBillingAddress();
        $shippingAddress = $this->_order->getShippingAddress();
        
        /* @var $model Harapartners_HpSalesforce_Model_Export_Address */
        $model = Mage::getModel('hpsalesforce/export_address');
        return $model->createSfAddressesObjects($billingAddress, $shippingAddress, $sfCustomerId);
    }

    protected function _createSoBillingAddress($sfCustomerId)
    {
        $addresses = $this->_createSoAddress($sfCustomerId);
        $sObject = array(
            $addresses[0]
        );
        
        $this->_setNextCall($sObject, 'upsert', 'ECS__External_Address_ID__c');
        return $this;
    }

    protected function _createSoShippingAddress($sfCustomerId)
    {
        $addresses = $this->_createSoAddress($sfCustomerId);
        $sObject = array(
            $addresses[1]
        );
        
        $this->_setNextCall($sObject, 'upsert', 'ECS__External_Address_ID__c');
        return $this;
    }

    protected function _createSoOrder($sfCustomerId, $sfBillingAddrId)
    {
        $order = $this->_order;
        
        /* @var $model Harapartners_HpSalesforce_Model_Export_Order */
        $model = Mage::getModel('hpsalesforce/export_order');
        $sObject = array(
            $model->createSfOrderObject($order, $sfCustomerId, $sfBillingAddrId)
        );
        
        $this->_setNextCall($sObject, 'upsert', 'ECS__External_Order_ID__c');
        return $this;
    }

    protected function _createSoProducts()
    {
        $orderItems = $this->_order->getAllItems();
        
        /* @var $model Harapartners_HpSalesforce_Model_Export_Product */
        $model = Mage::getModel('hpsalesforce/export_product');
        $sObject = $model->createSfProductObjects($orderItems);
        
        $this->_setNextCall($sObject, 'upsert', 'ECS__Product_Id__c');
        return $this;
    }

    protected function _createSoLineItems($sProducts, $resultProducts, $sfOrderId, $sfCustomerId)
    {
        $orderItems = $this->_order->getAllItems();
        $productMap = array();
        foreach ($resultProducts as $count => $result) {
            $sku = $sProducts[$count]->fields['name'];
            $productSkuToSfIdMap[$sku] = $result->id;
        }
        
        $orderItemsBySku = array();
        foreach ($orderItems as $orderItem) {
            $orderItemsBySku[$orderItem->getSku()] = $orderItem;
        }
        
        /* @var $model Harapartners_HpSalesforce_Model_Export_Lineitem */
        $model = Mage::getModel('hpsalesforce/export_lineitem');
        $sObject = $model->createSfLineItemObjects($sProducts, $productSkuToSfIdMap, $orderItemsBySku, $sfOrderId, $sfCustomerId);
        
        $this->_setNextCall($sObject, 'upsert', 'ECS__External_Order_Line_ID__c');
        return $this;
    }

    protected function _createSoShippingDetail($sfOrderId, $sfCustomerId, $sfShippingAddressId)
    {
        $shipment = $this->_shipment;
        
        /* @var $model Harapartners_HpSalesforce_Model_Export_Shippingdetail */
        $model = Mage::getModel('hpsalesforce/export_shippingdetail');
        $sObject = $model->createSfShippingDetail($shipment, $sfOrderId, $sfCustomerId, $sfShippingAddressId);
        
        $this->_setNextCall($sObject, 'upsert', 'ECS__Shipping_Detail_External_Id__c');
        return $this;
    }

    protected function _createSoShippingLines($sfLineItemInfo, $sfShippingDetailIds)
    {
        $shipment = $this->_shipment;
        
        /* @var $model Harapartners_HpSalesforce_Model_Export_Shippinglines */
        $model = Mage::getModel('hpsalesforce/export_shippinglines');
        $sObject = $model->createSfShippingLines($shipment, $sfLineItemInfo, $sfShippingDetailIds);
        
        $this->_setNextCall($sObject, 'create');
        return $this;
    }

    protected function _createSoLineUpdateShipping($sShippingLines)
    {
        $shippingLinesArray = array();
        foreach ($sShippingLines as $count => $sShippingLineInfo) {
            $lineId = $sShippingLineInfo->fields['ECS__Order_Line__c'];
            $shippingDetailId = $sShippingLineInfo->fields['ECS__Shipping_Detail__c'];
            $shippingLinesArray[$lineId] = $shippingDetailId;
        }
        
        /* @var $model Harapartners_HpSalesforce_Model_Export_Lineitem */
        $model = Mage::getModel('hpsalesforce/export_lineitem');
        $sObject = $model->createSfLineItemUpdateShippingObjects($shippingLinesArray);
        
        $this->_setNextCall($sObject, 'update');
        return $this;
    }

    protected function _createSoOrderNote($statusHistory, $sfOrderId)
    {
        $status = str_pad(substr($statusHistory->getStatus(), 0, 9), 9, ' ');
        $notified = $statusHistory->getIsCustomerNotified() ? 'custNotifed   ' : 'custNotNotifed';
        $frontend = $statusHistory->getIsVisibleOnFront() ? 'Visible' : 'NotVisb';
        $createdAt = $statusHistory->getCreatedAt();
        $commentChunk = substr($statusHistory->getComment(), 0, 40);
        
        $title = "{$status} : {$notified}  : {$frontend} : {$commentChunk}";
        $comment = "{$status} : {$notified}  : {$frontend} : {$createdAt}\r\n";
        $comment .= $statusHistory->getComment();
        
        /* @var $model Harapartners_HpSalesforce_Model_Export_Note */
        $model = Mage::getModel('hpsalesforce/export_note');
        $sObject = array(
            $model->createSfNoteObject($sfOrderId, $title, $comment)
        );
        
        $this->_setNextCall($sObject, 'create');
        return $this;
    }

    protected function _createSoOrderCancelUpdate($sfOrderId)
    {
        $order = $this->_order;
        
        /* @var $model Harapartners_HpSalesforce_Model_Export_Order */
        $model = Mage::getModel('hpsalesforce/export_order');
        $sObject = array(
            $model->createSoOrderCancelUpdateObject($sfOrderId, time())
        );
        
        $this->_setNextCall($sObject, 'update');
        return $this;
    }

    protected function _createSoRmaLines($rmaLines, $sfLineItemInfoByLineItemId)
    {
        /* @var $model Harapartners_HpSalesforce_Model_Export_Rma */
        $model = Mage::getModel('hpsalesforce/export_rma');
        $sObject = $model->createSfRmaLines($rmaLines, $sfLineItemInfoByLineItemId);
        
        $this->_setNextCall($sObject, 'upsert', 'CloudConversion__External_RMA_Number__c');
        return $this;
    }

    protected function _setNextCall($sObject, $type, $ExternalFieldName = '')
    {
        $this->_nextSoObject = $sObject;
        $this->_nextCallType = $type;
        $this->_nextCallExternalFieldName = $ExternalFieldName;
        
        return $this;
    }

    protected function _getsfOrderIdbyMageOrderId($orderId)
    {
        $registryKey = 'harapartners_salesforce_last_order_id';
        if (! ($sfOrderId = Mage::registry($registryKey))) {
            try {
                $query = "SELECT Name, id FROM ECS__eCommSource_Order__c WHERE ECS__External_Order_ID__c = '{$orderId}'";
                $response = $this->_getConection()->query($query);
                if (count($response->records)) {
                    // Process Results => there should only be one
                    foreach ($response->records as $record) {
                        $sOrder = new SObject($record);
                        $sfOrderId = $sOrder->Id;
                        Mage::register($registryKey, $sfOrderId);
                    }
                }
            } catch (Exception $e) {}
        }
        return empty($sfOrderId) ? false : $sfOrderId;
    }

    protected function _getCreateSfOrderLineInfoByMageOrderId($id = null)
    {
        $id = $id ? $id : $this->_order->getId();
        
        $query = "SELECT Name, id, ECS__SKU__c, ECS__Product__c, ECS__Customer__c, ECS__Order__c FROM ECS__eCommSource_Order_Line__c WHERE ECS__Order__r.name = '{$id}'";
        $response = $this->_getConection()->query($query);
        if (count($response->records)) {
            // Do Nothing, This is correct
        } else {
            // Order Does not Exist or missing line Items
            $firstResponce = $response;
            $this->mainOrder($this->_order);
            $response = $this->_getConection()->query($query);
        }
        
        return $response;
    }

    protected function _isFailureInResponce($responce)
    {
        if (! $responce) {
            return true;
        }
        
        /*
    	 * created => bool
    	 * id => string
    	 * success => true
    	 */
        foreach ($responce as $count => $result) {
            if (! $result->success) {
                return true;
            }
        }
        
        return false;
    }

    protected function _getLastResponceFirstId()
    {
        return $this->_lastResponceObject[0]->id;
    }

    protected function _getLastResponceAllIds($indexField = '')
    {
        $ids = array();
        if (empty($indexField)) {
            foreach ($this->_lastResponceObject as $result) {
                $ids[] = $result->Id;
            }
        } else {
            $lastObject = $this->_lastSoObject;
            foreach ($this->_lastResponceObject as $count => $result) {
                $indexData = $lastObject[$count]->fields[$indexField];
                $ids[$indexData] = $result->id;
            }
        }
        
        return $ids;
    }

    protected function _getRmaItemCollection($rma)
    {
        $rmaId = $rma->getId();
        $collection = Mage::getModel('enterprise_rma/item')->getCollection();
        $collection->addFieldToFilter('rma_entity_id', array(
            'eq' => $rmaId
        ));
        
        return $collection;
    }

    protected function _getConection()
    {
        return Mage::getSingleton('hpsalesforce/adapter')->getConnection();
    }
}
