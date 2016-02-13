<?php

/*
 * 
 */

/**
 * Description of magento_rdi_cart_customer_load
 *
 * @author PBliss
 * @package Core\Load\Customers\Magento
 */
class rdi_cart_customer_load extends rdi_general
{
   /**
     * Class Constructor
     *
     * @param rdi_catalog_load $db
     */
    public function rdi_cart_customer_load($db = '')
    {
        $this->check_customer_lib_version(); 

        if ($db)
            $this->set_db($db);                 
    }

    public function pre_load()
    {
       global $hook_handler, $cart;       
       
       $cart->get_processor("rdi_cart_export_orders")->create_annonymous_customers();
       
       $hook_handler->call_hook("cart_customer_load_pre_load");
    }
    
    public function post_load()    
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("cart_customer_load_post_load");
    }
    
    public function get_customer_insert_parameters()
    {
        global $field_mapping, $hook_handler;
        
        $customer_update_parameters = array(                        
                        "join" => "INNER JOIN {$this->prefix}customer_entity ON " . $field_mapping->map_field('customer_in', 'entity_id') . " = {$this->prefix}customer_entity.entity_id",            
                        "where" => "{$this->prefix}customer_entity.entity_id is null",
                        "group_by" => '',        
                        "order_by" => '',
                        "fields" => "{$this->prefix}customer_entity.related_id = " . $field_mapping->map_field('customer_in', 'related_id')            
                    );
        
        $hook_handler->call_hook("cart_get_customer_update_parameters", $customer_update_parameters);
        
        return $customer_update_parameters;
    }
    
    public function get_customer_update_parameters()
    {
        global $field_mapping, $hook_handler, $debug;
        
         $customer_update_parameters = array(                        
                        "join" => "INNER JOIN {$this->prefix}customer_entity ON " . $field_mapping->map_field('customer_in', 'entity_id') . " = {$this->prefix}customer_entity.entity_id",            
                        "where" => '',
                        "group_by" => '',        
                        "order_by" => '',
                        "fields" => "{$this->prefix}customer_entity.related_id = " . $field_mapping->map_field('customer_in', 'related_id')            
                    );
        
        $hook_handler->call_hook("cart_get_customer_update_parameters", $customer_update_parameters);
        
//        $debug->write("magento_rdi_cart_customer_load.php", "get_customer_update_parameters", "parameters", 2, array("join" => $join,
//                                                                                                                   "where" => $where,
//                                                                                                                   "group_by" => $group_by,
//                                                                                                                   "order_by" => $order_by));
        
        return $customer_update_parameters;
    }
    
    public function process_customer_records($customer_records, $update_parameter = '')    
    {
        if(is_array($customer_records))
        {
            foreach($customer_records as $customer_record)
            {     
                $sql = "SELECT {$this->prefix}customer_entity.entity_id from {$this->prefix}customer_entity where related_id = '{$customer_record['related_id']}'";                
                $customer_record['entity_id'] = $this->db_connection->cell($sql, "entity_id");
                
                if(!$customer_record['entity_id'])
                {
                    $sql = "SELECT {$this->prefix}customer_entity.entity_id from {$this->prefix}customer_entity where email = '{$customer_record['email']}'";                
                    $customer_record['entity_id'] = $this->db_connection->cell($sql, "entity_id");
                }
                
                if($update_parameter == '' && !$customer_record['entity_id'])  
                {
                    rdi_customer_load($customer_record);                                                       
                }
                else
                {
                    rdi_update_customer($customer_record);
                }
            }
        }
    }
    
    private function check_customer_lib_version()
    {
        global $customer_cart_lib_ver, $debug;
        
        $debug->write("magento_rdi_cart_customer_load.php", "check_customer_lib_version", "check customer lib", 1, array("customer_cart_lib_ver" => $customer_cart_lib_ver));
        
        //check the versioning here
        switch($customer_cart_lib_ver)
        {
            case "1.6.x":
            {
                require_once "libraries/cart_libs/magento/version_libs/1.6.x/magento_rdi_customer_lib.php";
                break;
            }
            case "1.7.x":
            {
                break;
            }
            case "mage":
            {
                require_once "libraries/cart_libs/magento/version_libs/mage/magento_rdi_customer_lib.php";
                break;
            }
            case "auto":
            {
                break;
            }
        }
    }
}

?>
