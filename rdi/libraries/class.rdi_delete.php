<?php

/**
 * This handles the removal and update to the end user of the appending removal of products on the website.
 * 
 * @copyright  2005-2014 Retail Dimensions Inc.
 * 
 * @package Core\Delete
 * @author  PMBliss <pmbliss@retaildimensions.com
 */

class rdi_delete extends rdi_general {
          
    private $hook_name;
    
    public function rdi_pos_delete($db = '')
    {
        if ($db)
            $this->set_db($db);    
        
        $this->hook_name = str_replace("rdi_", "", __CLASS__);
    }
    
        
    public function load()
    {
        global $cart;
        
        $cart_delete = $cart->get_processor("rdi_cart_delete");
        
        if(is_a($cart_delete,"rdi_cart_delete"))
        {
            $cart_delete->load();            
        }
    }
}
?>
