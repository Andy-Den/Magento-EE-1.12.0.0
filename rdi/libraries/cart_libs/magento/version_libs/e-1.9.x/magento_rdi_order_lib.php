<?php

    //will still use the mage for the order capture
    require_once("../app/Mage.php");				// External script - Load magento framework
    Mage::app();

    /*
     * process the order record, build out a record to pass off to the pos
     */
    function rdi_order_lib_process_order($order_id)
    {
        global $debug, $field_mapping, $cart;
         
        $order_record = array();
        
        $order_data = $cart->get_db()->row("select * from sales_flat_order where entity_id = {$order_id}");
        
        if(is_array($order_data))
        {
            $order_billto_data = $cart->get_db()->row("select * from sales_flat_order_address where entity_id = {$order_data['billing_address_id']}");
            $order_shipto_data = $cart->get_db()->row("select * from sales_flat_order_address where entity_id = {$order_data['shipping_address_id']}");
            $order_payment_data = $cart->get_db()->row("select * from sales_flat_order_payment where parent_id = {$order_data['entity_id']}");
            
            if($order_billto_data['customer_id'] == null || $order_billto_data['customer_id'] == '')
                $order_billto_data['customer_id'] = $order_data['customer_id'];
            
            if($order_shipto_data['customer_id'] == null || $order_shipto_data['customer_id'] == '')
                $order_shipto_data['customer_id'] = $order_data['customer_id'];
            
            if($order_billto_data['email'] == null || $order_billto_data['email'] == '')
                $order_billto_data['email'] = $order_data['customer_email'];
            
            if($order_shipto_data['email'] == null || $order_shipto_data['email'] == '')
                $order_shipto_data['email'] = $order_data['customer_email'];
                        
            //may have to use this to convert the time listed to the correct datetime
            //$saved_time = Mage::getModel('core/date')->timestamp($deal->getTime())            
            
            //set the related customer id
            //$order_record['base_data'][$field_mapping->map_field("order", "customer_related_id")] =  rdi_order_lib_get_customer_id($order_data);        
            $order_data['customer_related_id'] = rdi_order_lib_get_customer_id($order_data);        
            $order_record['base_data'] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order'), $order_data);                
            $order_record['bill_to_data'] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order_bill_to'), $order_billto_data);
            $order_record['ship_to_data'] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order_ship_to'), $order_shipto_data);
            $order_record['payment_data'] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order_payment'), $order_payment_data);
            
            //handle the card type mapping
            $order_record['base_data'][$field_mapping->map_field("order", "card_type")] = rdi_order_lib_map_credit_card($order_id);
            
            //Handle the shipping method mapping
            $shipping_ids = rdi_order_lib_map_shipping_method_and_provider($order_data['shipping_method']);  
            
            //add the last 4 digits for the credit card number
            if($cart_last4 = $field_mapping->map_field("order", "card_last4"))
            {
                $order_record['base_data'][$cart_last4] = rdi_order_lib_credit_card_last4($order_id);
            }
            
            
            $shipping_method_id_field = $field_mapping->map_field("order", "shipping_method_id");
            $shipping_provider_id_field = $field_mapping->map_field("order", "shipping_provider_id");
            
            if($shipping_method_id_field)
                $order_record['base_data'][$shipping_method_id_field] = $shipping_ids['method_id'];
            
            if($shipping_provider_id_field)
                $order_record['base_data'][$shipping_provider_id_field] = $shipping_ids['provider_id'];             

            //get the order items data
            $order_record['item_data'] = rdi_order_lib_process_items($order_id);                                    
        }
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
        
        //so there is the possibility of augmenting the fields that are mapped, but we have to map these fields into our query if they are used
        //data that is mapped declared in the mapping should be handled to pull it in 
        //such as using a custom attribute to populate a field on order download
        //this leads to use of the cart_common get_field_value
        //its used in the data process tho        
        
        //$itemnum_attribute_id = $cart->get_db()->cell("select attribute_id from eav_attribute where attribute_code = 'itemnum' and entity_type_id = {$product_entity_type_id}", "attribute_id");
        
        //get the item data with a query
        $sql = "SELECT simple.item_id, 
                simple.order_id, 
                simple.parent_item_id, 
                simple.quote_item_id,
                simple.store_id, 
                simple.created_at, 
                simple.updated_at, 
                simple.product_id, 
                simple.product_type,
                configurable.product_type parent_type,
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
                ifnull(configurable.base_weee_tax_applied_row_amnt, simple.base_weee_tax_applied_row_amnt) as base_weee_tax_applied_row_amnt,
                ifnull(configurable.weee_tax_applied_amount, simple.weee_tax_applied_amount) as weee_tax_applied_amount, 
                ifnull(configurable.weee_tax_applied_row_amount, simple.weee_tax_applied_row_amount) as weee_tax_applied_row_amount, 
                ifnull(configurable.weee_tax_applied, simple.weee_tax_applied) as weee_tax_applied, 
                simple.weee_tax_disposition, 
                simple.weee_tax_row_disposition, 
                simple.base_weee_tax_disposition, 
                simple.base_weee_tax_row_disposition,
                v.value as related_id,
                g.`message` as gift_message
                from sales_flat_order_item simple
                left join catalog_product_entity_varchar v on v.entity_id = simple.product_id and v.attribute_id = {$related_attribute_id} and v.entity_type_id = {$product_entity_type_id}                
                left join sales_flat_order_item configurable on configurable.order_id = simple.order_id and configurable.product_type IN('configurable','bundle') and configurable.item_id = simple.parent_item_id
                LEFT JOIN gift_message g ON g.`gift_message_id` = simple.`gift_message_id`
                where simple.order_id = {$order_id} and simple.product_type = 'simple'
				UNION
		
SELECT DISTINCT
  simple.item_id,
  simple.order_id,
  simple.parent_item_id,
  simple.quote_item_id,
  simple.store_id,
  simple.created_at,
  simple.updated_at,
  simple.product_id,
  simple.product_type,
  configurable.product_type parent_type,
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
  IFNULL(
    configurable.price,
    simple.price
  ) AS price,
  IFNULL(
    configurable.base_price,
    simple.base_price
  ) AS base_price,
  IFNULL(
    configurable.original_price,
    simple.original_price
  ) AS original_price,
  IFNULL(
    configurable.base_original_price,
    simple.base_original_price
  ) AS base_original_price,
  IFNULL(
    configurable.tax_percent,
    simple.tax_percent
  ) AS tax_percent,
  IFNULL(
    configurable.tax_amount,
    simple.tax_amount
  ) AS tax_amount,
  IFNULL(
    configurable.base_tax_amount,
    simple.base_tax_amount
  ) AS base_tax_amount,
  IFNULL(
    configurable.tax_invoiced,
    simple.tax_invoiced
  ) AS tax_invoiced,
  IFNULL(
    configurable.base_tax_invoiced,
    simple.base_tax_invoiced
  ) AS base_tax_invoiced,
  IFNULL(
    configurable.discount_percent,
    simple.discount_percent
  ) AS discount_percent,
  IFNULL(
    configurable.discount_amount,
    simple.discount_amount
  ) AS discount_amount,
  IFNULL(
    configurable.base_discount_amount,
    simple.base_discount_amount
  ) AS base_discount_amount,
  IFNULL(
    configurable.discount_invoiced,
    simple.discount_invoiced
  ) AS discount_invoiced,
  IFNULL(
    configurable.base_discount_invoiced,
    simple.base_discount_invoiced
  ) AS base_discount_invoiced,
  IFNULL(
    configurable.amount_refunded,
    simple.amount_refunded
  ) AS amount_refunded,
  IFNULL(
    configurable.base_amount_refunded,
    simple.base_amount_refunded
  ) AS base_amount_refunded,
  IFNULL(
    configurable.row_total,
    simple.row_total
  ) AS row_total,
  IFNULL(
    configurable.base_row_total,
    simple.base_row_total
  ) AS base_row_total,
  IFNULL(
    configurable.row_invoiced,
    simple.row_invoiced
  ) AS row_invoiced,
  IFNULL(
    configurable.base_row_invoiced,
    simple.base_row_invoiced
  ) AS base_row_invoiced,
  IFNULL(
    configurable.row_weight,
    simple.row_weight
  ) AS row_weight,
  IFNULL(
    configurable.base_tax_before_discount,
    simple.base_tax_before_discount
  ) AS base_tax_before_discount,
  IFNULL(
    configurable.tax_before_discount,
    simple.tax_before_discount
  ) AS tax_before_discount,
  simple.ext_order_item_id,
  simple.locked_do_invoice,
  simple.locked_do_ship,
  IFNULL(
    configurable.price_incl_tax,
    simple.price_incl_tax
  ) AS price_incl_tax,
  IFNULL(
    configurable.base_price_incl_tax,
    simple.base_price_incl_tax
  ) AS base_price_incl_tax,
  IFNULL(
    configurable.row_total_incl_tax,
    simple.row_total_incl_tax
  ) AS row_total_incl_tax,
  IFNULL(
    configurable.base_row_total_incl_tax,
    simple.base_row_total_incl_tax
  ) AS base_row_total_incl_tax,
  IFNULL(
    configurable.hidden_tax_amount,
    simple.hidden_tax_amount
  ) AS hidden_tax_amount,
  IFNULL(
    configurable.base_hidden_tax_amount,
    simple.base_hidden_tax_amount
  ) AS base_hidden_tax_amount,
  IFNULL(
    configurable.hidden_tax_invoiced,
    simple.hidden_tax_invoiced
  ) AS hidden_tax_invoiced,
  IFNULL(
    configurable.base_hidden_tax_invoiced,
    simple.base_hidden_tax_invoiced
  ) AS base_hidden_tax_invoiced,
  IFNULL(
    configurable.hidden_tax_refunded,
    simple.hidden_tax_refunded
  ) AS hidden_tax_refunded,
  IFNULL(
    configurable.base_hidden_tax_refunded,
    simple.base_hidden_tax_refunded
  ) AS base_hidden_tax_refunded,
  simple.is_nominal,
  simple.tax_canceled,
  simple.hidden_tax_canceled,
  IFNULL(
    configurable.tax_refunded,
    simple.tax_refunded
  ) AS tax_refunded,
  simple.gift_message_id,
  simple.gift_message_available,
  IFNULL(
    configurable.base_weee_tax_applied_amount,
    simple.base_weee_tax_applied_amount
  ) AS base_weee_tax_applied_amount,
  IFNULL(
    configurable.base_weee_tax_applied_row_amnt,
    simple.base_weee_tax_applied_row_amnt
  ) AS base_weee_tax_applied_row_amnt,
  IFNULL(
    configurable.weee_tax_applied_amount,
    simple.weee_tax_applied_amount
  ) AS weee_tax_applied_amount,
  IFNULL(
    configurable.weee_tax_applied_row_amount,
    simple.weee_tax_applied_row_amount
  ) AS weee_tax_applied_row_amount,
  IFNULL(
    configurable.weee_tax_applied,
    simple.weee_tax_applied
  ) AS weee_tax_applied,
  simple.weee_tax_disposition,
  simple.weee_tax_row_disposition,
  simple.base_weee_tax_disposition,
  simple.base_weee_tax_row_disposition,
  v.related_id AS related_id,
  g.`message` AS gift_message 
FROM
  sales_flat_order_item `simple`
  JOIN enterprise_giftcard_amount v 
    ON v.entity_id = simple.product_id 
    AND v.value = simple.base_price
  JOIN sales_flat_order_item configurable
    ON configurable.order_id = simple.order_id 
    AND configurable.item_id = simple.item_id
  LEFT JOIN gift_message g 
    ON g.`gift_message_id` = simple.`gift_message_id` 
WHERE simple.order_id =  {$order_id} AND simple.product_type IN ('giftcard') 
AND simple.product_type IN ('giftcard')";
	

        $items = $cart->get_db()->rows($sql);
        
        
        foreach ($items as $item) 
        {                  
            if($item['product_type'] == "simple")
            {                                                                           
                $item_data[] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order_item'), $item);                                              
            }
            //enterprise only
            if($item['parent_type'] == "giftcard")//PMB 01172013 must add the related_id field to the enterprise_giftcard_amount table for this to work.
            {   
                $item['related_id'] = $cart->get_db()->cell("SELECT gc.related_id FROM enterprise_giftcard_amount gc
                                                                JOIN sales_flat_order_item i
                                                                ON i.product_id = gc.entity_id 
                                                                AND gc.value = i.base_price
                                                                where i.order_id = {$order_id}",'related_id');
                
                $item_data[] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order_item'), $item);                                              
            }
	}
                       
                
        return $item_data;
    }

function rdi_order_lib_get_customer_id($order_data)
    {
        global $cart, $debug, $field_mapping;
              
        if($order_data['customer_is_guest'] == 1 || 
			$order_data['customer_id'] == 'NULL' || 
			$order_data['customer_id'] == ''	 ||
			$order_data['customer_id'] == 'null' )
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
    
    function rdi_order_lib_map_shipping_method_and_provider($shipMethod)
    {
        global $debug, $field_mapping, $cart;
                
	$shipping = explode("_", $shipMethod);

	$sql = "SELECT
			id
			, IFNULL(rpro_provider_id, 1) AS rpro_provider_id
			, IFNULL(rpro_method_id, 1) AS rpro_method_id
		FROM rpro_mage_shipping
		WHERE
			shipper = '{$shipping[0]}'
			AND ship_code = '{$shipMethod}'";

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
            
            //map the card type used to the value in the table            
            //rdi_card_type_mapping
            
            
            //cant use the cc_type anymore it seems its not being set            
            //$sql = "Select pos_type from rdi_card_type_mapping 
            //            inner join sales_flat_order_payment on sales_flat_order_payment.cc_type = rdi_card_type_mapping.cart_type
            //            where sales_flat_order_payment.parent_id = {$order}";
            
            //check the method of payment
            //ccsave is credit card
            //checkmo is check money order
            $sql = "Select method from sales_flat_order_payment where parent_id = {$order}";
            $method = $cart->get_db()->cell($sql, 'method');
            
            //echo "METHOD ---- {$method}";
            
            if($method == "ccsave" || $method == "authorizenet" || $method = "shift4payments")
            {            
                //do a double check, get the cc_type, if null, use pull the cc_type from the additional_information serialzied array            
                $sql = "Select cc_type from sales_flat_order_payment where parent_id = {$order}";
                $cc_type = $cart->get_db()->cell($sql, 'cc_type');

                if($cc_type == "")
                {
                    $sql = "Select additional_information from sales_flat_order_payment where parent_id = {$order}";
                    $additional_information = $cart->get_db()->cell($sql, 'additional_information');

                    $additional_info = unserialize($additional_information);
                        
                    $cc_type_array = $cart->get_db()->array_rfind('cc_type',$additional_info);
                    
                    if(is_array($cc_type_array))
                    {
                        return $cc_type_array[0];
                    }
                    
                    
                    /*foreach($additional_info['authorize_cards'] as $card)
                    {
                        $cc_type =  $card['cc_type'];
                        
                        //echo "";
                    }*/
                    
                    
                }
            }
            else
            {
                $cc_type = $method;
            }
            
            $sql = "Select pos_type from rdi_card_type_mapping where rdi_card_type_mapping.cart_type = '{$cc_type}'";
            $card_type = $cart->get_db()->cell($sql, 'pos_type');

            if($card_type == '')
                $card_type = $default_card_type;          
        }
        
        return $card_type;
    }
    
    function rdi_order_lib_credit_card_last4($order)
    {
        global $get_cc_last4, $debug, $field_mapping, $default_card_type, $cart;
        
        $cc_last4 = 'XXXX';
		
        if(isset($get_cc_last4) && ($get_cc_last4 !== '' || $get_cc_last4 != null ))
        {
            $sql = "Select additional_information from sales_flat_order_payment where parent_id = {$order}";
            $additional_information = $cart->get_db()->cell($sql, 'additional_information');

            $additional_info = unserialize($additional_information);
            $cc_last4_array = $cart->get_db()->array_rfind($get_cc_last4,$additional_info);


            if(is_array($cc_last4_array))
            {
                $cc_last4 = $cc_last4_array[0];
            }
        }
        return $cc_last4;
    }
    
    
    
    function rdi_order_lib_process_data($mapping, $data)
    {
        global $helper_funcs, $cart;
        
        $return_data = array();
             
        if($mapping)
        {        
            foreach($mapping as $field)
            {            
                if(!isset($data[$field['cart_field']]))
                {
                    //if we dont know what the value is, try and find it, its possibly some custom attribute                                
                    //

                    //have to check for what the entity_id may be here, since it can change, item_id, order_id etc
                    if(array_key_exists('entity_id', $data))                    
                        $d = $cart->get_processor("rdi_cart_common")->get_field_value("catalog_product", $field['cart_field'], $data['entity_id']);
                    else if(array_key_exists('product_id', $data))
                        $d = $cart->get_processor("rdi_cart_common")->get_field_value("catalog_product", $field['cart_field'], $data['product_id']);
                    else if(array_key_exists('order_id', $data))
                        $d = $cart->get_processor("rdi_cart_common")->get_field_value("catalog_product", $field['cart_field'], $data['order_id']);                
                    else if(array_key_exists('customer_id', $data))
                        $d = $cart->get_processor("rdi_cart_common")->get_field_value("catalog_product", $field['cart_field'], $data['customer_id']);

                    if($d != null)
                    {                        
                        if($field['special_handling'] != '' && $field['special_handling'] != null)
                        {
                            $d = $helper_funcs->process_special_handling($field['special_handling'], $d, $data, 'order');
                        }
                            //dont do it this way as it may compound things
                            //$data[$field['cart_field']] = $helper_funcs->process_special_handling($field['special_handling'], $data[$field['cart_field']]);

                        //check on the pos level special handling
                        if($field['alternative_field'] != '' && $field['alternative_field'] != null)
                        {
                            $d = $helper_funcs->process_special_handling($field['alternative_field'], $d, $data, 'order');
                        }
                        
                        if(isset($return_data[$field['pos_field']]))
                            $return_data[$field['pos_field']] .= $d;
                        else
                            $return_data[$field['pos_field']] = $d;

                        //move on to the next value
                        continue;
                    }
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
                            $v = $street_data[0];
                            
                            if($field['special_handling'] != '' && $field['special_handling'] != null)
                            {
                                $v = $helper_funcs->process_special_handling($field['special_handling'], $v, $data);                                                               
                            }
                            if($field['alternative_field'] != '' && $field['alternative_field'] != null)
                            {
                                $v = $helper_funcs->process_special_handling($field['alternative_field'], $v, $data);                                                             
                            }                                                       
                            
                            if(isset($return_data[$field['pos_field']]))
                                $return_data[$field['pos_field']] .= $v;
                            else
                                $return_data[$field['pos_field']] = $v; 


                            $return_data[$field['pos_field']] = $v;
                        }
                        else
                        {
                            $v = (isset($street_data[1]) ? $street_data[1] : '');
                            
                            if($field['special_handling'] != '' && $field['special_handling'] != null)
                            {
                                $v = $helper_funcs->process_special_handling($field['special_handling'], $v, $data);                                
                            }
                            
                            if($field['alternative_field'] != '' && $field['alternative_field'] != null)
                            {
                                $v = $helper_funcs->process_special_handling($field['alternative_field'], $v, $data);                                                             
                            }                                                        

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
                        $v = $data[$field['cart_field']];
                        
                        //check on the cart level special handling
                        if($field['special_handling'] != '' && $field['special_handling'] != null)
                        {
                            $v = $helper_funcs->process_special_handling($field['special_handling'], $v, $data, 'order');                       
                        }
                            //dont do it this way as it may compound things
                            //$data[$field['cart_field']] = $helper_funcs->process_special_handling($field['special_handling'], $data[$field['cart_field']]);

                        //check on the pos level special handling
                        if($field['alternative_field'] != '' && $field['alternative_field'] != null)
                        {
                            $v = $helper_funcs->process_special_handling($field['alternative_field'], $v, $data, 'order');                            
                        }                                               

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
        }
        return $return_data;
    }
    
   function rdi_process_shipment($increment_id, $shipment_data, $shipment_items)
   {
       global $ship_all, $cart;
      
//       if ($carrier == 'ups') {
//           $carrierTitle = 'United Parcel Service';
//       }
//
//       if ($carrier == 'usps') {
//           $carrierTitle = 'United States Postal Service';
//       }
//
//       if ($carrier == 'fedex') {
//           $carrierTitle = 'Federal Express';
//       }
  
       $order = Mage::getModel('sales/order')->loadByIncrementId($increment_id);
       //$order = Mage::getModel('sales/order')->load(848);;

       //This converts the order to "Completed".
       $convertor = Mage::getModel('sales/convert_order');
       $shipment = $convertor->toShipment($order);

       
       //i know this isnt that great, but couldnt get anything else to work
       foreach ($order->getAllItems() as $orderItem) 
       {              
           //echo "GOT ITEMS";
           
            if($ship_all == 0 && is_array($shipment_items))
            {
                foreach($shipment_items as $shipment_item)
                {           
                    $item_data = $orderItem->getData();

                    $product = Mage::getModel('catalog/product')->loadByAttribute('related_id', $shipment_item['related_id']);
                                       
                    if($item_data['product_id'] == $product->getId())
                    {                
                        if(array_key_exists('qty', $shipment_item) && $shipment_item['qty'] && $shipment_item['qty'] > 0)
                        {                
                            if ($orderItem->getIsVirtual()) {
                               continue;
                            }

                            $item = $convertor->itemToShipmentItem($orderItem);

                            $item->setQty($shipment_item['qty']);
                            //$item->setData('qty', $shipment_item['qty']);

                            $shipment->addItem($item);     
                        }   
                    }
                    
                }
           }
           else if($ship_all == 1)               
           {
                if ($orderItem->getIsVirtual()) {
                   continue;
                }

                $item = $convertor->itemToShipmentItem($orderItem);

                $item->setQty($orderItem->getQtyToShip());
                
                //$item->setQty(1);
                //$item->setData('qty', $shipment_item['qty']);

                $shipment->addItem($item);    
           }
       }
       

       $shipment->register();
       $shipment->addComment($shipment_data['comment'], ($shipment_data['comment'] != '' ? true : false));
       $shipment->setEmailSent(true);
       $shipment->getOrder()->setIsInProcess(true);

	   $arrTracking = array(
                'carrier_code' => isset($shipment_data['carrier_code']) && $shipment_data['carrier_code'] !== '' ? $shipment_data['carrier_code'] : $order->getShippingCarrier()->getCarrierCode(),
                'title' => isset($shipment_data['carrier_title']) && $shipment_data['carrier_title'] !== '' ? $shipment_data['carrier_title']  : $order->getShippingCarrier()->getConfigData('title'),
                'number' => ($shipment_data['tracking_number'] == null ? '' : $shipment_data['tracking_number']),
            );
		
        $track = Mage::getModel('sales/order_shipment_track')->addData($arrTracking);
        $shipment->addTrack($track);
		$order->addStatusHistoryComment('Shipment Created for order # ' . $increment_id);
        $order->addStatusHistoryComment('Tracking #' . $shipment_data['tracking_number'] . ' Added for order # ' . $increment_id);
        
	   
	   /*
       $track = Mage::getModel('sales/order_shipment_track')
               ->setNumber(($shipment_data['tracking_number'] == null ? '' : $shipment_data['tracking_number']))
               ->setCarrierCode($shipment_data['carrier_code'])
               ->setTitle($shipment_data['carrier_title']);

       $shipment->addTrack($track);
*/
       try {
                    
           $transactionSave = Mage::getModel('core/resource_transaction')
                   ->addObject($shipment)
                   ->addObject($shipment->getOrder())
                   ->save();

           $shipment->save();
       } catch (Exception $e) {
           /*echo "ERROR 1-";
           echo "<pre>";
           print_r($e); //Prints out any exceptions that have been thrown up, hopefully none!
           echo "\n</pre>";*/
       }

       //if($shipment_data['email_sent'] != '0')
       //{
           //going to have to get the email off the order?
           //who are we emailing??
           
            // Send the shipping conformation email
            if($shipment->sendEmail())
            {
               $shipment->setEmailSent(TRUE); 
			   $order->addStatusHistoryComment('Customer Notified for order #  ' . $increment_id);
            }
       //}
       
       // Update the status on the order record
        $sql = "UPDATE sales_flat_order
                SET
                    rdi_shipper_created = 1
                WHERE
                    increment_id = {$increment_id}";
        $cart->get_db()->exec($sql);
   }
    
    function rdi_order_lib_invoice_order($order_data)
   {
		global $debug, $cart;
       
        $debug->write("Can Invoicing order","rdi_order_lib_invoice_order","magento_rdi_order_lib",0, array("order_data" => $order_data));
        
        if ($order_data['receipt_shipping'] == null || $order_data['receipt_shipping'] == '') {
            $order_data['receipt_shipping'] = 0;
        }
               
        $subtotal = $order_data['rdi_cc_amount'] - $order_data['receipt_shipping'] - $order_data['receipt_tax'];
        $subtotal_incl_tax = $subtotal + $order_data['receipt_tax'];
        $shipping_incl_tax = $order_data['receipt_shipping'] + $order_data['receipt_tax'];
        
   
		$_order = Mage::getModel('sales/order');     

		$order = $_order->loadByIncrementId($order_data['increment_id']);
                $orderID = $order->getId();
		
		$orderTotal = $order->getBaseTotalDue();
		
		if( $orderTotal == $order_data['rdi_cc_amount'])
		{
			
			try
			{
					// Check if order can be invoiced
                                        //echo $order->canInvoice();
					if (!$order->canInvoice())
					{
						// If cannot invoice order, check if there are any pending invoices.
                                                //echo $order->hasInvoices();
						if ($order->hasInvoices())
						{
								// Loop through the invoices.
								foreach($order->getInvoiceCollection() as $invoice)
								{
										// If invoice state is equal to open (Pending) then capture the invoice else throw an error.
                                                                    echo $invoice->getState();
										if ($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_OPEN)
										{
												captureInvoice($invoice);
										}
										else
										{
                                                                                        $error_message = 'Order # '.$order->getIncrementId().': The order does not allow creating an invoice';
                                                                                            Mage::log($error_message);
												//Mage::getSingleton('message/session')->addError($_order->__('Order # '.$order->getIncrementId().': The order does not allow creating an invoice'));
										}
								}
						}
					}
					else
					{
						//create invoice
						$invoiceId = Mage::getModel('sales/order_invoice_api')->create($order->getIncrementId(), array());
						//echo $invoiceId;
						//load invoice
						$invoice = Mage::getModel('sales/order_invoice')->loadByIncrementId($invoiceId);
						// Capture invoice
						//$invoice->register();
						captureInvoice($invoice);
					}
					
					
			}
			
			// Catch Magento errors.
			catch (Mage_Core_Exception $e)
			{
					//Mage::getSingleton('sales/order')->addError($e->getMessage());
                                        Mage::log(serialize($e));
			}
			// Catch other errors.
			/*catch (Exception $e)
			{
					Mage::getSingleton('sales/order')->addError(Mage::getModel('sales/order')->__('Order # '.$order->getIncrementId().': Unable to save the invoice.'));
                                        
					Mage::log(serialize($e));
			}*/
			
			$order->save();
			
		}
		else
		{
                    if($orderTotal != 0)
                    {
			Mage::getSingleton('message/session')->addError(Mage::getModel('sales/order')->__('Order # '.$order->getIncrementId().': Amount from Retail Pro of ' . $order_data['rdi_cc_amount'] . ' did not match Total Due ' . $orderTotal));
                    }
		}
		
		//check the sales_payment_transaction table to see if there is a record of the capture for this order                
		$sql = "Select txn_id from sales_payment_transaction where order_id = {$orderID} and txn_type = 'capture'";
		
		$txn_id = $cart->get_db()->cell($sql, "txn_id");

		if($txn_id)
		{                
			$debug->write("Order Funds captured","rdi_order_lib_invoice_order","magento_rdi_order_lib",0, array("orderid" => $orderID));

			$cart->get_db()->exec("UPDATE sales_flat_invoice SET grand_total = {$order_data['rdi_cc_amount']} WHERE order_id = {$orderID}");

			$sql = "UPDATE sales_flat_order
				SET                        
					rdi_upload_status = 2,
					total_paid = {$order_data['rdi_cc_amount']}
				WHERE
					increment_id = {$order_data['increment_id']}
					AND (rdi_upload_status = 1 OR rdi_upload_status = 2)";

			$cart->get_db()->exec($sql);
		}
		
		
        
   }
   
   
    function captureInvoice($invoice)
    {
                 // If no products add an error.
        if (!$invoice->getTotalQty())
        {
            Mage::getModel('sales/order')->_getSession()->addError(Mage::getModel('sales/order')->__('Order # '.$invoice->getOrder()->getIncrementId().': Cannot create an invoice without products.'));
        }
        else
        {
          // Set capture case to online and register the invoice.
          $invoice->setRequestedCaptureCase('online');
 
  	  // Try and send the customer notification email.
	  try
	  {
      	        $invoice->sendEmail(true);
    	        $invoice->setEmailSent(true);
   		$invoice->getOrder()->setCustomerNoteNotify(true); 
	  }
	  // Catch exceptions.
	  catch (Exception $e)
	  {
	  	Mage::logException($e);
	  	//Mage::getModel('sales/order')->addError('Order # '.$invoice->getOrder()->getIncrementId().': '. Mage::getModel('sales/order')->__('Unable to send the invoice email.'));
	  } 
          
                    // Capture invoice.
    	  $invoice->getOrder()->setIsInProcess(true);  
    	  $invoice->capture();
 
    	  // Go grab order from external resource and capture (etc. paypal, worldpay).
    	  $transactionSave = Mage::getModel('core/resource_transaction')
          	->addObject($invoice)
        	->addObject($invoice->getOrder());
    	  $transactionSave->save();
 
          $_order_increment_id = $invoice->getOrder()->getIncrementId();
          $order_increment_id =  $_order_increment_id[0];
    	  // Success message.
            Mage::log('Order # ' . $invoice->getOrder()->getIncrementId() . ': The invoice for order has been captured.');
    }	
}
    
    
    /**
    * This function creates the shipper form and places it into the cart
    *  
    * @param int $orderId  this is the orderid to get the order information for the creation of the invoice
    *  This int must be currently in the format of 10000010 AKA the full order number
    */
   function rdi_process_shipment_old($increment_id, $shipment_data, $shipment_items)
   {
       global $ship_all, $cart;
           
       $order = Mage::getModel('sales/order')->loadByIncrementId($increment_id);
       //$order = Mage::getModel('sales/order')->load(848);

       //This converts the order to "Completed".
       $convertor = Mage::getModel('sales/convert_order');
       $shipment = $convertor->toShipment($order);

       //i know this isnt that great, but couldnt get anything else to work
       foreach ($order->getAllItems() as $orderItem) 
       {              
           //echo "GOT ITEMS";
           
            if($ship_all == 0 && is_array($shipment_items))
            {
                foreach($shipment_items as $shipment_item)
                {           
                    $item_data = $orderItem->getData();

                    $product = Mage::getModel('catalog/product')->loadByAttribute('related_id', $shipment_item['related_id']);
                                       
                    if($item_data['product_id'] == $product->getId())
                    {                
                        if(array_key_exists('qty', $shipment_item) && $shipment_item['qty'] && $shipment_item['qty'] > 0)
                        {                
                            if ($orderItem->getIsVirtual()) {
                               continue;
                            }

                            $item = $convertor->itemToShipmentItem($orderItem);

                            $item->setQty($shipment_item['qty']);
                            //$item->setData('qty', $shipment_item['qty']);

                            $shipment->addItem($item);     
                        }   
                    }
                    
                }
           }
           else if($ship_all == 1)               
           {
                if ($orderItem->getIsVirtual()) {
                   continue;
                }

                $item = $convertor->itemToShipmentItem($orderItem);

                $item->setQty($orderItem->getQtyToShip());
                
                //$item->setQty(1);
                //$item->setData('qty', $shipment_item['qty']);

                $shipment->addItem($item);    
           }
       }
       

       
       $shipment->register();
       $shipment->addComment($shipment_data['comment'], ($shipment_data['comment'] != '' ? true : false));
       $shipment->setEmailSent($shipment_data['email_sent']);
       $shipment->getOrder()->setIsInProcess(true);
       
// if the carrier code does not come in on the reciepts and in_so we will look up the carrier code and title from the order.
	   //PMB 02042013
       $arrTracking = array(
                'carrier_code' => isset($shipment_data['carrier_code']) && $shipment_data['carrier_code'] !== '' ? $shipment_data['carrier_code'] : $order->getShippingCarrier()->getCarrierCode(),
                'title' => isset($shipment_data['carrier_title']) && $shipment_data['carrier_title'] !== '' ? $shipment_data['carrier_title']  : $order->getShippingCarrier()->getConfigData('title'),
                'number' => ($shipment_data['tracking_number'] == null ? '' : $shipment_data['tracking_number']),
            );
		
        $track = Mage::getModel('sales/order_shipment_track')->addData($arrTracking);
        $shipment->addTrack($track);

//commented out PMB 02042013
//       $track = Mage::getModel('sales/order_shipment_track')
//               ->setNumber(($shipment_data['tracking_number'] == null ? '' : $shipment_data['tracking_number']))
//               ->setCarrierCode($shipment_data['carrier_code'])
//               ->setTitle($shipment_data['carrier_title']);

 //      $shipment->addTrack($track);
       
                 
       

       try {
                    
           $transactionSave = Mage::getModel('core/resource_transaction')
                   ->addObject($shipment)
                   ->addObject($shipment->getOrder())
                   ->save();

           $shipment->save();
       } catch (Exception $e) {
//           echo "ERROR 1-";
//           echo "<pre>";
//           print_r($e); //Prints out any exceptions that have been thrown up, hopefully none!
//           echo "\n</pre>";
       }

        if($shipment->sendEmail())
        {
           $shipment->setEmailSent(TRUE); 
        }
       
       
       //}
       
       // Update the status on the order record
        $sql = "UPDATE sales_flat_order
                SET
                    rdi_shipper_created = 1
                WHERE
                    increment_id = {$increment_id}";
        $cart->get_db()->exec($sql);
   }
    
    /**
     * This function takes the order id number and creates an invoice and then captures the funds for that order.
     *
     * @param int $orderId  this is the orderid to get the order information for the creation of the invoice
     *  This int must be currently in the format of 10000010 AKA the full order number
     */
    function rdi_order_lib_invoice_order_old($order_data)
    {     
        global $debug, $cart;
       
        $debug->write("Can Invoicing order","rdi_order_lib_invoice_order","magento_rdi_order_lib",0, array("order_data" => $order_data));
        
        if ($order_data['receipt_shipping'] == null || $order_data['receipt_shipping'] == '') {
            $order_data['receipt_shipping'] = 0;
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
                               
            $debug->write("Can Invoicing order","rdi_order_lib_invoice_order","magento_rdi_order_lib",0, array("order_data" => $order_data));
            // Create the invoice for the order id given
            $invoiceId = Mage::getModel('sales/order_invoice_api')
                    ->create($order->getIncrementId(), array());
            
            // Get the invoice so that it can be used to capture funds
            $invoice = Mage::getModel('sales/order_invoice')
                    ->loadByIncrementId($invoiceId);

            if ($invoice->canCapture()) {
                $invoice->capture()->save();
                
//                $resp = $invoice->capture();                
//                $invoice->save();                
//                if($resp->checkResponseCode())
                
                //check the sales_payment_transaction table to see if there is a record of the capture for this order                
                $sql = "Select txn_id from sales_payment_transaction where order_id = {$orderID} and txn_type = 'capture'";
                
                $txn_id = $cart->get_db()->cell($sql, "txn_id");

                if($txn_id)
                {                
                    $debug->write("Order Funds captured","rdi_order_lib_invoice_order","magento_rdi_order_lib",0, array("orderid" => $orderID));

                    $cart->get_db()->exec("UPDATE {$dbPrefix}sales_flat_invoice SET grand_total = {$order_data['rdi_cc_amount']} WHERE order_id = {$orderID}");

                    $sql = "UPDATE sales_flat_order
                        SET                        
                            rdi_upload_status = 2,
                            total_paid = {$order_data['rdi_cc_amount']}
                        WHERE
                            increment_id = {$order_data['increment_id']}
                            AND (rdi_upload_status = 1 OR rdi_upload_status = 2)";

                    $cart->get_db()->exec($sql);
                }   
                                        
                
            }

          } catch (Mage_Core_Exception $e) 
            {
//                                    echo "<pre>";
//                                    echo "Invoice : {$orderId}\n";
//                                    // print_r($e); 
//                                    echo "</pre>";
              
              $debug->write("error invoicing","rdi_order_lib_invoice_order","magento_rdi_order_lib",0, array("e" => $e));
            }
            
            if($orderTotal == $order_data['rdi_cc_amount'])
            {
                $sql = "SELECT total_due FROM sales_flat_order WHERE entity_id = {$orderID}";

                $total_due = $cart->get_db()->cell($sql, "total_due");

                if($total_due <= 0)
                {
                    $sql = "UPDATE sales_flat_order
                            SET                        
                                rdi_upload_status = 3,
                                total_paid = {$order_data['rdi_cc_amount']}
                             WHERE
                                increment_id = {$order_data['increment_id']}
                                AND (rdi_upload_status = 1 OR rdi_upload_status = 2)";

                         $cart->get_db()->exec($sql);
                }


            }  

        }
    }
?>
