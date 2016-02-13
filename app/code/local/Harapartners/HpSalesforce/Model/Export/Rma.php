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
class Harapartners_HpSalesforce_Model_Export_Rma
{

    public function createSfRmaLines($rmaLines, $sfLineItemInfoByLineItemId)
    {
        $rmaItem = Mage::getModel('enterprise_rma/item');
        $recordShipmentLines = array();
        foreach ($rmaLines as $index => $rmaLine) {
            $sfLineItemInfo = $sfLineItemInfoByLineItemId[$rmaLine->getOrderItemId()];
            
            $recordRmaLine = new SObject();
            $recordRmaLine->fields = $this->_mapIntoFields($rmaLine, $sfLineItemInfo);
            $recordRmaLine->type = 'CloudConversion__RMA_Line__c';
            $recordRmaLines[] = $recordRmaLine;
        
        }
        
        return $recordRmaLines;
    }

    protected function _mapIntoFields($rmaLine, $sfLineItemInfo)
    {
        $soFields = array();
        $varienMapper = new Varien_Object_Mapper();
        $soFields = $varienMapper->accumulateByMap($rmaLine->getData(), $soFields, $this->_getRmaItemMap());
        $soFields = $varienMapper->accumulateByMap($sfLineItemInfo, $soFields, $this->_getSfLineItemMap());
        
        // Extra Stuff
        // Nothing here
        return $soFields;
    }

    protected function _getRmaItemMap()
    {
        return array(
            'qty_approved' => 'CloudConversion__Returned_Quantity__c' , 
            'entity_id' => 'CloudConversion__External_RMA_Number__c'
        );
    }

    protected function _getSfLineItemMap()
    {
        return array(
            'id' => 'CloudConversion__Original_Order_Line__c' , 
            'productId' => 'CloudConversion__Original_Product__c' , 
            'orderId' => 'CloudConversion__Original_Order__c' , 
            'customerId' => 'CloudConversion__Customer__c'
        );
    }
}




