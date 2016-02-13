<?php

include 'init.php';

global $cart, $verbose_queries, $start,$index_finish;

$verbose_queries=1;



$cart->get_processor('rdi_cart_common')->load_magento();

$db = $cart->get_db();

//reindex flag for an attribute_code
/*
		$process = Mage::getModel('catalog/product_flat_indexer');
		$cart->_methods($process);
		
		$process->updateAttribute('shoe_size');
	*/	
	//instead of doing index_eav
	$_attributeIds = Mage::getModel('catalogindex/indexer_eav')->getIndexableAttributeCodes();
	//$_attributeIds = array("'180'");
	if(!empty($_attributeIds))
	{
		foreach($_attributeIds as $attribute_id)
		{
			$db->exec("INSERT INTO catalog_product_index_eav 
					select i.entity_id,i.attribute_id,1 as store_id, i.value FROM catalog_product_entity_int i
					left join catalog_product_index_eav eav
					on eav.entity_id = i.entity_id
					and eav.attribute_id = {$attribute_id}
					where i.attribute_id = {$attribute_id}
					and ifnull(eav.value,'0')!=i.value
					on duplicate key update value = values(value)");
		
		}
		
	
	}
	exit;
		Mage::log("start index 1",null,"system.log");
			$process = Mage::getModel('index/process')->load(1);
			$process->reindexAll();
		Mage::log("finish index 1",null,"system.log");exit;
		$process = Mage::getModel('catalogindex/indexer_eav');
		$cart->_filename($process,'saveIndex');
		$cart->_var_dump($process->getIndexableAttributeCodes());
		$cart->_methods($process);exit;
		
		$process->updateAttribute('shoe_size');
		exit;


$total = $db->cell("SELECT count(DISTINCT e.entity_id) c FROM catalog_product_entity e
							JOIN catalog_category_product p
							ON p.product_id = e.entity_id
							LEFT JOIN catalog_product_super_link sl
							ON sl.product_id = e.entity_id
							WHERE sl.product_id IS NULL ",'c');

$cart->_echo("Price indexing {$i} of {$total} products");
							
$per_call = 100;
							
for($i = $start; $i < $total +1; $i+=$per_call)
{							
				
	$product_ids = $db->cells("SELECT DISTINCT e.entity_id FROM catalog_product_entity e
								JOIN catalog_category_product p
								ON p.product_id = e.entity_id
								LEFT JOIN catalog_product_super_link sl
								ON sl.product_id = e.entity_id
								WHERE sl.product_id IS NULL LIMIT {$i},{$per_call}",'entity_id');
								
	$cart->_echo("Price indexing {$i} of {$total} products");
	
	try
	{
		Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($product_ids);
	}
	catch(Mage_Core_Exception $e)
	{
		$cart->_methods($e);
		$cart->_print_r($e->getMessage());
	}

}				

			//$process = Mage::getModel('index/process')->load(8)->reindexEverything();exit;
 //Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds(array(1065990));
	//	echo Varien_Debug::backtrace(true, true); exit;

//Mage::log(Varien_Debug::backtrace(true, true), null, 'backtrace.log');exit;

$index = 2;
$indexes = array(
                       1 => "catalog_product_attribute",
                       2 => "catalog_product_price",
                       3 => "catalog_url",
                       4 => "catalog_product_flat",
                       5 => "catalog_category_flat",
                       6 => "catalog_category_product",
                       7 => "catalogsearch_fulltext",
                       8 => "cataloginventory_stock",
                       9 => "tag_summary"
                    );
            
        $retVal = exec('which php');
                
        //should be an optional setting here but skipping for now
        //$cmdReIndexAll = $cmdExec . 'reindexall';
        $cmdExec = $retVal . ' -f ../shell/indexer.php -- -';

        $cmd = $cmdExec . 'reindex ' . $indexes[$index];
        
        //echo $cmd . "<br>";

        if(isset($cmd))
            echo exec($cmd) . "<br>";
			
		echo Varien_Debug::backtrace(true, true); exit;
			
			$process = Mage::getModel('index/process')->load(2);
			
			$process->reindexAll();
			echo Varien_Debug::backtrace(true, true); exit;
//or
Mage::log(Varien_Debug::backtrace(true, true), null, 'backtrace.log');
			debug_print_backtrace();exit;


indexer_index(2);exit;

for($i=$index_start; $i <($index_finish+1);$i++)
{
	indexer_index($i);
}

?>