<?php
$addParent = new AddToParentCats();
$addParent->_initMage();
$addParent->addAllProductsToTheirParents();

class AddToParentCats {
	protected $_catIdToParents = array ();
	
	public function _initMage() {
		require_once ('../../app/Mage.php');
		Mage::app ();
	}
	
	public function addAllProductsToTheirParents() {
		/* @var $productCollection Mage_Catalog_Model_Resource_Product_Collection */
		$productCollection = Mage::getModel ( 'catalog/product' )->getCollection ();
		
		/* @var $connection Varien_Adapter_Interface */
		$connection = Mage::getSingleton('core/resource')->getConnection('write');
		$tableName = Mage::getModel('importexport/import_proxy_product_resource')->getProductCategoryTable();
		
		echo '-Getting Count' . PHP_EOL;
		$totalCount = $productCollection->getSize();
		$pageSize = 500;
		$pages = ceil ( $totalCount / $pageSize );
		
		echo "-Total Count: {$totalCount}. In {$pages} pages" . PHP_EOL;
		for($currentPage = 1; $currentPage <= $pages; $currentPage ++) {
		echo "-START Page {$currentPage}" . PHP_EOL;
			$productCollection->clear ();
			$productCollection->setPage ( $currentPage, $pageSize );
			$productCollection->load ()->addCategoryIds ();
			foreach ( $productCollection as $product ) {
				$productId = $product->getId();
			
				/* @var $product Mage_Catalog_Model_Product */
				$productCatIds = $product->getCategoryIds ();
				$productCatIdsStore = $productCatIds;
				
				$parentIds = $this->_getParentCategoriesById ( $productCatIds [0] );
				unset ( $parentIds [0] ); // Unset Store Hidden Root
				foreach ( $parentIds as $parentId ) {
					$productCatIds [] = $parentId;
					$categoriesIn[] = array('product_id' => $productId, 'category_id' => $parentId, 'position' => 1);
				}
				
				// Nothing will happen if Already in Cats??
				////$product->setCategoryIds ($productCatIds);
				////$product->save ();
				echo $product->getId() .' Was in: '. implode($productCatIdsStore ,' ') . '. Will be In: ' . implode($productCatIds ,' ') . PHP_EOL;
			}
			
		$connection->insertOnDuplicate($tableName, $categoriesIn, array('position'));
		//echo(print_r($categoriesIn,true));
		echo "-END Page {$currentPage}" . PHP_EOL;
		}
	
	}
	
	protected function _getParentCategoriesById($catId) {
		if (! isset ( $this->_catIdToParents [$catId] )) {
			$catModel = Mage::getModel ( 'catalog/category' )->load ( $catId );
			$parentIds = $catModel->getPath();
			$pathArray = explode( '/', $parentIds );
			array_pop($pathArray);
			$this->_catIdToParents [$catId] = $pathArray;
		}
		
		return $this->_catIdToParents [$catId];
	}

}
