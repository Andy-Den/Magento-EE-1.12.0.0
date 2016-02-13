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
class Harapartners_HpSalesforce_Model_Export_Shippingdetail
{

    public function createSfShippingDetail($shipments, $sfOrderId, $sfCustomerId, $sfShippingAddressId)
    {
        $recordShipments = array();
        foreach ($shipments as $index => $shipment) {
            $recordShipment = new SObject();
            $recordShipment->fields = $this->_mapIntoFields($shipment, $sfOrderId, $sfCustomerId, $sfShippingAddressId);
            $recordShipment->type = 'ECS__Shipping_Detail__c';
            $recordShipments[] = $recordShipment;
        }
        
        return $recordShipments;
    }

    protected function _mapIntoFields($shipment, $sfOrderId, $sfCustomerId, $sfShippingAddressId)
    {
        $tracking = $this->_getFirstTrackingByShipment($shipment);
        
        $soFields = array();
        $varienMapper = new Varien_Object_Mapper();
        $soFields = $varienMapper->accumulateByMap($shipment->getData(), $soFields, $this->_getShipmentMap());
        $soFields = $varienMapper->accumulateByMap($tracking->getData(), $soFields, $this->_getTrackingMap());
        
        // Extra Stuff
        $soFields['ECS__Shipped_Time__c'] = date('c', strtotime($soFields['ECS__Shipped_Time__c']));
        $soFields['ECS__Customer__c'] = $sfCustomerId;
        $soFields['ECS__Order__c'] = $sfOrderId;
        $soFields['ECS__Ship_From_Address__c'] = $sfShippingAddressId;
        $soFields['ECS__Shipping_Cost__c'] = $shipment->getOrder()->getShippingAmount();
        return $soFields;
    }

    protected function _getShipmentMap()
    {
        return array(
            'created_at' => 'ECS__Shipped_Time__c' , 
            'increment_id' => 'name' , 
            'entity_id' => 'ECS__Shipping_Detail_External_Id__c'
        );
    }

    protected function _getTrackingMap()
    {
        return array(
            'track_number' => 'ECS__Tracking_Number__c' , 
            'title' => 'ECS__Shipping_Class__c' , 
            'carrier_code' => 'ECS__Shipping_Carrier__c'
        );
    }

    protected function _getFirstTrackingByShipment($shipment)
    {
        $tracks = $shipment->getAllTracks();
        return isset($tracks[0]) ? $tracks[0] : false;
    }
}




