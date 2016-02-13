<?php

/**
 * @todo There are missing alias definitions in here to get counter point working.
 * Magento Product Load Version Lib
 * @global rdi_hook $hook_handler
 * @global rdi_helper_funcs $helper_funcs
 * @global rdi_lib $cart
 * @global attribute_id $related_attribute_id
 * @global entity_type_id $product_entity_type_id
 * @global array $attribute_ids array of attribute ids
 * @global array $exclusion_list Array of fields that have the allow update turned off
 * @global type $special_handling_list
 * @global type $debug
 * @global type $store_id
 * @global type $default_site
 * @param type $product_class_def
 * @param type $product_type
 * @param type $product_data
 * @param type $referenced_entities
 * @param int $website_idsoption_id
 * @package    Core\Load\Product\Magento\Lib
 */

/**
 * 
 * @global rdi_hook $hook_handler
 * @global type $helper_funcs
 * @global rdi_lib $cart
 * @global type $related_attribute_id
 * @global type $product_entity_type_id
 * @global rdi_lib $cart
 * @global array $attribute_ids
 * @global type $exclusion_list
 * @global type $special_handling_list
 * @global type $debug
 * @global type $store_id
 * @global type $default_site
 * @global type $keep_configurable_associations
 * @global int $pos
 * @global type $use_super_attribute_mapping
 * @global type $field_mapping
 * @global type $inserted_products
 * @global type $updated_products
 * @param type $product_class_def
 * @param type $product_type
 * @param type $product_data
 * @param type $referenced_entities
 * @param int $website_ids
 */
class magento_rdi_product_lib extends rdi_general {

    /**
     * 
     * @global rdi_hook $hook_handler
     * @global type $helper_funcs
     * @global rdi_lib $cart
     * @global type $related_attribute_id
     * @global entity_type_id $product_entity_type_id
     * @global array $attribute_ids
     * @global type $exclusion_list
     * @global type $special_handling_list
     * @global type $debug
     * @global type $store_id
     * @global type $default_site
     * @global type $keep_configurable_associations
     * @global int $pos
     * @global type $use_super_attribute_mapping
     * @global type $field_mapping
     * @global type $inserted_products
     * @global type $updated_products
     * @param type $product_class_def
     * @param type $product_type
     * @param type $product_data
     * @param type $referenced_entities
     * @param int $website_ids
     */
    public function insertUpdateProductRecord($product_class_def, $product_type, $product_data, $referenced_entities = array(), $website_ids = array(0))
    {

        global $hook_handler, $helper_funcs, $related_attribute_id, $product_entity_type_id, $attribute_ids, $exclusion_list, $special_handling_list, $store_id, $default_site, $keep_configurable_associations, $pos, $use_super_attribute_mapping, $field_mapping, $inserted_products, $updated_products;

        $hook_handler->call_hook("cart_insertUpdateProductRecord", $product_class_def, $product_data);

        //check if the product is new
        $new_product = $product_data['entity_id'] != '' ? false : true;

        //get the ids of the fields if they are not known
        if (!isset($product_entity_type_id) || $product_entity_type_id == '')
        {
            //get the catalog entity type id
            $product_entity_type_id = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
        }

        //get the attribute if of the related id if need be
        if ($related_attribute_id == '')
        {
            $related_attribute_id = $this->db_connection->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");
        }

        //get a list of the fields not allowed to update
        if (!isset($exclusion_list) || $exclusion_list == '')
        {
            //
            $exclusion_list = $this->db_connection->rows("select allow_update, cart_field from rdi_field_mapping where field_type = 'product' and (field_classification = '{$product_class_def['product_class']}' or field_classification is null) and (entity_type = '{$product_type['product_type']}' or entity_type is null)", "cart_field");
        }

        //get a list of the fields that require a special handling
        if (!isset($special_handling_list) || $special_handling_list == '')
        {
            $special_handling_list = $this->db_connection->rows("select special_handling, cart_field from rdi_field_mapping where field_type = 'product' and (field_classification = '{$product_class_def['product_class']}' or field_classification is null) and (entity_type = '{$product_type['product_type']}' or entity_type is null)", "cart_field");
        }

        if (!isset($attribute_ids) || count($attribute_ids) == 0)
        {
            $this->get_all_product_attribute_ids();
        }

        if (count($website_ids) == 0)
        {
            $website_ids[] = 0;
        }

        //the break down of the attribute data and their values for this product
        $attribute_data_sets = array();

        //build out the value array
        foreach ($attribute_ids as $attribute_code => $attribute_data)
        {
            $attribute_field = array();

            if ($attribute_code == "related_id")
                continue;

            if (!$new_product && isset($exclusion_list[$attribute_code]) && $exclusion_list[$attribute_code]['allow_update'] == 0)
            {
                continue;
            }

            //check if the product data has included this value
            if (isset($product_data[$attribute_code]) && $attribute_data['type'] != 'static')
            {
                //check the front end display type, select types need to update the option tables and pass the id now the value in
                if ($attribute_data['front_type'] == "select" && ($attribute_data['source_model'] == 'eav/entity_attribute_source_table' || $attribute_data['is_user_defined'] == 1))
                {

                    if ($product_data[$attribute_code] != '' || $product_data[$field_for_update] == NULL)
                    {
                        //special handling for the option_value
                        //check if there is special handling on this field
                        if (isset($special_handling_list[$attribute_code]) && $special_handling_list[$attribute_code]['special_handling'] != '')
                        {
                            $product_data[$attribute_code] = $helper_funcs->process_special_handling($special_handling_list[$attribute_code]['special_handling'], $product_data[$attribute_code], $product_data, 'product', $product_class_def, $product_type);
                        }


                        //create the attribute option if not exists
                        //find the attribute option value for this attribute if it already exists,
                        if ($product_data[$field_for_update] == NULL)
                        {
                            $option_id = '0';
                        }
                        else
                        {
                            //$option_id = get_attribute_option_value($field_for_update, $product_data[$field_for_update]);
                            $option_id = $this->get_attribute_option_value_set($product_data[$field_for_update], $field_for_update);
                        }

                        if (!$option_id || $option_id == '' || $option_id === '')
                        {
                            $position = 0;
                            if (isset($product_data[$attribute_code . "_sort_order"]))
                            {
                                $position = $product_data[$attribute_code . "_sort_order"];

                                //echo "<BR><BR><BR>{$position}<BR><BR><BR>";
                            }

                            //check if there is special handling on this field
                            if (isset($special_handling_list[$attribute_code]) && $special_handling_list[$attribute_code]['special_handling'] != '')
                            {
                                $product_data[$attribute_code] = $helper_funcs->process_special_handling($special_handling_list[$attribute_code]['special_handling'], $product_data[$attribute_code], $product_data, 'product', $product_class_def, $product_type);
                            }

                            $option_id = $this->add_attribute_option_value($attribute_code, $product_data[$attribute_code], $position, $product_data['entity_id']);
                        }

                        $product_data[$attribute_code] = $option_id;
                    }
                }

                //prevent 0 going in for a value when it should be nothing at all
                if ($product_data[$attribute_code] == '' && $attribute_data['type'] == 'int')
                    continue;

                if ($product_data[$attribute_code] == '')
                    $product_data[$attribute_code] = 'null';

                //check if there is special handling on this field
                if (isset($special_handling_list[$attribute_code]) && $special_handling_list[$attribute_code]['special_handling'] != '')
                {
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
        if (!$new_product)
        {
            //update
            $this->update_product_entity($product_data['entity_id'], $product_class_def['product_class_id'], $product_type, $product_data['sku']);

            //update the product stock entity
            $this->update_product_stock_entity($product_data['entity_id'], $product_data, $product_type);
        }
        else
        {
            //insert
            $product_data['entity_id'] = $this->insert_product_entity($product_class_def['product_class_id'], $product_type, $product_data['sku']);

            //PMB 08112015
            $inserted_products[] = $product_data['entity_id'];

            //insert the product_stock_entity();
            $this->insert_product_stock_entity($product_data['entity_id'], $product_data);
        }

        foreach ($attribute_data_sets as $type => $attribute_data_set)
        {
            if (!$new_product)
            {
                //update the attribute fields
                $this->update_product_attribute_field_table($product_data['entity_id'], $type, $website_ids[0], $attribute_data_set, $attribute_data['attribute_code']);
            }
            else
            {
                //insert the attribute fields
                $this->insert_product_attribute_field_table($product_data['entity_id'], $type, $website_ids[0], $attribute_data_set);
            }
        }

        unset($attribute_data_sets);

        if ($product_type['product_type'] == "configurable")
        {

            if (is_array($product_class_def['field_data']))
            {
                $super_attributes = array();

                if (isset($use_super_attribute_mapping) && $use_super_attribute_mapping == 1)
                {
                    $super_attributes = $field_mapping->attribute_mapping_to_field_data($product_data['style_id']);
                }
                else
                {
                    $super_attributes = $product_class_def['field_data'];
                }

                if (!empty($super_attributes))
                {
                    //tell the configurable that it is using these types of values for its options
                    foreach ($super_attributes as $attr)
                    {
                        if (isset($attribute_ids[$attr['cart_field']]['attribute_id']))
                        {
                            $product_super_attribute_id = $this->db_connection->insert("REPLACE INTO `{$this->prefix}catalog_product_super_attribute` (`product_id`, `attribute_id`, `position`)
											VALUES ({$product_data['entity_id']}, {$attribute_ids[$attr['cart_field']]['attribute_id']}, {$attr['position']})");

                            $attribute_label = $attr['label'] == null || $attr['label'] == '' ? $attr['cart_field'] : $attr['label'];

                            $this->db_connection->insert("REPLACE INTO `{$this->prefix}catalog_product_super_attribute_label` (`product_super_attribute_id`, `store_id`, `use_default`, `value`)
											VALUES ({$product_super_attribute_id}, {$store_id}, 0, '{$attribute_label}')");
                        }
                    }
                }
            }

            //$this->_echo("keep_configurable_associations: {$keep_configurable_associations}");
            if (isset($keep_configurable_associations) && $keep_configurable_associations == 1)
            {

                $this->set_product_associations($referenced_entities, $product_data['entity_id'], false);
            }
            else
            {
                $this->set_product_associations($referenced_entities, $product_data['entity_id']);
            }
        }
        else if ($product_type['product_type'] == "grouped")
        {
            $referenced_entities = $pos->get_processor("rdi_pos_product_load")->get_related_item_ids($product_data['related_id'], $parameters);
            //doesnt use the super link so dont use this
            //set_product_associations($referenced_entities, $product_data['entity_id']);
            //get the link type ids
            $super_id = $this->db_connection->cell("Select link_type_id from {$this->prefix}catalog_product_link_type where code = 'super'", 'link_type_id');

            $pos_id = $this->db_connection->cell("select product_link_attribute_id from {$this->prefix}catalog_product_link_attribute where product_link_attribute_code = 'position' and link_type_id = {$super_id}", 'product_link_attribute_id');

            $position = 0;
            //insert the data into the link table
            foreach ($referenced_entities as $p)
            {
                //insert the new relation
                $link_id = $this->db_connection->insert("insert ignore into {$this->prefix}catalog_product_link(product_id, linked_product_id, link_type_id)
                    values({$product_data['entity_id']}, {$p['entity_id']}, {$super_id})");

                $this->db_connection->insert("insert ignore into {$this->prefix}catalog_product_link_attribute_int (link_id, product_link_attribute_id, value)
                values ({$link_id}, {$pos_id}, {$position})");

                $position++;
            }
        }
        else
        {
            //check if the configurable exists for this item yet, if it doesnt then this is not an addition, should be a standard insert
            /*
              $sql = "SELECT {$this->prefix}catalog_product_entity.entity_id from {$this->prefix}catalog_product_entity inner join {$this->prefix}catalog_product_entity_varchar on {$this->prefix}catalog_product_entity_varchar.entity_id = {$this->prefix}catalog_product_entity.entity_id and type_id = 'configurable' where attribute_id = {$related_attribute_id} and value = '{$product_data['style_id']}'";

              $configurable_id = $this->db_connection->cell($sql, "entity_id");

              if(isset($configurable_id) && $configurable_id != '' && $configurable_id != null)
              {
              set_product_associations(array(0 => array("entity_id" => $product_data['entity_id'])), $configurable_id, false);
              } */
        }

        unset($referenced_entities);

        $product_data['attribute_set_id'] = $product_class_def['product_class_id'];
        $product_data['type_id'] = $product_type['product_type'];

        //create_indexed_record($product_data);
        //enable to do custom url rewrite
        //create_core_url_rewrite_record($product_data, $website_ids[0]);

        if (isset($default_site) && $default_site != '')
        {
            $sites_used = explode(',', $default_site);

            if (is_array($sites_used))
            {
                foreach ($sites_used as $website_id)
                {
                    //assign the product to the default site
                    $this->db_connection->exec("replace into {$this->prefix}catalog_product_website (product_id, website_id) values({$product_data['entity_id']}, {$website_id})");
                }
            }
        }
        else
        {
            //assign the product to the default site
            $this->db_connection->exec("replace into {$this->prefix}catalog_product_website (product_id, website_id) values({$product_data['entity_id']}, 1)");
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

    public function magento_update_field($product_data, $field_for_update, $product_type, $product_class_def)
    {
        global $helper_funcs, $cart, $product_entity_type_id, $cart, $attribute_ids, $exclusion_list, $special_handling_list;

        //This is pretty standard boiler plate here get the data needed
        //-----------------------------
        //get the ids of the fields if they are not known
        if (!isset($product_entity_type_id) || $product_entity_type_id == '')
        {
            //get the catalog entity type id
            $product_entity_type_id = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
        }

        //get a list of the fields not allowed to update
        if (!isset($exclusion_list) || $exclusion_list == '')
        {
            //
            $exclusion_list = $this->db_connection->rows("select allow_update, cart_field from rdi_field_mapping where field_type = 'product' and (field_classification = '{$product_class_def['product_class_id']}' or field_classification is null) and (entity_type = '{$product_type['product_type']}' or entity_type is null)", "cart_field");
        }

        //get a list of the fields that require a special handling
        if (!isset($special_handling_list) || $special_handling_list == '')
        {
            $special_handling_list = $this->db_connection->rows("select special_handling, cart_field from rdi_field_mapping where field_type = 'product' and (field_classification = '{$product_class_def['product_class_id']}' or field_classification is null) and (entity_type = '{$product_type['product_type']}' or entity_type is null)", "cart_field");
        }

        //@todo need to store attribute_ids.
        if (!isset($attribute_ids) || count($attribute_ids) == 0)
        {
            $this->get_all_product_attribute_ids();
        }

        $website_ids[] = 0;

        //-----------------------
        //$attribute_ids contains more info about this field, see if its in there
        //cant do this, will break updating a field to a null value
        //make sure the product has this field
        //if( isset($product_data[$field_for_update]) )
        //{
        if ($field_for_update == 'avail')
        {
            //avail is a special update, its the sell always or etc, it isnt a direct map so has to be handled special
            $this->magento_update_field($product_data, 'manage_stock', $product_type, $product_class_def);
            $this->magento_update_field($product_data, 'use_config_manage_stock', $product_type, $product_class_def);
            $this->magento_update_field($product_data, 'is_in_stock', $product_type, $product_class_def);
            $this->magento_update_field($product_data, 'use_config_manage_backorders', $product_type, $product_class_def);
            $this->magento_update_field($product_data, 'backorders', $product_type, $product_class_def);
            if (isset($product_data['qty']))
            {
                $this->magento_update_field($product_data, 'qty', $product_type, $product_class_def);
            }

            //if this is a configurable product then we need to update all its associated
            //if()
        }
        else if (isset($attribute_ids[$field_for_update]))
        {
            $attribute_data = $attribute_ids[$field_for_update];

            //check the front end display type, select types need to update the option tables and pass the id now the value in
            if ($attribute_data['front_type'] == "select" && ($attribute_data['source_model'] == 'eav/entity_attribute_source_table' || $attribute_data['is_user_defined'] == 1))
            {
                if (isset($product_data[$field_for_update]))
                {
                    //check if there is special handling on this field
                    if (isset($special_handling_list[$field_for_update]) && $special_handling_list[$field_for_update]['special_handling'] != '')
                    {
                        $product_data[$field_for_update] = $helper_funcs->process_special_handling($special_handling_list[$field_for_update]['special_handling'], $product_data[$field_for_update], $product_data, 'product', $product_class_def, $product_type);
                    }

                    //create the attribute option if not exists
                    //find the attribute option value for this attribute if it already exists,
                    //$option_id = get_attribute_option_value($field_for_update, $product_data[$field_for_update]);
                    $option_id = $this->get_attribute_option_value_set($product_data[$field_for_update], $field_for_update);

                    if (!$option_id || $option_id == '' || $option_id === '')
                    {
                        $position = 0;
                        if (isset($product_data[$field_for_update . "_sort_order"]))
                        {
                            $position = $product_data[$field_for_update . "_sort_order"];

                            //echo "<BR><BR><BR>{$position}<BR><BR><BR>";
                        }

                        //if not we have to add it
                        $option_id = $this->add_attribute_option_value($field_for_update, $product_data[$field_for_update], $position, $product_data['entity_id']);
                    }

                    $product_data[$field_for_update] = $option_id;
                }
                else
                {
                    $product_data[$field_for_update] = 'null';
                }
            }

            //prevent 0 going in for a value when it should be nothing at all
            if ($product_data[$field_for_update] == '' && $attribute_data['type'] == 'int')
                return;

            if ($product_data[$field_for_update] == '')
                $product_data[$field_for_update] = 'null';

            //check if there is special handling on this field
            if (isset($special_handling_list[$field_for_update]) && $special_handling_list[$field_for_update]['special_handling'] != '')
            {
                $product_data[$field_for_update] = $helper_funcs->process_special_handling($special_handling_list[$field_for_update]['special_handling'], $product_data[$field_for_update], $product_data, 'product', $product_class_def, $product_type);
            }

            //set the field we will send for processing
            //add it to the broke down sets
            ///$attribute_data_sets[$attribute_data['type']][$attribute_data['attribute_id']] = $product_data[$attribute_code];

            $this->update_product_attribute_field_table($product_data['entity_id'], $attribute_data['type'], $website_ids[0], array($attribute_data['attribute_id'] => $product_data[$field_for_update]), $attribute_data['attribute_code']);
        }
        else if (in_array($field_for_update, array("qty", "min_qty", "use_config_min_qty", "is_qty_decimal", "use_config_backorders", "use_config_backorders", "backorders", "use_config_min_sale_qty", "use_config_max_sale_qty",
                    "is_in_stock", "low_stock_date", "use_config_notify_stock_qty", "use_config_manage_stock", "stock_status_changed_auto", "use_config_qty_increments",
                    "use_config_enable_qty_inc", "manage_stock")))
        {
            //stock update
            $this->update_product_stock_entity_field($product_data, $field_for_update);
        }
        //}
    }

//works but not needed, keeping for possible use later?
    public function create_core_url_rewrite_record($product_data, $store_id)
    {
        //get the unicity of the url_path
        $id_path = $this->db_connection->cell("Select id_path from {$this->prefix}core_url_rewrite where request_path = '{$product_data['url_path']}' and store_id = {$store_id}", "id_path");

        $idx = 0;

        do
        {
            //check to see if this url path is already used for this store
            if ($id_path !== false)
            {
                //if it is is this just the same product we are dealing with
                if ($id_path == "product/{$product_data['entity_id']}")
                {
                    return;
                }
                else
                {
                    //otherwise we need to fix this url
                    $i = pathinfo($product_data['url_path']);

                    $product_data['url_path'] = $i['filename'] . "_" . $idx . "." . $i['extension'];
                }

                $id_path = $this->db_connection->cell("Select id_path from {$this->prefix}core_url_rewrite where request_path = '{$product_data['url_path']}' and store_id = {$store_id}", "id_path");
            }

            $idx++;
        } while ($id_path !== false);

        $this->db_connection->exec("insert INTO {$this->prefix}core_url_rewrite (store_id, id_path, request_path, target_path, is_system, product_id)
                                    values
                                         (
                                            {$store_id},
                                            'product/{$product_data['entity_id']}',
                                            '{$product_data['url_path']}',
                                            'catalog/product/view/id/{$product_data['entity_id']}',
                                            1,
                                            {$product_data['entity_id']}
                                         ) on duplicate key update request_path = '{$product_data['url_path']}'");
    }

//works but not needed, keeping for possible use later?
    public function create_indexed_record($product_data)
    {
        //find the columns from the index table
        $columns = $this->db_connection->rows("SHOW COLUMNS FROM {$this->prefix}catalog_product_flat_1");

        $fields = '';
        $values = '';

        foreach ($columns as $column)
        {
            if (isset($product_data[$column['Field']]))
            {
                $fields .= $column['Field'] . ",";
                $values .= "'{$product_data[$column['Field']]}',";
            }
        }

        $fields = substr($fields, 0, -1);
        $values = substr($values, 0, -1);

        $this->db_connection->exec("replace INTO {$this->prefix}catalog_product_flat_1 ({$fields}) values ({$values})");
    }

//
    /**
     * sets the product associations used for configurable products
     * @depricated link_products in the product library handles all of this.
     * @global type $product_associations_updated
     * @param type $referenced_entities
     * @param type $product_entity_id
     * @param type $clear_associations
     * @return boolean|null
     */
    public function set_product_associations($referenced_entities, $product_entity_id, $clear_associations = true)
    {
        return null;
        global $product_associations_updated;

        if (!isset($product_associations_updated))
        {
            $product_associations_updated = array();
        }

        if (!in_array($product_entity_id, $product_associations_updated))
        {
            $product_associations_updated[] = $product_entity_id;
        }
        else
        {
            return false;
        }

        if (is_array($referenced_entities))
        {
            $refs = '';
            foreach ($referenced_entities as $entity)
            {
                if ($entity['entity_id'] != '')
                {
                    $refs .= "({$entity['entity_id']}, {$product_entity_id}),";
                }
            }

            $refs = substr($refs, 0, -1);

            if ($refs != '')
            {
                if ($clear_associations)
                {
                    //clear out any existing relations
                    $this->db_connection->insert("delete from `{$this->prefix}catalog_product_super_link` where `parent_id` = {$product_entity_id}");

                    $this->db_connection->insert("delete from `{$this->prefix}catalog_product_relation` where `parent_id` = {$product_entity_id}");
                }

                //set the relations for the configurables
                $this->db_connection->insert("REPLACE INTO `{$this->prefix}catalog_product_super_link` (`product_id`,`parent_id`) VALUES {$refs}");

                $this->db_connection->insert("REPLACE INTO `{$this->prefix}catalog_product_relation` (`child_id`, `parent_id`) VALUES {$refs}");
            }

            unset($refs);
        }
    }

    public function update_product_entity($product_entity_id, $attribute_set_id, $product_type, $sku)
    {
        $sku = "'" . $this->db_connection->clean($sku) . "'";
    }

    public function insert_product_entity($attribute_set_id, $product_type, $sku)
    {
        global $product_entity_type_id;

        $sku = "'" . $this->db_connection->clean($sku) . "'";

        //insert the product entity record
        return $this->db_connection->insert("INSERT INTO `{$this->prefix}catalog_product_entity`
                                (`entity_type_id`, `attribute_set_id`, `type_id`,
                                `sku`, `created_at`, `updated_at`)
                                VALUES (
                                        {$product_entity_type_id},
                                        {$attribute_set_id},
                                        '{$product_type['product_type']}',
                                        {$sku},
                                        now(),
                                        now()
                                        );");
    }

    public function update_product_stock_entity($product_entity_id, $product_data, $product_type)
    {
        global $use_config_enable_qty_inc, $stock_status_changed_auto, $default_stock_id, $updated_stock;

        //have to set the stock status properly based on the product having stock
        //if its a configurable have to sum up the associated and use that value for the comparison

        if (isset($product_data['qty']))
        {
            $product_stock = $product_data['qty'];
            $min_qty = 0;


            if (isset($product_data['min_qty']))
            {
                $min_qty = $product_data['min_qty'];
            }

            if ($product_stock < $min_qty)
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

        $updated_stock[] = $product_entity_id;
        
        $this->db_connection->exec("REPLACE INTO {$this->prefix}cataloginventory_stock_item (qty,
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
                                                      {$stock_status_changed_auto},
                                                      use_config_qty_increments,
                                                      {$use_config_enable_qty_inc},
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
                                                      " . (!isset($default_stock_id) ? '1' : $default_stock_id) . ",
                                                      " . (!isset($product_data['manage_stock']) ? '1' : $product_data['manage_stock']) . "
                                                     )");
    }

    public function update_product_stock_entity_field($product_data, $field)
    {
        global $default_stock_id, $updated_stock;

        if (isset($product_data[$field]))
        {
            $updated_stock[] = $product_data['entity_id'];
            
            $this->db_connection->exec("update `{$this->prefix}cataloginventory_stock_item` set
                                                        {$field} = '{$product_data[$field]}'
                                                     where
                                                        `product_id` = {$product_data['entity_id']} and stock_id = {$default_stock_id}");
        }
    }

    /**
     * @depricated A new library does related products.
     * @global type $product_link_type
     * @param type $upsell_data
     */
    public function process_link_insert($upsell_data)
    {
        global $product_link_type;

        if (is_array($upsell_data) && count($upsell_data) > 0)
        {


            $position_link_attribute_id = $this->db_connection->cell("SELECT product_link_attribute_id FROM {$this->prefix}catalog_product_link_attribute pla
                                                                    JOIN {$this->prefix}catalog_product_link_type plt
                                                                    ON plt.link_type_id = pla.link_type_id
                                                                    AND plt.code = '{$product_link_type}'
                                                                    WHERE pla.product_link_attribute_code = 'position'
                                                                    ", "product_link_attribute_id");

            foreach ($upsell_data as $p)
            {
                $link_id = $this->db_connection->insert("insert into {$this->prefix}catalog_product_link(product_id, linked_product_id, link_type_id)
                    values({$p['product_id']}, {$p['linked_product_id']}, {$p['link_type_id']})");

                $this->db_connection->insert("insert into {$this->prefix}catalog_product_link_attribute_int (link_id, product_link_attribute_id, value)
                values ({$link_id}, {$position_link_attribute_id}, {$p['position']})");
            }
        }

        unset($upsell_data);
    }

    public function set_visibility_status_array($product_array)
    {
        global $hide_out_of_stock, $disable_out_of_stock, $store_id;

        #get visibility and status attribute
        $product_entity_type_id = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
        $visibility_attribute_id = $this->db_connection->cell("Select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'visibility' AND entity_type_id = {$product_entity_type_id}", "attribute_id");
        $status_attribute_id = $this->db_connection->cell("Select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'status' AND entity_type_id = {$product_entity_type_id}", "attribute_id");
        $deactivated_date_attribute_id = $this->db_connection->cell("Select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'rdi_deactivated_date' AND entity_type_id = {$product_entity_type_id}", "attribute_id");

        if (is_array($product_array))
        {
            foreach ($product_array as $row)
            {
                #temparily set rdi_deactivated_date to NULL
                $this->db_connection->exec("INSERT IGNORE INTO {$this->prefix}catalog_product_entity_datetime (entity_type_id, attribute_id, store_id, entity_id, value) values({$product_entity_type_id}, {$deactivated_date_attribute_id}, " . (isset($store_id) ? $store_id : 0) . ", {$row['product_id']}, '3000-12-31 14:29:14')");

                #out of stock
                if ((($row['qty'] < $row['min_qty']) || $row['qty'] == 0) && $row['manage_stock'] == 1)
                {
                    #update visibility
                    if (isset($hide_out_of_stock) && $hide_out_of_stock == 1)
                    {
                        $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_int SET {$this->prefix}catalog_product_entity_int.value = 1
                    WHERE
                            {$this->prefix}catalog_product_entity_int.entity_id = {$row['product_id']}
                    AND
                            {$this->prefix}catalog_product_entity_int.attribute_id = {$visibility_attribute_id}");
                    }
                    #update enabled?
                    if (isset($disable_out_of_stock) && $disable_out_of_stock == 1)
                    {
                        $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_int SET {$this->prefix}catalog_product_entity_int.value = 2
                    WHERE
                            {$this->prefix}catalog_product_entity_int.entity_id = {$row['product_id']}
                    AND
                            {$this->prefix}catalog_product_entity_int.attribute_id = {$status_attribute_id}");
                    }
                }
                else if (($row['qty'] > $row['min_qty']) && $row['manage_stock'] == 1)
                {
                    #update visibility
                    if (isset($hide_out_of_stock) && $hide_out_of_stock == 1)
                    {
                        $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_int SET {$this->prefix}catalog_product_entity_int.value = 4
                    WHERE
                            {$this->prefix}catalog_product_entity_int.entity_id = {$row['product_id']}
                    AND
                            {$this->prefix}catalog_product_entity_int.attribute_id = {$visibility_attribute_id}");
                    }
                    #update enabled?
                    if (isset($disable_out_of_stock) && $disable_out_of_stock == 1)
                    {
                        $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_int SET {$this->prefix}catalog_product_entity_int.value = 1
                    WHERE
                            {$this->prefix}catalog_product_entity_int.entity_id = {$row['product_id']}
                    AND
                            {$this->prefix}catalog_product_entity_int.attribute_id = {$status_attribute_id}");
                    }
                }
                else
                {
                    #update visibility
                    if (isset($hide_out_of_stock) && $hide_out_of_stock == 1)
                    {
                        $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_int SET {$this->prefix}catalog_product_entity_int.value = 4
                    WHERE
                            {$this->prefix}catalog_product_entity_int.entity_id = {$row['product_id']}
                    AND
                            {$this->prefix}catalog_product_entity_int.attribute_id = {$visibility_attribute_id}");
                    }
                    #update enabled?
                    if (isset($disable_out_of_stock) && $disable_out_of_stock == 1)
                    {
                        $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_int SET {$this->prefix}catalog_product_entity_int.value = 1
                    WHERE
                            {$this->prefix}catalog_product_entity_int.entity_id = {$row['product_id']}
                    AND
                            {$this->prefix}catalog_product_entity_int.attribute_id = {$status_attribute_id}");
                    }
                }
            }
        }
    }

    public function process_out_of_stock()
    {
        global $hide_out_of_stock, $disable_out_of_stock, $field_mapping, $pos, $default_stock_id;

        $updated = false;

        //make out of stock not visible, or in stock visible
        if ((isset($hide_out_of_stock) && $hide_out_of_stock == 1) || (isset($disable_out_of_stock) && $disable_out_of_stock == 1))
        {
            $product_entity_type_id = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
            //get the visibility attribute id
            $related_parent_attribute_id = $this->db_connection->cell("Select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_parent_id'  AND entity_type_id = {$product_entity_type_id}", "attribute_id");
            $related_attribute_id = $this->db_connection->cell("Select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_id'  AND entity_type_id = {$product_entity_type_id}", "attribute_id");
            $visibility_attribute_id = $this->db_connection->cell("Select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'visibility'  AND entity_type_id = {$product_entity_type_id}", "attribute_id");
            $status_attribute_id = $this->db_connection->cell("Select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'status'  AND entity_type_id = {$product_entity_type_id}", "attribute_id");

            $configurable_related_id_mapping = $field_mapping->map_field('product', 'related_id', 'configurable');

            if (isset($configurable_related_id_mapping) && $configurable_related_id_mapping !== '')
            {
                $rows = $this->db_connection->rows("SELECT DISTINCT 1 AS qty,parent_id product_id, 0 as min_qty, manage_stock FROM {$this->prefix}catalog_product_super_link
                        INNER JOIN {$this->prefix}cataloginventory_stock_item
                        ON {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_super_link.product_id
						AND stock_id = {$default_stock_id}
                        INNER JOIN {$this->prefix}catalog_product_entity_varchar related_id
                        ON related_id.entity_id = {$this->prefix}catalog_product_super_link.parent_id
                        AND related_id.attribute_id = {$related_attribute_id}
                        INNER JOIN " . $pos->get_processor("rdi_staging_db_lib")->get_table_name('in_items') . " style on " .
                        $field_mapping->map_field('product', 'related_id', 'configurable') . " = related_id.value
                        GROUP BY {$this->prefix}catalog_product_super_link.parent_id
						HAVING SUM({$this->prefix}cataloginventory_stock_item.qty > {$this->prefix}cataloginventory_stock_item.min_qty) > 0
						");

                if (!empty($rows))
                {
                    $updated = true;
                }

                $this->set_visibility_status_array($rows);

                $rows = $this->db_connection->rows("SELECT DISTINCT 0 AS qty,parent_id product_id, 0 as min_qty, manage_stock FROM {$this->prefix}catalog_product_super_link
                        INNER JOIN {$this->prefix}cataloginventory_stock_item
                        ON {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_super_link.product_id
						AND stock_id = {$default_stock_id}
                        INNER JOIN {$this->prefix}catalog_product_entity_varchar related_id
                        ON related_id.entity_id = {$this->prefix}catalog_product_super_link.parent_id
                        AND related_id.attribute_id = {$related_attribute_id}
                        INNER JOIN " . $pos->get_processor("rdi_staging_db_lib")->get_table_name('in_items') . " style on " .
                        $field_mapping->map_field('product', 'related_id', 'configurable') . " = related_id.value
                        GROUP BY {$this->prefix}catalog_product_super_link.parent_id
						HAVING SUM({$this->prefix}cataloginventory_stock_item.qty > {$this->prefix}cataloginventory_stock_item.min_qty) = 0
						");

                if (!empty($rows))
                {
                    $updated = true;
                }

                $this->set_visibility_status_array($rows);
            }

            $rows = $this->db_connection->rows("SELECT DISTINCT qty AS qty,{$this->prefix}catalog_product_entity.entity_id product_id,min_qty, manage_stock FROM {$this->prefix}catalog_product_entity
                                LEFT JOIN {$this->prefix}catalog_product_super_link
                                    ON {$this->prefix}catalog_product_entity.entity_id = {$this->prefix}catalog_product_super_link.product_id
                                    AND {$this->prefix}catalog_product_entity.type_id = 'simple'
                                INNER JOIN {$this->prefix}cataloginventory_stock_item
                                    ON {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_entity.entity_id
									AND {$this->prefix}cataloginventory_stock_item.stock_id = {$default_stock_id}
                                INNER JOIN {$this->prefix}catalog_product_entity_varchar related_id
                                    ON related_id.entity_id = {$this->prefix}catalog_product_entity.entity_id
                                    AND related_id.attribute_id = {$related_attribute_id}
                                INNER JOIN " . $pos->get_processor("rdi_staging_db_lib")->get_table_name('in_items') . " item on " .
                    $field_mapping->map_field('product', 'related_id', 'simple') . " = related_id.value
                                WHERE {$this->prefix}catalog_product_super_link.product_id IS NULL
                                    AND {$this->prefix}catalog_product_entity.type_id = 'simple';");

            if (!empty($rows))
            {
                $updated = true;
            }

            $this->set_visibility_status_array($rows);
        }

        return $updated;
    }

    public function insert_product_stock_entity($product_entity_id, $product_data)
    {
        global $use_config_enable_qty_inc, $stock_status_changed_auto, $default_stock_id;

        $this->db_connection->exec("INSERT INTO `{$this->prefix}cataloginventory_stock_item` (`product_id`, `stock_id`,
                                                `qty`, `min_qty`, `use_config_min_qty`, `is_qty_decimal`, `use_config_backorders`,
                                                `use_config_min_sale_qty`, `use_config_max_sale_qty`, `is_in_stock`,
                                                `low_stock_date`, `use_config_notify_stock_qty`, `use_config_manage_stock`,
                                                `{$stock_status_changed_auto}`, `use_config_qty_increments`, `{$use_config_enable_qty_inc}`, manage_stock)
                                             VALUES
                                             (
                                                {$product_entity_id},
                                                " . (!isset($default_stock_id) ? '1' : $default_stock_id) . ",
                                                " . (isset($product_data['qty']) && $product_data['qty'] !== '' ? $product_data['qty'] : '0') . ",
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
                                              );");
    }

//generate an update request and run it for the specified data
//attribute values will be code => value array
    public function update_product_attribute_field_table($product_entity_id, $field_type, $store_id, $attribute_values, $attribute_code = '')
    {
        global $product_entity_type_id, $index_one_at_a_time, $attribute_ids, $updated_products;

        $updated_products[] = $product_entity_id;
        //there is never a static table so always need to skip that
        if ($field_type != "static")
        {
            $values = '';
            foreach ($attribute_values as $attribute_id => $attribute_value)
            {
                if ($attribute_value != 'null' && $attribute_value != null)
                    $attribute_value = "'" . $this->db_connection->clean($attribute_value) . "'";

                if ($attribute_value == '')
                    $attribute_value = 'null';

                //$sql = "update `catalog_product_entity_{$field_type}` set value = {$attribute_value} where entity_type_id = {$product_entity_type_id}
                //        and attribute_id = {$attribute_id} and store_id = {$store_id} and entity_id = {$product_entity_id}";

                $this->db_connection->exec("INSERT into `{$this->prefix}catalog_product_entity_{$field_type}` (entity_type_id, attribute_id, store_id, entity_id, value)
                        values(
                                {$product_entity_type_id},
                                {$attribute_id},
                                {$store_id},
                                {$product_entity_id},
                                {$attribute_value}
                            ) ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
            }
        }
        else if ($attribute_code !== '')
        {
            foreach ($attribute_values as $attribute_id => $attribute_value)
            {
                if ($attribute_value != 'null' && $attribute_value != null)
                    $attribute_value = "'" . $this->db_connection->clean($attribute_value) . "'";

                if ($attribute_value == '')
                    $attribute_value = 'null';

                $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity SET {$attribute_code} = {$attribute_value} WHERE entity_id = {$product_entity_id}");
            }
        }
    }

//generate an insert request and run it for the specified data
//attribute values will be code => value array
    public function insert_product_attribute_field_table($product_entity_id, $field_type, $store_id, $attribute_values)
    {
        global $product_entity_type_id;

        if ($field_type != "static")
        {
            $values = '';
            foreach ($attribute_values as $attribute_id => $attribute_value)
            {
                if ($attribute_value != 'null' && $attribute_value != null && !strstr($attribute_value, 'NOW()'))
                    $attribute_value = "'" . $this->db_connection->clean($attribute_value) . "'";

                if ($attribute_value == '')
                    $attribute_value = 'null';

                $values .= "(
                        {$product_entity_type_id},
                        {$attribute_id},
                        {$store_id},
                        {$product_entity_id},
                        {$attribute_value}
                        ),";
            }

            $values = substr($values, 0, -1);

            if ($values != '')
            {
                $this->db_connection->insert("INSERT INTO `{$this->prefix}catalog_product_entity_{$field_type}` (`entity_type_id`,`attribute_id`,
                                                    `store_id`,`entity_id`,`value`)
                                                VALUES
                                                {$values} ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
            }

            unset($values);
        }
    }

    public function get_all_product_attribute_ids()
    {
        global $attribute_ids, $product_entity_type_id;

        $attributes = $this->db_connection->rows("select attribute_id,
                                                attribute_code,
                                                backend_type,
                                                frontend_input,
                                                source_model,
                                                is_user_defined
                                            from {$this->prefix}eav_attribute where entity_type_id = {$product_entity_type_id}");

        $attribute_ids = array();

        foreach ($attributes as $attribute)
        {
            $attribute_ids[$attribute['attribute_code']] = array("attribute_id" => $attribute['attribute_id'],
                "type" => $attribute['backend_type'],
                "front_type" => $attribute['frontend_input'],
                "source_model" => $attribute['source_model'],
                "attribute_code" => $attribute['attribute_code'],
                "is_user_defined" => $attribute['is_user_defined']);
        }
    }

    /**
     * @depricated
     * gets the attribute id for the code specified, and stores it in the global variable, so next read wont have to hit the datbase
     * @global string $code
     * @global entity_type_id $product_entity_type_id
     * @global rdi_lib $cart
     * @param type $attr_code
     * @return type
     */
    public function get_product_attr_id($attr_code)
    {
        $code = "product_attr_id" . $attr_code;
        global $$code, $product_entity_type_id;

        //get the ids of the fields if they are not known
        if (!isset($$code) || $$code == '')
        {
            //get the catalog entity type id
            $$code = $this->db_connection->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = '{$attr_code}' and entity_type_id = {$product_entity_type_id}", 'attribute_id');
        }

        return $$code;
    }

    /**
     * 
     * @global rdi_lib $cart
     * @global type $store_id
     * @global rdi_hook $hook_handler
     * @global array $attribute_ids
     * @param type $attribute_code
     * @param string $attribute_value
     * @return boolean
     */
    public function get_attribute_option_value($attribute_code, $attribute_value)
    {
        global $store_id, $attribute_ids;

        $attribute_value = "'" . $this->db_connection->clean($attribute_value) . "'";

        if ($attribute_code !== "''")
        {
            $option_id = $this->db_connection->cell("select {$this->prefix}eav_attribute_option.option_id from {$this->prefix}eav_attribute_option
                                    inner join {$this->prefix}eav_attribute_option_value on {$this->prefix}eav_attribute_option_value.option_id = {$this->prefix}eav_attribute_option.option_id
                                    inner join {$this->prefix}eav_attribute on {$this->prefix}eav_attribute.attribute_id = {$this->prefix}eav_attribute_option.attribute_id and {$this->prefix}eav_attribute.attribute_code = '{$attribute_code}'
                                    where {$this->prefix}eav_attribute_option_value.value = {$attribute_value} and {$this->prefix}eav_attribute_option_value.store_id = {$store_id}", "option_id");
        }
        if ($option_id != '')
            return $option_id;

        return false;
    }

    /*
     * We got to this public function  either
     * 1. the product is new and there is no product using this option yet.
     * 2. The product exists and it needs to change its attribute value
     *      a. We need to check if any other product is using this value.
     *              if so we need to make a new option
     *                  if the other products need this option_id, they will get it when they come through again to be processed.
     *      b. If no others are using it, then we will update the value on the eaov table.
     */

    public function add_attribute_option_value($attribute_code, $attribute_value, $position, $product_id = '')
    {
        global $attribute_ids, $store_id, $attribute_option_values_storage;

        if (!isset($product_id) || $product_id == null)
            $product_id = '';

        if ($position == '')
            $position = 0;

        $attribute_id = $attribute_ids[$attribute_code]['attribute_id'];

        if ($product_id != '')
        {
            //check the current usage for this option.
            $option_id = $this->db_connection->cell("select value from {$this->prefix}catalog_product_entity_int where attribute_id = {$attribute_ids[$attribute_code]['attribute_id']} and entity_id = {$product_id}", "value");

            //save this.
            $original_option_id = $option_id;


            //If there is an option already there
            if (isset($option_id) && $option_id !== '' && $option_id !== '0')
            {
                $check_option_id_usage = $this->check_attribute_option_usage($attribute_code, $option_id, $store_id);
            }
            //else there is not option set for this product
            else
            {
                $check_option_id_usage = 2;
            }
        }

        //does the new option_value exist?
        $option_id = $this->get_attribute_option_value_set($attribute_value, $attribute_code, $store_id);
        /*
          $this->_var_dump($option_id);
          $this->_var_dump($check_option_id_usage);
         */
        //if we dont have the product, for some reason. OR the check says more than one product has this option.
        //else we have sole ownership and the new option exists
        /* $this->_var_dump($check_option_id_usage !== '1');
          $this->_var_dump($check_option_id_usage !== '-1');
          $this->_var_dump($check_option_id_usage == '1' && is_numeric($option_id));
          $this->_var_dump($check_option_id_usage == '-1' && is_numeric($original_option_id)); */

        if (!isset($check_option_id_usage) || $check_option_id_usage !== '1' && $check_option_id_usage !== '-1')
        {
            if (!$option_id)
            {

                $option_id = $this->db_connection->insert("INSERT INTO `{$this->prefix}eav_attribute_option` (`attribute_id`, `sort_order`) VALUES ({$attribute_ids[$attribute_code]['attribute_id']}, {$position})");

                //update our storage.
                $attribute_option_values_storage[$attribute_id][$attribute_value] = $option_id;

                $attribute_value_insert = "'" . $this->db_connection->clean($attribute_value) . "'";

                $value_id = $this->db_connection->insert("INSERT INTO `{$this->prefix}eav_attribute_option_value` (`option_id`, `store_id`, `value`) VALUES ({$option_id}, {$store_id}, {$attribute_value_insert})");
            }

            if ($option_id)
            {

                return $option_id;
            }
        }
        elseif ($check_option_id_usage == '-1' && is_numeric($original_option_id))
        {
            $option_id = $this->db_connection->insert("INSERT INTO `{$this->prefix}eav_attribute_option` (`option_id`,`attribute_id`, `sort_order`) VALUES ({$original_option_id},{$attribute_ids[$attribute_code]['attribute_id']}, {$position}) ON DUPLICATE KEY UPDATE sort_order = values(`sort_order`)");

            //update our storage.
            $attribute_option_values_storage[$attribute_id][$attribute_value] = $original_option_id;

            $attribute_value_insert = "'" . $this->db_connection->clean($attribute_value) . "'";

            $value_id = $this->db_connection->insert("INSERT INTO `{$this->prefix}eav_attribute_option_value` (`option_id`, `store_id`, `value`) VALUES ({$original_option_id}, {$store_id}, {$attribute_value_insert})");

            if ($original_option_id)
            {

                return $original_option_id;
            }
        }
        elseif ($check_option_id_usage == '1' && is_numeric($option_id))
        {
            return $option_id;
        }
        else
        {
            //We want to update if the product is the only one using the option and the other option does not exist.
            
            $attribute_value_insert = "'" . $this->db_connection->clean($attribute_value) . "'";
            $this->db_connection->exec("UPDATE {$this->prefix}eav_attribute_option_value SET value = {$attribute_value_insert} WHERE option_id = {$original_option_id}");

            //update our storage.
            $attribute_option_values_storage[$attribute_id][$attribute_value] = $original_option_id;
        }

        return false;
    }

    /**
     * Check if the option has been used by other products not included in their parent(configurable)
     * We use storage here.
     * @global rdi_lib $cart
     * @global array $attribute_usage This is going to contain all current option usages we seek to update.
     * @param int/string $attribute_id
     * @param int/string $option_id
     */
    public function check_attribute_option_usage($attribute_code, $option_id, $store_id = 0)
    {
        global $attribute_usage, $attribute_ids;

        if (!isset($attribute_usage) && !is_array($attribute_usage))
        {
            $attribute_usage = array();
        }

        $attribute_id = $attribute_ids[$attribute_code]['attribute_id'];

        if (!isset($attribute_usage[$attribute_id]))
        {
            $attribute_usage[$attribute_id] = $this->db_connection->cells("SELECT i.value, IF(IFNULL(o.option_id,'') = '','-1',COUNT(DISTINCT IFNULL(sl.parent_id,i.entity_id))) AS test FROM {$this->prefix}catalog_product_entity_int i
                                                                LEFT JOIN (SELECT v.option_id, o.attribute_id FROM {$this->prefix}eav_attribute_option o
                                                                            LEFT join {$this->prefix}eav_attribute_option_value v
                                                                            on v.option_id = o.option_id) as o
                                                                ON o.option_id  = i.value
                                                                AND o.attribute_id = i.attribute_id
                                                                LEFT JOIN {$this->prefix}catalog_product_super_link sl
                                                                ON sl.product_id = i.entity_id
                                                                WHERE i.attribute_id = {$attribute_ids[$attribute_code]['attribute_id']}
                                                                AND store_id = {$store_id}
                                                                GROUP BY VALUE", "test", "value");
        }

        return isset($attribute_usage[$attribute_id][$option_id]) ? $attribute_usage[$attribute_id][$option_id] : 0;
    }

    /**
     * Store the option_values for each attribute_id to memory. Save pressure on MYSQL.
     * @global array $attribute_option_values_storage
     * @global rdi_lib $cart
     * @param string $option_value
     * @param int $attribute_id
     */
    public function get_attribute_option_value_set($option_value, $attribute_code, $store_id = 0)
    {
        global $attribute_option_values_storage, $attribute_ids;

        $attribute_id = $attribute_ids[$attribute_code]['attribute_id'];

        //create the storage if we dont have it already.
        if (!isset($attribute_option_values_storage))
        {
            $attribute_option_values_storage = array();
        }
        //if we dont have the options for this array, lets create it.
        if (!isset($attribute_option_values_storage[$attribute_id]))
        {
            $attribute_option_values_storage[$attribute_id] = $this->db_connection->cells("SELECT DISTINCT v.value, o.option_id FROM {$this->prefix}eav_attribute_option_value v
                                                                                    JOIN {$this->prefix}eav_attribute_option o
                                                                                    ON o.option_id = v.option_id
                                                                                    AND o.attribute_id = {$attribute_id}
                                                                                    WHERE v.store_id = {$store_id}", 'option_id', 'value');
        }

        return isset($attribute_option_values_storage[$attribute_id][$option_value]) ? $attribute_option_values_storage[$attribute_id][$option_value] : false;
    }

    /**
     * 
     * @global rdi_lib $cart
     * @global type $product_link_type
     * @param type $upsell_data
     */
    public function process_link_update($upsell_data)
    {
        global $product_link_type;

        $position_link_attribute_id = $this->db_connection->cell("SELECT product_link_attribute_id FROM {$this->prefix}catalog_product_link_attribute pla
                                                                    JOIN {$this->prefix}catalog_product_link_type plt
                                                                    ON plt.link_type_id = pla.link_type_id
                                                                    AND plt.code = '{$product_link_type}'
                                                                    WHERE pla.product_link_attribute_code = 'position'
                                                                    ", "product_link_attribute_id");


        if (is_array($upsell_data) && count($upsell_data) > 0)
        {
            foreach ($upsell_data as $p)
            {
                $this->db_connection->exec("INSERT INTO {$this->prefix}catalog_product_link_attribute_int (link_id, product_link_attribute_id, value)
                values ({$p['link_id']}, {$position_link_attribute_id}, {$p['position']}) ON DUPLICATE KEY UPDATE value = {$p['position']} ");
            }
        }

        unset($upsell_data);
    }

    /**
     * 
     * @global rdi_lib $cart
     * @global type $product_link_type
     * @param type $upsell_data
     */
    public function process_link_removal($upsell_data)
    {
        global $product_link_type;

        if (is_array($upsell_data) && count($upsell_data) > 0)
        {
            foreach ($upsell_data as $p)
            {
                $this->db_connection->exec("DELETE FROM {$this->prefix}catalog_product_link_attribute_int where link_id = {$p['link_id']}");
                $this->db_connection->exec("DELETE FROM {$this->prefix}catalog_product_link where link_id = {$p['link_id']}");
            }
        }
    }

    /**
     * 
     * @global rdi_lib $cart
     * @param type $product_class
     * @param type $product_type
     */
    public function process_attribute_price_updates($product_class, $product_type)
    {
        //need to get a little data to prep for the query
        //need to the id of each of the attributes

        $product_entity_type_id = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
        $price_id = $this->db_connection->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'price' and entity_type_id = {$product_entity_type_id}", "attribute_id");

//    $sql = "SELECT b.value, s.sku, catalog_product_super_attribute.product_super_attribute_id, eav_attribute_option_value.value_id, 0, (p.value - b.value) as variance_price, 0
        $sql = "SELECT {$this->prefix}catalog_product_super_attribute.product_super_attribute_id, {$this->prefix}eav_attribute_option_value.value_id, 0, (p.value - b.value) as variance_price, 0
                from {$this->prefix}catalog_product_entity s
                inner join {$this->prefix}catalog_product_entity_decimal p on p.entity_id = s.entity_id and p.attribute_id = {$price_id}
                inner join {$this->prefix}catalog_product_super_link on {$this->prefix}catalog_product_super_link.product_id = s.entity_id
                inner join {$this->prefix}catalog_product_entity_decimal b on b.entity_id = {$this->prefix}catalog_product_super_link.parent_id and b.attribute_id = {$price_id}
                ";

        if (count($product_class['field_data']) > 2)
        {
            //use this to determine which attribute set has more options, these will be the ones that are marked for the variance
            $attr_count = "select x.entity_id, x.attribute_id, count(x.attribute_id) from (
                        SELECT s.entity_id, {$this->prefix}eav_attribute_option.attribute_id, {$this->prefix}eav_attribute_option_value.value
                        from {$this->prefix}catalog_product_entity s
                        inner join {$this->prefix}catalog_product_super_link on {$this->prefix}catalog_product_super_link.parent_id = s.entity_id
                        inner join {$this->prefix}catalog_product_entity_int on {$this->prefix}catalog_product_entity_int.entity_id = {$this->prefix}catalog_product_super_link.product_id
                        inner join {$this->prefix}eav_attribute_option on {$this->prefix}eav_attribute_option.option_id = {$this->prefix}catalog_product_entity_int.value
                        inner join {$this->prefix}eav_attribute_option_value on {$this->prefix}eav_attribute_option_value.option_id = {$this->prefix}eav_attribute_option.option_id
                        where s.type_id = 'configurable' and and s.attribute_set_id = {$product_class['product_class_id']}
                        group by {$this->prefix}eav_attribute_option_value.value, {$this->prefix}eav_attribute_option.attribute_id) as x
                        group by x.entity_id, x.attribute_id";
        }

        //have to do a little logic here, if there is more than one attribute then just do the insert, otherwise have to figure out which is the one changing first
        if (count($product_class['field_data']) < 2)
        {
            $sql = "replace into {$this->prefix}catalog_product_super_attribute_pricing (product_super_attribute_id, value_index, is_percent, pricing_value, website_id) " . $sql;
        }

        if (is_array($product_class['field_data']))
        {
            $query = $sql;

            //get the attribute variances for the each of the attribute codes, have to do a little logic
            foreach ($product_class['field_data'] as $field)
            {
                $sql = $query;

                $field_id = $this->db_connection->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = '{$field['cart_field']}' and entity_type_id = {$product_entity_type_id}", "attribute_id");

                $sql .= "inner join {$this->prefix}catalog_product_super_attribute on {$this->prefix}catalog_product_super_attribute.product_id = {$this->prefix}catalog_product_super_link.parent_id and {$this->prefix}catalog_product_super_attribute.attribute_id = {$field_id}
                    inner join {$this->prefix}catalog_product_entity_int on {$this->prefix}catalog_product_entity_int.entity_id = {$this->prefix}catalog_product_super_link.product_id and {$this->prefix}catalog_product_entity_int.attribute_id = {$field_id}
                    inner join {$this->prefix}eav_attribute_option on {$this->prefix}eav_attribute_option.option_id = {$this->prefix}catalog_product_entity_int.value
                    inner join {$this->prefix}eav_attribute_option_value on {$this->prefix}eav_attribute_option_value.option_id = {$this->prefix}eav_attribute_option.option_id
                    having variance_price != 0";

                unset($field_id);
            }
        }
        unset($sql, $product_entity_type_id, $price_id);
    }

    /**
     * 
     * @global rdi_lib $cart
     * @global type $update_availability
     * @return boolean
     */
    public function update_availability()
    {
        global $update_availability, $updated_stock;

        $updated = false;
        //handle the availability, make it optional setting
        if (isset($update_availability) && $update_availability > 0)
        {
            $this->db_connection->exec("UPDATE {$this->prefix}cataloginventory_stock_item
					JOIN {$this->prefix}catalog_product_entity cpe ON cpe.entity_id = {$this->prefix}cataloginventory_stock_item.product_id
					SET is_in_stock = 0
					  WHERE  (cpe.type_id = 'simple' and {$this->prefix}cataloginventory_stock_item.manage_stock = 1
					  AND   qty <= min_qty AND backorders = 0)");

            $this->db_connection->exec("UPDATE {$this->prefix}cataloginventory_stock_item
					JOIN {$this->prefix}catalog_product_entity cpe ON cpe.entity_id = {$this->prefix}cataloginventory_stock_item.product_id
					SET is_in_stock = 1
					  WHERE  (cpe.type_id = 'simple' and {$this->prefix}cataloginventory_stock_item.manage_stock = 1
					  AND   qty > min_qty) OR backorders > 0");

            //Configurables
            //turn these off
            //SET is_in_stock = 0
            $rows = $this->db_connection->rows("SELECT SUM({$this->prefix}cataloginventory_stock_item.qty) AS qty,parent_id product_id,{$this->prefix}cataloginventory_stock_item.min_qty , configurable.is_in_stock FROM {$this->prefix}catalog_product_super_link
                        INNER JOIN {$this->prefix}cataloginventory_stock_item
                        ON {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_super_link.product_id
                        JOIN {$this->prefix}cataloginventory_stock_item configurable
                        ON configurable.product_id = {$this->prefix}catalog_product_super_link.parent_id
                        WHERE (configurable.is_in_stock = 1 and configurable.manage_stock = 1) AND configurable.backorders = 0
                        GROUP BY {$this->prefix}catalog_product_super_link.parent_id
                        HAVING SUM({$this->prefix}cataloginventory_stock_item.qty > {$this->prefix}cataloginventory_stock_item.min_qty) = 0");

            if (!empty($rows))
            {
                $updated = true;

                foreach ($rows as $row)
                {
                    $updated_stock[] = $row['product_id'];
                    
                    $this->db_connection->exec("UPDATE {$this->prefix}cataloginventory_stock_item
						SET is_in_stock = 0
					  WHERE  product_id = {$row['product_id']} and {$this->prefix}cataloginventory_stock_item.manage_stock = 1");
                }
            }

            // turn these on
            //SET is_in_stock = 1
            $rows = $this->db_connection->rows("SELECT SUM({$this->prefix}cataloginventory_stock_item.qty) AS qty,parent_id product_id,{$this->prefix}cataloginventory_stock_item.min_qty , configurable.is_in_stock FROM {$this->prefix}catalog_product_super_link
                        INNER JOIN {$this->prefix}cataloginventory_stock_item
                        ON {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_super_link.product_id
                        JOIN {$this->prefix}cataloginventory_stock_item configurable
                        ON configurable.product_id = {$this->prefix}catalog_product_super_link.parent_id
                        WHERE (configurable.is_in_stock = 0 and configurable.manage_stock = 1) OR configurable.backorders > 0
                        GROUP BY {$this->prefix}catalog_product_super_link.parent_id
                        HAVING SUM({$this->prefix}cataloginventory_stock_item.qty > {$this->prefix}cataloginventory_stock_item.min_qty) > 0
                        ");

            if (!empty($rows))
            {
                $updated = true;

                foreach ($rows as $row)
                {
                    $updated_stock[] = $row['product_id'];
                    
                    $this->db_connection->exec("UPDATE {$this->prefix}cataloginventory_stock_item
						SET is_in_stock = 1
					  WHERE  product_id = {$row['product_id']} and {$this->prefix}cataloginventory_stock_item.manage_stock = 1");
                }
            }
        }

        return $updated;
    }

}

?>