<?php
    require_once("../app/Mage.php");				// External script - Load magento framework
    Mage::app();

    /*
     * process the order record, build out a record to pass off to the pos
     */
    function rdi_order_lib_process_order($order_id)
    {
        global $debug, $field_mapping, $cart;
         
        $order_record = array();
        
        #this always needs to be inside the loop. otherwise we end up with bad data.
	$order = Mage::getModel('sales/order');
	
        //load the order
        $order->load($order_id);
                  
        $order_data = $order->getData();
        
        //set the related customer id
        //$order_record['base_data'][$field_mapping->map_field("order", "customer_related_id")] =  rdi_order_lib_get_customer_id($order_data);        
        $order_data['customer_related_id'] = rdi_order_lib_get_customer_id($order_data);        
        $order_record['base_data'] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order'), $order_data);                
        $order_record['bill_to_data'] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order_bill_to'), $order->getBillingAddress()->getData());
        $order_record['ship_to_data'] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order_ship_to'), $order->getShippingAddress()->getData());
                   
        //handle the card type mapping
        $order_record['base_data'][$field_mapping->map_field("order", "card_type")] = rdi_order_lib_map_credit_card($order);
         
        //Handle the shipping method mapping
        $shipping_ids = rdi_order_lib_map_shipping_method_and_provider($order);        
        $order_record['base_data'][$field_mapping->map_field("order", "shipping_method_id")] = $shipping_ids['method_id'];
        $order_record['base_data'][$field_mapping->map_field("order", "shipping_provider_id")] = $shipping_ids['provider_id'];             
                
        //get the order items data
        $order_record['item_data'] = rdi_order_lib_process_items($order_id);
                                      
        return $order_record;        	
    }
    
    function rdi_order_lib_process_items($order_id)
    {
        global $debug, $field_mapping, $cart;
        
        $item_data = array();
               
        //dont use mage for this its messy        
//	foreach ($order->getItemsCollection() as $item) 
//        {            
//            $itm = $item->getData();
//            
//            if($itm['product_type'] == "simple")
//            {            
//                $item_data[] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order_item'), $itm);
//            }
//	}
        
        $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
        $related_attribute_id = $cart->get_db()->cell("select attribute_id from eav_attribute where attribute_code = 'related_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");
                
        //get the item data through a query
        $sql = "SELECT simple.item_id, 
                simple.order_id, 
                simple.parent_item_id, 
                simple.quote_item_id,
                simple.store_id, 
                simple.created_at, 
                simple.updated_at, 
                simple.product_id, 
                simple.product_type,
                simple.product_options, 
                simple.weight, 
                simple.is_virtual, 
                simple.sku, 
                simple.name,
                simple.description,
                simple.applied_rule_ids,
                simple.additional_data, 
                simple.free_shipping, 
                simple.is_qty_decimal,
                simple.no_discount, 
                simple.qty_backordered, 
                simple.qty_canceled, 
                simple.qty_invoiced, 
                simple.qty_ordered,
                simple.qty_refunded, 
                simple.qty_shipped, 
                simple.base_cost, 
                ifnull(configurable.price, simple.price) as price,
                ifnull(configurable.base_price, simple.base_price) as base_price, 
                ifnull(configurable.original_price, simple.original_price) as original_price,
                ifnull(configurable.base_original_price, simple.base_original_price) as base_original_price, 
                ifnull(configurable.tax_percent, simple.tax_percent) as tax_percent,
                ifnull(configurable.tax_amount, simple.tax_amount) as tax_amount, 
                ifnull(configurable.base_tax_amount, simple.base_tax_amount) as base_tax_amount, 
                ifnull(configurable.tax_invoiced, simple.tax_invoiced) as tax_invoiced, 
                ifnull(configurable.base_tax_invoiced, simple.base_tax_invoiced) as base_tax_invoiced,
                ifnull(configurable.discount_percent, simple.discount_percent) as discount_percent,
                ifnull(configurable.discount_amount, simple.discount_amount) as discount_amount, 
                ifnull(configurable.base_discount_amount, simple.base_discount_amount) as base_discount_amount, 
                ifnull(configurable.discount_invoiced, simple.discount_invoiced) as discount_invoiced,
                ifnull(configurable.base_discount_invoiced, simple.base_discount_invoiced) as base_discount_invoiced, 
                ifnull(configurable.amount_refunded, simple.amount_refunded) as amount_refunded, 
                ifnull(configurable.base_amount_refunded, simple.base_amount_refunded) as base_amount_refunded, 
                ifnull(configurable.row_total, simple.row_total) as row_total,
                ifnull(configurable.base_row_total, simple.base_row_total) as base_row_total, 
                ifnull(configurable.row_invoiced, simple.row_invoiced) as row_invoiced, 
                ifnull(configurable.base_row_invoiced, simple.base_row_invoiced) as base_row_invoiced, 
                ifnull(configurable.row_weight, simple.row_weight) as row_weight,
                ifnull(configurable.base_tax_before_discount, simple.base_tax_before_discount) as base_tax_before_discount, 
                ifnull(configurable.tax_before_discount, simple.tax_before_discount) as tax_before_discount, 
                simple.ext_order_item_id, 
                simple.locked_do_invoice, 
                simple.locked_do_ship,
                ifnull(configurable.price_incl_tax, simple.price_incl_tax) as price_incl_tax, 
                ifnull(configurable.base_price_incl_tax, simple.base_price_incl_tax) as base_price_incl_tax,
                ifnull(configurable.row_total_incl_tax, simple.row_total_incl_tax) as row_total_incl_tax,
                ifnull(configurable.base_row_total_incl_tax, simple.base_row_total_incl_tax) as base_row_total_incl_tax, 
                ifnull(configurable.hidden_tax_amount, simple.hidden_tax_amount) as hidden_tax_amount, 
                ifnull(configurable.base_hidden_tax_amount, simple.base_hidden_tax_amount) as base_hidden_tax_amount, 
                ifnull(configurable.hidden_tax_invoiced, simple.hidden_tax_invoiced) as hidden_tax_invoiced, 
                ifnull(configurable.base_hidden_tax_invoiced, simple.base_hidden_tax_invoiced) as base_hidden_tax_invoiced,
                ifnull(configurable.hidden_tax_refunded, simple.hidden_tax_refunded) as hidden_tax_refunded, 
                ifnull(configurable.base_hidden_tax_refunded, simple.base_hidden_tax_refunded) as base_hidden_tax_refunded, 
                simple.is_nominal, 
                simple.tax_canceled, 
                simple.hidden_tax_canceled,
                ifnull(configurable.tax_refunded, simple.tax_refunded) as tax_refunded, simple.gift_message_id,
                simple.gift_message_available, 
                ifnull(configurable.base_weee_tax_applied_amount, simple.base_weee_tax_applied_amount) as base_weee_tax_applied_amount, 
                ifnull(configurable.base_weee_tax_applied_row_amount, simple.base_weee_tax_applied_row_amount) as base_weee_tax_applied_row_amount,
                ifnull(configurable.weee_tax_applied_amount, simple.weee_tax_applied_amount) as weee_tax_applied_amount, 
                ifnull(configurable.weee_tax_applied_row_amount, simple.weee_tax_applied_row_amount) as weee_tax_applied_row_amount, 
                ifnull(configurable.weee_tax_applied, simple.weee_tax_applied) as weee_tax_applied, 
                simple.weee_tax_disposition, 
                simple.weee_tax_row_disposition, 
                simple.base_weee_tax_disposition, 
                simple.base_weee_tax_row_disposition,
                v.value as related_id
                from sales_flat_order_item simple
                left join catalog_product_entity_varchar v on v.entity_id = simple.product_id and v.attribute_id = {$related_attribute_id} and v.entity_type_id = {$product_entity_type_id}
                left join sales_flat_order_item configurable on configurable.order_id = simple.order_id and configurable.product_type = 'configurable' and configurable.sku = simple.sku
                where simple.order_id = {$order_id} and simple.product_type = 'simple'";
	

        $items = $related_id = $cart->get_db()->rows($sql);
                        
        foreach ($items as $item) 
        {                  
            if($item['product_type'] == "simple")
            {                              
                $item_data[] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order_item'), $item);                                              
            }
	}
                
        return $item_data;
    }

    function rdi_order_lib_get_customer_id($order_data)
    {
        global $cart, $debug, $field_mapping;
              
        if($order_data['customer_is_guest'] == 1)
        {
//         //   return $order_data['increment_id'];
            
            return '';
        }
  
                
        $sql = "SELECT related_id AS related_id FROM customer_entity WHERE entity_id = {$order_data['customer_id']}";
	$related_id = $cart->get_db()->cell($sql, 'related_id');
        
//        if (empty($related_id))
//        {            
//            $custSID = $order_data['customer_id'];             
//	}
//	else 
//        {                                 
//            if ($related_id != '') {
//                $custSID = $related_id;
//            }
//            else {
//                $custSID = $order_data['customer_id'];
//            }
//        }     
        
        return $related_id;
    }
    
    function rdi_order_lib_map_shipping_method_and_provider($order)
    {
        global $debug, $field_mapping, $cart;
        
        $shipMethod = $order->getShippingMethod();
	$shipping = explode("_", $shipMethod);

	$sql = "SELECT
			id
			, IFNULL(rpro_provider_id, 1) AS rpro_provider_id
			, IFNULL(rpro_method_id, 1) AS rpro_method_id
		FROM rpro_mage_shipping
		WHERE
			shipper = '{$shipping[0]}'
			AND ship_code = '{$shipping[1]}'";

	$shippingInfo =  $cart->get_db()->row($sql);

	if (is_array($shippingInfo)) {
		$providerID = $shippingInfo['rpro_provider_id'];
		$methodID = $shippingInfo['rpro_method_id'];
	}
	else {
		$providerID = 1;
		$methodID = 1;
	}
        
        return array("provider_id" => $providerID, "method_id" => $methodID);
    }
    
    function rdi_order_lib_map_credit_card($order)
    {
        global $use_card_type_mapping, $debug, $field_mapping, $default_card_type, $cart;
        
        if($use_card_type_mapping)
        {
            //rdi_card_type_mapping
            //map the card type used to the value in the table            
            $sql = "Select pos_type from rdi_card_type_mapping where cart_type = '" . $order->getPayment()->getCc_type() . "'";
            
            $card_type = $cart->get_db()->cell($sql, 'pos_type');
            
            if($card_type == '')
                $card_type = $default_card_type;                        
        }
        
        return $card_type;
    }
    
    function rdi_order_lib_process_data($mapping, $data)
    {
        global $helper_funcs;
        
        $return_data = array();      
        
        foreach($mapping as $field)
        {            
            if(isset($return_data[$field['pos_field']]) && $return_data[$field['pos_field']] != '')
            {
                //?????
                //continue;
            }
            
            //this may be useful later, it will break the address into 2 fields 
            //select distinct SUBSTRING_INDEX(SUBSTRING_INDEX( `street` , '\n', 2 ),'\n', 1) as address, SUBSTRING_INDEX(SUBSTRING_INDEX( `street` , '\n', 2 ),'\n', -1) address2 from sales_flat_order_address where entity_id = 3

            //since magento does this rather strange have to split up the address into its 2 fields here manually
            
            if($field['cart_field'] == "street" || $field['cart_field'] == 'street2')
            {                               
                if(array_key_exists('street', $data) && $field['pos_field'] != '')
                {                            
                    $street_data = explode("\n", $data['street']);                                    

                    if($field['cart_field'] == "street")
                    {
                        if($field['special_handling'] != '' && $field['special_handling'] != null)
                            $v = $helper_funcs->process_special_handling($field['special_handling'], $street_data[0], $data);
                        else if($field['alternative_field'] != '' && $field['alternative_field'] != null)
                            $v = $helper_funcs->process_special_handling($field['alternative_field'], $street_data[0], $data);
                        else                        
                            $v = $street_data[0];

                        if(isset($return_data[$field['pos_field']]))
                            $return_data[$field['pos_field']] .= $v;
                        else
                            $return_data[$field['pos_field']] = $v; 
                        
                        
                        $return_data[$field['pos_field']] = $v;
                    }
                    else
                    {
                        if($field['special_handling'] != '' && $field['special_handling'] != null)
                            $v = $helper_funcs->process_special_handling($field['special_handling'], $street_data[1], $data);
                        else if($field['alternative_field'] != '' && $field['alternative_field'] != null)
                            $v = $helper_funcs->process_special_handling($field['alternative_field'], $street_data[1], $data);
                        else                        
                            $v = $street_data[1];

                        if(isset($return_data[$field['pos_field']]))
                            $return_data[$field['pos_field']] .= $v;
                        else
                            $return_data[$field['pos_field']] = $v;
                        
                        $return_data[$field['pos_field']] = $v;
                    }                                        
                }
                else if(!array_key_exists('street', $data) && $field['pos_field'] != '')
                {
                    //use the default
                    $return_data[$field['pos_field']] = $field['default_value'];
                }
            }
            else
            {
                //see if this mapping field exists
                if(array_key_exists($field['cart_field'], $data) && $field['pos_field'] != '')
                {       
                    //check on the cart level special handling
                    if($field['special_handling'] != '' && $field['special_handling'] != null)
                        $v = $helper_funcs->process_special_handling($field['special_handling'], $data[$field['cart_field']], $data);
                        
                        //dont do it this way as it may compound things
                        //$data[$field['cart_field']] = $helper_funcs->process_special_handling($field['special_handling'], $data[$field['cart_field']]);
                    
                    //check on the pos level special handling
                    else if($field['alternative_field'] != '' && $field['alternative_field'] != null)
                        $v = $helper_funcs->process_special_handling($field['alternative_field'], $data[$field['cart_field']], $data);
                    else                        
                        $v = $data[$field['cart_field']];
                                      
                    if(isset($return_data[$field['pos_field']]))
                        $return_data[$field['pos_field']] .= $v;
                    else
                        $return_data[$field['pos_field']] = $v;
                }
                else if(!array_key_exists($field['cart_field'], $data) && $field['pos_field'] != '')
                {
                    //use the default
                    $return_data[$field['pos_field']] = $field['default_value'];
                }
            }
        }  
        return $return_data;
    }
    
    /**
     * This function takes the order id number and creates an invoice and then captures the funds for that order.
     *
     * @param int $orderId  this is the orderid to get the order information for the creation of the invoice
     *  This int must be currently in the format of 10000010 AKA the full order number
     */
    function rdi_order_lib_invoice_order($order_data)
    {     
        global $debug, $cart;                 
                
        if ($order_data['receipt_shipping'] == null || $order_data['receipt_shipping'] == '') {
            $order_data['receipt_shipping'] = 0;
        }

        if ($order_data['receipt_tax'] == null) {
            $order_data['receipt_tax'] = 0;
        }
        
        $subtotal = $order_data['rdi_cc_amount'] - $order_data['receipt_shipping'] - $order_data['receipt_tax'];
        $subtotal_incl_tax = $subtotal + $order_data['receipt_tax'];
        $shipping_incl_tax = $order_data['receipt_shipping'] + $order_data['receipt_tax'];

        // This gets the getModel for a order
        $_order = Mage::getModel('sales/order');

        // There are two options to get the order information load which takes the actual order number AKA 6 or 7.
        // While loadByIncrementId takes the order number AKA 1000010 or 1000009.
        $order = $_order->loadByIncrementId($order_data['increment_id']);
        $orderID = $order->getId();
        $orderTotal = $order->getBaseTotalDue();
        $captureCheck = true;
               
        if ($order->canInvoice()) {
                            try {
            // Create the invoice for the order id given
            $invoiceId = Mage::getModel('sales/order_invoice_api')
                    ->create($order->getIncrementId(), array());

            // Get the invoice so that it can be used to capture funds
            $invoice = Mage::getModel('sales/order_invoice')
                    ->loadByIncrementId($invoiceId);

            if ($invoice->canCapture()) {
                $invoice->capture()->save();

                $cart->get_db()->exec("UPDATE {$dbPrefix}sales_flat_invoice SET grand_total = {$order_data['rdi_cc_amount']} WHERE order_id = {$orderID}");
            }

          } catch (Mage_Core_Exception $e) {
//                                    echo "<pre>";
//                                    echo "Invoice : {$orderId}\n";
//                                    // print_r($e); 
//                                    echo "</pre>";
                            }

        }
    }
?>
