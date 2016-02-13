<?php

/*
 * Load images into the magento cache
 */

/**
 * Description of rdi_cart_image_load
 *
 * @author PBliss
 */
class rdi_pos_return_export extends rdi_cart_return_export
{
	/*const UPSELL_TABLE 		= "smyth_in_related_products";
	const UPSELL_ALIAS 		= "related_product";
	const UPSELL_PARENT 	= "style_id";
	const UPSELL_PRODUCT	= "related_style_id";
	*/
	const POS_ORDER_ID	= "orderid";
	
	
	public $export_tables = array('header' => 'rpro_out_returns', 'items' => 'rpro_out_returns_items','billing'=>'rpro_out_return_customer','shipping'=>'rpro_out_return_shipto_customer');
	
	public function insert_data()
	{		
		if(empty($this->returns[0]))
		{return $this;}
	
		foreach($this->returns as $return)
		{
			//@todo needs to error check of this.
			$order_id = $return[$this->CART_ORDER_ID];
			
			foreach($return as $return_table => $records)
			{
				if(!is_array($records))
				{
					continue;
				}

				/*$this->_echo("Return table");
				$this->_print_r($return_table);
				$this->_echo("records");
				$this->_print_r($records);
				*/
				
				
				if(is_numeric(current(array_keys($records))))
				{
					foreach($records as $record)
					{
						$record[$this->POS_ORDER_ID] = $order_id;
						
						$this->db_connection->insertAr2($this->export_tables[$return_table], $record);
					}
				}
				else
				{
					$records[$this->POS_ORDER_ID] = $order_id;
					//$this->db_connection->create_table_from_data_keys($this->export_tables[$return_table], $records);
					$this->db_connection->insertAr2($this->export_tables[$return_table], $records);
				}
				
			}
			$this->mark_exported($order_id);
		}
		
		return $this;
	}	
	
}

?>
