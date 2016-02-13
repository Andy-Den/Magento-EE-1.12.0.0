<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */
class Harapartners_QuietLogistics_Model_Source_Carrierinfo{
	
    public function toOptionArray(){
    	$optionArray = array();
    	$optionArray[] = array('value' => '', 'label' => '');
    	$dataHelper = Mage::helper('quietlogistics');
    	foreach($dataHelper->getCarrierInfoMapping() as $carrierId => $carrierInfo){
    		foreach($carrierInfo as $carrierServiceLevel){
    			$optionArray[] = array('value' => $carrierId . '_' . $carrierServiceLevel, 'label' => $carrierId . '_' . $carrierServiceLevel);
    		}
    	}
        return $optionArray;
    }
    
}