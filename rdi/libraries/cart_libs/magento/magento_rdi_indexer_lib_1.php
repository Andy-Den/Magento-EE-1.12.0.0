<?php
/**
 * Magento Indexer Library
 * @package Core\Common\Magento\Indexer
 */

global $cart;

$cart->get_processor('rdi_cart_common')->load_magento();

/* This is going to only be called from the processor.
include_once '../app/Mage.php';
umask(0);
Mage::app();
*/

//set all indexing to be manual
function indexer_set_manual()
{
    //set the indexer to manual
    $pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
    foreach ($pCollection as $process) {
      $process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
      //$process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
    }
}

//set all indexers to happen on save
function indexer_set_real_time()
{
    $pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
    foreach ($pCollection as $process) {
      //$process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
      $process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
    }
}

//reindex all of the indexes
function indexer_index_all()
{
    $pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection(); 
    foreach ($pCollection as $process) {
      $process->reindexAll();      
    }        
}

function indexer_index_product_attributes()
{
    indexer_index(1);
}

function indexer_index_product_prices()
{		
	global $cart, $db_lib, $indexer_index_product_prices;
	
	if(isset($indexer_index_product_prices) && $indexer_index_product_prices == 1)
	{    
		Mage::getModel('index/process')->load(2)->reindexAll();
	}
	else
	{	
		clear_magento_product_prices_in_staging();
	
	
		$ids = get_rdi_staging_products();
		
		if(!empty($ids))
		{
			$cart->_echo(__FUNCTION__ . " : " . count($ids));
			
			Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($ids);

			echo "success";
		}
	}
}

function indexer_index_catalog_url_rewrites()
{
   global $cart, $db_lib, $indexer_index_catalog_url_rewrites, $load_categories;
	
	//get the staging category count, if there are categories and categories are turned on, we will index normally. Else, lets do just the products we got.
	
	
	
	if(isset($load_categories) && $load_categories == 1 && $db_lib->get_category_count() > 0)
	{    
		Mage::getModel('index/process')->load(3)->reindexAll();
	}
	else
	{			
		$ids = get_rdi_staging_products();
 
		$urlModel = Mage::getSingleton('catalog/url');
		
		
		$resourceModel = Mage::getResourceSingleton('catalog/url');
		
		$resourceModel->beginTransaction();	
		try 
		{
			if(!empty($ids)) {
				$urlModel->clearStoreInvalidRewrites(); // Maybe some products were moved or removed from website
				foreach ($ids as $productId) {
					 $urlModel->refreshProductRewrite($productId);
				}
			}			

			$resourceModel->commit();
		}
		catch (Exception $e) 
		{		
			Mage::log($e->getMessage(),null,"indexer_error.log");
		}
		echo "success";
	}
}

function indexer_index_product_flat_data()
{
	global $cart, $db_lib, $indexer_index_product_flat_data;
	
	if(isset($indexer_index_product_flat_data) && $indexer_index_product_flat_data == 1)
	{    
		Mage::getModel('index/process')->load(4)->reindexAll();
	}
	else
	{
		
		$ids = get_rdi_staging_products();
		
		if(!empty($ids))
		{
			$cart->_echo(__FUNCTION__ . " : " . count($ids));
			$r = new rdi_magento_product_flat();

			$r->rdiUpdateProductFlat($ids);

			echo "success";
		}
	}
	
	
}

function indexer_index_category_flat_data()
{
    indexer_index(5);
}

function indexer_index_category_products()
{
     indexer_index(6);
}

function indexer_index_catalog_search_index()
{
    indexer_index(7);
}

function indexer_index_stock_status()
{
    indexer_index(8);
}

function indexer_index_tag_aggregation_data()
{
    indexer_index(9);
}

function indexer_index($index)
{
    global $indexer_exec, $benchmarker, $verbose_queries;

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
    
    $benchmarker->set_start_time("magento_rdi_indexer_lib", "indexing index {$indexes[$index]}");
    
    if($indexer_exec == "shell")
    {        
        $retVal = exec('which php');
                
        //should be an optional setting here but skipping for now
        //$cmdReIndexAll = $cmdExec . 'reindexall';
        $cmdExec = $retVal . ' -f ../shell/indexer.php -- -';

        $cmd = $cmdExec . 'reindex ' . $indexes[$index];
        
        //echo $cmd . "<br>";

        if(isset($cmd))
            echo exec($cmd) . "<br>";        
    }
    else
    {           
         try
		{
			if(isset($verbose_queries) && $verbose_queries == 1)
			{
				echo "<br>Running index {$indexes[$index]}<br>";
			}
			if(isset($process))
            {
				unset($process);
            }
			
			if($index == 4)
			{
				indexer_index_product_flat_data();
			}
			else if($index == 2)
			{
				indexer_index_product_prices();
			}
			else if($index == 3)
			{
				indexer_index_catalog_url_rewrites();
			}
			else
			{
				$process = Mage::getModel('index/process')->load($index);
				$process->reindexAll();
			}
		
			echo "success";
			
		}
		catch(Exception $e)
		{
			echo "<br>{$indexes[$index]} did not finish<br>";			
		
			Mage::log($e->getMessage(),null,"indexer_error.log");
			
		}
                  
		
		if(isset($process))
		{
			unset($process);
		}
    }
    
    $benchmarker->set_end_time("magento_rdi_indexer_lib", "finished index {$indexes[$index]}");
}

function set_index_dirty($index)
{
    global $cart;
    
    //set the index invalidated
    $cart->get_db()->exec("UPDATE `{$cart->get_db()->get_db_prefix()}index_process` SET `status` = 'require_reindex' WHERE (process_id='{$index}')");
}

function set_indexes_all_clear()
{
    global $cart;
    
    //set the index invalidated
    $cart->get_db()->exec("UPDATE `{$cart->get_db()->get_db_prefix()}index_process` SET `status` = 'pending'");
}

function indexer_set_product_attributes_dirty()
{
    set_index_dirty(1);
}

function indexer_set_product_prices_dirty()
{
    set_index_dirty(2);
}

function indexer_set_catalog_url_rewrites_dirty()
{
    set_index_dirty(3);
}

function indexer_set_product_flat_data_dirty()
{
    set_index_dirty(4);
}

function indexer_set_category_flat_data_dirty()
{
    set_index_dirty(5);
}

function indexer_set_category_products_dirty()
{
    set_index_dirty(6);
}

function indexer_set_catalog_search_index_dirty()
{
    set_index_dirty(7);
}

function indexer_set_stock_status_dirty()
{
    set_index_dirty(8);
}

function indexer_set_tag_aggregation_data_dirty()
{
    set_index_dirty(9);
}

function indexer_clear_cache()
{
    Mage::app()->cleanCache();
}

/*
 * updating and saving a product one at a time for large category structures.
 * Saving a product takes about 3 seconds and the idea is to avoid doing a total 
 * catalog URL rewrite if only updates and inserts are happening. Deletion of products
 * will require a complete reindex. As we typically just disable unused products from the site,
 * this should work in most cases.
 */

function rdi_magento_save()
{    
    global $cart,$index_one_at_a_time;
    
    $rows = $cart->get_db()->rows("select product_id from rdi_magento_save");
    
    
    if(isset($index_one_at_a_time) && $index_one_at_a_time == 1)
    {
        foreach($rows as $product_id)
        {

            $product = Mage::getModel('catalog/product')->load($product_id);
            Mage::getSingleton('index/indexer')->processEntityAction(
            $product, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_SAVE);
        }
    }

}

class rdi_magento_product_flat extends Mage_Catalog_Model_Product_Flat_Indexer
{
	public function rdiUpdateAttribute($field,$product_ids)
	{
		parent::updateAttribute($field,null,$product_ids);	
	}
	
	public function rdiUpdateProductFlat($product_ids)
	{
		$this->updateProduct($product_ids);
	}
}

function get_rdi_staging_products()
{
	global $db_lib, $cart, $rdi_stating_products;

	if(!isset($rdi_staging_products))
	{
	
		$attributes = $cart->get_db()->cells("SELECT attribute_code,attribute_id FROM {$cart->get_db()->get_db_prefix()}eav_attribute
														INNER JOIN {$cart->get_db()->get_db_prefix()}eav_entity_type on {$cart->get_db()->get_db_prefix()}eav_entity_type.entity_type_id = {$cart->get_db()->get_db_prefix()}eav_attribute.entity_type_id
														AND {$cart->get_db()->get_db_prefix()}eav_entity_type.entity_type_code = 'catalog_product'","attribute_id","attribute_code");

		$rdi_staging_products = $cart->get_db()->cells("select distinct entity_id from {$cart->get_db()->get_db_prefix()}catalog_product_entity_varchar r
							join " . $db_lib->get_table_name('in_items') . " item
							on item.{$db_lib->get_item_sid()} = r.value
							join {$cart->get_db()->get_db_prefix()}catalog_category_product cp
							on cp.product_id = r.entity_id
							where r.attribute_id = {$attributes['related_id']}
							union
							select distinct entity_id from {$cart->get_db()->get_db_prefix()}catalog_product_entity_varchar r
							join " . $db_lib->get_table_name('in_items') . " item
							on item.{$db_lib->get_style_sid()} = r.value
							join {$cart->get_db()->get_db_prefix()}catalog_category_product cp
							on cp.product_id = r.entity_id
							where r.attribute_id = {$attributes['related_id']}",'entity_id');
	}
	
	return $rdi_staging_products;
	
}

function get_rdi_staging_categories()
{
    global $db_lib, $cart, $rdi_staging_categories;

    if (!isset($rdi_staging_categories))
    {
        $rdi_staging_categories = $cart->get_db()->cells("select distinct entity_id from {$cart->get_db()->get_db_prefix()}catalog_category_entity  where related_id is not null", 'entity_id');
    }

    return $rdi_staging_categories;
}

function clear_magento_product_prices_in_staging()
{
	global $cart, $db_lib;
	
	$attributes = $cart->get_db()->cells("SELECT attribute_code,attribute_id FROM {$cart->get_db()->get_db_prefix()}eav_attribute
														INNER JOIN {$cart->get_db()->get_db_prefix()}eav_entity_type on {$cart->get_db()->get_db_prefix()}eav_entity_type.entity_type_id = {$cart->get_db()->get_db_prefix()}eav_attribute.entity_type_id
														AND {$cart->get_db()->get_db_prefix()}eav_entity_type.entity_type_code = 'catalog_product'", "attribute_id", "attribute_code");
	
	
	$cart->get_db()->exec("DELETE t.* FROM {$cart->get_db()->get_db_prefix()}catalog_product_index_price_tmp t
								JOIN {$cart->get_db()->get_db_prefix()}catalog_product_entity_varchar r
								ON r.entity_id = t.entity_id
								AND r.attribute_id = {$attributes['related_id']}
								JOIN {$db_lib->get_table_name('in_items')} item
								ON item.{$db_lib->get_style_sid()} = r.value");
								
	$cart->get_db()->exec("DELETE t.* FROM {$cart->get_db()->get_db_prefix()}catalog_product_index_price_tmp t
								JOIN {$cart->get_db()->get_db_prefix()}catalog_product_entity_varchar r
								ON r.entity_id = t.entity_id
								AND r.attribute_id = {$attributes['related_id']}
								JOIN {$db_lib->get_table_name('in_items')} item
								ON item.{$db_lib->get_style_sid()} = r.value");

}

?>