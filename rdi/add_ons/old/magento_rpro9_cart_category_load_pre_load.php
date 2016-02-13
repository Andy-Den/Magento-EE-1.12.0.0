<?php

global $cart, $db_lib;

$db = $cart->get_db();

$prefix = $db->get_db_prefix();


if($db_lib->get_product_count() > 0 && $db_lib->get_category_count() > 0)
{
	$cart->_comment("If any of the styles are still in the catalog we will add a row for the parent style so the product stays on. ALL styles will need to be removed to disable the product on the web.");

	$attribute_names = "'status'";

	$entity_type_code = "catalog_product";

	$_attributes        = $db->cells("SELECT 
													 attribute_code,attribute_id FROM {$prefix}eav_attribute
													INNER JOIN {$prefix}eav_entity_type on {$prefix}eav_entity_type.entity_type_id = {$prefix}eav_attribute.entity_type_id
													WHERE attribute_code in('related_id','related_parent_id',{$attribute_names}) 
													AND {$prefix}eav_entity_type.entity_type_code = '{$entity_type_code}'","attribute_id","attribute_code");
	
	//Store the original style/item association. We will use this post category load to disable simples.
	$db->exec("update rdi_style_item_sid_original siso
					JOIN {$prefix}catalog_product_entity_varchar r
					ON r.value = siso.item_sid
					AND r.attribute_id = {$_attributes['related_id']}
					left join {$prefix}rpro_in_category_products catalog
					on catalog.style_sid = siso.style_sid
					join {$prefix}catalog_product_entity_int st
					on st.entity_id = r.entity_id
					and st.attribute_id = {$_attributes['status']}
					set st.value = 2
					where catalog.style_sid is null");


	$db->exec("UPDATE rdi_style_item_sid_original siso
					JOIN {$prefix}catalog_product_entity_varchar r
					ON r.value = siso.item_sid
					AND r.attribute_id = {$_attributes['related_id']}
					JOIN rpro_in_category_products catalog
					ON catalog.style_sid = siso.style_sid
					JOIN {$prefix}catalog_product_entity_int st
					ON st.entity_id = r.entity_id
					AND st.attribute_id = {$_attributes['status']}
					SET st.value = 1");
					
	//Add a style/category record back in if the parent has been removed from the ECI catalog.
	$db->exec("INSERT INTO rpro_in_category_products (catalog_id, style_sid, sort_order)
				SELECT DISTINCT cc.catalog_id, gsm.group_sid AS style_sid, MIN(cc.sort_order) AS sort_order FROM rdi_group_sid_members gsm
				LEFT JOIN rpro_in_category_products cp
				ON cp.style_sid = gsm.group_sid
				JOIN rpro_in_category_products cc
				ON cc.style_sid = gsm.rpro_sid 
				WHERE cp.style_sid IS NULL
				AND cc.style_sid IS NOT NULL
				GROUP BY gsm.group_sid, cc.parent_sid");

}

?>