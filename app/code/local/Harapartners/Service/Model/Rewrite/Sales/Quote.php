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
class Harapartners_Service_Model_Rewrite_Sales_Quote extends Mage_Sales_Model_Quote  {

	protected function _addCatalogProduct(Mage_Catalog_Model_Product $product, $qty = 1) {
		$item = parent::_addCatalogProduct($product, $qty);
		
		//Logic from HpVendor module, START:
		//Only valid IDs, excluding "0"!
		if($product->getVendorId()){
			$item->setVendorId($product->getVendorId());
		}
		//Logic from HpVendor module, END.
		
		
		//Logic from GiftWithPurchase module, START:
		if($product->getData('is_gift_with_purchase_product')){
			$item->setData('is_gift_with_purchase_product', $product->getData('is_gift_with_purchase_product'));
		}
		//Logic from GiftWithPurchase module, END.
		
        return $item;
    }
    
}