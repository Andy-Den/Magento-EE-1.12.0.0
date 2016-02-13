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

class Harapartners_Vendoroptions_Model_Source_Vendor extends Mage_Eav_Model_Entity_Attribute_Source_Abstract {
	
	protected $_cachedValues;
	protected $_cachedCodeValues;
	//Code value lookup is a historical support function to convert old product data into new schema

	//Critical logic to make the product attribute compatible with catalog flat tables! START
	public function getFlatColums() {
        $attributeCode = $this->getAttribute()->getAttributeCode();
        $column = array(
            'unsigned'  => true,
            'default'   => null,
            'extra'     => null
        );
        if (Mage::helper('core')->useDbCompatibleMode()) {
            $column['type']     = 'int(10)';
            $column['is_null']  = true;
        } else {
            $column['type']     = Varien_Db_Ddl_Table::TYPE_INTEGER;
            $column['nullable'] = true;
            $column['comment']  = 'Haraparners ' . $attributeCode . ' column';
        }
        return array($attributeCode => $column);
    }

    public function getFlatUpdateSelect($store) {
        return Mage::getResourceModel('eav/entity_attribute')->getFlatUpdateSelect($this->getAttribute(), $store);
    }
    //Critical logic to make the product attribute compatible with catalog flat tables! END
    
	public function getValues() {
		if($this->_cachedValues === null){
			//Initialized, default no brand
			$this->_cachedValues = array(
					0 => ''
			);
			foreach(Mage::getModel('vendoroptions/vendoroptions_configure')->getCollection() as $vendoroptions){
				$this->_cachedValues[$vendoroptions->getId()] = $vendoroptions->getName();
			}
		}
        return $this->_cachedValues;
    }
    
	public function getCodeValues() {
		if($this->_cachedCodeValues === null){
			//Initialized, default no brand
			$this->_cachedCodeValues = array(
					0 => ''
			);
			foreach(Mage::getModel('vendoroptions/vendoroptions_configure')->getCollection() as $vendoroptions){
				$this->_cachedCodeValues[$vendoroptions->getId()] = $vendoroptions->getCode();
			}
		}
        return $this->_cachedCodeValues;
    }
	
    public function getAllOptions() {
        $result = array();
        foreach ($this->getValues() as $k => $v) {
            $result[] = array(
                'value' => $k,
                'label' => $v,
            );
        }
        return $result;
    }

    public function getOptionText($value) {
        $options = $this->getValues();
        $optionText = '';
        
        //Is used as multi-select, the input would be an array
        if(is_array($value)){
        	$optionsMultiSelectArray = array();
        	foreach($value as $optionValue){
	        	if (isset($options[$optionValue])) {
		            $optionsMultiSelectArray[] = $options[$optionValue];
		        }
        	}
        	$optionText = implode(',', $optionsMultiSelectArray);
        }else{
	        if (isset($options[$value])) {
	            $optionText = $options[$value];
	        }
        }
        return $optionText;
    }

}