<?php
/** 
 * category library for version 1.6.x
 * 
 * @global type $helper_funcs
 * @global type $cart
 * @global type $category_entity_type_id
 * @global type $category_attribute_ids
 * @global type $cart
 * @global type $debug
 * @global type $cat_exclusion_list
 * @global type $cat_special_handling_list
 * @global type $category_anchors
 * @global type $store_id
 * @param type $category_data
 * @return type
 * @package Core\Load\Catalog\Magento
 */

//preps, inserts or updates a category record
function insertUpdateCategoryRecord($category_data)
{   
    global $helper_funcs, $cart, $category_entity_type_id, $category_attribute_ids, $cat_exclusion_list, $cat_special_handling_list, $category_anchors, $store_id, $updated_categories;        
    
     //check if the category is new
    $new_category = $category_data['entity_id'] != '' ? false : true;
    
    //get the ids of the fields if they are not known
    if(!isset($category_entity_type_id) || $category_entity_type_id == '')
    {                
        //get the catalog entity type id
        $category_entity_type_id = $cart->get_db()->cell("select entity_type_id from {$cart->get_db()->get_db_prefix()}eav_entity_type where entity_type_code = 'catalog_category'", 'entity_type_id');
    }
    
    //get a list of the fields not allowed to update
    if(!isset($cat_exclusion_list) || $cat_exclusion_list == '')
    {
        $cat_exclusion_list = $cart->get_db()->rows("select allow_update, cart_field from rdi_field_mapping where field_type = 'category'", "cart_field");
    }
    
    //get a list of the fields that require a special handling
    if(!isset($cat_special_handling_list) || $cat_special_handling_list == '')
    {
        $cat_special_handling_list = $cart->get_db()->rows("select special_handling, cart_field from rdi_field_mapping where field_type = 'category'", "cart_field");
    }
    
    if(!isset($attribute_ids) || count($attribute_ids) == 0)       
        get_all_category_attribute_ids();
         
    if(!isset($category_data['store_id']) || $category_data['store_id'] == '' || $category_data['store_id'] == ' ')
        $category_data['store_id'] = $store_id;
       
    
    //the break down of the attribute data and their values for this product
    $attribute_data_sets = array();
  
    //build out the value array
    foreach($category_attribute_ids as $attribute_code => $attribute_data)
    {                
        $attribute_field = array();
        
        if($attribute_data['type'] == "static")
            continue;
        
        //skip related id as it wont exist
        if($attribute_code == "related_id")
            continue;
        
        if(!$new_category && isset($cat_exclusion_list[$attribute_code]) && $cat_exclusion_list[$attribute_code]['allow_update'] == 0)
        {            
            continue;
        }
                             
        //check if the product data has included this value
        if(isset($category_data[$attribute_code]))
        {                                    
            if($category_data[$attribute_code] == '')
                    $category_data[$attribute_code] = 'null';
                        
            //check if there is special handling on this field
            if(isset($cat_special_handling_list[$attribute_code]) && $cat_special_handling_list[$attribute_code]['special_handling'] != '')
            {               
                $category_data[$attribute_code] = $helper_funcs->process_special_handling($cat_special_handling_list[$attribute_code]['special_handling'], $category_data[$attribute_code], $category_data, 'category');             
            }
                        
            //set the field we will send for processing            
            //add it to the broke down sets
            $attribute_data_sets[$attribute_data['type']][$attribute_data['attribute_id']] = $category_data[$attribute_code];            
        }
    }
    
    if(!$new_category)
    {
        $updated_categories[] = $category_data['entity_id'];
        //update
        update_category_entity($category_data['path'], $category_data['position'], $category_data['related_id']);               
    }
    else
    {   
        //insert
        $category_data['entity_id'] = insert_category_entity($category_data['path'], $category_data['position'], $category_data['related_id']);                     
    }
        
    foreach($attribute_data_sets as $type => $attribute_data_set)
    {   
        if(!$new_category)
        {                
            //update the attribute fields
            update_category_attribute_field_table($category_data['entity_id'], $type, $category_data['store_id'], $attribute_data_set);          
        }
        else
        {
            //insert the attribute fields
            insert_category_attribute_field_table($category_data['entity_id'], $type, $category_data['store_id'], $attribute_data_set);                
        }
    }
    
    unset($attribute_data_sets);
    
    return $category_data['entity_id'];
}

//remove the specified category id to the list of of categories this product is assigned to
function remove_product_category_relation($product_id, $category_id)
{
    
}

function update_category_entity($path, $position, $related_id)
{
    global $cart, $category_entity_type_id;
    
    //level is the number of slashes in the path
    $level = substr_count($path, '/');
    
    $pathIDs = explode('/', $path);
    
    $parent_categories = str_replace('/', ',', $path);
    
    //check if we are updating the position of the category
    $position_update = $cart->get_db()->cell("Select allow_update from rdi_field_mapping where field_type = 'category' and cart_field = 'position'", 'allow_update');
    
    $sql = "update `{$cart->get_db()->get_db_prefix()}catalog_category_entity` SET 
                                                 `parent_id` = {$pathIDs[count($pathIDs) - 1]},
                                                 `updated_at` = now(),
                                                 `path` = '{$path}',";                                                     
    if($position_update == 1) $sql .=           "`position` = " . ($position == null ? '0' : $position) . ",";
    $sql .=                                     "`level` = {$level}
                                             WHERE entity_type_id = {$category_entity_type_id} and related_id = '{$related_id}'";
      
   $category_entity_id = $cart->get_db()->exec($sql);
   
   $cart->get_processor("rdi_cart_common")->mark_category_updated($category_entity_id);
   
   //echo $sql;                                                                           
                           
   unset($sql, $level, $pathIDs, $parent_categories, $category_entity_id);
}

function insert_category_entity($path, $position, $related_id)
{
    global $cart, $category_entity_type_id, $debug, $category_attribute_set_id;
    
    //level is the number of slashes in the path
    $level = substr_count($path, '/') + 1;
    
    $pathIDs = explode('/', $path);
    
   // print_r($pathIDs);
    
    
    if(!isset($category_attribute_set_id) || strlen($category_attribute_set_id) == 0)
    {
        $category_attribute_set_id = $cart->get_db()->cell("SELECT eas.attribute_set_id FROM {$cart->prefix}eav_attribute_set eas
                                                        JOIN {$cart->prefix}eav_entity_type et
                                                        ON et.entity_type_id = eas.entity_type_id 
                                                        AND et.entity_type_code = 'catalog_category'","attribute_set_id");
    }
    
    if($position == '')   
    {
        $sql = "SELECT MAX(`position`) as pos FROM `{$cart->get_db()->get_db_prefix()}catalog_category_entity` WHERE (`path` LIKE '{$category_data['path']}/%') AND (`level` = {$level})";
        $debug->show_query("cart_insert_category", $sql);
        $position = $cart->get_db()->cell($sql, "pos");        
    }
    
    $parent_categories = str_replace('/', ',', $path);
    
    $sql = "UPDATE `{$cart->get_db()->get_db_prefix()}catalog_category_entity` SET `children_count` = children_count+1 WHERE (entity_id IN({$parent_categories}))";
    $cart->get_db()->exec($sql);
    
    $debug->show_query("cart_insert_category", $sql);
    
    $debug->write("magento_rdi_catalog_lib.php", "insertUpdateCategoryRecord", "setup category entity", 3, array("level" => $level,
                                                                                                                 "pathIDs" => $pathIDs,
                                                                                                                 "position"=> $position,
                                                                                                                 "parent_categories"=> $parent_categories
                                                                                                                 ));
    
    $sql = "INSERT INTO `{$cart->get_db()->get_db_prefix()}catalog_category_entity` (`entity_type_id`, 
                            `attribute_set_id`, `parent_id`, `created_at`, `updated_at`,
                            `path`, `position`, `level`, `children_count`, `related_id`) 
                            VALUES (
                                    '{$category_entity_type_id}',
                                    '{$category_attribute_set_id}',
                                    {$pathIDs[count($pathIDs) - 1]},
                                    NOW(),
                                    NOW(),
                                    '{$path}',
                                    " . ($position == null ? '0' : $position) . ",
                                    {$level},                                  
                                    0,
                                    '{$related_id}')";
                                    
   $category_entity_id = $cart->get_db()->insert($sql);
   
   $debug->show_query("cart_insert_category", $sql);
        
   $sql = "UPDATE `{$cart->get_db()->get_db_prefix()}catalog_category_entity` SET `path` = '{$path}/{$category_entity_id}' WHERE (entity_id = '{$category_entity_id}')";
   $cart->get_db()->exec($sql);
   
   $debug->show_query("cart_insert_category", $sql);
   
   $debug->write("magento_rdi_catalog_lib.php", "insertUpdateCategoryRecord", "category entity id", 1, array("category_entity_id" => $category_entity_id));
   
   unset($sql);
   
   return $category_entity_id;
}

//generate an update request and run it for the specified data
//attribute values will be code => value array
function update_category_attribute_field_table($category_entity_id, $field_type, $store_id, $attribute_values)
{
     global $cart, $category_entity_type_id, $debug;

     $cart->get_processor("rdi_cart_common")->mark_category_updated($category_entity_id);
     
     if($field_type != "static")
     {     
        $values = '';
        foreach($attribute_values as $attribute_id => $attribute_value)
        {         
            $sql = '';

            if($attribute_value != 'null' && $attribute_value != null)
                $attribute_value = "'" . $cart->get_db()->clean($attribute_value) . "'"; 

            if($field_type == "text")
            {
                if($attribute_value != 'null' && $attribute_value != null)
                {
                    //text there is a possibility of not having the record in the first place, and update is worthless then
                    //le sigh.
                    $sql = "INSERT INTO `{$cart->get_db()->get_db_prefix()}catalog_category_entity_{$field_type}` 
                        (value, entity_type_id, attribute_id, store_id, entity_id) 
                        values({$attribute_value}, {$category_entity_type_id}, {$attribute_id}, {$store_id}, {$category_entity_id})
                        ON DUPLICATE KEY UPDATE value = {$attribute_value}";                               
                }
            }
            else
            {
                $sql = "update `{$cart->get_db()->get_db_prefix()}catalog_category_entity_{$field_type}` set value = {$attribute_value} where entity_type_id = {$category_entity_type_id}
                    and attribute_id = {$attribute_id} and store_id = {$store_id} and entity_id = {$category_entity_id}";
            }

            if($sql != '')
            {
                $debug->show_query("cart_update_category", $sql);
                $cart->get_db()->exec($sql);
            }
        }    
     }
}

//generate an insert request and run it for the specified data
//attribute values will be code => value array
function insert_category_attribute_field_table($category_entity_id, $field_type, $store_id, $attribute_values)
{
     global $cart, $category_entity_type_id, $debug;

     if($field_type != "static")
     {
        $values = '';
        foreach($attribute_values as $attribute_id => $attribute_value)
        {                                
            if($attribute_value != 'null' && $attribute_value != null)        
                $attribute_value = "'" . $cart->get_db()->clean($attribute_value) . "'";             

            $values .= "(
                        {$category_entity_type_id}, 
                        {$attribute_id}, 
                        {$store_id}, 
                        {$category_entity_id}, 
                        {$attribute_value}
                        ),";
        }

        $values = substr($values,0,-1);

        if($values != '')
        {     
            $sql = "INSERT INTO `{$cart->get_db()->get_db_prefix()}catalog_category_entity_{$field_type}` (`entity_type_id`,`attribute_id`,
                                                    `store_id`,`entity_id`,`value`) 
                                                VALUES
                                                {$values}";
            $debug->show_query("cart_insert_category", $sql);                                       
            $cart->get_db()->insert($sql);
        }
     }
}
            
function get_all_category_attribute_ids()
{
    global $category_attribute_ids, $cart, $category_entity_type_id;
        
    $attributes = $cart->get_db()->rows("select attribute_id,
                                                attribute_code,
                                                backend_type,
                                                frontend_input,
                                                source_model,
                                                is_user_defined
                                            from {$cart->get_db()->get_db_prefix()}eav_attribute where entity_type_id = {$category_entity_type_id}");        
    
    $category_attribute_ids = array();
    
    foreach($attributes as $attribute)
    {
        $category_attribute_ids[$attribute['attribute_code']] = array("attribute_id" => $attribute['attribute_id'],
                                                             "type" => $attribute['backend_type'],
                                                             "front_type" => $attribute['frontend_input'],
                                                             "source_model" => $attribute['source_model'],
                                                             "is_user_defined" => $attribute['is_user_defined']);
    }
}

//gets the attribute id for the code specified, and stores it in the global variable, so next read wont have to hit the datbase
function get_category_attr_id($attr_code)
{
    $code = "catalog_attr_id" . $attr_code;
    global $$code, $catalog_entity_type_id, $cart;
    
    //get the ids of the fields if they are not known
    if(!isset($$code) || $$code == '')
    {        
        //get the catalog entity type id
        $$code = $cart->get_db()->cell("select attribute_id from {$cart->get_db()->get_db_prefix()}eav_attribute where attribute_code = '{$attr_code}' and entity_type_id = {$catalog_entity_type_id}", 'attribute_id');        
    }
    
    return $$code;
}

?>