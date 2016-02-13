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
 
class Harapartners_AuthorizeNetCIM_Block_Account_List extends Mage_Core_Block_Template{
	
	public function getPaymentProfiles(){
		$currentCustomer = Mage::getModel('customer/session')->getCustomer();
		if($currentCustomer->getId()){
			$paymentProfiles = Mage::getModel('authorizenetcim/profilemanager')->getCollection()->getCustomerProfilesByCustomerId($currentCustomer->getId());
			return $paymentProfiles;
		}
	}
	
	public function getEditUrl( Harapartners_AuthorizeNetCIM_Model_ProfileManager $profile ) {
		$encodedUri = "key/" . Mage::helper( 'authorizenetcim' )->uriEncode( serialize( array( 
			'profile_id' => $profile->getId(), 
			'customer_id' => $profile->getData( 'customer_id' ) 
		)));
		return $this->getUrl( 'authorizenetcim/manage/edit' ).$encodedUri;
	}
	
	public function getDeleteUrl( Harapartners_AuthorizeNetCIM_Model_ProfileManager $profile ) {
		$encodedUri = "key/" . Mage::helper( 'authorizenetcim' )->uriEncode( serialize( array( 
			'profile_id' => $profile->getId(), 
			'customer_id' => $profile->getData( 'customer_id' ) 
		)));
		return $this->getUrl( 'authorizenetcim/manage/delete' ).$encodedUri;
	}
	
	public function getPagerHtml() { //need revisit
        return $this->getChildHtml('pager');
    }
	
}
