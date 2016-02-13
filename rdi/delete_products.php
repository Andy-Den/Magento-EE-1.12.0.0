<?php

include 'init.php';

global $cart;


$db = $cart->get_db();


$products = $db->cell("SELECT count(entity_id) c FROM catalog_product_entity","c");
echo $products . "|START<br>";


//check if the products actually exist and go to process new list.
$products = $db->cells("SELECT entity_id FROM catalog_product_entity where entity_id limit 1000","entity_id");


	$mage_path = '../app/Mage.php';

	include_once $mage_path;
	umask(0);
	Mage::app();
Mage::register('isSecureArea', 1);

foreach($products as $productId)
{
	$cart->_print_r($productId);
	

	$product = Mage::getModel('catalog/product')->load($productId);
	
	//$cart->_print_r($product->getData());
	
	$product->delete();
	
	$p = $productId;
}

$products = $db->cell("SELECT count(entity_id) c FROM catalog_product_entity","c");
echo $products . "|END<br>";


?>