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
 * @package     Harapartners\Service\Helper
 * @author      Richu Leong <r.leong@harapartners.com>
 * @copyright   Copyright (c) 2012 Harapartners Inc.
 */

// app/code/local/Harapartners/Service/Helper/Locations.php
class Harapartners_Service_Helper_Locations extends Mage_Core_Helper_Data
{

    public function isUSA($address)
    {
        if (is_object($address)) {
            $address = $address->getCountryId();
        } elseif (is_array($address)) {
            $address = $address['country_id'];
        } else {
            // Do nothing
        }
        
        switch ($address) {
            case 'US':
                $isUS = true;
                break;
            default:
                $isUS = false;
        }
        
        return $isUS;
    }

    public function isPoBox($address)
    {
        if (is_object($address)) {
            $address = $address->getStreet(1);
        } elseif (is_array($address)) {
        	$streetArray = is_array($address['street']) ? $address['street'] : explode("\n", $address['street']);
            $address = $streetArray[0];
        } else {
            // Do nothing
        }
        
        // This Matches pobox
        if (preg_match("/p\.* *o\.* *box/i", $address)) {
            return true;
        } else {
            return false;
        }
    }

    public function isUsMilitary($address)
    {
        if (is_object($address)) {
            $address = $address->getRegionId();
        } elseif (is_array($address)) {
            $address = $address['region_id'];
        } else {
            // Do nothing
        }
        
        // These are Armed Forces codes in defualt mage install
        switch ($address) {
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
            case 11:
                return true;
                break;
            default:
                return false;
        }
    }

    public function isUsTerritories($address)
    {
        if (is_object($address)) {
            $address = $address->getRegionId();
        } elseif (is_array($address)) {
            $address = $address['region_id'];
        } else {
            // Do nothing
        }
        
        // These are US territories codes in defualt mage install
        switch ($address) {
            case 3:
            case 17:
            case 20:
            case 30:
            case 46:
            case 50:
            case 52:
            case 60:
                return true;
                break;
            default:
                return false;
        }
    }

    public function isNonMainlandUs($address)
    {
        if (is_object($address)) {
            $address = $address->getRegionId();
        } elseif (is_array($address)) {
            $address = $address['region_id'];
        } else {
            // Do nothing
        }
        
        // These are non-48 codes in defualt mage install
        switch ($address) {
            case 2:
            case 21:
                return true;
                break;
            default:
                return false;
        }
    }
}