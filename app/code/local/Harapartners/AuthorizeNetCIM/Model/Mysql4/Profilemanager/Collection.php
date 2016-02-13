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
 
class Harapartners_AuthorizeNetCIM_Model_Mysql4_Profilemanager_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract{
	public function _construct(){
		$this->_init('authorizenetcim/profilemanager');
	}
	
	public function getCustomerProfilesByCustomerId( $customerId ) {
		$this->getSelect()->where( 'customer_id = ?', $customerId );
		return $this;
	}
	
	public function getAllExpiredProfiles(){
		$this->getSelect()->where( 'status = ?', Harapartners_AuthorizeNetCIM_Model_Profilemanager::STATUS_ACTIVE)
				->where( 'STR_TO_DATE(expire_date, "%Y-%m") < CURRENT_DATE()');
		return $this;
	}
	
	public function countPaymentProfiles( $currentCustomerId ) {
		$this->getSelect()->where( 'customer_id = ?', $currentCustomerId );
		return $this->count();
	}
}