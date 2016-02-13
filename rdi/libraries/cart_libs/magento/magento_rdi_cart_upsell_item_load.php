<?php

/*
 * Load images into the magento cache
 */

/**
 * Description of rdi_cart_image_load
 *
 * @author PBliss
 */
class rdi_cart_upsell_item_load extends rdi_pos_upsell_item_load 
{
	//holding off creating these.
	const CART_PRODUCT_TABLE 	= "rdi_storeinventory_inventory";
	const CART_PRODUCT_ALIAS 	= "rdi_storeinventory_inventory";
	const CART_PRODUCT_ID 		= "rdi_storeinventory_inventory";
	const CART_PRODUCT_KEY 		= "rdi_storeinventory_inventory";
	
	public $product_link_attributes = array();
	public $link_type_id = array();
	public $_attributes = array();
		
	public function pre_load()
	{
		$this->_attributes = $this->db_connection->cells("SELECT et.entity_type_code, ea.*
                                                        FROM {$this->prefix}eav_attribute ea
                                                        INNER JOIN {$this->prefix}eav_entity_type et
                                                        ON et.entity_type_id = ea.entity_type_id
                                                        AND et.entity_type_code = 'catalog_product'","attribute_id","attribute_code");
		
		return parent::pre_load();
	}
		
	public function insert()
	{	
		if(!$this->test_setting(__FUNCTION__))
		{
			return $this;
		}	
		
		$this->set_product_link_types();
				
		if(!empty($this->_product_link_type))
		{
			foreach($this->_product_link_type as $type)
			{
				$this->insert_product_links($type);
			}
		}
		
		return $this;
	}
	
	public function update()
	{	
		if(!$this->test_setting(__FUNCTION__))
		{
			return $this;
		}	
		
		$this->set_product_link_types();
				
		if(!empty($this->_product_link_type))
		{
			foreach($this->_product_link_type as $type)
			{
				$this->update_product_links($type);
			}
		}
		
		return $this;
	}
	
	public function delete()
	{	
		if(!$this->test_setting(__FUNCTION__))
		{
			return $this;
		}		
		
		$this->set_product_link_types();
				
		if(!empty($this->_product_link_type))
		{
			foreach($this->_product_link_type as $type)
			{
				$this->delete_product_links($type);
			}
		}
		
		return $this;
	}
	
	public function insert_product_links($type)
	{
		//get link attribute_id		
		$link_type_id = $this->get_link_type_id($type);
		
		if(!$link_type_id)
		{
			return false;
		}
		
		$upsell_data = $this->db_connection->rows("SELECT DISTINCT 
														  {$this->UPSELL_ALIAS}.{$this->UPSELL_POSITION} AS position,
														  parent.entity_id AS product_id,
														  product.entity_id AS linked_product_id
														FROM
														{$this->UPSELL_TABLE} {$this->UPSELL_ALIAS}
														  INNER JOIN {$this->prefix}catalog_product_entity_varchar parent 
															ON parent.value = {$this->UPSELL_ALIAS}.{$this->UPSELL_PARENT} 
															AND parent.attribute_id = {$this->_attributes['related_parent_id']}
														  LEFT JOIN {$this->prefix}catalog_product_super_link psl
															ON psl.product_id = parent.entity_id
														  INNER JOIN {$this->prefix}catalog_product_entity_varchar product
															ON product.value = {$this->UPSELL_ALIAS}.{$this->UPSELL_PRODUCT}
															AND product.attribute_id = {$this->_attributes['related_parent_id']}
														  LEFT JOIN {$this->prefix}catalog_product_super_link sl 
															ON sl.product_id = product.entity_id 
														  LEFT JOIN {$this->prefix}catalog_product_link pl
															ON pl.product_id = parent.entity_id 
															AND pl.linked_product_id = product.entity_id
															AND pl.link_type_id = {$link_type_id} 															
														WHERE 
														pl.link_id IS NULL
														AND	psl.product_id IS NULL 
														AND sl.product_id IS NULL");
														  		
														  //INNER JOIN {$this->prefix}catalog_product_link_type plt
															//	ON plt.code = '{$type}' 
		if(!empty($upsell_data))
		{		
			foreach ($upsell_data as $p)
			{
				$this->insert_product_link($p['product_id'], $p['linked_product_id'], $type, $p['position']);				
			}
		}

		unset($upsell_data);
	}
	
	public function insert_product_link($product_id, $linked_product_id, $type, $position)
	{
		$position_link_attribute_id = $this->get_product_link_attribute($type, 'position');
		$link_type_id = $this->get_link_type_id($type);
		
		$link_id = $this->db_connection->insert("insert into {$this->prefix}catalog_product_link(product_id, linked_product_id, link_type_id)
						values('{$product_id}', '{$linked_product_id}', '{$link_type_id}')");

		if($link_id > 0)
		{
			$this->db_connection->insert("insert into {$this->prefix}catalog_product_link_attribute_int (link_id, product_link_attribute_id, value)
											values ('{$link_id}', '{$position_link_attribute_id}', '{$position}')");
		}
	}
	
	// the only update is the position, should be one query.
	public function update_product_links($type)
	{
		//get link attribute_id		
		$position_link_attribute_id = $this->get_product_link_attribute($type, 'position');
		$link_type_id = $this->get_link_type_id($type);
		
		if(!$link_type_id)
		{
			return false;
		}
		
		$upsell_data = $this->db_connection->rows("SELECT DISTINCT 
														  plai.value_id,
														  {$this->UPSELL_ALIAS}.{$this->UPSELL_POSITION} value
														FROM
														  {$this->UPSELL_TABLE} {$this->UPSELL_ALIAS}
														  INNER JOIN {$this->prefix}catalog_product_entity_varchar parent 
															ON parent.value = {$this->UPSELL_ALIAS}.{$this->UPSELL_PARENT} 
															AND parent.attribute_id = {$this->_attributes['related_parent_id']} 
														  LEFT JOIN {$this->prefix}catalog_product_super_link psl 
															ON psl.product_id = parent.entity_id 
														  INNER JOIN {$this->prefix}catalog_product_entity_varchar product 
															ON product.value = {$this->UPSELL_ALIAS}.{$this->UPSELL_PRODUCT} 
															AND product.attribute_id = {$this->_attributes['related_parent_id']} 
														  LEFT JOIN {$this->prefix}catalog_product_super_link sl 
															ON sl.product_id = product.entity_id 
														  JOIN {$this->prefix}catalog_product_link pl 
															ON pl.product_id = parent.entity_id 
															AND pl.linked_product_id = product.entity_id 
															AND pl.link_type_id = '{$link_type_id}'
															JOIN {$this->prefix}catalog_product_link_attribute_int plai
															ON plai.link_id = pl.link_id
															AND plai.product_link_attribute_id = '{$position_link_attribute_id}'
														WHERE psl.product_id IS NULL 
														  AND sl.product_id IS NULL 
														  AND plai.value != {$this->UPSELL_ALIAS}.{$this->UPSELL_POSITION}");
														  		
														  //INNER JOIN {$this->prefix}catalog_product_link_type plt
															//	ON plt.code = '{$type}' 
		if(!empty($upsell_data))
		{		
			foreach ($upsell_data as $p)
			{
				$this->update_product_link($p['value'], $p['value_id']);				
			}
		}

		unset($upsell_data);
	}
	
	public function update_product_link($value, $value_id)
	{
		$this->db_connection->exec("UPDATE {$this->prefix}catalog_product_link_attribute_int SET value = '{$value}' WHERE value_id = '{$value_id}'");
	}
	
	
	public function delete_product_links($type)
	{
		//get link attribute_id		
		$link_type_id = $this->get_link_type_id($type);
		
		if(!$link_type_id)
		{
			return false;
		}
		
		$upsell_data = $this->db_connection->rows("SELECT pl.link_id FROM {$this->prefix}catalog_product_link pl
													  INNER JOIN {$this->prefix}catalog_product_entity_varchar parent 
														ON parent.entity_id = pl.product_id
														AND parent.attribute_id = {$this->_attributes['related_parent_id']}
													  LEFT JOIN {$this->prefix}catalog_product_super_link sl 
														ON sl.product_id = parent.entity_id 
													  INNER JOIN {$this->prefix}catalog_product_entity_varchar product 
														ON product.entity_id = pl.linked_product_id
														AND product.attribute_id = {$this->_attributes['related_parent_id']}
													  LEFT JOIN {$this->prefix}catalog_product_super_link psl
														ON psl.product_id = product.entity_id
													  JOIN   {$this->UPSELL_TABLE} upsell
													  ON   upsell.{$this->UPSELL_PARENT} = parent.value
													  LEFT JOIN   {$this->UPSELL_TABLE} {$this->UPSELL_ALIAS} 
													  ON   {$this->UPSELL_ALIAS}.{$this->UPSELL_PARENT} = parent.value
													  AND {$this->UPSELL_ALIAS}.{$this->UPSELL_PRODUCT} = product.value
													  WHERE psl.product_id IS NULL 
													  AND sl.product_id IS NULL
													  AND {$this->UPSELL_ALIAS}.{$this->UPSELL_PARENT} IS NULL");
														  		
		if(!empty($upsell_data))
		{		
			foreach ($upsell_data as $p)
			{
				$this->delete_product_link($p['link_id']);
			}
		}

		unset($upsell_data);
	}
	
	public function delete_product_link($link_id)
	{
		$link_id = $this->db_connection->exec("DELETE FROM {$this->prefix}catalog_product_link WHERE link_id = '{$link_id}'");
	}
	
	public function get_product_link_attribute($type, $attribute_code)
	{
		if(!isset($this->product_link_attributes[$type]))
		{
			$this->product_link_attributes[$type] = $this->db_connection->cells("SELECT distinct product_link_attribute_id, product_link_attribute_code FROM {$this->prefix}catalog_product_link_attribute pla
																		JOIN {$this->prefix}catalog_product_link_type plt
																		ON plt.link_type_id = pla.link_type_id
																		AND plt.code = '{$type}'
																		", "product_link_attribute_id", "product_link_attribute_code");
		}
		
		if(isset($this->product_link_attributes[$type][$attribute_code]))
		{
			return $this->product_link_attributes[$type][$attribute_code];
		}
		else
		{
			return false;
		}
	}
	
	public function get_link_type_id($link_type)
	{
		if(empty($this->link_type_id))
		{
			$this->link_type_id = $this->db_connection->cells("SELECT distinct link_type_id, code FROM {$this->prefix}catalog_product_link_type
																		", "link_type_id", "code");
		}
		
		if(isset($this->link_type_id[$link_type]))
		{
			return $this->link_type_id[$link_type];
		}
		else
		{
			return false;
		}
	}

}

?>
