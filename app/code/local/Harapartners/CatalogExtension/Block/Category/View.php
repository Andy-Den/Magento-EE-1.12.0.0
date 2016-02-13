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

class Harapartners_CatalogExtension_Block_Category_View extends Mage_Catalog_Block_Product_Abstract {
	
	const MAX_CATEGORY_TRACE_LEVEL = 2;
	
	protected $_vendor;
	
	public function getVendor(){
		if($this->_vendor === null){
			$this->_vendor = Mage::getModel('vendoroptions/vendoroptions_configure')->load($this->getCurrentCategory()->getVendorId());
		}
		return $this->_vendor;
	}
	
	public function getCurrentCategory() {
        if (!$this->hasData('current_category')) {
            $this->setData('current_category', Mage::registry('current_category'));
        }
        return $this->getData('current_category');
    }
	
    public function getCategoryBrandFilterUrl($category, $vendorId = null){
    	if(!$vendorId){
    		$vendorId = $this->getVendor()->getId();
    	}
    	return $category->getUrl() . "?vendor_id=$vendorId";
    }
    
    public function getCatalogRootInfoArray(){
    	$catalogRootInfoArray = array();
    	$vendor = $this->getVendor();
		$catalogHelper = Mage::helper('service/catalog');
		//Test parameter
//		$catalogRootCategoryCollecion =  array(Mage::getModel('catalog/category')->load(226), Mage::getModel('catalog/category')->load(226));
		$catalogRootCategoryCollecion = $catalogHelper->getCatalogRootCategoryCollection();
		foreach($catalogRootCategoryCollecion as $catalogRootCategory){
			$productCollection = $catalogHelper->getCategoryProductCollectionByVendorId($catalogRootCategory, $vendor->getId());
			$productCollection->setPageSize(Harapartners_Service_Helper_Catalog::PRODUCT_BRAND_PAGE_GRID_LIMIT);
			if($productCollection->getSize() == 0){
				continue;
			}
			$catalogRootInfoArray[] = array(
				'category_object' => $catalogRootCategory,
				'product_collection' => $productCollection
			);
		}
		return $catalogRootInfoArray;
    }
    
	public function getMappedCategoryProductHtml() {
		$outputHtml = "";
		$headerHtml = "";
		$bodyHtml = "";
		$mappedCategoryProductInfo = $this->getMappedCategoryProductInfo();
		foreach($mappedCategoryProductInfo as $topCategory){
			$innerItemCount = 0;
			$innerHtml = "";
			foreach($topCategory['sub_categories'] as $subCategory){
				$innerItemCount += $subCategory['count'];
				$innerHtml .= "<div class=\"mapped-category-level-1\">";
				$innerHtml .= "<a href=\"{$subCategory['url']}\">{$subCategory['name']}<span class=\"count\">({$subCategory['count']})</span></a>";
				foreach($subCategory['sub_categories'] as $subSubCategory){
					$innerHtml .= "<div class=\"mapped-category-level-2\"><a href=\"{$subSubCategory['url']}\">{$subSubCategory['name']}<span class=\"count\">({$subSubCategory['count']})</span></a></div>";
				}
				$innerHtml .= "</div>";
			}
			if($innerItemCount > 0){
				$headerHtml .= "<div class=\"mapped-category-level-0\" category_id=\"{$topCategory['id']}\">{$topCategory['name']}</div>";
				$bodyHtml .= "<div class=\"mapped-category-tab\" category_id=\"{$topCategory['id']}\">$innerHtml</div>";
			}
		}
		
		$outputHtml .= "<div class=\"mapped-category-header\">$headerHtml</div>";
		$outputHtml .= "<div class=\"clear\"></div>";
		$outputHtml .= "<div class=\"mapped-category-body\">$bodyHtml</div>";
		$outputHtml .= "<div class=\"clear\"></div>";
		return $outputHtml;
	}
	
	public function getMappedCategoryProductInfo(){
		$mappedCategoryProductInfo = array();
		$vendor = $this->getVendor();
		$catalogHelper = Mage::helper('service/catalog');
		$catalogRootCategoryCollecion = $catalogHelper->getCatalogRootCategoryCollection();
		foreach($catalogRootCategoryCollecion as $catalogRootCategory){
			//Some catalog root may be empty
//			$productCount = $catalogHelper->getCategoryProductCollectionByVendorId($catalogRootCategory, $vendor->getId())->getSize();
//			if($productCount == 0){
//				continue;
//			}
			$mappedCategoryProductInfo[$catalogRootCategory->getId()] = array(
					'id' => $catalogRootCategory->getId(),
					'name' => $catalogRootCategory->getName(),
					'url' => '',
					'count' => $productCount,
					'sub_categories' => array()
			);
			foreach($catalogRootCategory->getChildrenCategories() as $subCategory){
				$productCount = $catalogHelper->getCategoryProductCollectionByVendorId($subCategory, $vendor->getId())->getSize();
				if($productCount == 0){
					continue;
				}
				$mappedCategoryProductInfo[$catalogRootCategory->getId()]['sub_categories'][$subCategory->getId()] = array(
						'id' => $subCategory->getId(),
						'name' => $subCategory->getName(),
						'url' => $this->getCategoryBrandFilterUrl($subCategory, $vendor->getId()),
						'count' => $productCount,
						'sub_categories' => array()
				);
				foreach($subCategory->getChildrenCategories() as $subSubCategory){
					$productCount = $catalogHelper->getCategoryProductCollectionByVendorId($subSubCategory, $vendor->getId())->getSize();
					if($productCount == 0){
						continue;
					}
					$mappedCategoryProductInfo[$catalogRootCategory->getId()]['sub_categories'][$subCategory->getId()]['sub_categories'][$subSubCategory->getId()] = array(
							'id' => $subSubCategory->getId(),
							'name' => $subSubCategory->getName(),
							'url' => $this->getCategoryBrandFilterUrl($subSubCategory, $vendor->getId()),
							'count' =>  $productCount,
							'sub_categories' => array()
					);
				}
			}
		}
		return $mappedCategoryProductInfo;
	}
	
}