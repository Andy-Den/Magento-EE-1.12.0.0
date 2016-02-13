<?php

/**
 * Description of class
 *
 * @author PBliss
 * @package Core\Export\Customers
 */
class rdi_export_customers extends rdi_general {
    
    public $pos;
    public $cart;
    /**
     * Gets query information from cart.
     * Gets data from pos.
     * Export Data to pos tables.
     * 
     * @global rdi_lib $cart
     * @global rdi_lib $pos
     * @global rdi_debug $debug
     * @global type $benchmarker
     * @package  ImportExport
     */
    public function export_customers()
    {
        global $cart, $pos, $benchmarker;
        $this->pos = $pos->get_processor("rdi_pos_export_customers");
        $this->cart = $cart->get_processor("rdi_cart_export_customers");
              
        //hit the preload functions for the libraries
        $this->pos->pre_load();
        $this->cart->pre_load();
                 
        //query the cart lib for the customers that need to be downloaded
        $benchmarker->set_start("rdi_export_customers", "Checking for customers for export");
        $customers_for_export = $this->cart->get_customers_for_export();        
        $benchmarker->set_end("rdi_export_customers", "Checking for customers for export"); 
        
        $benchmarker->set_end("rdi_export_customers", "Processing customers for export");
        
        //pass the customers on to the pos lib
        if(!empty($customers_for_export))
        {
            $count = count($customers_for_export);
            $benchmarker->set_start("rdi_export_customers", "Processing {$count} customers for export");
            
            foreach($customers_for_export as $customer_record)
            {            
                $this->pos->process_customer_record($customer_record);
            }
            $benchmarker->set_end("rdi_export_customers", "Processing {$count} customers for export");
        }
        
        //hit the post load functions
        $this->pos->post_load();
        $this->cart->post_load();
    }
    
    /**
     * Need to move function above into these.
     * Will convert later to v2 of the export library.
     */
    public function get_customers_for_export()
    {
        
    }
    
    /**
     * 
     */
    public function export_customers_to_pos()
    {
        
    }
    
}
?>
