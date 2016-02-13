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
class Harapartners_HpSalesforce_Model_Observer
{

    public function salesInvoiceAfter($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $exportModel = $this->_getExportModel();
        $exportModel->orderMain($order);
        
        // Add the first comments.
        foreach ($order->getStatusHistoryCollection() as $index => $statusHistory) {
            $exportModel->statusHistoryForOrder($statusHistory);
        }
    }

    public function orderStatusHistorySaveBefore($observer)
    {
        $statusHistory = $observer->getEvent()->getStatusHistory();
        if (! $statusHistory->getId()) {
            $exportModel = $this->_getExportModel();
            $exportModel->statusHistoryForOrder($statusHistory);
        }
    }

    public function shipmentSaveAfter($observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $exportModel = $this->_getExportModel();
        $exportModel->shippingUpdate($shipment);
    }

    public function orderCancelAfter($observer)
    {
        $order = $observer->getEvent()->getOrder();
        $exportModel = $this->_getExportModel();
        $exportModel->orderCancel($order);
    }

    public function rmaSaveAfter($observer)
    {
        $rma = $observer->getEvent()->getRma();
        $exportModel = $this->_getExportModel();
        $exportModel->rma($rma);
    }

    /**
     * Gets the Export Model
     *
     * @return Harapartners_HpSalesforce_Model_Export
     */
    protected function _getExportModel()
    {
        return Mage::getModel('hpsalesforce/export');
    }

}
