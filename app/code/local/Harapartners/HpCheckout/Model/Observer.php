<?php

class Harapartners_HpCheckout_Model_Observer {
	const HPCHECKOUT_CHECKOUT_INDEX_URI = 'hpcheckout/checkout';
	
	public function hpcheckoutControllerRewrite($observer){
		$frontContoller = $observer->getEvent()->getControllerAction();
		$request = Mage::app()->getRequest();
		if($frontContoller instanceof Mage_Checkout_OnepageController){
			$frontContoller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
            Mage::app()->getResponse()->setRedirect(Mage::getUrl(self::HPCHECKOUT_CHECKOUT_INDEX_URI));
		}
	}

}