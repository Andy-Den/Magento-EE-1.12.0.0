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
class Harapartners_Service_Model_Rewrite_Catalog_Product_Type_Configurable_Price extends Mage_Catalog_Model_Product_Type_Configurable_Price {

    /**
     * Get product final price
     *
     * @param   double $qty
     * @param   Mage_Catalog_Model_Product $product
     * @return  double
     */
    public function getFinalPrice($qty=null, $product)
    {
        if (is_null($qty) && !is_null($product->getCalculatedFinalPrice())) {
            return $product->getCalculatedFinalPrice();
        }

        $basePrice = $this->getBasePrice($product, $qty);
        $finalPrice = $basePrice;
        $product->setFinalPrice($finalPrice);
        Mage::dispatchEvent('catalog_product_get_final_price', array('product' => $product, 'qty' => $qty));
        $finalPrice = $product->getData('final_price');

        $finalPrice = $this->getTotalConfigurableItemsPrice($product, $finalPrice, $qty);
        $finalPrice += $this->_applyOptionsPrice($product, $qty, $basePrice) - $basePrice;
        $finalPrice = max(0, $finalPrice);

        $product->setFinalPrice($finalPrice);
        return $finalPrice;
    }
    
    
   /**
     * Get Total price for configurable items
     *
     * @param Mage_Catalog_Model_Product $product
     * @param float $finalPrice
     * @return float
     */
    public function getTotalConfigurableItemsPrice($product, $finalPrice, $qty)
    {
        $price = 0.0;
        $price = $finalPrice;

        $product->getTypeInstance(true)
                ->setStoreFilter($product->getStore(), $product);
        $attributes = $product->getTypeInstance(true)
                ->getConfigurableAttributes($product);

        $selectedAttributes = array();
        if ($product->getCustomOption('attributes')) {
            $selectedAttributes = unserialize($product->getCustomOption('attributes')->getValue());
			$subProduct = $product->getTypeInstance(true)->getProductByAttributes($selectedAttributes, $product);
			$subProduct = Mage::getModel('catalog/product')->load($subProduct->getData('entity_id'));
			$price = $subProduct->getFinalPrice($qty, $subProduct);			
        }
		return $price;
    }
    

}  