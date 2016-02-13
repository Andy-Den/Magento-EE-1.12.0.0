<?php

//will still use the mage for the order capture
require_once("../app/Mage.php");    // External script - Load magento framework
Mage::app();

/*
 * process the order record, build out a record to pass off to the pos
 */

function rdi_order_lib_process_order($order_id)
{
    global $debug, $field_mapping, $cart, $helper_funcs, $magento_time_zone, $default_tax_area_name, $export_date, $export_time_function;
	
	if(!isset($export_date) && strlen($export_date) == 0)
	{
		$export_func = "NOW()";
		if(isset($export_time_function) && strlen($export_time_function) > 0)
		{
			$export_func = $export_time_function;
		}
		$export_date = $cart->get_db()->cell("SELECT {$export_func} c","c");
	}

    $order_record = array();

    $order_data = $cart->get_db()->row("select * from {$cart->get_db()->get_db_prefix()}sales_flat_order where entity_id = {$order_id}");

    if (!isset($order_data['shipping_address_id']) || $order_data['shipping_address_id'] == null || $order_data['shipping_address_id'] == '')
    {
        $order_data['shipping_address_id'] = $order_data['billing_address_id'];
    }

    if (is_array($order_data))
    {
        $order_billto_data = $cart->get_db()->row("select * from {$cart->get_db()->get_db_prefix()}sales_flat_order_address where entity_id = {$order_data['billing_address_id']}");
        $order_shipto_data = $cart->get_db()->row("select * from {$cart->get_db()->get_db_prefix()}sales_flat_order_address where entity_id = {$order_data['shipping_address_id']}");
        $order_payment_data = $cart->get_db()->row("select * from {$cart->get_db()->get_db_prefix()}sales_flat_order_payment where parent_id = {$order_data['entity_id']}");

        if ($cart->get_db()->cell("SELECT field_mapping_id FROM rdi_field_mapping where field_type = 'order_bill_to' AND cart_field = 'tax_area2'"))
        {
            //handle straight two tax areas. This would work for CP and V9, but not v8
            $_tax_area = $cart->get_db()->cells("SELECT ifnull(pos_type, code) as 'tax_area' from {$cart->get_db()->get_db_prefix()}sales_order_tax sot
														LEFT join rdi_tax_area_mapping tam
														ON tam.cart_type = sot.code 
														WHERE sot.order_id = {$order_data['entity_id']}
														ORDER BY sot.priority ", 'tax_area');


            if (!empty($_tax_area))
            {
                foreach ($_tax_area as $key => $tax_area)
                {
                    $increment = $key === 0 ? "" : $key + 1;

                    $order_billto_data["tax_area{$increment}"] = $tax_area;

                    $get_second_tax_for_line_items = $key > 0;
                }
            }
        }
        else
        {
            $tax_area_name = $cart->get_db()->cell("SELECT ifnull(pos_type, code) as 'tax_area' from {$cart->get_db()->get_db_prefix()}sales_order_tax sot
                                                    LEFT join rdi_tax_area_mapping tam
                                                    ON tam.cart_type = sot.code 
                                                    WHERE sot.order_id = {$order_data['entity_id']}", 'tax_area');

            $get_second_tax_for_line_items = false;

            $order_billto_data['tax_area'] = (!isset($tax_area_name) || $tax_area_name == null || $tax_area_name == '' || !$tax_area_name) ? $default_tax_area_name : $tax_area_name;
        }


        if ($order_billto_data['customer_id'] == null || $order_billto_data['customer_id'] == '')
            $order_billto_data['customer_id'] = $order_data['customer_id'];

        if ($order_shipto_data['customer_id'] == null || $order_shipto_data['customer_id'] == '')
            $order_shipto_data['customer_id'] = $order_data['customer_id'];

        if ($order_billto_data['email'] == null || $order_billto_data['email'] == '')
            $order_billto_data['email'] = $order_data['customer_email'];

        if ($order_shipto_data['email'] == null || $order_shipto_data['email'] == '')
            $order_shipto_data['email'] = $order_data['customer_email'];

        //may have to use this to convert the time listed to the correct datetime
        //$saved_time = Mage::getModel('core/date')->timestamp($deal->getTime())
        $order_data['exported_at'] = $export_date;
        $order_data['created_at'] = $helper_funcs->changedatetime($order_data['created_at'], $magento_time_zone);

        //set the related customer id
        //$order_record['base_data'][$field_mapping->map_field("order", "customer_related_id")] =  rdi_order_lib_get_customer_id($order_data);        
        $order_data['customer_related_id'] = rdi_order_lib_get_customer_id($order_data);
        $order_record['base_data'] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order'), $order_data);
        $order_record['bill_to_data'] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order_bill_to'), $order_billto_data);
        $order_record['ship_to_data'] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order_ship_to'), $order_shipto_data);
        $order_record['payment_data'] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order_payment'), $order_payment_data);

        //handle the card type mapping
        $order_record['base_data'][$field_mapping->map_field("order", "card_type")] = rdi_order_lib_map_credit_card($order_id);

        //add the last 4 digits for the credit card number
        if ($cart_last4 = $field_mapping->map_field("order", "card_last4"))
        {
            $order_record['base_data'][$cart_last4] = rdi_order_lib_credit_card_last4($order_id);
        }


        //Handle the shipping method mapping
        $shipping_ids = rdi_order_lib_map_shipping_method_and_provider($order_data['shipping_method']);
        $shipping_method_id_field = $field_mapping->map_field("order", "shipping_method_id");
        $shipping_provider_id_field = $field_mapping->map_field("order", "shipping_provider_id");


        if ($shipping_method_id_field)
            $order_record['base_data'][$shipping_method_id_field] = $shipping_ids['method_id'];

        if ($shipping_provider_id_field)
            $order_record['base_data'][$shipping_provider_id_field] = $shipping_ids['provider_id'];

        //get the order items data
        $order_record['item_data'] = rdi_order_lib_process_items($order_id);
    }
    return $order_record;
}

function rdi_order_lib_process_items($order_id)
{
    global $debug, $field_mapping, $cart, $get_second_tax_for_line_items;

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

    $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from {$cart->get_db()->get_db_prefix()}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
    $related_attribute_id = $cart->get_db()->cell("select attribute_id from {$cart->get_db()->get_db_prefix()}eav_attribute where attribute_code = 'related_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");
    $itemnum_attribute_id = $cart->get_db()->cell("select attribute_id from {$cart->get_db()->get_db_prefix()}eav_attribute where attribute_code = 'related_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");

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
                ifnull(configurable.tax_amount, simple.tax_amount)/simple.qty_ordered as tax_amount, 
                ifnull(configurable.base_tax_amount, simple.base_tax_amount)/simple.qty_ordered as base_tax_amount, 
                ifnull(configurable.tax_invoiced, simple.tax_invoiced) as tax_invoiced, 
                ifnull(configurable.base_tax_invoiced, simple.base_tax_invoiced) as base_tax_invoiced,
                ifnull(configurable.discount_percent, simple.discount_percent) as discount_percent,
                ifnull(configurable.discount_amount, simple.discount_amount)/simple.qty_ordered as discount_amount, 
                ifnull(configurable.base_discount_amount, simple.base_discount_amount)/simple.qty_ordered as base_discount_amount, 
                ifnull(configurable.discount_invoiced, simple.discount_invoiced)/simple.qty_ordered as discount_invoiced,
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
                ifnull(v.value,simple.related_id) as related_id,
                g.`message` as gift_message
                from {$cart->get_db()->get_db_prefix()}sales_flat_order_item simple
                left join {$cart->get_db()->get_db_prefix()}catalog_product_entity_varchar v on v.entity_id = simple.product_id and v.attribute_id = {$related_attribute_id} and v.entity_type_id = {$product_entity_type_id}                
                left join {$cart->get_db()->get_db_prefix()}sales_flat_order_item configurable on configurable.order_id = simple.order_id and configurable.product_type IN('configurable','bundle','giftcard') and configurable.item_id = simple.parent_item_id
                LEFT JOIN {$cart->get_db()->get_db_prefix()}gift_message g ON g.`gift_message_id` = simple.`gift_message_id`
                where simple.order_id = {$order_id} and simple.product_type in('simple','grouped','downloadable','ugiftcert','virtual','giftcard')
				UNION
		SELECT simple.item_id, 
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
                ifnull(configurable.tax_amount, simple.tax_amount)/simple.qty_ordered as tax_amount, 
                ifnull(configurable.base_tax_amount, simple.base_tax_amount)/simple.qty_ordered as base_tax_amount, 
                ifnull(configurable.tax_invoiced, simple.tax_invoiced) as tax_invoiced, 
                ifnull(configurable.base_tax_invoiced, simple.base_tax_invoiced) as base_tax_invoiced,
                ifnull(configurable.discount_percent, simple.discount_percent) as discount_percent,
                ifnull(configurable.discount_amount, simple.discount_amount)/simple.qty_ordered as discount_amount, 
                ifnull(configurable.base_discount_amount, simple.base_discount_amount)/simple.qty_ordered as base_discount_amount, 
                ifnull(configurable.discount_invoiced, simple.discount_invoiced) as discount_invoiced,
                ifnull(configurable.base_discount_invoiced, simple.base_discount_invoiced)/simple.qty_ordered as base_discount_invoiced, 
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
                ifnull(v.value,simple.related_id) as related_id,
                g.`message` as gift_message
                from {$cart->get_db()->get_db_prefix()}sales_flat_order_item simple
                 join {$cart->get_db()->get_db_prefix()}catalog_product_entity_varchar v on v.entity_id = simple.product_id and v.attribute_id = {$related_attribute_id} and v.entity_type_id = {$product_entity_type_id}      
                 left join {$cart->get_db()->get_db_prefix()}catalog_product_entity_varchar itemnum on itemnum.entity_id = simple.product_id and itemnum.attribute_id = {$itemnum_attribute_id} and v.entity_type_id = {$product_entity_type_id}     
                 join {$cart->get_db()->get_db_prefix()}sales_flat_order_item configurable on configurable.order_id = simple.order_id and configurable.product_type IN('giftcard','ugiftcert') and configurable.item_id = simple.parent_item_id
                LEFT JOIN {$cart->get_db()->get_db_prefix()}gift_message g ON g.`gift_message_id` = simple.`gift_message_id`
                where simple.order_id = {$order_id}";


    $items = $cart->get_db()->rows($sql);

    if (!empty($items))
    {
        foreach ($items as $item)
        {
            if ($item['product_type'] == "simple" || $item['product_type'] == "downloadable" || $item['product_type'] == "virtual" || $item['product_type'] == "ugiftcert" || $item['product_type'] == "giftcard")
            {
                //need to do more processing before pushing into the array
                if (isset($get_second_tax_for_line_items) && $get_second_tax_for_line_items)
                {
                    //update get the 
                    $_taxes = $cart->get_db()->rows("SELECT i.* FROM {$cart->get_db()->get_db_prefix()}sales_order_tax_item i 
                                                                JOIN {$cart->get_db()->get_db_prefix()}sales_order_tax t
                                                                ON t.tax_id = i.tax_id
                                                                WHERE i.item_id = '{$item['parent_item_id']}' ORDER BY priority");

                    if (!empty($_taxes))
                    {
                        $tax_percent = $item["tax_percent"];

                        foreach ($_taxes as $key => $tax)
                        {
                            $increment = $key === 0 ? "" : $key + 1;

                            $item["tax_amount{$increment}"] = $item['base_tax_amount'] * $tax['tax_percent'] / $tax_percent;
                            $item["tax_percent{$increment}"] = $tax_percent * $tax['tax_percent'] / $tax_percent;

                            if (isset($billto_data["tax_area{$increment}"]))
                            {
                                $item["tax_area{$increment}"] = $billto_data["tax_area{$increment}"];
                            }
                        }
                    }
                }

                $product = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order_item'), $item);
                
                process_product_options($product);
                
                $item_data[] = $product;
            }
            //enterprise only
            if ($item['parent_type'] == "giftcard")//PMB 01172013 must add the related_id field to the enterprise_giftcard_amount table for this to work.
            {
                $item['related_id'] = $cart->get_db()->cell("SELECT gc.related_id FROM {$cart->get_db()->get_db_prefix()}enterprise_giftcard_amount gc
                                                                    JOIN {$cart->get_db()->get_db_prefix()}sales_flat_order_item i
                                                                    ON i.product_id = gc.entity_id 
                                                                    AND gc.value = i.base_price", 'related_id');

                $item_data[] = rdi_order_lib_process_data($field_mapping->get_field_list_expanded('order_item'), $item);
            }
        }
    }

    return $item_data;
}

function rdi_order_lib_get_customer_id($order_data)
{
    global $cart, $debug, $field_mapping, $db_lib;

    $email = $cart->get_db()->clean($order_data['customer_email']);
    
    if($db_lib->require_related_id_value())
    {
        $related_id_sql = " IFNULL(entity_id,related_id) as related_id ";
    }
    else
    {
        $related_id_sql = " related_id ";
    }
    
    if(strlen($order_data['customer_id']) > 0)
    {
        $sql = "SELECT {$related_id_sql} FROM {$cart->get_db()->get_db_prefix()}customer_entity WHERE entity_id = {$order_data['customer_id']}";
        $related_id = $cart->get_db()->cell($sql, 'related_id');
    }

    elseif(strlen($order_data['customer_email']) > 0)
    {
        $email = $cart->get_db()->clean($order_data['customer_email']);
        $sql = "SELECT  {$related_id_sql} FROM {$cart->get_db()->get_db_prefix()}customer_entity WHERE email = '{$email}'";
	$related_id = $cart->get_db()->cell($sql, 'related_id');

    }

    elseif(strlen($order_data['billing_address_id']) > 0)
    {
            //get customer email from billing address id
            $email = $cart->get_db()->cell("SELECT email from {$cart->get_db()->get_db_prefix()}sales_flat_order_address WHERE entity_id = '{$order_data['billing_address_id']}'");
            $email =  $cart->get_db()->clean($email);
            $sql = "SELECT {$related_id_sql} FROM {$cart->get_db()->get_db_prefix()}customer_entity WHERE email = '{$email}'";
            $related_id = $cart->get_db()->cell($sql, 'related_id');
    }
    
    //if we dont have a related_id at this point, add in the fake customer record using the billing_address.email
    if(strlen($related_id) == 0)
    {		
	$related_id = $cart->get_db()->cell("SELECT related_id FROM rdi_customer_email WHERE email = '{$cart->get_db()->clean($email)}'",'related_id');
		
        //$cart->get_db()->insertAr2("{$cart->get_db()->get_db_prefix()}customer_entity",array('email'=>$email,'related_id'=>$related_id),false);
		
    } 

    return $related_id;
}

function rdi_order_lib_map_shipping_method_and_provider($shipMethod)
{
    global $debug, $field_mapping, $cart, $default_shipping_method, $default_shipping_provider;

    $shipping = explode("_", $shipMethod);


    if (!isset($default_shipping_method))
    {
        $default_shipping_method = '1';
    }

    if (!isset($default_shipping_provider))
    {
        $default_shipping_provider = '1';
    }

    if ($shipping[0] == "matrixrate")
    {
        $sql = "SELECT id
			, IFNULL(rpro_provider_id, '{$default_shipping_provider}') AS rpro_provider_id
			, IFNULL(rpro_method_id, '{$default_shipping_method}') AS rpro_method_id
                    FROM {$cart->get_db()->get_db_prefix()}shipping_matrixrate sm
                    LEFT JOIN rpro_mage_shipping rms
                    ON  rms.ship_code = sm.delivery_type
                    WHERE sm.pk = '{$shipping[2]}'";
    }
    else
    {
        $sql = "SELECT
			id
			, IFNULL(rpro_provider_id, '{$default_shipping_provider}') AS rpro_provider_id
			, IFNULL(rpro_method_id,'{$default_shipping_method}') AS rpro_method_id
		FROM rpro_mage_shipping
		WHERE
			shipper = '{$shipping[0]}'
			AND ship_code = '{$shipMethod}'";
    }


    $shippingInfo = $cart->get_db()->row($sql);

    if (is_array($shippingInfo))
    {
        $providerID = $shippingInfo['rpro_provider_id'];
        $methodID = $shippingInfo['rpro_method_id'];
    }
    else
    {
        $providerID = isset($default_shipping_provider) && $default_shipping_provider !== '' ? $default_shipping_provider : 'Ground';
        $methodID = isset($default_shipping_method) && $default_shipping_method !== '' ? $default_shipping_method : 1;
    }

    return array("provider_id" => $providerID, "method_id" => $methodID);
}

function rdi_order_lib_map_credit_card($order)
{
    global $use_card_type_mapping, $debug, $field_mapping, $default_card_type, $cart;

    if ($use_card_type_mapping)
    {

        //map the card type used to the value in the table            
        //rdi_card_type_mapping
        //check the method of payment
        //ccsave is credit card
        //checkmo is check money order
        $sql = "Select method from {$cart->get_db()->get_db_prefix()}sales_flat_order_payment where parent_id = {$order}";
        $method = $cart->get_db()->cell($sql, 'method');

        if (strstr($method, 'paypal'))
        {
            $method = 'paypal';
        }


        if (in_array($method,array('ccsave','authorizenet','shift4payments','authnetcim','verisign','verisign_customerstored')))
        {
            //do a double check, get the cc_type, if null, use pull the cc_type from the additional_information serialzied array            
            $sql = "Select cc_type from {$cart->get_db()->get_db_prefix()}sales_flat_order_payment where parent_id = {$order}";
            $cc_type = $cart->get_db()->cell($sql, 'cc_type');

            if ($cc_type == "")
            {
                $sql = "Select additional_information from {$cart->get_db()->get_db_prefix()}sales_flat_order_payment where parent_id = {$order}";
                $additional_information = $cart->get_db()->cell($sql, 'additional_information');

                $additional_info = unserialize($additional_information);

                $cc_type_array = $cart->get_db()->array_rfind('cc_type', $additional_info);

                if (is_array($cc_type_array) && isset($cc_type_array[0]))
                {
                    $cc_type = $cc_type_array[0];
                }
                else
                {
                    $cc_type = 'VISA';
                }
            }
        }
        else
        {
            $cc_type = $method;
        }

        $sql = "Select pos_type from rdi_card_type_mapping where rdi_card_type_mapping.cart_type = '{$cc_type}'";
        $card_type = $cart->get_db()->cell($sql, 'pos_type');

        if ($card_type == '')
            $card_type = $default_card_type;
    }

    return $card_type;
}

/*
 * @author PMBLISS
 * @date 05172013
 * @setting get_cc_last4
 * @mapping add cart: cc_last4 -> POS: cc_last4(add to _out_so)
 * @help comma seperated list of the last4 possible last 4 cc fields to look for in the additional information, will default to XXXX if cannot be found.
 */

function rdi_order_lib_credit_card_last4($order)
{
    global $get_cc_last4, $debug, $field_mapping, $default_card_type, $cart;

    $cc_last4 = 'XXXX';

    if (isset($get_cc_last4) && ($get_cc_last4 !== '' || $get_cc_last4 != null ))
    {
        $sql = "Select additional_information from {$cart->get_db()->get_db_prefix()}sales_flat_order_payment where parent_id = {$order}";
        $additional_information = $cart->get_db()->cell($sql, 'additional_information');

        $additional_info = unserialize($additional_information);
        $cc_last4_array = $cart->get_db()->array_rfind($get_cc_last4, $additional_info);


        if (is_array($cc_last4_array))
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

    if ($mapping)
    {
        foreach ($mapping as $field)
        {
            if (!isset($data[$field['cart_field']]))
            {
                //if we dont know what the value is, try and find it, its possibly some custom attribute                                
                //

                    //have to check for what the entity_id may be here, since it can change, item_id, order_id etc
                if (array_key_exists('entity_id', $data))
                    $d = $cart->get_processor("rdi_cart_common")->get_field_value("catalog_product", $field['cart_field'], $data['entity_id']);
                else if (array_key_exists('product_id', $data))
                    $d = $cart->get_processor("rdi_cart_common")->get_field_value("catalog_product", $field['cart_field'], $data['product_id']);
                else if (array_key_exists('order_id', $data))
                    $d = $cart->get_processor("rdi_cart_common")->get_field_value("catalog_product", $field['cart_field'], $data['order_id']);
                else if (array_key_exists('customer_id', $data))
                    $d = $cart->get_processor("rdi_cart_common")->get_field_value("catalog_product", $field['cart_field'], $data['customer_id']);

                if ($d != null)
                {
                    if ($field['special_handling'] != '' && $field['special_handling'] != null)
                    {
                        $d = $helper_funcs->process_special_handling($field['special_handling'], $d, $data, 'order');
                    }
                    //dont do it this way as it may compound things
                    //$data[$field['cart_field']] = $helper_funcs->process_special_handling($field['special_handling'], $data[$field['cart_field']]);
                    //check on the pos level special handling
                    if ($field['alternative_field'] != '' && $field['alternative_field'] != null)
                    {
                        $d = $helper_funcs->process_special_handling($field['alternative_field'], $d, $data, 'order');
                    }

                    if (isset($return_data[$field['pos_field']]))
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

            if ($field['cart_field'] == "street" || $field['cart_field'] == 'street2')
            {
                if (array_key_exists('street', $data) && $field['pos_field'] != '')
                {
                    $street_data = explode("\n", $data['street']);

                    if ($field['cart_field'] == "street")
                    {
                        $v = $street_data[0];

                        if ($field['special_handling'] != '' && $field['special_handling'] != null)
                        {
                            $v = $helper_funcs->process_special_handling($field['special_handling'], $v, $data);
                        }
                        if ($field['alternative_field'] != '' && $field['alternative_field'] != null)
                        {
                            $v = $helper_funcs->process_special_handling($field['alternative_field'], $v, $data);
                        }

                        if (isset($return_data[$field['pos_field']]))
                            $return_data[$field['pos_field']] .= $v;
                        else
                            $return_data[$field['pos_field']] = $v;


                        $return_data[$field['pos_field']] = $v;
                    }
                    else
                    {
                        $v = (isset($street_data[1]) ? $street_data[1] : '');

                        if ($field['special_handling'] != '' && $field['special_handling'] != null)
                        {
                            $v = $helper_funcs->process_special_handling($field['special_handling'], $v, $data);
                        }

                        if ($field['alternative_field'] != '' && $field['alternative_field'] != null)
                        {
                            $v = $helper_funcs->process_special_handling($field['alternative_field'], $v, $data);
                        }

                        if (isset($return_data[$field['pos_field']]))
                            $return_data[$field['pos_field']] .= $v;
                        else
                            $return_data[$field['pos_field']] = $v;

                        $return_data[$field['pos_field']] = $v;
                    }
                }
                else if (!array_key_exists('street', $data) && $field['pos_field'] != '')
                {
                    //use the default
                    $return_data[$field['pos_field']] = $field['default_value'];
                }
            }
            else
            {
                //see if this mapping field exists
                if (array_key_exists($field['cart_field'], $data) && $field['pos_field'] != '')
                {
                    $v = $data[$field['cart_field']];

                    //check on the cart level special handling
                    if ($field['special_handling'] != '' && $field['special_handling'] != null)
                    {
                        $v = $helper_funcs->process_special_handling($field['special_handling'], $v, $data, 'order');
                    }
                    //dont do it this way as it may compound things
                    //$data[$field['cart_field']] = $helper_funcs->process_special_handling($field['special_handling'], $data[$field['cart_field']]);
                    //check on the pos level special handling
                    if ($field['alternative_field'] != '' && $field['alternative_field'] != null)
                    {
                        $v = $helper_funcs->process_special_handling($field['alternative_field'], $v, $data, 'order');
                    }

                    if (isset($return_data[$field['pos_field']]))
                        $return_data[$field['pos_field']] .= $v;
                    else
                        $return_data[$field['pos_field']] = $v;
                }
                //06302014 if orders break probably here.
                else if ((!array_key_exists($field['cart_field'], $data) || $data[$field['cart_field']] == null) && $field['pos_field'] != '')
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
    global $ship_all, $cart, $tracking_to_shipping_method, $helper_funcs, $mage_log_shipment;


    $order = Mage::getModel('sales/order')->loadByIncrementId($increment_id);
    //$order = Mage::getModel('sales/order')->load(848);;
    //This converts the order to "Completed".
    $convertor = Mage::getModel('sales/convert_order');
    $shipment = $convertor->toShipment($order);

    //if the shipment already exists for this, we are going to assume the item was added properly below.




    $cart->_echo('shipment_data');
    $cart->_print_r($shipment_data);
    $cart->_echo('shipment_items');
    $cart->_print_r($shipment_items);
    //i know this isnt that great, but couldnt get anything else to work

    foreach ($order->getAllItems() as $orderItem)
    {
        if ($ship_all == 0 && is_array($shipment_items) && $orderItem->canShip())
        {
            if (!$shipment->loadByIncrementId($shipment_data['document_number'])->isObjectNew())
            {
                $cart->_echo("We already shipped this item");

                return false;
            }

            foreach ($shipment_items as $shipment_item)
            {
                if ($orderItem->getData("product_type") == "configurable")
                {

                    foreach ($orderItem->getChildrenItems() as $child)
                    {
                        if ($shipment_item['related_id'] == $child->getProduct()->getData('related_id'))
                        {
                            $shipment_item['is_right_simple'] = true;
                        }
                    }
                }

                if ($shipment_item['related_id'] == $orderItem->getProduct()->getData('related_id') || (isset($shipment_item['is_right_simple']) && $shipment_item['is_right_simple']))
                {

                    if (array_key_exists('qty', $shipment_item) && $shipment_item['qty'] && $shipment_item['qty'] > 0)
                    {
                        if ($orderItem->getIsVirtual())
                        {
                            continue;
                        }

                        $item = $convertor->itemToShipmentItem(($orderItem->getData("parent_item_id") == NULL ? $orderItem : $orderItem->getParentItem()));

                        $item->setQty($shipment_item['qty']);
                        //$item->setData('qty', $shipment_item['qty']);

                        $shipment->addItem($item);
                    }
                }
            }

            $shipment->setData('increment_id', $shipment_data['document_number']);
        }
        else if ($ship_all == 1)
        {
            if ($orderItem->getIsVirtual())
            {
                continue;
            }

            $item = $convertor->itemToShipmentItem($orderItem);

            $item->setQty($orderItem->getQtyToShip());

            //$item->setQty(1);
            //$item->setData('qty', $shipment_item['qty']);

            $shipment->addItem($item);
        }
    }

    //$cart->_methods($shipment);exit;
    if (count($shipment->getAllItems) == 0)
    {
        if (!$order->canShip())
        {
            $sql = "UPDATE {$cart->prefix}sales_flat_order
					SET
						rdi_shipper_created = 1
					WHERE
						increment_id = '{$increment_id}'";

            $cart->get_db()->exec($sql);

            //check and see if we can go to complete.
            $cart->get_processor("rdi_cart_so_status_load")->mark_order($increment_id);

            return null;
        }
    }
    $shipment->register();
    $shipment->addComment($shipment_data['comment'], ($shipment_data['comment'] != '' ? true : false));
    //$shipment->setEmailSent(true);
    $shipment->getOrder()->setIsInProcess(true);


    if (isset($tracking_to_shipping_method) && $tracking_to_shipping_method == 1)
    {
        $shipData = $helper_funcs->tracking_to_method($shipment_data['tracking_number']);
        $arrTracking = array(
            'carrier_code' => $shipData['carrier_code'],
            'title' => $shipData['carrier_title'],
            'number' => $shipment_data['tracking_number']
        );
    }
    else
    {
        $arrTracking = array(
            'carrier_code' => isset($shipment_data['carrier_code']) && $shipment_data['carrier_code'] !== '' ? $shipment_data['carrier_code'] : $order->getShippingCarrier()->getCarrierCode(),
            'title' => isset($shipment_data['carrier_title']) && $shipment_data['carrier_title'] !== '' ? $shipment_data['carrier_title'] : $order->getShippingCarrier()->getConfigData('title'),
            'number' => ($shipment_data['tracking_number'] == null ? '' : $shipment_data['tracking_number']),
        );
    }



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
    try
    {

        $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($shipment->getOrder())
                ->save();

        $shipment->save();
    } catch (Exception $e)
    {
	$error_message = print_r($e->getMessage(),true);
        
        $order->addStatusHistoryComment('Error Occurred while saving Shipment for order #  ' . $increment_id . '. ' . $error_message);
        
        if (isset($mage_log_shipment) && $mage_log_shipment == 1)
        {
            $message = "Error saving shipment:" . __LINE__ . print_r($e->getMessage(), true);

            Mage::log($message, null, "rdi_shipment.log");
        }
    }

    //if($shipment_data['email_sent'] != '0')
    //{
    //going to have to get the email off the order?
    //who are we emailing??
    // Send the shipping conformation email
    if (!$shipment->getEmailSent() || $order()->canShip())
    {
        $shipment->sendEmail(true)
                ->setEmailSent(true)
                ->save();
        $order->addStatusHistoryComment('Customer Notified for order #  ' . $increment_id);
    }
    //}
    //if we can't ship any more. Mark the shipper created.
    if (!$order->canShip())
    {
        $sql = "UPDATE {$cart->prefix}sales_flat_order
                SET
                    rdi_shipper_created = 1
                WHERE
                    increment_id = '{$increment_id}'";

        $cart->get_db()->exec($sql);

        //check and see if we can go to complete.
        $cart->get_processor("rdi_cart_so_status_load")->mark_order($increment_id);
    }
}

function rdi_order_lib_invoice_order($order_data)
{
    global $debug, $cart, $mage_log_capture, $ignore_rdi_cc_amount, $capture_on_first_shipment, $allow_no_tracking_number;

    $debug->write("Can Invoicing order", "rdi_order_lib_invoice_order", "magento_rdi_order_lib", 0, array("order_data" => $order_data));

    if ($order_data['receipt_shipping'] == null || $order_data['receipt_shipping'] == '')
    {
        $order_data['receipt_shipping'] = 0;
    }


    $subtotal = $order_data['rdi_cc_amount'] - $order_data['receipt_shipping'] - $order_data['receipt_tax'];
    $subtotal_incl_tax = $subtotal + $order_data['receipt_tax'];
    $shipping_incl_tax = $order_data['receipt_shipping'] + $order_data['receipt_tax'];


    $_order = Mage::getModel('sales/order');

    $order = $_order->loadByIncrementId($order_data['increment_id']);
    $orderID = $order->getId();

    $captureThis = false;
    //@settings $$capture_on_first_shipment [0-OFF, 1-ON] If there is a shipment, we will capture. Else, we are requiring the full order to be shipped.
    //@settings $$allow_tracking_number [0-OFF, 1-Capture & Complete, 2-Capture Only] We dont care if there has been a shipment.
    if ((isset($capture_on_first_shipment) && $capture_on_first_shipment == 1 && $order->hasShipments()) || (isset($allow_no_tracking_number) && $allow_no_tracking_number > 0))
    {

        // do nothing
        $captureThis = true;
        $order_data['rdi_cc_amount'] = $order->getBaseTotalDue();
    }
    else
    {
        if ($order->canShip())
        {
            return false;
        }

        $captureThis = true;
    }


    $orderTotal = $order->getBaseTotalDue();
    //$orderTotal == $order_data['rdi_cc_amount']
    if (($captureThis || $orderTotal == $order_data['rdi_cc_amount']) || (isset($ignore_rdi_cc_amount) && $ignore_rdi_cc_amount == 1))
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
                    foreach ($order->getInvoiceCollection() as $invoice)
                    {
                        // If invoice state is equal to open (Pending) then capture the invoice else throw an error.
                        // echo $invoice->getState();
                        if ($invoice->getState() == Mage_Sales_Model_Order_Invoice::STATE_OPEN)
                        {
                            captureInvoice($invoice);
                        }
                        else
                        {
                            $error_message = 'Order # ' . $order->getIncrementId() . ': The order does not allow creating an invoice';
                            Mage::log($out_message, null, 'rdi_capture_log.log');
                            //   Mage::log($error_message);
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
            if (isset($mage_log_capture) && $mage_log_capture == 1)
            {
                //$out_message = "Caught Error {$orderID}: " . print_r($e->toString(), true);												
                $out_message = "Caught Error In Capture {$orderID}: " . print_r($e->getMessages(), true);
                Mage::log($out_message, null, 'rdi_capture_log.log');
                $out_message = "Message {$orderID}: " . print_r($e->getMessage(), true);
                Mage::log($out_message, null, 'rdi_capture_log.log');
                $out_message = "code {$orderID}: " . print_r($e->getCode(), true);
                Mage::log($out_message, null, 'rdi_capture_log.log');
                $out_message = "file {$orderID}: " . print_r($e->getFile(), true);
                Mage::log($out_message, null, 'rdi_capture_log.log');
                $out_message = "line {$orderID}: " . print_r($e->getLine(), true);
                Mage::log($out_message, null, 'rdi_capture_log.log');
            }

            $order->addStatusHistoryComment('Order # ' . $invoice->getOrder()->getIncrementId() . ': ' . print_r($e->getMessage(), true));
        }
        // Catch Magento errors.
        catch (Mage_Core_Exception $e)
        {
            if (isset($mage_log_capture) && $mage_log_capture == 1)
            {
                $out_message = "Caught Error {$orderID}: " . print_r($e->getMessage(), true);
                Mage::log($out_message, null, 'rdi_capture_log.log');
            }
        }

        $order->save();
    }
    else
    {

        if ($orderTotal != 0)
        {
            if (isset($mage_log_capture) && $mage_log_capture == 1)
            {
                //$out_message = "Caught Error {$orderID}: " . print_r($e->toString(), true);												
                $out_message = 'Order # ' . $order->getIncrementId() . ': Amount from Retail Pro of ' . $order_data['rdi_cc_amount'] . ' did not match Total Due ' . $orderTotal;
                Mage::log($out_message, null, 'rdi_capture_log.log');
            }
        }
    }

    //check the sales_payment_transaction table to see if there is a record of the capture for this order                
    $sql = "Select txn_id from {$cart->get_db()->get_db_prefix()}sales_payment_transaction where order_id = {$orderID} and txn_type = 'capture'";

    $txn_id = $cart->get_db()->cell($sql, "txn_id");

    //for cc saves and offline captures and checks
    // 2 is paid
    $sql = "select i.state from {$cart->get_db()->get_db_prefix()}sales_flat_invoice_grid i
				where i.state = 2 and i.order_id = {$orderID}";

    $invoice_state = $cart->get_db()->cell($sql, "state");

    if ($txn_id || (isset($invoice_state) && $invoice_state == '2'))
    {
        $debug->write("Order Funds captured", "rdi_order_lib_invoice_order", "magento_rdi_order_lib", 0, array("orderid" => $orderID));

        $cart->get_db()->exec("UPDATE {$cart->get_db()->get_db_prefix()}sales_flat_invoice SET grand_total = {$order_data['rdi_cc_amount']} WHERE order_id = {$orderID}");


        $sql = "UPDATE {$cart->get_db()->get_db_prefix()}sales_flat_order
				SET                        
					rdi_upload_status = 2,
					total_paid = {$order_data['rdi_cc_amount']}
				WHERE
					increment_id = '{$order_data['increment_id']}'
					AND (rdi_upload_status = 1 OR rdi_upload_status = 2)";

        $cart->get_db()->exec($sql);
    }
}

function captureInvoice($invoice)
{
    // If no products add an error.
    if (!$invoice->getTotalQty())
    {
        Mage::getModel('sales/order')->_getSession()->addError(Mage::getModel('sales/order')->__('Order # ' . $invoice->getOrder()->getIncrementId() . ': Cannot create an invoice without products.'));
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
            if (isset($mage_log_capture) && $mage_log_capture == 1)
            {
                //$out_message = "Caught Error {$orderID}: " . print_r($e->toString(), true);												
                $out_message = "Caught Error In Capture {$orderID}: " . print_r($e->getMessage(), true);
                Mage::log($out_message, null, 'rdi_capture_log.log');
            }
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
        $order_increment_id = $_order_increment_id[0];
        // Success message.
        if (isset($mage_log_capture) && $mage_log_capture == 1)
        {
            //$out_message = "Caught Error {$orderID}: " . print_r($e->toString(), true);												
            $out_message = Mage::log('Order # ' . $invoice->getOrder()->getIncrementId() . ': The invoice for order has been captured.');
            Mage::log($out_message, null, 'rdi_capture_log.log');
        }
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

        if ($ship_all == 0 && is_array($shipment_items))
        {
            foreach ($shipment_items as $shipment_item)
            {
                $item_data = $orderItem->getData();

                $product = Mage::getModel('catalog/product')->loadByAttribute('related_id', $shipment_item['related_id']);

                if ($item_data['product_id'] == $product->getId())
                {
                    if (array_key_exists('qty', $shipment_item) && $shipment_item['qty'] && $shipment_item['qty'] > 0)
                    {
                        if ($orderItem->getIsVirtual())
                        {
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
        else if ($ship_all == 1)
        {
            if ($orderItem->getIsVirtual())
            {
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
        'title' => isset($shipment_data['carrier_title']) && $shipment_data['carrier_title'] !== '' ? $shipment_data['carrier_title'] : $order->getShippingCarrier()->getConfigData('title'),
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




    try
    {

        $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($shipment)
                ->addObject($shipment->getOrder())
                ->save();

        $shipment->save();
    } catch (Exception $e)
    {
//           echo "ERROR 1-";
//           echo "<pre>";
//           print_r($e); //Prints out any exceptions that have been thrown up, hopefully none!
//           echo "\n</pre>";
    }

    if (!$shipment->getEmailSent() || $order()->canShip())
    {
        $shipment->sendEmail(true)
                ->setEmailSent(true)
                ->save();
        Mage::log('rdi_process_shipment_old: send shipment email for #' . $increment_id);
    }
    else
    {
        Mage::log('rdi_process_shipment_old: DO NOT send shipment email for #' . $increment_id);
    }


    //}
    // Update the status on the order record
    $sql = "UPDATE {$cart->get_db()->get_db_prefix()}sales_flat_order
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

    $debug->write("Can Invoicing order", "rdi_order_lib_invoice_order", "magento_rdi_order_lib", 0, array("order_data" => $order_data));

    if ($order_data['receipt_shipping'] == null || $order_data['receipt_shipping'] == '')
    {
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

    if ($order->canInvoice())
    {
        try
        {

            $debug->write("Can Invoicing order", "rdi_order_lib_invoice_order", "magento_rdi_order_lib", 0, array("order_data" => $order_data));
            // Create the invoice for the order id given
            $invoiceId = Mage::getModel('sales/order_invoice_api')
                    ->create($order->getIncrementId(), array());

            // Get the invoice so that it can be used to capture funds
            $invoice = Mage::getModel('sales/order_invoice')
                    ->loadByIncrementId($invoiceId);

            if ($invoice->canCapture())
            {
                $invoice->capture()->save();

//                $resp = $invoice->capture();                
//                $invoice->save();                
//                if($resp->checkResponseCode())
                //check the sales_payment_transaction table to see if there is a record of the capture for this order                
                $sql = "Select txn_id from {$cart->get_db()->get_db_prefix()}sales_payment_transaction where order_id = {$orderID} and txn_type = 'capture'";

                $txn_id = $cart->get_db()->cell($sql, "txn_id");

                if ($txn_id)
                {
                    $debug->write("Order Funds captured", "rdi_order_lib_invoice_order", "magento_rdi_order_lib", 0, array("orderid" => $orderID));

                    $cart->get_db()->exec("UPDATE {$cart->get_db()->get_db_prefix()}sales_flat_invoice SET grand_total = {$order_data['rdi_cc_amount']} WHERE order_id = {$orderID}");

                    $sql = "UPDATE {$cart->get_db()->get_db_prefix()}sales_flat_order
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

            $debug->write("error invoicing", "rdi_order_lib_invoice_order", "magento_rdi_order_lib", 0, array("e" => $e));
        }

        if ($orderTotal == $order_data['rdi_cc_amount'])
        {
            $sql = "SELECT total_due FROM {$cart->get_db()->get_db_prefix()}sales_flat_order WHERE entity_id = {$orderID}";

            $total_due = $cart->get_db()->cell($sql, "total_due");

            if ($total_due <= 0)
            {
                $sql = "UPDATE {$cart->get_db()->get_db_prefix()}sales_flat_order
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

/**
 * Processes the product_options field if it has the word "options" in it. This is the seriallizes array of custom options.
 * cart_field = product_options_[title of custom option]. I wish there was a key/code The title leaves opennings for change.
 * 
 * @global rdi_lib $cart
 * @param array $product Array of line item data with cart_fiels as keys.
 */
function process_product_options(&$product)
{
    global $cart;

    $prefix = $cart->get_db()->get_db_prefix();

    if (isset($product['product_options']) && strstr($product['product_options'], "options"))
    {
        $product_options = unserialize($product['product_options']);
        $product_options = $product_options['info_buyRequest']['options'];
    }
    elseif (isset($product['parent_product_options']) && strstr($product['parent_product_options'], "options"))
    {
        $product_options = unserialize($product['product_options']);
        $product_options = $product_options['info_buyRequest']['options'];
    }
    else
    {
        $product_options = null;
    }

    if(!empty($product_options))
    {

        foreach ($product_options as $option_id => $option_type_id)
        {
            if (!is_array($option_type_id) && is_numeric($option_type_id))
            {
                $a = get_product_option_value($option_type_id, $option_id);

                $product[$a['option_title']] = $a['title'];
            }
            else if (!is_array($option_type_id))
            {
                $a = $cart->get_db()->row("SELECT CONCAT('product_option_', o.title) AS option_title, '{$cart->get_db()->clean($option_type_id)}' as title  FROM {$prefix}catalog_product_option_title o
                                                                     WHERE o.option_id = {$option_id}");


                $product[$a['option_title']] = $a['title'];
            }
            else
            {
                foreach ($option_type_id as $oti)
                {
                    $a = get_product_option_value($oti, $option_id);

                    if (isset($product[$a['option_title']]))
                    {
                        $product[$a['option_title']] .= "|" . $a['title'];
                    }
                    else
                    {
                        $product[$a['option_title']] = $a['title'];
                    }
                }
            }
        }
    }
}

/**
 * Gets the new cart_field and the value from the available options in the cart.
 * 
 * If the product is gone, these will be gone.
 * 
 * @global rdi_lib $cart
 * @param int $option_type_id
 * @param int $option_id
 */
function get_product_option_value($option_type_id, $option_id)
{
    global $cart;

    $prefix = $cart->get_db()->get_db_prefix();

    return $cart->get_db()->row("SELECT CONCAT('product_option_', ot.title) AS option_title, IFNULL(ott.title,'{$option_type_id}') as title  FROM {$prefix}catalog_product_option o
								JOIN {$prefix}catalog_product_option_title ot
								ON ot.option_id = o.option_id
								JOIN {$prefix}catalog_product_option_type_value otv
								ON otv.option_id = o.option_id
								AND otv.option_type_id = {$option_type_id} 
								JOIN {$prefix}catalog_product_option_type_title ott
								ON ott.option_type_id = otv.option_type_id
								 WHERE o.option_id = {$option_id}");
}

?>