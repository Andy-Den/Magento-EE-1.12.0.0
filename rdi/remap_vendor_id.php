<?php

include 'init.php';

global $cart,$product_entity_type_id;

$db = $cart->get_db();

$cart_product_lib = $cart->get_processor("rdi_cart_product_load");

$attribute_code = 'vendor_id';
$store_id = 0;
$field_type = 'int';
$product_entity_type_id = '4';	
	     
get_all_product_attribute_ids();             

$_products = $db->rows("SELECT 4 AS entity_type_id, {$attribute_ids['vendor_id']['attribute_id']} AS attribute_id, 0 AS store_id, v.entity_id, c.entity_id AS `value` FROM catalog_product_entity_varchar v
						JOIN vendoroptions_configure c
						ON c.code = v.value
						LEFT JOIN catalog_product_entity_int i
						ON i.entity_id = v.entity_id
						AND i.attribute_id = {$attribute_ids['vendor_id']['attribute_id']}
						 WHERE v.attribute_id = {$attribute_ids['vendor_code']['attribute_id']} 
						 AND v.value IS NOT NULL 
						 AND IFNULL(i.value,0) != c.entity_id");

if(!empty($_products))
{
	foreach($_products as $product)
	{	
		update_product_attribute_field_table($product['entity_id'], $attribute_ids['vendor_id']['type'], $product['store_id'], array($attribute_ids['vendor_id']['attribute_id']=>$product['value']), $attribute_code);
	}
}

?>