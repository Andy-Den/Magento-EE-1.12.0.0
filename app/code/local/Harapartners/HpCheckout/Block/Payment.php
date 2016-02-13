<?php
class Harapartners_HpCheckout_Block_Payment extends Harapartners_HpCheckout_Block_Abstract
{
	public function getPaymentMethodFormHtml(Mage_Payment_Model_Method_Abstract $method)
    {
         return $this->getChildHtml('payment.method.' . $method->getCode());
    }
    
}
