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
class Harapartners_ShoemartEdi_Model_Edi_Order
{
    const FIELD_NAME_S_RECORD_ID = 'SUM';
    
    const CANCEL_DAYS_OFFSET = '+5 day';
    
    const ATTRIBUTE_COLOR = 'shoe_color_manu';
    const ATTRIBUTE_SIZE = 'shoe_size';
    const ATTRIBUTE_WIDTH = 'shoe_width';
    const ATTRIBUTE_STOCK_NUMBER = 'stock_number';
    const ATTRIBUTE_STOCK_NAME = 'stock_name';
    const ATTRIBUTE_ITEM_NUMBER = 'rpro_item_number';
    
    const SHIP_METHOD_ENUM_GROUND = 4;
    const SHIP_METHOD_ENUM_3DAY = 3;
    const SHIP_METHOD_ENUM_2DAY = 2;
    const SHIP_METHOD_ENUM_1DAY = 1;
    
    const EDI_LINE_ENDING_WEAK = "\n";
    const EDI_LINE_ENDING_STRONG = "\r\n";
    
    protected $_wmsModel = null;
    
    /**
     * VendorCode => VendorModel
     *
     * @var array
     */
    protected $_vendorStorage = array();
    
    protected $_orderModel = null;

    /**
     * Transforms Mage Order into Array (
     *     Order => OrderLvlInfo
     *     lineItems => LineItems
     * )
     *
     * @param Mage_Sales_Model_Order $order
     * @param array $qtys itemId => qty
     * @return array parsed array
     */
    public function parseOrder($order, $qtys)
    {
        $this->_orderModel = $order;
        $orderIncr = $order->getIncrementId();
        $orderDate = $order->getCreatedAtDate();
        $shippingAddress = $order->getShippingAddress();
        $billingAddress = $order->getBillingAddress();
        $allItems = $order->getAllItems();
        $shipMethodArray = explode('_', $order->getData('shipping_method'), 2); // carrier_type
        

        // Validate -> address is there
        $noAddressError = false;
        if ($billingAddress == null) {
            if ($shippingAddress != null) {
                $billingAddress = $shippingAddress;
            } else {
                $noAddressError = true;
            }
        }
        if ($shippingAddress == null) {
            if ($billingAddress != null) {
                $shippingAddress = $billingAddress;
            } else {
                $noAddressError = true;
            }
        }
        if ($noAddressError) {
            // $this->_errorString = 'Missing All Address Info';
            // Return empty frame, so rest of program can run itself out
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'cs_needed', 'Parse Failed: ' . 'Missing All Address Info');
            return array(
                'lineItems' => null , 
                'order' => null
            );
        }
        
        // TODO get all super config attributes for this??
        $lineItems = array();
        foreach ($allItems as $orderItem) {
            if (isset($qtys[$orderItem->getId()])) {
                $mageProduct = $this->_getLoadedProduct($orderItem); //$this->_getSimpleProductByOrderItem($orderItem);
                if ($mageProduct->getId() != $orderItem->getProductId()) { // This does nothign when useing simple products
                    $parentId = $orderItem->getProductId();
                } else {
                    $parentId = '';
                }
                
                $lineItems[] = array(
                    'itemId' => $orderItem->getId() , 
                    'productId' => $mageProduct->getId() , 
                    'parentProductId' => $parentId , 
                    'vendorCode' => $mageProduct->getData('vendor_code') , 
                    'upc' => $mageProduct->getData('upc') , 
                    'qty' => $qtys[$orderItem->getId()] , 
                    'cost' => $mageProduct->getData('cost') , 
                    'price' => $mageProduct->getData('price') ,  // Retail Price ??
                    'stockNumber' => $mageProduct->getData(self::ATTRIBUTE_STOCK_NUMBER) , 
                    'itemNumber' => $mageProduct->getData(self::ATTRIBUTE_ITEM_NUMBER) , 
                    'stockName' => $mageProduct->getData(self::ATTRIBUTE_STOCK_NAME) , 
                    'color' => $mageProduct->getData(self::ATTRIBUTE_COLOR) , 
                    'size' => $mageProduct->getAttributeText(self::ATTRIBUTE_SIZE) , 
                    'width' => $mageProduct->getAttributeText(self::ATTRIBUTE_WIDTH)
                );
            }
        }
        
        $parsed = array(
            'order' => array(
                'orderId' => $orderIncr , 
                'orderDate' => $orderDate , 
                'shipCarrier' => $shipMethodArray[0] , 
                'shipMethod' => $shipMethodArray[1] , 
                'shippingAddress' => $shippingAddress->getData() , 
                'billingAddress' => $billingAddress->getData()
            ) , 
            'lineItems' => $lineItems
        );
        return $parsed;
    }

    /**
     * Splits the Parsed Array by Vendor Info, Method
     *
     * @param array $parsedArray
     * @return array Type => parsedData
     */
    public function splitByType($parsedArray)
    {
        foreach ($parsedArray['lineItems'] as $lineItem) {
            switch ($this->_getDropshipType($lineItem)) {
                case 'edi':
                    $splitArray['edi']['lineItems'][] = $lineItem;
                    break;
                case 'email':
                    $splitArray['email']['lineItems'][] = $lineItem;
                    break;
                case 'fax':
                    $splitArray['fax']['lineItems'][] = $lineItem;
                    break;
                default:
                    $splitArray['other']['lineItems'][] = $lineItem;
            }
        }
        
        $splitArray['edi']['order'] = $parsedArray['order'];
        $splitArray['email']['order'] = $parsedArray['order'];
        $splitArray['fax']['order'] = $parsedArray['order'];
        $splitArray['other']['order'] = $parsedArray['order'];
        return $splitArray;
    }

    public function renderOrderByType($dividedArray)
    {
        $renderedArray = array();
        if (isset($dividedArray['edi']['lineItems']) && count($dividedArray['edi']['lineItems']) != 0) {
            $renderedArray['edi'] = $this->renderOrderInto($dividedArray['edi'], 'edi');
        }
        if (isset($dividedArray['email']['lineItems']) && count($dividedArray['email']['lineItems']) != 0) {
            $renderedArray['email'] = $this->renderOrderInto($dividedArray['email'], 'email');
        }
        if (isset($dividedArray['fax']['lineItems']) && count($dividedArray['fax']['lineItems']) != 0) {
            $renderedArray['fax'] = $this->renderOrderInto($dividedArray['fax'], 'fax');
        }
        if (isset($dividedArray['other']['lineItems']) && count($dividedArray['other']['lineItems']) != 0) {
            $renderedArray['other'] = $this->_renderOrderOther($dividedArray['other']);
        }
        
        return $renderedArray;
    }

    /**
     * Factory for Createing the specific types of EDI documents.
     *
     * @param array $parsedOrderArray
     * @param string $dropshipType A valid type (edi, fax, email, other)
     * @return mixed return value of the inner function
     */
    public function renderOrderInto($parsedOrderArray, $dropshipType)
    {
        $function = '_renderOrderInto' . $dropshipType;
        return $this->$function($parsedOrderArray);
    }

    /**
     * Creates array of EDI document Strings
     *
     * @param array $parsedOrderArray
     * @return array vendorCode_OrderIncrementId => string
     */
    protected function _renderOrderIntoEdi($parsedOrderArray)
    {
        $dividedByVendor = array();
        foreach ($parsedOrderArray['lineItems'] as $lineItem) {
            $dividedByVendor[$lineItem['vendorCode']][] = $lineItem;
        }
        
        foreach ($dividedByVendor as $vendorCode => $lineItems) {
            $this->_sendLeadTimeEmail($vendorCode, 's.hoffman@harapartners.com', 'Steven Hoffman', $lineItems);
            
            list ($parsedOrderArray['order']['shipCarrier'], $parsedOrderArray['order']['shipMethod']) = $this->_getVendorShippingArray($vendorCode, array(
                $parsedOrderArray['order']['shipCarrier'] , 
                $parsedOrderArray['order']['shipMethod']
            ));
            $headerArray = array();
            $lineItemsArray = array();
            $summaryArray = array();
            $isSpecialOrder = $this->_isSpecialOrder($vendorCode, $parsedOrderArray['order']);
            
            // Do header Array
            $headerArray = Mage::getModel('shoemartedi/edi_edi_header')->createHeader($parsedOrderArray['order'], $vendorCode, $isSpecialOrder);
            if ($isSpecialOrder) {
                $specialOrderResult = $this->_getWMSModel()->placeSpecialRecievingOrder($this->_orderModel, $lineItems);
            }
            
            /* @var $lineItemModel Harapartners_ShoemartEdi_Model_Edi_Edi_Lineitem */
            $lineItemModel = Mage::getModel('shoemartedi/edi_edi_lineitem');
            foreach ($lineItems as $lineItemInfo) {
                $lineItemsArray[] = $lineItemModel->createLineItem($lineItemInfo);
            }
            
            // === Summery Row === //
            $summaryArray[] = self::FIELD_NAME_S_RECORD_ID;
            $summaryArray[] = count($lineItems);
            $summaryArray[] = $lineItemModel->getTotalItemCount();
            
            // === CREATE output String === //
            $outputString = implode('|', $headerArray) . self::EDI_LINE_ENDING_WEAK;
            foreach ($lineItemsArray as $lineItem) {
                $outputString .= implode('|', $lineItem) . self::EDI_LINE_ENDING_WEAK;
            }
            $outputString .= implode('|', $summaryArray) . self::EDI_LINE_ENDING_STRONG;
            $outputStrings[$vendorCode . '_' . $parsedOrderArray['order']['orderId']] = $outputString;
        }
        return $outputStrings;
    }

    /**
     * Creates array of FAX document Strings
     * @todo Use PDF template or such?
     *
     * @param array $parsedOrderArray
     * @return array $vendorCode . '_' . $parsedOrderArray['order']['orderId'] => String
     */
    protected function _renderOrderIntoFax($parsedOrderArray)
    {
        // $faxModel =  Mage::getModel('shoemartedi/edi_fax_full')->createAll($parsedOrderArray);
        $date = date('F d, Y');
        $order = $parsedOrderArray['order'];
        $orderId = $order['orderId'];
        $shippingAddress = $order['shippingAddress'];
        $splitShippingStreet = explode("\n", $shippingAddress['street']);
        $shipCarrier = $order['shipCarrier'];
        $shipMethod = $order['shipMethod'];
        
        foreach ($this->_divideByVendor($parsedOrderArray) as $vendorCode => $lineItems) {
            list ($shipCarrier, $shipMethod) = $this->_getVendorShippingArray($vendorCode, array(
                $shipCarrier , 
                $shipMethod
            ));
            
            $output = '';
            $output .= 'THESHOEMART' . PHP_EOL;
            $output .= 'SINCE 1956 rt'; // TODO add in symbol
            $output .= PHP_EOL;
            $output .= '---------------------------------------------------------------------------' . PHP_EOL;
            $output .= PHP_EOL;
            $output .= 'Date:            ' . $date . PHP_EOL;
            $output .= 'Purchase Order:  ' . $vendorCode . $order['orderId'] . PHP_EOL;
            $output .= 'Account #:       ' . $this->_getVendorAccountNumber($vendorCode) . PHP_EOL;
            $output .= PHP_EOL;
            $output .= PHP_EOL;
            $output .= PHP_EOL;
            $output .= 'Ship To:' . PHP_EOL;
            $output .= PHP_EOL;
            $output .= $shippingAddress['firstname'] . ' ' . $shippingAddress['lastname'] . PHP_EOL;
            $output .= empty($shippingAddress['company']) ? '' : $shippingAddress['company'] . PHP_EOL;
            $output .= $splitShippingStreet[0] . PHP_EOL;
            $output .= ! empty($splitShippingStreet[1]) ? $splitShippingStreet[1] . PHP_EOL : '';
            $output .= $shippingAddress['city'] . ', ' . $shippingAddress['region'] . ' ' . $shippingAddress['postcode'] . PHP_EOL;
            $output .= PHP_EOL;
            $output .= 'Method : ' . $shipCarrier . ' ' . $shipMethod . PHP_EOL;
            $output .= PHP_EOL;
            $output .= PHP_EOL;
            $output .= 'Please Drop Ship the following items:' . PHP_EOL;
            $output .= PHP_EOL;
            $output .= str_pad('SKU', 25) . str_pad('Desc.', 30) . str_pad('Qty. ', 10) . 'Size' . PHP_EOL;
            $output .= '---------------------------------------------------------------------------' . PHP_EOL;
            foreach ($parsedOrderArray['lineItems'] as $lineItem) {
                $output .= str_pad($lineItem['stockNumber'], 25) . str_pad($lineItem['stockName'], 30) . str_pad($lineItem['qty'], 10) . $lineItem['size'] . ' ' . $lineItem['width'] . PHP_EOL;
            }
            $output .= PHP_EOL;
            $output .= PHP_EOL;
            $output .= PHP_EOL;
            $output .= 'Thank You,' . PHP_EOL;
            $output .= 'Shoe Mart Customer Service' . PHP_EOL;
            $output .= PHP_EOL;
            $output .= '950 Bridgeport Avenue.' . PHP_EOL;
            $output .= 'Milford, CT 06460' . PHP_EOL;
            $output .= 'P: 900.850.7463' . PHP_EOL;
            $output .= 'F: 203.816.6654' . PHP_EOL;
            $outputs[$vendorCode . '_' . $parsedOrderArray['order']['orderId']] = $output;
        }
        return $outputs;
    }

    /**
     * Call Email, They are the same atm
     *
     * @param array $parsedOrderArray
     * @return array $vendorCode . '_' . $parsedOrderArray['order']['orderId'] => String
     */
    protected function _renderOrderIntoEmail($parsedOrderArray)
    {
        return $this->_renderOrderIntoFax($parsedOrderArray);
    }

    /**
     * Splits line items by Vendor
     *
     * @param array $parsedOrderArray
     * @return array vendor_code => lineItems
     */
    protected function _divideByVendor($parsedOrderArray)
    {
        foreach ($parsedOrderArray['lineItems'] as $lineItem) {
            $dividedByVendor[$lineItem['vendorCode']][] = $lineItem;
        }
        
        return $dividedByVendor;
    }

    /**
     * Get Vendor Account number
     *
     * @param string $vendorCode 
     * @return string
     */
    protected function _getVendorAccountNumber($vendorCode)
    {
        $vendorModel = $this->_getVendorModel($vendorCode);
        return strtolower($vendorModel->getData('account_number'));
    }

    /**
     * Get Vendor DropShip Type
     *
     * @param array $lineItemInfo
     * @return string type 
     */
    protected function _getDropshipType($lineItemInfo)
    {
        $vendorCode = $lineItemInfo['vendorCode'];
        $vendorModel = $this->_getVendorModel($vendorCode);
        return strtolower($vendorModel->getData('dropship_method'));
    }

    /**
     * Gets Loaded Vendor Model by Code
     * Impliments Cacheing
     *
     * @param string $vendorCode
     * @return Harapartners_Vendoroptions_Model_Vendoroptions_Configure
     */
    protected function _getVendorModel($vendorCode)
    {
        if (! isset($this->_vendorStorage[$vendorCode])) {
            $this->_vendorStorage[$vendorCode] = Mage::getModel('vendoroptions/vendoroptions_configure')->load($vendorCode, 'code');
        }
        
        return $this->_vendorStorage[$vendorCode];
    }

    /**
     * Map Shipping code to Vendor Shipping code
     *
     * @param string $vendorCode
     * @param array $shippingArray (carrier, method)
     * @return array (carrier, method)
     */
    protected function _getVendorShippingArray($vendorCode, $shippingArray)
    {
        $shippingMap = array();
        list ($shippingCarrier, $shippingMethod) = $shippingArray;
        $shipMethodEnum = $this->_getMethodClass($shippingCarrier, $shippingMethod);
        $vendorModel = $this->_getVendorModel($vendorCode);
        
        // Note: UPS is not a valid type. Noted: 7/3
        $shippingCarriers = array(
            'fedex' => 'fedex_codes' , 
            'usps' => 'usps_codes' , 
            'ups' => 'ups_codes'
        );
        
        // Bring inputted method to the front
        if (isset($shippingCarriers[$shippingCarrier])) {
            $selectedMethod = $shippingCarriers[$shippingCarrier];
            unset($shippingCarriers[$shippingCarrier]);
            $shippingCarriers = array(
                $shippingCarrier => $selectedMethod
            ) + $shippingCarriers;
        }
        
        // Find the first Matching Vendor Shipping Method
        foreach ($shippingCarriers as $shippingCarrierName => $shippingCodesField) {
            $shippingCode = $vendorModel->getData($shippingCodesField);
            if (! empty($shippingCode)) {
                $shippingMap = explode('|', $shippingCode);
                $shippingCarrier = strtoupper($shippingCarrierName);
                break;
            }
        }
        
        $header = array(
            self::SHIP_METHOD_ENUM_GROUND , 
            self::SHIP_METHOD_ENUM_3DAY , 
            self::SHIP_METHOD_ENUM_2DAY , 
            self::SHIP_METHOD_ENUM_1DAY
        );
        
        $shippingMap = array_combine($header, $shippingMap);
        return array(
            $shippingCarrier , 
            $shippingMap[$shipMethodEnum]
        );
    }

    /**
     * Maps Current Carrier + Method to a Service Level
     *
     * @param string $shippingCarrier
     * @param string $shippingMethod
     * @return int const enum
     */
    protected function _getMethodClass($shippingCarrier, $shippingMethod)
    {
        $lookupArray = array(
            'fedex' => array(
                'FEDEX_2_DAY' => self::SHIP_METHOD_ENUM_2DAY , 
                'FEDEX_2_DAY_AM' => self::SHIP_METHOD_ENUM_2DAY , 
                'FEDEX_EXPRESS_SAVER' => self::SHIP_METHOD_ENUM_3DAY , 
                'FEDEX_GROUND' => self::SHIP_METHOD_ENUM_GROUND , 
                'FIRST_OVERNIGHT' => self::SHIP_METHOD_ENUM_1DAY , 
                'GROUND_HOME_DELIVERY' => self::SHIP_METHOD_ENUM_GROUND , 
                'INTERNATIONAL_ECONOMY' => self::SHIP_METHOD_ENUM_GROUND ,  // This is bec Int Orders will become special Orders
                'PRIORITY_OVERNIGHT' => self::SHIP_METHOD_ENUM_1DAY , 
                'STANDARD_OVERNIGHT' => self::SHIP_METHOD_ENUM_1DAY
            ) , 
            'usps' => array(
                'Priority Mail 3-Day' => self::SHIP_METHOD_ENUM_3DAY , 
                'Priority Mail Military' => self::SHIP_METHOD_ENUM_3DAY , 
                'Priority Mail International' => self::SHIP_METHOD_ENUM_GROUND
            )
        );
        
        if (isset($lookupArray[$shippingCarrier][$shippingMethod])) {
            return $lookupArray[$shippingCarrier][$shippingMethod];
        } else {
            return 0;
        }
    }

    /**
     * Check if Special Order
     * Non-Us/US Mil/US Territories/PO w/ Non-USPS Vendor
     *
     * @param string $vendorCode
     * @param array $orderInfo
     * @return bool 
     */
    protected function _isSpecialOrder($vendorCode, $orderInfo)
    {
        /* @var $serviceLocationHelper Harapartners_Service_Helper_Locations */
        $serviceLocationHelper = Mage::helper('service/locations');
        $orderAddressShippingArray = $orderInfo['shippingAddress'];
        
        if (! $serviceLocationHelper->isUSA($orderAddressShippingArray)) {
            return true;
        } elseif ($serviceLocationHelper->isUsMilitary($orderAddressShippingArray)) {
            return true;
        } elseif ($serviceLocationHelper->isUsTerritories($orderAddressShippingArray)) {
            return true;
        } elseif ($serviceLocationHelper->isPoBox($orderAddressShippingArray) && ! $this->_isVendorShipByUsps($vendorCode)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Uses Vendor Model to check if Vendor Ships by USPS
     *
     * @param string $vendorCode
     * @return bool|string
     */
    protected function _isVendorShipByUsps($vendorCode)
    {
        return $this->_getVendorModel($vendorCode)->getData('is_usps');
    }

    /**
     * Get WMS model
     *
     * @return Harapartners_HpIntWms_Model_Export
     */
    protected function _getWMSModel()
    {
        if (! isset($this->_wmsModel)) {
            $this->_wmsModel = Mage::getModel('hpintwms/export');
        }
        
        return $this->_wmsModel;
    }

    /**
     * DOES Not WORK
     * Get the Product Model for the product by OrderItem
     * If Simple product, same as basic getProduct()
     *
     * @param Mage_Sales_Model_Order_Item $orderItem
     * @return Mage_Catalog_Model_Product
     */
    protected function _getSimpleProductByOrderItem(Mage_Sales_Model_Order_Item $orderItem)
    {
        $parentId = $orderItem->getParentItemId();
        if (empty($parentId)) {
            $product = $orderItem->getProduct();
        } else {
            $product = $orderItem->getParentItem()->getProduct();
        }
        
        return $product;
    }

    protected function _getLoadedProduct(Mage_Sales_Model_Order_Item $orderItem)
    {
        $product = $orderItem->getProduct();
        if (! ($product->getData(self::ATTRIBUTE_COLOR) && $product->getData(self::ATTRIBUTE_STOCK_NAME))) {
            $product = $product->load($product->getId());
            // $orderItem->setProduct($product); Don't Modify Unless needed.
        }
        
        return $product;
    }

    /**
     * This sends an email to the Customer with lead time information
     * Checks if enabled first
     *
     * @param string $vendorCode
     * @param array $lineItems
     */
    protected function _sendLeadTimeEmail($vendorCode, $customerEmail, $customerName, $lineItems)
    {
        if (! Mage::getStoreConfigFlag('customer/vendor_lead_time_email/use_vendor_lead_time_email')) {
            return;
        }
        
        $templateConfigPath = 'customer/vendor_lead_time_email/vendor_lead_time_email';
        $to = $customerEmail;
        $name = $customerName;
        
        $vendor = $this->_getVendorModel($vendorCode);
        $vendorLeadtime = $vendor->getData('dropship_lead_time_info');
        $vendorName = $vendor->getData('name');
        
        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);
        
        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        
        $template = Mage::getStoreConfig($templateConfigPath, Mage::app()->getStore()->getId());
        $mailTemplate->setDesignConfig(array(
            'area' => 'frontend' , 
            'store' => Mage::app()->getStore()->getId()
        ))->sendTransactional($template, Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_IDENTITY, Mage::app()->getStore()->getId()), $to, $name, array(
            'vendor_name' => $vendorName , 
            'vendor_leadtime' => $vendorLeadtime , 
            'customer_name' => $name , 
            'line_items' => $lineItems
        ));
        
        $translate->setTranslateInline(true);
        return $this;
    }
}
