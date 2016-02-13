<?php

class SL_Signaturelink_Block_Adminhtml_Whitelist_Edit extends Mage_Core_Block_Template{

    /**
     * Sets the form action urls for the page.
     */
    public function _construct()
    {
        parent::_construct();
        $this->setFormAction(Mage::helper('adminhtml')->getUrl('*/signaturelink/whitelistProcessEdit'));
    }


    /**
     * Retrieve Session Form Key
     *
     * @return string
     */
    public function getFormKey()
    {
        return Mage::getSingleton('core/session')->getFormKey();
    }

}

