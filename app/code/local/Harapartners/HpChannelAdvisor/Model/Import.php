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
 * @package     Harapartners_HpChannelAdvisor_Model_Import
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/HpChannelAdvisor/Model/Import.php
class Harapartners_HpChannelAdvisor_Model_Import extends Mage_Core_Model_Abstract
{
    protected $_orderProcess = null;
    
    protected $_result = array();
    protected $_resultMsg = '';

    /**
     * @return array
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * @return string
     */
    public function getResultMsg()
    {
        return $this->_resultMsg;
    }

    public function _construct()
    {
        include_once dirname(__FILE__) . DS . '..' . DS . 'lib' . DS . 'output' . DS . 'InventoryService.php';
        include_once dirname(__FILE__) . DS . '..' . DS . 'lib' . DS . 'output' . DS . 'OrderService.php';
        include_once dirname(__FILE__) . DS . '..' . DS . 'lib' . DS . 'output' . DS . 'ShippingService.php';
    }

    public function importNewCompleteOrders()
    {
        $results = $this->_getOrderProcess()->processAllOrdersFromCa();
        
        // Result Msg
        $msg = 'Syncing Orders: ' . '<br>' . PHP_EOL;
        foreach ($results as $cId_oId => $result) {
            list ($clientId, $orderId) = explode('_', $cId_oId);
            $msg .= "CA Order ID: {$orderId}. Client Order ID: {$clientId} ";
            $msg .= "Result: {$result['result']}. Reason: {$result['reason']}" . '<br>' . PHP_EOL;
        }
        $this->_result = $results;
        $this->_resultMsg = $msg;
    }

    /**
     * Gets the order Processing Model
     *
     * @return Harapartners_HpChannelAdvisor_Model_Import_Order
     */
    protected function _getOrderProcess()
    {
        if (! $this->_orderProcess) {
            $this->_orderProcess = Mage::getModel('hpchanneladvisor/import_order');
        }
        return $this->_orderProcess;
    }

    public function authAccount()
    {
        include_once dirname(__FILE__) . DS . '..' . DS . 'lib' . DS . 'output' . DS . 'AdminService.php';
        
        /* @var $zendService AdminService */
        $zendService = Mage::helper('hpchanneladvisor')->getZendWrapper(new AdminService(), null);
        
        $requestAcc = new RequestAccess();
        $requestAcc->localID = Mage::getStoreConfig('hpchanneladvisor/core_config/profile_id');
        
        $zendService->RequestAccess($requestAcc);
    }

    public function getAuthKey()
    {
        include_once dirname(__FILE__) . DS . '..' . DS . 'lib' . DS . 'output' . DS . 'AdminService.php';
        
        /* @var $zendService AdminService */
        $zendService = Mage::helper('hpchanneladvisor')->getZendWrapper(new AdminService(), null);
        
        $requestAcc = new GetAuthorizationList();
        $requestAcc->localID = Mage::getStoreConfig('hpchanneladvisor/core_config/profile_id');
        
        $result = $zendService->GetAuthorizationList($requestAcc)->GetAuthorizationListResult;
        /* @var $resultResponce1 AuthorizationResponse */
        $resultResponce1 = $result->ResultData->AuthorizationResponse;
        $accountId = $resultResponce1->AccountID;
        
        // Set accountId -> unset profileId
        $coreConfig = new Mage_Core_Model_Config();
        $coreConfig->saveConfig('hpchanneladvisor/core_config/account_id', $accountId, 'default', 0);
        $coreConfig->saveConfig('hpchanneladvisor/core_config/profile_id', "", 'default', 0);
        
        return true;
    }
}
