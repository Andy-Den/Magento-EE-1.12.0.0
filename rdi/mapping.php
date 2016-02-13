<?php

include 'init.php';

global $cart;

$db = $cart->get_db();

$fp = fopen("out/matches.csv",'w');

$matches = $db->rows("SELECT distinct s.sku, concat(\"'\",s.style_sid), e.entity_id FROM rdi_configurable_sku s
						JOIN catalog_product_entity e
						ON e.sku = s.sku
						AND e.type_id = 'configurable'
						order by 1 desc");

foreach($matches as $match)
{
	fputcsv($fp,$match);
}

fclose($fp);


$fp = fopen("out/retail_pro_not_magento.csv",'w');

$retail_pro_not_magento = $db->rows("SELECT distinct s.sku, concat(\"'\",s.style_sid), e.entity_id FROM rdi_configurable_sku s
										LEFT JOIN catalog_product_entity e
										ON e.sku = s.sku
										AND e.type_id = 'configurable'
										WHERE e.entity_id IS NULL");

foreach($retail_pro_not_magento as $match)
{
	fputcsv($fp,$match);
}

fclose($fp);


?>