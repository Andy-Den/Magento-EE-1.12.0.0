<?php
/**
 * Class File
 */

/**
 * Customer Load class
 *
 * @author PBliss
 * @author     Paul Bliss <pbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Load\Customer
 */
class rdi_customer_load extends rdi_general{
    /**
     * 
     * @global benchmarker $benchmarker
     * @global rdi_lib $pos
     * @global rdi_lib $cart
     * @global rdi_debug $debug
     * @setting $insert_customers 1-ON, 0-OFF
     * @setting $update_customers 1-ON, 0-OFF
     */
    public function load_customers()
    {
        global $benchmarker, $pos, $cart, $insert_customers, $update_customers; 
                
        
        $benchmarker->set_start(__FUNCTION__, "load");
          
        if($insert_customers == 1)
        { 
            $insert_parameters = $cart->get_processor("rdi_cart_customer_load")->get_customer_insert_parameters();
        
            $customer_records = $pos->get_processor("rdi_pos_customer_load")->get_customer_data();
        
            $cart->get_processor("rdi_cart_customer_load")->process_customer_records($customer_records);
        }
            
        if($update_customers == 1)
        {
            //set the relation of the cart customer to the pos
            $update_parameters = $cart->get_processor("rdi_cart_customer_load")->get_customer_update_parameters();
        
            $pos->get_processor("rdi_pos_customer_load")->set_customer_relation($update_parameters);
        }
        
        $benchmarker->set_end(__FUNCTION__, "load");            
    }
}
?>
