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
 * @package     Harapartners_HpChannelAdvisor_Model_Export_Inventory
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/HpChannelAdvisor/Model/Export/Inventory.php
/**
 * Order Import
 * 
 * @todo Add in logging a log file.
 *
 */
class Harapartners_HpChannelAdvisor_Model_Export_Inventory extends Mage_Core_Model_Abstract
{
	const CA_RELATIONSHIP_SHOES = '';
	
    protected $_vendorData = array();
    protected $_getAttributeModel = null;

    /**
     * Sync Full: one Product with CA.
     *
     * @param Mage_Catalog_Model_Product $product Needs to be a full product load
     */
    public function syncOneItem($product)
    {
        $zendService = Mage::helper('hpchanneladvisor')->getZendWrapper(new InventoryService(), null);
        $synchInventoryItem = new SynchInventoryItem();
        $synchInventoryItem->accountID = Mage::getStoreConfig('hpchanneladvisor/core_config/account_id');
        $synchInventoryItem->item = $this->getInventoryItemSubmit($product);
        $result = $zendService->SynchInventoryItem($synchInventoryItem);
        // TODO Process Responce
    }

    public function syncAllItems($syncDate = '')
    {
        /* @var $zendService InventoryService */
        $zendService = Mage::helper('hpchanneladvisor')->getZendWrapper(new InventoryService(), null);
        $productCollection = $this->_getCaProductCollectionSyncFull($syncDate);
        
        $totalCount = $productCollection->getSize();
        $pageSize = 1000;
        $pages = ceil($totalCount / $pageSize);
        $productList = array();
        for ($currentPage = 238; $currentPage <= $pages; $currentPage ++) {
            $productCollection->clear();
            $productCollection->setPage($currentPage, $pageSize);
            foreach ($productCollection as $product) {
                $iisArray[] = $this->getInventoryItemSubmit($product);
            }
            // TODO try catch the outside
            $syncList = new SynchInventoryItemList();
            $syncList->accountID = Mage::getStoreConfig('hpchanneladvisor/core_config/account_id');
            $syncList->itemList = $iisArray;
            try {
                $result[$currentPage] = $zendService->SynchInventoryItemList($syncList);
            } catch (SoapFault $sf) {
                $result[$currentPage] = $sf->getMessages();
            }
            $productList = array();
        }
        
    // === Process Responces === //
    

    }

    public function syncOneItemQtyPrice($product)
    {
        /* @var $zendService InventoryService */
        $zendService = Mage::helper('hpchanneladvisor')->getZendWrapper(new InventoryService(), null);
        
        $UpdateInventoryItemQuantityAndPrice = new UpdateInventoryItemQuantityAndPrice();
        $UpdateInventoryItemQuantityAndPrice->accountID = Mage::getStoreConfig('hpchanneladvisor/core_config/account_id');
        $UpdateInventoryItemQuantityAndPrice->itemQuantityAndPrice = $this->getInventoryItemQuantityAndPrice($product);
        $result = $zendService->UpdateInventoryItemQuantityAndPrice($UpdateInventoryItemQuantityAndPrice);
        // === Process Responces === //
        print_r($result);
        // TODO
    }

    public function syncAllItemsQtyPrice($syncDate = '')
    {
        $zendService = Mage::helper('hpchanneladvisor')->getZendWrapper(new InventoryService(), null);
        $productCollection = $this->_getCaProductCollectionSyncPQ($syncDate);
        
        $totalCount = $productCollection->getSize();
        $pageSize = 1000;
        $pages = ceil($totalCount / $pageSize);
        for ($currentPage = 1; $currentPage <= $pages; $currentPage ++) {
            $productCollection->clear();
            $productCollection->setPage($currentPage, $pageSize);
            foreach ($productCollection as $product) {
                $iisqpArray[] = $this->getInventoryItemQuantityAndPrice($product);
            }
            // TODO try catch the outside
            $syncList = new UpdateInventoryItemQuantityAndPriceList();
            $syncList->accountID = Mage::getStoreConfig('hpchanneladvisor/core_config/account_id');
            $syncList->itemQuantityAndPriceList = $iisqpArray;
            try {
                $result[$currentPage] = $zendService->UpdateInventoryItemQuantityAndPriceList($syncList);
            } catch (SoapFault $sf) {
                $result[$currentPage] = $sf->getMessage();
            }
        }
        
        // Process Results
        return $result;
    }

    public function getInventoryItemQuantityAndPrice($product)
    {
        $request = new InventoryItemQuantityAndPrice();
        $request->DistributionCenterCode = Mage::getStoreConfig('hpchanneladvisor/location/distribution_center_code');
        $request->Quantity = $this->_getProductQty($product);
        $request->UpdateType = 'Absolute';
        $request->Sku = $product->getSku();
        $request->PriceInfo = $this->_getPriceInfo($product);
        return $request;
    }

    /**
     * Creates a CA InventoryItemSubmit From a Magento Product
     *
     * @param Varien_Object $product
     * 
     * @return InventoryItemSubmit
     */
    public function getInventoryItemSubmit($product)
    {
        $iis = new InventoryItemSubmit();
        
        $iis->Sku = $product->getData('sku');
        $iis->Title = $product->getData('title');
        //// $iis->Subtitle = 'Were Not Gonna Support This';
        $iis->ShortDescription = $product->getData('short_description');
        $iis->Description = $product->getData('description');
        // TODO Warrenty (255) and MetaDescription
        // TODO some of the middle items
        

        $iis->Weight = $product->getWeight();
        // TODO HLW
        

        $iis->SupplierCode = Mage::getStoreConfig('hpchanneladvisor/location/supplier_code');
        $iis->WarehouseLocation = Mage::getStoreConfig('hpchanneladvisor/location/warehouse_location');
        $iis->TaxProductCode = Mage::getStoreConfig('hpchanneladvisor/configs/tax_product_code');
        $iis->FlagStyle = 'Blue';
        $iis->FlagDescription = 'Magento Synced';
        $iis->IsBlocked = false;
        $iis->BlockComment = '';
        
        // Quantity Info
        $iis->DistributionCenterList = $this->_getDistributionCenterInfoSubmit($product);
        
        // Price Info
        $iis->PriceInfo = $this->_getPriceInfo($product);
        
        $iis->StoreInfo 	= $this->_getStoreInfo($product);
        $iis->AttributeList = $this->_getAttributeList($product);
        $iis->ImageList 	= $this->_getImageList($product);
        $iis->VariationInfo = $this->_getVariationInfo($product);
        
        return $iis;
    }

    protected function _getProductQty($product)
    {
        $vendorDatas = $this->_getEbayAmzVendorData();
        $vendorData = $vendorDatas[$product->getData('vendor_code')];
        
        if ($vendorData['channel_qty_wms_only']) {
            return $product->getData('orderflow_wms_stock');
        } else {
            return $product->getQty() ? $product->getQty() : $product->getStockItem()->getQty(); // Bec of join need to check qty
        }
    }

    protected function _getStoreInfo($product)
    {
        // TODO
        //throw new Exception('NI');
        return null;
    }

    protected function _getAttributeList($product)
    {
        $caAttributeSet = $this->_getAttributeModel()->getCaAttributeSetFromProduct($product);
        
        $attributeSet = array();
        foreach ($caAttributeSet as $code => $caName) {
            $attribute = new AttributeInfo();
            $attribute->Name = $caName ? $caName : $code;
            $attribute->Value = $product->getData($code);
            
            $attributeSet[] = $attribute;
        }
        
        return $attributeSet;
    
    }

    /**
     * Gets the product's image list
     * @todo FolderName -  How do we want to use this.
     * @todo PlacementName - What does this do ?
     *
     * @param Mage_Catalog_Model_Product $product
     * @return array {ImageInfoSubmit, ImageInfoSubmit, ...}
     */
    protected function _getImageList($product)
    {
        $attributeModel = Mage::getModel('eav/entity_attribute')->loadByCode('catalog_product', 'media_gallery');
        $attributeModel->getBackend()->afterLoad($product);
        $mediaGallery = $product->getMediaGalleryImages();
        if($mediaGallery->count() == 0){
        	return null;
        }
        
        $imageInfos = array();
        foreach ($mediaGallery as $id => $mediaInfo){
        	$fullUrl= $mediaInfo->getData('url');
        	if(empty($fullUrl)){
        		continue;
        	}

        	$newImageInfoSubmit = new ImageInfoSubmit();
        	$newImageInfoSubmit->FilenameOrUrl = $fullUrl;
        	
        	$imageInfos = $newImageInfoSubmit;
        }
    
        return $imageInfos;
    }

    /**
     * Gets the Variation Info
     * @todo needs to get SKU from id, there may be a better way
     *
     * @param Mage_Catalog_Model_Product $product
     * @return VariationInfo
     */
    protected function _getVariationInfo($product)
    {
        $productType = $product->getTypeId();
        $isParent = ($productType == 'configurable');
        $parentId = $product->getParentId();
        $isInRelationship = ($isParent || $parentId);
        $parentSku = $isParent ? $product->getSku() : Mage::getModel('catalog/product')->load($parentId)->getSku();
        
        $variationInfo = new VariationInfo();
        $variationInfo->IsInRelationship = $isInRelationship;
        if ($isInRelationship) {
            $variationInfo->IsParent = $isParent;
            $variationInfo->ParentSku = $parentSku;
            $variationInfo->RelationshipName = self::CA_RELATIONSHIP_SHOES;
        }
        
        return $variationInfo;
    }

    protected function _getDistributionCenterInfoSubmit($product)
    {
        $distributionCenterList = new DistributionCenterInfoSubmit();
        $distributionCenterList->DistributionCenterCode = Mage::getStoreConfig('hpchanneladvisor/location/distribution_center_code');
        $distributionCenterList->Quantity = $product->getQty();
        $distributionCenterList->QuantityUpdateType = 'Absolute';
        /* Absolute	 Sets the inventory quantity for this item to the value of the 
			submitted quantity.
		Relative	 Adds the value of the submitted quantity to the current 
			inventory quantity. Negative numbers are acceptable.
		Available	 Takes the submitted value of quantity, subtracts out any open 
			listings for this item and sets the remaining value as the inventory 
			quantity.
		InStock	 Takes the submitted value of quantity, subtracts out any open 
			listings, pending checkouts, and pending payments for this item and 
			sets the remaining value as the inventory quantity.
		UnShipped	 Takes the submitted value of quantity, subtracts out any open 
			listings, pending checkouts, pending payments and pending shipments 
			for this item and sets the remaining value as the inventory quantity.*/
        
        $distributionCenterList->ShippingRateList = $this->_getShippingRateList($product);
        return array(
            $distributionCenterList
        );
    }

    protected function _getShippingRateList($product)
    {
        $ShippingRateInfo = array();
        
        $shippingRateInfo = new ShippingRateInfo();
        $shippingRateInfo->DestinationZoneName = Mage::getStoreConfig('hpchanneladvisor/shipping1/destination_zone_name');
        $shippingRateInfo->CarrierCode = Mage::getStoreConfig('hpchanneladvisor/shipping1/carrier_code');
        $shippingRateInfo->ClassCode = Mage::getStoreConfig('hpchanneladvisor/shipping1/class_code');
        $shippingRateInfo->FirstItemRateAttribute = Mage::getStoreConfig('hpchanneladvisor/shipping1/first_item_rate_attribute');
        $shippingRateInfo->FirstItemRate = Mage::getStoreConfig('hpchanneladvisor/shipping1/first_item_rate');
        $shippingRateInfo->FirstItemHandlingRateAttribute = Mage::getStoreConfig('hpchanneladvisor/shipping1/first_item_handling_rate_attribute');
        $shippingRateInfo->FirstItemHandlingRate = Mage::getStoreConfig('hpchanneladvisor/shipping1/first_item_handling_rate');
        $shippingRateInfo->AdditionalItemHandlingRateAttribute = Mage::getStoreConfig('hpchanneladvisor/shipping1/additional_item_rate_attribute');
        $shippingRateInfo->AdditionalItemHandlingRate = Mage::getStoreConfig('hpchanneladvisor/shipping1/additional_item_rate');
        $shippingRateInfo->AdditionalItemHandlingRateAttribute = Mage::getStoreConfig('hpchanneladvisor/shipping1/additional_item_handling_rate_attribute');
        $shippingRateInfo->AdditionalItemHandlingRate = Mage::getStoreConfig('hpchanneladvisor/shipping1/additional_item_handling_rate');
        $shippingRateInfo->FreeShippingIfBuyItNow = Mage::getStoreConfig('hpchanneladvisor/shipping1/free_shipping_if_buy_it_now');
        $ShippingRateInfo[] = $shippingRateInfo;
        
        $maxShippingConfig = 4; // TODO Mage::getStoreConfig("hpchanneladvisor/shipping/max_used");
        for ($i = 2; $i <= $maxShippingConfig; $i ++) {
            if (Mage::getStoreConfig("hpchanneladvisor/shipping{$i}/is_used")) {
                $shippingRateInfo = new ShippingRateInfo();
                $shippingRateInfo->DestinationZoneName = Mage::getStoreConfig("hpchanneladvisor/shipping{$i}/destination_zone_name");
                $shippingRateInfo->CarrierCode = Mage::getStoreConfig("hpchanneladvisor/shipping{$i}/carrier_code");
                $shippingRateInfo->ClassCode = Mage::getStoreConfig("hpchanneladvisor/shipping{$i}/class_code");
                $shippingRateInfo->FirstItemRateAttribute = Mage::getStoreConfig("hpchanneladvisor/shipping{$i}/first_item_rate_attribute");
                $shippingRateInfo->FirstItemRate = Mage::getStoreConfig("hpchanneladvisor/shipping{$i}/first_item_rate");
                $shippingRateInfo->FirstItemHandlingRateAttribute = Mage::getStoreConfig("hpchanneladvisor/shipping{$i}/first_item_handling_rate_attribute");
                $shippingRateInfo->FirstItemHandlingRate = Mage::getStoreConfig("hpchanneladvisor/shipping{$i}/first_item_handling_rate");
                $shippingRateInfo->AdditionalItemHandlingRateAttribute = Mage::getStoreConfig("hpchanneladvisor/shipping{$i}/additional_item_rate_attribute");
                $shippingRateInfo->AdditionalItemHandlingRate = Mage::getStoreConfig("hpchanneladvisor/shipping{$i}/additional_item_rate");
                $shippingRateInfo->AdditionalItemHandlingRateAttribute = Mage::getStoreConfig("hpchanneladvisor/shipping{$i}/additional_item_handling_rate_attribute");
                $shippingRateInfo->AdditionalItemHandlingRate = Mage::getStoreConfig("hpchanneladvisor/shipping{$i}/additional_item_handling_rate");
                $shippingRateInfo->FreeShippingIfBuyItNow = Mage::getStoreConfig("hpchanneladvisor/shipping{$i}/free_shipping_if_buy_it_now");
                $ShippingRateInfo[] = $shippingRateInfo;
            }
        }
        
        return $ShippingRateInfo;
    }

    protected function _getPriceInfo($product)
    {
        $caEbayPrice = $product->getData('ca_ebay_price');
        $price = empty($caEbayPrice) ? $product->getPrice() : $caEbayPrice;
        
        $priceInfo = new PriceInfo();
        $priceInfo->Cost = $product->getCost();
        // $priceInfo->RetailPrice = 'Were Not Gonna Support This Now';
        $priceInfo->StartingPrice = $price;
        // $priceInfo->ReservePrice = 'Were Not Gonna Support This Now';
        $priceInfo->TakeItPrice = $price;
        // $priceInfo->SecondChanceOfferPrice = 'Were Not Gonna Support This Now';
        $priceInfo->StorePrice = $price;
        
        return $priceInfo;
    }

    /**
     * If the Vendor Code is a select
     *
     * @param unknown_type $vendorCodes
     * @return unknown
     */
    protected function _getVendorCodesToId($vendorCodes)
    {
        $attributeIds = array();
        
        $attributeId = Mage::getResourceModel('eav/entity_attribute')->getIdByCode('catalog_product', 'vendor_code');
        $attribute = Mage::getModel('catalog/resource_eav_attribute')->load($attributeId);
        foreach ($attribute->getSource()->getAllOptions(false, false) as $options) {
            $attributeIds[$options['label']] = $options['value'];
        }
        return $attributeIds;
    }

    protected function _getEbayAmzVendorData($isNonEbayAmz = false)
    {
        if (empty($this->_vendorData)) {
            $vendorCollection = Mage::getModel('vendoroptions/vendoroptions_configure')->getCollection();
            if (! $isNonEbayAmz) {
                $vendorCollection->addFilter('sell_ebay', 1, 'or');
                $vendorCollection->addFilter('sell_amz', 1, 'or');
            }
            
            foreach ($vendorCollection as $vendorModel) {
                $this->_vendorData[$vendorModel->getCode()] = $vendorModel->getData();
            }
        }
        
        return $this->_vendorData;
    }

    protected function _getAttributeModel()
    {
        if (! $this->_getAttributeModel) {
            $this->_getAttributeModel = Mage::getModel('hpchanneladvisor/export_attributes');
        }
        return $this->_getAttributeModel;
    }

    protected function _getBasicCaProductCollection()
    {
        $vendorData = $this->_getEbayAmzVendorData(false);
        foreach ($vendorData as $vendor) {
            $vendorCodes[] = $vendor['code'];
        }
        
        $productCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('vendor_code', array(
            'in' => $vendorCodes
        ));
        $productCollection->addAttributeToSelect('cost')->addAttributeToSelect('price');
        $productCollection->addAttributeToSelect('ca_ebay_price');
        $productCollection->addAttributeToSelect('orderflow_wms_stock');
        $productCollection->joinField('qty', 'cataloginventory/stock_item', 'qty', 'product_id=entity_id', array(
            'stock_id' => 1
        ), 'left');
        
        return $productCollection;
    }

    protected function _getCaProductCollectionSyncPQ($syncDate = '')
    {
        $productCollection = $this->_getBasicCaProductCollection();
        if (! empty($syncDate)) {
            $productCollection->addFieldToFilter(array(
                array(
                    'attribute' => 'updated_at' , 
                    'from' => $syncDate , 
                    'date' => true
                ) , 
                array(
                    'attribute' => 'orderflow_updated_at' , 
                    'from' => $syncDate , 
                    'date' => true
                )
            ));
        }
        
        return $productCollection;
    }

    protected function _getCaProductCollectionSyncFull($syncDate = '')
    {
        $productCollection = $this->_getBasicCaProductCollection();
        
        $productCollection->addAttributeToSelect('description')->addAttributeToSelect('short_description');
        $productCollection->addAttributeToSelect('weight');
        
        // == This is for parentInfo == //
        $productCollection->joinField('parent_id', 'catalog/product_super_link', 'parent_id', 'product_id=entity_id', null, 'left');
        // == End Parent Info ==//
        

        if (! empty($syncDate)) {
            $productCollection->addFieldToFilter(array(
                array(
                    'attribute' => 'updated_at' , 
                    'from' => $syncDate , 
                    'date' => true
                ) , 
                array(
                    'attribute' => 'orderflow_updated_at' , 
                    'from' => $syncDate , 
                    'date' => true
                )
            ));
        }
        
        return $productCollection;
    }
}
