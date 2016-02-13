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

class Harapartners_AuthorizeNetCIM_Model_Observer {
	
	public function salesOrderPaymentPlaceStart($observer){
		$payment = $observer->getPayment(); 
		if($this->_allowCimSaveProfile($payment)){
			Mage::register('authnet_cim_payment_data', $payment->getData());
		}
		return;
	}
	
	public function salesOrderPaymentPlaceEnd($observer){
		$payment = $observer->getPayment(); 
		if($this->_allowCimSaveProfile($payment)){
			try{
				$this->saveCreditCard($observer);
			}catch(Exception $e){
				Mage::logException($e);
			}
		}
		return;
	}
	
	public function saveCreditCard($observer) {
		$paymentData = Mage::registry('authnet_cim_payment_data');
		$payment = $observer->getPayment(); 
		if(!!$paymentData){
			//Append billing address related data
			$billingAddress = $payment->getOrder()->getBillingAddress();
			$paymentData['first_name'] = $billingAddress->getData('firstname');
			$paymentData['last_name'] = $billingAddress->getData('lastname');
			$paymentData['company'] = $billingAddress->getData('company');
			$paymentData['address'] = $billingAddress->getData('street');
			$paymentData['city'] = $billingAddress->getData('city');
			$paymentData['region'] = $billingAddress->getData('region');
			$paymentData['region_id'] = $billingAddress->getData('region_id');
			$paymentData['zipcode'] = $billingAddress->getData('postcode');
			$paymentData['country_id'] = $billingAddress->getData('country_id');
			$paymentData['phone_number'] = $billingAddress->getData('telephone');

			//Payment profile ID will be added back to the order payment object for further processing (e.g. subscription)
			$profilemanager = Mage::getModel("authorizenetcim/profilemanager")->addCreditCard($paymentData);
			$payment->setData('customer_profile_id', $profilemanager->getData('customer_profile_id'));
			$payment->setData('payment_profile_id', $profilemanager->getData('customer_payment_profile_id'));
		}
		Mage::unregister('authnet_cim_payment_data');
	}
	
	public function checkCard(){
		$expiredCardProfiles = $this->_checkExpiredCard();
		$this->_setExpiredStatus($expiredCardProfiles);
		$this->_sendNotifyEmail($expiredCardProfiles);
	}
	
	protected function _allowCimSaveProfile($payment) {
		if ($payment->getData('method') == Harapartners_AuthorizeNetCIM_Model_Checkout::METHOD_CODE 
				&& $payment->getData('save_credit_card')){
			return true;
		}
		return false;
	}
	
	protected function _checkExpiredCard(){
		$expiredCardProfiles = Mage::getModel("authorizenetcim/profilemanager")->getCollection()->getAllExpiredProfiles();
		return $expiredCardProfiles;
	}
	
	protected function _setExpiredStatus($expiredCardProfiles){
		foreach($expiredCardProfiles as $profile){
			$profile->setStatus(Harapartners_AuthorizeNetCIM_Model_Profilemanager::STATUS_EXPIRED);
			$profile->save();
		}
	}
	
	protected function _sendNotifyEmail($expiredCardProfiles){
		$customer = Mage::getModel('customer/customer');
		$emailSender = Mage::helper('authorizenetcim/emailSender');
		foreach($expiredCardProfiles as $profile){
			$customerId = $profile->getCustomerId();
			$customerEmail = $customer->load($customerId)->getEmail();
			$emailSender->sendEmail($customerEmail);
		}
	}
	
}