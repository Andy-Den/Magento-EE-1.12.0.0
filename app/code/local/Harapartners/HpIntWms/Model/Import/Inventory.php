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
class Harapartners_HpIntWms_Model_Import_Inventory
{
    const COUNT_PRODUCT_NOT_FOUND = 'productNotFound';
    const COUNT_UPDATED = 'updated';
    
    protected $_orderWorkflowInventory = null;

    /**
     * Updates WMS and stock values based on array of results.
     *
     * @param array $inventory WMS Inventory Items
     * @return array notfound and found
     */
    public function processInventoy($inventory)
    {
        $updatedCount = 0;
        $productNotFound = 0;
        try {
            foreach ($inventory as $item) {
                $product = Mage::getModel('catalog/product')->load($item['ItemNumber']);
                if (! ! $product && ! ! $product->getId()) {
                    // Must reload to get the stock item as 'cataloginventory/stock_item' object
                    // HP:Steven can use get Stock by ProductId
                    if ($this->_isAvailible($item)) {
                        $qty = (int) $item['OnHandQuantity'];
                    }
                    if ($this->_updateInventory($product, $qty)) {
                        $updatedCount ++;
                    }
                } else {
                    // Unknown SKU.
                    $productNotFound ++;
                }
            }
            return array(
                self::COUNT_UPDATED => $updatedCount , 
                self::COUNT_PRODUCT_NOT_FOUND => $productNotFound
            );
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    public function _isAvailible($item)
    {
        // Possible Check for later
        return true;
    }

    /**
     * Perform the core Update. Uses OrderWorkflow if needed.
     *
     * @param unknown_type $product
     * @param numeric $qty
     * @return unknown
     */
    public function _updateInventory($product, $qty)
    {
        // Get OrderWorkflow Module
        if ($this->_isUseOrderWorkflow()) {
            $orderWorkflowInventory = $this->_getOrderWorkflowInventory();
            $orderWorkflowInventory->processWMSUpdate($product, $qty);
        } else {
            if ($qty > 0) {
                $isInStock = 1;
            } else {
                $isInStock = 0;
            }
            
            /* $stockItem Mage_CatalogInventory_Model_Stock_Item */
            $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
            $stockItem->setQty($qty);
            $stockItem->setIsInStock($isInStock);
            $stockItem->save();
        }
        
        return true; // TODO better logging
    }

    protected function _isUseOrderWorkflow()
    {
        return true;
    }

    protected function _getOrderWorkflowInventory()
    {
        if (! $this->_orderWorkflowInventory) {
            $this->_orderWorkflowInventory = Mage::getModel('hporderworkflow/inventory');
        }
        return $this->_orderWorkflowInventory;
    }
}
