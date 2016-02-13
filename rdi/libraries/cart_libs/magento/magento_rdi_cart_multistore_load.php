<?php

/**
 * This is going to untilize a new field_mapping class that exists as a parent of load/export.
 *
 * @package Core\Multistore\Magento
 */


class rdi_cart_multistore_load extends rdi_pos_multistore_load
{
	const CART_STORE_TABLE 				= "rdi_storeinventory_inventory";
	const CART_STORE_ALIAS 				= "inventory";
	const CART_STORE_KEY 				= "store_code";
	const CART_STORE_ID 				= "entity_id";
	const CART_STORE_QTY 				= "qty";
	const CART_STORE_QTY_TABLE 			= "rdi_storeinventory_inventory_product";
	const CART_STORE_QTY_ALIAS 			= "inventory_product";
	const CART_STORE_QTY_PRODUCT_ID 	= "product_id";
	const CART_STORE_QTY_PARENT_ID 		= "inventory_id";
	const CART_PRODUCT_RELATED_ID_TABLE = "catalog_product_entity_varchar";
	const CART_PRODUCT_RELATED_ID_ALIAS = "related_id";
	const CART_PRODUCT_RELATED_ID_KEY 	= "value";
	const CART_PRODUCT_ID 				= "entity_id";
	
	//public $cart_mapping = array("fields" => "", "join" => "");
	    
    public function insert()
    {
		if($this->test_setting(__FUNCTION__))
		{
			$this->insert_store_inventory()->insert_store_inventory_product();
		}
		
		return $this;
	}
	
	public function insert_store_inventory()
	{
		if($this->test_setting(__FUNCTION__))
		{		
			$get_store_inventorys = $this->get_store_inventorys();
			
			if(!empty($get_store_inventorys))
			{
				foreach($get_store_inventorys as $get_store_inventory)
				{
					//insert_store()
					$this->db_connection->insertAr2("{$this->CART_STORE_TABLE}", $get_store_inventory, false, array("store_id"), false, false);
				}
			}
		}
		
		return $this;
	}

	public function get_store_inventorys()
	{
		$fields = $this->map_field_type($this->load_type,false, 'store_inventory')->fields_list();
			
		if(strlen($fields) > 0)
		{
			//get_stores()
			return $this->db_connection->rows("SELECT DISTINCT {$this->STORE_ALIAS}.{$this->STORE_KEY} as store_id, {$fields} FROM {$this->STORE_TABLE} {$this->STORE_ALIAS}
											LEFT JOIN {$this->prefix}{$this->CART_STORE_TABLE} {$this->CART_STORE_ALIAS}
											ON {$this->CART_STORE_ALIAS}.{$this->CART_STORE_KEY} = {$this->STORE_ALIAS}.{$this->STORE_KEY}
											WHERE {$this->CART_STORE_ALIAS}.{$this->CART_STORE_KEY} IS NULL
											GROUP BY {$this->STORE_ALIAS}.{$this->STORE_KEY}");		
		}
		return null;
	}
	
	public function insert_store_inventory_product()
	{
		if($this->test_setting(__FUNCTION__))
		{		
			$store_inventory_products = $this->get_store_inventory_products();
			
			if(!empty($store_inventory_products))
			{
				foreach($store_inventory_products as $store_inventory_product)
				{
					//insert_store()
					$this->db_connection->insertAr2("{$this->CART_STORE_QTY_TABLE}", $store_inventory_product,false, array(), false, false);
				}
			}
		}
		
		return $this;
	}

	public function get_store_inventory_products()
	{
		$fields = $this->map_field_type($this->load_type,false, 'store_inventory_product')->fields_list();
			
		if(strlen($fields) > 0)
		{
			//get_stores()
			return $this->db_connection->rows("SELECT DISTINCT 
													{$this->CART_PRODUCT_RELATED_ID_ALIAS}.{$this->CART_PRODUCT_ID} as {$this->CART_STORE_QTY_PRODUCT_ID},
													{$this->CART_STORE_ALIAS}.{$this->CART_STORE_ID} as {$this->CART_STORE_QTY_PARENT_ID},
													{$fields} 
													FROM {$this->STORE_TABLE} {$this->STORE_ALIAS}
													JOIN {$this->STORE_QTY_TABLE} {$this->STORE_QTY_ALIAS}
													ON {$this->STORE_QTY_ALIAS}.{$this->STORE_KEY} = {$this->STORE_ALIAS}.{$this->STORE_KEY}
													JOIN {$this->prefix}{$this->CART_STORE_TABLE} {$this->CART_STORE_ALIAS}
													ON {$this->CART_STORE_ALIAS}.{$this->CART_STORE_KEY} = {$this->STORE_ALIAS}.{$this->STORE_KEY}
													JOIN {$this->prefix}{$this->CART_PRODUCT_RELATED_ID_TABLE} {$this->CART_PRODUCT_RELATED_ID_ALIAS}
													ON {$this->CART_PRODUCT_RELATED_ID_ALIAS}.{$this->CART_PRODUCT_RELATED_ID_KEY} = {$this->STORE_QTY_ALIAS}.{$this->STORE_QTY_KEY}
													LEFT JOIN {$this->CART_STORE_QTY_TABLE} {$this->CART_STORE_QTY_ALIAS}
													ON {$this->CART_STORE_QTY_ALIAS}.{$this->CART_STORE_QTY_PRODUCT_ID} = {$this->CART_PRODUCT_RELATED_ID_ALIAS}.{$this->CART_PRODUCT_ID}
													WHERE {$this->CART_STORE_QTY_ALIAS}.{$this->CART_STORE_QTY_PRODUCT_ID} IS NULL");
		}
		return null;
	}

    
    public function update()
    {
		if($this->test_setting(__FUNCTION__))
		{
			
		}
        
        return $this;
    }
    
	//everything called here is specific to magento. Should not be joining to the point of sale.
    public function cart_load()
    {
        $this->add_update_parents_to_store();
		
        return $this;
    }
	
	
	public function add_update_parents_to_store()
	{
		//need a reverse mapping here to get the quantity field.
		
		$store_inventory_products = $this->db_connection->rows("SELECT DISTINCT 
																sl.parent_id as {$this->CART_STORE_QTY_PRODUCT_ID}, 
																{$this->CART_STORE_QTY_ALIAS}.{$this->CART_STORE_QTY_PARENT_ID},
																SUM({$this->CART_STORE_QTY_ALIAS}.{$this->CART_STORE_QTY}) as {$this->CART_STORE_QTY}
																FROM {$this->CART_STORE_QTY_TABLE} {$this->CART_STORE_QTY_ALIAS}
																JOIN catalog_product_super_link sl
																ON sl.product_id = {$this->CART_STORE_QTY_ALIAS}.{$this->CART_STORE_QTY_PRODUCT_ID}
																GROUP BY sl.parent_id");
							
		if(!empty($store_inventory_products))
		{
			foreach($store_inventory_products as $store_inventory_product)
			{
				$this->db_connection->insertAr2("{$this->CART_STORE_QTY_TABLE}", $store_inventory_product,false, array(), false, false);
			}
		}
		return $this;
	}
    
    
}


?>
