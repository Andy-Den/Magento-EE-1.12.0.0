<?php
class SL_Signaturelink_Block_Checkout_Authenticationerror extends Mage_Core_Block_Template
{
    /**
     * @return SL_Signaturelink_Helper_Data
     */
    public function getHelper()
    {
        return Mage::helper('signaturelink');
    }

    /**
     * Get the quarantine message
     *
     * @return bool|mixed
     */
    public function getQuarantineMessage()
    {
        $helper = $this->getHelper();
        $message = $helper->getConfig('tm_config', 'tm_quarantine_fraud_message');

        return $message;
    }

}
