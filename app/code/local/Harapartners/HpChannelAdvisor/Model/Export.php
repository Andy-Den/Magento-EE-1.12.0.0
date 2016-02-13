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
 * @package     Harapartners_HpChannelAdvisor_Model_Export
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/HpChannelAdvisor/Model/Export.php
class Harapartners_HpChannelAdvisor_Model_Export extends Mage_Core_Model_Abstract
{
    
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

    public function syncAllTrackings()
    {
        // TODO HERE TODO
        throw new Exception('NI');
    }

    public function fullProductSync()
    {
        //throw new Exception('Not Fully Implimented Yet');
        /* @var $inventoryModel Harapartners_HpChannelAdvisor_Model_Export_Inventory */
        $inventoryModel = Mage::getModel('hpchanneladvisor/export_inventory');
        $result = $inventoryModel->syncAllItems();
    }

    public function fullInventorySync($date)
    {
        /* @var $inventoryModel Harapartners_HpChannelAdvisor_Model_Export_Inventory */
        $inventoryModel = Mage::getModel('hpchanneladvisor/export_inventory');
        $result = $inventoryModel->syncAllItemsQtyPrice($date);
    }

    public function postCreditMemo($creditMemo)
    {
        /* @var $cmModel Harapartners_HpChannelAdvisor_Model_Export_Creditmemo */
        $cmModel = Mage::getModel('hpchanneladvisor/export_creditmemo');
        return $cmModel->pushCreditMemo($creditMemo);
    }

    public function postTrackingnumbers()
    {
        /* @var $trackingModel Harapartners_HpChannelAdvisor_Model_Export_Tracking */
        $trackingModel = Mage::getModel('hpchanneladvisor/export_tracking');
        $trackingModel->pushAllTrackings();
    }
}
