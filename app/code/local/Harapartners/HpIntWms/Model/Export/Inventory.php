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
class Harapartners_HpIntWms_Model_Export_Inventory {
	
	/**
	 * Generates the Info to be used by the DB insert
	 *
	 * @param string $sku
	 * @return array fillef info array
	 */
	public function generateItemInfo($sku) {
		$product = Mage::getModel ( 'catalog/product' )->loadByAttribute ( 'sku', $sku);
		if (! $product || ! $product->getId ()) {
			return null;
		}
		
		$info = array ();
		$info ['id'] = $product->getId ();
		$info ['name'] = $product->getName ();
		$info ['upc'] = $product->getUpc ();
		$info ['sku'] = $product->getSku ();
		// $info ['type'] = $product->getSku ();
		$info ['vendorCode'] = $product->getVendorCode ();
		$info ['vendorName'] = $product->getVendorName ();
		// $info ['sex'] = $product->getSku ();
		$info['cat'] = $product->getPath();
		$info['stockNumber']=$product->getStockNumber();
		$info['stockName']=$product->getStockName();
		$info['color']=$product->getShoeColorManu();
		$info['size']=$product->getShoeSize();
		$info['width']=$product->getShoeWidth();
		
		return $info;
	}
}
