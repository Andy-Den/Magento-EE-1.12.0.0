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

class Harapartners_Service_Model_Rewrite_Catalog_Convert_Adapter_Product extends Mage_Catalog_Model_Convert_Adapter_Product {
    
    const MULTI_DELIMITER   = ','; //Harapartners, Jun
    const DEFAULT_FIELD_DELIMITER   = ','; //Harapartners, Jun
	const DEFAULT_ATTR_DELIMITER = '||';
	
    public function saveRow(array $importData){
        $product = $this->getProductModel()
            ->reset();

        if (empty($importData['store'])) {
            if (!is_null($this->getBatchParams('store'))) {
                $store = $this->getStoreById($this->getBatchParams('store'));
            } else {
                $message = Mage::helper('catalog')->__(
                    'Skipping import row, required field "%s" is not defined.',
                    'store'
                );
                Mage::throwException($message);
            }
        } else {
            $store = $this->getStoreByCode($importData['store']);
        }

        if ($store === false) {
            $message = Mage::helper('catalog')->__(
                'Skipping import row, store "%s" field does not exist.',
                $importData['store']
            );
            Mage::throwException($message);
        }

        if (empty($importData['sku'])) {
            $message = Mage::helper('catalog')->__('Skipping import row, required field "%s" is not defined.', 'sku');
            Mage::throwException($message);
        }
        $product->setStoreId($store->getId());
        $productId = $product->getIdBySku($importData['sku']);
        $new = true; //Hara Partners, Richu
        if ($productId) {
            $product->load($productId);
            $new = false; //Hara Partners, Richu
        } else {
            $productTypes = $this->getProductTypes();
            $productAttributeSets = $this->getProductAttributeSets();

            /**
             * Check product define type
             */
            if (empty($importData['product_type']) || !isset($productTypes[strtolower($importData['product_type'])])) {
                $value = isset($importData['product_type']) ? $importData['product_type'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, is not valid value "%s" for field "%s"',
                    $value,
                    'type'
                );
                Mage::throwException($message);
            }
            $product->setTypeId($productTypes[strtolower($importData['product_type'])]);
            /**
             * Check product define attribute set
             */
            if (empty($importData['attribute_set']) || !isset($productAttributeSets[$importData['attribute_set']])) {
                $value = isset($importData['attribute_set']) ? $importData['attribute_set'] : '';
                $message = Mage::helper('catalog')->__(
                    'Skip import row, the value "%s" is invalid for field "%s"',
                    $value,
                    'attribute_set'
                );
                Mage::throwException($message);
            }
            $product->setAttributeSetId($productAttributeSets[$importData['attribute_set']]);

            foreach ($this->_requiredFields as $field) {
                $attribute = $this->getAttribute($field);
                if ((!isset($importData[$field]) || empty($importData[$field]) && $attribute && $attribute->getIsRequired())) {
                    $message = Mage::helper('catalog')->__(
                        'Skipping import row, required field "%s" for new products is not defined.',
                        $field
                    );
                    Mage::throwException($message);
                }
            }
            
            
            /**
             * Hara Partners START
             * Richu
             */
        
            if ($importData['product_type'] == 'configurable') {
            	try{
	                $product->setCanSaveConfigurableAttributes(true);
	                $configAttributeCodes = explode(self::DEFAULT_FIELD_DELIMITER, $importData['configurable_attribute_codes']);
	                $usingAttributeIds = array();
	                foreach($configAttributeCodes as $attributeCode) {
	                    $attributeCode = trim($attributeCode);
	                    $attribute = $product->getResource()->getAttribute($attributeCode);
	                    if ($product->getTypeInstance()->canUseAttribute($attribute)) {
	                        if ($new) { // fix for duplicating attributes error
	                            $usingAttributeIds[] = $attribute->getAttributeId();
	                        }
	                    }
	                }
	                if (!empty($usingAttributeIds)) {
	                    $product->getTypeInstance()->setUsedProductAttributeIds($usingAttributeIds);
	                    $configurableAttributeArray = $product->getTypeInstance()->getConfigurableAttributesAsArray();
	                    $product->setConfigurableAttributesData($configurableAttributeArray);
	                    $product->setCanSaveConfigurableAttributes(true);
	                    $product->setCanSaveCustomOptions(true);
	                }
	                if (isset($importData['conf_simple_products'])) {
	                    $product->setConfigurableProductsData($this->_getProductIdFromSku($importData['conf_simple_products'], $product));
	                }
            	}catch(Exception $e){
            		throw new Exception($e->getMessage());
            	}
            }
            /**
             * Hara Partners END
             * Richu
             */
            
           //Harapartners Quan Start Grouped Products
           if ($importData['product_type'] == 'grouped') {
					$groupedSkus = explode(',', $importData['grouped_simple_products']);
						$relationData = array();
						foreach($groupedSkus as $groupedSku){
							$simpleProductId = Mage::getModel('catalog/product')->getIdBySku($groupedSku);
							$relationData[$simpleProductId] = array('qty' => 0,
													'position' => 0
												);
						}
					
						$product->setGroupedLinkData($relationData);	
            }
            
            //Harapartners Quan End Grouped Products
            //Harapartners Quan Start Bundle Products
          if ($importData['product_type'] == 'bundle') {
					$bundleSimpleString = explode(';', $importData['bundle_selections']);
					foreach($bundleSimpleString as $optionSimpleString){
						$optionSimples = explode(',', $optionSimpleString);
						$bundleSimples[] = $optionSimples;						
					}
					$bundlOptions = explode(',', $importData['bundle_options']);
					//$options = $product->getTypeInstance(true)->getOptionsCollection($product);
					$selections = array();
					foreach($bundlOptions as $key => $bundlOption){
						$options[] = array(
			                'title' => $bundlOption,
			                'option_id' => '',
			                'delete' => '',
			                'type' => 'radio',
			                'required' => 1,
			                'position' => 0
						);	
						$selectionData = array();
												
						foreach($bundleSimples[$key] as $bundleSimple){
							$selectionData[] = array(
				                'selection_id' => '',
				                'option_id' => '',
				                'product_id' => Mage::getModel('catalog/product')->getIdBySku($bundleSimple),
				                'delete' => '',
				                'selection_price_value' => '10',
				                'selection_price_type' => 0,
				                'selection_qty' => 1,
				                'selection_can_change_qty' => 0,
				                'position' => 0
							);	
						}
						$selections[$key] = $selectionData;
						
					}
		            Mage::register('product', $product);
		            Mage::register('current_product', $product);					
					$product->setBundleOptionsData($options);
					//$bundlOptions =  $product->getTypeInstance(true)->getOptionsCollection($product);
					$product->setBundleSelectionsData($selections);					
		            $product->setCanSaveCustomOptions(true);
		            $product->setCanSaveBundleSelections(true);
		            if(!!$product->getData('store_id')){
		            	$product->setStoreId(0);
		            }
					
            }                     
            //Harapartners Quan End Bundle Products
        }

        $this->setProductTypeInstance($product);

        if (isset($importData['category_ids'])) {
            $product->setCategoryIds($importData['category_ids']);
        }

        /*
         * Hara Partners, Richu
         * If a product is NOT new, ignoreFields will be skipped.
         */

        foreach ($this->_ignoreFields as $field) {
            if (isset($importData[$field]) && $field!='type' ) {
                unset($importData[$field]);
            }
        }
        

        if ($store->getId() != 0) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds)) {
                $websiteIds = array();
            }
            if (!in_array($store->getWebsiteId(), $websiteIds)) {
                $websiteIds[] = $store->getWebsiteId();
            }
            $product->setWebsiteIds($websiteIds);
        }

        if (isset($importData['websites'])) {
            $websiteIds = $product->getWebsiteIds();
            if (!is_array($websiteIds) || !$store->getId()) {
                $websiteIds = array();
            }
            $websiteCodes = explode(',', $importData['websites']);
            foreach ($websiteCodes as $websiteCode) {
                try {
                    $website = Mage::app()->getWebsite(trim($websiteCode));
                    if (!in_array($website->getId(), $websiteIds)) {
                        $websiteIds[] = $website->getId();
                    }
                } catch (Exception $e) {}
            }
            $product->setWebsiteIds($websiteIds);
            unset($websiteIds);
        }

        foreach ($importData as $field => $value) {
            if (in_array($field, $this->_inventoryFields)) {
                continue;
            }
            if (is_null($value)) {
                continue;
            }
            
            //Hara Partners, Richu
            if($field == 'category_ids'){
            	continue;
            }

            $attribute = $this->getAttribute($field);
            if (!$attribute) {
                continue;
            }

            $isArray     = false;
            $setValue     = $value;

            if ($attribute->getFrontendInput() == 'multiselect') {
                //Harapartners, Jun, START
                if(!$value || empty($value)){
                    continue;
                }
                $value = explode(self::MULTI_DELIMITER, $value);
                foreach($value as &$valueEntry){
                    $valueEntry = trim($valueEntry);
                }
                //Harapartners, Jun, END
                $isArray = true;
                $setValue = array();
            }

            if ($value && $attribute->getBackendType() == 'decimal') {
                $setValue = $this->getNumber($value);
            }
            
            if($field == "weight"){
            	$setValue = $value;
            }
            
			$optionExistFlag = true;
            if ($attribute->usesSource()) {
                $options = $attribute->getSource()->getAllOptions(false);
				
                if ($isArray) {
                    foreach ($options as $item) {
                        if (in_array($item['label'], $value)) {
                            $setValue[] = $item['value'];
                        }
                    }
                } else {
                    $optionExistFlag = false;
                    foreach ($options as $item) {
                        if (is_array($item['value'])) {
                            foreach ($item['value'] as $subValue) {
                                if (isset($subValue['value']) && $subValue['value'] == $value) {
                                    $setValue = $value;
                                    $optionExistFlag = true;
                                }
                            }
                        }else if($item['value'] == $value){ // Check for attribute value as well -- Hara Quan
                        	$setValue = $value;
                        	$optionExistFlag = true;       
                        } else if ($item['label'] == $value) {
                            $setValue = $item['value'];
                            $optionExistFlag = true;    
                        }
                    }
                }
            }

            //Hara Partners, Richu
            if((!isset( $setValue ) || $optionExistFlag == false) && !!$value){
                $message = 'Attribute \''.$field.'\' has options that does not exists.';
                Mage::throwException($message);
            }else{
                $product->setData($field, $setValue);
            }
            
        }

        if (!$product->getVisibility()) {
            $product->setVisibility(Mage_Catalog_Model_Product_Visibility::VISIBILITY_NOT_VISIBLE);
        }

        $stockData = array();
        $inventoryFields = isset($this->_inventoryFieldsProductTypes[$product->getTypeId()])
            ? $this->_inventoryFieldsProductTypes[$product->getTypeId()]
            : array();
        foreach ($inventoryFields as $field) {
            if (isset($importData[$field])) {
                if (in_array($field, $this->_toNumber)) {
                    $stockData[$field] = $this->getNumber($importData[$field]);
                } else {
                    $stockData[$field] = $importData[$field];
                }
            }
        }
        $product->setStockData($stockData);

        $mediaGalleryBackendModel = $this->getAttribute('media_gallery')->getBackend();

        $arrayToMassAdd = array();
        
            // Hara Song: Remove all  images before setting

        $mediaGallery = $product->getMediaGallery();
        $mediaGallery = $mediaGallery['images'];
        
        foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
            if (isset($importData[$mediaAttributeCode])) {
                $file = trim($importData[$mediaAttributeCode]);
                $imageExist = false;
                if (!empty($file) && !$mediaGalleryBackendModel->getImage($product, $file)){                	
       				foreach($mediaGallery as $image){
       					if($image['file'] == $product->getOrigData($mediaAttributeCode) ){
       						unset($importData[$mediaAttributeCode]);
       						$imageExist = true;
       						break;
       					}
       				}
       				if(!$imageExist)       			
                    	$arrayToMassAdd[] = array('file' => trim($file), 'mediaAttribute' => $mediaAttributeCode);
                }
            }
        }        
          // Hara Song: Remove all  images before setting end
          
        $addedFilesCorrespondence = $mediaGalleryBackendModel->addImagesWithDifferentMediaAttributes(
            $product,
            $arrayToMassAdd, Mage::getBaseDir('media') . DS . 'import',
            false,
            false
        );

        foreach ($product->getMediaAttributes() as $mediaAttributeCode => $mediaAttribute) {
            $addedFile = '';
            if (isset($importData[$mediaAttributeCode . '_label'])) {
                $fileLabel = trim($importData[$mediaAttributeCode . '_label']);
                if (isset($importData[$mediaAttributeCode])) {
                    $keyInAddedFile = array_search($importData[$mediaAttributeCode],
                        $addedFilesCorrespondence['alreadyAddedFiles']);
                    if ($keyInAddedFile !== false) {
                        $addedFile = $addedFilesCorrespondence['alreadyAddedFilesNames'][$keyInAddedFile];
                    }
                }

                if (!$addedFile) {
                    $addedFile = $product->getData($mediaAttributeCode);
                }
                if ($fileLabel && $addedFile) {
                    $mediaGalleryBackendModel->updateImage($product, $addedFile, array('label' => $fileLabel));
                }
            }
        }
        
         
        //Hara Partners, Richu media_gallery Start
        if (!empty($importData['media_gallery'])) {
        	$mediaGalleryImages = explode(self::DEFAULT_FIELD_DELIMITER, $importData['media_gallery']);
        	foreach($mediaGalleryImages as $imageAttr){
        		$imageAttr = explode(self::DEFAULT_ATTR_DELIMITER, $imageAttr);
        		$imageFile = trim($imageAttr[0]);
        		$imageLabel = trim($imageAttr[1]);
        		$file = Mage::getBaseDir('media') . DS . 'import' . DS . $imageFile;
        		if(!empty($file) && file_exists($file)){
        			$mediaGalleryBackendModel->addImageWithLabel($product,$file, null,$imageLabel, false, false);
        		}
        	}
        }
        //Hara Partners, Richu media_gallery End
        //Hara Partners, Quan Start
     	if ( true ||!empty($importData['tier_prices']) ){
     		$this->setTierPrices( $product,$importData );
     	}
        $product->setIsMassupdate(true);
        $product->setExcludeUrlRewrite(true);

        $product->save();
		//Set price label for config Start
		$productSavedId = Mage::getModel('catalog/product')->getIdBySku($importData['sku']);
		$product = Mage::getModel('catalog/product')->load($productSavedId);
		if($product->getTypeId() == 'configurable'){
			$this->setConfigurableProductPriceLabel($product, $importData);
		}
		//Set price label for config End

        if(!!$product->getData('redirect_url')){
        	$this->_setRedirectUrl($product);
        }
        // Store affected products ids
        $this->_addAffectedEntityIds($product->getId());

        return true;
    }

    
    protected function _getProductIdFromSku($configurableSkusData,$product) {
        $productIds = array();
        $configurableSkus = explode(',', str_replace(" ", "", $configurableSkusData));
        foreach ($configurableSkus as $productSku) {
            if (($sku = (int)$product->getIdBySku($productSku)) > 0) {
                parse_str("position=", $productIds[$sku]);
            }
        }
        return $productIds;
    }
    
    protected function _setRedirectUrl( $product ) {
    	    	
    	$coreUrlRewrite	= Mage::getModel( 'core/url_rewrite' );
    	
    	$productId		= $product->getData( 'entity_id' );
    	$productSku		= $product->getData( 'sku' );
    	$storeId 		= $product->getData( 'store_id' ); 	
    	$requestPath	= $product->getData( 'redirect_url' ); 	
    	$idPath			= 'product/redirect/' . $productSku;
    	$isSystem		= '0';
    	$options		= 'RP';
    	$targetPath		= strtolower( $product->getData( 'url_path' ) );
    	
    	$coreUrlRewrite = $coreUrlRewrite->loadByRequestPath($requestPath);
		if($coreUrlRewrite->getId() != null){
			return;
		}
		
    	$coreUrlRewrite->setStoreId( $storeId );
		$coreUrlRewrite->setIdPath( $idPath );
		$coreUrlRewrite->setRequestPath( $requestPath );
		$coreUrlRewrite->setTargetPath( $targetPath );
		$coreUrlRewrite->setIsSystem( $isSystem );
		$coreUrlRewrite->setOptions( $options );
		$coreUrlRewrite->setProductId( $productId );
	
		try {
			$coreUrlRewrite->save();
		} catch ( Exception $e ){
			Mage::throwException( $e->getMessage() );
		}
    }
    
    public function setTierPrices( $product, $importData ){
    	$tierData = explode( ";", trim( $importData['tier_prices'] ) );
    	foreach ($tierData as $tierArray){
    		$singleTier = explode( ",", trim( $tierArray ) );
  			$tierPrices[] = array(
                  'website_id'  => 0,
                  'cust_group'  => 32000, //Need to modify to const
                  'price_qty'   => $singleTier[0],
                  'price'       => $singleTier[1],
  				  'cost'		=> $singleTier[2]
            );
    		
    	}
		//Now we set the tier price and save the product
		$product->setTierPrice($tierPrices); 	
    }
    
    
    public function setConfigurableProductPriceLabel( $product, array $importData ){
    	 $optionPriceString =$importData['CONFIGURABLE_OPTION_PRICES'];
    	 if($optionPriceString){
	    	 $optionPrices = explode(";",trim($optionPriceString));
	    	 foreach ($optionPrices as $optionPrice){
	    	 	$optionPrice = explode("||",trim($optionPrice));
	    	 	$optionPriceArray[$optionPrice[0]][$optionPrice[1]] = $optionPrice[2];
	    	 }
		   	 if ($data = $product->getTypeInstance()->getConfigurableAttributesAsArray(($product))) {
			        foreach ($data as $attributeData) {
			            $id = isset($attributeData['id']) ? $attributeData['id'] : null;
			            $size = sizeof($attributeData['values']);
			            for($j=0; $j< $size ; $j++){
			                $attributeData['values'][$j]['pricing_value'] = $optionPriceArray[$attributeData['attribute_code']][$attributeData['values'][$j]['label']];
			            }	
			               $attribute = Mage::getModel('catalog/product_type_configurable_attribute')
			               ->setData($attributeData)
			               ->setId($id)
			               ->setStoreId($product->getStoreId())
			               ->setProductId($productid)
			               ->save();
			        }
			   }
    	 }
    }
}
