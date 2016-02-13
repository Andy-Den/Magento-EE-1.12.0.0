<?php
class Harapartners_HpCheckout_Block_Shipping extends Harapartners_HpCheckout_Block_Abstract
{
    protected $_address = null;

    public function getAddress()
    {
        if (is_null($this->_address)) {
            $this->_address = $this->getQuote()->getShippingAddress();
        }

        return $this->_address;
    }
    
    public function getFirstname()
    {
        $firstname = $this->getAddress()->getFirstname();
        if (empty($firstname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getFirstname();
        }
        return $firstname;
    }

    public function getLastname()
    {
        $lastname = $this->getAddress()->getLastname();
        if (empty($lastname) && $this->getQuote()->getCustomer()) {
            return $this->getQuote()->getCustomer()->getLastname();
        }
        return $lastname;
    }

    public function isShow()
    {
        return !$this->getQuote()->isVirtual();
    }
}
