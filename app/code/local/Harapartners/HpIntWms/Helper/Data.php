<?php
class Harapartners_HpIntWms_Helper_Data extends Mage_Core_Helper_Abstract
{
    const CARRIER_USPS = 'usps';
    const CARRIER_FEDEX = 'fedex';
    const METHOD_FEDEX_HOME = 'home';
    const METHOD_FEDEX_GROUND = 'ground';

    public function convertShippingMethod($shippingMethod, $shippingAddress)
    {
        if (! is_array($shippingMethod)) {
            $shippingMethod = explode('_', $shippingMethod, 2);
        }
        
        // === Short Circut
        if (true || $isNeeded) {
            return $this->_mapBasicArrayToOutputFormat($shippingMethod);
        }
        
        if (! $shippingAddress) {
            return $this->_mapBasicArrayToOutputFormat($shippingMethod);
        }
        
        // === Main Logic
        $isExpidited = $this->_isMethodExpidited($shippingMethod);
        $isUsps = false;
        
        // This Matches pobox
        if (preg_match("/p\.* *o\.* *box/i", $shippingAddress->getStreet(1))) {
            $isUsps = true;
        }
        
        // These are Armed Forces codes in defualt mage install
        switch ($shippingAddress->getRegionId()) {
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
            case 11:
                $isUsps = true;
                break;
            default:
                break;
        }
        
        // These are US territories codes in defualt mage install
        switch ($shippingAddress->getRegionId()) {
            case 3:
            case 17:
            case 20:
            case 30:
            case 46:
            case 50:
            case 52:
            case 60:
                $isUsps = true;
                break;
            default:
                break;
        }
        
        // These are non-48 codes in defualt mage install
        if (! $isExpidited) {
            switch ($shippingAddress->getRegionId()) {
                case 2:
                case 21:
                    $isUsps = true;
                    break;
                default:
                    break;
            }
        }
        
        // Check if Fedex ground
        if (! $isUsps) {
            if (! $isExpidited) {
                $company = $shippingAddress->getCompany();
                if (empty($company)) {
                    $isFedexGround = false;
                } else {
                    $isFedexGround = true;
                }
            }
        }
        
        // Create the array
        if ($isUsps) {
            $shippingMethodArray = array(
                'carrier' => self::CARRIER_USPS , 
                'method' => $shippingMethod[1]
            );
        } else {
            if ($isExpidited) {
                $shippingMethodArray = array(
                    'carrier' => self::CARRIER_FEDEX , 
                    'method' => $shippingMethod[1]
                );
            } else {
                if ($isFedexGround) {
                    $shippingMethodArray = array(
                        'carrier' => self::CARRIER_FEDEX , 
                        'method' => self::METHOD_FEDEX_GROUND
                    );
                } else {
                    $shippingMethodArray = array(
                        'carrier' => self::CARRIER_FEDEX , 
                        'method' => self::METHOD_FEDEX_HOME
                    );
                }
            }
        }
        
        return $shippingMethodArray;
    }

    protected function _mapBasicArrayToOutputFormat($array)
    {
        return array(
            'carrier' => $array[0] , 
            'method' => $array[1]
        );
    }

    protected function _isMethodExpidited($shippingMethodArray)
    {
        if (isset($shippingMethodArray[1]) && $shippingMethodArray[1] != 'ground') {
            return true;
        } else {
            return false;
        }
    }

}
