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
 */

class Harapartners_CatalogExtension_Block_Category_Brand extends Mage_Core_Block_Template {
	
	public function getChildrenCategories() {
		$childrenCategories = array();
		//Need to make sure collection has enough detailed information
		$category = $this->getCurrentCategory();
		if($category){
			$childrenCategories = $category->getCollection();
	        $childrenCategories->addAttributeToSelect('url_key')
	            ->addAttributeToSelect('name')
	            ->addAttributeToSelect('image')
	            ->addAttributeToSelect('thumbnail')
	            ->addAttributeToSelect('all_children')
	            ->addAttributeToSelect('is_anchor')
	            ->addAttributeToFilter('is_active', 1)
	            ->addIdFilter($category->getChildren())
	            ->setOrder('position', Varien_Db_Select::SQL_ASC)
	            ->joinUrlRewrite();
		}
		return $childrenCategories;
	}
	
	public function getCurrentCategory() {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', Mage::registry('current_category'));
        }
        return $this->getData('current_category');
    }
    
	public function getThumbnailFilePath($category) {
        $filePath = false;
        if ($thumbnailImage = $category->getThumbnail()) {
            $filePath = 'catalog' . DS . 'category' . DS . $thumbnailImage;
        }
        return $filePath;
    }
    
    public function getThumbnailUrl($category) {
        $url = '';
        
        try{
        $imageModel = Mage::getModel('service/image');
        $imageModel->setBaseFile($this->getThumbnailFilePath($category));
        $imageModel->setDestinationSubdir('thumbnail'); //important for cache path and placeholder
        //$imageModel->setWidth(Harapartners_Service_Helper_Catalog::BRAND_LIST_PAGE_BRAND_IMAGE_SIZE);
        //$imageModel->setKeepFrame(false);
        //$imageModel->resize();
        $url = $imageModel->saveFile()->getUrl();
        }catch (Exception $e){
        	//Image resize failed, do nothing
        }

        return $url;
    }
	
}