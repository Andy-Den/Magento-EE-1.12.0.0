<?php
/**
 * 
 * 
 */

/**
 * Export orders handler
 *
 @author PBliss
 * @author     Paul Bliss <pbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Export\Orders
 */
class rdi_export_orders extends rdi_general {

	public $_pos, $_cart;
	
	public function __construct($db)
	{
		global $cart, $pos;
		 
		parent::rdi_general($db);
		
		$this->_pos 	= $pos->get_processor("rdi_pos_export_orders");
		$this->_cart 	= $cart->get_processor("rdi_cart_export_orders");		
	}

    public function export_orders()
    {
        global $debug, $benchmarker;
        
        $debug->write_message("class.rdi_export_orders.php", "export_orders", "Exporting Orders");
      
        //hit the preload functions for the libraries
        $this->_pos->pre_load();
        $this->_cart->pre_load();
        
        $benchmarker->set_start_time("rdi_export_orders", "Getting orders for export");
        
        //query the cart lib for the orders that need to be downloaded
        $orders_for_export = $this->_cart->get_orders_for_export();
        
        $benchmarker->set_end_time("rdi_export_orders", "Getting orders for export");
        
        $benchmarker->set_start_time("rdi_export_orders", "Processing orders for export");
        //pass the orders on to the pos lib
        foreach($orders_for_export as $order_record)
        {
            $this->_pos->process_order_record($order_record);
        }
        $benchmarker->set_end_time("rdi_export_orders", "Processing orders for export");
        
        //Mark orders for that has successfully been downloaded.
        $this->_cart->mark_orders($this->_pos->get_parameters_for_export_status());
        
        
        //hit the post load functions
        $this->_pos->post_load();
        $this->_cart->post_load();
    }
}
?>
