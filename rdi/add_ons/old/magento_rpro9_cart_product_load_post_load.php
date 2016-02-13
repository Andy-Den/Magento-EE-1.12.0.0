<?php

global $cart, $db_lib, $helper_funcs;

$db = $cart->get_db();

$prefix = $db->get_db_prefix();


if($db_lib->get_product_count() > 0)
{
	$cart->_comment("We will need to unlink any simple grouped by their original style sid. This is checking for exclude for web");

	$attribute_names = "'status','use_smd_colorswatch','color'";

	$entity_type_code = "catalog_product";

	$_attributes        = $db->cells("SELECT 
													 attribute_code,attribute_id FROM {$prefix}eav_attribute
													INNER JOIN {$prefix}eav_entity_type on {$prefix}eav_entity_type.entity_type_id = {$prefix}eav_attribute.entity_type_id
													WHERE attribute_code in('related_id','related_parent_id',{$attribute_names}) 
													AND {$prefix}eav_entity_type.entity_type_code = '{$entity_type_code}'","attribute_id","attribute_code");
	
	//get all the original stylesids in the staging.
	$cart->get_db()->exec("CREATE TEMPORARY TABLE rdi_temp_style_sid_list (UNIQUE(style_sid)) AS
									SELECT DISTINCT o.style_sid FROM rdi_style_item_sid_original o
									JOIN rpro_in_items item
									ON item.item_sid = o.item_sid");
	
	
	//unlink the d
	$db->exec("DELETE sl.* from catalog_product_super_link sl
				join catalog_product_entity_varchar r
				on r.entity_id = sl.product_id
				and r.attribute_id = {$_attributes['related_id']}
				join rdi_style_item_sid_original siso
				on siso.item_sid = r.value
				join rdi_temp_style_sid_list style_list
				on style_list.style_sid = siso.style_sid
				join rdi_group_sid_members gsm
				on gsm.rpro_sid = siso.style_sid
				left join rpro_in_items item
				on item.item_sid = r.value
				where item.item_sid is null");
	/*			
	//set the configurable color SMD 
	$db->exec("INSERT INTO catalog_product_entity_int (entity_type_id, entity_id, store_id, attribute_id, VALUE)
				SELECT 4 AS entity_type_id, e.entity_id, 0 AS store_id, {$_attributes['use_smd_colorswatch']} AS attribute_id, 1 AS VALUE FROM catalog_product_entity e
				LEFT JOIN catalog_product_entity_int i
				ON i.entity_id = e.entity_id 
				AND i.attribute_id =  {$_attributes['use_smd_colorswatch']}
				WHERE e.type_id = 'configurable'
				AND i.entity_id IS NULL
				ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
				*/
			
	//Add back in the super_attribute for color if it is not already there.				
	/* If they could possibly not have the color when they first load it.
	
	$_cells = $db->cells("select distinct fldstylesid from rpro_in_styles where flddesc4 is not null","fldstylesid");
	
	if(!empty($cells))
	{
		$cells = implode("','",$_cells);
		
		$db->exec(" INSERT INTO catalog_product_super_attribute(product_id, attribute_id, POSITION)
					 SELECT r.entity_id product_id, {$_attributes['color']} attribute_id, 0 AS POSITION FROM catalog_product_entity_varchar r
					LEFT JOIN catalog_product_super_attribute sa
					ON sa.product_id = r.entity_id
					AND sa.attribute_id = {$_attributes['color']} 
					JOIN rdi_group_sid_members gsm
					ON gsm.group_sid = r.value
					AND gsm.group_sid != gsm.rpro_sid
					  WHERE sa.product_id IS NULL AND r.attribute_id = {$_attributes['related_id']} AND r.value IN('{$cells}');");
		
	
		$db->exec("  INSERT INTO catalog_product_super_attribute_label (product_super_attribute_id, store_id, use_default, `value`)
					  SELECT sa.product_super_attribute_id, 0 store_id, 1 use_default, 'Color' AS `value` FROM catalog_product_super_attribute sa
					  LEFT JOIN catalog_product_super_attribute_label sal
					  ON sal.product_super_attribute_id = sa.product_super_attribute_id
					  WHERE sal.product_super_attribute_id IS NULL AND ;");
	}
	*/
	

}

?>