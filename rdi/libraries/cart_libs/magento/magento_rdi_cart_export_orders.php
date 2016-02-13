<?php
/**
 * 
 */

/**
 * Description of magento_rdi_cart_export_orders
 *
 * @author PBliss
 * @author PMBliss <pmbliss@retaildimensions.com>
 * @package    Core\Export\Orders\Magento
 */
class rdi_cart_export_orders extends rdi_general 
{
    public function rdi_cart_export_orders($db = '')
    {
        $this->check_order_lib_version(); 
        
        if ($db)
            $this->set_db($db);      
    }
    
    public function pre_load()
    {
        global $hook_handler; 
		
        $this->update_related_ids();      
        
        $hook_handler->call_hook("cart_export_orders_pre_load");
    }
    
    public function post_load()    
    {
        global $hook_handler, $export_gift_registry_orders, $cart;
        
        if(isset($export_gift_registry_orders) && $export_gift_registry_orders == 1)
        {
            $cart->get_processor('rdi_cart_gift_reg')->update_so_ref();
        }
        
        $hook_handler->call_hook("cart_export_orders_post_load");
    }
    
     /*
     * get a list of orders for export to the pos
     */
    public function get_orders_for_export()
    {
        global $debug, $order_export_status, $default_site, $use_multisite;
             
                
        if(strpos($order_export_status, "'") === false)
        {                
            $ord_stats = explode(',', $order_export_status);

            $order_export_status = '';

            foreach($ord_stats as $s)
            {                       
              $order_export_status .= "'{$s}',";
            }
            
            $order_export_status = substr($order_export_status,0,-1);
        }     
		
		$join = '';
		
		if(isset($use_multisite) && $use_multisite == 1)
		{
			$join = "join {$this->prefix}core_store s
						on s.store_id = so.store_id
						and s.website_id = {$default_site}";
		}
        
        $sql = "SELECT
			so.entity_id			
		FROM {$this->prefix}sales_flat_order so
		{$join}
		WHERE so.rdi_upload_status = 0
		AND so.status in ({$order_export_status})";

        $rows = $this->db_connection->rows($sql);

        $order_records = array();
        
        if (is_array($rows)) 
        {
            foreach($rows as $row)
            {
                //process the order record
                $order_records[] = rdi_order_lib_process_order($row['entity_id']);
                /*
                //mark the order as having been downloaded
                $sql = "update			
                        {$this->prefix}sales_flat_order 
                        set rdi_upload_status = 1
                        where entity_id = {$row['entity_id']}";

                $rows = $this->db_connection->rows($sql);*/
            }        
        }
        
        return $order_records;
    }       
    
    /*
     * only planning support for the mage, but to keep things consistent having this here, and incase its needed for some reason
     */
    private function check_order_lib_version()
    {              
        global $order_cart_lib_ver, $debug, $rdi_path, $cart_type;
        
        $debug->write("magento_rdi_cart_order_load.php", "check_order_lib_version", "checking lib version", 0, array("order_cart_lib_ver" => $order_cart_lib_ver));
        
        require_once $rdi_path . "libraries/cart_libs/{$cart_type}/version_libs/{$order_cart_lib_ver}/{$cart_type}_rdi_order_lib.php";
    }
    
    public function create_annonymous_customers()    
    {
        global $order_cart_lib_ver, $debug, $rdi_path, $cart_type;
        
        $debug->write("magento_rdi_cart_export_orders.php", "create_annonymous_customers", "create annonymous", 0, array("order_cart_lib_ver" => $order_cart_lib_ver));
        
		//create annoymous customers
		//this happens after the order status is updated to 1 so we will be looking at statuses less than 2.
		// distinct so we don't add multiple of the same emails.
		
		/*$sql = "INSERT INTO {$this->prefix}customer_entity(email) 
				SELECT DISTINCT customer_email FROM {$this->prefix}sales_flat_order 
				LEFT JOIN {$this->prefix}customer_entity ce
				ON ce.email = {$this->prefix}sales_flat_order.customer_email
				WHERE rdi_upload_status < 2                                 
				and sales_flat_order.customer_email is not null
				AND ce.email IS NULL
				AND customer_id IS NULL";

		$this->db_connection->exec($sql);*/
                
                
                if($this->db_connection->column_exists('rdi_customer_email', 'email'))
		{
			$this->db_connection->exec("UPDATE {$this->prefix}customer_entity e
							JOIN rdi_customer_email r
							ON r.email = e.email
							SET e.related_id = r.related_id
							WHERE e.related_id IS NULL");			
		}
		
    }
	
    public function update_criteria_annonymous_customers()    
    {
        global $order_cart_lib_ver, $debug, $rdi_path, $cart_type;
        
        $debug->write("magento_rdi_cart_export_orders.php", "create_annonymous_customers", "create annonymous", 0, array("order_cart_lib_ver" => $order_cart_lib_ver));
        
		$criteria = array();
		
		//create annoymous customers criteria for updating the orders out table.
		$criteria['join'] = " JOIN {$this->prefix}customer_entity ";
		$criteria['on'] = " {$this->prefix}customer_entity.email ";
		$criteria['set'] = " {$this->prefix}customer_entity.entity_id ";
		//$criteria['where'] = ' AND customer_entity.website_id IS NULL ';

		return $criteria;
		
    }
    
    /**
     * @author PMBLISS 
     * @return array(orderid, comment, customer_note)
     * @date 01292014
     * @see rdi_pos_export_orders::set_customer_notes
     * @param string $order_table order table from the pos
     * @param string $order_field order field from the pos     * 
     */
    public function get_customer_notes($order_table, $order_field)
    {
        return $this->db_connection->rows("SELECT o.increment_id as orderid, GROUP_CONCAT(h.comment) AS `comment`, GROUP_CONCAT(o.customer_note) AS customer_note FROM {$this->prefix}sales_flat_order_status_history h
                                    JOIN {$this->prefix}sales_flat_order o
                                    ON o.entity_id = h.parent_id
                                    JOIN {$order_table} AS so
                                    ON so.{$order_field} = o.increment_id
                                    WHERE h.comment IS NOT NULL OR customer_note IS NOT NULL
                                    GROUP BY h.parent_id");        
    }
    
    public function mark_orders($parameters)
    {
        global $order_prefix, $test_export_orders;
        
        if(isset($test_export_orders) && $test_export_orders == 1)
        {
                return false;
        }
        
        //check if the rdi_upload_date exists
        $columns = $this->db_connection->cells("SHOW COLUMNS FROM {$this->prefix}sales_flat_order_item", "Field");
        
        $rdi_upload_date = in_array("rdi_upload_date", $columns)?",
                    rdi_upload_date = NOW()":"";
        
        $gmt_date = Mage::getModel('core/date')->gmtDate();
		
		//adds a downloaded note to the history.
        $this->db_connection->exec("INSERT INTO {$this->prefix}sales_flat_order_status_history
            (parent_id,
             is_customer_notified,
             is_visible_on_front,
             COMMENT,
             STATUS,
             created_at,
             entity_name) 
SELECT o.entity_id AS parent_id, 0 AS is_customer_notified,0 AS is_visible_on_front, 'Order downloaded by Point of Sale.' AS COMMENT, 'rdi_downloaded' AS STATUS, '{$gmt_date}' AS created_at, 'order' AS entity_name FROM    {$this->prefix}sales_flat_order o
                    JOIN {$parameters['table']} so
                    ON {$parameters['field']} = o.increment_id");
        
        $this->db_connection->exec("UPDATE {$this->prefix}sales_flat_order o
                    JOIN {$parameters['table']} so
                    ON {$parameters['field']} = o.increment_id
                    SET rdi_upload_status = 1 {$rdi_upload_date}");
    }
    
    
    
	public function update_related_ids()
	{
            //todo add check if the sales_flat_order_item column exists on the table before doing these updates.
		$attributes = $this->db_connection->cells("SELECT attribute_id, attribute_code FROM {$this->prefix}eav_attribute ea
												join {$this->prefix}eav_entity_type et
												on et.entity_type_id = ea.entity_type_id
												and et.entity_type_code = 'catalog_product'","attribute_id","attribute_code");
		
		$this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_varchar r
										JOIN {$this->prefix}sales_flat_order_item i
										ON i.product_id = r.entity_id
										SET i.related_id = r.value
										WHERE r.attribute_id = {$attributes['related_id']} AND r.value IS NOT NULL 
										AND i.related_id IS NULL;");



		$this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_varchar r
										JOIN {$this->prefix}sales_flat_order_item i
										ON i.product_id = r.entity_id
										SET i.related_parent_id = r.value
										WHERE r.attribute_id = {$attributes['related_parent_id']} AND r.value IS NOT NULL 
										AND i.related_parent_id IS NULL");
	}
    
    
}

?>
