<?php
/**
	1. copy to add_ons folder
	2. call with rdi/add_ons/magento_rpro9_pos_common_pre_load.php?verbose_queries=1&install=1
*/

function install_backuptables($db, $prefix)
{
   
	// watch for prefixes
	// Create required tables
	// products
	// varchar
	$db->exec("CREATE TABLE rdi_cpev AS (
	SELECT * FROM {$prefix}catalog_product_entity_varchar LIMIT 1
	)");

	$db->exec("ALTER TABLE `rdi_cpev`   
	 ADD COLUMN `rdi_backup_date` TIMESTAMP NULL AFTER `value`");
	 $db->exec("TRUNCATE rdi_cpev");

	// text
	$db->exec("CREATE TABLE rdi_cpet AS (
	SELECT * FROM {$prefix}catalog_product_entity_text LIMIT 1
	)");

	$db->exec("ALTER TABLE `rdi_cpet`   
	 ADD COLUMN `rdi_backup_date` TIMESTAMP NULL AFTER `value`");
	 $db->exec("TRUNCATE rdi_cpet");
	 
	 // categories
	 // varchar
	$db->exec("CREATE TABLE rdi_ccev AS (
	SELECT * FROM {$prefix}catalog_product_entity_text LIMIT 1
	)");

	$db->exec("TRUNCATE rdi_ccev");

	$db->exec("ALTER TABLE `rdi_ccev`   
	 ADD COLUMN `rdi_backup_date` TIMESTAMP NULL AFTER `value`");	

	// text
	$db->exec("CREATE TABLE rdi_ccet AS (
	SELECT * FROM {$prefix}catalog_product_entity_text LIMIT 1
	)");

	$db->exec("TRUNCATE rdi_ccet");

	$db->exec("ALTER TABLE `rdi_ccet`   
	 ADD COLUMN `rdi_backup_date` TIMESTAMP NULL AFTER `value`");
}
 


if(isset($_GET['install']) && $_GET['install'] == 1)
{
	chdir('../');
    include 'init.php';
	global $cart, $pos_type;
    $db1 = $cart->get_db();
    $prefix = $db1->get_db_prefix();

    install_backuptables($db1, $prefix);
}
else
{
	global $cart, $pos_type;
    $db1 = $cart->get_db();
    $prefix = $db1->get_db_prefix();
}
if(isset($_SERVER['SCRIPT_FILENAME']) && strstr($_SERVER['SCRIPT_FILENAME'],'export')
||
strstr(getcwd(),'export')
)
{
	
}
else
{

	$_product_id = array();
	$_category_id = array();

	$cart->_echo("This backs up varchar/text fields for every product and category in the staging table.<br><br>",'em');

	// Backup products
	// all the items that can be effected by the load
	if($pos_type == 'rpro8')
	{
		$_product_id = $db1->cells("SELECT DISTINCT r.entity_id FROM rpro_in_styles style
								JOIN {$prefix}catalog_product_entity_varchar r
								ON r.value = style.fldstylesid
								WHERE style.record_type = 'style'",'entity_id');
	}

	if($pos_type == 'rpro9')
	{
		$_product_id = $db1->cells("SELECT DISTINCT r.entity_id FROM rpro_in_styles style
								JOIN {$prefix}catalog_product_entity_varchar r
								ON r.value = style.style_sid",'entity_id');
	}





	if(!empty($_product_id))
	{
		$product_ids = implode(",",$_product_id);

		$db1->exec("INSERT INTO rdi_cpev
					SELECT *, NOW() FROM {$prefix}catalog_product_entity_varchar 
					WHERE entity_id IN({$product_ids})");

		$db1->exec("INSERT INTO rdi_cpet
					SELECT *, NOW() FROM {$prefix}catalog_product_entity_text 
					WHERE entity_id IN({$product_ids})");
	}	

	// backup categories
	if($pos_type == 'rpro8')
	{
		$_category_id = $db1->cells("SELECT DISTINCT e.entity_id FROM rpro_in_catalog c
								JOIN {$prefix}catalog_category_entity e
								ON e.related_id = c.sid","entity_id");
	}

	if($pos_type == 'rpro9')
	{
		$_category_id = $db1->cells("SELECT DISTINCT e.entity_id FROM rpro_in_categories c
								JOIN {$prefix}catalog_category_entity e
								ON e.related_id = c.catalog_id","entity_id");
	}


	if(!empty($_category_id))
	{
		$category_ids = implode(",",$_category_id);

		$db1->exec("INSERT INTO rdi_ccev
					SELECT *, NOW() FROM {$prefix}catalog_category_entity_varchar 
					WHERE entity_id IN({$category_ids})");

		$db1->exec("INSERT INTO rdi_ccet
					SELECT *, NOW() FROM {$prefix}catalog_category_entity_text 
					WHERE entity_id IN({$category_ids})");

	// clear out data older than 5 days
		$db1->exec("DELETE FROM rdi_cpev WHERE rdi_backup_date < ADDDATE(NOW(), INTERVAL - 5 DAY)");
		$db1->exec("DELETE FROM rdi_cpet WHERE rdi_backup_date < ADDDATE(NOW(), INTERVAL - 5 DAY)");
		$db1->exec("DELETE FROM rdi_ccev WHERE rdi_backup_date < ADDDATE(NOW(), INTERVAL - 5 DAY)");
		$db1->exec("DELETE FROM rdi_ccet WHERE rdi_backup_date < ADDDATE(NOW(), INTERVAL - 5 DAY)");
	}

	//To repopulate these tables from the backup
	/*

	select count(*), rdi_backup_date from rdi_cpev group by rdi_backup_date order by rdi_backup_date desc;
	select count(*), rdi_backup_date from rdi_cpet group by rdi_backup_date order by rdi_backup_date desc;
	select count(*), rdi_backup_date from rdi_ccev group by rdi_backup_date order by rdi_backup_date desc;
	select count(*), rdi_backup_date from rdi_ccet group by rdi_backup_date order by rdi_backup_date desc;

	INSERT INTO catalog_product_entity_varchar (value_id, entity_type_id, store_id, attribute_id, `value`)
	SELECT value_id, entity_type_id, store_id, attribute_id, `value` FROM rdi_cpev where rdi_backup_date = ''
	ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

	INSERT INTO catalog_product_entity_text (value_id, entity_type_id, store_id, attribute_id, `value`)
	SELECT value_id, entity_type_id, store_id, attribute_id, `value` FROM rdi_cpet where rdi_backup_date = ''
	ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

	INSERT INTO catalog_category_entity_varchar (value_id, entity_type_id, store_id, attribute_id, `value`)
	SELECT value_id, entity_type_id, store_id, attribute_id, `value` FROM rdi_ccev where rdi_backup_date = ''
	ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

	INSERT INTO catalog_category_entity_text (value_id, entity_type_id, store_id, attribute_id, `value`)
	SELECT value_id, entity_type_id, store_id, attribute_id, `value` FROM rdi_ccet where rdi_backup_date = ''
	ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
	*/	
	
}	


//start the combining


global $cart, $db_lib;

$db = $cart->get_db();

$prefix = $db->get_db_prefix();

if($db_lib->get_product_count() > 0)
{
	$cart->_comment("Join all products in this list if they have the same subclass_name. No one uses this.");
	
	//update the desc1 to the subclass_name
	$db->exec("UPDATE rpro_in_styles set subclass_name = SUBSTRING_INDEX(desc1,'-',1) WHERE desc1 like '%-%'");

	$related_id = $db->cell("SELECT attribute_id FROM eav_attribute WHERE attribute_code IN('related_id')",'attribute_id');
	
	$join_field = "subclass_name";
	
	//clean out any joins for products that no longer exist.
	$db->exec("DELETE gsm.* FROM rdi_style_grouping g
				LEFT JOIN catalog_product_entity_varchar r
				ON r.value = g.group_sid
				AND r.attribute_id = {$related_id}
				JOIN rdi_group_sid_members gsm
				ON gsm.group_sid = g.group_sid
				WHERE r.value IS NULL");
				
	$db->exec("DELETE g.* FROM rdi_style_grouping g
				LEFT JOIN catalog_product_entity_varchar r
				ON r.value = g.group_sid
				AND r.attribute_id = {$related_id}
				WHERE r.value IS NULL");
	
	//Store the original style/item association. We will use this pre category load to unassociate simples.
	//left join so we dont have two item_sids for a stylesid on the table
	$db->exec("REPLACE INTO rdi_style_item_sid_original
				SELECT DISTINCT s.style_sid, s.item_sid FROM rpro_in_items s
				LEFT JOIN rdi_style_item_sid_original o
				ON o.item_sid = s.item_sid
				 WHERE o.item_sid IS NULL;");
	

	//process the list of the new groups, these would be groups that are not defined in the rdi_style_grouping table
	//a group is defined as the fldDesc3 value, 

	$db->exec("insert into rdi_style_grouping 
			SELECT DISTINCT s.{$join_field}, s.style_sid
			FROM rpro_in_styles s
			LEFT JOIN rdi_style_grouping g 
			ON g.{$join_field} = s.{$join_field}
			where g.{$join_field} is null 
			and s.{$join_field} is not null
			group by s.{$join_field}");

	//if the item was in a group of its own, or not in a group, this will remove it from is assignment so it can be put into a group proper
	$db->exec("delete from rdi_group_sid_members where rpro_sid in (select * from (
	 SELECT DISTINCT 
				s.style_sid
			FROM rpro_in_styles s
			left join rdi_group_sid_members s1 
			on s1.rpro_sid = s.style_sid
			left join rdi_style_grouping s2 
			on s2.group_sid = s1.group_sid
			WHERE s.subclass_name IS NOT NULL 
			and s2.group_sid is null) as x)");


	//assign the items to the grouping
	$db->exec("insert into rdi_group_sid_members
			SELECT DISTINCT (
			SELECT group_sid
			FROM rdi_style_grouping g
			WHERE g.{$join_field} = s.{$join_field}) AS group_sid, s.style_sid
			FROM rpro_in_styles s
			left join rdi_group_sid_members s1 
			on s1.rpro_sid = s.style_sid
			WHERE s.{$join_field} IS NOT NULL and group_sid is null");


	//need to put items that have a null desc3 into their own group
	//these are just a straight relation, not grouped at all
	$db->exec("insert into rdi_group_sid_members
			  SELECT DISTINCT s.style_sid, s.style_sid
			  FROM rpro_in_styles s
			  left join rdi_group_sid_members s1 
			  on s1.rpro_sid = s.style_sid
			  WHERE s.{$join_field} IS NULL 
			  and group_sid is null");

	//////////////////////////////////////////////////////////////////		  
	//Manipulate the staging table.

				
// update the desc4 to the children for grouped products
/*$db->exec("update rpro_in_styles style
			JOIN rpro_in_styles item
			ON item.style_sid = style.style_sid
			AND item.record_type = 'item'
			JOIN rdi_group_sid_members gsm_style
			ON gsm_style.rpro_sid = style.style_sid
			set item.flddesc4 = style.flddesc4
			WHERE style.record_type = 'style' 
			AND style.flddcs IS NOT NULL and item.flddesc4 is null");
	*/
	
//remove the desc4 on the item level for products that are a single group

$_sids = $db->cells("SELECT rpro_sid FROM rdi_group_sid_members 
			GROUP BY group_sid 
			HAVING COUNT(rpro_sid) = 1","rpro_sid");

$sids =implode("','", $_sids);
			
//$db->exec("UPDATE rpro_in_styles SET flddesc4 = NULL WHERE record_type != 'style' AND fldstylesid IN('{$sids}')");

unset($_sids,$sids);

// update the sid to the group_sid only for the items
$db->exec("update rpro_in_items s
			join rdi_group_sid_members gsm
			on gsm.rpro_sid = s.style_sid
			set s.style_sid = gsm.group_sid");

// delete the parents that are not longer connected
$db->exec("delete s.* FROM rpro_in_styles s
			JOIN rdi_group_sid_members gsm
			ON gsm.rpro_sid = s.style_sid
			and gsm.group_sid != s.style_sid");



// if there is no header on the style we need to grab the style from the backup table.
$db->exec("INSERT INTO rpro_in_styles 
				SELECT DISTINCT header.* FROM rpro_in_items item
				left join rpro_in_styles style
				on style.style_sid = item.style_sid
				join rpro_in_styles_headers header
				on header.style_sid = item.style_sid
				where style.style_sid is null");


// add a style record for the parent
// // delete the parents in there
$db->exec("DELETE header.* FROM rpro_in_styles style
					JOIN rdi_style_grouping sg
					ON sg.group_sid = style.style_sid
					JOIN rpro_in_styles_headers header
					ON header.style_sid = style.style_sid");


// // add the parent back
$db->exec("INSERT INTO rpro_in_styles_headers
			SELECT style.* FROM rpro_in_styles style
			JOIN rdi_style_grouping sg
			ON sg.group_sid = style.style_sid");
			
// check to see that all grouped configurables have color in their super attribute
// If not we will add it as zero and update any other attributes to +1 sort
/*
$_sids = $db->cells("SELECT rpro_sid FROM rdi_group_sid_members 
			GROUP BY group_sid 
			HAVING COUNT(rpro_sid) > 1","rpro_sid");

$sids =implode("','", $_sids);
			
$db->exec("UPDATE rpro_in_styles SET flddesc4 = NULL WHERE record_type != 'style' AND fldstylesid IN('{$sids}')");
*/
unset($_sids,$sids);
/*
if($db->count('rpro_in_upsell_item') > 0 )
{
	// update the fldstylesid to the parent
	$db->exec("update rpro_in_upsell_item ui
	join rdi_group_sid_members gsm
	on gsm.rpro_sid = ui.fldstylesid
	set ui.fldstylesid = gsm.group_sid");

	// update the fldupsellsid to the parent
	$db->exec("UPDATE rpro_in_upsell_item ui
	JOIN rdi_group_sid_members gsm
	ON gsm.rpro_sid = ui.fldupsellsid
	SET ui.fldupsellsid = gsm.group_sid");

	// may need to reduce and duplicated on the fldstylesid, fldupsellsid because the orderno could be different and would reduce a DISTINCT.
	$deletes = $db->cells("SELECT concat('DELETE FROM rpro_in_upsell_item where fldstylesid = \"',fldstylesid,'\" AND fldupsellsid = \"',fldupsellsid, '\", AND fldorderno != \"', min(fldorderno),'\";') as f FROM rpro_in_upsell_item ui
			group by fldstylesid, fldupsellsid
			having count(*) > 1",'f');
			
	if(!empty($deletes))
	{
		foreach($deletes as $d)
		{
			$db->exec($d);		
		}
		unset($deletes);
	}

}
*/

}

	


?>