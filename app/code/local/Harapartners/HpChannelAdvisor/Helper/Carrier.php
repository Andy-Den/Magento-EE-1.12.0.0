<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 * @package     Harapartners_HpChannelAdvisor_Helper
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/HpChannelAdvisor/Helper/Carrier.php
class Harapartners_HpChannelAdvisor_Helper_Carrier extends Mage_Core_Helper_Abstract
{
    const ORDER_TYPE_EBAY = 'ebay';
    const ORDER_TYPE_AMAZON = 'amazon';
    
    const ADDRESS_TYPE_DEFAULT_US = 'default_us';
    const ADDRESS_TYPE_COMMERCIAL = 'commercial';
    const ADDRESS_TYPE_POBOX_US = 'po_box_us';
    const ADDRESS_TYPE_MILITARY = 'military';
    const ADDRESS_TYPE_US_TERITORIES = 'teritories_us';
    const ADDRESS_TYPE_POBOX_OTHER = 'po_box_other';
    const ADDRESS_TYPE_DEFAULT_OTHER = 'default_other';

    public function getMageShippingMethod($shippingAddress, $carrier, $method)
    {
        $orderType = $this->_getOrderType($carrier);
        $addressType = $this->_getAddressType($shippingAddress);
        
        switch ($orderType) {
            case self::ORDER_TYPE_EBAY:
                $shippingMethod = $this->_getShippingMethodFromEbay($carrier, $method, $addressType);
                break;
            case self::ORDER_TYPE_AMAZON:
                $shippingMethod = $this->_getShippingMethodFromAmazon($carrier, $method, $addressType);
                break;
            default:
                $shippingMethod = "{$carrier}_{$method}";
        }
        
        // Be rdy to notify CSR
        if ($shippingMethod == 'CSR_CSR') {
            $shippingMethod = "CSR_{$carrier}_{$method}";
        }
        
        return $shippingMethod;
    }

    protected function _getShippingMethodFromEbay($carrier, $method, $addressType)
    {
        $methods = $this->_getEbayMethodArray();
        $ebayType = (strtolower($carrier) == 'ebay') ? 'ebay' : 'fedex';
        
        if (isset($methods[$ebayType][$method][$addressType])) {
            $shippingMethod = $methods[$ebayType][$method][$addressType];
        } else {
            $shippingMethod = 'CSR_CSR';
        }
        
        return $shippingMethod;
    
    }

    protected function _getEbayMethodArray()
    {
        return array(
            'ebay' => array(
                'Standard' => array(
                    self::ADDRESS_TYPE_DEFAULT_US => 'FEDEX_GROUND_HOME_DELIVERY' , 
                    self::ADDRESS_TYPE_COMMERCIAL => 'FEDEX_GROUND' , 
                    self::ADDRESS_TYPE_POBOX_US => 'USPS_Priority Mail 3-Day' , 
                    self::ADDRESS_TYPE_MILITARY => 'USPS_Priority Mail 3-Day' , 
                    self::ADDRESS_TYPE_US_TERITORIES => 'USPS_Priority Mail 3-Day'
                )
            ) , 
            'fedex' => array(
                'Express Saver' => array(
                    self::ADDRESS_TYPE_DEFAULT_US => 'FEDEX_EXPRESS_SAVER' , 
                    self::ADDRESS_TYPE_COMMERCIAL => 'FEDEX_EXPRESS_SAVER' , 
                    self::ADDRESS_TYPE_POBOX_US => 'USPS_Priority Mail 3-Day' , 
                    self::ADDRESS_TYPE_MILITARY => 'USPS_Priority Mail 3-Day' , 
                    self::ADDRESS_TYPE_US_TERITORIES => 'USPS_Priority Mail 3-Day'
                ) , 
                '2 Day' => array(
                    self::ADDRESS_TYPE_DEFAULT_US => 'FEDEX_2_DAY' , 
                    self::ADDRESS_TYPE_COMMERCIAL => 'FEDEX_2_DAY' , 
                    self::ADDRESS_TYPE_POBOX_US => 'USPS_Priority Mail 3-Day' , 
                    self::ADDRESS_TYPE_MILITARY => 'FEDEX_2_DAY' , 
                    self::ADDRESS_TYPE_US_TERITORIES => 'FEDEX_2_DAY'
                ) , 
                'Standard Overnight' => array(
                    self::ADDRESS_TYPE_DEFAULT_US => 'FEDEX_STANDARD_OVERNIGHT' , 
                    self::ADDRESS_TYPE_COMMERCIAL => 'FEDEX_STANDARD_OVERNIGHT' , 
                    self::ADDRESS_TYPE_POBOX_US => 'CSR_CSR' , 
                    self::ADDRESS_TYPE_MILITARY => 'FEDEX_STANDARD_OVERNIGHT' , 
                    self::ADDRESS_TYPE_US_TERITORIES => 'FEDEX_STANDARD_OVERNIGHT'
                ) , 
                'International Economy' => array(
                    self::ADDRESS_TYPE_DEFAULT_OTHER => 'FEDEX_INTERNATIONAL_ECONOMY' , 
                    self::ADDRESS_TYPE_POBOX_OTHER => 'USPS_Priority Mail International'
                )
            )
        );
    }

    public function _getShippingMethodFromAmazon($carrier, $method, $addressType)
    {
        
        $methods = $this->_getAmazonMethodArray();
        if (isset($methods[$carrier][$method][$addressType])) {
            $shippingMethod = $methods[$carrier][$method][$addressType];
        } else {
            $shippingMethod = 'CSR_CSR';
        }
        return $shippingMethod;
    
    }

    protected function _getAmazonMethodArray()
    {
        return array(
            'Amazon Merchants@' => array(
                'Standard' => array(
                    self::ADDRESS_TYPE_DEFAULT_US => 'FEDEX_GROUND_HOME_DELIVERY' , 
                    self::ADDRESS_TYPE_COMMERCIAL => 'FEDEX_GROUND' , 
                    self::ADDRESS_TYPE_POBOX_US => 'USPS_Priority Mail 3-Day' , 
                    self::ADDRESS_TYPE_MILITARY => 'USPS_Priority Mail 3-Day' , 
                    self::ADDRESS_TYPE_US_TERITORIES => 'USPS_Priority Mail 3-Day' , 
                    self::ADDRESS_TYPE_DEFAULT_OTHER => 'FEDEX_INTERNATIONAL_ECONOMY' , 
                    self::ADDRESS_TYPE_POBOX_OTHER => 'USPS_Priority Mail International'
                ) , 
                'Expedited' => array(
                    self::ADDRESS_TYPE_DEFAULT_US => 'FEDEX_EXPRESS_SAVER' , 
                    self::ADDRESS_TYPE_POBOX_US => 'USPS_Priority Mail 3-Day' , 
                    self::ADDRESS_TYPE_MILITARY => 'USPS_Priority Mail 3-Day' , 
                    self::ADDRESS_TYPE_US_TERITORIES => 'USPS_Priority Mail 3-Day'
                ) , 
                'SecondDay' => array(
                    self::ADDRESS_TYPE_DEFAULT_US => 'FEDEX_2_DAY' , 
                    self::ADDRESS_TYPE_COMMERCIAL => 'FEDEX_2_DAY' , 
                    self::ADDRESS_TYPE_POBOX_US => 'USPS_Priority Mail 3-Day' , 
                    self::ADDRESS_TYPE_MILITARY => 'FEDEX_2_DAY' , 
                    self::ADDRESS_TYPE_US_TERITORIES => 'FEDEX_2_DAY'
                ) , 
                'NextDay' => array(
                    self::ADDRESS_TYPE_DEFAULT_US => 'FEDEX_STANDARD_OVERNIGHT' , 
                    self::ADDRESS_TYPE_POBOX_US => 'CSR_CSR'
                )
            )
        );
    }

    protected function _getOrderType($carrier)
    {
        $orderType = '';
        switch ($carrier) {
            case 'eBay':
            case 'FedEx':
                $orderType = self::ORDER_TYPE_EBAY;
                break;
            case 'Amazon Merchants@':
                $orderType = self::ORDER_TYPE_AMAZON;
                break;
            default:
                $orderType = '';
        }
        
        return $orderType;
    }

    protected function _getAddressType($address)
    {
        /* @var $serviceAddressHelper Harapartners_Service_Helper_Locations */
        $serviceAddressHelper = Mage::helper('service/locations');
        if ($serviceAddressHelper->isUSA($address)) {
            $isUS = true;
        } else {
            $isUS = false;
        }
        
        if ($isUS) {
            if ($serviceAddressHelper->isPoBox($address)) {
                return self::ADDRESS_TYPE_POBOX_US;
            } elseif ($serviceAddressHelper->isUsMilitary($address)) {
                return self::ADDRESS_TYPE_MILITARY;
            } elseif ($serviceAddressHelper->isUsTerritories($address)) {
                return self::ADDRESS_TYPE_US_TERITORIES;
            } elseif ($serviceAddressHelper->isNonMainlandUs($address)) {
                return self::ADDRESS_TYPE_US_TERITORIES;
            } else {
                return self::ADDRESS_TYPE_DEFAULT_US;
            }
        } else {
            if ($serviceAddressHelper->isPoBox($address)) {
                return self::ADDRESS_TYPE_POBOX_OTHER;
            } else {
                return self::ADDRESS_TYPE_DEFAULT_OTHER;
            }
        }
    }
}
