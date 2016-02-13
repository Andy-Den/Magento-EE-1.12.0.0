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
class Harapartners_HpIntWms_Model_Order
{
    protected $_wmsInventoryModel = null;

    protected function _getWMSInventory()
    {
        if (! $this->_wmsInventoryModel) {
            $this->_wmsInventoryModel = Mage::getModel('hporderworkflow/inventory');
        }
        return $this->_wmsInventoryModel;
    }

    protected function _getExportModel()
    {
        return Mage::getModel('hpintwms/export');
    }

    public function isSuitable($orderItem, $qty)
    {
        if ($qty <= 0) {
            return 0;
        } else {
            $productId = $orderItem->getProductId();
            $product = Mage::getModel('catalog/product')->setId($productId);
            $wmsInventoryQty = $this->_getWMSInventory()->getWMSStockValue($product);
            if ($wmsInventoryQty <= 0) {
                return 0;
            } else {
                return $wmsInventoryQty > $qty ? $qty : $wmsInventoryQty;
            }
        }
    }

    public function processOrder(Mage_Sales_Model_Order $order, $qtys, $items)
    {
        /* @var $exportModel Harapartners_HpIntWms_Model_Export */
        $exportModel = $this->_getExportModel();
        return $exportModel->submitOrder($order, $qtys, true, $items);
    }
}
