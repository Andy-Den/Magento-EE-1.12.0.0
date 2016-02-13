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
 * @package     Harapartners_HpChannelAdvisor_Model_Export_Tracking
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/HpChannelAdvisor/Model/Export/Tracking.php
/**
 * Tracking Export
 * 
 *
 */
class Harapartners_HpChannelAdvisor_Model_Export_Tracking extends Mage_Core_Model_Abstract
{
    const CA_PAGE_SIZE = 50;
    
    const SYNC_CODE_RDY = 0;
    const SYNC_CODE_SYNCED = 1;
    const SYNC_CODE_FAILED = 2;
    const SYNC_CODE_SOAPFAIL = 3;
    
    /**
     * shipmentId => shipment Model
     *
     * @var array of shipments
     */
    protected $_shipmentModels = array();
    
    /**
     * array(
     *   service_transactionid => shipment_id
     * )
     */
    protected $_lastShipmentIds = array();

    /**
     * Push all trackings to CA
     * Check Shippment service_flag = 0
     *
     * @todo $this->_getLastShipmentIds()
     */
    public function pushAllTrackings()
    {
        $success = false;
        $errorSku = array();
        
        /* @var $zendService ShippingService */
        $zendService = Mage::helper('hpchanneladvisor')->getZendWrapper(new ShippingService(), null);
        $submitOrderShipmentList = new SubmitOrderShipmentList();
        $submitOrderShipmentList->accountID = Mage::getStoreConfig('hpchanneladvisor/core_config/account_id');
        
        $isMoreItems = true;
        while ($isMoreItems) {
            $submitOrderShipmentList->shipmentList = $this->_getOrderShipmentArray();
            // TODO extra check on next logic statement ???
            if (count($submitOrderShipmentList->shipmentList)) {
                try {
                    $result = $zendService->SubmitOrderShipmentList($submitOrderShipmentList);
                    $this->_updateSyncCode($result->SubmitOrderShipmentListResult->ResultData->ShipmentResponse);
                } catch (SoapFault $sf) {
                    Mage::log("CA Error in Trackings:{$this->_getLastShipmentIds()}", null, 'CA_Tracking_error.log');
                    $setShippmentSyncCode = self::SYNC_CODE_SOAPFAIL;
                }
            } else {
                $isMoreItems = false;
            }
        }
    }

    protected function _getOrderShipmentArray()
    {
        $shipmentIds = array();
        $orderShipments = array();
        
        foreach ($this->_getPageSizeRdyShippmentCollection() as $shipment) {
            $serviceTId = $shipment->getOrder()->getData('service_transactionid');
            $carreirClass = implode('_', array(
                $shipment->getData('carrier_code') , 
                $shipment->getData('carrier_title')
            ));
            
            $this->_shipmentModels[$shipment->getID()] = $shipment;
            $shipmentIds[] = array(
                'service_transactionid' => $serviceTId , 
                'shipment_id' => $shipment->getID()
            );
            
            $orderShipment = new OrderShipment();
            $orderShipment->ClientOrderIdentifier = $serviceTId;
            $orderShipment->ShipmentType = 'Partial';
            $orderShipment->PartialShipment = new PartialShipmentContents();
            
            $lineItems = array();
            foreach ($shipment->getAllItems() as $shipmentItem) {
                $lineItem = new LineItem();
                $lineItem->SKU = $shipmentItem->getData('sku');
                $lineItem->Quantity = $shipmentItem->getData('qty');
                $lineItems[] = $lineItem;
            }
            $orderShipment->PartialShipment->LineItemList = $lineItems;
            
            // TODO this is wrong => need to put a map in place...
            list ($orderShipment->PartialShipment->CarrierCode, $orderShipment->PartialShipment->ClassCode) = explode('_', $carreirClass);
            $orderShipment->PartialShipment->TrackingNumber = $shipment->getData('track_number');
            $orderShipments[] = $orderShipment;
        }
        
        $this->_lastShipmentIds = $shipmentIds;
        return $orderShipments;
    }

    protected function _getCarrier($creditMemo)
    {
        $refundItems = array();
        foreach ($creditMemo->getAllItems() as $creditMemoItem) {
            $refundItem = new RefundItem();
            $refundItem->SKU = $creditMemoItem->getSku();
            $refundItem->Amount = $creditMemoItem->getRowTotal(); // TODO Include Tax or not??
            $refundItem->Quantity = $creditMemoItem->getQty();
            $refundItems = $refundItem;
            // $refundItem->ShippingAmount = 'Not Useing this Right Now';
        // $refundItem->ShippingTaxAmount = 'Not Useing this Right Now';
        // $refundItem->TaxAmount = 'Not Useing this Right Now';
        // $refundItem->RecyclingFee = 'Not Useing this Right Now';
        // $refundItem->GiftWrapAmount = 'Not Useing this Right Now';
        // $refundItem->GiftWrapTaxAmount = 'Not Useing this Right Now';
        // $refundItem->RestockQuantity = 1; Not going to use this
        }
        
        return $refundItems;
    }

    protected function _getPageSizeRdyShippmentCollection()
    {
        $shipmentCollection = Mage::getModel('sales/order_shipment')->getCollection();
        $shipmentCollection->setPageSize(self::CA_PAGE_SIZE);
        $shipmentCollection->join(array(
            'salesorder' => 'sales/order'
        ), 'main_table.order_id = salesorder.entity_id', array(
            'shipping_method' , 
            'service_transactionid'
        ));
        $shipmentCollection->join(array(
            'track' => 'sales/shipment_track'
        ), 'main_table.entity_id = track.parent_id', array(
            'track_number' , 
            'carrier_code' , 
            'carrier_title' => 'title'
        ));
        //        $shipmentCollection->addFieldToFilter('service_type', array(
        //            'eq' => Harapartners_HpChannelAdvisor_Helper_Data::CHANNELADVISOR_SHIPMENT_EXPORT_RDY
        //        ));
        $shipmentCollection->addFieldToFilter('service_flag', array(
            'eq' => 0
        ));
        
        return $shipmentCollection;
    }

    protected function _updateSyncCode($shipmentResponces)
    {
        if (! is_array($shipmentResponces)) {
            $shipmentResponces = array(
                $shipmentResponces
            );
        }
        
        $successShipmentIds = array();
        $failShipmentIds = array();
        for ($i = 0; $i < count($shipmentResponces); $i ++) {
            /* @var $shipmentResponce ShipmentResponse */
            $shipmentResponce = $shipmentResponces[$i];
            if ($shipmentResponce->ShipmentResponse->Success) {
                $successShipmentIds[] = $this->_lastShipmentIds[$i];
            } else {
                $failShipmentIds[] = $this->_lastShipmentIds[$i];
                Mage::log("CA Error in Trackings:{$this->_lastShipmentIds[$i]} Message:{$shipmentResponce->Message}", null, 'CA_Tracking_error.log');
            }
        }
        
        $this->_setSyncCodes($successShipmentIds, $failShipmentIds);
    }

    protected function _setSyncCodes($successShipmentIds, $failShipmentIds)
    {
        foreach ($successShipmentIds as $shipmentInfo) {
            $shipmentModel = $this->_shipmentModels[$shipmentInfo['shipment_id']];
            $shipmentModel->setData('service_flag', 1);
            $shipmentModel->getOrder()->setData('service_type', Harapartners_HpChannelAdvisor_Helper_Data::CHANNELADVISOR_COMPLETE);
            Mage::getModel('core/resource_transaction')->addObject($shipmentModel)->addObject($shipmentModel->getOrder())->save();
        }
        
        foreach ($failShipmentIds as $shipmentInfo) {
            $shipmentModel = $this->_shipmentModels[$shipmentInfo['shipment_id']];
            $shipmentModel->setData('service_flag', 1);
            $shipmentModel->getOrder()->setData('service_type', Harapartners_HpChannelAdvisor_Helper_Data::CHANNELADVISOR_SHIP_ERROR);
            Mage::getModel('core/resource_transaction')->addObject($shipmentModel)->addObject($shipmentModel->getOrder())->save();
        }
    }
}
