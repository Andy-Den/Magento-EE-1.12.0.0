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

class Harapartners_GiftWithPurchase_Model_Observer {
	
	//Triggered before collect total so that the gift item can be validated through collect total logic
	//Collect total is triggered very often, there should be a calculated subtotal from previous collect total
	public function salesQuoteCollectTotalsBefore($observer){
		$quote = $observer->getQuote();
		
		if(!Mage::getStoreConfig('checkout/giftwithpurchase/is_active')){
			return;
		}
		$productId = Mage::getStoreConfig('checkout/giftwithpurchase/product_id');
		$minimalSubtotal = Mage::getStoreConfig('checkout/giftwithpurchase/minimum_subtotal');
		$product = Mage::getModel('catalog/product')->load($productId);
		
		if(!$product->getId() || !$product->isSalable() || !$product->getData('is_gift_with_purchase_product')){
			return;
		}
		
		//If a gift with purchase product already in cart
		$giftWithPurchaseProductCount = 0;
		foreach($quote->getAllItems() as $quoteItem){
			//Ignore children products
			if($quoteItem->getParentItemId()){
				continue;
			}
			if($quoteItem->getProduct()->getData('is_gift_with_purchase_product')){
				$giftWithPurchaseProductCount += $quoteItem->getQty();
			}
		}
		if($giftWithPurchaseProductCount > 0){
			return;
		}
		
		if(!$quote->getSubtotal() || $quote->getSubtotal() < $minimalSubtotal){
			return;
		}
		try{
			$quote->addProduct($product);
			Mage::getSingleton('checkout/session')->addSuccess('A gift product has been added to your shopping cart.');
		}catch (Exception $e){
			//do nothing
		}
	}
	
}