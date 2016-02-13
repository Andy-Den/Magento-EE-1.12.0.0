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

class Harapartners_AuthorizeNetCIM_Helper_Profilemanager extends Mage_Core_Helper_Abstract{
	
	const LOG_FILE = 'hpauthorizenetcim_profilemanager.log';
	
	protected $_configHelper;
	protected $_connectionHelper;
	
	protected function _getConfigHelper(){
		if(!$this->_configHelper){
			$this->_configHelper = Mage::helper('authorizenetcim/config');
		}
		return $this->_configHelper;
	}
	
	protected function _getConnectionHelper(){
		if(!$this->_connectionHelper){
			$this->_connectionHelper = Mage::helper('authorizenetcim/connection');
		}
		return $this->_connectionHelper;
	}
	
	public function createCustomerProfile($customerId, $customerEmail){
		if($customerId && $customerEmail){
			$content = "<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
					   "<createCustomerProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
					   $this->_getConnectionHelper()->merchantAuthenticationBlock().
					   "<profile>".
					   "<merchantCustomerId>".$this->_generateMerchantCustomerId($customerId)."</merchantCustomerId>". 
					   "<description></description>".
					   "<email>" . $customerEmail. "</email>".
					   "</profile>".
					   "</createCustomerProfileRequest>";
		    $response = $this->_getConnectionHelper()->sendXmlRequest($content);
		    $result = $this->_getConnectionHelper()->parseApiResponse($response);
			if(isset($result->messages->resultCode) && $result->messages->resultCode == Harapartners_AuthorizeNetCIM_Helper_Connection::AUTHORIZE_NET_SUCCESS_CODE && isset($result->customerProfileId) ){
				return (int)$result->customerProfileId;
			}elseif( isset($result->messages->resultCode) && $result->messages->resultCode == Harapartners_AuthorizeNetCIM_Helper_Connection::AUTHORIZE_NET_ERROR_CODE && isset($result->messages->message->text) ) {
				Mage::log((string)$result->messages->message->text, null, self::LOG_FILE, true);
				Mage::throwException(Mage::helper('authorizenetcim')->__($result->messages->message->text));
			}else{
				Mage::log('Failed to complete the request, please try again later.', null, self::LOG_FILE, true);
				Mage::throwException(Mage::helper('authorizenetcim')->__('Failed to complete the request, please try again later.'));
			}
		}
	}
	
	public function createPaymentProfile($customerProfileId, $postData){
		$firstName = isset($postData["first_name"]) ? $postData["first_name"] : null;
		$lastName = isset($postData["last_name"]) ? $postData["last_name"] : null;
		$phoneNumber = isset($postData["phone_number"]) ? $postData["phone_number"] : null;
		$company = isset($postData["company"]) ? $postData["company"] : null;
		$address = isset($postData["address"]) ? $postData["address"] : null;
		$city = isset($postData["city"]) ? $postData["city"] : null;
		$state = Mage::helper('authorizenetcim/region')->loadRegionNameById($postData["region_id"]);
		$zipCode = isset($postData["zipcode"]) ? $postData["zipcode"] : null;
		$country = isset($postData["country_id"]) ? $postData["country_id"] : null;
		$cardNumber = isset($postData["cc_number"]) ? $postData["cc_number"] : null;
		$expireYear = $postData["cc_exp_year"];
		$expireMonth = $postData["cc_exp_month"];
		//Difference between admin panel and frontend
		if($postData['admin'] == 1){
			$expireDate = isset($postData["expire_date"]) ? $postData["expire_date"] : null;
		}else{
			$expireDate = $postData['cc_exp_year'] . '-' . str_pad($postData['cc_exp_month'], 2, '0', STR_PAD_LEFT); // required format for API is YYYY-MM
		}
		$cardCvv = isset($postData["cc_cid"]) ? $postData["cc_cid"] : null;
		
		
		$content = <<<XML_TEMPLATE
<?xml version="1.0" encoding="utf-8"?>
<createCustomerPaymentProfileRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
	{$this->_getConnectionHelper()->merchantAuthenticationBlock()}
	<customerProfileId>$customerProfileId</customerProfileId>
	<paymentProfile>
		<billTo>
			<firstName>$firstName</firstName>
			<lastName>$lastName</lastName>
			<company>$company</company>
			<address>$address</address>
			<city>$city</city>
			<state>$state</state>
			<zip>$zipCode</zip>
			<country>$country</country>
			<phoneNumber>$phoneNumber</phoneNumber>
		</billTo>
		<payment>
			<creditCard>
				<cardNumber>$cardNumber</cardNumber>
				<expirationDate>$expireDate</expirationDate>
				<cardCode>$cardCvv</cardCode>
			</creditCard>
		</payment>
	</paymentProfile>
	<validationMode>{$this->_getConfigHelper()->getApiMode()}</validationMode>
</createCustomerPaymentProfileRequest>
XML_TEMPLATE;

	    $response = $this->_getConnectionHelper()->sendXmlRequest($content);
	    $result = $this->_getConnectionHelper()->parseApiResponse($response);
		if(isset($result->messages->resultCode) && $result->messages->resultCode == Harapartners_AuthorizeNetCIM_Helper_Connection::AUTHORIZE_NET_SUCCESS_CODE && isset($result->customerPaymentProfileId) ){
			return (int)$result->customerPaymentProfileId;
		} else if( isset($result->messages->resultCode) && $result->messages->resultCode == Harapartners_AuthorizeNetCIM_Helper_Connection::AUTHORIZE_NET_ERROR_CODE && isset($result->messages->message->text) ) {
			Mage::log((string)$result->messages->message->text, null, self::LOG_FILE, true);
			Mage::throwException(Mage::helper('authorizenetcim')->__($result->messages->message->text));
		} else {
			Mage::log('Failed to complete the request, please try again later.', null, self::LOG_FILE, true);
			Mage::throwException(Mage::helper('authorizenetcim')->__('Failed to complete the request, please contact us or try again later.'));	    
		}
	}
	
	public function editPaymentProfile($customerProfileId, $paymentProfileId, $postData){
		$firstName = isset($postData["first_name"]) ? $postData["first_name"] : null;
		$lastName = isset($postData["last_name"]) ? $postData["last_name"] : null;
		$phoneNumber = isset($postData["phone_number"]) ? $postData["phone_number"] : null;
		$company = isset($postData["company"]) ? $postData["company"] : null;
		$address = isset($postData["address"]) ? $postData["address"] : null;
		$city = isset($postData["city"]) ? $postData["city"] : null;
		$state = Mage::helper('authorizenetcim/region')->loadRegionNameById($postData["region_id"]);
		$zipCode = isset($postData["zipcode"]) ? $postData["zipcode"] : null;
		$country = isset($postData["country_id"]) ? $postData["country_id"] : null;
		$cardNumber = isset($postData["cc_number"]) ? $postData["cc_number"] : null;
		$expireYear = $postData["cc_exp_year"];
		$expireMonth = $postData["cc_exp_month"];
		if($postData['admin'] == 1){
			$expireDate = isset($postData["expire_date"]) ? $postData["expire_date"] : null;
		}else {
			$expireDate = $expireYear . "-" . $expireMonth;
		}
		
		
		$content ="<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
				  "<updateCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
				  $this->_getConnectionHelper()->merchantAuthenticationBlock().
				  "<customerProfileId>" . $customerProfileId . "</customerProfileId>".
				  "<paymentProfile>".
				  "<billTo>".
				  "<firstName>".$firstName."</firstName>".
				  "<lastName>".$lastName."</lastName>".
				  "<company>".$company."</company>".
				  "<address>".$postData["address"]."</address>".
				  "<city>".$city."</city>".
				  "<state>".$state."</state>".
				  "<zip>".$zipCode."</zip>".
				  "<country>".$country."</country>".
				  "<phoneNumber>".$phoneNumber."</phoneNumber>".
				  "</billTo>".
				  "<payment>".
				  "<creditCard>".
				  "<cardNumber>".$cardNumber."</cardNumber>".
				  "<expirationDate>".$expireDate."</expirationDate>". // required format for API is YYYY-MM
				  "</creditCard>".
				  "</payment>".
				  "<customerPaymentProfileId>" . $paymentProfileId . "</customerPaymentProfileId>".
				  "</paymentProfile>".
				  "<validationMode>".$this->_getConfigHelper()->getApiMode()."</validationMode>". // or testMode
				  "</updateCustomerPaymentProfileRequest>";
		$response = $this->_getConnectionHelper()->sendXmlRequest($content);
	    $result = $this->_getConnectionHelper()->parseApiResponse($response);
		if(isset($result->messages->resultCode) && $result->messages->resultCode == Harapartners_AuthorizeNetCIM_Helper_Connection::AUTHORIZE_NET_SUCCESS_CODE ){
			return true;
		} else if( isset($result->messages->resultCode) && $result->messages->resultCode == Harapartners_AuthorizeNetCIM_Helper_Connection::AUTHORIZE_NET_ERROR_CODE && isset($result->messages->message->text) ) {
			Mage::log((string)$result->messages->message->text, null, self::LOG_FILE, true);
			Mage::throwException(Mage::helper('authorizenetcim')->__($result->messages->message->text));
		} else {
			Mage::log('Failed to complete the request, please contact us or try again later.', null, self::LOG_FILE, true);
			Mage::throwException(Mage::helper('authorizenetcim')->__('Failed to complete the request, please contact us or try again later.'));	    
		}
	}
	
	public function deletePaymentProfile($customerProfileId, $paymentProfileId){
		$content ="<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
			  "<deleteCustomerPaymentProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
			  $this->_getConnectionHelper()->merchantAuthenticationBlock().
			  "<customerProfileId>" . $customerProfileId . "</customerProfileId>".
			  "<customerPaymentProfileId>" . $paymentProfileId . "</customerPaymentProfileId>".
			  "</deleteCustomerPaymentProfileRequest>";
  	    $response = $this->_getConnectionHelper()->sendXmlRequest($content);
	   	$result = $this->_getConnectionHelper()->parseApiResponse($response);
		if(isset($result->messages->resultCode) && $result->messages->resultCode == Harapartners_AuthorizeNetCIM_Helper_Connection::AUTHORIZE_NET_SUCCESS_CODE){
			return true;
		} else if( isset($result->messages->resultCode) && $result->messages->resultCode == Harapartners_AuthorizeNetCIM_Helper_Connection::AUTHORIZE_NET_ERROR_CODE && isset($result->messages->message->text) ) {
			Mage::log((string)$result->messages->message->text, null, self::LOG_FILE, true);
			Mage::throwException(Mage::helper('authorizenetcim')->__($result->messages->message->text));
		} else {
			Mage::log('Failed to complete the request, please contact us or try again later.', null, self::LOG_FILE, true);
		  	Mage::throwException(Mage::helper('authorizenetcim')->__('Failed to complete the request, please contact us or try again later.'));	    
		}
	}
	
	public function deleteCustomerProfile($customerProfileId){
		$content ="<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
			  "<deleteCustomerProfileRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
			  $this->_getConnectionHelper()->merchantAuthenticationBlock().
			  "<customerProfileId>" . $customerProfileId . "</customerProfileId>".
			  "</deleteCustomerProfileRequest>";
		$response = $this->_getConnectionHelper()->sendXmlRequest($content);
	   	$result = $this->_getConnectionHelper()->parseApiResponse($response);
		if(isset($result->messages->resultCode) && $result->messages->resultCode == Harapartners_AuthorizeNetCIM_Helper_Connection::AUTHORIZE_NET_SUCCESS_CODE){
			return true;
		} else if( isset($result->messages->resultCode) && $result->messages->resultCode == Harapartners_AuthorizeNetCIM_Helper_Connection::AUTHORIZE_NET_ERROR_CODE && isset($result->messages->message->text) ) {
			Mage::log((string)$result->messages->message->text, null, self::LOG_FILE, true);
			Mage::throwException(Mage::helper('authorizenetcim')->__($result->messages->message->text));
		} else {
			Mage::log('Failed to complete the request, please contact us or try again later.', null, self::LOG_FILE, true);
		  	Mage::throwException(Mage::helper('authorizenetcim')->__('Failed to complete the request, please contact us or try again later.'));	    
		}
	}
	
	protected function _generateMerchantCustomerId($customerId){
		//20 characters max
		//Separate different stores
		$store = Mage::app()->getStore();
		$baseUrl = Mage::getStoreConfig('base/unsecure/url');
		return $customerId . '_' . crc32($store->getCode() . '_' . $store->getId() . '_' . $baseUrl);
	}
	
}