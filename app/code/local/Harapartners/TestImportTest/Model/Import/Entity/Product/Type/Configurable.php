<?php
/**
 * Import entity configurable product type model
 * FIXED BUG on Massive product list
 *
 * @category    Harapartners
 * @package     Harapartners_TestImportTest
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 */
class Harapartners_TestImportTest_Model_Import_Entity_Product_Type_Configurable extends Mage_ImportExport_Model_Import_Entity_Product_Type_Configurable {
	/**
	 * This Function is extended to do nothing
	 * Product Attributes are loaded in _loadSkuSuperAttributeValuesBatch($productIdArray)
	 *
	 * @return Harapartners_TestImportTest_Model_Import_Entity_Product_Type_Configurable1234
	 */
	protected function _loadSkuSuperAttributeValues() {
		// This overwrites to do null
		// Product Attributes are loaded in _loadSkuSuperAttributeValuesBatch($productIdArray)
		return $this;
	}
	
	/**
	 * This is the same as 1.13's version of _loadSkuSuperAttributeValues
	 * It just uses $productIdArray to only get Relevent Products Info
	 * NOTE the "->addFieldToFilter('entity_id', array('in' => $productIdArray))"
	 *
	 * @param array $productIdArray
	 * @return Harapartners_TestImportTest_Model_Import_Entity_Product_Type_Configurable1234
	 */
	protected function _loadSkuSuperAttributeValuesBatch(array $productIdArray) {
		if ($this->_superAttributes) {
			$attrSetIdToName = $this->_entityModel->getAttrSetIdToName ();
			$allowProductTypes = array ();
			
			foreach ( Mage::getConfig ()->getNode ( 'global/catalog/product/type/configurable/allow_product_types' )->children () as $type ) {
				$allowProductTypes [] = $type->getName ();
			}
			foreach ( Mage::getResourceModel ( 'catalog/product_collection' )->addFieldToFilter ( 'type_id', $allowProductTypes )->addFieldToFilter ( 'entity_id', array ('in' => $productIdArray ) )->addAttributeToSelect ( array_keys ( $this->_superAttributes ) ) as $product ) {
				$attrSetName = $attrSetIdToName [$product->getAttributeSetId ()];
				
				$data = array_intersect_key ( $product->getData (), $this->_superAttributes );
				foreach ( $data as $attrCode => $value ) {
					$attrId = $this->_superAttributes [$attrCode] ['id'];
					$this->_skuSuperAttributeValues [$attrSetName] [$product->getId ()] [$attrId] = $value;
				}
			}
		}
		return $this;
	}
	
	/**
	 * This only loads the Related Ids. This can hit a bit extra.
	 * This is cleanest in terms of Function Rewrites though.
	 * Also the large size can Cause disk thrash to Mysql
	 *
	 * @param array $superData
	 * @param array $superAttributes
	 * @return Mage_ImportExport_Model_Import_Entity_Product_Type_Configurable
	 */
	protected function _processSuperData(array $superData, array &$superAttributes) {
		if ($superData && count ( $superData ['assoc_ids'] ) != 0) {
			// Load up the relevent Product Entity Ids
			foreach ( $superData ['assoc_ids'] as $assocId => $isUsed ) {
				$productIdArray [] = $assocId;
			}
			
			// Load the Product Ids for this bunch
			$this->_loadSkuSuperAttributeValuesBatch ( $productIdArray );
		}
		// Call Parent Like regular Configurable.
		return parent::_processSuperData ( $superData, $superAttributes );
	}
}
