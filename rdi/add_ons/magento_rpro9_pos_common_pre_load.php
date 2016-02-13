<?php
/**
	1. copy to add_ons folder
	2. call with rdi/add_ons/magento_rpro8_pos_common_pre_load.php?verbose_queries=1&install=1
	
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
	 
	$db->exec("ALTER TABLE `rdi_cpet`   
  ADD  INDEX `IDX_rdi_cpet_date` (`rdi_backup_date`);");
	$db->exec("ALTER TABLE `rdi_cpev`   
  ADD  INDEX `IDX_rdi_cpev_date` (`rdi_backup_date`);");
  
	$db->exec("ALTER TABLE `rdi_ccev`   
  ADD  INDEX `IDX_rdi_ccev_date` (`rdi_backup_date`);");
	$db->exec("ALTER TABLE `rdi_ccet`   
  ADD  INDEX `IDX_rdi_ccet_date` (`rdi_backup_date`);");
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
        $temp_table = false;
	$cart->_echo("This backs up varchar/text fields for every product and category in the staging table.<br><br>",'em');
$time = microtime(true);
	// Backup products
	// all the items that can be effected by the load
	if($pos_type == 'rpro8' || $pos_type == 'rpro4web')
	{
		$_product_id = $db1->cells("SELECT DISTINCT r.entity_id FROM rpro_in_styles style
								JOIN {$prefix}catalog_product_entity_varchar r
								ON r.value = style.fldstylesid
								WHERE style.record_type = 'style'
								order by 1",'entity_id');
								
		/*$db1->exec("CREATE TEMPORARY TABLE rdi_back_ups_entity_ids  (UNIQUE(entity_id)) AS
								SELECT DISTINCT r.entity_id FROM rpro_in_styles style
								JOIN {$prefix}catalog_product_entity_varchar r
								ON r.value = style.fldstylesid
								WHERE style.record_type = 'style'
								order by 1");$temp_table=true;
	*/	
	}

	if($pos_type == 'rpro9')
	{
		$_product_id = $db1->cells("SELECT DISTINCT r.entity_id FROM rpro_in_styles style
								JOIN {$prefix}catalog_product_entity_varchar r
								ON r.value = style.style_sid",'entity_id');
	}


	//temp table method
	
	if($temp_table && $db1->cell("SELECT count(*) c from rdi_back_ups_entity_ids",'c') > 0)
	{
		$db1->exec("INSERT INTO rdi_cpev
						SELECT v.*, NOW() FROM {$prefix}catalog_product_entity_varchar v
						inner join rdi_back_ups_entity_ids e
						on e.entity_id = v.entity_id
						WHERE  value is not null");

		$db1->exec("INSERT INTO rdi_cpet
					SELECT t.*, NOW() FROM {$prefix}catalog_product_entity_text t
					inner join rdi_back_ups_entity_ids e
					on e.entity_id = t.entity_id
					WHERE  value is not null");
	}


	if(!empty($_product_id))
	{	
		$count = count($_product_id);
		$cart->_echo($count);
		$backup_divide = 300;
		
		if($count > 1000)
		{
			$__product_ids = array();
		
			for($i=0;$i<$count;$i+=$backup_divide)
			{
				$__product_ids[] = implode(",",array_slice($_product_id,$i,$backup_divide));
			}
			
		}
				
		//test for all at once backup
		//$__product_ids = array(implode(",",$_product_id));
		//$time = microtime(true);
		if(!empty($__product_ids))
		{
			foreach($__product_ids as $product_ids)
			{
				$db1->exec("INSERT INTO rdi_cpev
							SELECT *, NOW() FROM {$prefix}catalog_product_entity_varchar 
							WHERE  value is not null and entity_id IN({$product_ids})");

				$db1->exec("INSERT INTO rdi_cpet
							SELECT *, NOW() FROM {$prefix}catalog_product_entity_text
							WHERE  value is not null and entity_id IN({$product_ids})");
			}
		}
		
		$db1->exec("DELETE FROM rdi_cpev WHERE rdi_backup_date < ADDDATE(NOW(), INTERVAL - 5 DAY)");
		$db1->exec("DELETE FROM rdi_cpet WHERE rdi_backup_date < ADDDATE(NOW(), INTERVAL - 5 DAY)");
	}	
	$cart->_echo(microtime(true)-$time);

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
		$db1->exec("DELETE FROM rdi_ccev WHERE rdi_backup_date < ADDDATE(NOW(), INTERVAL - 5 DAY)");
		$db1->exec("DELETE FROM rdi_ccet WHERE rdi_backup_date < ADDDATE(NOW(), INTERVAL - 5 DAY)");

	}
	//To repopulate these tables from the backup
	/*

	SELECT COUNT(*), rdi_backup_date FROM rdi_cpev GROUP BY rdi_backup_date ORDER BY rdi_backup_date DESC;
	SELECT COUNT(*), rdi_backup_date FROM rdi_cpet GROUP BY rdi_backup_date ORDER BY rdi_backup_date DESC;
	SELECT COUNT(*), rdi_backup_date FROM rdi_ccev GROUP BY rdi_backup_date ORDER BY rdi_backup_date DESC;
	SELECT COUNT(*), rdi_backup_date FROM rdi_ccet GROUP BY rdi_backup_date ORDER BY rdi_backup_date DESC;

	INSERT INTO catalog_product_entity_varchar (value_id, entity_type_id, store_id, entity_id, attribute_id, `value`)
	SELECT value_id, entity_type_id, store_id, entity_id, attribute_id, `value` FROM rdi_cpev WHERE rdi_backup_date = ''
	ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

	INSERT INTO catalog_product_entity_text (value_id, entity_type_id, store_id,entity_id,  attribute_id, `value`)
	SELECT value_id, entity_type_id, store_id, entity_id, attribute_id, `value` FROM rdi_cpet WHERE rdi_backup_date = ''
	ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

	INSERT INTO catalog_category_entity_varchar (value_id, entity_type_id, store_id,entity_id,  attribute_id, `value`)
	SELECT value_id, entity_type_id, store_id,entity_id,  attribute_id, `value` FROM rdi_ccev WHERE rdi_backup_date = ''
	ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);

	INSERT INTO catalog_category_entity_text (value_id, entity_type_id, store_id,entity_id,  attribute_id, `value`)
	SELECT value_id, entity_type_id, store_id,entity_id,  attribute_id, `value` FROM rdi_ccet WHERE rdi_backup_date = ''
	ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
	*/	
	
}	

	


?>