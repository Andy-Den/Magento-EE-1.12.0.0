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
 
class Harapartners_AuthorizeNetCIM_Model_Mysql4_Profilemanager extends Mage_Core_Model_Mysql4_Abstract{
	
	protected function _construct(){
		$this->_init('authorizenetcim/profilemanager', 'entity_id');
	}
	
	public function loadByCustomerPaymentProfileId($customerPaymentProfileId){
		$read = $this->_getReadAdapter();
		$select = $read->select()->from($this->getMainTable())
				->where('customer_payment_profile_id=?', $customerPaymentProfileId);
		$result = $read->fetchRow($select);
		if(!$result){
			return array();
		}
		return $result;
	}
	
// ===== Obsolete functions ver 1.0	
//	public function loadCustomerProfileIdByCustomerId($customerId){
//		$read = $this->_getReadAdapter();
//		$select = $read->select()->from($this->getMainTable(), array('customer_profile_id'=>'customer_profile_id'))->where('customer_id=?', $customerId);
//		$result = $read->fetchRow($select);
//		if(!$result){
//			return null;
//		}else{
//			return isset( $result["customer_profile_id"] ) ? $result["customer_profile_id"]: null;
//		}
//	}
//	
//	public function loadCustomerProfileIdByPaymentProfileId($paymentProfileId){
//		$read = $this->_getReadAdapter();
//		$select = $read->select()->from($this->getMainTable(), array('customer_profile_id'=>'customer_profile_id'))->where('customer_payment_profile_id=?', $paymentProfileId);
//		$result = $read->fetchRow($select);
//		if(!$result){
//			return null;
//		}else{
//			return isset( $result["customer_profile_id"] ) ? $result["customer_profile_id"]: null;
//		}
//	}
	
}