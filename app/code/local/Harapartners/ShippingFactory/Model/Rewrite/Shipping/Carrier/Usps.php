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
class Harapartners_ShippingFactory_Model_Rewrite_Shipping_Carrier_Usps extends Mage_Usa_Model_Shipping_Carrier_Usps {
   
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
    	//Check if fedex method is available
    	$dataHelper = Mage::helper('shippingfactory');
    	foreach($request->getAllItems() as $requestItem){
    		//Only process the first item
    		$quote = $requestItem->getQuote();
    		break;
    	}
    	if(count($dataHelper->getNoInternationalItemArray($quote))){
    		return Mage::getModel('shipping/rate_result');
    	}
    	if($dataHelper->isUspsExcludedByFedexMethods()){
    		return Mage::getModel('shipping/rate_result');
    	}
    	
    	$shippingRateResult = parent::collectRates($request);
    	$requestDestCountryId = $request->getDestCountryId();
    	
    	$requestQty = 0;
    	foreach($request->getAllItems() as $requestItem){
    		//Ignore children products
    		if($requestItem->getParentItemId()){
    			continue;
    		}
    		$requestQty += $requestItem->getQty();
    	}    	
    	
    	foreach($shippingRateResult->getAllRates() as $shippingRate){
    		$dataHelper->modifyResultMethodRate($shippingRate, $requestQty, $requestDestCountryId);
    	}
    	
		return $shippingRateResult;
    }

}