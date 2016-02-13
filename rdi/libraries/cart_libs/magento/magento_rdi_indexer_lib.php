<?php

/**
 * Magento Indexer Library
 * @package Core\Common\Magento\Indexer
 */
$mage_path = file_exists('../app/Mage.php') ? '../app/Mage.php' : dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . "/app/Mage.php";

include_once $mage_path;
umask(0);
Mage::app();


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
    foreach ($pCollection as $process)
    {
        $process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
        //$process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
    }
}

//set all indexers to happen on save
function indexer_set_real_time()
{
    $pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection();
    foreach ($pCollection as $process)
    {
        //$process->setMode(Mage_Index_Model_Process::MODE_MANUAL)->save();
        $process->setMode(Mage_Index_Model_Process::MODE_REAL_TIME)->save();
    }
}

//reindex all of the indexes
function indexer_index_all()
{
    $pCollection = Mage::getSingleton('index/indexer')->getProcessesCollection();
    foreach ($pCollection as $process)
    {
        $process->reindexAll();
    }
}

function indexer_index_product_attributes()
{
    indexer_index(1);
}

function indexer_index_product_prices()
{
    indexer_index(2);
}

function indexer_index_catalog_url_rewrites()
{
    indexer_index(3);
}

function indexer_index_product_flat_data()
{
    indexer_index(4);
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
    global $indexer_exec, $benchmarker, $cart;

    $indexes = $cart->get_db()->cells("select indexer_code,process_id from {$cart->prefix}index_process", 'indexer_code', 'process_id');

    //test is the flat tables are on.
    if ($indexes[$index] == 'catalog_product_flat')
    {
        $on = $cart->get_db()->cell("SELECT value FROM {$cart->prefix}core_config_data WHERE path = 'catalog/frontend/flat_catalog_product'", 'value');

        if ($on == '0')
        {
            return true;
        }
    }

    if ($indexes[$index] == 'catalog_category_flat')
    {
        $on = $cart->get_db()->cell("SELECT value FROM {$cart->prefix}core_config_data WHERE path = 'catalog/frontend/flat_catalog_category'", 'value');

        if ($on == '0')
        {
            return true;
        }
    }

    $benchmarker->set_start_time("magento_rdi_indexer_lib", "Indexing index {$indexes[$index]}");

    if ($indexer_exec == "shell")
    {
        $retVal = exec('which php');

        //should be an optional setting here but skipping for now
        //$cmdReIndexAll = $cmdExec . 'reindexall';
        $cmdExec = $retVal . ' -f ../shell/indexer.php -- -';

        $cmd = $cmdExec . 'reindex ' . $indexes[$index];

        //echo $cmd . "<br>";

        if (isset($cmd))
            echo exec($cmd) . "<br>";
    }
    else
    {
        $cart->echo_message("Running index {$indexes[$index]}", 2);
        
        $process = Mage::getModel('index/process');

        $process->load($index);
        /* @var $process Mage_Index_Model_Process */
        try
        {
            $startTime = microtime(true);
            $process->reindexEverything();
            $resultTime = microtime(true) - $startTime;
            Mage::dispatchEvent($process->getIndexerCode() . '_shell_reindex_after');
            $cart->echo_message($process->getIndexer()->getName() . " index was rebuilt successfully in " . gmdate('H:i:s', $resultTime), 4);
        } catch (Mage_Core_Exception $e)
        {
            $cart->echo_message($e->getMessage(), 4);
        } catch (Exception $e)
        {
            $cart->echo_message($process->getIndexer()->getName() . " index process unknown error:", 4);
            $cart->echo_message($e);
        }
    }

    $benchmarker->set_end_time("magento_rdi_indexer_lib", "Indexing index {$indexes[$index]}");
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

/*
 * updating and saving a product one at a time for large category structures.
 * Saving a product takes about 3 seconds and the idea is to avoid doing a total 
 * catalog URL rewrite if only updates and inserts are happening. Deletion of products
 * will require a complete reindex. As we typically just disable unused products from the site,
 * this should work in most cases.
 */

function rdi_magento_save()
{
    global $cart, $index_one_at_a_time;

    $rows = $cart->get_db()->rows("select product_id from rdi_magento_save");


    if (isset($index_one_at_a_time) && $index_one_at_a_time == 1)
    {
        foreach ($rows as $product_id)
        {

            $product = Mage::getModel('catalog/product')->load($product_id);
            Mage::getSingleton('index/indexer')->processEntityAction(
                    $product, Mage_Catalog_Model_Product::ENTITY, Mage_Index_Model_Event::TYPE_SAVE);
        }
    }
}

function get_rdi_staging_products()
{
    global $db_lib, $cart, $rdi_stating_products;

    if (!isset($rdi_staging_products))
    {

        $attributes = $cart->get_db()->cells("SELECT attribute_code,attribute_id FROM {$cart->get_db()->get_db_prefix()}eav_attribute
														INNER JOIN {$cart->get_db()->get_db_prefix()}eav_entity_type on {$cart->get_db()->get_db_prefix()}eav_entity_type.entity_type_id = {$cart->get_db()->get_db_prefix()}eav_attribute.entity_type_id
														AND {$cart->get_db()->get_db_prefix()}eav_entity_type.entity_type_code = 'catalog_product'", "attribute_id", "attribute_code");

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
							where r.attribute_id = {$attributes['related_id']}", 'entity_id');
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

Class rdi_cache_model extends Mage_Core_Model_Cache {

    public function rdi_clear_cache($_ids, $type = '')
    {
        global $cart;

        //$cart->_var_dump($this->_frontend->getTags());
        //$cart->_var_dump($this->_frontend->getIdsMatchingAnyTags(array('3e0_PRODUCT_4520')));

        $tags = array();

        //flush every product_id
        if (!empty($_ids))
        {
            foreach ($_ids as $id)
            {
                $tags[] = "{$this->_idPrefix}{$type}_{$id}";
            }

            if (!empty($tags))
            {
                foreach ($this->_frontend->getIdsMatchingAnyTags($tags) as $id)
                {
                    $this->remove($id);
                }
            }
        }
        //$cart->_var_dump($tags);		
        //$cart->_var_dump($this->_frontend->getTags());
        //$cart->_var_dump($this->_frontend->getIdsMatchingAnyTags(array('3e0_PRODUCT_4520')));
    }

}

function indexer_clear_cache()
{
    global $benchmarker;

    $benchmarker->set_start("Clear Cache", "Clear Cache");
    $r = new rdi_cache_model();

    $product_ids = get_rdi_staging_products();

    $r->rdi_clear_cache($product_ids, 'PRODUCT');


    $category_ids = get_rdi_staging_categories();
    $r->rdi_clear_cache($category_ids, 'CATEGORY');

    $benchmarker->set_end("Clear Cache", "Clear Cache");
}

?>