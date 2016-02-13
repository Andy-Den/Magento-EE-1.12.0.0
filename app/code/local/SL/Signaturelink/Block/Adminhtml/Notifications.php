<?php
/**
 * SL_Signaturelink_Block_Adminhtml_Notifications
 * Notifications block. Displays a note at the top of adminhtml pages.
 *
 * @category    Signaturelink
 * @package     SL_Signaturelink
 */
class SL_Signaturelink_Block_Adminhtml_Notifications extends Mage_Adminhtml_Block_Template
{
    /**
     * Get x management url
     *
     * @return string
     */
    public function getManageUrl()
    {
        return $this->getUrl('adminhtml/system_config/edit', array('section' => 'signaturelink'));
    }

    /**
     * Check to see if config options are set
     * @return bool
     */
    public function isRequiredSettingsNotification()
    {		
		$test = (strlen(trim(Mage::helper('signaturelink')->getConfig('sl_config', 'te_user'))) == 0 || strlen(trim(Mage::helper('signaturelink')->getConfig('sl_config', 'te_pass'))) == 0);
		return $test;
    }
}
