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

class Harapartners_AuthorizeNetCIM_Helper_Data extends Mage_Core_Helper_Abstract{

	public function uriEncode( $uri ) {
//		return Mage::getModel('core/encryption')->encrypt( $uri );
		return base64_encode( $uri );
	}
	
	public function uriDecode( $encodedUri ) {
//		return Mage::getModel('core/encryption')->decrypt( $encodedUri );
		return base64_decode( $encodedUri );
	}
	
	public function paymentProfileIdEncode($paymentProfileId) {
		return Mage::getModel('core/encryption')->encrypt( $paymentProfileId );
	}
	
	public function paymentProfileIdDecode($encodedPaymentProfileId) {
		return Mage::getModel('core/encryption')->decrypt( $encodedPaymentProfileId );
	}
}