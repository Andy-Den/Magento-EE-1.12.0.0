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
class Harapartners_ShoemartEdi_Model_Order
{
    const ATTRIBUTE_VENDOR_CODE = 'vendor_code';
    
    protected $_productIdToVendorCode = array();
    protected $_vendorCodeToVendorInfo = array();
    
    protected $_dropshipInventoryModel = null;

    protected function _getDropshipInventory()
    {
        if (! $this->_dropshipInventoryModel) {
            $this->_dropshipInventoryModel = Mage::getModel('hporderworkflow/inventory');
        }
        return $this->_dropshipInventoryModel;
    }

    protected function _getVendorCodeFromProduct($productId)
    {
        if (! isset($this->_productIdToVendorCode[$productId])) {
            $storeId = Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)->getCode();
            $this->_productIdToVendorCode[$productId] = Mage::getResourceModel('catalog/product')->getAttributeRawValue($productId, self::ATTRIBUTE_VENDOR_CODE, $storeId);
        }
        return $this->_productIdToVendorCode[$productId];
    }

    protected function _getVendorInfo($productId)
    {
        $vendorCode = $this->_getVendorCodeFromProduct($productId);
        if (! isset($this->_vendorCodeToVendorInfo[$vendorCode])) {
            $this->_vendorCodeToVendorInfo[$vendorCode] = Mage::getModel('vendoroptions/vendoroptions_configure')->load($vendorCode, 'code')->getData();
        }
        return $this->_vendorCodeToVendorInfo[$vendorCode];
    }

    public function isSuitable($orderItem, $qty)
    {
        if ($qty <= 0) {
            return 0;
        } else {
            $productId = $orderItem->getProductId();
            $product = Mage::getModel('catalog/product')->setId($productId);
            $vendorInfo = $this->_getVendorInfo($productId);
            if ($vendorInfo['dropship'] == 0) {
                return 0;
            } else {
                $dropshipInventoryQty = $this->_getDropshipInventory()->getDropshipStockValue($product);
                if ($dropshipInventoryQty <= 0) {
                    return 0;
                } else {
                    return $dropshipInventoryQty > $qty ? $qty : $dropshipInventoryQty;
                }
            }
        }
        return $qty;
    }

    public function processOrder($order, $qtys, $items)
    {
        return Mage::getModel('shoemartedi/export_order')->processOrder($order, $qtys, true, $items);
    }
}
