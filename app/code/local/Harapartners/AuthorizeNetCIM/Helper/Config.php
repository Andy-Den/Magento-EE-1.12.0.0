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

class Harapartners_AuthorizeNetCIM_Helper_Config extends Mage_Core_Helper_Abstract{
	
	protected $_loginname; 
	protected $_transactionkey; 
	protected $_mode;
	protected $_gatewayUrl;
	
	public function getApiLoginId(){
		if(!$this->_loginname){
			$this->_loginname = Mage::getStoreConfig('payment/authorizenetcim/login');
		}
		return $this->_loginname;
	}
	
	public function getTransactonKey(){
		if(!$this->_transactionkey){
			$this->_transactionkey = Mage::getStoreConfig('payment/authorizenetcim/trans_key');
		}
		return $this->_transactionkey;
	}
	
	public function getGatewayUrl(){
		if(!$this->_gatewayUrl){
			$this->_gatewayUrl = Mage::getStoreConfig('payment/authorizenetcim/cim_cgi_url');
		}
		return $this->_gatewayUrl;
	}
	
	public function getApiMode(){
		if(!$this->_mode){
			if( Mage::getStoreConfig('payment/authorizenetcim/test') ){
		 		$this->_mode = "testMode";
			}else{
		 		$this->_mode = "liveMode";
			}
		}
		return $this->_mode;
	}
	
}