<?php
/**
 * Magento
 *
 */

class Slap_Aim_Model_Source_Cctype extends Mage_Payment_Model_Source_Cctype
{
    public function getAllowedTypes()
    {
        return array('VI', 'MC', 'AE', 'DI', 'OT');
    }
}
