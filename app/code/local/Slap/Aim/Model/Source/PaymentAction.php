<?php
/**
 * Magento
 */

/**
 *
 * Cybersource Payment Action Dropdown source
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Slap_Aim_Model_Source_PaymentAction
{
    public function toOptionArray()
    {
        return array(
            array('value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE, 'label' => 'Authorize Only'),
            array('value' => Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE, 'label' => 'Authorize and Capture'),
        );
    }
}
