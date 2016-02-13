<?php
// ========== Init setup ========== //
error_reporting(-1);
require_once ('../../app/Mage.php');
Mage::app();

if($argc!=3){
    die('This takes two command like options. Orig CatId and FreshCatId');
}else{
    $catIdOld = $argv[1];
    $catIdNew = $argv[2];
}

$dupcats = new DupToNewRootCat ( );
$dupcats->setCatIds ( $catIdOld, $catIdNew);
$dupcats->duplicateIntoNew(true);

class DupToNewRootCat {
	protected $_oldRoot = null;
	protected $_newRoot = null;
	
	public function setCatIds($oldCatId, $newCatId = null) {
		if ($newCatId == null) {
			$newCatId = $this->_getNewRootCat ();
		}
		
		$this->_oldRoot = $oldCatId;
		$this->_newRoot = $newCatId;
	}
	
	protected function _getOldRootModel() {
		return Mage::getModel ( 'catalog/category' )->load ( $this->_oldRoot );
	}
	
	protected function _getNewRootModel() {
		return Mage::getModel ( 'catalog/category' )->load ( $this->_newRoot );
	}
	
	protected function _getNewRootCat(){
		echo 'Not Implimented yet';
		die();
	}
	
	protected function _getCloneCatModel($model) {
		$cloned = clone $model;
		return $cloned;
	}
	
	public function duplicateIntoNew($isSetPathMapping = true) {
		$oldRoot = $this->_getOldRootModel ();
		$newRoot = $this->_getNewRootModel ();
		$this->_putChildrenIntoNew ( $oldRoot, $newRoot, $isSetPathMapping );
	}
	
	protected function _putChildrenIntoNew($oldCat, $newCat, $isSetPathMapping) {
		$children = $oldCat->getChildrenCategories ();
		$newParentId = $newCat->getId ();
		$newBasePath = $newCat->getPath();
		if (count ( $children )) {
			foreach ( $children as $subChild ) {
				$newModel = $this->_getCloneCatModel ( $subChild );
				$newModel->setId ();
				$newModel->setRequestPath();
				$newModel->setPath($newBasePath);
				$newModel->setParentId ( $newParentId );
				$newModel->setData ( 'redirect_category_info', $subChild->getId () );
				$newModel->save ();
				

				$this->_putChildrenIntoNew ( $subChild, $newModel, $isSetPathMapping );
			}
		}
	}
}