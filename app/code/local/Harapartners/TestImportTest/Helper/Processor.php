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

class Harapartners_Import_Helper_Processor extends Mage_Core_Helper_Abstract {
	
	const DEFAULT_DATAFLOW_PROFILE_ID 					= 7;
	const DEFAULT_PRODUCT_STORE_CODE	 				= 'admin';
	const DEFAULT_PRODUCT_TYPE			 				= 'simple';
	const DEFAULT_PRODUCT_WEBSITE_CODE	 				= 'base';
	const DEFAULT_PRODUCT_ATTRIBUTE_SET 				= 'model';
	const DEFAULT_PRODUCT_STATUS		 				= 'Enabled';
	const DEFAULT_PRODUCT_TAX_CLASS		 				= 'Taxable Goods';
	const DEFAULT_PRODUCT_SHORT_DESCRIPTION				= '';//'Welcome to IBuy Store!';
	const DEFAULT_PRODUCT_DESCRIPTION					= '';//'Welcome to IBuy Store!';
	const DEFAULT_PRODUCT_WEIGHT						= '0.0'; 	//Note all fields MUST be text
	const DEFAULT_PRODUCT_IS_IN_STOCK					= '1'; 		//Note all fields MUST be text
	const DEFAULT_PRODUCT_PRICE							= '0.0'; 	//Note all fields MUST be text
	
	const PRODUCT_SKU_MAX_LENGTH						= 17; 		//Restricted by DotCom
	const CONFIGURABLE_ATTRIBUTE_CODE					= 'color,size';
	
	const DEFAULT_VISIBILITY							= 'Catalog, Search';
	const DEFAULT_STATUS								= 'Enabled';
	const DEFAULT_DESCRIPTION							= 'Please add description';
	const DEFAULT_ATTRIBUTE_SET							= 'Default';
	const DEFAULT_IS_IN_STOCK							= 1;
	
	protected $_errorFilePath 				= null;
	protected $_errorFileWebPath 			= null;
	protected $_errorMessages 				= array();
	protected $_requiredFields 				= array();
	protected $_confSimpleProducts 			= array();
	
	public function __construct(){
		$this->_errorFilePath = BP.DS.'var'.DS.'log'.DS.'import_error'.DS;
		$this->_errorFileWebPath = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'var/log/import_error/';
	}
	
	
	// ================================================================== //
	// ===== Entry Points =============================================== //
	// ================================================================== //
	
	public function runDataflowProfile($filename){	
		$profile = Mage::getModel('dataflow/profile')->load(self::DEFAULT_DATAFLOW_PROFILE_ID);

		if (!!$profile && !!$profile->getId()) {
		    $gui_data = $profile->getData('gui_data');
		    $gui_data['file']['filename'] = $filename;
		    $profile->setData('gui_data', $gui_data);
		    $profile->save();
		}else{
			throw new Exception('The profile you are trying to save no longer exists');
		  	Mage::getSingleton('adminhtml/session')->addError('The profile you are trying to save no longer exists');
		}
		
		$importPath = str_replace('/', DS , $gui_data['file']['path']);
		//$filenameVar = str_replace(' ', '_' , $filename);
		$delimiter = ',';
		$importCsvFile 	= BP .DS .$importPath .DS . $filename;
		if(($handle = fopen($importCsvFile,'r')) !== FALSE){
			$data = fgetcsv($handle, 4096, $delimiter, '"');
			if( count($data)!= count(array_unique($data))){
				throw new Exception('Import File has duplicate entry, please delete duplicated columns before processing.');
			}
			fclose($handle);
		}
		else{
			throw new Exception('Unable to open file, please check.');
		}
//		Mage::register('current_convert_profile', $profile);
		$profile->run();
		$batchModel = Mage::getSingleton('dataflow/batch');
		if ($batchModel->getId()) {
		  	if ($batchModel->getIoAdapter()) {
		  		$batchId = $batchModel->getId();
				return $batchId;
		  	}
		}
		return null;
    }
    
	public function runImport($importObjectId = null, $shouldRunIndex = false){
		$importObject = $this->_getUploadedImportModel($importObjectId);
		if(!$importObject || !$importObject->getId() || !$importObject->getData('import_batch_id')){
			//Nothing to run
			$this->_errorMessages[] = "Nothing to run!" . "\n";
		}else{
			
			// ===== disable indexing for better performance ===== //
			if(!$shouldRunIndex){
				Mage::unregister('batch_import_no_index');
				Mage::register('batch_import_no_index', true);
				//Note catalog URL rewrite is always refreshed after product save: afterCommitCallback()
			}
			
			// ===== dataflow, processing ===== //
			try{
				$batchModel = Mage::getModel('dataflow/batch')->load($importObject->getData('import_batch_id'));
				if (!!$batchModel && !!$batchModel->getId()){
					$batchImportModel = $batchModel->getBatchImportModel(); //read line item
					$adapter = Mage::getModel($batchModel->getAdapter()); //processor/writer
					
					//update status to 'lock' this import
					$importObject->setStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_PROCESSING);
					$importObject->save();
					
					//collection load is not possible due to the large amount of data per row
					$batchId = $batchModel->getId();  
					$importObjectIds = $batchImportModel->getIdCollection($batchId);
					
					//Get the required fields
					$this->_prepareRequiredFields();
					$row = 2; //Skip the header row
					foreach ($importObjectIds as $importObjectId) {	
						try{
							$batchImportModel->load($importObjectId);
							if (!$batchImportModel || !$batchImportModel->getId()) {
								$this->_errorMessages[] = Mage::helper('dataflow')->__('Skip undefined row ' . $row . "\n");
								continue;	
							}
							$importData = $batchImportModel->getBatchData();
							foreach ($importData as $key => $value) {
							   if(preg_match('/^\_/',$key)){							
							   $new_key = preg_replace('/^\_/','',$key);					   						
							   $importData[$new_key] = $value;
							   unset($importData[$key]);
							   }
							}
							
							$importData = $this->_importDataCleaning($importData, $importObject);
							$adapter->saveRow($importData);
							
						} catch(Exception $ex) {
							$this->_errorMessages[] = 'Error in row ' . $row . ', ' . $ex->getMessage() . "\n";
						}  
							$row++;
					}
				}
				$batchModel->delete();
			}catch(Exception $ex){
				$this->_errorMessages[] = $ex->getMessage() . "\n";
				$this->_errorMessages[] = "Execution terminated!" . "\n";
			}
		}
		
		//Clean up and error handling
		if(count($this->_errorMessages)){
			array_unshift($this->_errorMessages[], "Please make sure the header row has all required fields. All contents are case sensitive.");
			$filename = $this->_logErrorToFile();
			$importObject->setStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_ERROR);
			$importObject->setErrorMessage('<a href="' . $this->_errorFileWebPath . $filename . '">Error</a>');
			$importObject->save();
			Mage::throwException('There is an error processing the uploaded data. Please check the error log.');
		}
		
		$importObject->setStatus(Harapartners_Import_Model_Import::IMPORT_STATUS_COMPLETE);
		$importObject->save();
		
		return true;
	}
	
	
	// ================================================================== //
	// ===== Data Cleaning ============================================== //
	// ================================================================== //
	protected function _importDataCleaning($importData, $importObject){
		
	
		/**
		 * Hara Partners, Richu
		 * remove special characters from description and short_description
		 */
		
		if ( isset( $importData['description'] ) ) {
			$importData['description'] = preg_replace( '/[^(\x20-\x7F)]*/','', $importData['description'] );
		}
		
		if ( isset( $importData['short_description'] ) ) {
			$importData['short_description'] = preg_replace( '/[^(\x20-\x7F)]*/','', $importData['short_description'] );
		}
		// trim the data
		foreach($importData as $key => $value){
			$importData[$key] = trim($value);
		}
		unset($value);
		//retrive vendor data
		$vendorId = $importObject->getData('vendor_id');
		
//		if(!$importObject->getData('category_id')){
//			throw new Exception('category_id is required');
//		}
//		$importData['category_ids'] = $importObject->getData('category_id');	
		$product = Mage::getModel('catalog/product');		
		$productId = $product->getIdBySku($importData['sku']);
		if($productId){
			$product = Mage::getModel('catalog/product')->load($productId);
		// --- Vendor Data Import Logic Update Product --- //		
			if(!!$vendorId){
				if( $product->getData('vendor_id')!=$vendorId ){
					throw new Exception('Product ' . $product->getSku() . ' could not be modified');
				}
				foreach($importData as $key => $value){
					if($key !== 'type' && $key !== 'id' && $key !== 'product_id'){
						unset($importData[$key]);
						$key = Harapartners_HpVendor_Helper_Data::VENDOR_ATTRIBUTE_PREFIX . $key; 
						$importData[$key] = $value;
					}
				}
			}
			foreach ($this->_requiredFields as $field) {
				if (!isset($importData[$field]) || empty($importData[$field])){
					switch ($field) {
						case 'store':
							$importData['store'] = self::DEFAULT_PRODUCT_STORE_CODE;
							break;
						case 'product_type':
							$importData['product_type'] = $product->getData('type_id');
							break;
						case 'attribute_set':
							$importData['attribute_set'] = Mage::getModel('eav/entity_attribute_set')->load($product->getAttributeSetId())->getAttributeSetName();
							break;
						case 'weight':
							if($product->getData('weight') == '0')
								$importData['weight'] ='0';
							else
								$importData['weight'] = $product->getData('weight');
							break;
						case 'price':
							if($product->getData('price') == '0')
								$importData['price'] ='0';
							else
								$importData['price'] = $product->getData('price');
							break;
							
						case 'tax_class_id':
							$importData['tax_class_id'] = self::DEFAULT_PRODUCT_TAX_CLASS;
							break;
						case 'is_in_stock':
							$importData['is_in_stock'] = self::DEFAULT_IS_IN_STOCK;
							break;
//						case 'product_websites':
//							$websiteIds = $product->getWebsiteIds();
//							if(count($websiteIds)==1){
//								$websiteCodes = Mage::getModel('core/website')->load($websiteIds[0])->getCode();
//							}
//							else{
//								foreach ($websiteIds as $websiteId){									
//									$websiteCodes = $websiteCodes + ","+ Mage::getModel('core/website')->load($websiteId)->getWebsiteCode();
//								}
//							}
//							$importData['product_websites'] = $websiteCodes;
//							break;
						default:
							if( $product->getData($field) ){
								$importData[$field] = $product->getData($field);								
							}
							else{
								throw new Exception($field . ' is required.');
							}
							break;
					}
				}
			}
			//----ignore empty field----//
			foreach($importData as $key => $value){
				if( $key !== '' && $value == ''){
					unset($importData[$key]);
				}
			}
		}			
		else{
		// ----- Data from Import Form ----- //
			foreach ($this->_requiredFields as $field ) {
				if ( !isset( $importData[$field] ) || empty( $importData[$field] ) ) {
					switch ( $field ) {
						case 'store':
							$importData['store'] = self::DEFAULT_PRODUCT_STORE_CODE;
							break;
						case 'weight':
							$importData['weight'] = self::DEFAULT_PRODUCT_WEIGHT;
							break;
						case 'price':
							$importData['price'] = self::DEFAULT_PRODUCT_PRICE;
							break;
						case 'tax_class_id':
							$importData['tax_class_id'] = self::DEFAULT_PRODUCT_TAX_CLASS;
							break;
						case 'sku':
							$importData['sku'] = $this->_generateRandomSku($importData);
							break;
						case 'status':
							$importData['status'] = self::DEFAULT_STATUS;
							break;
						case 'visibility':
							$importData['visibility'] = self::DEFAULT_VISIBILITY;
							break;
						case 'description':
							$importData['description'] = self::DEFAULT_DESCRIPTION;
							break;
						case 'short_description':
							$importData['short_description'] = self::DEFAULT_DESCRIPTION;
							break;
						case 'attribute_set':
							$importData['attribute_set'] = self::DEFAULT_ATTRIBUTE_SET;
							break;
						case 'is_in_stock':
							$importData['is_in_stock'] = self::DEFAULT_IS_IN_STOCK;
							break;
						default:
							throw new Exception($field . ' is required.');
							break;
					}
				}
			}
			// --- Vendor Data Import Logic New Product --- //		
			if(!!$vendorId){
				$importData['vendor_id'] = $vendorId;
				$importData['status'] = 'Disabled'; 
			}				
		}
		// ----- Configurable/Simple products ----- // 
		if($importData['product_type'] == 'configurable'){
			if(array_key_exists('configurable_attribute_codes', $importData) && !$importData['configurable_attribute_codes']){
				$importData['configurable_attribute_codes'] = self::CONFIGURABLE_ATTRIBUTE_CODE; //Hard Coded.  Need to enforce in template!
			}
			$importData['conf_simple_products'] = implode(',', $this->_confSimpleProducts);
			$this->_hideAssociatedSimpleProducts();
			$this->_confSimpleProducts = array();
		}elseif($importData['product_type'] == 'grouped'){
			$importData['grouped_simple_products'] = implode(',', $this->_confSimpleProducts);
			$this->_hideAssociatedSimpleProducts();
			$this->_confSimpleProducts = array();
		}elseif($importData['product_type'] == 'bundle'){
			$importData['bundle_simple_products'] = implode(',', $this->_confSimpleProducts);
			$this->_hideAssociatedSimpleProducts();
			$this->_confSimpleProducts = array();
		}else{
			$this->_confSimpleProducts[] = $importData['sku'];
		}
		
		
		
		return $importData;
	}
	
	protected function _prepareRequiredFields(){
		$this->_requiredFields = array(
				//Magento core default
				 'store', 'product_type', 'attribute_set', 'sku', 'status', 'visibility', 'description', 'short_description', 'weight','is_in_stock'//,'store', 'product_websites',
		);
        
		//Additional dataflow fields
		$fieldset = Mage::getConfig()->getFieldset('catalog_product_dataflow', 'admin');
        foreach ($fieldset as $code => $node) {
        	if ($node->is('required')) {
        		if(!in_array($code, $this->_requiredFields)){
                	$this->_requiredFields[] = $code;
        		}
            }
        }
	}
	
	protected function _hideAssociatedSimpleProducts(){
		foreach ($this->_confSimpleProducts as $sku) {
			$productId = Mage::getModel('catalog/product')->getIdBySku($sku);
			$product = Mage::getModel('catalog/product')->load($productId);
			if(!!$product && $product->getId()){
				$product->setData('visibility', '1');
				$product->save(); //exceptions will be caught as _errorMessage
			}
		}
	}
	
	protected function _generateRandomSku($importData){
		$color = str_replace('/', '-', $importData['color']);
		$color = str_replace(' ', '-', $color);
		$sku = $importData[style] . $color . $importData['size']. base_convert(rand(0, base_convert('zzz', 36, 10)), 10, 36);
		return $sku;
	}
	
	protected function _generateProductSku($importData, $importObject){
		//$importObject must have 'vendor_id' here
		$sku = $importObject->getData('vendor_id') //The vendor_id is readable and can be 10^6 big
				. '-' . base_convert(time(), 10, 36) // 7 characters, including '-'
				. base_convert(rand(0, base_convert('zzz', 36, 10)), 10, 36); // 3 character
		$sku = substr($sku, 0, self::PRODUCT_SKU_MAX_LENGTH);
		return $sku;
	}
	
	
	// ================================================================== //
	// ===== Utilities ================================================== //
	// ================================================================== //
	protected function _getUploadedImportModel($importId = null){
		$import = Mage::getModel('import/import');
		if(!!$importId){
			$import->load($importId);
		}
		//If not specified, only runs the last 'uploaded' import
		if(!$import || !$import->getId()){
			$collection = Mage::getModel('import/import')->getCollection();
			$collection->addFieldToFilter('import_status', Harapartners_Import_Model_Import::IMPORT_STATUS_UPLOADED);
			$collection->getSelect()->limit(1);
			$import = $collection->getFirstItem();
		}
		if(!!$import 
				&& !!$import->getId()
				&& $import->getData('status') == Harapartners_Import_Model_Import::IMPORT_STATUS_UPLOADED
		){
			return $import;
		}else{
			return null;
		}
	}
	
//	}
	
	
	// ================================================================== //
	// ===== Error Logging ============================================== //
	// ================================================================== //
	protected function _logErrorToFile(){
		$filename = date('Y_m_d'). '_' . md5(time()) . '.txt';
		if(!is_dir($this->_errorFilePath)){
			mkdir($this->_errorFilePath, 0777);
		}		
		$errorFile = fopen($this->_errorFilePath . $filename, 'w');
		foreach($this->_errorMessages as $errorMessage){
			fwrite($errorFile, $errorMessage);
		}
		fclose($errorFile);
		return $filename;
	}
	
	
	
}