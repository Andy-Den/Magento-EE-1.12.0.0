<?php

/**
 * does not do anything yet. 01172013
 * @package Core\Multistore\Magento
 */


class rdi_multistore_status_load extends rdi_general {
    
    
    
    public function rdi_multistore_status_load($db = ''){
        
        $this->check_order_lib_version(); 
        
        if ($db){
            $this->set_db($db); 
        }
    }
    
    public function pre_load()
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("cart_multistore_pre_load");
    }
    
    public function post_load()    
    {
         global $hook_handler;       
        
        $hook_handler->call_hook("cart_multistore_post_load");
    }
    
    
    //get the parameters for the multistore    
     public function get_multistore_load_parameters()
    {
        global $field_mapping, $debug, $order_prefix;
        
        $parameters = array();
        
        $fields = '';
        
        if(isset($order_prefix))
        {
            $join = "";
        }
        else
        {//$field_mapping->map_field('multistore_status', 'increment_id')
            $join = "";
                    
        }   
        $table = '';
        $where = "";
        
        return array(
                        "fields" => $fields,
                        "join" => $join, 
                        "table" => $table,
                        "where" => $where,
                        "group_by" => $group_by,
                        "order_by" => $order_by

                    );
        
        return $parameters;     
    }
    
    
    private function check_order_lib_version()
    {
        global $order_cart_lib_ver, $debug;
        
        $debug->write("magento_rdi_cart_multistore_status_load.php", "check_order_lib_version", "check product lib", 1, array("order_cart_lib_ver" => $order_cart_lib_ver));
        
        //check the versioning here
        switch($order_cart_lib_ver)
        {
            case "1.6.x":
            {
                require_once "libraries/cart_libs/magento/version_libs/1.6.x/magento_rdi_order_lib.php";
                break;
            }
            case "1.7.x":
            {
                break;
            }
            case "mage":
            {
                require_once "libraries/cart_libs/magento/version_libs/mage/magento_rdi_order_lib.php";
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
