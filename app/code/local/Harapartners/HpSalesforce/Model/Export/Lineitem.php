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
class Harapartners_HpSalesforce_Model_Export_Lineitem
{

    public function createSfLineitemObjects($sProducts, $productSkuToSfId, $orderItemsBySku, $orderSfId, $customerSfId)
    {
        $recordLineItems = array();
        foreach ($sProducts as $index => $sProduct) {
            $recordLineItem = new SObject();
            $recordLineItem->fields = $this->_mapIntoFields($sProduct, $productSkuToSfId, $orderItemsBySku, $orderSfId, $customerSfId);
            $recordLineItem->type = 'ECS__eCommSource_Order_Line__c';
            $recordLineItems[] = $recordLineItem;
        }
        
        return $recordLineItems;
    }

    public function createSfLineItemUpdateShippingObjects($shippingLinesArray)
    {
        $recordLineItems = array();
        foreach ($shippingLinesArray as $sLineItemId => $sShippingDetailId) {
            $recordLineItem = new SObject();
            $recordLineItem->fields = array(
                'id' => $sLineItemId , 
                'ECS__Shipping_Detail__c' => $sShippingDetailId
            );
            $recordLineItem->type = 'ECS__eCommSource_Order_Line__c';
            $recordLineItems[] = $recordLineItem;
        }
        return $recordLineItems;
    }

    protected function _mapIntoFields($sProduct, $productSkuToSfId, $orderItemsBySku, $orderSfId, $customerSfId)
    {
        $soFields = array();
        $varienMapper = new Varien_Object_Mapper();
        $soFields = $varienMapper->accumulateByMap($sProduct->fields, $soFields, $this->_getLineitemMap());
        
        // Extra Stuff
        $sProductSku = $sProduct->fields['ECS__External_Product_ID__c'];
        $soFields['ECS__Customer__c'] = $customerSfId;
        $soFields['ECS__Product__c'] = $productSkuToSfId[$sProductSku];
        $soFields['ECS__Order__c'] = $orderSfId;
        $soFields['ECS__External_Order_Line_ID__c'] = $this->_getParentItemIdOrThis($orderItemsBySku[$sProductSku]);
        $soFields['ECS__Order_Line_Total__c'] = $soFields['ECS__Order_Line_Total__c'] ? $soFields['ECS__Order_Line_Total__c'] : $orderItemsBySku[$sProductSku]->getParentItem()->getRowTotal();
        $soFields['name'] = $soFields['ECS__External_Order_Line_ID__c'];
        return $soFields;
    }

    protected function _getLineitemMap()
    {
        return array(
            'row_total' => 'ECS__Order_Line_Total__c'
        );
    
    }

    protected function _getParentItemIdOrThis($item)
    {
        $parentId = $item->getParentItem()->getId();
        return empty($parentId) ? $item->id() : $parentId;
    }
}




