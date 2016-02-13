<?php
/**
 * 
 */

/**
 * Description of magento_rdi_cart_export_customers
 *
 * @author PBliss
 * @package    Core\Export\Customers\Magento
 */
class rdi_cart_export_customers extends rdi_general 
{   
    public function rdi_cart_export_customers($db = '')
    {
        $this->check_customer_lib_version(); 
        
        if ($db)
            $this->set_db($db);      
    }
    
    public function pre_load()
    {
        global $hook_handler, $cart;  
        
        //handles the creation of customers for annonymous checkout and updating if they become a member of the site.
        //$cart->get_processor("rdi_cart_export_orders")->create_annonymous_customers(); 
        $this->clean_annon_customers(); 
        $cart->get_processor("rdi_cart_export_orders")->update_related_ids(); 
        
        $hook_handler->call_hook("cart_export_customers_pre_load");
    }
    
    public function post_load()    
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("cart_export_customers_post_load");
    }
    
    public function get_customers_for_export()
    {
        global $debug, $order_export_status;
               
        $ord_stats = explode(',', $order_export_status);

        $order_export_status = '';

        foreach($ord_stats as $s)
        {
          $order_export_status .= "'{$s}',";
        }

        $order_export_status = substr($order_export_status,0,-1);
               
        $sql = "SELECT distinct
                    sfoa.firstname,
                    sfoa.lastname,
                    sfoa.prefix, 
                    sfoa.company,
                    sfoa.street,                           
                    sfoa.city,
                    sfoa.country_id,
                    sfoa.region,
                    sfoa.postcode,
                    sfoa.telephone,
                    sfoa.fax, 
                    ifnull(sfoa.email, {$this->prefix}customer_entity.email) as email,
                    ifnull(so.customer_id,email.entity_id) as entity_id,
                    ifnull({$this->prefix}customer_entity.related_id,email.related_id) as related_id
		FROM {$this->prefix}sales_flat_order so
                inner join {$this->prefix}sales_flat_order_address sfoa
                on so.entity_id = sfoa.parent_id 
                left join {$this->prefix}customer_entity 
                on {$this->prefix}customer_entity.entity_id = so.customer_id
                LEFT join {$this->prefix}customer_entity email
                on email.email = so.customer_email 
		WHERE so.rdi_upload_status = 0 
                and so.status in ({$order_export_status})
                and sfoa.address_type = 'billing'";

        $rows = $this->db_connection->rows($sql);
              
        $customer_records = array();
        
        if (is_array($rows)) 
        {           
            foreach($rows as $row)
            {
                //process the order record
                $customer_records[] = rdi_customer_lib_process_customer($row);                                                
            }        
        }
        
        return $customer_records;
    }
    
    //if a customer creates an annon order then creates a real login on the website. The will be created twice in retail pro with two ids.
    //leave the shell customer, it will be okay. We just need to put the related_id on the new customer and should be okay.
    public function clean_annon_customers()
    {
        $this->db_connection->exec("UPDATE {$this->prefix}customer_entity `real`
                                    JOIN {$this->prefix}customer_entity `fake`
                                    ON real.email = fake.email
                                    AND fake.website_id IS NULL
                                    AND fake.related_id is not null
                                    SET real.related_id = fake.related_id
                                    WHERE real.website_id IS NOT NULL 
                                    AND real.related_id IS NULL");
        return $this;
        
    }
    
    /**
     * 
     * @global type $customer_cart_lib_ver
     * @global type $debug
     * @global type $rdi_path
     */
    private function check_customer_lib_version()
    {
        global $customer_cart_lib_ver, $debug, $rdi_path;
        
        $debug->write("magento_rdi_cart_export_customer.php", "check_customer_lib_version", "checking lib version", 0, array("customer_cart_lib_ver" => $customer_cart_lib_ver));
        
        require_once $rdi_path . "libraries/cart_libs/magento/version_libs/{$customer_cart_lib_ver}/magento_rdi_customer_lib.php";
    }
}

?>
