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
 * @package     Harapartners_Webservice_Model_Observer
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/HpChannelAdvisor/Model/Observer.php
class Harapartners_HpChannelAdvisor_Model_Observer extends Mage_Core_Model_Abstract
{

    public function salesOrderShipmentTrackSaveAfter(Varien_Event_Observer $observer)
    {
        if (! Mage::registry('Hara_ CA_salesOrderShipmentTrackSaveAfter')) {
            Mage::register('Hara_ CA_salesOrderShipmentTrackSaveAfter', true, true);
            $track = $observer->getEvent()->getTrack();
            $shipment = $track->getShipment();
            $order = $shipment->getOrder();
            
            $shippingMethod = $order->getData('shipping_method'); // String in format of 'carrier_method'
            if (! $shippingMethod) {
                return;
            }
            
            if (Mage::Helper('hpchanneladvisor/data')->isChannelAdvisorOrder($order)) {
                $order->setData('service_type', Harapartners_HpChannelAdvisor_Helper_Data::CHANNELADVISOR_SHIPMENT_EXPORT_RDY);
                $shipment->setData('service_flag', 0);
                Mage::getModel('core/resource_transaction')->addObject($order)->addObject($shipment)->save();
            }
        }
    }

    public function creditMemoRefund(Varien_Event_Observer $observer)
    {
        $creditMemo = $observer->getEvent()->getCreditmemo();
        if (Mage::Helper('hpchanneladvisor/data')->isChannelAdvisorOrder($creditMemo->getOrder())) {
            /* @var $exportModel Harapartners_HpChannelAdvisor_Model_Export */
            $exportModel = Mage::getModel('hpchanneladvisor/export');
            $result = $exportModel->postCreditMemo($creditMemo);
        }
    }
}
