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
 
class Harapartners_AuthorizeNetCIM_Model_Profilemanager extends Mage_Core_Model_Abstract{
	
	const STATUS_ACTIVE = 1; // This should be the default
	const STATUS_SUSPENDED = 2; //If payment failed many times is should be suspended
	const STATUS_EXPIRED = 3;
	
	protected $_profileHelper;
	
	protected function _construct(){
        $this->_init('authorizenetcim/profilemanager');
    }
	
	protected function _getProfileHelper(){
		if(!$this->_profileHelper){
			$this->_profileHelper = Mage::helper('authorizenetcim/profilemanager');
		}
		return $this->_profileHelper;
	}
	
	protected function _beforeSave(){
		$datetime = date('Y-m-d H:i:s');
    	if(!$this->getId()){
    		$this->setData('created_at', $datetime);
    	}
    	$this->setData('updated_at', $datetime);
    	if(!$this->getStoreId()){
    		$this->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID);
    	}
    	parent::_beforeSave();
    }
	
	public function createCustomerProfile($customerId, $customerEmail){
		if(!$customerId){
			Mage::throwException(Mage::helper('authorizenetcim')->__("Invalid Customer"));
		}else{
			$customerProfileId = $this->_getProfileHelper()->createCustomerProfile($customerId, $customerEmail);
		}
		return $customerProfileId;
	}
	
	public function addCreditCard($postData){
		//Data cleaning
		foreach($postData as $dataIndex => $dataValue){
			if(is_string($dataValue)){
				$postData[$dataIndex] = trim($dataValue);
			}
		}
		
		//Post data could be from either checkout form or customer account form
		$cardNumber = isset($postData["cc_number"]) ? $postData["cc_number"] : null;
		if(!empty($postData["cc_exp_year"]) && !empty($postData["cc_exp_month"])){
			$expireDate = $postData['cc_exp_year'] . '-' . str_pad($postData['cc_exp_month'], 2, '0', STR_PAD_LEFT);
		}
		$cardCvv = isset($postData["cc_cid"]) ? $postData["cc_cid"] : null;
		
		//Check if the data is from admin panel or front end
		if(Mage::app()->getStore()->isAdmin()){
			$currentCustomer = Mage::getSingleton('adminhtml/session_quote')->getCustomer();
		}else{
			$currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
		}
		
		$customerProfileId = $currentCustomer->getData('cim_customer_profile_id');
		
		if( ! $cardNumber || ! $expireDate ) {
			Mage::throwException(Mage::helper('authorizenetcim')->__("Invalid input in required fields"));
		}
		
		
		if( ! $customerProfileId ) {
			$customerProfileId = $this->createCustomerProfile($currentCustomer->getId(), $currentCustomer->getEmail());
			$currentCustomer->setData('cim_customer_profile_id', $customerProfileId);
			$currentCustomer->save();
		}
		
		$customerPaymentProfileId = $this->_getProfileHelper()->createPaymentProfile($customerProfileId, $postData);
		$regionName = Mage::helper('authorizenetcim/region')->loadRegionNameById($postData["region_id"]);
		
		$saveData = array(
			'customer_id'					=> $currentCustomer->getId(),
			'customer_profile_id' 			=> $customerProfileId,
			'customer_payment_profile_id'	=> $customerPaymentProfileId,
			'first_name'					=> isset($postData["first_name"]) ? $postData["first_name"] : null,
			'last_name'						=> isset($postData["last_name"]) ? $postData["last_name"] : null,
			'company'						=> isset($postData["company"]) ? $postData["company"] : null,
			'address'						=> isset($postData["address"]) ? $postData["address"] : null,
			'city'							=> isset($postData["city"]) ? $postData["city"] : null,
			'region'						=> $regionName,
			'region_id'						=> isset($postData["region_id"]) ? $postData["region_id"] : null,
			'zipcode'						=> isset($postData["zipcode"]) ? $postData["zipcode"] : null,
			'country'						=> isset($postData["country_id"]) ? $postData["country_id"] : null,
			'phone_number'					=> isset($postData["phone_number"]) ? $postData["phone_number"] : null,
			'card_number'					=> $cardNumber,
			'expire_date'					=> $expireDate,
			'status'						=> self::STATUS_ACTIVE
		);
		$this->savePaymentProfile($saveData);
		return $this;
	}
	
	public function editCreditCard($postData){
		
		//Data cleaning
		foreach($postData as $dataIndex => $dataValue){
			$postData[$dataIndex] = trim($dataValue);
		}
		
		$cardNumber = isset($postData["cc_number"]) ? $postData["cc_number"] : null;
		//check the data is passed from admim panel or frontend
		if($postData['admin'] == 1){
			$expireDate = $postData['expire_date'];
		}else{
			if(!empty($postData["cc_exp_year"]) && !empty($postData["cc_exp_month"])){
				$expireDate = $postData['cc_exp_year'] . '-' . str_pad($postData['cc_exp_month'], 2, '0', STR_PAD_LEFT);
			}
		}
		
		if( ! $cardNumber || ! $expireDate ) {
			Mage::throwException(Mage::helper('authorizenetcim')->__("Invalid input in required fields"));
		}
		$this->load( isset($postData["profile_id"]) ? $postData["profile_id"] : null );
		if( ! $this->getId() ) {
			Mage::throwException(Mage::helper('authorizenetcim')->__("Invalid Payment Profile"));
		}
		$customerProfileId = $this->getData( "customer_profile_id" ) ; 
		$paymentProfileId = $this->getData( "customer_payment_profile_id" );
		if($this->_getProfileHelper()->editPaymentProfile($customerProfileId, $paymentProfileId, $postData)){
			$regionName = Mage::helper('authorizenetcim/region')->loadRegionNameById($postData["region_id"]);
			$saveData = array(
				'profile_id'					=> $this->getId(),
				'first_name'					=> isset($postData["first_name"]) ? $postData["first_name"] : null,
				'last_name'						=> isset($postData["last_name"]) ? $postData["last_name"] : null,
				'company'						=> isset($postData["company"]) ? $postData["company"] : null,
				'address'						=> isset($postData["address"]) ? $postData["address"] : null,
				'city'							=> isset($postData["city"]) ? $postData["city"] : null,
				'region'						=> $regionName,
				'region_id'						=> isset($postData["region_id"]) ? $postData["region_id"] : null,
				'zipcode'						=> isset($postData["zipcode"]) ? $postData["zipcode"] : null,
				'country'						=> isset($postData["country_id"]) ? $postData["country_id"] : null,
				'phone_number'					=> isset($postData["phone_number"]) ? $postData["phone_number"] : null,
				'card_number'					=> $cardNumber,
				'expire_date'					=> $expireDate
			);
			$this->savePaymentProfile($saveData);
		}
		return $this;
	}
	
	public function deleteCreditCard($profileId){
		try{
			$this->load( $profileId );
			$this->deletePaymentProfile($this);
		}catch(Exception $e){
			Mage::throwException( Mage::helper('authorizenetcim')->__( "Invalid payment profile" ) );
		}
	}
	
	public function deletePaymentProfile( Harapartners_AuthorizeNetCIM_Model_ProfileManager $paymentProfile ){
		$customerProfileId = $paymentProfile->getData( 'customer_profile_id' ); 
		$paymentProfileId = $paymentProfile->getData( 'customer_payment_profile_id' );
		if(!$customerProfileId || !$paymentProfileId){
			Mage::throwException(Mage::helper('authorizenetcim')->__("Invalid Payment Profile"));
		}else{
			if( $this->_getProfileHelper()->deletePaymentProfile($customerProfileId, $paymentProfileId) ) {
				$paymentProfile->delete();
			}
		}
	}
	
	public function savePaymentProfile($saveData){
		if(isset( $saveData[ "profile_id" ] )){
			$this->load($saveData['profile_id']);
			if( ! $this->getId() ) {
				Mage::throwException(Mage::helper('authorizenetcim')->__("Invalid Payment Profile"));
			}
		} else if(isset($saveData['customer_profile_id']) && isset($saveData['customer_payment_profile_id'])){
			$this->setData('customer_profile_id', $saveData['customer_profile_id']);
			$this->setData('customer_payment_profile_id', $saveData['customer_payment_profile_id']);
			$this->setData('customer_id', $saveData['customer_id']);
		} else{
			Mage::throwException(Mage::helper('authorizenetcim')->__("We are unable to process the request, please contact us or try again later"));
		}
		
		$this->setData('first_name', $saveData['first_name']);
		$this->setData('last_name', $saveData['last_name']);
		$this->setData('company', $saveData['company']);
		$this->setData('address', $saveData['address']);
		$this->setData('city', $saveData['city']);
		$this->setData('region', $saveData['region']);
		$this->setData('region_id', $saveData['region_id']);
		$this->setData('zipcode', $saveData['zipcode']);
		$this->setData('country', $saveData['country']);
		$this->setData('phone_number', $saveData['phone_number']);
		$this->setData('last4digits', substr( $saveData['card_number'], -4, 4 ) );
		if(isset($saveData['status'])){
			$this->setData('status',  $saveData['status']);
		}
		if(isset($saveData['expire_date'])){
			$this->setData('expire_date', $saveData['expire_date']);
		}
		
		//Make sure cvv is not saved by accident
		$this->unsData('card_cvv');
		unset($saveData['card_cvv']);
		
		$this->save();
		return $this;
	}
	
	public function toOptionsArray(){
		return array(
			self::STATUS_ACTIVE  => 'active',
			self::STATUS_EXPIRED => 'expired',
			self::STATUS_SUSPENDED => 'pending'
		);
	}
	
	public function getCountryOptionsArray(){
		return array(
			"US"	=>  	Mage::getModel('directory/country')->loadByCode('US')->getName()
		);
	}
	
	public function getRegionOptionsArray(){
		$regions = Mage::getModel('directory/country')->loadByCode('US')->getRegions()->getItems();
		$regionMap = array();
		foreach($regions as $regionId => $region){
			$regionId = $region->getId();
			$regionMap[$regionId] = $region->getName();	
		}
		return $regionMap;
	}
	
	public function loadByCustomerPaymentProfileId($customerPaymentProfileId){
		$result = $this->getResource()->loadByCustomerPaymentProfileId($customerPaymentProfileId);
		$this->addData($result);
		return $this;
	}
	

// ===== Obsolete function from ver 1.0 ===== //	
//	public function loadCustomerProfileIdByCustomerId($customerId){
//		return $this->getResource()->loadCustomerProfileIdByCustomerId($customerId);
//	}
//	
//	public function loadCustomerProfileIdByPaymentProfileId($paymentProfileId){
//		return $this->getResource()->loadCustomerProfileIdByPaymentProfileId($paymentProfileId);
//	}
//
//	public function deleteCustomerProfile( Harapartners_AuthorizeNetCIM_Model_ProfileManager $paymentProfile ){
//		$customerProfileId = $paymentProfile->getData( 'customer_profile_id' ); 
//		if($customerProfileId){
//			if( $this->_getProfileHelper()->deleteCustomerProfile($customerProfileId) ) {
//				$paymentProfile->delete();
//			}
//		}else{
//			Mage::throwException(Mage::helper('authorizenetcim')->__("Invalid Customer"));
//		}
//	}
	
}