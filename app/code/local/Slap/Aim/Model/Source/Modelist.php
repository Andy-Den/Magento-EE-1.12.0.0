<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 */

class Slap_Aim_Model_Source_Modelist
{
    /**
     * Prepare payment mode list
     *
     * @return array
     */
    public function toOptionArray()
    {
		$options = array();
		$options[] = array('value'=>'0', 'label'=>'Test');
		$options[] = array('value'=>'1', 'label'=>'Live');
		return $options;
        
    }
}
