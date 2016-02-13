<?php

//preps, inserts or updates a category record
function insertUpdateProductRecord($product_class_def, $product_type, $product_data, $referenced_entities = array(), $website_ids = array(0))
{  
    global $hook_handler, $helper_funcs, $cart, $related_attribute_id, $product_entity_type_id, $cart, $attribute_ids, $exclusion_list, $special_handling_list, $debug;      
    
    $hook_handler->call_hook("cart_insertUpdateProductRecord", $product_class_def, $product_data);
    
    //check if the product is new
    $new_product = $product_data['entity_id'] != '' ? false : true;
    
    $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "product", 1, array("product_class_def" => $product_class_def, "product_type" => $product_type, "product_data" => $product_data));
    
    $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "product new?", 1, array("new_product" => $new_product, "product_data" => $product_data));
    
     //get the ids of the fields if they are not known
    if(!isset($product_entity_type_id) || $product_entity_type_id == '')
    {
        //get the catalog entity type id
        $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
        
        $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "product entity type id", 1, array("product_entity_type_id" => $product_entity_type_id));
    }
    
     //get the attribute if of the related id if need be
    if($related_attribute_id == '')
    {            
        $related_attribute_id = $this->db_connection->cell("select attribute_id from eav_attribute where attribute_code = 'related_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");

        $debug->write("magento_rdi_cart_product_load.php", "process_product_records", "get the related attribute id", 1, array("related_attribute_id" => $related_attribute_id));
    }
    
    //get a list of the fields not allowed to update
    if(!isset($exclusion_list) || $exclusion_list == '')
    {
        //
        $exclusion_list = $cart->get_db()->rows("select allow_update, cart_field from rdi_field_mapping where field_type = 'product'", "cart_field");
        $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "exclusion_list", 1, array("exclusion_list" => $exclusion_list));
    }
    
     //get a list of the fields that require a special handling
    if(!isset($special_handling_list) || $special_handling_list == '')
    {
        $special_handling_list = $cart->get_db()->rows("select special_handling, cart_field from rdi_field_mapping where field_type = 'product'", "cart_field");
        $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "special_handling_list", 1, array("special_handling_list" => $special_handling_list));
    }
        
    if(!isset($attribute_ids) || count($attribute_ids) == 0)       
        get_all_product_attribute_ids();
        
    if(count($website_ids) == 0)
    {
        $website_ids[] = 0;
    }
        
    //the break down of the attribute data and their values for this product
    $attribute_data_sets = array();
     
    //build out the value array
    foreach($attribute_ids as $attribute_code => $attribute_data)
    {
        $attribute_field = array();
        
        if($attribute_code == "related_id")
            continue;
        
        if(!$new_product && isset($exclusion_list[$attribute_code]) && $exclusion_list[$attribute_code]['allow_update'] == 0)
        {            
            continue;
        }
             
        //check if the product data has included this value
        if(isset($product_data[$attribute_code]) && $attribute_data['type'] != 'static')
        {            
            //check the front end display type, select types need to update the option tables and pass the id now the value in
            if($attribute_data['front_type'] == "select" && ($attribute_data['source_model'] == 'eav/entity_attribute_source_table' || $attribute_data['is_user_defined'] == 1))
            {                                
                $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "need to create or find an option value for this attribute", 0, array("attribute_data" => $attribute_data, "attribute_code" => $attribute_code));
                
                //create the attribute option if not exists
                //find the attribute option value for this attribute if it already exists,
                $option_id = get_attribute_option_value($attribute_code, $product_data[$attribute_code]);

                $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "the option id found", 1, array("option_id" => $option_id, "attribute_code" => $attribute_code));
                
                if(!$option_id || $option_id == '' || $option_id === '')
                {       
                    $position = 0;
                    if(isset($product_data[$attribute_code . "_sort_order"]))
                    {
                        $position = $product_data[$attribute_code . "_sort_order"];
                        
                        echo "<BR><BR><BR>{$position}<BR><BR><BR>";
                    }
                    
                    //if not we have to add it
                    $option_id = add_attribute_option_value($attribute_code, $product_data[$attribute_code], $position);
                    $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "the option id from the add", 1, array("option_id" => $option_id, "attribute_code" => $attribute_code));
                }
                               
                $product_data[$attribute_code] = $option_id;
            }
            
            //prevent 0 going in for a value when it should be nothing at all
            if($product_data[$attribute_code] == '' && $attribute_data['type'] == 'int')
                continue;
            
            if($product_data[$attribute_code] == '')
                    $product_data[$attribute_code] = 'null';
                        
            //check if there is special handling on this field
            if(isset($special_handling_list[$attribute_code]) && $special_handling_list[$attribute_code]['special_handling'] != '')
            {
                $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "record requires special handling", 1, array("special_handling_list" => $special_handling_list, "attribute_code" => $attribute_code));
                
                $product_data[$attribute_code] = $helper_funcs->process_special_handling($special_handling_list[$attribute_code]['special_handling'], $product_data[$attribute_code], $product_data, 'product', $product_class_def, $product_type);             
            }
                        
            //set the field we will send for processing            
            //add it to the broke down sets
            $attribute_data_sets[$attribute_data['type']][$attribute_data['attribute_id']] = $product_data[$attribute_code];                     
        }
    }
    
    //set the related id
    $attribute_data_sets['varchar'][$attribute_ids['related_id']['attribute_id']] = $product_data['related_id'];
      
    //check if this is an update
    if(!$new_product)
    {
        $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "before update", 2, array("entity id" => $product_data['entity_id'], "product_class_id" => $product_class_def['product_class_id'], "product_type" => $product_type, "sku" => $product_data['sku']));
        
        //update
        update_product_entity($product_data['entity_id'], $product_class_def['product_class_id'], $product_type, $product_data['sku']);
        
        //update the product stock entity
        update_product_stock_entity($product_data['entity_id'], $product_data);
    }
    else
    {
        $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "before insert", 2, array("product_class_id" => $product_class_def['product_class_id'], "product_type" => $product_type, "sku" => $product_data['sku']));

        //insert
        $product_data['entity_id'] = insert_product_entity($product_class_def['product_class_id'], $product_type, $product_data['sku']);
              
        //insert the product_stock_entity();
        insert_product_stock_entity($product_data['entity_id'], $product_data);     
    }
        
    foreach($attribute_data_sets as $type => $attribute_data_set)
    {        
        $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "process attribute data set", 2, array("attribute_data_set" => $attribute_data_set));
        
        if(!$new_product)
        {                
            //update the attribute fields
            update_product_attribute_field_table($product_data['entity_id'], $type, $website_ids[0], $attribute_data_set);          
        }
        else
        {
            //insert the attribute fields
            insert_product_attribute_field_table($product_data['entity_id'], $type, $website_ids[0], $attribute_data_set);                
        }
    }
       
    //echo "HERE!";
    
    if($product_type == "configurable")
    {
//        echo ">>>>>";
//        print_r($product_class_def);
//        echo "<<<<<";
        
        if(is_array($product_class_def['field_data']))
        {
            //tell the configurable that it is using these types of values for its options
            foreach($product_class_def['field_data'] as $attr)
            {                                   
                $sql = "REPLACE INTO `catalog_product_super_attribute` (`product_id`, `attribute_id`, `position`) 
                                    VALUES ({$product_data['entity_id']}, {$attribute_ids[$attr['cart_field']]['attribute_id']}, {$attr['position']})";
                $product_super_attribute_id = $cart->get_db()->insert($sql);
                $debug->show_query("cart_update_product", $sql);
                
                $sql = "REPLACE INTO `catalog_product_super_attribute_label` (`product_super_attribute_id`, `store_id`, `use_default`, `value`) 
                                    VALUES ({$product_super_attribute_id}, 0, 0, '{$attr['cart_field']}')";
                
                $cart->get_db()->insert($sql);    
                $debug->show_query("cart_update_product", $sql);                                    
                
                $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "mark configurable option", 3, array("entity_id" => $product_data['entity_id'],
                                                                                                           "attribute_id" => $attribute_ids[$attr['cart_field']]['attribute_id'],
                                                                                                           "product_super_attribute_id" => $product_super_attribute_id,
                                                                                                           "cart_field" => $attr['cart_field']));
            }
        }
        
        set_product_associations($referenced_entities, $product_data['entity_id']);
    } 
    else
    {         
        //check if the configurable exists for this item yet, if it doesnt then this is not an addition, should be a standard insert

        $sql = "SELECT catalog_product_entity.entity_id from catalog_product_entity inner join catalog_product_entity_varchar on catalog_product_entity_varchar.entity_id = catalog_product_entity.entity_id and type_id = 'configurable' where attribute_id = {$related_attribute_id} and value = '{$product_data['style_id']}'";

        $configurable_id = $cart->get_db()->cell($sql, "entity_id");      

        if(isset($configurable_id) && $configurable_id != '' && $configurable_id != null)
        {
            set_product_associations(array(0 => array("entity_id" => $product_data['entity_id'])), $configurable_id, false);
        }
    }
    
    $product_data['attribute_set_id'] = $product_class_def['product_class_id'];
    $product_data['type_id'] = $product_type;
    
    //create_indexed_record($product_data);
    //create_core_url_rewrite_record($product_data, $website_ids[0]);
    
    //assign the product to the default site
    $sql = "replace into catalog_product_website (product_id, website_id) values({$product_data['entity_id']}, 1)";
    $debug->show_query("cart_update_product", $sql); 
    $cart->get_db()->exec($sql);
    
    $hook_handler->call_hook("cart_insertUpdateProductRecord_post", $product_class_def, $product_record);
}

/*
 * perform an update on a single field, 
 * params
 * the product record
 * and the field that you wish to update
 */
function magento_update_field($product_data, $field_for_update, $product_class_def = array(), $product_type = array())
{        
    global $hook_handler, $helper_funcs, $cart, $product_entity_type_id, $cart, $attribute_ids, $exclusion_list, $special_handling_list, $debug;      
     
    //This is pretty standard boiler plate here get the data needed
    //-----------------------------
    
    //get the ids of the fields if they are not known
    if(!isset($product_entity_type_id) || $product_entity_type_id == '')
    {
        //get the catalog entity type id
        $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');

        $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "product entity type id", 1, array("product_entity_type_id" => $product_entity_type_id));
    }

    //get a list of the fields not allowed to update
    if(!isset($exclusion_list) || $exclusion_list == '')
    {
        //
        $exclusion_list = $cart->get_db()->rows("select allow_update, cart_field from rdi_field_mapping where field_type = 'product'", "cart_field");
        $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "exclusion_list", 1, array("exclusion_list" => $exclusion_list));
    }

     //get a list of the fields that require a special handling
    if(!isset($special_handling_list) || $special_handling_list == '')
    {
        $special_handling_list = $cart->get_db()->rows("select special_handling, cart_field from rdi_field_mapping where field_type = 'product'", "cart_field");
        $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "special_handling_list", 1, array("special_handling_list" => $special_handling_list));
    }

    if(!isset($attribute_ids) || count($attribute_ids) == 0)       
        get_all_product_attribute_ids();

    $website_ids[] = 0;
    
    //-----------------------
    
    //$attribute_ids contains more info about this field, see if its in there
    
    //make sure the product has this field
    if(isset($product_data[$field_for_update]) )
    {        
        if(isset($attribute_ids[$field_for_update]))
        {                
            $attribute_data = $attribute_ids[$field_for_update];
            
             //check the front end display type, select types need to update the option tables and pass the id now the value in
            if($attribute_data['front_type'] == "select" && ($attribute_data['source_model'] == 'eav/entity_attribute_source_table' || $attribute_data['is_user_defined'] == 1))
            {                                
                //create the attribute option if not exists
                //find the attribute option value for this attribute if it already exists,
                $option_id = get_attribute_option_value($field_for_update, $product_data[$field_for_update]);

                if(!$option_id || $option_id == '' || $option_id === '')
                {       
                    $position = 0;
                    if(isset($product_data[$field_for_update . "_sort_order"]))
                    {
                        $position = $product_data[$field_for_update . "_sort_order"];

                        //echo "<BR><BR><BR>{$position}<BR><BR><BR>";
                    }

                    //if not we have to add it
                    $option_id = add_attribute_option_value($field_for_update, $product_data[$field_for_update], $position);                    
                }

                $product_data[$field_for_update] = $option_id;
            }

            //prevent 0 going in for a value when it should be nothing at all
            if($product_data[$field_for_update] == '' && $attribute_data['type'] == 'int')
             return;

            if($product_data[$field_for_update] == '')
                $product_data[$field_for_update] = 'null';

            //check if there is special handling on this field
            if(isset($special_handling_list[$field_for_update]) && $special_handling_list[$field_for_update]['special_handling'] != '')
            {
                $debug->write("magento_rdi_product_lib.php", "updateProductRecord", "record requires special handling", 1, array("special_handling_list" => $special_handling_list, "field_for_update" => $field_for_update));

                $product_data[$field_for_update] = $helper_funcs->process_special_handling($special_handling_list[$field_for_update]['special_handling'], $product_data[$field_for_update], $product_data, 'product', $product_class_def, $product_type);             
            }

            //set the field we will send for processing            
            //add it to the broke down sets
            ///$attribute_data_sets[$attribute_data['type']][$attribute_data['attribute_id']] = $product_data[$attribute_code];  
            
            update_product_attribute_field_table($product_data['entity_id'], $attribute_data['type'], $website_ids[0], array($attribute_data['attribute_id'] => $product_data[$field_for_update]));
        }
        else if(in_array($field_for_update, array("qty", "use_config_min_qty", "is_qty_decimal", "use_config_backorders", "use_config_backorders", "use_config_min_sale_qty", "use_config_max_sale_qty",
                                                "is_in_stock", "low_stock_date", "use_config_notify_stock_qty", "use_config_manage_stock", "stock_status_changed_auto", "use_config_qty_increments",
                                                "use_config_enable_qty_inc")))            
        {            
            //stock update
            update_product_stock_entity_field($product_data, $field_for_update);
        }
    }
}

function process_out_of_stock()
{
    if(isset($hide_out_of_stock) && $hide_out_of_stock == 1)
    {
        //get the visibility attribute id
        
        $visibility_attribute_id = $cart->get_db()->cell("Select attribute_id from eav_attribute where attribute_code = 'visibility'", "attribute_id");
        
        //get the attribute set for the rpro simple, since these will need to be a part of this query
        $rpro_simple_attribute_set_id = $cart->get_db()->cell("select attribute_set_id from eav_attribute_set where attribute_set_name = 'RPro_simple'", "attribute_set_id");
        
        $sql = "UPDATE catalog_product_entity_int
                    JOIN cataloginventory_stock_item ON catalog_product_entity_int.entity_id = cataloginventory_stock_item.product_id
                    JOIN catalog_product_entity cpe ON cpe.entity_id = cataloginventory_stock_item.product_id SET
                    catalog_product_entity_int.value = 1
                    WHERE 
                    catalog_product_entity_int.attribute_id = {$visibility_attribute_id} AND
                    (cpe.type_id = 'configurable' OR cpe.attribute_set_id = {$rpro_simple_attribute_set_id}) AND
                    (qty < min_qty OR qty = 0)";

        $cart->get_db()->exec($sql);         
        
        $sql = "UPDATE catalog_product_entity_int
                    join cataloginventory_stock_item on catalog_product_entity_int.entity_id = cataloginventory_stock_item.product_id
                    JOIN catalog_product_entity cpe ON cpe.entity_id = cataloginventory_stock_item.product_id SET
                    catalog_product_entity_int.value = 4
                    WHERE 
                    catalog_product_entity_int.attribute_id = {$visibility_attribute_id} and
                    (cpe.type_id = 'configurable' or cpe.attribute_set_id = {$rpro_simple_attribute_set_id}) AND
                    (qty > min_qty)";
        
        $cart->get_db()->exec($sql); 
    }
}

//works but not needed, keeping for possible use later?
function create_core_url_rewrite_record($product_data, $store_id)
{    
//    global $cart;
//    
//    $sql = "Delete from core_url_rewrite where product_id = {$product_data['entity_id']}";
//    $cart->get_db()->exec($sql);  
//    
//    $sql = "INSERT INTO core_url_rewrite (store_id, id_path, request_path, target_path, is_system, product_id) 
//                                    values 
//                                         (  
//                                            {$store_id},
//                                            'product/{$product_data['entity_id']}',
//                                            '{$product_data['url_path']}',
//                                            'catalog/product/view/id/{$product_data['entity_id']}',
//                                            1,
//                                            {$product_data['entity_id']}
//                                         )";    
//    echo $sql;
//    $cart->get_db()->exec($sql);  
}

//works but not needed, keeping for possible use later?
function create_indexed_record($product_data)
{
    global $cart, $debug;
    
    //find the columns from the index table
    $columns = $cart->get_db()->rows("SHOW COLUMNS FROM catalog_product_flat_1");
    
    $fields = '';
    $values = '';
        
    foreach($columns as $column)
    {
        if(isset($product_data[$column['Field']]))
        {
            $fields .= $column['Field'] . ",";
            $values .= "'{$product_data[$column['Field']]}',";
        }                
    }
    
    $fields = substr($fields,0,-1);
    $values = substr($values,0,-1);

    $sql = "replace INTO catalog_product_flat_1 ({$fields}) values ({$values})";  
    $debug->show_query("cart_update_product", $sql); 
    $cart->get_db()->exec($sql);    
}

//sets the product associations used for configurable products
function set_product_associations($referenced_entities, $product_entity_id, $clear_associations = true)
{
    global $cart, $debug;
            
    if(is_array($referenced_entities))
    {
        $refs = '';
        foreach($referenced_entities as $entity)
        {
            if($entity['entity_id'] != '')
            {
                $refs .= "({$entity['entity_id']}, {$product_entity_id}),";
            }
        }

//        print_r($referenced_entities);
//        
//        exit;

        $refs = substr($refs,0,-1);

        if($refs != '')
        {
            if($clear_associations)
            {
                //clear out any existing relations
                $sql = "delete from `catalog_product_super_link` where `parent_id` = {$product_entity_id}";
                $debug->show_query("cart_update_product", $sql); 
                $cart->get_db()->insert($sql);            

                $sql = "delete from `catalog_product_relation` where `parent_id` = {$product_entity_id}";
                $debug->show_query("cart_update_product", $sql); 
                $cart->get_db()->insert($sql); 
            }
            
            //set the relations for the configurables
            $sql = "REPLACE INTO `catalog_product_super_link` (`product_id`,`parent_id`) VALUES {$refs}";
            $debug->show_query("cart_update_product", $sql); 
            $cart->get_db()->insert($sql);            

            $sql = "REPLACE INTO `catalog_product_relation` (`child_id`, `parent_id`) VALUES {$refs}";
            $debug->show_query("cart_update_product", $sql); 
            $cart->get_db()->insert($sql);                    
        } 
    }
}



function update_product_entity($product_entity_id, $attribute_set_id, $product_type, $sku)
{
    global $cart, $product_entity_type_id, $debug;
    
    $sku = "'" . $cart->get_db()->clean($sku) . "'";
    
    //insert the product entity record
    //type_id = {$product_type},  dont do type updates, this terribly breaks things
    //attribute_set_id = {$attribute_set_id} breaks things
//    $sql = "update `catalog_product_entity` set                                                
//                                                sku = {$sku}, 
//                                                updated_at = now()
//                                            where
//                                                entity_id = {$product_entity_id}";
//    $debug->show_query("cart_update_product", $sql);                                 
//    $cart->get_db()->exec($sql);
}

function insert_product_entity($attribute_set_id, $product_type, $sku)
{
    global $cart, $product_entity_type_id, $debug;
    
    $sku = "'" . $cart->get_db()->clean($sku) . "'"; 
    
    //insert the product entity record
    $sql = "INSERT INTO `catalog_product_entity` 
                                (`entity_type_id`, `attribute_set_id`, `type_id`, 
                                `sku`, `created_at`, `updated_at`) 
                                VALUES (
                                        {$product_entity_type_id}, 
                                        {$attribute_set_id}, 
                                        '{$product_type}', 
                                        {$sku},  
                                        now(),  
                                        now()
                                        );";
                                        
    $debug->show_query("cart_insert_product", $sql);                                                                   
    return $cart->get_db()->insert($sql);
}

function update_product_stock_entity($product_entity_id, $product_data)
{
    global $cart, $debug;
            
//    $sql = "update `cataloginventory_stock_item` set 
//                                                    `qty` = " . (!isset($product_data['qty']) ? '1' : $product_data['qty']) . ",
//                                                    `use_config_min_qty` = " . (!isset($product_data['use_config_min_qty']) ? '0' : $product_data['use_config_min_qty']) . ",
//                                                    `is_qty_decimal` = " . (!isset($product_data['is_qty_decimal']) ? '1' : $product_data['is_qty_decimal']) . ",                                                     
//                                                    `use_config_backorders` = " . (!isset($product_data['use_config_backorders']) ? '0' : $product_data['use_config_backorders']) . ",
//                                                    `use_config_min_sale_qty` = " . (!isset($product_data['use_config_min_sale_qty']) ? '1' : $product_data['use_config_min_sale_qty']) . ", 
//                                                    `use_config_max_sale_qty` = " . (!isset($product_data['use_config_max_sale_qty']) ? '1' : $product_data['use_config_max_sale_qty']) . ", 
//                                                    `is_in_stock` = " . (!isset($product_data['is_in_stock']) ? '1' : $product_data['is_in_stock']) . ",
//                                                    `low_stock_date` = " . ((!isset($product_data['low_stock_date']) || $product_data['low_stock_date'] == '') ? 'null' : "'" . $product_data['low_stock_date'] . "'") . ", 
//                                                    `use_config_notify_stock_qty` = " . (!isset($product_data['use_config_notify_stock_qty']) ? '1' : $product_data['use_config_notify_stock_qty']) . ", 
//                                                    `use_config_manage_stock` = " . (!isset($product_data['use_config_manage_stock']) ? '1' : $product_data['use_config_manage_stock']) . ",
//                                                    `stock_status_changed_auto` = " . (!isset($product_data['stock_status_changed_auto']) ? '1' : $product_data['stock_status_changed_auto']) . ", 
//                                                    `use_config_qty_increments` = " . (!isset($product_data['use_config_qty_increments']) ? '1' : $product_data['use_config_qty_increments']) . ", 
//                                                    `use_config_enable_qty_inc` = " . (!isset($product_data['use_config_enable_qty_inc']) ? '1' : $product_data['use_config_enable_qty_inc']) . "
//                                                 where
//                                                    `product_id` = {$product_entity_id}";                                                    
    $sql = "REPLACE INTO cataloginventory_stock_item (qty, 
                                                      min_qty,
                                                      use_config_min_qty, 
                                                      is_qty_decimal, 
                                                      use_config_backorders, 
                                                      use_config_min_sale_qty,
                                                      use_config_max_sale_qty,
                                                      is_in_stock,
                                                      low_stock_date,
                                                      use_config_notify_stock_qty,
                                                      use_config_manage_stock,
                                                      stock_status_changed_automatically,
                                                      use_config_qty_increments,
                                                      use_config_enable_qty_inc,
                                                      product_id,
                                                      stock_id) 
                                              values (
                                                      " . (!isset($product_data['qty']) ? '1' : $product_data['qty']) . ",
                                                      " . (!isset($product_data['min_qty']) ? '0' : $product_data['min_qty']) . ",
                                                      " . (!isset($product_data['use_config_min_qty']) ? '0' : $product_data['use_config_min_qty']) . ",
                                                      " . (!isset($product_data['is_qty_decimal']) ? '1' : $product_data['is_qty_decimal']) . ",                                                     
                                                      " . (!isset($product_data['use_config_backorders']) ? '0' : $product_data['use_config_backorders']) . ",
                                                      " . (!isset($product_data['use_config_min_sale_qty']) ? '1' : $product_data['use_config_min_sale_qty']) . ", 
                                                      " . (!isset($product_data['use_config_max_sale_qty']) ? '1' : $product_data['use_config_max_sale_qty']) . ", 
                                                      " . (!isset($product_data['is_in_stock']) ? '1' : $product_data['is_in_stock']) . ",
                                                      " . ((!isset($product_data['low_stock_date']) || $product_data['low_stock_date'] == '') ? 'null' : "'" . $product_data['low_stock_date'] . "'") . ", 
                                                      " . (!isset($product_data['use_config_notify_stock_qty']) ? '1' : $product_data['use_config_notify_stock_qty']) . ", 
                                                      " . (!isset($product_data['use_config_manage_stock']) ? '1' : $product_data['use_config_manage_stock']) . ",
                                                      " . (!isset($product_data['stock_status_changed_automatically']) ? '1' : $product_data['stock_status_changed_auto']) . ", 
                                                      " . (!isset($product_data['use_config_qty_increments']) ? '1' : $product_data['use_config_qty_increments']) . ", 
                                                      " . (!isset($product_data['use_config_enable_qty_inc']) ? '1' : $product_data['use_config_enable_qty_inc']) . ",
                                                      {$product_entity_id},
                                                      1
                                                     )";
    $debug->show_query("cart_update_product", $sql); 
    $cart->get_db()->exec($sql);
}

function update_product_stock_entity_field($product_data, $field)
{
    global $cart, $debug, $update_availability, $hide_out_of_stock;
    
    $sql = "update `cataloginventory_stock_item` set 
                                                    {$field} = '{$product_data[$field]}'                                                    
                                                 where
                                                    `product_id` = {$product_data['entity_id']}";                                                    
    $debug->show_query("cart_update_product", $sql); 
    $cart->get_db()->exec($sql);
    
    //handle the availability, make it optional setting
    if(isset($update_availability) && $update_availability == 1)
    {        
        $sql = "UPDATE cataloginventory_stock_item
                        JOIN catalog_product_entity cpe ON cpe.entity_id = cataloginventory_stock_item.product_id 
                        SET
                        stock_status_changed_auto = 0
                        , low_stock_date = NULL
                        , is_in_stock = 1
                        WHERE 
                        cpe.type_id = 'simple' AND
                        qty > min_qty
                        AND is_in_stock = 0";

        $cart->get_db()->exec($sql);  

        $sql = "UPDATE cataloginventory_stock_item
                JOIN catalog_product_entity cpe ON cpe.entity_id = cataloginventory_stock_item.product_id 
                SET 
                    is_in_stock = 0
                WHERE 
                    cpe.type_id = 'simple' AND
                    qty <= min_qty";

        $cart->get_db()->exec($sql);
    }        
}

function insert_product_stock_entity($product_entity_id, $product_data)
{
    global $cart, $debug;
    
    $sql = "INSERT INTO `cataloginventory_stock_item` (`product_id`, `stock_id`,
                                                `qty`, `min_qty`, `use_config_min_qty`, `is_qty_decimal`, `use_config_backorders`,
                                                `use_config_min_sale_qty`, `use_config_max_sale_qty`, `is_in_stock`,
                                                `low_stock_date`, `use_config_notify_stock_qty`, `use_config_manage_stock`,
                                                `stock_status_changed_auto`, `use_config_qty_increments`, `use_config_enable_qty_inc`) 
                                             VALUES 
                                             (
                                                {$product_entity_id},
                                                1,
                                                " . (!isset($product_data['qty']) ? '1' : $product_data['qty']) . ",
                                                " . (!isset($product_data['min_qty']) ? '0' : $product_data['min_qty']) . ",                                                    
                                                " . (!isset($product_data['use_config_min_qty']) ? '0' : $product_data['use_config_min_qty']) . ",
                                                " . (!isset($product_data['is_qty_decimal']) ? '1' : $product_data['is_qty_decimal']) . ",
                                                " . (!isset($product_data['use_config_backorders']) ? '0' : $product_data['use_config_backorders']) . ",
                                                " . (!isset($product_data['use_config_min_sale_qty']) ? '1' : $product_data['use_config_min_sale_qty']) . ",
                                                " . (!isset($product_data['use_config_max_sale_qty']) ? '1' : $product_data['use_config_max_sale_qty']) . ",
                                                " . (!isset($product_data['is_in_stock']) ? '1' : $product_data['is_in_stock']) . ",
                                                " . ((!isset($product_data['low_stock_date']) || $product_data['low_stock_date'] == '') ? 'null' : "'" . $product_data['low_stock_date'] . "'") . ",                                                                                                
                                                " . (!isset($product_data['use_config_notify_stock_qty']) ? '1' : $product_data['use_config_notify_stock_qty']) . ",
                                                " . (!isset($product_data['use_config_manage_stock']) ? '1' : $product_data['use_config_manage_stock']) . ",
                                                " . (!isset($product_data['stock_status_changed_auto']) ? '1' : $product_data['stock_status_changed_auto']) . ",
                                                " . (!isset($product_data['use_config_qty_increments']) ? '1' : $product_data['use_config_qty_increments']) . ",
                                                " . (!isset($product_data['use_config_enable_qty_inc']) ? '1' : $product_data['use_config_enable_qty_inc']) . "
                                              );";
                                                
    $debug->show_query("cart_insert_product", $sql);                                             
    $cart->get_db()->exec($sql);
}

//generate an update request and run it for the specified data
//attribute values will be code => value array
function update_product_attribute_field_table($product_entity_id, $field_type, $store_id, $attribute_values)
{
     global $cart, $product_entity_type_id, $debug;

     //there is never a static table so always need to skip that
     if($field_type != "static")
     {     
        $values = '';
        foreach($attribute_values as $attribute_id => $attribute_value)
        {                
            if($attribute_value != 'null' && $attribute_value != null)
                $attribute_value = "'" . $cart->get_db()->clean($attribute_value) . "'";  

            if($attribute_value == '')
                $attribute_value = 'null';

            $sql = "update `catalog_product_entity_{$field_type}` set value = {$attribute_value} where entity_type_id = {$product_entity_type_id}
                    and attribute_id = {$attribute_id} and store_id = {$store_id} and entity_id = {$product_entity_id}";

            $debug->show_query("cart_update_product", $sql); 
            $cart->get_db()->exec($sql);
        }            
     }
}

//generate an insert request and run it for the specified data
//attribute values will be code => value array
function insert_product_attribute_field_table($product_entity_id, $field_type, $store_id, $attribute_values)
{
     global $cart, $product_entity_type_id, $debug;
          
     if($field_type != "static")
     {
        $values = '';
        foreach($attribute_values as $attribute_id => $attribute_value)
        {                
            if($attribute_value != 'null' && $attribute_value != null)
                $attribute_value = "'" . $cart->get_db()->clean($attribute_value) . "'";  

            if($attribute_value == '')
                $attribute_value = 'null';

            $values .= "(
                        {$product_entity_type_id}, 
                        {$attribute_id}, 
                        {$store_id}, 
                        {$product_entity_id}, 
                        {$attribute_value}
                        ),";
        }

        $values = substr($values,0,-1);

        if($values != '')
        {     
            $sql = "INSERT INTO `catalog_product_entity_{$field_type}` (`entity_type_id`,`attribute_id`,
                                                    `store_id`,`entity_id`,`value`) 
                                                VALUES
                                                {$values}";
            $debug->show_query("cart_update_product", $sql); 
            $cart->get_db()->insert($sql);
        }
     }
}
            
function get_all_product_attribute_ids()
{
    global $attribute_ids, $cart, $product_entity_type_id, $debug;
        
    $attributes = $cart->get_db()->rows("select attribute_id,
                                                attribute_code,
                                                backend_type,
                                                frontend_input,
                                                source_model,
                                                is_user_defined
                                            from eav_attribute where entity_type_id = {$product_entity_type_id}");        
    
    $attribute_ids = array();
    
    foreach($attributes as $attribute)
    {
        $attribute_ids[$attribute['attribute_code']] = array("attribute_id" => $attribute['attribute_id'],
                                                             "type" => $attribute['backend_type'],
                                                             "front_type" => $attribute['frontend_input'],
                                                             "source_model" => $attribute['source_model'],
                                                             "is_user_defined" => $attribute['is_user_defined']);
    }
}

//gets the attribute id for the code specified, and stores it in the global variable, so next read wont have to hit the datbase
function get_product_attr_id($attr_code)
{
    $code = "product_attr_id" . $attr_code;
    global $$code, $product_entity_type_id, $cart;
    
    //get the ids of the fields if they are not known
    if(!isset($$code) || $$code == '')
    {        
        //get the catalog entity type id
        $$code = $cart->get_db()->cell("select attribute_id from eav_attribute where attribute_code = '{$attr_code}' and entity_type_id = {$product_entity_type_id}", 'attribute_id');        
    }
    
    return $$code;
}

function get_attribute_option_value($attribute_code, $attribute_value)
{    
    global $cart;        
     
    $attribute_value = "'" . $cart->get_db()->clean($attribute_value) . "'"; 
    
    $option_id = $cart->get_db()->cell("select eav_attribute_option.option_id from eav_attribute_option
                                    inner join eav_attribute_option_value on eav_attribute_option_value.option_id = eav_attribute_option.option_id
                                    inner join eav_attribute on eav_attribute.attribute_id = eav_attribute_option.attribute_id and eav_attribute.attribute_code = '{$attribute_code}'
                                    where eav_attribute_option_value.value = {$attribute_value} and eav_attribute_option_value.store_id = 0", "option_id");     
    if($option_id != '')
        return $option_id;
    
    return false;
}

function add_attribute_option_value($attribute_code, $attribute_value, $position)
{
    global $cart, $attribute_ids, $debug;
            
    if($position == '')
        $position = 0;
    
    $sql = "INSERT INTO `eav_attribute_option` (`attribute_id`, `sort_order`) VALUES ({$attribute_ids[$attribute_code]['attribute_id']}, {$position})";
    $debug->show_query("cart_insert_product", $sql); 
    $option_id = $cart->get_db()->insert($sql);

    $attribute_value = "'" . $cart->get_db()->clean($attribute_value) . "'";  
    
    $sql = "INSERT INTO `eav_attribute_option_value` (`option_id`, `store_id`, `value`) VALUES ({$option_id}, 0, {$attribute_value})";
    $debug->show_query("cart_insert_product", $sql); 
    $value_id = $cart->get_db()->insert($sql);
    
    if($option_id != '')
    {
        return $option_id;
    }
    
    return false;
}

function process_attribute_price_updates($product_class, $product_type)
{
    global $cart;
    //need to get a little data to prep for the query

    //need to the id of each of the attributes

    $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
    $price_id = $this->db_connection->cell("select attribute_id from eav_attribute where attribute_code = 'price' and entity_type_id = {$product_entity_type_id}", "attribute_id");

    $sql = "SELECT b.value, s.sku, catalog_product_super_attribute.product_super_attribute_id, eav_attribute_option_value.value_id, 0, (p.value - b.value) as variance_price, 0
                from catalog_product_entity s
                inner join catalog_product_entity_decimal p on p.entity_id = s.entity_id and p.attribute_id = {$price_id}
                inner join catalog_product_super_link on catalog_product_super_link.product_id = s.entity_id
                inner join catalog_product_entity_decimal b on b.entity_id = catalog_product_super_link.parent_id and b.attribute_id = {$price_id}
                ";
    
    if(count($product_class['field_data']) > 2)
    {
        //use this to determine which attribute set has more options, these will be the ones that are marked for the variance
        $attr_count = "select x.entity_id, x.attribute_id, count(x.attribute_id) from (
                        SELECT s.entity_id, eav_attribute_option.attribute_id, eav_attribute_option_value.value
                        from catalog_product_entity s
                        inner join catalog_product_super_link on catalog_product_super_link.parent_id = s.entity_id
                        inner join catalog_product_entity_int on catalog_product_entity_int.entity_id = catalog_product_super_link.product_id
                        inner join eav_attribute_option on eav_attribute_option.option_id = catalog_product_entity_int.value
                        inner join eav_attribute_option_value on eav_attribute_option_value.option_id = eav_attribute_option.option_id 
                        where s.type_id = 'configurable' and and s.attribute_set_id = {$product_class['product_class_id']}
                        group by eav_attribute_option_value.value, eav_attribute_option.attribute_id) as x
                        group by x.entity_id, x.attribute_id";
                        
         
    }
                
    //have to do a little logic here, if there is more than one attribute then just do the insert, otherwise have to figure out which is the one changing first
    if(count($product_class['field_data']) < 2)
    {
        $sql = "insert into catalog_product_super_attribute_pricing (product_super_attribute_id, value_index, is_percent, pricing_value, website_id) " . $sql;
    }
    
    
    //get the attribute variances for the each of the attribute codes, have to do a little logic 
    foreach($product_class['field_data'] as $field)
    {        
        $field_id = $this->db_connection->cell("select attribute_id from eav_attribute where attribute_code = '{$field['cart_field']}' and entity_type_id = {$product_entity_type_id}", "attribute_id");
        
        $sql .= "inner join catalog_product_super_attribute on catalog_product_super_attribute.product_id = catalog_product_super_link.parent_id and catalog_product_super_attribute.attribute_id = {$field_id}
                inner join catalog_product_entity_int on catalog_product_entity_int.entity_id = catalog_product_super_link.product_id and catalog_product_entity_int.attribute_id = {$field_id}
                inner join eav_attribute_option on eav_attribute_option.option_id = catalog_product_entity_int.value
                inner join eav_attribute_option_value on eav_attribute_option_value.option_id = eav_attribute_option.option_id 
                having variance_price != 0";
              
//        if(count($product_class['field_data']) < 2)
//        {
            $this->db_connection->exec($sql);
//        }
//        else
//        {
//            $this->db_connection->rows($sql);
//        }
    }

}

//function apply_attribute_price_variance($product_class_def)
//{
//    global $cart, $product_entity_type_id;
////have to call the query for each of the data fields used
////have to keep an exclusion list of values that have been set already
//
//    $exlusion_list = array();
//
//    if(is_array($product_class_def['field_data']))
//    {            
//        $attribute_data = array();
//        
//        //loop and build out the attribute data array
//        foreach($product_class_def['field_data'] as $attr)
//        {
//            $attribute_data[]['code'] = $attr['cart_field'];
//            
//            //get the attribute id for this code
//            if(!isset($product_entity_type_id))
//                $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
//                
//             $attribute_data[]['attribute_id'] = $cart->get_db()->cell("select attribute_id from eav_attribute where attribute_code = '{$attr['cart_field']}' and entity_type_id = {$product_entity_type_id}", "attribute_id");
//        }
//                     
//        //run the query based on the array data
//        foreach($attribute_data as $a)
//        {            
//            //$query = get_attribute_price_variance_query($attribute_data, $a['code']);
//            
//            //$print_r($query);
//            
//            $rows = $cart->get_db()->rows($query);
//            
//            //print_r($rows);
//        }
//    }  
//    
//    exit;
//}

//function get_attribute_price_variance_query($attribute_data, $key)
//{
//    global $cart, $product_entity_type_id, $price_attribute_id;
//    
//   // print_r($attribute_data);
//    
//    $joins = '';
//    foreach($attribute_data as $data)
//    {
//        //print_r($data);
//        
//        $joins .= "left join catalog_product_entity_int {$data['code']} on {$data['code']}.entity_id = catalog_product_super_link.product_id and {$data['code']}.attribute_id = {$data['attribute_id']}";
//    }
//    
//    
//    if(!isset($product_entity_type_id))
//        $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
//    
//    //get the price data attribute id
//    if(!isset($price_attribute_id))    
//        $price_attribute_id = $cart->get_db()->cell("select attribute_id from eav_attribute where attribute_code = 'price' and entity_type_id = {$product_entity_type_id}", "attribute_id");
//    
//    $query = <<< QUERY
//   
//   select pp.entity_id, c.value as 'color_attr', s.value as 'size_attr', pp.value as 'parent_price', p.value as 'price', (p.value - pp.value) as 'difference'
//from catalog_product_super_link
//inner join catalog_product_entity_int on catalog_product_entity_int.entity_id = catalog_product_super_link.product_id
//inner join catalog_product_entity_decimal p on p.entity_id = catalog_product_super_link.product_id and p.attribute_id = {$price_attribute_id}
//inner join catalog_product_entity_decimal pp on pp.entity_id = catalog_product_super_link.parent_id and pp.attribute_id = {$price_attribute_id}
//{$joins}
//left join catalog_product_super_attribute on catalog_product_super_attribute.product_id = pp.entity_id 
//where pp.value != p.value and pp.value != 0 and
//	 pp.entity_id in (
//                    select * from (select pp.entity_id
//                    from catalog_product_super_link
//                    inner join catalog_product_entity_int on catalog_product_entity_int.entity_id = catalog_product_super_link.product_id
//                    inner join catalog_product_entity_decimal p on p.entity_id = catalog_product_super_link.product_id and p.attribute_id = 69
//                    inner join catalog_product_entity_decimal pp on pp.entity_id = catalog_product_super_link.parent_id and pp.attribute_id = 69
//                    {$joins}
//                    left join catalog_product_super_attribute on catalog_product_super_attribute.product_id = pp.entity_id 
//                    where pp.value != p.value and pp.value != 0
//                    group by {$key}.value
//                    order by pp.entity_id) as x
//                    group by x.entity_id
//                    having count(*) > 1
//                )
//group by {$key}.value
//order by pp.entity_id 
//   
//QUERY;
//
//}

?>