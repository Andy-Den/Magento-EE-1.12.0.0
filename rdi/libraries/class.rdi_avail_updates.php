<?php

/**
 * This call updates stock based on availability rules.
 * 
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @date    12272013
 * @package Core\Load\Product\Avail
 * @author  PMBliss <pmbliss@retaildimensions.com
 */
class rdi_avail_updates extends rdi_general {
          
    public function rdi_avail_updates($db = '')
    {
         if ($db)
            $this->set_db($db);  
    }
    /**
     * 
     * @global rdi_lib $cart
     * @global rdi_debug $debug
     * @global rdi_benchmark $benchmarker
     */
    public function load()
    {
		global $cart, $benchmarker;
		
		$this->set_benchmarker();
		
		$benchmarker->set_start_time(basename(__FILE__), __CLASS__ , __FUNCTION__);
		
		/*$this->db_connection->exec("UPDATE catalog_product_entity_varchar avail
									JOIN catalog_product_entity_varchar rp
									ON rp.entity_id = avail.entity_id
									AND rp.attribute_id = 136
									JOIN rpro_in_styles style
									ON style.style_sid = rp.value
									set avail.value = style.avail
									where avail.attribute_id = 142
									");*/
		
		
		//load all the items with stock for the cart
		$cart->get_processor("rdi_cart_avail_updates")->load();
		
		//load all the others that join the above together.
		$cart->get_processor("rdi_cart_avail_updates")->set_nonstock_items()->load();
		
		//load all the others that join the above together.
		$cart->get_processor("rdi_cart_product_is_saleable")->load();
		
		$benchmarker->set_end_time(basename(__FILE__), __CLASS__ , __FUNCTION__);
      
    }
    
    /**
     * Quick function for setting a benchmarker class into a subclass
     * @global rdi_benchmark $benchmarker
     * @global setting $benchmark_global_display_screen
     * @global setting $benchmark_global_save_db
     */
    
    public function set_benchmarker()
    {
            global $benchmarker, $benchmark_global_display_screen, $benchmark_global_save_db;
            
            if(!isset($benchmarker) || !is_a("rdi_benchmark",$benchmarker))
            {
                    $benchmarker = new rdi_benchmark($this->db_connection, $benchmark_global_display_screen, $benchmark_global_save_db);
            }
    }
}
?>
