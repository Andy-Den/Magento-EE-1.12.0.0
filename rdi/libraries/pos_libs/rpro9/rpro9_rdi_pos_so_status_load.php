<?php
/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Retail Pro 9 SoStatus load class
 *
 * Handles the loading of the SO data
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Load\SOStatus\RPro9
 */
class rdi_pos_so_status_load extends rdi_general 
{
    /**
     * Class Constructor
     *
     * @param rdi_catalog_load $db
     */
    public function rdi_pos_so_status_load($db = '')
    {
        if ($db)
            $this->set_db($db);        
    }
       
    /**
     * Pre Load Function
     * @global type $hook_handler
     * @hook pos_so_status_pre_load
     */
    public function pre_load()
    {
       global $hook_handler, $cart;       
        
		//this should happen in the main library and will be replaced later
		//get the cancel orders first and then pass them to the cancel function in the cart.
		$cart->get_processor('rdi_cart_so_status_load')->cancel_orders_main($this->get_cancel_order_sql_parameters());
		
		
        $hook_handler->call_hook("pos_so_status_pre_load");
    }
    
    /**
     * Post Load Function
     * @global type $hook_handler
     * @hook pos_so_status_post_load
     */
    public function post_load()    
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_so_status_post_load");
    }
    
    /**
     * Get Shipping data from the orders loaded.
     * 
     * @global rdi_debug $debug
     * @global rdi_field_mapping $field_mapping
     * @param array $order_data data from the orders
     * @param array $parameters fields
     * @return array An array of shipment data for the order.
     */ 
    public function get_shipment_data($order_data, $parameters)
    {
        global $debug, $field_mapping;
        
        $parameters = $field_mapping->prep_query_parameters("so_shipment", $parameters);      
              
        $sql = "SELECT distinct                    
                        {$parameters['fields']}
						,rpro_in_so.invc_no as document_number,
                        rpro_in_so.tracking_number as tracking_number
                FROM rpro_in_so
                WHERE 
                        SID = '{$order_data['pos_order_id']}'
						AND
						( rpro_in_so.tracking_number IS NOT NULL 
							OR
							rpro_in_so.tracking_number != '')
						AND
						( rpro_in_so.shipprovider IS NOT NULL 
							OR
							rpro_in_so.shipprovider != '')	";

        return $this->db_connection->rows($sql);                
    }
    
    public function get_shipment_items($shipment_data, $parameters)
    {
        global $debug, $field_mapping;
        
        $parameters = $field_mapping->prep_query_parameters("so_shipment_item", $parameters);      
               
        $sql = "SELECT distinct                        
                        {$parameters['fields']}
                FROM rpro_in_so
                WHERE 
                        invc_no = '{$shipment_data['document_number']}'
												
                        AND
						( rpro_in_so.tracking_number IS NOT NULL 
							OR
							rpro_in_so.tracking_number != '')
						AND
						( rpro_in_so.shipprovider IS NOT NULL 
							OR
							rpro_in_so.shipprovider != '')";
//AND rpro_in_so.tracking_number IS NOT NULL
        return $this->db_connection->rows($sql);     
    }
    
    public function get_so_status_data($parameters = array())
    {
        global $benchmarker, $field_mapping, $debug;
        
        $parameters = $field_mapping->prep_query_parameters("so_status", $parameters); 
   
		$parameters['fields'] = str_replace("rpro_in_so.tender_amt as 'rdi_cc_amount'", "rpro_in_so.tender_amt + rpro_in_so.subtotal + rpro_in_so.tax_total + rpro_in_so.fee_amt AS 'rdi_cc_amount'",$parameters['fields']);
			
		//$this->_echo($parameters['fields']);exit;
   
   
        if($parameters['fields'] != '')
            $parameters['fields'] = ", " . $parameters['fields'];
        
        //build out the query                        
        $sql = "SELECT DISTINCT    
                rpro_in_so.SID AS pos_order_id
                {$parameters['fields']}
            FROM
                rpro_in_so
            {$parameters['join']}
            LEFT JOIN rpro_in_receipts ON rpro_in_so.so_number = rpro_in_receipts.so_number
            WHERE
                rpro_in_so.SID IS NOT NULL
				
                {$parameters['where']}
                {$parameters['group_by']}
                {$parameters['order_by']}";         

        $debug->show_query("pos_get_products", $sql, "Product Class {$product_class['product_class']} Product Type: {$product_type}");

        return $this->db_connection->rows($sql);                                              
    }
	
	public function get_cancel_order_sql_parameters()
	{	
		return array('table'=>'rpro_in_so','pos_field'=>'so_number', 'where'=>'cancel_date IS NOT NULL');
	}
	
	public function order_recorded($so_record)
	{
		if(isset($so_record['pos_status']))
		{
			if($so_record['pos_status']	== '4098')
			{
				return true;
			}
		}
		else
		{
			if(!isset($so_record['pos_status']))
			{
				die("<h1>Map CART pos_status to POS status</h1>");
			}
		}
		return false;
	}
}

?>
