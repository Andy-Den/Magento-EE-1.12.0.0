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

class Harapartners_AuthorizeNetCIM_Helper_Checkout extends Mage_Core_Helper_Abstract{
	
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
	
	public function authorize($quote, $amount, $customerProfileId, $paymentProfileId, $invoiceNum = null) {
		$this->_processRequest($quote, $amount, $customerProfileId, $paymentProfileId, Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_ONLY, $invoiceNum);
	}
	
	public function capture($quote, $amount, $customerProfileId, $paymentProfileId, $invoiceNum = null) {
		$this->_processRequest($quote, $amount, $customerProfileId, $paymentProfileId, Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_CAPTURE, $invoiceNum);
	}
	
	public function isPasswordValidationRequired() {
    	return Mage::getStoreConfig('payment/authorizenetcim/require_password_validation');
    }
	
	protected function _processRequest( Mage_Sales_Model_Quote $quote, $amount, $customerProfileId, $paymentProfileId, $requestType, $invoiceNum = null ){
		if( ! $customerProfileId || ! $paymentProfileId ) {
			Mage::throwException(Mage::helper('authorizenetcim')->__('Invalid payment profile'));
		}
		switch ($requestType) {
			case Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_ONLY:
				$actionTag = 'profileTransAuthOnly';
				break;
			case Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_CAPTURE:
				$actionTag = 'profileTransAuthCapture';
				break;
			default:
				Mage::throwException(Mage::helper('authorizenetcim')->__('Invalid request type'));
		}
		$content ="<?xml version=\"1.0\" encoding=\"utf-8\"?>" .
				  "<createCustomerProfileTransactionRequest xmlns=\"AnetApi/xml/v1/schema/AnetApiSchema.xsd\">" .
				  $this->_getConnectionHelper()->merchantAuthenticationBlock().
				  "<transaction>".
				  "<$actionTag>".
				  "<amount>" . $amount . "</amount>";
		$totals = $quote->getTotals();
		if (isset($totals['tax']) && is_a($totals['tax'], 'Mage_Sales_Model_Quote_Address_Total')) {
			$content .= '<tax><amount>' . $totals['tax']->getData('value') . '</amount></tax>';
		}
		if (isset($totals['shipping']) && is_a($totals['shipping'], 'Mage_Sales_Model_Quote_Address_Total')) {
			$content .= '<shipping><amount>' . $totals['shipping']->getData('value') . '</amount></shipping>';
		}
		foreach( $quote->getAllItems() as $item ) {
			if( $item->getParentItem() ) {
				continue;
			}
			$taxable = $item->getData( "tax_amount" ) ? 'true' : 'false';
			$content .= "<lineItems>".
					  	"<itemId>" . $item->getData( "item_id" ) . "</itemId>".
					  	"<name>" . $item->getData( "sku" ) . "</name>".
					  	"<quantity>" . $item->getData( "qty" ) . "</quantity>".
						"<unitPrice>" . $item->getData( "price" ) . "</unitPrice>".
						'<taxable>' . $taxable . '</taxable>' .
					  	"</lineItems>";
		}
		$content .= "<customerProfileId>" . $customerProfileId . "</customerProfileId>".
				  	"<customerPaymentProfileId>" . $paymentProfileId . "</customerPaymentProfileId>";
		if (is_numeric($invoiceNum)) {
			$content .= "<order><invoiceNumber>" . $invoiceNum . "</invoiceNumber></order>";
		}
		$content .= "</$actionTag>".
				  	"</transaction>".
				  	"</createCustomerProfileTransactionRequest>";
				  
		$response = $this->_getConnectionHelper()->sendXmlRequest($content);
	    $result = $this->_getConnectionHelper()->parseApiResponse($response);
		if(isset($result->messages->resultCode) && $result->messages->resultCode == Harapartners_AuthorizeNetCIM_Helper_Connection::AUTHORIZE_NET_SUCCESS_CODE ){
			return $this;
		}else if( isset($result->messages->resultCode) && $result->messages->resultCode == Harapartners_AuthorizeNetCIM_Helper_Connection::AUTHORIZE_NET_ERROR_CODE && isset($result->messages->message->text) ) {
			Mage::throwException(Mage::helper('authorizenetcim')->__($result->messages->message->text));
		} else{
			Mage::throwException(Mage::helper('authorizenetcim')->__('Failed to complete the request, please try again later.'));
		}
	}
}