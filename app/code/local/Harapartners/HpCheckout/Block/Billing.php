<?php
class Harapartners_HpCheckout_Block_Billing extends Harapartners_HpCheckout_Block_Abstract
{
    protected $_address;

    public function getCountries()
    {
        return Mage::getResourceModel('directory/country_collection')->loadByStore();
    }

    public function getAddress()
    {
        if (is_null($this->_address)) {
            $this->_address = $this->getQuote()->getBillingAddress();
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

    public function canShip()
    {
        return !$this->getQuote()->isVirtual();
    }

}
