<?php

/**
 * Common funcs for the cart module, these will be called from the outside, so should be pretty generic, but may also be specific
 */

/**
 * Description of magento_rdi_cart_common
 *
 * @author PMBliss
 * @package Core\Common\Magento
 */
class rdi_cart_common extends rdi_general {

    /**
     * Class Variables
     */
    public $attribute_set_ids;
    public $attribute_ids;
    public $indexers = array();
    public $ids_collection;

    /**
     * Class Constructor
     *
     * @param rdi_cart_common $db
     */
    public function rdi_cart_common($db = '')
    {
        global $store_id, $load_product_data, $default_stock_id, $inserted_products, $updated_products, $updated_stock, $updated_categories;

        //PMB 08112015
        if(!isset($inserted_products))
        {
            $inserted_products = array();
        }
        
        if(!isset($inserted_products))
        {
            $updated_products = array();
        }
        
        if(!isset($updated_categories))
        {
            $updated_categories = array();
        }
        
        if(!isset($updated_stock))
        {
            $updated_stock = array();
        }
        
        //@setting store_id The default store that a product's information should be stored.
        if (!isset($store_id))
            $store_id = 0;

        //@setting default_stock_id The default_stock_id that is used only when there is modules to handle mulitple stocks in magento.
        if (!isset($default_stock_id))
            $default_stock_id = 1;

        if ($db)
            $this->set_db($db);

        //This setting is to help with loading staging data XLSX
        if (isset($load_product_data) && $load_product_data == 1)
        {
            
        }
        else
        {
            require_once "libraries/cart_libs/magento/magento_rdi_indexer_lib.php";
        }
    }

    /*
     * Called pre load of the functions for the cart module
     */

    public function pre_load($call_hook = true)
    {
        global $hook_handler, $skip_validation, $magento_time_zone, $use_config_enable_qty_inc, $stock_status_changed_auto, $base_weee_tax_applied_row_amnt;

        $magento_time_zone = $this->db_connection->cell("SELECT value FROM {$this->prefix}core_config_data WHERE path = 'general/locale/timezone'", "value");

        set_indexes_all_clear();

        if (isset($skip_validation) && $skip_validation == 1)
        {
            
        }
        else
        {
            $invalid_classes = $this->validate_attribute_set_mapping();
            if ($invalid_classes !== false)
            {
                echo "<br>
                      <h1>Error with your rdi_cart_class_mapping</h1>
                      <br><b>{$invalid_classes}</b>
                      <br><span style='color:red'>Can not continue till this is fixed</span>
                      <br>";

                exit;
            }
        }

        $columns = $this->db_connection->cells("SHOW COLUMNS FROM {$this->prefix}cataloginventory_stock_item", "Field");

        $use_config_enable_qty_inc = in_array("use_config_enable_qty_inc", $columns) ? "use_config_enable_qty_inc" : "use_config_enable_qty_increments";
        $stock_status_changed_auto = in_array("stock_status_changed_auto", $columns) ? "stock_status_changed_auto" : "stock_status_changed_automatically";


        //sales_flat_order_item
        $columns = $this->db_connection->cells("SHOW COLUMNS FROM {$this->prefix}sales_flat_order_item", "Field");

        $base_weee_tax_applied_row_amnt = in_array("base_weee_tax_applied_row_amnt", $columns) ? "base_weee_tax_applied_row_amnt" : "base_weee_tax_applied_row_amount";

        $this->set_current_website();

        if ($call_hook)
        {
            $hook_handler->call_hook("cart_common_pre_load");
        }
    }
    
    /**
     * 
     * @global array $inserted_products
     * @global array $updated_products
     * @global array $updated_stock
     * @return array
     */
    public function collect_insert_updated_ids()
    {
        global $inserted_products, $updated_products, $updated_stock, $updated_categories;
        
        $this->update_product_rdi_last_updated($updated_products);
        
        $inserted_products = empty($inserted_products)?array():$inserted_products;
        $updated_products = empty($updated_products)?array():$updated_products;
        $updated_stock = empty($updated_stock)?array():$updated_stock;
        $updated_categories = empty($updated_categories)?array():$updated_categories;
        
        $ids = array_reduce(array_merge($inserted_products, $updated_products));
        
        
        $inserted_products = array_unique($inserted_products);
        $updated_products = array_unique($updated_products);
        $updated_stock = array_unique($updated_stock);
        $updated_categories = array_unique($updated_categories);
        //inserts will also come from new products and new products to categories
        if(!empty($inserted_products))
        {
            $this->indexers['catalog_url'] = true;
            $this->indexers['catalog_category_product'] = true;  
            $this->indexers['catalog_product_flat'] = true;
            $this->indexers['catalog_product_attribute'] = true; 
            $this->indexers['catalogsearch_fulltext'] = true;
            $this->indexers['tag_summary'] = true;         
        }
        
        if(!empty($updated_products))
        {
            $this->indexers['catalog_product_flat'] = true;
            $this->indexers['catalogsearch_fulltext'] = true;
            $this->indexers['tag_summary'] = true;
            $this->indexers['catalog_product_attribute'] = true;
        }
        
        if(!empty($updated_categories))
        {
            $this->indexers['catalog_category_flat'] = true;
            $this->indexers['catalog_url'] = true;
        }
        
        
        if(!empty($updated_stock))
        {
            $this->indexers['cataloginventory_stock'] = true;
        }
             
        //switch to a count before committing.
        $this->echo_message("Products",1);
        $this->echo_message("Inserted:  " . count($inserted_products),2);
        $this->echo_message("Updated:   " . count($updated_products), 2);
        $this->echo_message("Stock:     " . count($updated_stock),2);
        $this->echo_message("Categories",1);
        $this->echo_message("Updated: " . count($updated_categories),2);
        
        $this->ids_collection = array('inserted_products'=>$inserted_products,
            'updated_products'=>$updated_products,
            'updated_stock'=>$updated_stock,
            'updated_categories'=>$updated_categories);
        
        unset($inserted_products,$updated_products, $updated_stock, $updated_categories);
        
        $this->add_updated_ids_to_log($this->ids_collection);
        
        return $ids;
    }
    
    public function fill_indexer_array()
    {
        $rows = $this->db_connection->rows("select indexer_code from {$this->prefix}index_process");
        
        $this->indexers = array();
        
        if(!empty($rows))
        {
            foreach($rows as $row)
            {
                $this->indexers[$row['indexer_code']] = false;
            }        
        }
    }
    

    /**
     * 
     * @global type $debug
     * @global type $hook_handler
     * @global type $benchmarker
     * @global type $dont_index
     * @global type $dont_run_core_url_indexer
     * @global type $load_images
     * @global rdi_lib $cart
     * @global type $db
     * @global type $db_lib
     * @global type $rdi_images_changed
     * @global type $helper_funcs
     * @return boolean
     */
    public function post_load()
    {
        global $hook_handler, $benchmarker, $dont_index, $dont_run_core_url_indexer, $load_images, $cart, $db, $db_lib, $rdi_images_changed, $helper_funcs;

        //$this->cleanup_delete_products();
        //set the simples that are in the superlink table to visibility 1
        $this->set_visibility_on_all();
        $cart_product_lib = $cart->get_processor("rdi_cart_product_load");
        $cart_product_lib->disable_product_for_image();
        $cart_product_lib->set_display_only();
        
        $this->fill_indexer_array();
        
        $this->collect_insert_updated_ids();

        $hook_handler->call_hook("cart_common_post_load_pre_load");

        if (isset($dont_index) && $dont_index == 1)
        {
            $hook_handler->call_hook("cart_common_post_load");

            return true;
        }

        //this addon has access to the ids via $this->ids_collection;
        if (!($hook_handler->call_hook("cart_common_run_indexer",$this)))
        {
            require_once "libraries/cart_libs/magento/magento_rdi_indexer_lib.php";

            //find out which indexes are dirty and reindex it
            //the rows in the index_process table marked as require_reindex
            $processes = $this->db_connection->rows("select indexer_code, process_id, status from {$this->prefix}index_process", 'indexer_code');

            @ob_flush();

            $run_url = false;
            
            if(!empty($processes))
            {
                $this->echo_message("Running the Indexer", 1);
                Mage::dispatchEvent('shell_reindex_init_process');
                foreach ($processes as $indexer_code => $process)
                {
                    if ($process['process_id'] == 3)
                    {
                        $run_url = true;
                    }
                    else
                    {
                        if($this->indexers[$indexer_code] || $process['status'] == 'require_reindex')
                        {
                            indexer_index($process['process_id']);
                        }
                    }
                }

                //run the core url last, seems it fails sometimes if you dont
                if ($run_url)
                {
                    if (isset($dont_run_core_url_indexer) && $dont_run_core_url_indexer == 1)
                    {

                    }
                    else
                    {
                        if($this->indexers['catalog_url'])
                        {
                            indexer_index(3);
                        }                        

                        if($this->indexers['catalog_product_price'])
                        {
                            indexer_index(2);
                        }

                    }
                }
                Mage::dispatchEvent('shell_reindex_finalize_process');
            }
            
            $flush_images = (isset($rdi_images_changed)) ? $rdi_images_changed : false;

            // IMAGE CACHE
            if (isset($load_images) && $load_images == 1 && $db_lib->get_product_count() > 0 && $flush_images)
            {
                try
                {
                    Mage::getModel('catalog/product_image')->clearCache();
                    echo 'Image cache was cleared succesfuly' . "\n";
                } catch (Mage_Core_Exception $e)
                {
                    echo $e->getMessage() . "\n";
                } catch (Exception $e)
                {
                    echo 'Error while cleared Image cache. Please try again later' . $e->getMessage() . "\n";
                }
            }

            //clear the mage cache so it doesnt show invalid results
            indexer_clear_cache();
        }

        $hook_handler->call_hook("cart_common_post_load");
    }

    public function pre_export()
    {
        global $hook_handler;

        $hook_handler->call_hook("cart_common_pre_export");
    }

    public function post_export()
    {
        global $hook_handler;

        $hook_handler->call_hook("pos_common_post_export");
    }

    //validate the mapped classes
    private function validate_attribute_set_mapping()
    {
        $invalid_classes = '';

        //check that the specified classes exist and have the ids specified
        $classes = $this->db_connection->rows("SELECT product_class, product_class_id, (select attribute_set_id from {$this->prefix}eav_attribute_set where attribute_set_name = product_class) as 'correct_id'
                                                FROM rdi_cart_class_mapping
                                                LEFT JOIN {$this->prefix}eav_attribute_set ON rdi_cart_class_mapping.product_class_id = {$this->prefix}eav_attribute_set.attribute_set_id and rdi_cart_class_mapping.product_class =  {$this->prefix}eav_attribute_set.attribute_set_name
                                                WHERE {$this->prefix}eav_attribute_set.attribute_set_id IS NULL");

        if (is_array($classes))
        {
            foreach ($classes as $class)
            {
                $invalid_classes .= " Name: {$class['product_class']}<br>Current ID: {$class['product_class_id']}<br>Reported Correct ID: {$class['correct_id']}<br><br>";
            }
        }

        return $invalid_classes != '' ? $invalid_classes : false;
    }

    public function validate_cart_field_mapping_existance()
    {
        $_csi = $this->db_connection->cells("SHOW COLUMNS FROM {$this->prefix}cataloginventory_stock_item", "Field");

        $csi = implode("','", $_csi);

        unset($_csi);

        $invalid_product = $this->db_connection->rows("SELECT cart_field
                                        FROM rdi_field_mapping
                                        LEFT JOIN {$this->prefix}eav_attribute ON {$this->prefix}eav_attribute.attribute_code = rdi_field_mapping.cart_field
                                        WHERE field_type = 'product' AND {$this->prefix}eav_attribute.attribute_id IS NULL AND rdi_field_mapping.cart_field NOT IN ('{$csi}','avail','product_image', 'style_id', 'item_id', 'color_sort_order', 'size_sort_order')");

        $invalid_category = $this->db_connection->rows("SELECT cart_field
                                FROM rdi_field_mapping
                                LEFT JOIN {$this->prefix}eav_attribute ON {$this->prefix}eav_attribute.attribute_code = rdi_field_mapping.cart_field
                                WHERE field_type = 'category' AND {$this->prefix}eav_attribute.attribute_id IS NULL and cart_field not in ('entity_id', 'parent_id')");

        return array("category" => $invalid_category, "product" => $invalid_product);
    }

    public function validate_cart_field_mapping_minimum_required()
    {
        $required_products = array("visibility",
            "status",
            "custom_design_to",
            "custom_design_from",
            "special_to_date",
            "special_from_date",
            "news_to_date",
            "news_from_date",
            "custom_layout_update",
            "meta_keyword",
            "gift_message_available",
            "tax_class_id",
            "is_recurring",
            "use_config_min_qty",
            "visibility",
            "url_path",
            "use_config_enable_qty_inc",
            "use_config_qty_increments",
            "use_config_manage_stock",
            "use_config_notify_stock_qty",
            "low_stock_date",
            "is_in_stock",
            "use_config_max_sale_qty",
            "use_config_min_sale_qty",
            "use_config_backorders",
            "is_qty_decimal",
            "options_container",
            "name",
            "related_id",
            "sku",
            "style_id",
            "special_price",
            "price",
            "weight",
            "size",
            "related_id",
            "sku",
            "short_description",
            "description",
            "item_id",
            "custom_design",
            "thumbnail",
            "url_key",
            "meta_title",
            "meta_description",
            "image",
            "small_image",
            "qty",
            "product_image");

        $cart_fields = $this->db_connection->cells('select cart_field from rdi_field_mapping where field_type = "product"', 'cart_field');

        $missing_product = array();
        foreach ($required_products as $required_product)
        {
            if (!in_array($required_product, $cart_fields))
            {
                $missing_product[] = $required_product;
            }
        }

        $required_categories = array("custom_apply_to_products",
            "filter_price_range",
            "meta_keywords",
            "custom_design_to",
            "custom_design_from",
            "available_sort_by",
            "custom_layout_update",
            "meta_description",
            "is_active",
            "page_layout",
            "custom_design",
            "related_id",
            "parent_id",
            "description",
            "position",
            "name",
            "url_key",
            "meta_title",
            "display_mode",
            "is_anchor",
            "include_in_menu");

        $cart_fields = $this->db_connection->cells('select cart_field from rdi_field_mapping where field_type = "category"', 'cart_field');

        $missing_category = array();

        foreach ($required_categories as $required_category)
        {
            if (!in_array($required_category, $cart_fields))
            {
                $missing_category[] = $required_category;
            }
        }

        return array("product" => $missing_product, "category" => $missing_category);
    }

    public function validate_cart_field_mapping_warnings()
    {
        return null;
    }

    //a very open, handle the errors here validation function, handle the display and errors here
    public function validate_cart_settings()
    {
        //check that the related_id on the catalog_category_entity exists
        $sql = "show columns from {$this->prefix}catalog_category_entity where field = 'related_id'";

        $field = $this->db_connection->row($sql);

        if ($field['Field'] != "related_id")
        {
            echo "<br><h1>Error: Field Missing 'related_id'</h1>.<br> Add this field to the {$this->prefix}catalog_category_entity table. Type varchar 50. And be sure to add an index<br>
                  <br><span style='color:red'>Can not continue till this is fixed</span>";
            exit;
        }

        if (strpos($field['Type'], "varchar") === false)
        {
            echo "<br><h1>Error: Field wrong type 'related_id'</h1>.<br> The field is the wrong data type. Check the {$this->prefix}catalog_category_entity table. Correct type is varchar 50. And be sure to add an index<br>
                  <br><span style='color:red'>Can not continue till this is fixed</span>";
            exit;
        }
    }

    //mark the timestamp of a product as having been updated
    public function mark_category_updated($category_id)
    {
        $this->db_connection->exec("update {$this->prefix}catalog_category_entity set rdi_last_update = now() where entity_id = {$category_id}");
    }

    //get the clause for the products that are not configurables, just simples, to be used in a number of queries
    public function get_stand_alone_product_clause()
    {
        //need to know the type of the attributes that are stand alone if there is any
        //process this by checking our types table, get the ones that are stand alone, put into a (,,,,) list and use in
        $stand_alone_types = $this->db_connection->cell("SELECT distinct group_concat(product_class separator ',') as types
                                                            FROM rdi_cart_class_mapping
                                                            INNER JOIN rdi_cart_product_types ON rdi_cart_product_types.cart_class_mapping_id = rdi_cart_class_mapping.cart_class_mapping_id
                                                            where rdi_cart_product_types.cart_class_mapping_id not in (select distinct cart_class_mapping_id  from rdi_cart_product_types where product_type = 'configurable')", 'types');
        $stand_alones_clause = '';
        if ($stand_alone_types != '')
        {
            $a = explode(',', $stand_alone_types);

            $order_export_status = '';

            $stand_alone_types = '';

            foreach ($a as $s)
            {
                $stand_alone_types .= "'{$s}',";
            }

            $stand_alone_types = substr($stand_alone_types, 0, -1);

            $stand_alones_clause = " or {$this->prefix}eav_attribute_set.attribute_set_name in ({$stand_alone_types})";
        }

        // return $stand_alones_clause;

        return '';
    }

    //gets a value of a field specified that relates to the entity specified, this is used when some mapping is set but the value isnt present in the data
    //we will assume that missing data is a custom attribute and try and get its value
    public function get_field_value($entity_type, $attribute_code, $entity_id)
    {
        if (!($attribute_code == '' || $attribute_code == NULL))
        {
            $attribute_data = $this->get_attribute($attribute_code, $entity_type, true);

            if (is_array($attribute_data))
            {
                return $this->db_connection->cell("select value from {$this->prefix}{$entity_type}_entity_{$attribute_data['backend_type']} where attribute_id = '{$attribute_data['attribute_id']}' and entity_id = {$entity_id}", 'value');
            }
        }

        return null;
    }

    public function set_visibility_on_all()
    {
        global $cart, $load_products;

        if (isset($load_products) && $load_products == 1)
        {

            $entity_type_id = $cart->get_db()->cell("SELECT entity_type_id FROM {$this->prefix}eav_entity_type WHERE entity_type_code = 'catalog_product'", 'entity_type_id');

            $visibility_attribute_id = $cart->get_db()->cell("SELECT attribute_id FROM {$this->prefix}eav_attribute WHERE attribute_code = 'visibility' AND entity_type_id = {$entity_type_id}", 'attribute_id');

            $cart->get_db()->exec("UPDATE {$this->prefix}catalog_product_entity_int i
                        JOIN {$this->prefix}catalog_product_super_link sl
                        ON sl.product_id = i.entity_id
                        SET i.value = 1
                        WHERE i.value = 4 AND i.attribute_id = {$visibility_attribute_id}");

            $status_attribute_id = $cart->get_db()->cell("SELECT attribute_id FROM {$this->prefix}eav_attribute WHERE attribute_code = 'status' AND entity_type_id = {$entity_type_id}", 'attribute_id');

            $cart->get_db()->exec("UPDATE {$this->prefix}catalog_product_entity_int i
                        JOIN {$this->prefix}catalog_product_super_link sl
                        ON sl.product_id = i.entity_id
                        SET i.value = 1
                        WHERE i.value = 2 AND i.attribute_id = {$status_attribute_id}");
        }
    }

    /**
     * This never worked.
     * @global rdi_lib $cart
     */
    public function cleanup_delete_products()
    {
        
    }

    /**
     * sets the current website and default category for that site.
      from the calling url. This overrides two settings.
     *
     * @global setting $root_category_id
     */
    public function set_current_website()
    {
        global $root_category_id;

        if (isset($root_category_id) && $root_category_id > '0')
        {
            
        }
        else
        {
            $actual_link = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
            $config_data = dirname(dirname($actual_link));

            //the site might be set to {{base_url}} in that case we will use 2. IF not it  will not load.
            $sql = "SELECT DISTINCT ccw.website_id as default_site, csg.root_category_id FROM {$this->prefix}core_config_data ccd
					JOIN {$this->prefix}core_website ccw
					ON ccw.website_id = ccd.scope_id
					JOIN {$this->prefix}core_store_group csg
					ON csg.group_id = ccw.default_group_id
					AND csg.website_id = ccw.website_id
                                        and csg.root_category_id != 0
					WHERE ccd.value = '{$config_data}/'";

            $values = $this->db_connection->row($sql);

            if (is_array($values) && !empty($values))
            {
                foreach ($values as $setting => $value)
                {
                    $GLOBALS[$setting] = $value;
                }
            }
            else
            {

                $GLOBALS['root_category_id'] = $this->db_connection->cell("SELECT root_category_id FROM {$this->prefix}core_store_group csg
                                                                            LEFT join {$this->prefix}core_store cs
                                                                            on cs.store_id = default_store_id
                                                                            LEFT  JOIN {$this->prefix}core_website cw
                                                                                ON cw.website_id = csg.website_id
                                                                                WHERE  (cs.code = 'default' OR cw.is_default = 1)", "root_category_id");

                //$GLOBALS['root_category_id'] = $this->db_connection->cell("SELECT MIN(root_category_id) AS root_category_id FROM {$this->prefix}core_store_group WHERE root_category_id != 0", "root_category_id");
                //default_site is already set to be 1 in the settings
                //$GLOBALS['root_category_id'] = 2;
            }
        }
    }

    public function get_mapping_field_list($attribute_set_name, $entity_type_code = 'product')
    {
        $out = array();
        $out['fields'] = "attributes.attribute_code,
						  attributes.attribute_id,
						  attributes.backend_type,
						  attributes.frontend_input";

        $attribute_set_join = $entity_type_code == 'product' ? " INNER " : " LEFT ";

        $out['join'] = "LEFT JOIN (SELECT
						  ea.attribute_code,
						  ea.attribute_id,
						  ea.backend_type,
						  ea.frontend_input
						FROM
						  {$this->prefix}eav_entity_attribute eea
						  INNER JOIN {$this->prefix}eav_entity_type eet
							ON eet.entity_type_id = eea.entity_type_id
							AND eet.entity_type_code = 'catalog_{$entity_type_code}'
						  {$attribute_set_join} JOIN {$this->prefix}eav_attribute_set eas
							ON eas.attribute_set_id = eea.attribute_set_id
							AND eas.attribute_set_name = '{$attribute_set_name}'
						  {$attribute_set_join} JOIN {$this->prefix}eav_attribute ea
							ON ea.attribute_id = eea.attribute_id ) AS attributes
							ON attributes.attribute_code = f.cart_field";

        return $out;
    }

    /**
     * Load Magento
     * This will load in the Magento API framework if it has not already been loaded.
     */
    public function load_magento()
    {
        $this->_echo(__FUNCTION__);
        if (!class_exists('Mage'))
        {
            $mage_path = file_exists('../app/Mage.php') ? '../app/Mage.php' : dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . "/app/Mage.php";

            include_once $mage_path;
            umask(0);
            Mage::app();

            //set the area to be secure and set the store to the admin
            Mage::register('isSecureArea', 1);
            Mage::app('admin')->setUseSessionInUrl(false);
            Mage::app()->setCurrentStore(Mage_Core_Model_App::ADMIN_STORE_ID);
        }
    }

    /**
     *
     * @param string $attribute_set_name
     * @return attribute_set_id Magento's attribute set_id for the given name.
     */
    public function get_attribute_set_id($attribute_set_name, $entity_type_code = 'catalog_product')
    {
        return $this->db_connection->cell("SELECT eas.attribute_set_id
                                            FROM {$this->prefix}eav_attribute_set eas
                                            JOIN {$this->prefix}eav_entity_type eet
                                            on eet.entity_type_id = eas.entity_type_id
                                            WHERE eas.attribute_set_name = '{$attribute_set_name}'", "attribute_set_id");
    }

    /**
     * This will get and set all attribute_ids for a particular entity_type_code.
     * If that one is not found, it will go and get all of them.
     * NOT WORKING YET!!!
     * @param type $attribute_name
     * @param type $entity_type_code
     * @return type
     */
    public function get_attribute($attribute_name, $entity_type_code = "catalog_product", $extended = false)
    {
        if (!isset($this->attributes[$entity_type_code][$attribute_name]))
        {
            $attributes = $this->db_connection->rows("SELECT et.entity_type_code, ea.*
                                                    FROM {$this->prefix}eav_attribute ea
                                                    INNER JOIN {$this->prefix}eav_entity_type et
                                                    ON et.entity_type_id = ea.entity_type_id
                                                    AND et.entity_type_code = '{$entity_type_code}'");


            if (!empty($attributes))
            {
                foreach ($attributes as $attribute)
                {
                    //if we there is no attribute with this entity_type_code/attribute_code, we add it
                    if (!isset($this->attributes[$attribute['entity_type_code']][$attribute['attribute_code']]))
                    {
                        $this->attributes[$entity_type_code][$attribute['attribute_code']] = $attribute;
                    }
                }
                if ($extended)
                {
                    return $this->attributes[$entity_type_code][$attribute['attribute_code']];
                }
                else
                {
                    return $this->attributes[$entity_type_code][$attribute['attribute_code']]['attribute_id'];
                }
            }
        }
        else
        {
            if ($extended)
            {
                return $this->attributes[$entity_type_code][$attribute_name];
            }
            else
            {
                return $this->attributes[$entity_type_code][$attribute_name]['attribute_id'];
            }
        }
    }
    
    public function update_product_rdi_last_updated($updated_products)
    {
        $attribute_id = $this->get_attribute('rdi_last_updated');
        
        $now = $this->db_connection->cell("select now() a",'a');
        
        if(!empty($updated_products) && isset($attribute_id))
        {
            foreach(array_chunk($updated_products, 100) as $list)
            {
                $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_datetime SET value = '{$now}' WHERE entity_id in(".implode(",", $list).") AND attribute_id = {$attribute_id}");
            }
        }
    }
    
    public function add_updated_ids_to_log($array_type_ids)
    {
        if(!empty($array_type_ids))
        {
            $now = $this->db_connection->cell("select now() a",'a');
            
            foreach($array_type_ids as $type => $ids)
            {
                if(!empty($ids))
                {
                    $this->db_connection->exec("INSERT INTO rdi_debug_log (LEVEL,DATETIME,script,func,debug_message,DATA) VALUES ('999','{$now}','changes_ids','{$type}','Ids that have changed','".implode(',',$ids)."');");
                }
            }
        }
    }

    public function get_rdi_magento_reindexer()
    {
        if(!isset($this->reindexer))
        {
            $this->reindexer = new rdi_magento_reindexer($this->db_connection);
        }
        return $this->reindexer;
    }
    
}

class rdi_magento_reindexer extends rdi_general
{
    const MAX_INDEX_IDS = 200;
    
    protected $models = array();
    
    public function __construct($db)
    {
        $this->rdi_general($db);
        $this->models['product_price'] = Mage::getModel('catalog/product_indexer_price');
	//$process_flat = Mage::getModel('catalog/product_indexer_flat');
	$this->models['product_flat'] = Mage::getModel('catalog/product_flat_indexer');
	$this->models['product_url'] = Mage::getSingleton('catalog/url');
    }
    
    public function reindex($type,$ids)
    {
        if(!empty($ids))
        {
            $startTime = microtime(true);
            foreach(array_chunk($ids,200) as $ids_chunk)
            {
                try {
                    call_user_method_array($type, $this, array($ids_chunk));        
                }
                catch(Mage_Core_Exception $e) {
                    $this->_var_dump($e->getMessage(),4);
                } catch (Exception $e) {
                    $this->_var_dump(" index process unknown error:",4);
                    $this->_var_dump($e);
                }
            }            
            $resultTime = microtime(true) - $startTime;
            $this->echo_message(  " Reindexed {$type} " . gmdate('H:i:s', $resultTime) . "({$resultTime})",4);
        }
    }
    
    public function product_flat($ids)
    {
        $store_id = 1;
        $this->models['product_flat']->updateProduct($ids, $store_id);
    }
    
    public function product_price($ids)
    {
        //$store_id = 1;
        $this->models['product_price']->getResource()->reindexProductIds($ids);
    }
    
    public function product_url($ids)
    {
        $store_id = 1;
        foreach($ids as $id)
        {
            $this->models['product_url']->refreshProductRewrite($id);
        }
    }
    
}


?>