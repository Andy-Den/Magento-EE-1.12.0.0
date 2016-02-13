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

// app/code/local/Harapartners/HpChannelAdvisor/Model/Export/Creditmemo.php
/**
 * Order Import
 * 
 * @todo Add in logging a log file.
 *
 */
class Harapartners_HpChannelAdvisor_Model_Export_Creditmemo extends Mage_Core_Model_Abstract
{

    public function pushCreditMemo($creditMemo)
    {
        $success = false;
        $errorSku = array();
        
        /* @var $zendService OrderService */
        $zendService = Mage::helper('hpchanneladvisor')->getZendWrapper(new OrderService(), null);
        $submitOrderRefund = $this->_getSubmitOrderRefund($creditMemo);
        
        try {
            /* @var $responce SubmitOrderRefundResponse */
            $responce = $zendService->SubmitOrderRefund($submitOrderRefund);
        } catch (SoapFault $sf) {
            Mage::log("CA Error in CreditMemo:{$creditMemo->getIncrementIs()} Request Fail: {$sf->getMessage()}", null, 'CA_Creditmemo_error.log');
            return false;
        }
        
        $resultData = $responce->SubmitOrderRefundResult->ResultData;
        if ($resultData->MessageCode == 0) {
            $success = true;
            foreach ($resultData->RefundItems as $refundItem) {
                /* @var $refundItem RefundItem */
                if ($refundItem->RefundRequested != true) {
                    $success = false;
                    $errorSku[] = $refundItem->SKU;
                    Mage::log("CA Error in CreditMemo:{$creditMemo->getIncrementIs()} SKU:{$refundItem->SKU}", null, 'CA_Creditmemo_error.log');
                }
            }
        } else {
            $success = false;
        }
        
        return $success;
    
    }

    protected function _getSubmitOrderRefund($creditMemo)
    {
        $order = $creditMemo->getOrder();
        
        $submitOrderRefund = new SubmitOrderRefund();
        $submitOrderRefund->accountID = Mage::getStoreConfig('hpchanneladvisor/core_config/account_id');
        $submitOrderRefund->request = new RefundOrderRequest();
        $submitOrderRefund->request->ClientOrderIdentifier = $order->getData('service_transactionid');
        $submitOrderRefund->request->OrderID = 0; // Bug if left null -> CA internal parsing error
        // Placeholder for possible check for Full vs Partial Refund.
        if (true) {
            $submitOrderRefund->request->RefundItems = $this->_getRefundItems($creditMemo);
            $submitOrderRefund->request->Amount = 0;
        }
        //// $submitOrderRefund->request->AdjustmentReason = 'CustomerReturnedItem';
        

        return $submitOrderRefund;
    }

    protected function _getRefundItems($creditMemo)
    {
        $refundItems = array();
        foreach ($creditMemo->getAllItems() as $creditMemoItem) {
            $refundItem = new RefundItem();
            $refundItem->SKU = $creditMemoItem->getSku();
            $refundItem->Amount = $creditMemoItem->getRowTotal(); // TODO Include Tax or not??
            $refundItem->Quantity = $creditMemoItem->getQty();
            $refundItem->ShippingAmount = 0; // 'Not Useing this Right Now';
            $refundItem->ShippingTaxAmount = 0; // 'Not Useing this Right Now';
            $refundItem->TaxAmount = 0; // 'Not Useing this Right Now';
            $refundItem->RecyclingFee = 0; // 'Not Useing this Right Now';
            $refundItem->GiftWrapAmount = 0; // 'Not Useing this Right Now';
            $refundItem->GiftWrapTaxAmount = 0; // 'Not Useing this Right Now';
            $refundItem->RestockQuantity = 0; // Not going to use this
            $refundItem->RefundRequestID = 0; // Ignored on submit
            $refundItem->RefundRequested = true; // Ignored on submit
            $refundItems[] = $refundItem;
        }
        
        return $refundItems;
    }
}
