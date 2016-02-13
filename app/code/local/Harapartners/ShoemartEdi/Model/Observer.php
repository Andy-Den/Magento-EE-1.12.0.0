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
class Harapartners_ShoemartEdi_Model_Observer
{

    public function submitOrderAfterPayment($observer)
    {
        $order = $observer->getEvent()->getInvoice()->getOrder();
        $edi = Mage::getModel('shoemartedi/export');
        try {
            $edi->submitOrder($order, null);
        } catch (Exception $e) {
            Mage::log($e->getTraceAsString(), null, 'shoemartedi_error.log', true);
        }
    }


}
