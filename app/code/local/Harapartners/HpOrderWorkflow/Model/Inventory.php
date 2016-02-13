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
class Harapartners_HpOrderWorkflow_Model_Inventory
{
    protected $_wmsStockAttribute = null;
    protected $_dropshipStockAttribute = null;
    
    const ATTRIBUTE_DROPSHIP_STOCK = 'orderflow_dropship_stock';
    const ATTRIBUTE_WMS_STOCK = 'orderflow_wms_stock';
    const ATTRIBUTE_DROPSHIP_STOCK_DATE = 'orderflow_dropship_stock_date';
    const ATTRIBUTE_ORDERFLOW_LAST_UPDATE = 'orderflow_updated_at';

    // === GET / SET / SAVE === // 
    

    public function saveWMSAttribute($product, $value)
    {
        $attributeName = self::ATTRIBUTE_WMS_STOCK;
        return $this->_saveStockAttribute($product, $value, $attributeName);
    }

    public function saveDropshipAttribute($product, $value)
    {
        $attributeName = self::ATTRIBUTE_DROPSHIP_STOCK;
        return $this->_saveStockAttribute($product, $value, $attributeName);
    }

    protected function _saveStockAttribute($product, $value, $attribute)
    {
        $product->setData($attribute, $value);
        $product->setData(self::ATTRIBUTE_ORDERFLOW_LAST_UPDATE, now());
        $product->getResource()->saveAttribute($product, $attribute);
        return $product->getResource()->saveAttribute($product, self::ATTRIBUTE_ORDERFLOW_LAST_UPDATE);
    }

    public function subtractWMSStock($product, $qtyToSubtract)
    {
        $attributeName = self::ATTRIBUTE_WMS_STOCK;
        $newStock = $this->_subtractStockAttribute($product, $qtyToSubtract, $attributeName);
        return $this->saveWMSAttribute($product, $newStock);
    }

    public function subtractDropshipStock($product, $qtyToSubtract)
    {
        $attributeName = self::ATTRIBUTE_DROPSHIP_STOCK;
        $newStock = $this->_subtractStockAttribute($product, $qtyToSubtract, $attributeName);
        return $this->saveDropshipAttribute($product, $newStock);
    }

    protected function _subtractStockAttribute($product, $qtyToSubtract, $attributeName)
    {
        $oldStockValue = Mage::registry($this->_getRegistryString($attributeName, $product));
        return $oldStockValue - $qtyToSubtract;
    }

    public function getWMSStockValue($product)
    {
        $attributeName = self::ATTRIBUTE_WMS_STOCK;
        return $this->_getStockValue($product, $attributeName);
    }

    public function getDropshipStockValue($product)
    {
        $attributeName = self::ATTRIBUTE_DROPSHIP_STOCK;
        return $this->_getStockValue($product, $attributeName);
    }

    protected function _getStockValue($product, $attributeName)
    {
        $storeId = Mage::app()->getStore(Mage_Catalog_Model_Abstract::DEFAULT_STORE_ID)->getCode();
        $attributeStock = $product->getResource()->getAttributeRawValue($product->getId(), $attributeName, $storeId);
        Mage::register($this->_getRegistryString($attributeName, $product), $attributeStock);
        return $attributeStock;
    }

    protected function _getWMSAttributeModel()
    {
        if (! $this->_wmsStockAttribute) {
            $wmsAttributeCode = self::ATTRIBUTE_WMS_STOCK;
            $this->_wmsStockAttribute = Mage::getResourceModel('catalog/product')->getAttribute($wmsAttributeCode);
        }
        return $this->_wmsStockAttribute;
    
    }

    protected function _getDropshipAttributeModel()
    {
        if (! $this->_dropshipStockAttribute) {
            $dropshipAttributeCode = self::ATTRIBUTE_DROPSHIP_STOCK;
            $this->_dropshipStockAttribute = Mage::getResourceModel('catalog/product')->getAttribute($dropshipAttributeCode);
        }
        return $this->_dropshipStockAttribute;
    }

    protected function _getRegistryString($attributeName, $product)
    {
        return $attributeName . '_' . $product->getId();
    }

    // === End Get === //
    public function setDropshipDate($product, $date)
    {
        $product->setData(self::ATTRIBUTE_DROPSHIP_STOCK_DATE, $date);
        return $product->getResource()->saveAttribute($product, self::ATTRIBUTE_DROPSHIP_STOCK_DATE);
    }

    public function processWMSUpdate($product, $qty)
    {
        return $this->_processStockUpdate($product, $qty, null);
    }

    public function processDropshipUpdate($product, $qty)
    {
        return $this->_processStockUpdate($product, null, $qty);
    }

    public function processFullStockUpdate($product, $qtyWMS, $qtyDropship)
    {
        return $this->_processStockUpdate($product, $qtyWMS, $qtyDropship);
    }

    protected function _processStockUpdate($product, $qtyWMS = null, $qtyDropship = null)
    {
        // If we got here we assume that both are configured corretly
        // Get the two values, verify the data and set.
        // TODO Verify incomeing Data ??
        $isWmsStock = false;
        $isDropshiptStock = false;
        
        // Get the Current Stock Values
        /* $stockItem Mage_CatalogInventory_Model_Stock_Item */
        $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
        $oldStockTotal = $stockItem->getQty();
        $attributeWMSStock = $this->getWMSStockValue($product);
        $attributeDropshipStock = $this->getDropshipStockValue($product);
        
        // Chose which stock method to save
        if ($qtyDropship !== null && $qtyWMS !== null) {
            $newStockTotal = $qtyWMS + $qtyDropship;
            $isWmsStock = true;
            $isDropshiptStock = true;
        } elseif ($qtyDropship === null) {
            $newStockTotal = $qtyWMS + $attributeDropshipStock;
            $isWmsStock = true;
        } else {
            $newStockTotal = $attributeWMSStock + $qtyDropship;
            $isDropshiptStock = true;
        }
        
        // Check If we had a desync and log it
        $difference = $oldStockTotal - $attributeWMSStock - $attributeDropshipStock;
        if ($difference != 0) {
            $difference ? $this->_logInvSyncError('Plus ' . $difference) : $this->_logInvSyncError('Minus ' . $difference);
        }
        
        // Set InStatus based on Stock > 0
        if ($newStockTotal > 0) {
            $isInStock = 1;
        } else {
            $isInStock = 0;
        }
        
        // Save the main Stock Item
        $stockItem->setQty($newStockTotal);
        $stockItem->setIsInStock($isInStock);
        $stockItem->save();
        
        // Save the Stock Attributes
        if ($isWmsStock) {
            $this->saveWMSAttribute($product, $qtyWMS);
        }
        if ($isDropshiptStock) {
            $this->saveDropshipAttribute($product, $qtyDropship);
        }
        
    // return true;
    }

    public function removeOldDropshipStock()
    {
        $dateToComapre = now();
        
        /* @var $productCollection Mage_Catalog_Model_Resource_Product_Collection */
        $productCollection = Mage::getModel('catalog/product')->getCollection();
        $productCollection->addAttributeToSelect(self::ATTRIBUTE_DROPSHIP_STOCK);
        $productCollection->addAttributeToSelect(self::ATTRIBUTE_DROPSHIP_STOCK_DATE);
        $productCollection->addAttributeToFilter(self::ATTRIBUTE_DROPSHIP_STOCK_DATE, array(
            'lt' => $dateToComapre
        ));
        $productCollection->addAttributeToFilter(self::ATTRIBUTE_DROPSHIP_STOCK, array(
            'gt' => 0
        ));
        
        foreach ($productCollection as $product) {
            $product->setData(self::ATTRIBUTE_DROPSHIP_STOCK, 0);
            //// $product->setData(self::ATTRIBUTE_DROPSHIP_STOCK_DATE, ''); // By request, The date is left
            $product->save();
        }
    
    }

    protected function _logInvSyncError($msg)
    {
        Mage::log($msg, null, 'Order_Workflow_Inv_Desync.log', true);
    }
}
