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
class Harapartners_HpIntWms_Model_Observer
{

    public function rmaSaveBefore($observer)
    {
        $rma = $observer->getEvent()->getRma();
        Mage::getModel('hpintwms/rma')->authorizeNewRma($rma);
    }

    public function rmaSaveAfter($observer)
    {
        $rma = $observer->getEvent()->getRma();
        if ($rma->getStatus() == Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_AUTHORIZED) {
        	/* @var $exportModel Harapartners_HpIntWms_Model_Export */
            $exportModel = Mage::getModel('hpintwms/export');
            try {
                // $exportModel will check for sent already
                $exportModel->submitRma($rma);
            } catch (Exception $e) {
                // TODO something else
                Mage::logException($e);
                throw $e;
            }
        }
    }

}
