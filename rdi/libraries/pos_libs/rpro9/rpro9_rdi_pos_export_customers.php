<?php
/**
 * Class File
 */
/**
 * Retail Pro 9 Export Customers Class
 * Exports data to a staging table.
 *
 * @author PMBliss<pmbliss@retaildimensions.com>
 * @copyright Retail Dimensions Inc. 2005-2014
 * 
 * @package    Core\Export\Customers\RPro9
 * @todo move the customer load functions into the main class.
 */
class rdi_pos_export_customers extends rdi_general
{
    /**
     * Constructor Function
     * @param rdi_db $db
     */
    public function pos_rdi_export_customers($db = '')
    {
         if ($db)
            $this->set_db($db);      
    }
    
    /**
     * Pre Load Function
     * @global rdi_hook $hook_handler
     * 
     */
    public function pre_load()
    {
        global $hook_handler;       
        
        //@hook pos_export_customers_pre_load
        $hook_handler->call_hook("pos_export_customers_pre_load");
    }
    
    /**
     * Post Load Function
     * @global rdi_hook $hook_handler
     * 
     */
    public function post_load()    
    {
        global $hook_handler;       
        
        //@hook pos_export_customers_post_load
        $hook_handler->call_hook("pos_export_customers_post_load");
    }
    
    /**
     * Not used.
     * @global rdi_helper_funcs $helper_funcs
     * @global rdi_debug $debug
     * @param array $customer_record
     */
    public function process_customer_record($customer_record)
    {
        global $helper_funcs, $debug;
        
        /**
         * N/A gets the customer from the SO record
         */
        
    }
}

?>
