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

class Harapartners_AuthorizeNetCIM_Helper_Connection extends Mage_Core_Helper_Abstract{
	
	const AUTHORIZE_NET_SUCCESS_CODE = "Ok";
	const AUTHORIZE_NET_ERROR_CODE = "Error";
	
	protected $_configHelper;
	
	protected function _getConfigHelper(){
		if(!$this->_configHelper){
			$this->_configHelper = Mage::helper('authorizenetcim/config');
		}
		return $this->_configHelper;
	}
	
	public function sendXmlRequest($content){
		return $this->sendRequestViaCurl($content, $this->_getConfigHelper()->getGatewayUrl());
	}

	public function sendRequestViaCurl($content, $url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: text/xml"));
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $content);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		$response = curl_exec($ch);
		curl_close($ch);
		return $response;
	}

	public function parseApiResponse($content){
		$result = simplexml_load_string($content, "SimpleXMLElement", LIBXML_NOWARNING);
		//Success
		if(isset($result->messages->resultCode) 
				&& $result->messages->resultCode == Harapartners_AuthorizeNetCIM_Helper_Connection::AUTHORIZE_NET_SUCCESS_CODE 
		){
			return $result;
		}
		
		//Error handling
		//Known error
		if( isset($result->messages->resultCode) 
				&& $result->messages->resultCode == Harapartners_AuthorizeNetCIM_Helper_Connection::AUTHORIZE_NET_ERROR_CODE 
				&& isset($result->messages->message->text) 
		){
			Mage::throwException(Mage::helper('authorizenetcim')->__((string)$result->messages->message->text));
		}
		//Unknown error
		Mage::throwException(Mage::helper('authorizenetcim')->__('Failed to complete the request, please try again later.'));
	}

	public function merchantAuthenticationBlock(){
		$merchantAuthenticationBlock = <<< MERCHANT_AUTHENTICATION_BLOCK
<merchantAuthentication>
	<name>{$this->_getConfigHelper()->getApiLoginId()}</name>
	<transactionKey>{$this->_getConfigHelper()->getTransactonKey()}</transactionKey>
</merchantAuthentication>
MERCHANT_AUTHENTICATION_BLOCK;

		return $merchantAuthenticationBlock;
	}
	
}