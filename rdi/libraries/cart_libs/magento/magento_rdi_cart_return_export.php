<?php

/*
 * ALTER TABLE `sales_flat_creditmemo`   
  ADD COLUMN `rdi_upload_status` INT(3) DEFAULT 0  NULL  COMMENT 'RDi Upload Status' AFTER `discount_description`;

 */

/**
 * Description of rdi_cart_image_load
 *
 * @author PBliss
 */
class rdi_cart_return_export extends rdi_export_return 
{
	const CART_STORE_TABLE 				= "rdi_storeinventory_inventory";
	const CART_STORE_ALIAS 				= "inventory";
	const CART_STORE_KEY 				= "store_code";
	const CART_STORE_ID 				= "entity_id";
	const CART_STORE_QTY 				= "qty";
	//used for the key in the staging table.
	const CART_ORDER_ID 				= "entity_id";
		
	public function cart_export()
	{
		return $this;
	}
		
		
    public function select_data()
    {
		$this->get_ids()->get_returns();
					
		return $this;
	}
	
	//select all the ids that need to be downloaded.
	public function get_ids()
	{
		$this->returns = $this->db_connection->rows("SELECT entity_id FROM {$this->prefix}sales_flat_creditmemo WHERE rdi_upload_status = 0");
		
		return $this;
	}
	
	public function get_returns()
	{
		if(!empty($this->returns))
		{
			foreach($this->returns as &$return)
			{
				$this->get_header($return)->get_billing($return)->get_shipping($return)->get_items($return);
			}
		}
	}
	//select all the header information
	public function get_header(&$return)
	{
		$fields = $this->map_field_type('creditmemo', 'header')->fields_list_export();
				
		if(strlen($fields) > 0)
		{
			$return['header'] = $this->db_connection->row("SELECT order.billing_address_id, order.shipping_address_id, {$fields} FROM {$this->prefix}sales_flat_creditmemo creditmemo
															join {$this->prefix}sales_flat_order `order`
															on order.entity_id = creditmemo.order_id
															WHERE creditmemo.entity_id = '{$return['entity_id']}'");
															
			$return['billing_address_id']  = $return['header']['billing_address_id'];
			$return['shipping_address_id'] = $return['header']['shipping_address_id'];

			unset($return['header']['billing_address_id'],$return['header']['shipping_address_id']);
		}
		else
		{
			$return = array();
		}
		
		return $this;
	}
	
	//select all the item info.
	public function get_items(&$return)
	{
		if(isset($return['entity_id']))
		{
			$fields = $this->map_field_type('creditmemo', 'items')->fields_list_export();
		
			if(strlen($fields) == 0)
			{
				return $this;
			}
			
			$return['items'] = $this->db_connection->rows("SELECT {$fields} FROM {$this->prefix}sales_flat_creditmemo_item creditmemo_item
																JOIN {$this->prefix}sales_flat_order_item order_item
																ON order_item.item_id = creditmemo_item.order_item_id
																AND order_item.product_type = 'simple'																  
																  LEFT JOIN {$this->prefix}sales_flat_creditmemo_item creditmemo_item_parent 
																	ON creditmemo_item_parent.order_item_id = order_item.parent_item_id
																  LEFT JOIN {$this->prefix}sales_flat_order_item order_item_parent 
																	ON order_item_parent.item_id = order_item.parent_item_id  
																WHERE creditmemo_item.parent_id = '{$return['entity_id']}'");
		}
		
		return $this;
	}
	
	public function get_billing(&$return)
	{
		$fields = $this->map_field_type('creditmemo', 'billing')->fields_list_export();
		
		if(isset($return['header']))
		{
			$return['billing'] = $this->db_connection->row("SELECT {$fields} FROM {$this->prefix}sales_flat_order_address billing
															join {$this->prefix}sales_flat_order `order`
															on order.billing_address_id = billing.entity_id
																WHERE billing.entity_id = '{$return['billing_address_id']}'");
		}
		
		return $this;
	}
	
	public function get_shipping(&$return)
	{
		$fields = $this->map_field_type('creditmemo', 'shipping')->fields_list_export();
		
		if(isset($return['header']))
		{
			$return['shipping'] = $this->db_connection->row("SELECT {$fields} FROM {$this->prefix}sales_flat_order_address shipping
															join {$this->prefix}sales_flat_order `order`
															on order.shipping_address_id = shipping.entity_id
																WHERE shipping.entity_id = '{$return['shipping_address_id']}'");
		}
		
		return $this;
	}
	
	public function mark_exported($order_id)
	{
		$this->db_connection->exec("UPDATE {$this->prefix}sales_flat_creditmemo SET rdi_upload_status = 1, rdi_upload_date = NOW() WHERE entity_id = '{$order_id}'");
	}
	
}

?>
