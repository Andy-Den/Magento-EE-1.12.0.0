<?php

/**
 * Class File
 */

/**
 * SO Load Class
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Load\SOStatus
 */
class rdi_so_status_load extends rdi_general {

    /**
     * Main load Function.
     * 
     * @global rdi_benchmarker $benchmarker
     * @global type $pos
     * @global type $cart
     * @global type $field_mapping
     */
    public function load_so_statuses()
    {
        global $benchmarker, $pos, $cart, $field_mapping;

        $benchmarker->set_start_time(__CLASS__, "load so status");

        //get the load parameters
        $so_load_parameters = $cart->get_processor("rdi_cart_so_status_load")->get_so_load_parameters();
        $shipment_load_parameters = $cart->get_processor("rdi_cart_so_status_load")->get_shipment_info_load_parameters();
        $shipment_item_parameters = $cart->get_processor("rdi_cart_so_status_load")->get_shipment_items_load_parameters();

        //get the so records that are ready
        $so_records = $pos->get_processor("rdi_pos_so_status_load")->get_so_status_data($so_load_parameters);

        //loop the records
        if ($so_records)
        {
            foreach ($so_records as $so_record)
            {
                //get the tracking info for this order
                $shipment_data = $pos->get_processor("rdi_pos_so_status_load")->get_shipment_data($so_record, $shipment_load_parameters);

                if(isset($shipment_data['carrier_code']))
                {
                        $shipment_data = array($shipment_data);
                }
                
                
                
                if (is_array($shipment_data))
                {
                    foreach ($shipment_data as $shipment)
                    {

                        $shipment_item_data = $pos->get_processor("rdi_pos_so_status_load")->get_shipment_items($shipment, $shipment_item_parameters);

                        $cart->get_processor("rdi_cart_so_status_load")->process_shipment($so_record, $shipment, $shipment_item_data);
                    }
                }

                //check the order status, see if it needs invoicing            
                if ($so_record['rdi_upload_status'] == 1 && $pos->get_processor("rdi_pos_so_status_load")->order_recorded($so_record))
                {                                              
                    //invoice the order					
                    $this->_print_r($so_record);
                    
                    $cart->get_processor("rdi_cart_so_status_load")->invoice_order($so_record);
                }
                //do any extra processing needed
                $cart->get_processor("rdi_cart_so_status_load")->process_so_status_record($so_record);
            }
        }

        $benchmarker->set_end_time("rdi_customer_load", "load so status");
    }

}

?>
