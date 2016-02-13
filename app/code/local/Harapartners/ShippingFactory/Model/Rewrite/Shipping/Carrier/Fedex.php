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
class Harapartners_ShippingFactory_Model_Rewrite_Shipping_Carrier_Fedex extends Mage_Usa_Model_Shipping_Carrier_Fedex {
	
	public function getNoGroundShippingRegionInfo(){
		return array(
				2 => 'AK', //Alaska
				3 => 'AS', //American Samoa
				6 => 'AF', //Armed Forces Africa
				7 => 'AA', //Armed Forces Americas
				8 => 'AC', //Armed Forces Canada
				9 => 'AE', //Armed Forces Europe
				10 => 'AM', //Armed Forces Middle East
				11 => 'AP', //Armed Forces Pacific
				17 => 'FM', //Federated States Of Micronesia
				20 => 'GU', //Guam
				21 => 'HI', //Hawaii
				30 => 'MH', //Marshall Islands
				50 => 'PW', //Palau
				52 => 'PR', //Puerto Rico
				60 => 'VI', //Virgin Islands
		);
	}
	
	public function getConfigData($field){
        $configData = parent::getConfigData($field);
        if($field == 'allowed_methods'){
        	$allowedMethods = explode(',', $configData);
        	if(in_array($this->_rawRequest->getDestRegionCode(), $this->getNoGroundShippingRegionInfo())){
        		$filteredMethods = array();
        		$groundMethods = array('FEDEX_GROUND', 'GROUND_HOME_DELIVERY');
        		foreach($allowedMethods as $method){
        			if(in_array($method, $groundMethods)){
        				continue;
        			}
        			$filteredMethods[] = $method;
        		}
        		$configData = implode(',', $filteredMethods);
        	}
        }
        return $configData;
    }
	
    public function collectRates(Mage_Shipping_Model_Rate_Request $request) {
    	$dataHelper = Mage::helper('shippingfactory');
    	foreach($request->getAllItems() as $requestItem){
    		//Only process the first item
    		$quote = $requestItem->getQuote();
    		break;
    	}
    	if(count($dataHelper->getNoInternationalItemArray($quote))){
    		return Mage::getModel('shipping/rate_result');
    	}
    	//No PO BOX with FEDEX
    	if($dataHelper->isShippingAddressPoBox($request->getDestStreet())){
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
    		if($shippingRate->getCost() > 0.0 && $shippingRate->getPrice() > 0.0){
    			$dataHelper->modifyResultMethodRate($shippingRate, $requestQty, $requestDestCountryId);
    		}
    	}
    	
//    	//For Fedex, certain Army base address will not calculate properly and will return 0.0, exclude such results
//    	$shippingRateResult->reset();
//    	foreach($tempRates as $shippingRate){
//    		$shippingRateResult->append($shippingRate);
//    	}
    	
    	Mage::unregister('fedex_shipping_rate_result');
    	Mage::register('fedex_shipping_rate_result', $shippingRateResult);
		return $shippingRateResult;
    }
    
    public function setRequest(Mage_Shipping_Model_Rate_Request $request){
    	//The rate is very address sensitive, street address is needed here
    	$origStreetLines = array(
    			Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS1, $request->getStore()),
    			Mage::getStoreConfig(Mage_Shipping_Model_Shipping::XML_PATH_STORE_ADDRESS2, $request->getStore())
    	);
    	$request->setStreet(trim(implode("\n", $origStreetLines)));
    	$region = Mage::getModel('directory/region')->load($request->getRegionId());
    	$request->setRegionCode($region->getCode());
    	
    	parent::setRequest($request);
    	$this->_rawRequest->setOrigStreet($request->getStreet());
    	$this->_rawRequest->setOrigCity($request->getCity());
    	$this->_rawRequest->setOrigRegionCode($request->getRegionCode());
    	$this->_rawRequest->setDestStreet($request->getDestStreet());
    	$this->_rawRequest->setDestCity($request->getDestCity());
    	$this->_rawRequest->setDestRegionCode($request->getDestRegionCode());
    	return $this;
    }
    
	protected function _formRateRequest($purpose) {
		$r = $this->_rawRequest;
        $ratesRequest = parent::_formRateRequest($purpose);
        $ratesRequest['RequestedShipment']['Shipper']['Address']['StreetLines'] = array($r->getOrigStreet());
        $ratesRequest['RequestedShipment']['Shipper']['Address']['City'] = $r->getOrigCity();
        $ratesRequest['RequestedShipment']['Shipper']['Address']['StateOrProvinceCode'] = $r->getOrigRegionCode();
        $ratesRequest['RequestedShipment']['Recipient']['Address']['StreetLines'] = array($r->getDestStreet());
        $ratesRequest['RequestedShipment']['Recipient']['Address']['City'] = $r->getDestCity();
        $ratesRequest['RequestedShipment']['Recipient']['Address']['StateOrProvinceCode'] = $r->getDestRegionCode();
        return $ratesRequest;
    }
   
}