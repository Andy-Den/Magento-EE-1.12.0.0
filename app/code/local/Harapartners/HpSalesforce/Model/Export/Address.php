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
class Harapartners_HpSalesforce_Model_Export_Address
{

    public function createSfAddressesObjects($billingAddress, $shippingAddress, $sfCustomerId)
    {
        $recordBilling = new SObject();
        $recordBilling->fields = $this->_mapIntoFields($billingAddress, $sfCustomerId);
        $recordBilling->type = 'ECS__Address__c';
        
        $recordShipping = new SObject();
        $recordShipping->fields = $this->_mapIntoFields($shippingAddress, $sfCustomerId);
        $recordShipping->type = 'ECS__Address__c';
        
        return array(
            $recordBilling , 
            $recordShipping
        );
    }

    protected function _mapIntoFields($address, $sfCustomerId)
    {
        $soFields = array();
        $varienMapper = new Varien_Object_Mapper();
        $soFields = $varienMapper->accumulateByMap($address->getData(), $soFields, $this->_getAddressMap());
        
        // Extra Stuff
        $soFields['ECS__Customer__c'] = $sfCustomerId;
        list ($soFields['ECS__Address_Line_1__c'], $soFields['ECS__Address_Line_2__c']) = $address->getStreet();
        $soFields['ECS__Is_Billing_Address__c'] = $address->getAddressType() == 'billing' ? 1 : 0;
        $soFields['ECS__Is_Shipping_Address__c'] = $address->getAddressType() == 'shipping' ? 1 : 0;
        return $soFields;
    }

    protected function _getAddressMap()
    {
        return array(
            'entity_id' => 'ECS__External_Address_ID__c' , 
            'region' => 'ECS__State_Province_Region__c' , 
            'postcode' => 'ECS__Zip_Postal_Code__c' , 
            'city' => 'ECS__City__c' , 
            'company' => 'ECS__Company__c' , 
            'telephone' => 'ECS__Phone_Number__c' , 
            'country_id' => 'ECS__Country__c'
        );
    }
}




