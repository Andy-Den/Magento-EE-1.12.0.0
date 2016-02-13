<?php
class Harapartners_HpCheckout_Block_Login extends Harapartners_HpCheckout_Block_Abstract
{
    public function getPostAction()
    {
        return Mage::getUrl('customer/account/loginPost', array('_secure'=>true));
    }

    public function getUsername()
    {
        return Mage::getSingleton('customer/session')->getUsername(true);
    }
}
