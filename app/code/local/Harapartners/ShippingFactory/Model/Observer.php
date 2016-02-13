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
class Harapartners_ShippingFactory_Model_Observer {
	
	public function validateVendorShipInternational($observer){
		$quote = $observer->getQuote();
		$dataHelper = Mage::helper('shippingfactory');
		$noInternationalItemArray = $dataHelper->getNoInternationalItemArray($quote);
		
		if(count($noInternationalItemArray)){
			$itemNameArray = array();
			foreach($noInternationalItemArray as $noInternationalItem){
				$itemNameArray[] = $noInternationalItem->getName();
			}
			Mage::getSingleton('checkout/session')->addError("The following item(s) cannot be shipped internationally: " . implode(", ", $itemNameArray));
		}
		
	}
	
}