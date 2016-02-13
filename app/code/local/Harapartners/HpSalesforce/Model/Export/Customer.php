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
class Harapartners_HpSalesforce_Model_Export_Customer
{

    public function createSfCustomerObject($address)
    {
        $record = new SObject();
        $record->fields = $this->_mapIntoFields($address);
        $record->type = 'Contact';
        return $record;
    }

    protected function _mapIntoFields($address)
    {
        $soFields = array();
        $varienMapper = new Varien_Object_Mapper();
        $soFields = $varienMapper->accumulateByMap($address->getData(), $soFields, $this->_getCustomerMap());
        
        // Other
        return $soFields;
    }

    protected function _getCustomerMap()
    {
        return array(
            'company' => 'ECS__Company__c' , 
            'firstname' => 'FirstName' , 
            'lastname' => 'LastName' , 
            'customer_id' => 'ECS__Customer_External_Id__c' , 
            'email' => 'Email' , 
            'telephone' => 'Phone'
        );
    }
}
