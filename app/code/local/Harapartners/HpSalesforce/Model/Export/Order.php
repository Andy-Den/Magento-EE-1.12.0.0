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
class Harapartners_HpSalesforce_Model_Export_Order
{

    public function createSfOrderObject($order, $sfCustomerId, $sfBillingAddressId)
    {
        $recordOrder = new SObject();
        $recordOrder->fields = $this->_mapIntoFields($order, $order->getPayment(), $sfCustomerId, $sfBillingAddressId);
        $recordOrder->type = 'ECS__eCommSource_Order__c';
        
        return $recordOrder;
    }

    public function createSoOrderCancelUpdateObject($sfOrderId, $timestamp = null)
    {
        $timestamp = empty($timestamp) ? time() : $timestamp;
        $recordOrder = new SObject();
        $recordOrder->fields = array(
            'id' => $sfOrderId , 
            'ECS__Cancelled_Time__c' => date('c', $timestamp)
        );
        
        $recordOrder->type = 'ECS__eCommSource_Order__c';
        
        return $recordOrder;
    }

    protected function _mapIntoFields($order, $payment, $sfCustomerId, $sfBillingAddressId)
    {
        $soFields = array();
        $varienMapper = new Varien_Object_Mapper();
        $soFields = $varienMapper->accumulateByMap($order->getData(), $soFields, $this->_getOrderMap());
        $soFields = $varienMapper->accumulateByMap($payment->getData(), $soFields, $this->_getPaymentMap());
        
        // Extra Stuff
        $soFields['ECS__Customer__c'] = $sfCustomerId;
        $soFields['ECS__Billing_Address__c'] = $sfBillingAddressId;
        $soFields['ECS__Order_Time__c'] = date('c', strtotime($soFields['ECS__Order_Time__c']));
        $soFields['ECS__Credit_Card_Last_4__c'] = empty($soFields['ECS__Credit_Card_Last_4__c']) ? 0 : $soFields['ECS__Credit_Card_Last_4__c'];
        $soFields['name'] = $soFields['ECS__External_Order_ID__c'];
        return $soFields;
    }

    protected function _getOrderMap()
    {
        return array(
            'coupon_code' => 'ECS__Coupon_Code__c' , 
            'discount_amount' => 'ECS__Discount_Amount__c' , 
            'shipping_amount' => 'ECS__Shipping_Total__c' , 
            'entity_id' => 'ECS__External_Order_ID__c' , 
            'tax_amount' => 'ECS__Tax_Total__c' , 
            'grand_total' => 'ECS__Total_Order_Amount__c' , 
            'customer_email' => 'ECS__Customer_Email_Address__c' , 
            'created_at' => 'ECS__Order_Time__c'
        );
    }

    protected function _getPaymentMap()
    {
        return array(
            'cc_last4' => 'ECS__Credit_Card_Last_4__c' , 
            'last_trans_id' => 'ECS__Payment_Transaction_ID__c'
        ); //'method' => 'ECS__Payment_Type_Icon__c'
    

    }
}




