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

// app/code/local/Harapartners/HpChannelAdvisor/Helper/Data.php
class Harapartners_HpChannelAdvisor_Helper_Data extends Mage_Core_Helper_Abstract
{
    
    const CHANNELADVISOR_IMPORTED = 'channeladvisor_imported';
    const CHANNELADVISOR_SHIPMENT_EXPORT_RDY = 'channeladvisor_shiprdy';
    const CHANNELADVISOR_SHIP_ERROR = 'channeladvisor_shiperror';
    const CHANNELADVISOR_COMPLETE = 'channeladvisor_complete';
    const CHANNELADVISOR_DEFUALT = 'channeladvisor';

    public function isChannelAdvisorOrder(Mage_Sales_Model_Order $order)
    {
        $serviceType = $order->getData('service_type');
        switch ($serviceType) {
            case self::CHANNELADVISOR_DEFUALT:
            case self::CHANNELADVISOR_IMPORTED:
            case self::CHANNELADVISOR_SHIPMENT_EXPORT_RDY:
            case self::CHANNELADVISOR_COMPLETE:
                return true;
            default:
                return false;
        }
    }

    public function getSoapHeader()
    {
        $nameSpace = 'http://api.channeladvisor.com/webservices/';
        $name = 'APICredentials';
        $dataInfo = new stdClass();
        $dataInfo->DeveloperKey = Mage::getStoreConfig('hpchanneladvisor/core_config/developer_key');
        $dataInfo->Password = Mage::getStoreConfig('hpchanneladvisor/core_config/password');
        return new SoapHeader($nameSpace, $name, $dataInfo);
    
    }

    public function getZendWrapper(SoapClient $client, SoapHeader $header = null)
    {
        if ($header == null) {
            $header = $this->getSoapHeader();
        }
        $zendSoap = new Zend_Soap_Client();
        $zendSoap->setSoapClient($client);
        $zendSoap->addSoapInputHeader($header, true);
        //        $ping = new Ping();
        //        try {
        //            $pingRsponce = $zendService->Ping($ping);
        //        } catch (SoapFault $sf) {
        //            echo $sf;
        //        }
        return $zendSoap;
    }

    public function addToJson($jsonEncode, array $add)
    {
        if (! empty($jsonEncode) && ! empty($add)) {
            $decode = json_decode($jsonEncode, true);
            $decode = array_merge_recursive($decode, $add);
            return json_encode($decode);
        } else {
            return $jsonEncode;
        }
    }
}
