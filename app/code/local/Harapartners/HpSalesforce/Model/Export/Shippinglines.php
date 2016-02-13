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
class Harapartners_HpSalesforce_Model_Export_Shippinglines
{

    public function createSfShippingLines($shipments, $sfLineItemInfos, $sfShippingDetailIds)
    {
        $recordShipmentLines = array();
        foreach ($shipments as $index => $shipment) {
            foreach ($shipment->getAllItems() as $itemCount => $shipmentItem) {
                $sfLineItemInfo = $sfLineItemInfos[$shipmentItem->getOrderItemId()];
                $sfShippingDetailId = $sfShippingDetailIds[$shipmentItem->getParentId()];
                
                $recordShippmentLine = new SObject();
                $recordShippmentLine->fields = $this->_mapIntoFields($shipmentItem, $sfLineItemInfo, $sfShippingDetailId);
                $recordShippmentLine->type = 'ECS__Shipment_Line__c';
                $recordShipmentLines[] = $recordShippmentLine;
            }
        }
        
        return $recordShipmentLines;
    }

    protected function _mapIntoFields($shipmentItem, $sfLineItemInfo, $sfShippingDetailId)
    {
        $soFields = array();
        $varienMapper = new Varien_Object_Mapper();
        $soFields = $varienMapper->accumulateByMap($shipmentItem->getData(), $soFields, $this->_getShipmentItemMap());
        $soFields = $varienMapper->accumulateByMap($sfLineItemInfo, $soFields, $this->_getSfLineItemMap());
        
        // Extra Stuff
        $soFields['ECS__Shipping_Detail__c'] = $sfShippingDetailId;
        return $soFields;
    }

    protected function _getShipmentItemMap()
    {
        return array(
            'qty' => 'ECS__Quantity__c'
        );
    }

    protected function _getSfLineItemMap()
    {
        return array(
            'id' => 'ECS__Order_Line__c' , 
            'productId' => 'ECS__Product__c' , 
            'sku' => 'name'
        );
    }
}




