<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 *
*/

require_once BP . DS .'app/code/core/Mage/Checkout/controllers/CartController.php';
class Harapartners_Service_HpcheckoutController extends Mage_Checkout_CartController{
	
	public function updateFloatingCartPostAction()
    {
        $this->_updateShoppingCart();
        $this->_redirectReferer(Mage::getUrl('*/*'));
    }
	
}