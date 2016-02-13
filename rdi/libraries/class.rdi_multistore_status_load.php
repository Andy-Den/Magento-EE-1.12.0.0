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
    public function load()
    {
        global $benchmarker, $cart;
                   
        $benchmarker->set_start(__CLASS__, "load multistore");
        
        //the cart will load the pos, load in the field mapping.
        $multistore = $cart->get_processor('rdi_multistore_status_load');
                
        $multistore->load();
        
        $benchmarker->set_end(__CLASS__, "load multistore");        
    }
}
?>