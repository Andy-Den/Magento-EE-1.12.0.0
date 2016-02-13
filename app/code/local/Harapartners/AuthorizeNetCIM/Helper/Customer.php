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

class Harapartners_AuthorizeNetCIM_Helper_Customer extends Mage_Core_Helper_Abstract{

	public function getCustomerGroupId($paymentMethod){
    	$groupId = null; //Note, "Not Logged In" group has ID 0, must check null, NOT just !!$groupId
    	
    	try{
	    	if(Mage::app()->getStore()->isAdmin()){
	    		//Admin logic
	    		$adminQuote = Mage::getSingleton('adminhtml/session_quote');
	    		if(!!$adminQuote && !!$adminQuote->getCustomer() && $adminQuote->getCustomer()->getGroupId()){
			   		$groupId = $adminQuote->getCustomer()->getGroupId();
		    	}
	    	}else{
	    		//Frontend checkout, Check if User is Logged In
	    		$login = Mage::getSingleton( 'customer/session' )->isLoggedIn();
	    		if($login){
	    	    	$groupId = Mage::getSingleton('customer/session')->getCustomer()->getGroupId();
	    		}
	    	}
	    	
	    	//Cronjob batch processing does NOT create customer session
	    	if($groupId === null){
	    		//Test empty data, $paymentMethod->getInfoInstance() will force an exception!
	    		if(!!$paymentMethod->getData('info_instance')){
	    			$quote = $paymentMethod->getInfoInstance()->getQuote();
	    		}
			    if(!!$quote && !!$quote->getCustomer() && $quote->getCustomer()->getGroupId()){
			   		$groupId = $quote->getCustomer()->getGroupId();
		    	}
			}
			
			//At last, we assume it's Guest checkout, please note, this default must be at last
			//Due to the previous check on $groupId === null
			$groupId = 0;
			
    	}catch (Exception $e){
    		//$paymentMethod->getInfoInstance() may trigger exceptions, do nothing here, assume $groupId = null for the following logic
    	}
    	
    	return $groupId;
	}
    
}