<?php

/*
 * Multistore Load Class
 */

/**
 * Multistore Load Class
 *
 * @author PMBliss
 */
class rdi_multistore_status_load extends rdi_general
{    
    public function load_multistore_statuses()
    {
        global $benchmarker, $pos, $cart, $debug, $field_mapping;
        $debug->write_message("class.rdi_multistore_status_load.php", "load_multistore_statuses", "Class constructor");
                      
        $benchmarker->set_start_time("rdi_customer_load", "load  status");
        
        //get the load parameters
        $multistore_load_parameters = $cart->get_processor("rdi_cart_multistore_status_load")->get_multistore_load_parameters();
         
       
        //get the multistore records that are ready
        $multistore_records = $pos->get_processor("rdi_pos_multistore_status_load")->get_multistore_status_data($multistore_load_parameters);

        //loop the records
        if($multistore_records)
        {
            foreach($multistore_records as $multistore_record)
            {      
                  // go to the cart and run a function in there to load the statuses(?)                     
            }
        }
        
        $benchmarker->set_end_time("rdi_customer_load", "load so status");        
    }
}
?>