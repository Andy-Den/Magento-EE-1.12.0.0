<?php

//preps, inserts or updates a category record
function insertUpdateProductRecord($product_class_def, $product_type, $product_data, $referenced_entities = array(), $website_ids = array(0))
{  
    global $hook_handler, $helper_funcs, $cart, $related_attribute_id, $product_entity_type_id, $cart, $attribute_ids, $exclusion_list, $special_handling_list, $debug, $store_id, $default_site;      
    
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
        $exclusion_list = $cart->get_db()->rows("select allow_update, cart_field from rdi_field_mapping where field_type = 'product' and (field_classification = '{$product_class_def['product_class']}' or field_classification is null) and (entity_type = '{$product_type['product_type']}' or entity_type is null)", "cart_field");
        $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "exclusion_list", 1, array("exclusion_list" => $exclusion_list));
    }
    
     //get a list of the fields that require a special handling
    if(!isset($special_handling_list) || $special_handling_list == '')
    {
        $special_handling_list = $cart->get_db()->rows("select special_handling, cart_field from rdi_field_mapping where field_type = 'product' and (field_classification = '{$product_class_def['product_class']}' or field_classification is null) and (entity_type = '{$product_type['product_type']}' or entity_type is null)", "cart_field");
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
                
                if($product_data[$attribute_code] != '')
                {
                
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

                            //echo "<BR><BR><BR>{$position}<BR><BR><BR>";
                        }

                        //if not we have to add it
                        $option_id = add_attribute_option_value($attribute_code, $product_data[$attribute_code], $position);
                        $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "the option id from the add", 1, array("option_id" => $option_id, "attribute_code" => $attribute_code));
                    }

                    $product_data[$attribute_code] = $option_id;
                }
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
        update_product_stock_entity($product_data['entity_id'], $product_data, $product_type);
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
    
    unset($attribute_data_sets);
       
    //echo "HERE!";
    
    if($product_type['product_type'] == "configurable")
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
                                    VALUES ({$product_super_attribute_id}, {$store_id}, 0, '{$attr['cart_field']}')";
                
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
    else if ($product_type['product_type'] == "grouped")
    {
        //doesnt use the super link so dont use this
        //set_product_associations($referenced_entities, $product_data['entity_id']);
        
        //get the link type ids        
        $super_id = $cart->get_db()->cell("Select link_type_id from catalog_product_link_type where code = 'super'", 'link_type_id');
        
        $pos_id = $cart->get_db()->cell("select product_link_attribute_id from catalog_product_link_attribute where product_link_attribute_code = 'position' and link_type_id = {$super_id}", 'product_link_attribute_id');
                        
        $pos = 0;
        //insert the data into the link table
        foreach($referenced_entities as $p)
        {                        
            //insert the new relation
            $link_id = $cart->get_db()->insert("insert ignore into catalog_product_link(product_id, linked_product_id, link_type_id) 
                    values({$product_data['entity_id']}, {$p['entity_id']}, {$super_id})");                    
                                        
            $cart->get_db()->insert("insert ignore into catalog_product_link_attribute_int (link_id, product_link_attribute_id, value) 
                values ({$link_id}, {$pos_id}, {$pos})");
                
            $pos++;
        }    
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
    
    unset($referenced_entities);
    
    $product_data['attribute_set_id'] = $product_class_def['product_class_id'];
    $product_data['type_id'] = $product_type['product_type'];
    
    //create_indexed_record($product_data);
    //enable to do custom url rewrite
    //create_core_url_rewrite_record($product_data, $website_ids[0]);
    
    if(isset($default_site) && $default_site != '')
    {    
        $sites_used = explode(',', $default_site);

        if(is_array($sites_used))
        {
            foreach($sites_used as $website_id)
            {    
                //assign the product to the default site
                $sql = "replace into catalog_product_website (product_id, website_id) values({$product_data['entity_id']}, {$website_id})";
                $debug->show_query("cart_update_product", $sql); 
                $cart->get_db()->exec($sql);    
            }
        }
    }
    else
    {
        //assign the product to the default site
        $sql = "replace into catalog_product_website (product_id, website_id) values({$product_data['entity_id']}, 1)";
        $debug->show_query("cart_update_product", $sql); 
        $cart->get_db()->exec($sql);    
    }
    
    
    
    $hook_handler->call_hook("cart_insertUpdateProductRecord_post", $product_class_def, $product_type, $product_data);
    
    unset($product_data, $new_product);
}

/*
 * perform an update on a single field, 
 * params
 * the product record
 * and the field that you wish to update
 */
function magento_update_field($product_data, $field_for_update, $product_type, $product_class_def)
{        
    global $hook_handler, $helper_funcs, $cart, $product_entity_type_id, $cart, $attribute_ids, $exclusion_list, $special_handling_list, $debug;      
     
    //This is pretty standard boiler plate here get the data needed
    //-----------------------------
    
    //get the ids of the fields if they are not known
    if(!isset($product_entity_type_id) || $product_entity_type_id == '')
    {
        //get the catalog entity type id
        $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');

        $debug->write("magento_rdi_product_lib.php", "magento_update_field", "product entity type id", 1, array("product_entity_type_id" => $product_entity_type_id));
    }

    //get a list of the fields not allowed to update
    if(!isset($exclusion_list) || $exclusion_list == '')
    {
        //
        $exclusion_list = $cart->get_db()->rows("select allow_update, cart_field from rdi_field_mapping where field_type = 'product' and (field_classification = '{$product_class_def['product_class_id']}' or field_classification is null) and (entity_type = '{$product_type['product_type']}' or entity_type is null)", "cart_field");
        $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "exclusion_list", 1, array("exclusion_list" => $exclusion_list));
    }

     //get a list of the fields that require a special handling
    if(!isset($special_handling_list) || $special_handling_list == '')
    {
        $special_handling_list = $cart->get_db()->rows("select special_handling, cart_field from rdi_field_mapping where field_type = 'product' and (field_classification = '{$product_class_def['product_class_id']}' or field_classification is null) and (entity_type = '{$product_type['product_type']}' or entity_type is null)", "cart_field");
        $debug->write("magento_rdi_product_lib.php", "insertUpdateProductRecord", "special_handling_list", 1, array("special_handling_list" => $special_handling_list));
    }

    if(!isset($attribute_ids) || count($attribute_ids) == 0)       
        get_all_product_attribute_ids();

    $website_ids[] = 0;
    
    //-----------------------
    
    //$attribute_ids contains more info about this field, see if its in there
    
    //cant do this, will break updating a field to a null value
    //make sure the product has this field
    //if( isset($product_data[$field_for_update]) )
    //{ 
        if($field_for_update == 'avail')
        {
            //avail is a special update, its the sell always or etc, it isnt a direct map so has to be handled special
            magento_update_field($product_data, 'manage_stock', $product_type, $product_class_def);
            magento_update_field($product_data, 'use_config_manage_stock', $product_type, $product_class_def);
            
            //if this is a configurable product then we need to update all its associated
            //if()
        }    
        else if(isset($attribute_ids[$field_for_update]))
        {                                     
            $attribute_data = $attribute_ids[$field_for_update];
            
             //check the front end display type, select types need to update the option tables and pass the id now the value in
            if($attribute_data['front_type'] == "select" && ($attribute_data['source_model'] == 'eav/entity_attribute_source_table' || $attribute_data['is_user_defined'] == 1))
            {     
                if(isset($product_data[$field_for_update]))
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
        else if(in_array($field_for_update, array("qty", "min_qty", "use_config_min_qty", "is_qty_decimal", "use_config_backorders", "use_config_backorders", "use_config_min_sale_qty", "use_config_max_sale_qty",
                                                "is_in_stock", "low_stock_date", "use_config_notify_stock_qty", "use_config_manage_stock", "stock_status_changed_auto", "use_config_qty_increments",
                                                "use_config_enable_qty_inc", "manage_stock")))            
        {            
            //stock update
            update_product_stock_entity_field($product_data, $field_for_update);
        }
    //}
}

//works but not needed, keeping for possible use later?
function create_core_url_rewrite_record($product_data, $store_id)
{    
    global $cart;
    
//    $sql = "Delete from core_url_rewrite where product_id = {$product_data['entity_id']}";
//    $cart->get_db()->exec($sql);  
    
    //get the unicity of the url_path
    $id_path = $cart->get_db()->cell("Select id_path from core_url_rewrite where request_path = '{$product_data['url_path']}' and store_id = {$store_id}", "id_path");
    
    $idx = 0;
    
    do
    {        
        //check to see if this url path is already used for this store
        if($id_path !== false)
        {
            //if it is is this just the same product we are dealing with
            if($id_path == "product/{$product_data['entity_id']}")
            {
                return;
            }
            else
            {
                //otherwise we need to fix this url
                $i = pathinfo($product_data['url_path']);

                $product_data['url_path'] = $i['filename'] . "_". $idx . ".". $i['extension'];

            }
                        
            $id_path = $cart->get_db()->cell("Select id_path from core_url_rewrite where request_path = '{$product_data['url_path']}' and store_id = {$store_id}", "id_path");
        }                        
        
        $idx++;
        
    }while($id_path !== false);
    
    $sql = "insert INTO core_url_rewrite (store_id, id_path, request_path, target_path, is_system, product_id) 
                                    values 
                                         (  
                                            {$store_id},
                                            'product/{$product_data['entity_id']}',
                                            '{$product_data['url_path']}',
                                            'catalog/product/view/id/{$product_data['entity_id']}',
                                            1,
                                            {$product_data['entity_id']}
                                         ) on duplicate key update request_path = '{$product_data['url_path']}'";    
    
    $cart->get_db()->exec($sql);  
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
    
    unset($sql, $fields, $values, $columns);
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
        
        unset($refs, $sql);
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
                                        '{$product_type['product_type']}', 
                                        {$sku},  
                                        now(),  
                                        now()
                                        );";
                                        
    $debug->show_query("cart_insert_product", $sql);  
        
    return $cart->get_db()->insert($sql);
}

function update_product_stock_entity($product_entity_id, $product_data, $product_type)
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
    
    //have to set the stock status properly based on the product having stock
    
    //if its a configurable have to sum up the associated and use that value for the comparison
    
    if(isset($product_data['qty']))
    {
        $product_stock = $product_data['qty'];
        $min_qty = 0;
        
//        if($product_type['product_type'] == "configurable")
//        {
//            //get a sum of the products that are associated to this configurable
//            $sql = "select sum(qty) as qty from catalog_product_super_link 
//                    inner join cataloginventory_stock_item on cataloginventory_stock_item.product_id = catalog_product_super_link.parent_id
//                    where catalog_product_super_link.parent_id = '{$product_data['entity_id']}'";
//            $product_stock = $cart->get_db()->cell($sql, "qty");
//        }
        
        if(isset($product_data['min_qty']))
        {
            $min_qty = $product_data['min_qty'];
        }
        
        if($product_stock < $min_qty)
        {
            //set the product to out of stock            
            $product_data['is_in_stock'] = 0;
        }
        else
        {
            $product_data['is_in_stock'] = 1;
        }
        
        unset($min_qty, $product_stock);
    }
    
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
                                                      use_config_enable_qty_increments,
                                                      product_id,
                                                      stock_id,
                                                      manage_stock) 
                                              values (
                                                      " . (!isset($product_data['qty']) ? '1' : $product_data['qty']) . ",
                                                      " . (!isset($product_data['min_qty']) ? '0' : $product_data['min_qty']) . ",
                                                      " . (!isset($product_data['use_config_min_qty']) ? '0' : $product_data['use_config_min_qty']) . ",
                                                      " . (!isset($product_data['is_qty_decimal']) ? '0' : $product_data['is_qty_decimal']) . ",                                                     
                                                      " . (!isset($product_data['use_config_backorders']) ? '0' : $product_data['use_config_backorders']) . ",
                                                      " . (!isset($product_data['use_config_min_sale_qty']) ? '1' : $product_data['use_config_min_sale_qty']) . ", 
                                                      " . (!isset($product_data['use_config_max_sale_qty']) ? '1' : $product_data['use_config_max_sale_qty']) . ", 
                                                      " . (!isset($product_data['is_in_stock']) ? '1' : $product_data['is_in_stock']) . ",
                                                      " . ((!isset($product_data['low_stock_date']) || $product_data['low_stock_date'] == '') ? 'null' : "'" . $product_data['low_stock_date'] . "'") . ", 
                                                      " . (!isset($product_data['use_config_notify_stock_qty']) ? '1' : $product_data['use_config_notify_stock_qty']) . ", 
                                                      " . (!isset($product_data['use_config_manage_stock']) ? '1' : $product_data['use_config_manage_stock']) . ",
                                                      " . (!isset($product_data['stock_status_changed_auto']) ? '1' : $product_data['stock_status_changed_auto']) . ", 
                                                      " . (!isset($product_data['use_config_qty_increments']) ? '1' : $product_data['use_config_qty_increments']) . ", 
                                                      " . (!isset($product_data['use_config_enable_qty_inc']) ? '1' : $product_data['use_config_enable_qty_inc']) . ",
                                                      {$product_entity_id},
                                                      1,
                                                      " . (!isset($product_data['manage_stock']) ? '1' : $product_data['manage_stock']) . "
                                                     )";
    $debug->show_query("cart_update_product", $sql); 
    $cart->get_db()->exec($sql);
    
    unset($sql);
}

function update_product_stock_entity_field($product_data, $field)
{
    global $cart, $debug, $update_availability;
    
    $sql = "update `cataloginventory_stock_item` set 
                                                    {$field} = '{$product_data[$field]}'                                                    
                                                 where
                                                    `product_id` = {$product_data['entity_id']}";                                                    
    $debug->show_query("cart_update_product", $sql); 
    $cart->get_db()->exec($sql);
                      
    unset($sql);
}

function set_visibility_status_array($product_array)
{
    global $hide_out_of_stock, $cart, $disable_out_of_stock, $store_id;

    #get visibility and status attribute
    $visibility_attribute_id = $cart->get_db()->cell("Select attribute_id from eav_attribute where attribute_code = 'visibility'", "attribute_id");
    $status_attribute_id = $cart->get_db()->cell("Select attribute_id from eav_attribute where attribute_code = 'status'", "attribute_id");
    $deactivated_date_attribute_id = $cart->get_db()->cell("Select attribute_id from eav_attribute where attribute_code = 'rdi_deactivated_date'", "attribute_id");
    $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
	
    if(is_array($product_array))
    {
        foreach($product_array as $row)
        {
            #temparily set rdi_deactivated_date to NULL
            $sql = "INSERT IGNORE INTO catalog_product_entity_datetime (entity_type_id, attribute_id, store_id, entity_id, value) values({$product_entity_type_id}, {$deactivated_date_attribute_id}, " . (isset($store_id) ? $store_id : 0) . ", {$row['product_id']}, '3000-12-31 14:29:14')";
            $cart->get_db()->exec($sql);

            #out of stock
            if((($row['qty'] < $row['min_qty']) || $row['qty'] == 0) && $row['manage_stock'] == 1)
            {
                
                #update visibility
                if(isset($hide_out_of_stock) && $hide_out_of_stock == 1)
                {
                    $sql = "UPDATE catalog_product_entity_int SET catalog_product_entity_int.value = 1 
                    WHERE 
                            catalog_product_entity_int.entity_id = {$row['product_id']}
                    AND
                            catalog_product_entity_int.attribute_id = {$visibility_attribute_id}";
                    $cart->get_db()->exec($sql);  	

                }
                #update enabled?
                if(isset($disable_out_of_stock) && $disable_out_of_stock == 1)
                {
                    $sql = "UPDATE catalog_product_entity_int SET catalog_product_entity_int.value = 2 
                    WHERE 
                            catalog_product_entity_int.entity_id = {$row['product_id']}
                    AND
                            catalog_product_entity_int.attribute_id = {$status_attribute_id}"; 
                    $cart->get_db()->exec($sql);  
                }
            }
            else if (($row['qty'] > $row['min_qty']) && $row['manage_stock'] == 1)
            {
                #update visibility
                if(isset($hide_out_of_stock) && $hide_out_of_stock == 1)
                {
                    $sql = "UPDATE catalog_product_entity_int SET catalog_product_entity_int.value = 4
                    WHERE 
                            catalog_product_entity_int.entity_id = {$row['product_id']}
                    AND
                            catalog_product_entity_int.attribute_id = {$visibility_attribute_id}";
                    $cart->get_db()->exec($sql);  
                }
                #update enabled?
                if(isset($disable_out_of_stock) && $disable_out_of_stock == 1)
                {
                    $sql = "UPDATE catalog_product_entity_int SET catalog_product_entity_int.value = 1 
                    WHERE 
                            catalog_product_entity_int.entity_id = {$row['product_id']}
                    AND
                            catalog_product_entity_int.attribute_id = {$status_attribute_id}"; 
                    $cart->get_db()->exec($sql);  
                }
            }
            else 
            {
                #update visibility
                if(isset($hide_out_of_stock) && $hide_out_of_stock == 1)
                {
                    $sql = "UPDATE catalog_product_entity_int SET catalog_product_entity_int.value = 4
                    WHERE 
                            catalog_product_entity_int.entity_id = {$row['product_id']}
                    AND
                            catalog_product_entity_int.attribute_id = {$visibility_attribute_id}";
                    $cart->get_db()->exec($sql);  
                }
                #update enabled?
                if(isset($disable_out_of_stock) && $disable_out_of_stock == 1)
                {
                    $sql = "UPDATE catalog_product_entity_int SET catalog_product_entity_int.value = 1 
                    WHERE 
                            catalog_product_entity_int.entity_id = {$row['product_id']}
                    AND
                            catalog_product_entity_int.attribute_id = {$status_attribute_id}"; 
                    $cart->get_db()->exec($sql);  
                }
            }
        }
    }	
}

function process_out_of_stock()
{
    global $hide_out_of_stock, $cart, $disable_out_of_stock, $store_id, $field_mapping, $pos;
    
    //make out of stock not visible, or in stock visible
    if((isset($hide_out_of_stock) && $hide_out_of_stock == 1) || (isset($disable_out_of_stock) && $disable_out_of_stock == 1))
    {        
        //get the visibility attribute id        
        $related_parent_attribute_id = $cart->get_db()->cell("Select attribute_id from eav_attribute where attribute_code = 'related_parent_id'", "attribute_id");
        $related_attribute_id = $cart->get_db()->cell("Select attribute_id from eav_attribute where attribute_code = 'related_id'", "attribute_id");
        $visibility_attribute_id = $cart->get_db()->cell("Select attribute_id from eav_attribute where attribute_code = 'visibility'", "attribute_id");
        $status_attribute_id = $cart->get_db()->cell("Select attribute_id from eav_attribute where attribute_code = 'status'", "attribute_id");
                       
        #check configurable qty
//        $sql = "SELECT SUM(qty) as qty,parent_id product_id,min_qty, manage_stock FROM catalog_product_super_link 
//                        INNER JOIN cataloginventory_stock_item 
//                        ON cataloginventory_stock_item.product_id = catalog_product_super_link.product_id
//                        #and cataloginventory_stock_item.manage_stock = 1
//                        GROUP BY catalog_product_super_link.parent_id;";
        
        $sql = "SELECT DISTINCT SUM(qty) AS qty,parent_id product_id,min_qty, manage_stock FROM catalog_product_super_link 
                        INNER JOIN cataloginventory_stock_item 
                        ON cataloginventory_stock_item.product_id = catalog_product_super_link.product_id
                        INNER JOIN catalog_product_entity_varchar related_id
                        ON related_id.entity_id = catalog_product_super_link.parent_id
                        AND related_id.attribute_id = {$related_attribute_id}
                        INNER JOIN " . $pos->get_processor("rdi_staging_db_lib")->get_table_name('in_items') . " style on " .
                                       $field_mapping->map_field('product', 'related_id', 'configurable') . " = related_id.value                        
                        GROUP BY catalog_product_super_link.parent_id;";

        
        $rows = $cart->get_db()->rows($sql);

        set_visibility_status_array($rows);

        #stand alone simples qty
//        $sql = "SELECT qty AS qty,entity_id product_id,min_qty, manage_stock FROM catalog_product_entity 
//                                LEFT JOIN catalog_product_super_link
//                                ON catalog_product_entity.entity_id = catalog_product_super_link.product_id
//                                AND catalog_product_entity.type_id = 'simple'
//                                INNER JOIN cataloginventory_stock_item 
//                                ON cataloginventory_stock_item.product_id = catalog_product_entity.entity_id
//                                #and cataloginventory_stock_item.manage_stock = 1
//                                WHERE catalog_product_super_link.product_id IS NULL
//                                AND catalog_product_entity.type_id = 'simple';";
        $sql = "SELECT DISTINCT qty AS qty,catalog_product_entity.entity_id product_id,min_qty, manage_stock FROM catalog_product_entity 
                                LEFT JOIN catalog_product_super_link
                                    ON catalog_product_entity.entity_id = catalog_product_super_link.product_id
                                    AND catalog_product_entity.type_id = 'simple'
                                INNER JOIN cataloginventory_stock_item 
                                    ON cataloginventory_stock_item.product_id = catalog_product_entity.entity_id                                
                                INNER JOIN catalog_product_entity_varchar related_id
                                    ON related_id.entity_id = catalog_product_entity.entity_id
                                    AND related_id.attribute_id = {$related_parent_attribute_id}
                                INNER JOIN " . $pos->get_processor("rdi_staging_db_lib")->get_table_name('in_items') . " item on " .
                                              $field_mapping->map_field('product','related_id','simple') . " = related_id.value
                                WHERE catalog_product_super_link.product_id IS NULL
                                    AND catalog_product_entity.type_id = 'simple';";
                                                                
        $rows = $cart->get_db()->rows($sql);

        set_visibility_status_array($rows); 
    }
}

function insert_product_stock_entity($product_entity_id, $product_data)
{
    global $cart, $debug;
    
    $sql = "INSERT INTO `cataloginventory_stock_item` (`product_id`, `stock_id`,
                                                `qty`, `min_qty`, `use_config_min_qty`, `is_qty_decimal`, `use_config_backorders`,
                                                `use_config_min_sale_qty`, `use_config_max_sale_qty`, `is_in_stock`,
                                                `low_stock_date`, `use_config_notify_stock_qty`, `use_config_manage_stock`,
                                                `stock_status_changed_automatically`, `use_config_qty_increments`, `use_config_enable_qty_increments`, manage_stock) 
                                             VALUES 
                                             (
                                                {$product_entity_id},
                                                1,
                                                " . (!isset($product_data['qty']) ? '1' : $product_data['qty']) . ",
                                                " . (!isset($product_data['min_qty']) ? '0' : $product_data['min_qty']) . ",                                                    
                                                " . (!isset($product_data['use_config_min_qty']) ? '0' : $product_data['use_config_min_qty']) . ",
                                                " . (!isset($product_data['is_qty_decimal']) ? '0' : $product_data['is_qty_decimal']) . ",
                                                " . (!isset($product_data['use_config_backorders']) ? '0' : $product_data['use_config_backorders']) . ",
                                                " . (!isset($product_data['use_config_min_sale_qty']) ? '1' : $product_data['use_config_min_sale_qty']) . ",
                                                " . (!isset($product_data['use_config_max_sale_qty']) ? '1' : $product_data['use_config_max_sale_qty']) . ",
                                                " . (!isset($product_data['is_in_stock']) ? '1' : $product_data['is_in_stock']) . ",
                                                " . ((!isset($product_data['low_stock_date']) || $product_data['low_stock_date'] == '') ? 'null' : "'" . $product_data['low_stock_date'] . "'") . ",                                                                                                
                                                " . (!isset($product_data['use_config_notify_stock_qty']) ? '1' : $product_data['use_config_notify_stock_qty']) . ",
                                                " . (!isset($product_data['use_config_manage_stock']) ? '1' : $product_data['use_config_manage_stock']) . ",
                                                " . (!isset($product_data['stock_status_changed_auto']) ? '1' : $product_data['stock_status_changed_auto']) . ",
                                                " . (!isset($product_data['use_config_qty_increments']) ? '1' : $product_data['use_config_qty_increments']) . ",
                                                " . (!isset($product_data['use_config_enable_qty_inc']) ? '1' : $product_data['use_config_enable_qty_inc']) . ",
                                                " . (!isset($product_data['manage_stock']) ? '1' : $product_data['manage_stock']) . "
                                              );";
                                                
    $debug->show_query("cart_insert_product", $sql);                                             
    $cart->get_db()->exec($sql);
    
    unset($sql);
}

//generate an update request and run it for the specified data
//attribute values will be code => value array
function update_product_attribute_field_table($product_entity_id, $field_type, $store_id, $attribute_values)
{
     global $cart, $product_entity_type_id, $debug,$index_one_at_a_time;

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

            //$sql = "update `catalog_product_entity_{$field_type}` set value = {$attribute_value} where entity_type_id = {$product_entity_type_id}
            //        and attribute_id = {$attribute_id} and store_id = {$store_id} and entity_id = {$product_entity_id}";

            $sql = "replace into `catalog_product_entity_{$field_type}` (entity_type_id, attribute_id, store_id, entity_id, value)
                        values(
                                {$product_entity_type_id},
                                {$attribute_id},
                                {$store_id},
                                {$product_entity_id},
                                {$attribute_value}
                            )";
            if(isset($index_one_at_a_time) && $index_one_at_a_time == 1)
             {
                $cart->get_db()->exec("insert into rdi_magento_save_products ( product_id ) values ({$product_entity_id})");                   
             }
            $debug->show_query("cart_update_product", $sql); 
            $cart->get_db()->exec($sql);
            
            unset($sql);
        }            
     }
}

//generate an insert request and run it for the specified data
//attribute values will be code => value array
function insert_product_attribute_field_table($product_entity_id, $field_type, $store_id, $attribute_values)
{
     global $cart, $product_entity_type_id, $debug,$index_one_at_a_time;
          
     if($field_type != "static")
     {
        $values = '';
        foreach($attribute_values as $attribute_id => $attribute_value)
        {                
            if($attribute_value != 'null' && $attribute_value != null && $attribute_value != 'NOW()')
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
            $sql = "INSERT ignore INTO `catalog_product_entity_{$field_type}` (`entity_type_id`,`attribute_id`,
                                                    `store_id`,`entity_id`,`value`) 
                                                VALUES
                                                {$values}";
            if(isset($index_one_at_a_time) && $index_one_at_a_time == 1)
             {
                $cart->get_db()->exec("insert into rdi_magento_save_products ( product_id ) values ({$product_entity_id})");                   
             }
            $debug->show_query("cart_update_product", $sql); 
            $cart->get_db()->insert($sql);
            
            unset($sql);
        }
        
        unset($values);
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
    global $cart, $store_id;        
     
    $attribute_value = "'" . $cart->get_db()->clean($attribute_value) . "'"; 
    
    $option_id = $cart->get_db()->cell("select eav_attribute_option.option_id from eav_attribute_option
                                    inner join eav_attribute_option_value on eav_attribute_option_value.option_id = eav_attribute_option.option_id
                                    inner join eav_attribute on eav_attribute.attribute_id = eav_attribute_option.attribute_id and eav_attribute.attribute_code = '{$attribute_code}'
                                    where eav_attribute_option_value.value = {$attribute_value} and eav_attribute_option_value.store_id = {$store_id}", "option_id");     
    if($option_id != '')
        return $option_id;
    
    return false;
}

function add_attribute_option_value($attribute_code, $attribute_value, $position)
{
    global $cart, $attribute_ids, $debug, $store_id;
            
    if($position == '')
        $position = 0;
    
    $sql = "INSERT INTO `eav_attribute_option` (`attribute_id`, `sort_order`) VALUES ({$attribute_ids[$attribute_code]['attribute_id']}, {$position})";
    $debug->show_query("cart_insert_product", $sql); 
    $option_id = $cart->get_db()->insert($sql);

    $attribute_value = "'" . $cart->get_db()->clean($attribute_value) . "'";  
    
    $sql = "INSERT INTO `eav_attribute_option_value` (`option_id`, `store_id`, `value`) VALUES ({$option_id}, {$store_id}, {$attribute_value})";
    $debug->show_query("cart_insert_product", $sql); 
    $value_id = $cart->get_db()->insert($sql);
    
    unset($sql);
    
    if($option_id != '')
    {
        return $option_id;
    }
    
    return false;
}

function process_link_insert($upsell_data)
{
    global $cart, $debug;
        
    //print_r($upsell_data);
    
    if(is_array($upsell_data) && count($upsell_data) > 0)
    {    
        //insert the data retrieved

        //get the attribute id for the position
        //get the upsell link type id
        //$link_type_id = $cart->get_db()->cell("select link_type_id from catalog_product_link_type where code = 'up_sell'", 'link_type_id');

        //$product_link_attribute_id = $cart->get_db()->cell("select product_link_attribute_id from catalog_product_link_attribute where link_type_id = '{$link_type_id}' 
        //                        and product_link_attribute_code = 'position'", 'product_link_attribute_id');

        //build out a chained query
//        $product_link_query = "insert into catalog_product_link(product_id, linked_product_id, link_type_id) values";
//        $position_query = "insert into catalog_product_link_attribute_int (product_link_attribute_id, link_id, value) values";
//        foreach($upsell_data as $p)
//        {
//            $product_link_query .= "({$p['product_id']}, {$p['linked_product_id']}, {$p['link_type_id']}),";
//            $position_query .= "({$product_link_attribute_id}, {$link_type_id}, {$p['position']}),";
//        }
//
//        //remove trailing comma
//        $product_link_query = substr($product_link_query,0,-1);    
//        $position_query = substr($position_query,0,-1);    
//
//        //insert the upsell data
//        $cart->get_db()->exec($product_link_query);
//        
//        //insert the the ordering 
//        $cart->get_db()->exec($position_query);
        
        foreach($upsell_data as $p)
        {
            $link_id = $cart->get_db()->insert("insert into catalog_product_link(product_id, linked_product_id, link_type_id) 
                    values({$p['product_id']}, {$p['linked_product_id']}, {$p['link_type_id']})");
                    
            $cart->get_db()->insert("insert into catalog_product_link_attribute_int (link_id, product_link_attribute_id, value) 
                values ({$link_id}, {$p['link_type_id']}, {$p['position']})");
        }                     
    }
    
    unset($upsell_data);
}

function process_link_update($upsell_data)
{
    global $cart, $debug;
    
    if(is_array($upsell_data) && count($upsell_data) > 0)
    {     
        foreach($upsell_data as $p)
        {                                     
            $cart->get_db()->exec("replace into catalog_product_link_attribute_int (link_id, product_link_attribute_id, value) 
                values ({$p['link_id']}, {$p['link_type_id']}, {$p['position']})");
        }                     
    }
    
    unset($upsell_data);    
}

function process_link_removal($upsell_data)
{
    global $cart, $debug;
    
    if(is_array($upsell_data) && count($upsell_data) > 0)
    {
        foreach($upsell_data as $p)
        {
            $cart->get_db()->exec("DELETE FROM catalog_product_link_attribute_int where link_id = {$p['link_id']}");
            $cart->get_db()->exec("DELETE FROM catalog_product_link where link_id = {$p['link_id']}");
        }
    }
}

function process_attribute_price_updates($product_class, $product_type)
{
    global $cart;
    //need to get a little data to prep for the query

    //need to the id of each of the attributes

    $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
    $price_id = $cart->get_db()->cell("select attribute_id from eav_attribute where attribute_code = 'price' and entity_type_id = {$product_entity_type_id}", "attribute_id");

//    $sql = "SELECT b.value, s.sku, catalog_product_super_attribute.product_super_attribute_id, eav_attribute_option_value.value_id, 0, (p.value - b.value) as variance_price, 0
      $sql = "SELECT catalog_product_super_attribute.product_super_attribute_id, eav_attribute_option_value.value_id, 0, (p.value - b.value) as variance_price, 0
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
        $sql = "replace into catalog_product_super_attribute_pricing (product_super_attribute_id, value_index, is_percent, pricing_value, website_id) " . $sql;
    }
    
    if(is_array($product_class['field_data']))
    {
        $query = $sql;
        
        //get the attribute variances for the each of the attribute codes, have to do a little logic 
        foreach($product_class['field_data'] as $field)
        {   
            $sql = $query;
            
            $field_id = $cart->get_db()->cell("select attribute_id from eav_attribute where attribute_code = '{$field['cart_field']}' and entity_type_id = {$product_entity_type_id}", "attribute_id");

            $sql .= "inner join catalog_product_super_attribute on catalog_product_super_attribute.product_id = catalog_product_super_link.parent_id and catalog_product_super_attribute.attribute_id = {$field_id}
                    inner join catalog_product_entity_int on catalog_product_entity_int.entity_id = catalog_product_super_link.product_id and catalog_product_entity_int.attribute_id = {$field_id}
                    inner join eav_attribute_option on eav_attribute_option.option_id = catalog_product_entity_int.value
                    inner join eav_attribute_option_value on eav_attribute_option_value.option_id = eav_attribute_option.option_id 
                    having variance_price != 0";

    //        if(count($product_class['field_data']) < 2)
    //        {
                //$cart->get_db()->exec($sql);
    //        }
    //        else
    //        {
    //            $this->db_connection->rows($sql);
    //        }

            unset($field_id);
        }
    }
    unset($sql, $product_entity_type_id, $price_id);
}

function update_availability()
{
    global $cart, $update_availability;
    
    //handle the availability, make it optional setting
    if(isset($update_availability) && $update_availability > 0)
    {                   
        $sql = "UPDATE cataloginventory_stock_item 
					JOIN catalog_product_entity cpe ON cpe.entity_id = cataloginventory_stock_item.product_id
					SET is_in_stock = 0
					  WHERE  cpe.type_id = 'simple' and cataloginventory_stock_item.manage_stock = 1
					  AND   qty <= min_qty";
                        

        $cart->get_db()->exec($sql);  

        $sql = "UPDATE cataloginventory_stock_item 
					JOIN catalog_product_entity cpe ON cpe.entity_id = cataloginventory_stock_item.product_id
					SET is_in_stock = 1
					  WHERE  cpe.type_id = 'simple' and cataloginventory_stock_item.manage_stock = 1
					  AND   qty > min_qty";

        $cart->get_db()->exec($sql);
		
		  //Configurables
		  //turn these off
		  //SET is_in_stock = 0 
		$sql = "SELECT SUM(cataloginventory_stock_item.qty) AS qty,parent_id product_id,cataloginventory_stock_item.min_qty , configurable.is_in_stock FROM catalog_product_super_link 
                        INNER JOIN cataloginventory_stock_item 
                        ON cataloginventory_stock_item.product_id = catalog_product_super_link.product_id
                        JOIN cataloginventory_stock_item configurable
                        ON configurable.product_id = catalog_product_super_link.parent_id
                        WHERE configurable.is_in_stock = 1 and configurable.manage_stock = 1
                        GROUP BY catalog_product_super_link.parent_id
                        HAVING SUM(cataloginventory_stock_item.qty) <= SUM(cataloginventory_stock_item.min_qty)";
						
		$rows = $cart->get_db()->rows($sql);	

		if(is_array($rows))
		{
			foreach($rows as $row)
			{
				$sql = "UPDATE cataloginventory_stock_item 
						SET is_in_stock = 0
					  WHERE  product_id = {$row['product_id']} and cataloginventory_stock_item.manage_stock = 1";
				$cart->get_db()->exec($sql);
			}
		}
		
		// turn these on
		//SET is_in_stock = 1 
		$sql = "SELECT SUM(cataloginventory_stock_item.qty) AS qty,parent_id product_id,cataloginventory_stock_item.min_qty , configurable.is_in_stock FROM catalog_product_super_link 
                        INNER JOIN cataloginventory_stock_item 
                        ON cataloginventory_stock_item.product_id = catalog_product_super_link.product_id
                        JOIN cataloginventory_stock_item configurable
                        ON configurable.product_id = catalog_product_super_link.parent_id
                        WHERE configurable.is_in_stock = 0 and configurable.manage_stock = 1
                        GROUP BY catalog_product_super_link.parent_id
                        HAVING SUM(cataloginventory_stock_item.qty) > SUM(cataloginventory_stock_item.min_qty)
                        ";
						
		$rows = $cart->get_db()->rows($sql);
		
		if(is_array($rows))
		{
			foreach($rows as $row)
			{
				$sql = "UPDATE cataloginventory_stock_item 
						SET is_in_stock = 1
					  WHERE  product_id = {$row['product_id']} and cataloginventory_stock_item.manage_stock = 1";
				$cart->get_db()->exec($sql);
			}
		}
		
		

    }
}


//function set_configurable_qty()
//{
//    global $cart;
//    
//    //tried to figure it out in a single query but the structure being so complex didnt work
//
//    //get a list of the configureables
//    $sql = "select entity_id from catalog_product_entity where type_id = 'configurable' or type_id = 'grouped'";
//    $ids = $cart->get_db()->cells($sql, "entity_id");
//
//    //loop it and get the sums of its simples
//    foreach($ids as $id)
//    {
//        $sql = "update cataloginventory_stock_item itm
//                    set qty = 0
//                    where itm.product_id = {$id}";
//
//        $cart->get_db()->exec($sql);
//    }
//}

//send deactivated products into rdi_delete
function stage_stale_deactivated()
{
    global $cart, $deactivated_delete_time;
	
	//check the setting is there and not equal to zero
	if(isset($deactivated_delete_time) && $deactivated_delete_time !== '0')
	{
		$entity_type_id = $cart->get_db()->cell("SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'catalog_product'","entity_type_id");

		$rdi_deactivated_attribute_id = $cart->get_db()->cell("SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'rdi_deactivated_date' AND entity_type_id = {$entity_type_id}",'attribute_id');
		//will need both incase one is NULL being on an older integration that didnt fill the related_parent_id
		$related_parent_id = $cart->get_db()->cell("SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'related_parent_id' AND entity_type_id = {$entity_type_id}",'attribute_id');
		$related_id = $cart->get_db()->cell("SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'related_id' AND entity_type_id = {$entity_type_id}",'attribute_id');
				
		$cart->get_db()->exec("CREATE  TABLE IF NOT EXISTS `rdi_delete` (
										`product_id` VARCHAR(20) NULL DEFAULT NULL,
										`related_id` VARCHAR(20) NULL DEFAULT NULL,
										`disabled_date` DATETIME NULL DEFAULT NULL COMMENT 'This is the deactivated datetime',
										`deleted_date` DATETIME NULL DEFAULT NULL COMMENT 'This is the deactivated datetime',
										`entity_type` VARCHAR(20) NULL DEFAULT NULL
										)
										COLLATE='latin1_swedish_ci'
										ENGINE=MyISAM;
										");
		
						
		//fill the rdi_delete staging/log table
		$cart->get_db()->exec("REPLACE INTO rdi_delete (product_id,related_id,disabled_date,deleted_date,entity_type)
								SELECT 
								  e.entity_id product_id,
								  IFNULL(parent.value,related.value),
								  d.`value` `datetime`,
								  NULL deleted_date,
								  e.type_id entity_type 
								FROM
								  catalog_product_entity_datetime d 
								  JOIN catalog_product_entity e 
									ON e.entity_id = d.entity_id 
								  JOIN catalog_product_entity_varchar parent
									ON parent.entity_id = d.entity_id 
									and parent.attribute_id = {$related_parent_id}
								  JOIN catalog_product_entity_varchar related
									ON related.entity_id = d.entity_id 
									and related.attribute_id = {$related_id}
								WHERE d.value IS NOT NULL 
								  AND d.attribute_id = {$rdi_deactivated_attribute_id} 
								  ");
										  
    }
}





//Delete the items that were deactivated past the rdi_deactivated_date, but only the first 10 of them
function purge_stale_deactivated()
{
    global $cart, $deactivated_delete_time, $delete_products;
    
   // return;
    
    require_once '../app/Mage.php';
    
    $deactivated_date_attribute_id = $cart->get_db()->cell("Select attribute_id from eav_attribute where attribute_code = 'rdi_deactivated_date'", "attribute_id");
    
    if($deactivated_date_attribute_id)
    {
		//update the deleted date to the deactivated date if the deactivated date is no longer set and the product is enabled.
		$sql = "UPDATE rdi_delete rd
					INNER JOIN catalog_product_entity_datetime d
					ON d.entity_id = rd.product_id
					AND d.attribute_id = 138
					INNER JOIN catalog_product_entity_int i
					ON i.entity_id = rd.product_id
					AND i.attribute_id = 96
					SET rd.deleted_date = rd.disabled_date
					WHERE rd.deleted_date IS NULL
					AND i.value = 1
					AND d.value IS NULL";
		$cart->get_db()->exec($sql);
		
		//update the deleted_date to a day before disabled to signify that they deleted it
		$cart->get_db()->exec("UPDATE rdi_delete
								  LEFT JOIN catalog_product_entity
								  on catalog_product_entity.entity_id = rdi_delete.product_id
								  SET deleted_date = ADDDATE(
                                disabled_date,
                                INTERVAL - 1 DAY
                              )
							  WHERE	  catalog_product_entity.entity_id IS NULL 
								  AND deleted_date IS NULL");
								
		
		
        //select 10 products from the list and join to see if the product is still there.
        $sql = "SELECT DISTINCT 
                            `disabled_date`,
                            product_id,
                            entity_type 
                          FROM
                            rdi_delete
						  JOIN catalog_product_entity
						  on catalog_product_entity.entity_id = rdi_delete.product_id
                          WHERE (
                              disabled_date < ADDDATE(
                                NOW(),
                                INTERVAL - {$deactivated_delete_time} DAY
                              )
                            )
							AND deleted_date IS NULL
                          LIMIT 10 ";
        
        $delete_products = $cart->get_db()->rows($sql);
		
        $delete_ids = array();
		
        if(is_array($delete_products))//if there are products to delete
        {
            foreach($delete_products as $delete_product)
            {
                //getting all the simples under a configurable to delete
                if($delete_product['entity_type'] == 'configurable')
                {
                      $delete_ids = $cart->get_db()->cells("SELECT product_id FROM catalog_product_super_link WHERE parent_id = {$delete_product['product_id']}","product_id");                  
                }
                //add the configurable or the simple
                $delete_ids[] = $delete_product['product_id'];
                
				echo "<pre>";
				print_r($delete_ids);
				echo "</pre>";
				
				
				if(!isset($delete_products)|| $delete_products==0){return;}
              //here we have to get all the product ids and then check if the products and in our deleteIDS. We delete are in it.
			  Mage :: app( "default" ) -> setCurrentStore( Mage_Core_Model_App :: ADMIN_STORE_ID );
			  $products = Mage :: getResourceModel('catalog/product_collection')->setStoreId(1)->getAllIds();
			  if(is_array($products))
			  {
				
				foreach ($products as $key => $productId)
				{
					
					if(in_array($productId,$delete_ids))
					{
						//check to see if the product deleted
					
					  $message = "NOW()";
					  try {
						$product = Mage :: getSingleton('catalog/product')->load($productId);
						Mage :: dispatchEvent('catalog_controller_product_delete', array('product' => $product));
						$product->delete();
					  } catch (Exception $e) {
						$message = " NULL ";
						echo "<br/>Can't delete product w/ id: $productId";
					  }
					  $cart->get_db()->exec("UPDATE rdi_delete  SET deleted_date = {$message} WHERE product_id = {$productId}");
					}
				}
				
			  }  
            }
                //delete via queries
    //            //$cart->get_db()->exec("delete from `catalog_product_bundle_option` where entity_id = {$id}where entity_id = {$id}");
    //            //$cart->get_db()->exec("delete from `catalog_product_bundle_option_value` where entity_id = {$id}where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_bundle_selection` where product_id = {$id}where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_entity_datetime` where entity_id = {$id}where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_entity_decimal` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_entity_gallery` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_entity_int` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_entity_media_gallery` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_entity_media_gallery_value` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_entity_text` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_entity_tier_price` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_entity_varchar` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_link` where product_id = {$id}");
    //            //$cart->get_db()->exec("delete from `catalog_product_link_attribute` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_link_attribute_decimal` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_link_attribute_int` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_link_attribute_varchar` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_link_type` where entity_id = {$id}");
    //            //$cart->get_db()->exec("delete from `catalog_product_option` where entity_id = {$id}");
    //            //$cart->get_db()->exec("delete from `catalog_product_option_price` where entity_id = {$id}");
    //            //$cart->get_db()->exec("delete from `catalog_product_option_title` where entity_id = {$id}");
    ////            $cart->get_db()->exec("delete from `catalog_product_option_type_price` where entity_id = {$id}");
    ////            $cart->get_db()->exec("delete from `catalog_product_option_type_title` where entity_id = {$id}");
    ////            $cart->get_db()->exec("delete from `catalog_product_option_type_value` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_super_attribute` where product_id = {$id}");
    //            //$cart->get_db()->exec("delete from `catalog_product_super_attribute_label` where entity_id = {$id}");
    //            //$cart->get_db()->exec("delete from `catalog_product_super_attribute_pricing` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_super_link` where product_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_enabled_index` where product_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_website` where product_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_product_entity` where entity_id = {$id}");
    ////            $cart->get_db()->exec("delete from `catalog_category_entity` where entity_id = {$id}");
    ////            $cart->get_db()->exec("delete from `catalog_category_entity_datetime` where entity_id = {$id}");
    ////            $cart->get_db()->exec("delete from `catalog_category_entity_decimal` where entity_id = {$id}");
    ////            $cart->get_db()->exec("delete from `catalog_category_entity_int` where entity_id = {$id}");
    ////            $cart->get_db()->exec("delete from `catalog_category_entity_text` where entity_id = {$id}");
    ////            $cart->get_db()->exec("delete from `catalog_category_entity_varchar` where entity_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_category_product` where product_id = {$id}");
    //            $cart->get_db()->exec("delete from `catalog_category_product_index` where product_id = {$id}");
                 
        }
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
//             $attribute_data[]['attribute_id'] = $cart->get_db()->cell("select attribute_id from eav_attribute where attribute_code = '{$attr['cart_field']}' and entity_type_id = {$product_entity_type_id}", "attribute_idwhere entity_id = {$id}");
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