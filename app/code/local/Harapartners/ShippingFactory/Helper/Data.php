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
class Harapartners_ShippingFactory_Helper_Data extends Mage_Core_Helper_Abstract {
	
	const PRICING_METHOD_CALCULATE = 'C';
	const PRICING_METHOD_FLATRATE = 'F';
	const DOMESTIC_COUNTRY_ID = "US";
	
	protected $_domesticShippingRules;
	protected $_internationalShippingRules;
	
	protected $_noInternationalItemArray;
	protected $_uspsExcludedByFedexMethodArray = array('FEDEX_GROUND', 'GROUND_HOME_DELIVERY', 'INTERNATIONAL_ECONOMY');
	
	public function getNoInternationalItemArray($quote = null){
		if($this->_noInternationalItemArray === null){
			$this->_noInternationalItemArray = array();
			
			if(!$quote){
				if(Mage::app()->getStore()->isAdmin()){
	    			//Admin logic
	    			$quote = Mage::getSingleton('adminhtml/session_quote');
				}
	    	}else{
	    		//Frontend checkout, Check if User is Logged In
	    		$quote = Mage::getSingleton('checkout/session')->getQuote();
	    	}
			
			//Only validate items that would be shipped
			if(!$quote || $quote->isVirtual()){
				return $this->_noInternationalItemArray;
			}
			
			//Unknown shipping
			$shippingAddress = $quote->getShippingAddress();
			if(!$shippingAddress || !$shippingAddress->getCountryId() 
					|| $shippingAddress->getCountryId() == Harapartners_ShippingFactory_Helper_Data::DOMESTIC_COUNTRY_ID){
				return $this->_noInternationalItemArray;
			}
	
			//Get all item that cannot be shipped internationally
			foreach($quote->getAllItems() as $quoteItem){
				//Ignore children items
				if(!!$quoteItem->getParentItemId()){
					continue;
				}
				if($quoteItem->getProduct() && $quoteItem->getProduct()->getVendorCode()){
					$vendor = Mage::getModel('vendoroptions/vendoroptions_configure')->loadByCode($quoteItem->getProduct()->getVendorCode());
					if(!$vendor->getData('ship_intl')){
						$this->_noInternationalItemArray[] = $quoteItem;
					}
				}
			}
		}
		return $this->_noInternationalItemArray;
	}
	
	public function isShippingAddressPoBox($addressStreet){
		return preg_match("/^\s*((P(OST)?.?\s*(O(FF(ICE)?)?)?.?\s+(B(IN|OX))?)|B(IN|OX))/i", $addressStreet);
	}
	
	public function isUspsExcludedByFedexMethods(){
		$fedexShippingRateResult = Mage::registry('fedex_shipping_rate_result');
		if(!!$fedexShippingRateResult){
			foreach($fedexShippingRateResult->getAllRates() as $shippingRate){
	    		$method = $shippingRate->getData('method');
	    		if(in_array($method, $this->_uspsExcludedByFedexMethodArray)){
	    			return true;
	    		}
	    	}
		}
		return false;
	}
	
	public function modifyResultMethodRate($shippingRate, $requestQty, $requestDestCountryId){
		if($requestDestCountryId == self::DOMESTIC_COUNTRY_ID){
			$method = $shippingRate->getData('method');
			foreach($this->getDomesticShippingRules() as $shippingRule){
				if(strcasecmp(trim($method), trim($shippingRule['method'])) == 0){
					switch(trim($shippingRule['pricing_method'])){
						case self::PRICING_METHOD_CALCULATE:
							if(is_numeric($shippingRule['calculate_percent_modifier'])){
								$calculateRateFactor = 1.0 + $shippingRule['calculate_percent_modifier'] / 100.0;
								$shippingRate->setCost($shippingRate->getCost() * $calculateRateFactor);
								$shippingRate->setPrice($shippingRate->getPrice() * $calculateRateFactor);
							}
							break;
						case self::PRICING_METHOD_FLATRATE:
							if(is_numeric($shippingRule['flatrate_first_item_rate']) && is_numeric($shippingRule['flatrate_additional_item_rate'])){
								$flatRate = $shippingRule['flatrate_first_item_rate'] + ($requestQty - 1) * $shippingRule['flatrate_additional_item_rate'];
								$shippingRate->setCost($flatRate);
								$shippingRate->setPrice($flatRate);
							}
							break;
						default:
							break;
					}
					break;
				}
			}
		}else{
			foreach($this->getInternationalShippingRules() as $shippingRule){
				if(strcasecmp(trim($requestDestCountryId), trim($shippingRule['dest_country_id'])) == 0){
					switch(trim($shippingRule['pricing_method'])){
						case self::PRICING_METHOD_CALCULATE:
							if(is_numeric($shippingRule['calculate_percent_modifier'])){
								$calculateRateFactor = 1.0 + $shippingRule['calculate_percent_modifier'] / 100.0;
								$shippingRate->setCost($shippingRate->getCost() * $calculateRateFactor);
								$shippingRate->setPrice($shippingRate->getPrice() * $calculateRateFactor);
							}
							break;
						case self::PRICING_METHOD_FLATRATE:
							if(is_numeric($shippingRule['flatrate_first_item_rate']) && is_numeric($shippingRule['flatrate_additional_item_rate'])){
								$flatRate = $shippingRule['flatrate_first_item_rate'] + ($requestQty - 1) * $shippingRule['flatrate_additional_item_rate'];
								$shippingRate->setCost($flatRate);
								$shippingRate->setPrice($flatRate);
							}
							break;
						default:
							break;
					}
					break;
				}
			}
		}
		return $shippingRate;
	}
	
	public function getDomesticShippingRules(){
		if($this->_domesticShippingRules === null){
			$this->_domesticShippingRules = array();
			$configDomesticShippingRules = explode("\n", Mage::getStoreConfig('carriers/special_rules/domestic_rules'));
			foreach($configDomesticShippingRules as $rowCount => $rowData){
				if($rowCount == 0){
					$headerRow = explode(",", $configDomesticShippingRules[$rowCount]);
					continue;
				}
				$contentRow = explode(",", $configDomesticShippingRules[$rowCount]);
				$contentRowMapped = array();
				foreach($headerRow as $columnCount => $columnName){
					$contentRowMapped[trim($columnName)] = trim($contentRow[$columnCount]);
				}
				$this->_domesticShippingRules[] = $contentRowMapped;
			}
		}
		return $this->_domesticShippingRules;
	}
	
	public function getInternationalShippingRules(){
		if($this->_internationalShippingRules === null){
			$this->_internationalShippingRules = array();
			$configInternationalShippingRules = explode("\n", Mage::getStoreConfig('carriers/special_rules/international_rules'));
			foreach($configInternationalShippingRules as $rowCount => $rowData){
				if($rowCount == 0){
					$headerRow = explode(",", $configInternationalShippingRules[$rowCount]);
					continue;
				}
				$contentRow = explode(",", $configInternationalShippingRules[$rowCount]);
				$contentRowMapped = array();
				foreach($headerRow as $columnCount => $columnName){
					$contentRowMapped[trim($columnName)] = trim($contentRow[$columnCount]);
				}
				$this->_internationalShippingRules[] = $contentRowMapped;
			}
		}
		return $this->_internationalShippingRules;
	}
	
}