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
class Harapartners_HpIntWms_Model_Rma
{

    /**
     * This is for rma_save_before
     *
     * @param unknown_type $rma
     */
    public function authorizeNewRma($rma)
    {
        // New RMA ...
        if (! $rma->getId()) {
            $rmaItems = $rma->getItemsCollection();
            foreach ($rma->getItemsCollection() as $rmaItem) {
                $qtyRequested = $rmaItem->getData('qty_requested');
                $rmaItem->setData('qty_authorized', $qtyRequested);
                $rmaItem->setStatus(Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_AUTHORIZED);
            }
            $rma->setStatus(Enterprise_Rma_Model_Item_Attribute_Source_Status::STATE_AUTHORIZED);
            // TODO ?? $rma->setIsSendAuthEmail(1); ??
        }
    }
}
