<?php

/**
 * 
 */

/**
 * Description of magento_rdi_cart_so_status_load
 *
 * @author PBliss
 * @package    Core\Load\SOStatus\Magento
 */
class rdi_cart_so_status_load extends rdi_general {

    /**
     * Class Constructor
     *
     * @param rdi_catalog_load $db
     */
    public function rdi_cart_so_status_load($db = '')
    {
        $this->check_order_lib_version();

        if ($db)
            $this->set_db($db);
    }

    public function pre_load()
    {
        global $hook_handler;


        $hook_handler->call_hook("cart_so_status_pre_load");
    }

    public function post_load()
    {
        global $hook_handler;

        $hook_handler->call_hook("cart_so_status_post_load");
    }

    //mark the order as having been invoiced
    public function process_so_status_record($so_record)
    {
        global $cart, $debug, $payment_capture, $order_prefix;

        //remove any order prefix
        if (isset($order_prefix) && strlen($so_record['increment_id']) > 10)
        {
            $prefix_length = strlen($order_prefix);
            $so_record['increment_id'] = substr($so_record['increment_id'], $prefix_length);
        }

        $this->mark_order($so_record['increment_id']);
    }

    //invoice the order, just pass off the data to the library used here
    public function invoice_order($order_data_record)
    {
        global $order_prefix, $cart, $payment_capture;

        if (isset($order_prefix) && strlen($order_data_record['increment_id']) > 10)
        {
            $prefix_length = strlen($order_prefix);
            $order_data_record['increment_id'] = substr($order_data_record['increment_id'], $prefix_length);
        }

        if ($payment_capture == 'delayed')
        {
            //call the function in the library used
            rdi_order_lib_invoice_order($order_data_record);
        }
        else
        {
            //set the status to show that the order was already captured
            $sql = "UPDATE {$this->prefix}sales_flat_order
                    SET                        
                        rdi_upload_status = 2
                    WHERE
                        increment_id = '{$order_data_record['increment_id']}'
                        AND (rdi_upload_status = 1 OR rdi_upload_status = 2)";

            $cart->get_db()->exec($sql);
        }
    }

    //process the addition of tracking info to the order
    public function process_shipment($order_data_record, $shipment_info, $shipment_item_info)
    {
        global $order_prefix;

        if (isset($order_prefix) && strlen($order_data_record['increment_id']) > 10)
        {
            $prefix_length = strlen($order_prefix);
            $order_data_record['increment_id'] = substr($order_data_record['increment_id'], $prefix_length);
        }

        // create the shipment document        
        rdi_process_shipment($order_data_record['increment_id'], $shipment_info, $shipment_item_info);
    }

    //the base information for a shipment
    public function get_shipment_info_load_parameters()
    {
        global $field_mapping, $debug;

        $parameters = array();

        $fields = "";
        $join = "";
        $table = '';
        $where = "";
        $group_by = "";
        $order_by = '';

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

    //the items for the specified shipment
    public function get_shipment_items_load_parameters()
    {
        global $field_mapping, $debug;

        $parameters = array();

        $fields = "";
        $join = "";
        $table = '';
        $where = "";
        $group_by = "";
        $order_by = '';

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

    public function get_so_load_parameters()
    {
        global $field_mapping, $debug, $order_prefix;

        $parameters = array();

        $fields = "{$this->prefix}sales_flat_order.rdi_upload_status, {$this->prefix}sales_flat_order.rdi_shipper_created";

        if (isset($order_prefix))
        {
            $join = "INNER JOIN {$this->prefix}sales_flat_order ON concat('{$order_prefix}', {$this->prefix}sales_flat_order.increment_id) = "
                    . $field_mapping->map_field('so_status', 'increment_id');
        }
        else
        {
            $join = "INNER JOIN {$this->prefix}sales_flat_order ON {$this->prefix}sales_flat_order.increment_id = "
                    . $field_mapping->map_field('so_status', 'increment_id');
        }
        $table = '';
        $where = "({$this->prefix}sales_flat_order.rdi_shipper_created = 0 
                    OR {$this->prefix}sales_flat_order.status != 'complete' )";
        $group_by = '';
        $order_by = '';

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

    public function mark_order($increment_id)
    {
        global $allow_no_tracking_number;
        //check if we have completed
        $sql = "Select rdi_shipper_created, rdi_upload_status from {$this->prefix}sales_flat_order where increment_id = '{$increment_id}'";
        $test = $this->db_connection->row($sql);

        //@setting $allow_no_tracking_number [2-Capture Only] This wont be obvious here, but setting the option to 2 will still do everything else, but not fall through here to go to complete.
        if ($test && ($test['rdi_shipper_created'] == "1" || (isset($allow_no_tracking_number) && $allow_no_tracking_number == 1)) && $test['rdi_upload_status'] == "2")
        {
            $sql = "UPDATE {$this->prefix}sales_flat_order_grid 
                        SET status = 'complete' 
                        WHERE increment_id = '{$increment_id}'";
            $this->db_connection->exec($sql);

            $sql = "UPDATE {$this->prefix}sales_flat_order sfo 
                        SET 
                            sfo.STATUS = 'complete'
                            , sfo.state = 'complete' 
                        WHERE sfo.increment_id = '{$increment_id}'";
            $this->db_connection->exec($sql);
        }
    }

    public function cancel_orders_main($sql)
    {
        global $order_prefix;

        $_order = $this->db_connection->rows("SELECT distinct o.increment_id, r.cancel_date FROM {$sql['table']} r 
									join {$this->prefix}sales_flat_order o
									on concat('{$order_prefix}',o.increment_id) = r.{$sql['pos_field']}
									WHERE {$sql['where']}");

        if (!empty($_order))
        {
            foreach ($_order as $order)
            {
                //remove these from the table because we dont want them to be captured.
                $this->db_connection->exec("DELETE FROM {$sql['table']} WHERE {$sql['pos_field']} = '{$order_prefix}{$order['increment_id']}'");


                $this->cancel_order($order['increment_id'], $order['cancel_date']);
            }
        }
    }

    public function cancel_order($order_id, $cancel_date)
    {
        global $debug;

        $debug->write(basename(__File__), __CLASS__, __FUNCTION__, 1, $order_id);

        $api = new Mage_Sales_Model_Order_Api();

        try
        {
            if ($api->cancel($order_id))
            {
                $api->addComment($order_id, 'canceled', "Order Canceled from POS on {$cancel_date}", false);
            }
        } catch (Mage_Api_Exception $e)
        {
            $this->_print_r($e->getMessage);
        }
    }

    private function check_order_lib_version()
    {
        global $order_cart_lib_ver, $debug;

        $debug->write("magento_rdi_cart_so_status_load.php", "check_order_lib_version", "check product lib", 1, array("order_cart_lib_ver" => $order_cart_lib_ver));

        //check the versioning here
        switch ($order_cart_lib_ver)
        {
            case "1.6.x":
                {
                    require_once "libraries/cart_libs/magento/version_libs/1.6.x/magento_rdi_order_lib.php";
                    break;
                }
            case "e-1.9.x":
                {
                    require_once "libraries/cart_libs/magento/version_libs/e-1.9.x/magento_rdi_order_lib.php";
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
