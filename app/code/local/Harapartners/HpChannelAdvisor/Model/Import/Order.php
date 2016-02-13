<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 * @package     Harapartners_HpChannelAdvisor_Model_Import
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/HpChannelAdvisor/Model/Import/Order.php
/**
 * Order Import
 * 
 *
 */
class Harapartners_HpChannelAdvisor_Model_Import_Order extends Mage_Core_Model_Abstract
{
    /**
     * High => 50
     * Complete => 20
     *
     */
    const NUMBER_OF_ORDERS_PER_CALL = 50;
    const DETAIL_LEVEL = 'High';
    const RESULT_STATUS_Failure = 'Failure';
    
    protected $_foo = array();

    /**
     * Main Entry point to get -> Process all CA orders
     *
     */
    public function processAllOrdersFromCa()
    {
        /* @var $zendService OrderService */
        $zendService = Mage::helper('hpchanneladvisor')->getZendWrapper(new OrderService(), null);
        
        $golRequest = new GetOrderList();
        $golRequest->accountID = Mage::getStoreConfig('hpchanneladvisor/core_config/account_id');
        $orderCriteria = $this->_getOrderCriteria();
        
        $pageNumber = 0;
        $isMoreOrders = true;
        while ($isMoreOrders) {
            $pageNumber ++;
            $this->_setPageNumber($orderCriteria, $pageNumber);
            $golRequest->orderCriteria = $orderCriteria;
            
            try {
                $orderResponce = $zendService->GetOrderList($golRequest);
            } catch (SoapFault $sf) {
                return;
            }
            
            // Check Orders
            if ($orderResponce->GetOrderListResult == null || $orderResponce->GetOrderListResult->ResultData == null) {
                // no orders
                break;
            } else {
                $isMoreOrders = count($orderResponce->GetOrderListResult->ResultData) == self::NUMBER_OF_ORDERS_PER_CALL;
            }
            
            if ($orderResponce->GetOrderListResult->Message == self::RESULT_STATUS_Failure) {
                break;
            }
            
            // Make an array if it not
            if (! is_array($orderResponce->GetOrderListResult->ResultData->OrderResponseItem)) {
                $orderResponce->GetOrderListResult->ResultData->OrderResponseItem = array(
                    $orderResponce->GetOrderListResult->ResultData->OrderResponseItem
                );
            }
            
            // CreateOrder -> log failure
            foreach ($orderResponce->GetOrderListResult->ResultData->OrderResponseItem as $resultObject) {
                /* @var $resultObject OrderResponseDetailComplete */
                $parsed = $this->_getOrderRender()->renderOrderResponseDetailHigh($resultObject);
                $result = $this->_registerOrder($parsed);
                if (! $result['result']) {
                    Mage::log("OrderSync ERROR: OrderID: {$parsed['order']['extraInfo']['OrderID']} Reason: {$result['reason']}", null, 'CA_log.log', true);
                }
                
                // For Mark as exported -> it wount take the fails (cept for exists)
                $results[$resultObject->ClientOrderIdentifier . '_' . $resultObject->OrderID] = $result;
            }
            
            // Mark as Exported
            $postBackResult = $this->markAsExported($zendService, $results);
        }
        
        // log fails if result from CA
        if ($postBackResult) {
            // Make array if not array
            if (! is_array($postBackResult->SetExportStatusResponse->ResultData)) {
                $postBackResult->SetExportStatusResponse->ResultData = array(
                    $postBackResult->SetExportStatusResponse->ResultData
                );
            }
            
            // log Sync back failures
            foreach ($postBackResult->SetExportStatusResponse->ResultData as $pbResult) {
                /* @var $pbResult SetExportStatusResponse */
                if (! $pbResult->Success) {
                    Mage::log("OrderSyncBack FAIL: ClientOrderIdentifier: {$pbResult->ClientOrderIdentifier} OrderID:{$pbResult->OrderID}", null, 'CA_log.log', true);
                }
            }
        }
        
        return $results;
    }

    /**
     * Mark an array of Ids not to be exported again
     *
     * @param array $cID_oID
     * @return SetExportStatusResponse|null
     */
    public function markAsExported($zendService, array $results)
    {
        $setOrderExportStatus = new SetOrdersExportStatus();
        $setOrderExportStatus->accountID = Mage::getStoreConfig('hpchanneladvisor/core_config/account_id');
        $setOrderExportStatus->markAsExported = true;
        foreach ($results as $cID_oID => $resultArray) {
            list ($clientOrderId, $orderId) = explode('_', $cID_oID);
            $result = $resultArray['result'];
            $reason = $resultArray['reason'];
            
            if ($result || $reason == 'Order Exists') {
                $ids[] = $orderId;
            }
        }
        
        if (! empty($ids)) {
            $setOrderExportStatus->orderIDList = $ids;
            return $zendService->SetOrdersExportStatus($setOrderExportStatus);
        } else {
            return null;
        }
    }

    /**
     * Gets the OrderListRequest Base
     * 
     * @return OrderCriteria
     */
    protected function _getOrderCriteria()
    {
        $orderCriteria = new OrderCriteria();
        $orderCriteria->StatusUpdateFilterBeginTimeGMT = $this->_timeToWsdlDateTime(time() - 2592000); // 2592000 is 30 days/secs  
        $orderCriteria->StatusUpdateFilterEndTimeGMT = $this->_timeToWsdlDateTime(time());
        $orderCriteria->PaymentStatusFilter = 'Cleared';
        $orderCriteria->ShippingStatusFilter = 'Unshipped';
        $orderCriteria->ExportState = 'NotExported';
        $orderCriteria->DetailLevel = self::DETAIL_LEVEL;
        
        $orderCriteria->PageSize = self::NUMBER_OF_ORDERS_PER_CALL;
        
        return $orderCriteria;
    }

    /**
     * Set the Page Number for the next request
     *
     * @param OrderCriteria $orderCriteria
     * @param int $pageNumber
     */
    protected function _setPageNumber(OrderCriteria &$orderCriteria, $pageNumber)
    {
        $orderCriteria->PageNumberFilter = $pageNumber;
    }

    /**
     * Convert Unix Int time to WSDL timestamp
     *
     * @param int $unixTime
     * @return string DateString in 'c' format
     */
    protected function _timeToWsdlDateTime($unixTime = null)
    {
        if ($unixTime === null) {
            $unixTime = time();
        }
        return gmdate('Y-m-d\TH:i:s', $unixTime);
    }

    /**
     * Gets customer by Email address
     * OR
     * Creates a new one if not found
     *
     * @param array $parsedInfo
     * @return Mage_Customer_Model_Customer
     */
    protected function _getOrCreateCustomer($parsedInfo)
    {
        $customer = $this->_getCustomerByEmail($parsedInfo['order']['emailAddress']);
        if ($customer->getId()) {
            return $customer;
        } else {
            $this->_createDummyCustomer($customer, $parsedInfo);
        }
    }

    /**
     * Wrapped (Set Website Code)
     * Load customer by email
     *
     * @param string $emailAddress
     * @return Mage_Customer_Model_Customer
     */
    protected function _getCustomerByEmail($emailAddress)
    {
        $websiteId = Mage::getModel("core/website")->load(Mage::getStoreConfig('hpchanneladvisor/configs/website_code'))->getId();
        return Mage::getModel('customer/customer')->setWebsiteId($websiteId)->loadByEmail($emailAddress);
    }

    /**
     * Creates a new customer based on Info
     *
     * @param Mage_Customer_Model_Customer $customer Empty Customer that failed load
     * @param array $parsed Parsed Order Info
     * @return Mage_Customer_Model_Customer|null null if fail save
     */
    protected function _createDummyCustomer($customer, $parsed)
    {
        $shipping = $parsed['shipping']['contact'];
        $billing = $parsed['billing']['contact'];
        $firstName = isset($shipping['firstName']) ? $shipping['firstName'] : $billing['firstName'];
        $lastName = isset($shipping['lastName']) ? $shipping['lastName'] : $billing['lastName'];
        $websiteId = Mage::getModel("core/website")->load(Mage::getStoreConfig('hpchanneladvisor/configs/website_code'))->getId();
        
        $customer->setEmail($parsed['order']['emailAddress']);
        $customer->setFirstname($firstName);
        $customer->setLastname($lastName);
        $customer->setPassword('');
        $customer->setWebsiteId($websiteId);
        try {
            $customer->save();
            $customer->setConfirmation(null);
            $customer->save();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
        
        //Build billing and shipping address
        $transactionSave = Mage::getModel('core/resource_transaction');
        if (! empty($shipping['firstName']) || ! empty($shipping['LastName'])) {
            $shippingAddress = $this->_getAddressDataArray($shipping);
            $customAddress = Mage::getModel('customer/address')->setData($shippingAddress)->setCustomerId($customer->getId());
            $customAddress->setIsDefaultShipping('1')->setSaveInAddressBook(1);
            $transactionSave->addObject($customAddress);
        }
        
        if (! empty($billing['addressLine1'])) {
            $billingAddress = $this->_getAddressDataArray($billing);
            $customAddress = Mage::getModel('customer/address')->setData($shippingAddress)->setCustomerId($customer->getId());
            $customAddress->setIsDefaultBilling('1')->setSaveInAddressBook(1);
            $transactionSave->addObject($customAddress);
        }
        
        try {
            $transactionSave->save();
        } catch (Exception $e) {
            Mage::logException($e);
            return null;
        }
        return $customer;
    }

    /**
     * Creates and saves Order and Order info
     *
     * @param array $parsedInfo Order parsed info
     * @return array 'result' => true|false , 'reason' => string
     */
    protected function _registerOrder($parsedInfo)
    {
        // Get check the Order exists
        $order = $this->_getOrderByServiceId($parsedInfo['order']['serviceId']);
        if ($order && $order->getId()) {
            return array(
                'result' => false , 
                'reason' => 'Order Exists'
            );
        }
        
        // Init Config & Converters
        $storeCode = Mage::getStoreConfig('hpchanneladvisor/configs/store_code');
        $storeId = Mage::getModel("core/store")->load($storeCode)->getId();
        $customerGroupCode = Mage::getStoreConfig('hpchanneladvisor/configs/customer_group_code');
        $customerGroupId = Mage::getModel('customer/group')->load($customerGroupCode, 'customer_group_code')->getId();
        $quoteConverter = Mage::getModel('sales/convert_order');
        $orderConverter = Mage::getModel('sales/convert_order');
        
        // Get -> Create Customer 
        $customer = $this->_getOrCreateCustomer($parsedInfo);
        if (! $customer || ! $customer->getId()) {
            return array(
                'result' => false , 
                'reason' => 'Can Not Create Customer'
            );
        }
        
        // Start the Order
        /* @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->setStoreId($storeId)->setCustomerId($customer->getId())->setCustomerEmail($customer->getEmail())->setData('customer_group_id', $customerGroupId);
        $order->setData('customer_lastname', $customer->getLastname())->setData('customer_middlename', $customer->getMiddlename())->setData('customer_firstname', $customer->getFirstname());
        $order->setData('service_type', Harapartners_HpChannelAdvisor_Helper_Data::CHANNELADVISOR_IMPORTED)->setData('service_transactionid', $parsedInfo['order']['serviceId']);
        $order->setData('service_data', Mage::helper('hpchanneladvisor')->addToJson($order->getData('service_data'), $parsedInfo['order']['extraInfo']));
        
        // TODO Merge this
        // Try to get Billing & Shipping
        $orderItemShipping = Mage::getModel('sales/order_address')->setData($this->_getAddressDataArray($parsedInfo['shipping']['contact']));
        $orderItemBilling = Mage::getModel('sales/order_address')->setData($this->_getAddressDataArray($parsedInfo['billing']['contact']));
        if ($orderItemShipping->getData('firstname')) {
            $order->setShippingAddress($orderItemShipping);
            $isShippingAddress = true;
        } else {
            $isShippingAddress = false;
        }
        
        if ($orderItemBilling->getData('firstname')) {
            $order->setBillingAddress($orderItemBilling);
            $isBillingAddress = true;
        } else {
            $isBillingAddress = false;
        }
        
        // Validate
        if (! ($isBillingAddress || $isShippingAddress)) {
            // Error state
            return array(
                'result' => false , 
                'reason' => 'MISSING All Address Info'
            );
        } elseif (! $isBillingAddress && $isShippingAddress) {
            // Use shipping for billing
            $orderItemBilling = Mage::getModel('sales/order_address')->setData($this->_getAddressDataArray($parsedInfo['shipping']['contact']));
            $order->setBillingAddress($orderItemBilling);
        } elseif ($isBillingAddress && ! $isShippingAddress) {
            // Use Billing for shipping
            $orderItemShipping = Mage::getModel('sales/order_address')->setData($this->_getAddressDataArray($parsedInfo['billing']['contact']));
            $order->setShippingAddress($orderItemShipping);
        }
        
        // Add Items
        $subTotal = $this->_addItemsToOrder($parsedInfo['cart']['lineItems'], $order);
        
        // Set Order costs
        $this->_addOrderPriceInfo($parsedInfo['cart']['costs'], $parsedInfo['order']['orderDate'], 'shoemart', $order, $subTotal);
        
        // Set Shipping Method
        $methodTrack = $parsedInfo['shipping']['methodTracking'];
        if (! empty($methodTrack)) {
            $shippingMethod = $this->_getMageShippingMethod($orderItemShipping, $methodTrack['carrier'], $methodTrack['class']);
            $order->setData('shipping_method', $shippingMethod);
            $order->setData('shipping_description', 'Description: ' . $methodTrack['carrier'] . '_' . $methodTrack['class']); // TODO something specific ??
        }
        
        // Set State & Staus
        $order->setState('processing');
        $order->setStatus(Harapartners_HpChannelAdvisor_Helper_Data::CHANNELADVISOR_IMPORTED);
        
        // Create Payment
        $payment = $this->_getPaymentMethodByOrder($parsedInfo['payment']);
        $payment->setOrder($order);
        $order->addPayment($payment);
        
        // Create non-Order saved data
        // Create Invoice (If needed)
        $transactionSave = Mage::getModel('core/resource_transaction');
        $isSendCSREmail = false;
        if (strpos($shippingMethod, 'CRS_') == 0) {
            $order->setState('processing', 'cs_needed', 'CA Order: Shipping Method Needs Review');
            $isSendCSREmail = true;
        } else {
            // Create Invoice
            $invoice = $order->prepareInvoice();
            $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
            $invoice->register();
            $transactionSave->addObject($invoice);
        }
        
        // Save & return
        $result = false;
        try {
            $transactionSave->addObject($order);
            $transactionSave->save();
            $result = array(
                'result' => true , 
                'reason' => ''
            );
            if ($isSendCSREmail) {
                $this->_sendCSREmail($order); // Does nothing yet
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $result = array(
                'result' => false , 
                'reason' => 'Order Save Error'
            );
        }
        
        return $result;
    }

    /**
     * Adds Items to order from Order parsed Info -> Item Level
     *
     * @param array $itemLvl itemLvl from Order Parsed Info
     * @param order
     * @return double subTotal for the order's items
     */
    protected function _addItemsToOrder($itemLvl, &$order)
    {
        $subTotal = 0;
        foreach ($itemLvl as $itemInfo) {
            $qty = $itemInfo['qty'];
            $listingId = $itemInfo['listingId'];
            $sku = $itemInfo['sku'];
            $title = $itemInfo['title'];
            $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $sku);
            if (! $product || ! $product->getId()) {
                // Todo make this a comment??
                echo '<br> listingId ' . $listingId . ' imported with warning: Invalid product SKU "' . $sku . '"' . ' may does not exist anymore';
            }
            
            $productId = empty($product) ? null : $product->getId();
            $salePrice = $itemInfo['price'];
            $taxAmount = $itemInfo['tax'];
            $rowSubTotal = $salePrice * $qty;
            $rowSubTotalAfterTax = $rowSubTotal + $taxAmount;
            
            $Item = Mage::getModel('sales/order_item');
            $Item->setData('price', $salePrice);
            $Item->setData('base_price', $salePrice);
            $Item->setData('original_price', $salePrice);
            $Item->setData('original_base_price', $salePrice);
            $Item->setData('tax_amount', $taxAmount);
            $Item->setData('base_tax_amount', $taxAmount);
            $Item->setData('price_incl_tax', $taxAmount);
            $Item->setData('base_price_incl_tax', $taxAmount);
            $Item->setData('row_total', $rowSubTotal);
            $Item->setData('base_row_total', $rowSubTotal);
            $Item->setData('row_total_incl_tax', $rowSubTotalAfterTax);
            $Item->setData('base_row_total_incl_tax', $rowSubTotalAfterTax);
            
            $Item->setData('qty_ordered', $qty);
            $Item->setData('product_id', $productId);
            $Item->setData('sku', $sku);
            $Item->setData('name', $title);
            
            $order->addItem($Item);
            // Buffer the Item Info
            // $this->_subTotal += $rowSubTotal;
            // $this->_subTotalTax += $taxAmount;
            // $this->_itemBuffer[] = $Item;
            $subTotal += $rowSubTotal;
            
        // $this->_saleIDBuffer[] = $itemInfo['serviceId'];
        }
        
        return $subTotal;
    }

    /**
     * Adds Order Price totals and subtotals
     *
     * @param array $cartCosts cartCosts from cartLvl of orderParsed Info
     * @param string $date Date string
     * @param string $store Store Name
     */
    protected function _addOrderPriceInfo($cartCosts, $date, $store, &$order, $subTotal)
    {
        $shippingAmount = isset($cartCosts['ShippingInsurance']) ? $cartCosts['Shipping'] + $cartCosts['ShippingInsurance'] : $cartCosts['Shipping'];
        $orderSubTotal = $subTotal;
        $orderSubTotalTax = $cartCosts['SalesTax'];
        $orderTotalCost = $orderSubTotal + $orderSubTotalTax + $shippingAmount;
        $order->setData('subtotal', $orderSubTotal);
        $order->setData('base_subtotal', $orderSubTotal);
        //// $order->setData ( 'discount_amount', '0' ); // TODO dont think this applies -> Check this stuff
        //// $order->setData ( 'base_discount_amount', '0' );
        $order->setData('tax_amount', $orderSubTotalTax);
        $order->setData('base_tax_amount', $orderSubTotalTax);
        $order->setData('shipping_amount', $shippingAmount);
        $order->setData('base_shipping_amount', $shippingAmount);
        $order->setData('grand_total', $orderTotalCost);
        $order->setData('base_grand_total', $orderTotalCost);
        $createTime = date('Y-m-d H:i:s', strtotime($date));
        $order->setData('created_at', $createTime);
        $order->setData('store_name', $store);
        $order->setData('currency_id', 'USD'); // TODO unhardcode
    }

    /**
     * Converts CA payment methods to Magento Methods
     * Defualts to Check/MoneyOrder (check_mo)
     *
     * @param array $paymentLvl paymentLvl From OrderParsedInfo
     * @return Mage_Sales_Model_Order_Payment Loaded Order Payment
     */
    protected function _getPaymentMethodByOrder($paymentLvl)
    {
        $paymentMethod = '';
        $transactionId = '';
        $preparedMessage = '';
        switch ($paymentLvl['type']) {
            case 'PP':
                $paymentMethod = 'paypal_standard';
                $transactionId = $paymentLvl['transactionId'];
                $preparedMessage = 'CA Order Paid through PayPay';
                break;
            case 'GG':
                $paymentMethod = 'google_checkout';
                $transactionId = $paymentLvl['referenceNumber'];
                $preparedMessage = 'CA Order Paid through GoogleCheckout';
                break;
            default:
                $paymentMethod = 'checkmo';
                $transactionId = $paymentLvl['referenceNumber'];
                $preparedMessage = "CA Order Paid through Unknown. ({$paymentLvl['type']})";
        }
        $payment = Mage::getModel('sales/order_payment')->setMethod($paymentMethod);
        ////$payment->setOrder($order);
        $payment->setTransactionId($transactionId);
        $payment->setLastTransId($transactionId);
        $payment->setPreparedMessage($preparedMessage);
        
        return $payment;
    }

    /**
     * Gets an Order by CA service ID
     *
     * @param string $serviceId
     * @return Mage_Sales_Model_Order
     */
    protected function _getOrderByServiceId($serviceId)
    {
        $order = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('service_type', array(
            'like' => 'channeladvisor%'
        ))->addFilter('service_transactionid', $serviceId)->setPageSize(1)->getFirstItem();
        return $order;
    }

    protected function _getAddressDataArray($shipping)
    {
        if (empty($shipping['firstName']) || empty($shipping['lastName'])) {
            return array();
        }
        
        return array(
            'company' => $shipping['company'] , 
            'prefix' => $shipping['title'] , 
            'firstname' => $shipping['firstName'] , 
            'lastname' => $shipping['lastName'] , 
            'suffix' => $shipping['suffix'] , 
            'street' => implode("\r\n", array(
                '0' => $shipping['addressLine1'] , 
                '1' => $shipping['addressLine2']
            )) , 
            'city' => $shipping['city'] , 
            'region_id' => '' ,  //$shipping[''] , 
            'region' => $shipping['regionCode'] , 
            'postcode' => $shipping['postalCode'] , 
            'country_id' => $shipping['countryCode'] , 
            'telephone' => $shipping['phoneDay']
        );
    }

    protected function _getOrderRender()
    {
        return Mage::getModel('hpchanneladvisor/import_render_order');
    }

    protected function _getMageShippingMethod($shippingAddress, $carrier, $class)
    {
        return Mage::helper('hpchanneladvisor/carrier')->getMageShippingMethod($shippingAddress, $carrier, $class);
    }

    protected function _sendCSREmail($order)
    {
        return;
        
        // Template Code
        $mail = Mage::getModel('core/email');
        $mail->setToName('Your Name');
        $mail->setToEmail('Youe Email');
        $mail->setBody('Mail Text / Mail Content');
        $mail->setSubject('Mail Subject');
        $mail->setFromEmail('Sender Mail Id');
        $mail->setFromName("Msg to Show on Subject");
        $mail->setType('html'); // YOu can use Html or text as Mail format
        

        try {
            $mail->send();
        } catch (Exception $e) {// Is there anything we can do // TODO
}
    }

}
