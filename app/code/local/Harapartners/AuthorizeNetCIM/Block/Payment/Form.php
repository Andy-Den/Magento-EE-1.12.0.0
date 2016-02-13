<?php 

/* NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 */ 
 
class Harapartners_AuthorizeNetCIM_Block_Payment_Form extends Mage_Payment_Block_Form_Cc
{
    protected function _construct() {
        parent::_construct();
        $this->setTemplate('authorizenetcim/payment/form.phtml');
    }
    
    public function getProfileInfo($customerId) {
    	$profiles = Mage::getModel('authorizenetcim/profilemanager')->getCollection()->getCustomerProfilesByCustomerId($customerId);
    	$info = array();
    	foreach ($profiles as $profile) {
    		$encodedPaymentProfileId = Mage::helper('authorizenetcim')->paymentProfileIdEncode($profile->getData('customer_payment_profile_id'));
    		$info[] = array('last4digits' => $profile->getData('last4digits'), 'encodedPaymentProfileId' => $encodedPaymentProfileId);
    	}
    	return $info;
    }
    
    public function isPasswordValidationRequired() {
    	return Mage::helper('authorizenetcim/checkout')->isPasswordValidationRequired();
    }
    
}