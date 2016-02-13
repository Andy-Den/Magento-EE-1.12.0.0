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
 */

class Harapartners_Service_Helper_Catalog extends Mage_Core_Helper_Data {
	
	const CONF_SHOE_SIZE_ATTRIBUTE_CODE = 'shoe_size';
	const UNMAP_ATTRIBUTE_CODE = 'shoe_color_config';
    const UNMAP_ATTRIBUTE_NEW_LABEL_CODE = 'shoe_color_manu';
    const IMAGE_LABEL_SEPARATOR = '__';
    const PRIORITY_IMAGE_DESGINATION = '1D';
    const COLOR_SWATCH_IMAGE_SIZE = 48;
    const BRAND_LIST_PAGE_GRID_COLUMN_COUNT = 5;
    const BRAND_LIST_PAGE_BRAND_IMAGE_SIZE = 150; //pixels
    const PRODUCT_LIST_PAGE_GRID_COLUMN_COUNT = 4; //As in design, 4 columns are used instead of 3
    const PRODUCT_BRAND_PAGE_GRID_LIMIT = 8;
    
    const PRODUCT_GENERIC_IMAGE_PLACEHOLDER = 'generic_image_placehoder';
	
    public function getCategoryByVendorId($vendorId) {
    	//Only return the first item!
    	$categoryCollection = Mage::getModel('catalog/category')->getCollection()
    		->addAttributeToSelect(array('image', 'thumbnail'))
    		->addAttributeTofilter('vendor_id', $vendorId);
    	return $categoryCollection->getFirstItem();
    }
    
    public function getCatalogRootCategoryCollection() {
    	return Mage::getModel('catalog/category')->getCollection()
				->addAttributeToSelect('name')
				->addAttributeToFilter('is_catalog_root', 1);
    }
    
    public function getCategoryProductCollectionByVendorId($category, $vendorId){
		//Price filter is important to determine if the product is salable!
		//Due to the optimization, the category product collection will have not attribute by default, need to add them manually
		$productCollection = $category->getProductCollection()
				->addAttributeToSelect(array(
						'name', 
						'vendor_id', 
						'vendor_code', 
						'vendor_name', 
						'image', 
						'small_image', 
						'thumbnail', 
						'image_label', 
						'small_image_label', 
						'thumbnail_label'
				))
				->addAttributeToFilter('vendor_id', $vendorId)
				->addFinalPrice();
		Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($productCollection);
        Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($productCollection);
        return $productCollection;
    }
    
	public function getConfigurableDropDownHtml($product){
		$html = "";
		if($product->getTypeId() != Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
			return "";
		}
		$configurableViewBlock = Mage::app()->getLayout()->createBlock('catalog/product_view_type_configurable');
		$configurableViewBlock->setTemplate("catalog/product/list/configurable.phtml");
		$configurableViewBlock->setProduct($product);
		
		//To simulate a product view page environment
		$html = $configurableViewBlock->toHtml();
		return $html;
	}
	
	public function getConfAllProducts($confProduct){
        $products = array();
		if($confProduct->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE){
            $allProducts = $confProduct->getTypeInstance(true)->getUsedProducts(null, $confProduct);
            foreach ($allProducts as $product) {
                $products[] = $product;
            }
		}
        return $products;
    }
    
	public function getConfManuColorMapping($confProduct){
		$confManuColorMapping = array();
		$mediaGalleryArray = $this->_getMediaGalleryArray($confProduct);
		foreach($confProduct->getData('_cache_instance_products') as $simpleProduct){
			if(!array_key_exists($simpleProduct->getData(self::UNMAP_ATTRIBUTE_CODE), $confManuColorMapping)){
				$manuColor = $simpleProduct->getData(self::UNMAP_ATTRIBUTE_NEW_LABEL_CODE);
				$confManuColorInfo = $this->_getConfManuColorInfo($simpleProduct, $manuColor, $mediaGalleryArray);
				$confManuColorMapping[$simpleProduct->getData(self::UNMAP_ATTRIBUTE_CODE)] = $confManuColorInfo;
			}
		}
		return $confManuColorMapping;
	}
	
	protected function _getMediaGalleryArray($confProduct) {
        $mediaGalleryArray = array();
        $mediaGallery = $confProduct->getMediaGallery();
	        if(isset($mediaGallery['images'])){
	        foreach ($mediaGallery['images'] as $values) {
	            list ($vendorInfo, $color, $type) = explode(self::IMAGE_LABEL_SEPARATOR, $values['label']);
	            //Force upper for better string comparison
	            $color = strtoupper($color);
	            $mediaGalleryArray[$color][$type] = $values;
	        }
        }
        return $mediaGalleryArray;
    }

    protected function _getConfManuColorInfo($simpleProduct, $manuColor, $mediaGalleryArray) {
    	$confManuColorInfo = array(
    			'label' => $manuColor,
    			'style_number' => $simpleProduct->getData('stock_number'),
    			'image_label' => '',
    			'image_file' => '',
    			'image_url' => ''
    	);
    	
        $mediaGallery = array();
        //Force upper for better string comparison
        if(isset($mediaGalleryArray[strtoupper($manuColor)])){
        	$mediaGallery = $mediaGalleryArray[strtoupper($manuColor)];
        }
        
        $bestMatch = null;
        if (isset($mediaGallery[self::PRIORITY_IMAGE_DESGINATION])) {
            $bestMatch = $mediaGallery[self::PRIORITY_IMAGE_DESGINATION];
        } elseif(count($mediaGallery)) {
            $bestMatch = array_shift($mediaGallery);
        }
        
        //Create a placeholder, Magento logic will use placeholder image if the file is missing
    	if (!$bestMatch || empty($bestMatch['file'])){
			$bestMatch = array('label' => $manuColor, 'file' => '');
    	}
    	
       	$imageResized = Mage::helper('catalog/image')
       			->init($simpleProduct, 'image', $bestMatch['file'])
       			->resize(self::COLOR_SWATCH_IMAGE_SIZE);
       	$confManuColorInfo['image_label'] = $bestMatch['label']; 
       	$confManuColorInfo['image_file'] = $bestMatch['file'];
       	$confManuColorInfo['image_url'] = $imageResized->__toString();
        
        return $confManuColorInfo;
    }
	
    public function getConfProductStockShipInfo($confProduct){
    	$stockShipWidgetInfo = array(
    			'default_stock_info' => 'In stock',
    			'default_shipping_estimate_info' => 'Please select product options'
    	);
    	try{
			$confAttrInfo = array();
			foreach($confProduct->getData('_cache_instance_configurable_attributes') as $confAttr){
				$confAttrInfo[$confAttr->getAttributeId()] = $confAttr->getProductAttribute()->getAttributeCode();
			}
			foreach($confProduct->getData('_cache_instance_products') as $simpleProduct){
				$stockShipWidgetInfo[$simpleProduct->getId()] = array();
				foreach($confAttrInfo as $confAttrId => $confAttrCode){
					$stockShipWidgetInfo[$simpleProduct->getId()]['super_attr_info'][$confAttrId] = $simpleProduct->getData($confAttrCode);
				}
				
				//Controlled by "SM Psyical Store QTY" in the admin panel
				$additionalStockInfo = "In stock: Available online only";
				if($simpleProduct->getData('sm_qty') > 0){
					$additionalStockInfo = "In stock: Also available in store";
				}
				$stockShipWidgetInfo[$simpleProduct->getId()]['stock_info'] = $additionalStockInfo;
				
				//Controlled by WMS Stock Count
				$shippingEstimateInfo = "Ships in 2-5 business days"; 
				if($simpleProduct->getData('orderflow_wms_stock') > 0){
					$shippingEstimateInfo = "Ships in 1-2 business days";
				}
				$stockShipWidgetInfo[$simpleProduct->getId()]['shipping_estimate_info'] = $shippingEstimateInfo;
				
			}
    	}catch (Exception $e){
    		//Do nothing
    	}
    	return $stockShipWidgetInfo;
    }
    
    public function getProductSizeChartHtml($product){
		$sizeChartCmsBlock = 'size_chart_default';
		$vendor = Mage::getModel('vendoroptions/vendoroptions_configure')->load($product->getVendorId());
		if($vendor->getSizeChartCmsBlock()){
			$sizeChartCmsBlock = $vendor->getSizeChartCmsBlock();
		}
		return Mage::getSingleton('core/layout')->createBlock('cms/block')->setBlockId($sizeChartCmsBlock)->toHtml();
    }
    
    public function getProductReturnPolicyHtml(){
		return Mage::getSingleton('core/layout')->createBlock('cms/block')->setBlockId('easy-returns')->toHtml();
    }
    
}