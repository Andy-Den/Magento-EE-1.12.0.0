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
class Harapartners_ShoemartEdi_Model_Import_Inventory
{
    const COUNT_PRODUCT_NOT_FOUND = 'productNotFound';
    const COUNT_UPDATED = 'updated';
    
    protected $_moveToDirectory = '';
    protected $_orderWorkflowInventory = null;
    protected $_ediInventoryModel = null;
    protected $_vendorModel = null;
    
    protected $_fileLocation;
    protected $_amtNotFound = 0;
    protected $_amtUpdated = 0;

    public function importInventoryEdi($location = null)
    {
        $this->_resetAmounts();
        $this->_fileLocation = $location;
        
        /* @var $invfeedModel Harapartners_ShoemartEdi_Model_Edi_Inventory */
        $invfeedModel = $this->_getEdiInventoryModel();
        $parsedArray = $invfeedModel->parseInventoryFeed($location);
        $rdyArray = $invfeedModel->renderInventoryFeed($parsedArray);
        
        $result = $this->processInventoy($rdyArray);
        $this->_setAmounts($result);
        return $result;
    
    }

    public function processInventoy($inventory)
    {
        $updatedCount = 0;
        $productNotFound = 0;
        try {
            foreach ($inventory as $item) {
                if (! empty($item['productId'])) {
                    $product = Mage::getModel('catalog/product')->load($item['productId']);
                } elseif (! empty($item['sku'])) {
                    $product = Mage::getModel('catalog/product')->load($item['sku'], 'sku');
                } elseif (! empty($item['upc'])) {
                    $product = Mage::getModel('catalog/product')->loadByAttribute('upc', $item['upc']);
                }
                if (! ! $product && ! ! $product->getId()) {
                    if ($this->_isAvailible($item)) {
                        $qty = (int) $item['qty'];
                    }
                    if ($this->_updateInventory($product, $qty)) {
                        $this->_setInvDate($product);
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

    public function _updateInventory($product, $qty)
    {
        // Get OrderWorkflow Module
        if ($this->_isUseOrderWorkflow()) {
            $this->_processOrderWorkflowUpdate($product, $qty);
        } else {
            $this->_processSingleStockUpdate($product, $qty);
        }
        
        return true; // TODO better logging
    }

    protected function _isUseOrderWorkflow()
    {
        return true;
    }

    protected function _processOrderWorkflowUpdate($product, $qty)
    {
        $orderWorkflowInventory = $this->_getOrderWorkflowInventory();
        $orderWorkflowInventory->processDropshipUpdate($product, $qty);
    }

    protected function _processSingleStockUpdate($product, $qty)
    {
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

    protected function _getOrderWorkflowInventory()
    {
        if (! $this->_orderWorkflowInventory) {
            $this->_orderWorkflowInventory = Mage::getModel('hporderworkflow/inventory');
        }
        return $this->_orderWorkflowInventory;
    }

    protected function _getEdiInventoryModel()
    {
        if (! $this->_ediInventoryModel) {
            $this->_ediInventoryModel = Mage::getModel('shoemartedi/edi_inventory');
        }
        return $this->_ediInventoryModel;
    }

    protected function _setInvDate($product)
    {
        $vendorCode = $product->getData('vendor_code');
        $date = $this->_getOffsetInvDate($vendorCode);
        
        $this->_getOrderWorkflowInventory()->setDropshipDate($product, $date);
        return true;
    }

    protected function _getOffsetInvDate($vendorCode, $unixDate = null)
    {
        if ($unixDate == null) {
            $unixDate = time();
        }
        
        $offset = $this->_getVendorModel($vendorCode)->getData('edi_date_offset');
        if ($offset) {
            $unixOffset = strtotime($offset, $unixDate);
        } else {
            $unixOffset = $unixDate;
        }
        return date('c', $unixOffset);
    
    }

    protected function _getVendorModel($vendorCode)
    {
        if (! (isset($this->_vendorModel) && $this->_vendorModel->getData('code') == $vendorCode)) {
            $this->_vendorModel = Mage::getModel('vendoroptions/vendoroptions_configure')->load($vendorCode, 'code');
        }
        
        return $this->_vendorModel;
    }

    protected function _resetAmounts()
    {
        $this->_amtNotFound = 0;
        $this->_amtUpdated = 0;
    }

    protected function _setAmounts($result)
    {
        $this->_amtNotFound = $result[self::COUNT_PRODUCT_NOT_FOUND];
        $this->_amtUpdated = $result[self::COUNT_UPDATED];
    }

    public function isComplete()
    {
        return true;
    }

    public function moveCompletedDocument()
    {
        $this->_moveToDirectory = Mage::getBaseDir('base') . DS . Mage::getStoreConfig('shoemart_edi/sync_setting/base_dir') . DS . 'in' . DS . 'complete' . DS;
        $flocal = new Varien_Io_File();
        if ($flocal->checkAndCreateFolder($this->_moveToDirectory)) {
            return $flocal->mv($this->_fileLocation, $this->_moveToDirectory . basename($this->_fileLocation));
        }
        return false;
    }
}
