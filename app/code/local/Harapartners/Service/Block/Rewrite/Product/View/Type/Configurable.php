<?php
/* NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 */
class Harapartners_Service_Block_Rewrite_Product_View_Type_Configurable extends Mage_Catalog_Block_Product_View_Type_Configurable {

	public function getAllowAttributes(){
		$allowAttributes = parent::getAllowAttributes();
		//Special sorting is performed
		//This is for shoe size option, the numerical values will be sorted and put on top, other values are keep in default order in the bottom
		foreach($allowAttributes as &$attribute){
			if($attribute->getProductAttribute()->getAttributeCode() != Harapartners_Service_Helper_Catalog::CONF_SHOE_SIZE_ATTRIBUTE_CODE){
				continue;
			}
			$sortedNumerical = array();
			$sortedAlphabetical = array();
			foreach($attribute->getPrices() as $price){
				if(is_numeric($price['label'])){
					$sortedNumerical[$price['label']] = $price;
				}else{
					$sortedAlphabetical[] = $price;
				}
			}
			uksort($sortedNumerical, array($this, "uksortByNumericalKey"));
			$sortedNumerical = array_values($sortedNumerical); //Important to reset the keys
			$attribute->setPrices(array_merge($sortedNumerical, $sortedAlphabetical));
		}
		return $allowAttributes;
	}
	
	public function uksortByNumericalKey($indexA, $indexB) {
		//Double comparison, get sign of the result
		$result = ((double) $indexA - (double) $indexB) * 1.0;
    	return min(1, max(-1, $result == 0 ? 0 : $result * INF));
	}
	
    protected function _validateAttributeInfo(&$info) {
        if (parent::_validateAttributeInfo($info)) {
            $this->_modifyAttributes($info);
            return true;
        }
        return false;
    }

    protected function _modifyAttributes(&$info){
        if ($info['code'] == Harapartners_Service_Helper_Catalog::UNMAP_ATTRIBUTE_CODE) {
            $confManuColorMapping = Mage::helper('service/catalog')->getConfManuColorMapping($this->getProduct());
            foreach ($info['options'] as &$optionInfo) {
            	$optionInfo['image_label'] = '';
				$optionInfo[$count]['image_url'] = '';
				if(isset($optionInfo['id']) && isset($confManuColorMapping[$optionInfo['id']])){
					$mappedInfo = $confManuColorMapping[$optionInfo['id']];
					$optionInfo['label'] = $mappedInfo['label'];
					$optionInfo['style_number'] = $mappedInfo['style_number'];
					$optionInfo['image_label'] = $mappedInfo['label'];
					$optionInfo['image_file'] = $mappedInfo['image_file'];
					$optionInfo['image_url'] = $mappedInfo['image_url'];
				}
            }
        }
    }
    
}