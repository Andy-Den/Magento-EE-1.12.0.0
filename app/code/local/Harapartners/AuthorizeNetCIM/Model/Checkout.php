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

class Harapartners_AuthorizeNetCIM_Model_Checkout extends Mage_Paygate_Model_Authorizenet
{
	const METHOD_CODE = 'authorizenetcim';
	const CIM_RESPONSE_DELIM_CHAR = ",";
	
	protected $_code  = self::METHOD_CODE;
	protected $_formBlockType = 'authorizenetcim/payment_form';//need revisit
    protected $_infoBlockType = 'paygate/authorizenet_info_cc';//need revisit
	protected $_checkoutHelper;
	
	// ===== Special AEC store only logic, START ===== //
	public function getConfigPaymentAction(){
		if($this->getInfoInstance()->getOrder()->getIsVirtual()){
			return Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE;
		}else{
        	return $this->getConfigData('payment_action');
		}
    }
    // ===== Special AEC store only logic, END ===== //
    
	public function isAvailable($quote = null) {
        return $this->isCustomerGroupAvailable() && parent::isAvailable($quote);
    }
    
    public function isCustomerGroupAvailable(){
    	$groupId = Mage::helper('authorizenetcim/customer')->getCustomerGroupId($this);
    	//Note, "Not Logged In" group has ID 0, must check null, NOT just !!$groupId
    	if($groupId !== null){
    	    $allowedGroupIds = $this->getConfigData('allowed_customer_group_ids');
    	    if(in_array($groupId, explode(',', $allowedGroupIds))){
    	    	return true;
    	    }
		}
    	return false;
    }
    
    protected function _buildRequest(Varien_Object $payment) {
    	if (!$payment->getData('payment_profile_id')) {
			return parent::_buildRequest($payment);
		}
	    
	    $order = $payment->getOrder();
        $this->setStore($order->getStoreId());
        
        // ===== $merchantAuthenticationBlock ===== //
        $merchantAuthenticationBlock = Mage::helper('authorizenetcim/connection')->merchantAuthenticationBlock();
        
	    // ===== $profileBlock ===== //
	    $paymentProfileId = $payment->getData('payment_profile_id');
	    $paymentProfile = Mage::getModel('authorizenetcim/profilemanager')->loadByCustomerPaymentProfileId($paymentProfileId);
        $customerProfileId = $paymentProfile->getCustomerProfileId();
    	if(!$customerProfileId || !$paymentProfileId ) {
			Mage::throwException(Mage::helper('authorizenetcim')->__('Invalid payment profile'));
		}
		$profileBlock = "<customerProfileId>$customerProfileId</customerProfileId><customerPaymentProfileId>$paymentProfileId</customerPaymentProfileId>";
		
		// ===== $amountBlock ===== //
		$amountBlock = "";
    	if ($payment->getAmount() <= 0.0) {
	        Mage::throwException(Mage::helper('authorizenetcim')->__('Invalid amount for authorization.'));
	    }
    	$amountBlock .= "<amount>{$payment->getAmount()}</amount>";
		if ($order && $order->getTaxAmount()) {
			$amountBlock .= "<tax><amount>{$order->getTaxAmount()}</amount></tax>";
		}
		if ($order && $order->getShippingAmount()) {
			$amountBlock .= "<shipping><amount>{$order->getShippingAmount()}</amount></shipping>";
		}
		
		// ===== $lineItemsBlock ===== //
		$lineItemsBlock = "";
		foreach( $order->getAllItems() as $item ) {
			if( $item->getParentItemId() ) {
				continue;
			}
			$qty = $item->getQty();
			if(!is_numeric($qty)){
				$qty = 1.0;
			}
			$taxable = $item->getData( "tax_amount" ) ? 'true' : 'false';
			$lineItemsBlock .= <<< LINE_ITEMS
<lineItems>
	<itemId>{$item->getItemId()}</itemId>
	<name>{$item->getSku()}</name>
	<quantity>$qty</quantity>
	<unitPrice>{$item->getPrice()}</unitPrice>
	<taxable>$taxable</taxable>
</lineItems>
LINE_ITEMS;
		}
		
    	// ===== $orderBlock ===== //
		$orderBlock = "";
        if ($order && $order->getIncrementId()) {
			$orderBlock .= "<order><invoiceNumber>{$order->getIncrementId()}</invoiceNumber></order>";
		}
		
		// ===== $xmlRequest ===== //
    	switch ($payment->getAnetTransType()) {
			case Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_ONLY:
				$actionTag = 'profileTransAuthOnly';
				break;
			case Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_CAPTURE:
				$actionTag = 'profileTransAuthCapture';
				break;
			default:
				Mage::throwException(Mage::helper('authorizenetcim')->__('Invalid request type'));
		}
		$xmlRequest = <<< XML_REQUEST
<?xml version="1.0" encoding="utf-8"?>
<createCustomerProfileTransactionRequest xmlns="AnetApi/xml/v1/schema/AnetApiSchema.xsd">
	$merchantAuthenticationBlock
	<transaction>
		<$actionTag>
			$amountBlock
			$lineItemsBlock
			$profileBlock
			$orderBlock
		</$actionTag>
	</transaction>
</createCustomerProfileTransactionRequest>
XML_REQUEST;

		return new Varien_Object(array('xml_request' => $xmlRequest));
    }
    
	protected function _postRequest(Varien_Object $request) {
		
		if ($request instanceof Mage_Paygate_Model_Authorizenet_Request) {
			return parent::_postRequest($request);
		}
		
		// ===== Prepare request debug data ===== //
		$debugData = array('request' => array());
		try{
			//Quick way for XML array conversion
			$requestArray = json_decode(json_encode((array)simplexml_load_string($request->getXmlRequest())), 1);
			//The first element for either auth_only request or auth_capture request
			foreach($requestArray['transaction'] as $transaction){
				$debugData = array('request' => $transaction);
				break;
			}  
		}catch(Exception $e){
			//Do nothing
		}
		
		// ===== Sending request ===== //
        $result = Mage::getModel('paygate/authorizenet_result');
        try {
             $response = Mage::helper('authorizenetcim/connection')->sendXmlRequest($request->getXmlRequest());
             $responseBody = Mage::helper('authorizenetcim/connection')->parseApiResponse($response);
             $r = explode(self::CIM_RESPONSE_DELIM_CHAR, (string)$responseBody->directResponse);
        } catch (Exception $e) {
            $result->setResponseCode(-1)
                ->setResponseReasonCode($e->getCode())
                ->setResponseReasonText($e->getMessage());

            $debugData['result'] = $result->getData();
            $this->_debug($debugData);
            Mage::throwException($this->_wrapGatewayError($e->getMessage()));
        }
        
        // ===== Prepare response debug data ===== //
        
        if ($r) {
            $result->setResponseCode((int)str_replace('"','',$r[0]))
	                ->setResponseSubcode((int)str_replace('"','',$r[1]))
	                ->setResponseReasonCode((int)str_replace('"','',$r[2]))
	                ->setResponseReasonText($r[3])
	                ->setApprovalCode($r[4])
	                ->setAvsResultCode($r[5])
	                ->setTransactionId($r[6])
	                ->setInvoiceNumber($r[7])
	                ->setDescription($r[8])
	                ->setAmount($r[9])
	                ->setMethod($r[10])
	                ->setTransactionType($r[11])
	                ->setCustomerId($r[12])
	                ->setMd5Hash($r[37])
	                ->setCardCodeResponseCode($r[38])
	                ->setCAVVResponseCode( (isset($r[39])) ? $r[39] : null)
	                ->setSplitTenderId($r[52])
	                ->setAccNumber($r[50])
	                ->setCardType($r[51])
	                ->setRequestedAmount($r[53])
	                ->setBalanceOnCard($r[54])
	        ;
        }
        else {
             Mage::throwException(
                Mage::helper('paygate')->__('Error in payment gateway.')
            );
        }

        $debugData['result'] = $result->getData();
        $this->_debug($debugData);

        return $result;
    }
    
    // ===== For security purposes, Authorize.net CIM module does NOT save CC type, try to restore from response
	protected function _registerCard(Varien_Object $response, Mage_Sales_Model_Order_Payment $payment) {
		if(!!$response->getCardType()){
			$ccTypeInfo = Mage::app()->getConfig()->getNode('global/payment/cc/types');
			$ccTypeInfoArray = json_decode(json_encode($ccTypeInfo), 1);
			foreach($ccTypeInfoArray as $ccType){
				if(isset($ccType['code']) && isset($ccType['name'])
						&& strcasecmp($response->getCardType(), $ccType['name']) == 0){
					$payment->setCcType($ccType['code']);
					break;
				}
			}
		}
        return parent::_registerCard($response, $payment);
    }
    
//	public function authorize(Varien_Object $payment, $amount) {
//		if (!$payment->getData('payment_profile_id')) {
//			return parent::authorize($payment, $amount);
//		} else {
//			if ($amount <= 0) {
//	            Mage::throwException(Mage::helper('authorizenetcim')->__('Invalid amount for authorization.'));
//	        }
//	        $this->_place($payment, $amount, parent::REQUEST_TYPE_AUTH_ONLY);
//	        return $this;
//		}
//	}
//	
//	public function capture(Varien_Object $payment, $amount) {
//		if (!$payment->getData('payment_profile_id')) {
//			return parent::capture($payment, $amount);
//		} else {
//			if ($amount <= 0) {
//	            Mage::throwException(Mage::helper('authorizenetcim')->__('Invalid amount for capture.'));
//	        }
//            $this->_place($payment, $amount, parent::REQUEST_TYPE_AUTH_CAPTURE);
//	        return $this;
//		}
//	}
	
	public function assignData($data) {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        
        if ($encodedPaymentProfile = $data->getData('payment_profile')) {
        	$info->setPaymentProfileId(Mage::helper('authorizenetcim')->paymentProfileIdDecode($encodedPaymentProfile));
        	$this->_validatePasswordDuringAssignData($data->getData('account_password'));
        }elseif ($data->getData('payment_profile_id')) {
        	//Added case for subscription product order creation
        	$info->setCustomerProfileId($data->getData('customer_profile_id'));
        	$info->setPaymentProfileId($data->getData('payment_profile_id'));
        }else{
	        $info->setCcType($data->getCcType())
	            ->setCcOwner($data->getCcOwner())
	            ->setCcLast4(substr($data->getCcNumber(), -4))
	            ->setCcNumber($data->getCcNumber())
	            ->setCcCid($data->getCcCid())
	            ->setCcExpMonth($data->getCcExpMonth())
	            ->setCcExpYear($data->getCcExpYear())
	            ->setCcSsIssue($data->getCcSsIssue())
	            ->setCcSsStartMonth($data->getCcSsStartMonth())
	            ->setCcSsStartYear($data->getCcSsStartYear())
	            ->setSaveCreditCard($data->getSaveCc())
	            ;
        }
        return $this;
    }
    
    protected function _validatePasswordDuringAssignData($password) {
    	//Only when assign data, i.e. checkout form
    	//not used otherwise, e.g. order place, since the password is not available
    	//Also avoid any security risk of saving password unhashed
    	if(Mage::helper('authorizenetcim/checkout')->isPasswordValidationRequired()){
			if(!$password){
				Mage::throwException('Password required for this card.');
			}
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			if(!$customer || !$customer->validatePassword($password)){
				Mage::throwException('Invalid password.');
			}
		}
    }
    
	public function validate() {
		if (!$this->getInfoInstance()->getPaymentProfileId()) {
			parent::validate();
		}
        return $this;
    }
    
//	protected function _place($payment, $amount, $requestType) {
//		if ( $paymentProfileId = $payment->getData( "payment_profile_id" ) ) {
//			$quote = $this->_getSession()->getQuote();
//			$order = $payment->getOrder();
//			$invoiceNum = $order->getIncrementId();
//			
//			//Separate admin, frontend checkout
//			$customerProfileId = $payment->getData( "customer_profile_id" );
//			if(!$customerProfileId){
//				if(Mage::app()->getStore()->isAdmin()){
//					$currentCustomer = Mage::getSingleton('adminhtml/session_quote')->getCustomer();
//				}else{
//					$currentCustomer = Mage::getSingleton('customer/session')->getCustomer();
//				}
//				if(!!$currentCustomer && !!$currentCustomer->getId()){
//					$customerProfileId = $currentCustomer->getData('cim_customer_profile_id');
//				}
//			}
//			
//			if (!$customerProfileId) {
//				Mage::throwException(Mage::helper('authorizenetcim')->__('Invalid Customer Profile Id'));
//			}
//			switch($requestType) {
//				case Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_ONLY:
//					$this->_getCheckoutHelper()->authorize($quote, $amount, $customerProfileId, $paymentProfileId, $invoiceNum);
//					break;
//				case Mage_Paygate_Model_Authorizenet::REQUEST_TYPE_AUTH_CAPTURE:
//					$this->_getCheckoutHelper()->capture($quote, $amount, $customerProfileId, $paymentProfileId, $invoiceNum);
//					break;
//				default:
//					Mage::throwException(Mage::helper('authorizenetcim')->__('Invalid request type'));
//			}
//		} else {
//			parent::_place($payment, $amount, $requestType);
//		}
//	}
	
	protected function _getCheckoutHelper(){
		if(!$this->_checkoutHelper){
			$this->_checkoutHelper = Mage::helper("authorizenetcim/checkout");
		}
		return $this->_checkoutHelper;
	}
	
	protected function _wrapGatewayError($text){
		if(trim($text) == "The transaction has been declined because of an AVS mismatch. The address provided does not match billing address of cardholder."){
			return Mage::helper('paygate')->__('There is an error in processing your order: %s', "The address provided does not match billing address of cardholder.");
		}
		return Mage::helper('paygate')->__('There is an error in processing your order: %s', $text);
	}
	
}