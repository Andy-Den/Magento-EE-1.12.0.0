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
class Harapartners_HpSalesforce_Model_Export_Product
{

    public function createSfProductObjects($orderItems)
    {
        $recordProducts = array();
        foreach ($orderItems as $index => $orderItem) {
            if ($orderItem->getProductType() != 'simple') {
                continue;
            }
            
            $recordProduct = new SObject();
            $recordProduct->fields = $this->_mapIntoFields($orderItem);
            $recordProduct->type = 'ECS__Product__c';
            $recordProducts[] = $recordProduct;
        }
        
        return $recordProducts;
    }

    protected function _mapIntoFields($orderItem)
    {
        $soFields = array();
        $varienMapper = new Varien_Object_Mapper();
        $soFields = $varienMapper->accumulateByMap($orderItem->getProduct()->getData(), $soFields, $this->_getProductMap());
        
        // Extra Stuff
        $soFields['ECS__Last_Updated__c'] = date('c', strtotime($soFields['ECS__Last_Updated__c']));
        $soFields['name'] = $soFields['ECS__External_Product_ID__c'];
        return $soFields;
    }

    protected function _getProductMap()
    {
        return array(
            'vendor_code' => 'ECS__Brand__c' , 
            'description' => 'ECS__Description__c' , 
            'sku' => 'ECS__External_Product_ID__c' , 
            'updated_at' => 'ECS__Last_Updated__c' , 
            'name' => 'ECS__Title__c' , 
            'upc' => 'ECS__UPC__c' , 
            'cost' => 'ECS__Cost__c' , 
            'price' => 'ECS__Price__c' , 
            'entity_id' => 'ECS__Product_Id__c'
        );
    }
}




