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
class Harapartners_CatalogExtension_Model_Rewrite_Catalog_Category extends Mage_Catalog_Model_Category {

    public function getUrl() {
        //redirect_category_id is not a default attibute, make sure it's loaded
        if (! ($redirectCategoryInfo = $this->getData('redirect_category_info'))) {
            $redirectCategoryInfo = $this->getResource()->getAttributeRawValue($this->getId(), 'redirect_category_info', $this->getStoreId());
        }
        
        if (! ! $redirectCategoryInfo) {
            if (is_numeric($redirectCategoryInfo)) {
                $redirectCategory = Mage::getModel('catalog/category')->load($redirectCategoryInfo);
                if (! ! $redirectCategory && ! ! $redirectCategory->getId()) {
                    return $redirectCategory->getUrl();
                }
            } elseif (is_string($redirectCategoryInfo)) {
                return $redirectCategoryInfo;
            }
        }
        return parent::getUrl();
    }
    
	public function getLayoutUpdateHandle() {
        $layout = 'catalog_category_';
		if($this->getVendorId()){
        	$layout .= 'brand';
        }elseif ($this->getIsAnchor()) {
            $layout .= 'layered';
        }else {
            $layout .= 'default';
        }
        return $layout;
    }

}