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
class Harapartners_HpOrderWorkflow_Model_Observer
{
    public function processOrderAfterPayment($observer)
    {
        $order = $observer->getEvent()->getInvoice()->getOrder();
        $model = Mage::getModel('hporderworkflow/order');
        try {
            $model->processOrder($order);
        } catch (Exception $e) {
            Mage::log($e->getTraceAsString(), null, 'Order_Workflow_Order_Error.log', true);
        }
    }
}
