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
class Harapartners_ShoemartEdi_Helper_Data extends Mage_Core_Helper_Abstract {
	
//    const ORDER_STATUS_SENT = 'shoemartedi_sent';
//    const ORDER_STATUS_SENT_FAILED = 'shoemartedi_sent_failed';
//    
//	public function parseCarrierInfo($shippingMethod){
//		//$shippingMethod format: [carrier]_[method],
//		//the [carrier] usually lowercase only, 
//		//the [method] can be an arbitrary string (upper/lower case allowed, white space allowed)
//		
//		$carrierInfo = new Varien_Object();
//		
//		$defaultCarrierInfo = split('_', Mage::getStoreConfig('quietlogistics/account/default_carrier_info'), 2);
//		if(count($defaultCarrierInfo) == 2){
//			$carrierInfo->setCarrierId($defaultCarrierInfo[0]);
//			$carrierInfo->setServiceLevel($defaultCarrierInfo[1]);
//		}
//		
//		$shippingInfo = split('_', $shippingMethod, 2);
//		if(count($shippingInfo) == 2){
//			$carrierInfoMapping = $this->getCarrierInfoMapping();
//			if(array_key_exists(strtoupper($shippingInfo[0]), $carrierInfoMapping)
//					&& array_key_exists(strtoupper($shippingInfo[1]), $carrierInfoMapping[$shippingInfo[0]])){
//				$carrierInfo->setCarrierId($shippingInfo[0]);
//				$carrierInfo->setServiceLevel($carrierInfoMapping[$shippingInfo[0]][$shippingInfo[1]]);	
//			}
//		}
//		
//		return $carrierInfo;
//	}
//    
//    public function getCarrierInfoMapping(){
//    	$carrierInfoMapping = array(
//				'FEDEX'		=> array(
//						//Magento code	=> QL code
//						'FEDEX_1_DAY'		=> '1DAY', //Not mapped
//						'FEDEX_1_DAY_AM'	=> '1DAYAM', //Not mapped
//						'FEDEX_2_DAY'		=> '2DAY',
//						'FEDEX_3_DAY'		=> '3DAY', //Not mapped
//						'FEDEX_GROUND'		=> 'GROUND',
//						'GROUND_HOME_DELIVERY'				=> 'HOME',
//						'INTERNATIONAL_ECONOMY'				=> 'IECO',
//						'INTERNATIONAL_FIRST_OVERNIGHT'		=> 'IFO', //Not mapped
//						'INTERNATIONAL_PRIORITY'			=> 'IPRI',
//						'FEDEX_NEXT_DAY'					=> 'NDAY' //Not mapped	
//				),
//				'UPS'		=> array(
//						'NDAY'		=> 'NDAY', //Not mapped
//						'1DM'		=> '1DAYAM', 
//						'1DA'		=> '1DAY',
//						'2DM'		=> '2DAYAM', 
//						'2DA'		=> '2DAY',
//						'3DS'		=> '3DAY',
//						'STD'		=> 'CANADA',
//						'GND'		=> 'GROUND',
//						'XPR'		=> 'I', //Worldwide Express?
//						'XDM'		=> 'IP', //Worldwide Express Plus?
//						'XPD'		=> 'IE', //Worldwide Expedited?
//						'WXS'		=> 'IS' //Worldwide Express Saver?
//	
//				),
//				'USPS'		=> array(
//						'Express Mail'		=> 'EXPRESS',
//						'First-Class'		=> 'FIRST', 
//						'Express Mail International'			=> 'INTLEXPRESS',
//						'First-Class Mail International'		=> 'INTLFIRST', 
//						'Priority Mail International'			=> 'INTLPRIORITY',
//						'Parcel Post'		=> 'PARCELPOST',
//						'Priority Mail'		=> 'PRIORITY'
//				),
//				'FREIGHT'	=> array(
//						'GROUND' => 'GROUND'
//				),
//				'WILLCALL'	=> array(
//						'GROUND' => 'GROUND'
//				),
//		);
//    	return $carrierInfoMapping;
//    }
	
}