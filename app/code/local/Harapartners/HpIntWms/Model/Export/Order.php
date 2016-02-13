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
class Harapartners_HpIntWms_Model_Export_Order
{
    protected $_header = array();
    protected $_items = array();
    protected $_shippingAddress = array();
    protected $_billingAddress = array();
    protected $_errorString = '';
    
    protected $_orderBillingAddr;
    protected $_orderShippingAddr;

    public function generateParse(Mage_Sales_Model_Order $order, $qtys)
    {
        $noAddressError = false;
        $this->_orderBrillingAddr = $order->getBillingAddress();
        $this->_orderShippingAddr = $order->getShippingAddress();
        if ($this->_orderBillingAddr == null) {
            if ($this->_orderShippingAddr != null) {
                $this->_orderBillingAddr = $this->_orderShippingAddr;
            } else {
                $noAddressError = true;
            }
        }
        if ($this->_orderShippingAddr == null) {
            if ($this->_orderBillingAddr != null) {
                $this->_orderShippingAddr = $this->_orderBillingAddr;
            } else {
                $noAddressError = true;
            }
        }
        if ($noAddressError) {
            $this->_errorString = 'Missing All Address Info';
            return false;
        }
        
        $shippingMethodArray = Mage::helper('hpintwms')->convertShippingMethod($order->getData('shipping_method'), $this->_orderShippingAddr);
        
        // == Parse For Header
        $this->_header['Site'] = '{Default}';
        $this->_header['OrderNumber'] = $order->getIncrementId();
        $this->_header['OrderDate'] = $order->getCreatedAt(); // Does not Exist
        $this->_header['DueDate'] = $order->getCreatedAt();
        // $this->_header['PickDate'] = 'Not For Us';
        $this->_header['CustomerBillTo'] = $this->_orderBillingAddr->getId();
        $this->_header['CustomerShipTo'] = $this->_orderShippingAddr->getId();
        $this->_header['Carrier'] = $shippingMethodArray['carrier'];
        $this->_header['Method'] = $shippingMethodArray['method'];
        
        // == Parse For Line Items
        $isQtyUsed = (is_array($qtys) && ! empty($qtys));
        foreach ($order->getAllItems() as $item) {
            $qty = isset($qtys[$item->getId()]) ? $qtys[$item->getId()] : 0;
            if ($qty > 0 && $isQtyUsed) { // TODO better check is needed ?? Prob not.
                $outItem['Site'] = '{Default}';
                $outItem['OrderNumber'] = $order->getIncrementId();
                $outItem['LineNumber'] = $item->getParentItemId() ? $item->getParentItemId() : $item->getId(); // Send the parent lineID
                $outItem['ItemNumber'] = $item->getProductId(); // TODO check this ( This uses the simple or configurable depending on which the item is)
                $outItem['UnitOfMeasure'] = $item->getisQtyDecimal() ? 'MEASURE' : 'each';
                // $outItem['Pallet'] = 'Not For Us';
                // $outItem['SerialNumber'] = 'Not For Us';
                // $outItem['Lot'] = 'Not For Us';
                // $outItem['ExpirationDate'] = 'Not For Us';
                $outItem['OrderedQuantity'] = $qty;
                // $outItem['OrderedQuantity'] = 'Not For Us';
                // $outItem['ActualQuantity'] = 'Not For Us';
                // $outItem['ActualDate'] = 'Not For Us';
                

                $this->_items[] = $outItem;
            }
        }
        
        // == Parse Shipping
        $shippingAddrArray = $this->_orderShippingAddr->getStreet();
        $this->_shippingAddress['CustomerBillTo'] = $this->_orderBillingAddr->getId();
        $this->_shippingAddress['CustomerShipTo'] = $this->_orderShippingAddr->getId();
        $this->_shippingAddress['CustomerName'] = $this->_orderShippingAddr->getFirstName() . ' ' . $this->_orderShippingAddr->getLastName();
        $this->_shippingAddress['CompanyName'] = $this->_orderShippingAddr->getCompany();
        $this->_shippingAddress['Address1'] = $shippingAddrArray[0];
        $this->_shippingAddress['Address2'] = isset($shippingAddrArray[1]) ? $shippingAddrArray[1] : null;
        $this->_shippingAddress['City'] = $this->_orderShippingAddr->getCity();
        $this->_shippingAddress['State'] = $this->_orderShippingAddr->getRegion();
        $this->_shippingAddress['ZipCode'] = $this->_orderShippingAddr->getPostcode();
        
        // == Parse Billing
        $billingAddrArray = $this->_orderBillingAddr->getStreet();
        $this->_billingAddress['CustomerBillTo'] = $this->_orderBillingAddr->getId();
        $this->_billingAddress['CompanyName'] = $this->_orderBillingAddr->getCompany();
        $this->_billingAddress['CustomerName'] = $this->_orderBillingAddr->getFirstName() . ' ' . $this->_orderBillingAddr->getLastName();
        $this->_billingAddress['Address1'] = $shippingAddrArray[0];
        $this->_billingAddress['Address2'] = isset($billingAddrArray[1]) ? $billingAddrArray[1] : null;
        $this->_billingAddress['City'] = $this->_orderBillingAddr->getCity();
        $this->_billingAddress['State'] = $this->_orderBillingAddr->getRegion();
        $this->_billingAddress['ZipCode'] = $this->_orderBillingAddr->getPostcode();
        
        return true;
    }

    /**
     * @return array
     */
    public function getBillingAddress()
    {
        return $this->_billingAddress;
    }

    /**
     * @return array
     */
    public function getHeader()
    {
        return $this->_header;
    }

    /**
     * @return array
     */
    public function getAllItems()
    {
        return $this->_items;
    }

    /**
     * @return array
     */
    public function getShippingAddress()
    {
        return $this->_shippingAddress;
    }

    /**
     * @return string
     */
    public function getErrorString()
    {
        return $this->_errorString;
    }

    /**
     * This returns the special order header
     *
     * @param string $vendorCode
     * @return array
     */
    public function getSpecialOrderHeader($vendorCode)
    {
        $header = $this->_header;
        $recieveingHeader = array();
        $recieveingHeader['Site'] = $header['Site'];
        $recieveingHeader['OrderNumber'] = $header['OrderNumber'];
        $recieveingHeader['OrderDate'] = $header['OrderDate'];
        $recieveingHeader['DueDate'] = $header['DueDate'];
        $recieveingHeader['VendorId'] = $vendorCode;
        return $recieveingHeader;
    }

}
