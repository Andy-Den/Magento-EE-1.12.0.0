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
class Harapartners_HpIntWms_Model_Export_Rma
{
    protected $_header = array();
    protected $_items = array();
    protected $_shippingAddress = array();
    protected $_billingAddress = array();
    
    protected $_orderBillingAddr;
    protected $_orderShippingAddr;

    public function generateParse($rma)
    {
        $order = $rma->getOrder();
        ///$this->_orderBillingAddr = $order->getBillingAddress();
        ///$this->_orderShippingAddr = $order->getShippingAddress();
        foreach ($order->getAllItems() as $items) {
            if ($items->getProductType() == 'simple') {
            	// We want the simple (actual) products ID
                $itemSkuSimpleProductIdMap[$items->getSku()] = $items->getProductId();
            }
        }
        
        // == Parse For Header
        $this->_header['Site'] = '{Default}';
        $this->_header['OrderNumber'] = $rma->getIncrementId();
        $this->_header['PickingOrderNumber'] = $order->getIncrementId();
        $this->_header['OrderDate'] = $rma->getCreatedAt();
        $this->_header['DueDate'] = $rma->getCreatedAt();
        // $this->_header['PickDate'] = 'Not For Us';
        ////$this->_header['CustomerBillTo'] = $this->_orderBillingAddr->getId();
        ////$this->_header['CustomerShipTo'] = $this->_orderShippingAddr->getId();
        // $this->_header['ReceiveDate'] = 'Not For US';
        

        // == Parse For Line Items
        foreach ($rma->getItemsCollection() as $item) {
            $qty = $item->getQtyAuthorized();
            if ($qty > 0) {
                $outItem['Site'] = '{Default}';
                $outItem['OrderNumber'] = $rma->getIncrementId();
                $outItem['LineNumber'] = $item->getOrderItemId();
                $outItem['ItemNumber'] = $itemSkuSimpleProductIdMap[$item->getProductSku()];
                $outItem['UnitOfMeasure'] = $item->getisQtyDecimal() ? 'MEASURE' : 'each';
                // $outItem['Pallet'] = 'Not For Us';
                // $outItem['SerialNumber'] = 'Not For Us';
                // $outItem['Lot'] = 'Not For Us';
                // $outItem['ExpirationDate'] = 'Not For Us';
                $outItem['ReturnQuantity'] = $qty;
                // $outItem['OrderedQuantity'] = 'Not For Us';
                // $outItem['ActualQuantity'] = 'Not For Us';
                // $outItem['ActualDate'] = 'Not For Us';
                

                $this->_items[] = $outItem;
            }
        }
        
    /*
        // == Parse Billing
        $shippingAddrArray = $this->_orderShippingAddr->getStreet();
        $this->_shippingAddress['CustomerBillTo'] = $this->_orderBillingAddr->getId();
        $this->_shippingAddress['CustomerShipTo'] = $this->_orderShippingAddr->getId();
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
        $this->_billingAddress['Address1'] = $shippingAddrArray[0];
        $this->_billingAddress['Address2'] = isset($billingAddrArray[1]) ? $billingAddrArray[1] : null;
        $this->_billingAddress['City'] = $this->_orderBillingAddr->getCity();
        $this->_billingAddress['State'] = $this->_orderBillingAddr->getRegion();
        $this->_billingAddress['ZipCode'] = $this->_orderBillingAddr->getPostcode();
		*/
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
 *
    public function getShippingAddress()
    {
        return $this->_shippingAddress;
    }
    
    
 **
 * @return array
 *
    public function getBillingAddress()
    {
        return $this->_billingAddress;
    }
 */
}
