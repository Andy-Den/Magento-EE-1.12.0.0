<?php

/**
 * Catalog load class
 *
 * Handles the loading of the catalog data
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * 
 * @package    Core\Load\Catalog\Magento
 * @todo Surface the related_id on the category page so that user can change whether the integration can update the category.
 */
class rdi_cart_catalog_load extends rdi_general {

    private $disable_out_of_stock;
    public $parent_paths = array();
    public $attribute;

    /**
     * Class Constructor
     *
     * @param rdi_catalog_load $db
     */
    public function rdi_cart_catalog_load($db = '')
    {
        $this->check_catalog_lib_version();

        if ($db)
            $this->set_db($db);

        $this->attribute = $this->db_connection->cells("SELECT attribute_code,attribute_id FROM {$this->prefix}eav_attribute
                                                INNER JOIN {$this->prefix}eav_entity_type on {$this->prefix}eav_entity_type.entity_type_id = {$this->prefix}eav_attribute.entity_type_id
                                                WHERE {$this->prefix}eav_entity_type.entity_type_code = 'catalog_category'", 'attribute_id', 'attribute_code');
    }

    /**
     * Pre Load Function
     * @global type $hook_handler
     * @hook cart_catalog_load_pre_load
     */
    public function pre_load()
    {
        global $hook_handler;

        $this->set_root_category_id();

        $hook_handler->call_hook("cart_catalog_load_pre_load");
    }

    /**
     * Post Load Function
     * Calls the following operations
     * Process Status for Image,
     * Clean Exclude for web,
     * Clean Category Parent_ids,
     * Set Anchors,
     * Set Brand Anchors,
     * Turn on Categories and Children
     * 
     * @global type $hook_handler
     * @hook cart_catalog_load_post_load
     */
    public function post_load()
    {
        global $hook_handler;

        //set the indexes dirty
        require_once "libraries/cart_libs/magento/magento_rdi_indexer_lib.php";

        indexer_set_catalog_search_index_dirty();
        indexer_set_catalog_url_rewrites_dirty();
        indexer_set_category_flat_data_dirty();
        indexer_set_category_products_dirty();

        //hide products not in categories from the search
        //$this->process_orphan_visibility();
        //enable any products that are in categories to the search to searchable
        //$this->process_nonorphan_visibility();
        //handle the disable / enable of any products in categories
        //$this->process_orphan_status();
        //handle the enable of any products with an image that are disabled give the rest of the settings
        $this->fix_children_counts();
        $this->process_status_for_image();
        $this->clean_up_exclude_from_web();
        $this->clean_category_parent_ids();
        $this->set_anchors();
        $this->set_brand_anchors();
        $this->turn_on_categories_and_children();

        $this->clean_old_categories();

        //custom url rewrite 
        //$this->reindex_category_urls();

        $hook_handler->call_hook("cart_catalog_load_post_load");
        $this->enterprise_url_key_update('catalog_category');
    }

    public function enterprise_url_key_update($entity_type_code = 'catalog_product')
    {
        global $debug;

        $enterprise = $this->db_connection->rows("SHOW TABLES LIKE '{$this->prefix}enterprise%'");

        if (!empty($enterprise))
        {
            $url_key_attribute_id = $this->attribute['url_key'];

            $urls = $this->db_connection->rows("SELECT DISTINCT v.entity_type_id,
             v.attribute_id,
             v.store_id,
             v.entity_id,
             v.value FROM {$this->prefix}{$entity_type_code}_entity_varchar v
			LEFT JOIN {$this->prefix}{$entity_type_code}_entity_url_key u
			ON u.entity_id = v.entity_id
			AND u.attribute_id = v.attribute_id
			AND u.store_id = v.store_id
			WHERE v.attribute_id = {$url_key_attribute_id} 
			AND v.store_id = 0
			AND  u.value IS NULL");

            if (!empty($urls))
            {
                $url_count = count($urls);

                $debug->write(basename(__FILE__), __FUNCTION__, "Found {$url_count} {$entity_type_code} urls to insert.");

                foreach ($urls as $url)
                {
                    $this->db_connection->insertAr2("{$this->prefix}{$entity_type_code}_entity_url_key", $url, false, array(), array('value'));
                }
            }
        }
    }

    public function fix_children_counts()
    {

        $categories = $this->db_connection->rows("SELECT entity_id, children_count FROM {$this->prefix}catalog_category_entity WHERE related_id is not null and rdi_inactive_date is null ORDER BY level DESC");

        if (!empty($categories))
        {
            foreach ($categories as $category)
            {
                $count = $this->db_connection->cell("SELECT count(*) c FROM {$this->prefix}catalog_category_entity WHERE path like '%/{$category['entity_id']}/%'", 'c');

                if ($count !== $category['children_count'])
                {
                    $this->db_connection->exec("UPDATE {$this->prefix}catalog_category_entity SET children_count = {$count} WHERE entity_id = {$category['entity_id']}");
                }
            }
        }
    }

    /**
     * There is a certain series of category moves that can cause the parent_id to be set to the entity_id. This cleans them using the path.
     * @return \rdi_cart_catalog_load
     */
    public function clean_category_parent_ids()
    {
        $this->db_connection->exec("UPDATE {$this->prefix}catalog_category_entity 
                                    SET parent_id = SUBSTRING_INDEX(REPLACE(path,CONCAT('/',entity_id),''),'/','-1') 
                                    WHERE parent_id = entity_id ");

        $attribute_set_id = $this->db_connection->cell("SELECT eas.attribute_set_id FROM {$this->prefix}eav_attribute_set eas
                                                        JOIN {$this->prefix}eav_entity_type et
                                                        ON et.entity_type_id = eas.entity_type_id 
                                                        AND et.entity_type_code = 'catalog_category'", "attribute_set_id");


        $this->db_connection->exec("UPDATE {$this->prefix}catalog_category_entity 
                                    SET attribute_set_id = {$attribute_set_id}
                                    WHERE related_id is not null ");
                                    
                                    
        $this->db_connection->exec("UPDATE {$this->prefix}catalog_category_entity SET LEVEL = LENGTH(path) - LENGTH(REPLACE(path,'/',''))  WHERE LENGTH(path) - LENGTH(REPLACE(path,'/','')) != LEVEL");

        return $this;
    }

    /**
     * use this if we are looking to do post anchoring.
     * this will set all values in the database as the anchored ones.
     * 
     * This will set the category anchors for all categories on a particular level.
     * 
     * @global type $cart
     * @global type $category_anchors
     */
    public function set_anchors()
    {
        global $category_anchors;

        /**
         * get the attribute id for the is_anchor
         */
        $sql = "update {$this->prefix}catalog_category_entity_int 
                    inner join {$this->prefix}catalog_category_entity on {$this->prefix}catalog_category_entity_int.entity_id = {$this->prefix}catalog_category_entity.entity_id
                    set value = 1 
                    where {$this->prefix}catalog_category_entity.level in ({$category_anchors}) and {$this->prefix}catalog_category_entity_int.attribute_id = {$this->attribute['is_anchor']}";

        $this->db_connection->exec($sql);
    }

    /**
     * Use a list of Related_ids to keep a certain category tree turned on.
     * @author PMBliss
     */
    public function turn_on_categories_and_children()
    {
        global $enable_category_tree;
        //@setting $enable_category_tree  0 is off. Comma seperated list of related_ids that will enable all children under a category.

        if (isset($enable_category_tree) && $enable_category_tree != 0)
        {
            $_root_id = strstr($enable_category_tree, ",") ? explode(",", $brand_root_catalog_id) : $brand_root_catalog_id;

            foreach ($_root_id AS $root_id)
            {
                $root_cat_id = $this->db_connection->cell("SELECT entity_id FROM {$this->prefix}catalog_category_entity WHERE related_id = '{$root_id}'", "entity_id");

                $this->db_connection->exec("UPDATE {$this->prefix}catalog_category_entity e
                                            JOIN {$this->prefix}catalog_category_entity_int i
                                            ON i.entity_id = e.entity_id
                                            AND i.attribute_id = {$this->attribute['is_active']}
                                            AND i.value != 1
                                            SET i.value = 0
                                            WHERE path LIKE '%/{$root_cat_id}/%'");
            }
        }
    }

    /**
     *  Use this with Setting: brand_root_catalog_id, to set all the anchors under a category. Used for multilayered brands category structures.
     * @author PMBliss
     * 
     */
    public function set_brand_anchors()
    {
        global $brand_root_catalog_id;
        //@setting brand_root_catalog_id 0 is off. List of related_ids.

        if (isset($brand_root_catalog_id) && $brand_root_catalog_id != '0')
        {
            $_root_id = strstr($brand_root_catalog_id, ",") ? explode(",", $brand_root_catalog_id) : array($brand_root_catalog_id);

            foreach ($_root_id AS $root_id)
            {
                $root_cat_id = $this->db_connection->cell("SELECT entity_id FROM {$this->prefix}catalog_category_entity WHERE related_id = '{$root_id}'", "entity_id");


                $this->db_connection->exec("UPDATE {$this->prefix}catalog_category_entity e
                                            JOIN {$this->prefix}catalog_category_entity_int i
                                            ON i.entity_id = e.entity_id
                                            AND i.attribute_id = {$this->attribute['is_anchor']}
                                            AND i.value != 1
                                            SET i.value = 1
                                            WHERE path LIKE '%/{$root_cat_id}/%'");
            }
        }
    }

    /*
     * @PMBLISS 03252013
     * disables a product that does not have a main image assigned.
     */

    /**
     * Disables a product that does not have a main image assigned.
     * Here we are going to turn on a product for having an image given that it is not orphaned and not out of stock, if those settings are set.
     * 
     *  
     * @global rdi_lib $cart
     * 
     * @todo Remove the multiple queries for attribute ids.
     * @todo use the insertAr2 function for the update query. Or generate a list of products that need to update and set.
     */
    public function process_status_for_image()
    {
        global $product_require_image, $disable_orphans, $disable_out_of_stock;
        //@setting $product_require_image [0-OFF, 1-ON] Set to 1 to disable product if it does not have an image.
        //@setting $disable_orphans Disable [0-OFF, 1-ON] Set to 1 products that are not in a category.
        //@setting $disable_out_of_stock [0-OFF, 1-ON] Set to 1 Disable products that are out of stock.

        if (isset($product_require_image) && $product_require_image == 1)
        {

            /**
             * get the product_entity_code
             */
            $entity_type_id = $this->db_connection->cell("SELECT entity_type_id FROM {$this->prefix}eav_entity_type WHERE entity_type_code = 'catalog_product'", 'entity_type_id');

            /**
             * get the image attribute_id. The requires the main image radial button
             */
            $image_attribute_id = $this->db_connection->cell("SELECT attribute_id FROM {$this->prefix}eav_attribute WHERE attribute_code = 'image' AND entity_type_id = {$entity_type_id}", 'attribute_id');

            /**
             * get the status attribute_id for magento
             */
            $status_attribute_id = $this->db_connection->cell("SELECT attribute_id FROM {$this->prefix}eav_attribute WHERE attribute_code = 'status' AND entity_type_id = {$entity_type_id}", 'attribute_id');

            /**
             * if the disable_orphans is turned on, we dont want to turn the product on.
             * naming of attribute is confusing. This query turns on a product.
             * we will turn off products without images on the product load.
             * ditto for the disable_out_of_stock setting. If we turned it off for being out of stock we doing want to turn it back on.
             */
            $disable_orphan_join = ((isset($disable_orphans) && $disable_orphans == 1) ? " LEFT JOIN {$this->prefix}catalog_category_product ccp
ON ccp.product_id = image.entity_id " : '');

            $disable_orphan_where = ((isset($disable_orphans) && $disable_orphans == 1) ? " AND ccp.product_id IS NOT NULL " : '');
            $disable_out_of_stock_join = ((isset($disable_out_of_stock) && $disable_out_of_stock == 1) ? " INNER JOIN {$this->prefix}cataloginventory_stock_item csi
ON csi.product_id = image.entity_id " : '');
            $disable_out_of_stock_where = ((isset($disable_out_of_stock) && $disable_out_of_stock == 1) ? ' AND (csi.is_in_stock = 1 
AND (csi.manage_stock = 1 || csi.use_config_manage_stock = 1 )) ' : '');


            $sql = "UPDATE {$this->prefix}catalog_product_entity_varchar image 
            LEFT JOIN {$this->prefix}catalog_product_super_link  sl
            ON sl.product_id = image.entity_id
            JOIN {$this->prefix}catalog_product_entity_int stat
            ON stat.entity_id = image.entity_id
            AND stat.attribute_id = {$status_attribute_id}
            AND stat.value = 2
			{$disable_orphan_join}
			{$disable_out_of_stock_join}
            SET stat.value = 1
            WHERE  image.attribute_id = {$image_attribute_id}
            AND sl.parent_id IS NULL 
			{$disable_orphan_where}
			{$disable_out_of_stock_where}
			AND image.value != 'no_selection'";

            $this->db_connection->exec($sql);
        }
    }

    /**
     * Get category to product insert parameters from the cart.
     * 
     * @global rdi_field_mapping $field_mapping
     * @global rdi_debug $debug
     * 
     * @return array(fields,join,table,where)
     * @todo Get attribute_ids better.
     */
    public function get_category_product_insert_parameters()
    {
        global $field_mapping, $use_multisite, $root_category_id;
        //@setting $disable_product_sort_order [0-OFF, 1-ON]. Turn this on to disable updating the products within a category.

        /**
         * get the attribute type id
         */
        $entity_type_id = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');

        /**
         * get the related attribute id
         */
        $related_id_attribute_id = $this->db_connection->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_id' and entity_type_id = '{$entity_type_id}'", 'attribute_id');
        $related_parent_id_attribute_id = $this->db_connection->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_parent_id'  and entity_type_id = '{$entity_type_id}'", 'attribute_id');

        //$debug->write("magento_rdi_cart_catalog_load.php", "get_category_product_insert_parameters", "parameter ids", 1, array("entity_type_id" => $entity_type_id, "attribute_id"=> $related_id_attribute_id));            

        /**
         * create the temp table used to hold the new data
         */
        $sql = "CREATE TEMPORARY TABLE `{$this->prefix}catalog_category_product_temp` (
                        `category_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Category ID',
                        `product_id` INT(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT 'Product ID',
                        `position` INT(11) NOT NULL DEFAULT '0' COMMENT 'Position',
                        PRIMARY KEY (`category_id`, `product_id`),
                        INDEX `IDX_CATALOG_CATEGORY_PRODUCT_PRODUCT_ID` (`product_id`)	
                )";
        $this->db_connection->exec($sql);

        $fields = "category.entity_id AS 'category_id', related.entity_id AS 'product_id', ";

        $fields .= $field_mapping->map_field('category_product', 'position');

        $category_product_related_id = $field_mapping->map_field('category_product', 'related_id');
        $category_product_entity_id = $field_mapping->map_field('category_product', 'entity_id');

        $use_multisite_insert_sub = ((isset($use_multisite) && $use_multisite == 1) ? " (SELECT ccp.* FROM catalog_category_product ccp 
																						JOIN catalog_category_entity e
																						ON e.entity_id = ccp.category_id
																						AND e.path LIKE '%/{$root_category_id}/%') AS cp" : "  {$this->prefix}catalog_category_product cp ");


        return array(
            "fields" => $fields,
            "join" => "INNER JOIN {$this->prefix}catalog_category_entity category
                                          ON category.related_id = {$category_product_related_id}
                                    JOIN {$this->prefix}catalog_product_entity_varchar related 
                                          ON related.value = {$category_product_entity_id} 
                                          AND related.attribute_id = {$related_parent_id_attribute_id}
                                    LEFT JOIN {$this->prefix}catalog_product_super_link sl
                                          ON sl.product_id = related.entity_id
                                    LEFT JOIN {$use_multisite_insert_sub}
                                          ON cp.category_id = category.entity_id 
                                          AND cp.product_id = related.entity_id 						
						",
            "table" => "{$this->prefix}catalog_category_product_temp",
            "where" => "sl.product_id IS NULL"
        );
    }

    /**
     * Get an array that removes a product from a category
     * @return array(where)
     */
    public function get_category_product_removal_parameters()
    {
        $parameters = $this->get_category_product_insert_parameters();
        $parameters['where'] = '';
        return $parameters;
    }

    /**
     * an array of defined parameters to interject into the query so that they insert can occur only on records that need to be inserted
     * these values are parsed in the pos library to build the query used to get the categories
     * @global rdi_field_mapping $field_mapping
     * @return array('join','where')
     */
    public function get_category_insert_parameters()
    {
        global $field_mapping;

        return array(
            "join" => "left join {$this->prefix}catalog_category_entity on related_id = "
            . $field_mapping->map_field('category', 'related_id'),
            //"where" => "catalog_category_entity.entity_id is null"
            "where" => ""
        );
    }

    /**
     * an array of defined parameters to interject into the query so that they updates occur based on only what needs update
     * @global rdi_field_mapping $field_mapping
     * 
     * @return array(fields,join,where)
     * @todo Reduce attributes queries
     */
    public function get_category_update_parameters()
    {
        global $field_mapping, $store_id;
        //@setting $store_id The default store_id for Magento. This is usually the admin, 0.

        /**
         * get the attribute type id
         */
        //$entity_type_id = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_category'", 'entity_type_id');

        return array(
            "fields" => "{$this->prefix}catalog_category_entity.path",
            "join" => "inner join {$this->prefix}catalog_category_entity on related_id = "
            . $field_mapping->map_field('category', 'related_id') . "                                    
                                   left join {$this->prefix}catalog_category_entity_varchar on {$this->prefix}catalog_category_entity_varchar.entity_id = {$this->prefix}catalog_category_entity.entity_id and {$this->prefix}catalog_category_entity_varchar.attribute_id = {$this->attribute['name']}                                 
                                   left join {$this->prefix}catalog_category_entity_text on {$this->prefix}catalog_category_entity_text.entity_id = {$this->prefix}catalog_category_entity.entity_id and {$this->prefix}catalog_category_entity_text.attribute_id = {$this->attribute['description']}",
            "where" => "{$this->prefix}catalog_category_entity_varchar.store_id = {$store_id}
                                    and ((" . $field_mapping->map_field('category', 'name')
            . " != BINARY {$this->prefix}catalog_category_entity_varchar.value) or "
            . $field_mapping->map_field('category', 'position')
            . " != {$this->prefix}catalog_category_entity.position  
										OR
                                                                                (IFNULL(" . $field_mapping->map_field('category', 'description') . ",'') != BINARY IFNULL({$this->prefix}catalog_category_entity_text.value ,'')
                                                                                    
									
									
									))"
                //. " != catalog_category_entity_text.value and catalog_category_entity_text.store_id = 0) or catalog_category_entity_text.value_id is null))" //not sure why were looking for this text value id being null, but causes false positives
        );
    }

    /**
     * Process the category for insert / update into magento
     * 
     * @global rdi_debug $debug
     * 
     * @param array $category The current category being worked on.
     * @param string $arg The current path for the current category.
     * @return string The current path at the end of updating the category.
     * @todo Fix versioning.
     * @todo Attribute_ids need cleaning.
     */
    public function process_category_record($category, $arg)
    {
        global $update_categories, $product_cart_lib_ver, $root_category_id, $updated_categories;

		if(isset($category['entity_id']))
		{
			$updated_categories[] = $category['entity_id'];
		}
        //@setting $update_categories [0-OFF, 1-ON] Update an existing category.
        //@setting $product_cart_lib_ver String for the version lib
        //@setting $root_category_id The root category_id is set in the common_load. It will default to this setting if it does not find one. This is a per website setting. Usually 2.

        $this->check_catalog_lib_version();

        if (isset($this->parent_paths[$category['parent_id']]))
        {
            $path = $this->parent_paths[$category['parent_id']];
        }
        else
        {
            $path = $root_category_id == "1" ? "1" : "1/{$root_category_id}";
        }

        /**
         * check if this is an update passed in specially as an update
         */
        if ($arg == "update")
        {
            $this->parent_paths[$category['related_id']] = $category['path'];
            /**
             * get the related id
             */
            $entity_id = $this->db_connection->get_cell("{$this->prefix}catalog_category_entity", 'entity_id', "related_id = '{$category['related_id']}'");

            if ($entity_id !== false)
            {
                $category['entity_id'] = $entity_id;

                $updated_categories[] = $entity_id;

                return insertUpdateCategoryRecord($category);
            }

            /**
             * cant update the relation doesnt exist
             */
            return;
        }




        /**
         * set the category pathing
         */
        $category['path'] = isset($this->parent_paths[$category['parent_id']]) ? $this->parent_paths[$category['parent_id']] : ($root_category_id == "1" ? "1" : "1/{$root_category_id}");

        /**
         * get the related id
         */
        $entity_id = $this->db_connection->get_cell("{$this->prefix}catalog_category_entity", 'entity_id', "related_id = '{$category['related_id']}'");

        if ($entity_id !== false)
        {
            $category['entity_id'] = $entity_id;
            $category_id = $entity_id;
            $category['path'] = $this->db_connection->get_cell("{$this->prefix}catalog_category_entity", 'path', "related_id = '{$category['related_id']}'");
            $this->parent_paths[$category['related_id']] = $category['path'];
        }
        else
        {
            $category['entity_id'] = '';
            $category_id = insertUpdateCategoryRecord($category);
            $category['entity_id'] = $category_id;
        }

        /**
         * insert or update the current record
         */
        //$category_id = insertUpdateCategoryRecord($category);

        if ($category_id !== false && $category_id != $root_category_id)
        {
            $path = $path . '/' . $category_id;
        }

        $category['level'] = substr_count($path, '/');

        /**
         * if this is an update, we know the current path that came from the staging table, can compare it to the one in the database, and adjust if needed
         */
        if ($category_id > 0)
        {
            $cur_path = $this->db_connection->get_cell("{$this->prefix}catalog_category_entity", 'path', "related_id = '{$category['related_id']}'");

            if ($cur_path != $path)
            {
                if ($update_categories == 1)
                {
                    /**
                     * determine the parent
                     */
                    $path_values = explode("/", $path);

                    $parent_id = $path_values[sizeof($path_values) - 2];

                    if (isset($product_cart_lib_ver) && $product_cart_lib_ver !== '1.4.2')
                        $last_update = ", rdi_last_update = now()";
                    /**
                     * update the values, parent and the path
                     */
                    $this->db_connection->exec("update {$this->prefix}catalog_category_entity set path = '{$path}', parent_id = {$parent_id},level={$category['level']} {$last_update} where related_id = '{$category['related_id']}'");
                    $this->parent_paths[$category['related_id']] = $category['path'];
                }
            }
        }

        $this->parent_paths[$category['related_id']] = $path;

        return $path;
    }

    /**
     * A function that loops through an array only to pass it off to the current version.
     * @param array $product_category_relations Category Relations array from the POS.
     */
    public function set_product_category_relations($product_category_relations)
    {
        /**
         * roll through each of the relations
         * entity_id , category_id
         */
        foreach ($product_category_relations as $product_category_relation)
        {
            $this->echo_message("Found " . count($product_category_relations_for_removal) . " Product Category for Addition", 4);
            set_product_category_relation($product_category_relation['entity_id'], $product_category_relation['category_id']);
        }
    }

    /**
     * A function that loops through an array only to pass it off to the current version.
     * @param array $product_category_relations_for_removal  Category Relations array from the POS.
     */
    public function remove_product_category_relations($product_category_relations_for_removal)
    {
        /**
         * roll through each of the relations
         * entity_id , category_id
         */
        if (is_array($product_category_relations_for_removal))
        {
            $this->echo_message("Found " . count($product_category_relations_for_removal) . " Product Category for Removal", 4);

            foreach ($product_category_relations_for_removal as $product_category_relation)
            {
                remove_product_category_relation($product_category_relation['entity_id'], $product_category_relation['category_id']);
            }
        }
    }

    /**
     * 
     * @global rdi_lib $cart
     * @global rdi_debug $debug
     *  
     * @global rdi_hook $hook_handler
     * 
     * @return boolean
     * @todo Attribute_ids need fixing
     */
    public function set_product_category_relation()
    {
        global $disable_product_sort_order, $hook_handler, $set_nonorphans_findable, $hide_orphans, $enable_nonorphans, $disable_orphans, $use_multisite, $root_category_id, $product_require_image, $inserted_products;

        //@setting $disable_product_sort_order [0-OFF, 1-ON] Disables products sort order in a category.
        //@setting $verbose_queries [0-OFF, 1-ON] Used for testing and gives extra help for addons and other settings during the loading. See rdi_general for output functions.
        //@setting $set_nonorphans_findable [0-OFF, 1-ON] If a product is in a category, turn it on.
        //@setting $hide_orphans [0-OFF, 1-ON] Hide a product if it is not in a category.
        //@setting $enable_nonorphans [0-OFF, 1-ON] Turn on a product that is in a category.
        //@setting $disable_orphans [0-OFF, 1-ON] Turn off a product if it is not in a category.

        $use_multisite_insert_sub = ((isset($use_multisite) && $use_multisite == 1) ? " (SELECT ccp.* FROM {$this->prefix}catalog_category_product ccp 
																						JOIN {$this->prefix}catalog_category_entity e
																						ON e.entity_id = ccp.category_id
																						AND e.path LIKE '%/{$root_category_id}/%') AS {$this->prefix}catalog_category_product" : " {$this->prefix}catalog_category_product ");

        $use_multisite_insert_sub_ccp = ((isset($use_multisite) && $use_multisite == 1) ? " (SELECT ccp.* FROM {$this->prefix}catalog_category_product ccp 
																						JOIN {$this->prefix}catalog_category_entity e
																						ON e.entity_id = ccp.category_id
																						AND e.path LIKE '%/{$root_category_id}/%') AS ccp " : " {$this->prefix}catalog_category_product ccp ");
        $use_multisite_insert_sub_ccp = ((isset($use_multisite) && $use_multisite == 1) ? "  {$this->prefix}catalog_category_product ccp " : " {$this->prefix}catalog_category_product ccp ");

        $use_multisite_delete_where = ((isset($use_multisite) && $use_multisite == 1) ? " AND e.path like '%/{$root_category_id}/%'" : '');

        /**
         * here we need to determine which categories are added, which are removed, and which updated
         */
        $product_entity_type = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
        $related_parent_id_attribute_id = $this->db_connection->cell("Select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_parent_id' and entity_type_id = {$product_entity_type}", "attribute_id");
        $image_attribute_id = $this->db_connection->cell("Select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'image' and entity_type_id = {$product_entity_type}", "attribute_id");
        /**
         * insert new categories
         */
        $sql = "select {$this->prefix}catalog_category_product_temp.category_id, {$this->prefix}catalog_category_product_temp.product_id, {$this->prefix}catalog_category_product_temp.position from {$this->prefix}catalog_category_product_temp
                                left join {$use_multisite_insert_sub} 
								on {$this->prefix}catalog_category_product.category_id = {$this->prefix}catalog_category_product_temp.category_id and
                                {$this->prefix}catalog_category_product.product_id = {$this->prefix}catalog_category_product_temp.product_id
                                where {$this->prefix}catalog_category_product.category_id is null";

        $products_added = $this->db_connection->cells($sql, "product_id");

        $this->db_connection->exec("insert into {$this->prefix}catalog_category_product {$sql}");

        /**
         * remove the ones removed
         */
        $sql = "cp.* FROM {$this->prefix}catalog_category_product cp
                                LEFT JOIN {$this->prefix}catalog_category_product_temp 
                                ON cp.category_id = {$this->prefix}catalog_category_product_temp.category_id 
                                AND cp.product_id = {$this->prefix}catalog_category_product_temp.product_id
                                JOIN {$this->prefix}catalog_product_entity_varchar related
                                ON related.entity_id = cp.product_id
                                AND related.attribute_id = {$related_parent_id_attribute_id}
                                AND related.value IS NOT NULL
                                JOIN {$this->prefix}catalog_category_entity e
                                ON e.entity_id = cp.category_id
                                AND e.related_id IS NOT NULL
								{$use_multisite_delete_where}
                                WHERE {$this->prefix}catalog_category_product_temp.category_id IS NULL";

        $products_removed = $this->db_connection->cells("SELECT {$sql}", 'product_id');

        $this->db_connection->exec("DELETE {$sql}");


        $inserted_products = array_merge(array_merge($inserted_products, $products_added), $products_removed);

        if (isset($disable_product_sort_order) && $disable_product_sort_order == 1)
        {
            
        }
        else
        {
            /**
             * update sort order changes
             * The only products inserted into the temp are of the current website, no need to check here.
             */
            $this->db_connection->exec("update {$this->prefix}catalog_category_product_temp
                                    left join {$this->prefix}catalog_category_product 
                                    on {$this->prefix}catalog_category_product.category_id = {$this->prefix}catalog_category_product_temp.category_id 
                                    and {$this->prefix}catalog_category_product.product_id = {$this->prefix}catalog_category_product_temp.product_id
                                    set {$this->prefix}catalog_category_product.position = {$this->prefix}catalog_category_product_temp.position
                                    where {$this->prefix}catalog_category_product.category_id is not null 
                                    and {$this->prefix}catalog_category_product_temp.position != {$this->prefix}catalog_category_product.position");
        }

        $status_attribute_id = $this->db_connection->cell("Select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'status' and entity_type_id = {$product_entity_type}", "attribute_id");
        $deactivated_date_attribute_id = $this->db_connection->cell("Select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'rdi_deactivated_date' and entity_type_id = {$product_entity_type}", "attribute_id");
        $visibility_attribute_id = $this->db_connection->cell("Select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'visibility'  and entity_type_id = {$product_entity_type}", "attribute_id");
        $related_id_attribute_id = $this->db_connection->cell("Select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_id'  and entity_type_id = {$product_entity_type}", "attribute_id");


        /**
         * check for products to disable due to not being in our catalog
         * new version for this
         */
        $criteria = array();

        if (isset($hide_orphans) && $hide_orphans == 1)
        {
            $criteria[] = " vis.value != 1 ";
        }

        if (isset($disable_orphans) && $disable_orphans == 1)
        {
            $criteria[] = " st.value = 1 ";
        }

        if (count($criteria) > 0)
        {
            $this->_echo("SET RDI deactivated date", 'h1');

            $this->set_disable_for_stock_criteria('dt');


            /**
             * set the deactived date time for these
             */
            $sql = "UPDATE {$this->prefix}catalog_product_entity_datetime dt
            LEFT JOIN {$this->prefix}catalog_product_super_link sl
            ON sl.product_id = dt.entity_id

            LEFT JOIN {$use_multisite_insert_sub_ccp}
            ON ccp.product_id = dt.entity_id";

            $sql .= $this->disable_out_of_stock['join'];

            $sql .= (isset($disable_orphans) && $disable_orphans == 1) ? " JOIN {$this->prefix}catalog_product_entity_int st
            on st.entity_id = dt.entity_id
            and st.attribute_id = {$status_attribute_id}" : "";

            $sql .= (isset($hide_orphans) && $hide_orphans == 1) ? " JOIN {$this->prefix}catalog_product_entity_int vis
            on vis.entity_id = dt.entity_id
            and vis.attribute_id = {$visibility_attribute_id}" : "";

            $sql .= " JOIN {$this->prefix}catalog_product_entity_varchar related_id
            ON related_id.entity_id = dt.entity_id
            AND related_id.attribute_id = {$related_id_attribute_id} 
            AND related_id.value IS NOT NULL AND related_id.value != ''

            SET dt.value = NOW()

            WHERE 
            dt.attribute_id = {$deactivated_date_attribute_id}   
            AND sl.product_id IS NULL
            AND ccp.product_id IS NULL
            AND (" . implode(" OR ", $criteria) . ")";

            $this->db_connection->exec($sql);


            /**
             * remove set the date to xmas 3000 for products all in a category
             */
            $sql = "UPDATE {$this->prefix}catalog_product_entity_datetime dt
            LEFT JOIN {$this->prefix}catalog_product_super_link sl
            ON sl.product_id = dt.entity_id

            JOIN {$use_multisite_insert_sub_ccp}
            ON ccp.product_id = dt.entity_id
            
            {$this->disable_out_of_stock['join']}

            JOIN {$this->prefix}catalog_product_entity_varchar related_id
            ON related_id.entity_id = dt.entity_id
            AND related_id.attribute_id = {$related_id_attribute_id} 
            AND related_id.value IS NOT NULL AND related_id.value != ''

            SET dt.value = '3000-12-25 12:00:00'

            WHERE 
            dt.value != '3000-12-25 12:00:00'
            AND dt.attribute_id = {$deactivated_date_attribute_id}   
            AND sl.product_id IS NULL
            {$this->disable_out_of_stock['where']}
            ";

            $this->db_connection->exec($sql);
        }

        $sql_product_require_image = (isset($product_require_image) && $product_require_image == 1) ? " JOIN {$this->prefix}catalog_product_entity_varchar image ON image.entity_id = st.entity_id AND image.attribute_id = {$image_attribute_id} and image.value != 'no_selection' " : "";


        if (isset($enable_nonorphans) && $enable_nonorphans == 1)
        {
            $this->_echo("ENABLE Products in Category. SETTING:enable_nonorphans");

            $this->set_disable_for_stock_criteria('st');
            /**
             *  these products are disable, but in a category, so we enable them
             */
            $sql = "UPDATE {$this->prefix}catalog_product_entity_int st
            LEFT JOIN {$this->prefix}catalog_product_super_link sl
            ON sl.product_id = st.entity_id

            JOIN {$use_multisite_insert_sub_ccp}
            ON ccp.product_id = st.entity_id
            {$this->disable_out_of_stock['join']}
                
            {$sql_product_require_image}
                
            JOIN {$this->prefix}catalog_product_entity_varchar related_id
            ON related_id.entity_id = st.entity_id
            AND related_id.attribute_id = {$related_id_attribute_id}
            AND ifnull(related_id.value,'') != ''

            SET st.value = 1
            WHERE 
            st.attribute_id = {$status_attribute_id}   AND st.value = 2
            AND sl.product_id IS NULL
            {$this->disable_out_of_stock['where']}
            ";
            $this->db_connection->exec($sql);
        }

        if (isset($disable_orphans) && $disable_orphans == 1)
        {
            $this->_echo("DISABLE Products not in Category. SETTING: disable_orphans");

            // $this->set_disable_for_stock_criteria('st');

            /**
             *  these products are enabled, but not in a category,  so we disable them
             */
            $sql = "UPDATE {$this->prefix}catalog_product_entity_int st
            LEFT JOIN {$this->prefix}catalog_product_super_link sl
            ON sl.product_id = st.entity_id

            LEFT JOIN {$use_multisite_insert_sub_ccp}
            ON ccp.product_id = st.entity_id
            
            JOIN {$this->prefix}catalog_product_entity_varchar related_id
            ON related_id.entity_id = st.entity_id
            AND related_id.attribute_id = {$related_id_attribute_id}
            AND related_id.value IS NOT NULL AND related_id.value != ''

            SET st.value = 2
            WHERE 
            st.attribute_id = {$status_attribute_id}   AND st.value = 1
            AND sl.product_id IS NULL
            AND ccp.product_id IS NULL
            ";
            $this->db_connection->exec($sql);
        }


        if (isset($set_nonorphans_findable) && $set_nonorphans_findable == 1)
        {
            $this->_echo("SHOW Products in Category. SETTING: set_nonorphans_findable");

            $this->set_disable_for_stock_criteria('vis');

            /**
             * show products
             */
            $sql = "UPDATE {$this->prefix}catalog_product_entity_int vis
            LEFT JOIN {$this->prefix}catalog_product_super_link sl
            ON sl.product_id = vis.entity_id

            JOIN {$use_multisite_insert_sub_ccp}
            ON ccp.product_id = vis.entity_id
            {$this->disable_out_of_stock['join']}
            JOIN {$this->prefix}catalog_product_entity_varchar related_id
            ON related_id.entity_id = vis.entity_id
            AND related_id.attribute_id = {$related_id_attribute_id}
            AND related_id.value IS NOT NULL AND related_id.value != ''

            SET vis.value = 4

            WHERE 
            vis.attribute_id = {$visibility_attribute_id}   
            AND vis.value = 1
            AND sl.product_id IS NULL
            {$this->disable_out_of_stock['where']}
            ";

            $this->db_connection->exec($sql);
        }

        if (isset($hide_orphans) && $hide_orphans == 1)
        {
            $this->_echo("HIDE Products not in Category. SETTING: hide_orphans");

            $this->set_disable_for_stock_criteria('vis');

            /**
             *  hide these
             */
            $sql = "UPDATE {$this->prefix}catalog_product_entity_int vis
            LEFT JOIN {$this->prefix}catalog_product_super_link sl
            ON sl.product_id = vis.entity_id

            LEFT JOIN {$use_multisite_insert_sub_ccp}
            ON ccp.product_id = vis.entity_id
            
            JOIN {$this->prefix}catalog_product_entity_varchar related_id
            ON related_id.entity_id = vis.entity_id
            AND related_id.attribute_id = {$related_id_attribute_id}
            AND related_id.value IS NOT NULL AND related_id.value != ''

            SET vis.value = 1

            WHERE 
            vis.attribute_id = {$visibility_attribute_id}   
            AND vis.value = 4
            AND sl.product_id IS NULL
            AND ccp.product_id IS NULL
            ";

            $this->db_connection->exec($sql);
        }

        $hook_handler->call_hook("cart_catalog_load_product_category_relation");
        /**
         * kill temp table
         */
        $this->db_connection->exec("drop table {$this->prefix}catalog_category_product_temp;");

        return true;
    }

    /**
     * Not used.
     * @global type $cart
     * @global rdi_debug $debug
     * @global type $store_id
     */
    private function reindex_category_urls()
    {
        global $cart, $store_id, $root_category_id;

        //get the type id
        $product_entity_type_id = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
        $catalog_entity_type_id = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_category'", 'entity_type_id');

        //get the visibilit id
        $sql = "select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'name' and entity_type_id = {$product_entity_type_id}";
        $name_id = $this->db_connection->cell($sql, 'attribute_id');

        //get the visibilit id
        $sql = "select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'visibility' and entity_type_id = {$product_entity_type_id}";
        $visibility_id = $this->db_connection->cell($sql, 'attribute_id');

        //get the url path attribute id
        $sql = "select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'url_path' and entity_type_id = {$product_entity_type_id}";
        $url_path_id = $this->db_connection->cell($sql, 'attribute_id');
//        
//         //get the catalog name attribute id
        $sql = "select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'name' and entity_type_id = {$catalog_entity_type_id}";
        $name_id = $this->db_connection->cell($sql, 'attribute_id');
//
//        //get the path data
        $sql = "select distinct {$this->prefix}catalog_product_entity.entity_id, {$this->prefix}catalog_product_entity_varchar.value 
                from {$this->prefix}catalog_product_entity
                inner join {$this->prefix}catalog_product_entity_varchar 
                on {$this->prefix}catalog_product_entity_varchar.attribute_id = {$url_path_id} 
                and {$this->prefix}catalog_product_entity_varchar.entity_id = catalog_product_entity.entity_id
                inner join {$this->prefix}catalog_product_entity_int 
                on {$this->prefix}catalog_product_entity_int.attribute_id = {$visibility_id} 
                and {$this->prefix}catalog_product_entity_int.value > 1 
                and {$this->prefix}catalog_product_entity_int.entity_id = {$this->prefix}catalog_product_entity.entity_id
                left {$this->prefix}join core_url_rewrite 
                on {$this->prefix}core_url_rewrite.product_id = {$this->prefix}catalog_product_entity.entity_id 
                and {$this->prefix}core_url_rewrite.request_path = catalog_product_entity_varchar.value
                where {$this->prefix}core_url_rewrite.url_rewrite_id is null";

        $url_data = $this->db_connection->rows($sql);

        //get the category relation data
        $sql = "Select {$this->prefix}catalog_category_product.category_id, {$this->prefix}catalog_category_product.product_id, {$this->prefix}catalog_category_entity.path from {$this->prefix}catalog_category_product
                inner join {$this->prefix}catalog_category_entity on {$this->prefix}catalog_category_entity.entity_id = {$this->prefix}catalog_category_product.category_id";
        $relations = $this->db_connection->rows($sql, 'product_id');


        $category_lookup = array();

        //get all the categories
        $sql = "select {$this->prefix}catalog_category_entity.entity_id, catalog_category_entity.path, catalog_name.value as catalog_name from {$this->prefix}catalog_category_entity 
                    inner join {$this->prefix}catalog_category_entity_varchar catalog_name on catalog_name.entity_id = catalog_category_entity.entity_id and catalog_name.attribute_id = {$name_id}
                    ";

        $category_data = $this->db_connection->rows($sql);


        if (is_array($category_data))
        {
            foreach ($category_data as $cat)
            {
                $id_path = "category/{$cat['entity_id']}";
                $target_path = "catalog/category/view/id/{$cat['entity_id']}";

                //take the path and build out the named path
                $path_ids = str_replace('/', ',', $cat['path']);

                $new_path = '';
                if ($path_ids != '')
                {
                    $sql = "select distinct GROUP_CONCAT(value) as path from {$this->prefix}catalog_category_entity_varchar where entity_id in ({$path_ids}) and entity_id not in (1,2) and attribute_id = {$name_id}";
                    $new_path = $this->db_connection->cell($sql, 'path');

                    $new_path = str_replace(',', '/', $new_path);
                    $new_path = str_replace(' ', '-', strtolower($new_path));

                    if ($new_path != '')
                    {
                        $new_path .= ".html";

                        $sql = "insert INTO core_url_rewrite (store_id, id_path, request_path, target_path, is_system, category_id) 
                                                        values 
                                                             (  
                                                                {$store_id},
                                                                '{$id_path}',
                                                                '{$new_path}',
                                                                '{$target_path}',
                                                                1,
                                                                {$cat['entity_id']}
                                                             ) on duplicate key update request_path = '{$new_path}'";


                        $this->db_connection->exec($sql);
                    }
                }
            }
        }

        //set the category product rewrites
        if (is_array($url_data) && $relations !== false)
        {
            foreach ($url_data as $u)
            {
                $id_path = "product/{$u['entity_id']}";
                $target_path = "catalog/product/view/id/{$u['entity_id']}";
                if (isset($relations[$u['entity_id']]))
                {
                    //take the path and build out the named path
                    $path_ids = str_replace('/', ',', $relations[$u['entity_id']]['path']);

                    $new_path = '';
                    if ($path_ids != '')
                    {
                        $sql = "select distinct GROUP_CONCAT(value) as path from {$this->prefix}catalog_category_entity_varchar where entity_id in ({$path_ids}) and entity_id not in (1,{$root_category_id}) and attribute_id = {$name_id}";
                        $new_path = $this->db_connection->cell($sql, 'path');

                        $new_path = str_replace(',', '/', $new_path);
                        $new_path = str_replace(' ', '-', strtolower($new_path));
                        $new_path .= '/';
                    }

                    $id_path .= "/{$relations[$u['entity_id']]['category_id']}";
                    $target_path .= "/category/{$relations[$u['entity_id']]['category_id']}";

                    $sql = "insert INTO core_url_rewrite (store_id, id_path, request_path, target_path, is_system, product_id) 
                                                    values 
                                                         (  
                                                            {$store_id},
                                                            '{$id_path}',
                                                            '{$new_path}{$u['value']}',
                                                            '{$target_path}',
                                                            1,
                                                            {$u['entity_id']}
                                                         ) on duplicate key update request_path = '{$new_path}{$u['value']}'";

                    $this->db_connection->exec($sql);
                }
            }
        }
    }

    /**
     * All the categories that have enabled products in them.
     * @return StringQuery
     * @todo Attribute needs fixing.
     */
    private function get_category_usage_query()
    {
        global $use_multisite, $root_category_id;


        $use_multisite_where = ((isset($use_multisite) && $use_multisite == 1) ? " AND cce.path like '%/{$root_category_id}/%'" : '');

        $status_attribute = $this->db_connection->cell("SELECT attribute_id FROM {$this->prefix}eav_attribute
                                                INNER JOIN {$this->prefix}eav_entity_type on {$this->prefix}eav_entity_type.entity_type_id = {$this->prefix}eav_attribute.entity_type_id
                                                WHERE attribute_code in('status') 
                                                AND {$this->prefix}eav_entity_type.entity_type_code = 'catalog_product'", "attribute_id");

        return "SELECT REPLACE(cce.path, '/', ',') as ids
                FROM {$this->prefix}catalog_category_entity cce
                LEFT JOIN {$this->prefix}catalog_category_product ON {$this->prefix}catalog_category_product.category_id = cce.entity_id
                    
                LEFT JOIN {$this->prefix}catalog_product_entity_int st
                ON st.entity_id = {$this->prefix}catalog_category_product.product_id
                 AND st.attribute_id = {$status_attribute}
                  AND st.value = 1
                WHERE related_id is NOT NULL
				{$use_multisite_where}
                GROUP BY cce.entity_id";
    }

    /**
     * Disables categories that not longer have active products or children with active categories.
     * 
     * @todo Add setting dont_disable_unused_categories
     * @todo Attribute fixin
     */
    public function disable_unused_categories()
    {
        global $dont_disable_unused_categories, $use_multisite, $root_category_id;
        //@setting dont_disable_unused_categories
        $this->_echo(__FUNCTION__);
        $this->echo_message("Checking for categories to disable without enabled products ", 3);

        $status_attribute = $this->db_connection->cell("SELECT attribute_id FROM {$this->prefix}eav_attribute
                                                INNER JOIN {$this->prefix}eav_entity_type on {$this->prefix}eav_entity_type.entity_type_id = {$this->prefix}eav_attribute.entity_type_id
                                                WHERE attribute_code in('status') 
                                                AND {$this->prefix}eav_entity_type.entity_type_code = 'catalog_product'", "attribute_id");

        /**
         * get the attribute type id
         */
        $entity_type_id = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_category'", 'entity_type_id');

        /**
         * get the is_active attribute_id 
         */
        $is_active_id = $this->attribute['is_active'];

        //$rows = $this->db_connection->rows($this->get_category_usage_query() . " having count({$this->prefix}catalog_category_product.product_id) = 0");
        $rows = $this->db_connection->rows($this->get_category_usage_query() . " HAVING SUM(st.value) IS NULL ");

        $use_multisite_where = ((isset($use_multisite) && $use_multisite == 1) ? " AND cce.path like '%/{$root_category_id}/%'" : '');

        /**
         * make a list of the categories that are to be left alone before we disable anything
         * added 'WHERE related_id is NOT NULL' to ignore status on categories RDi did not create.
         */
        $enabled_list = array();
        /* $enabled_rows = $this->db_connection->rows("SELECT cce.path FROM {$this->prefix}catalog_category_entity cce 
          LEFT JOIN {$this->prefix}catalog_category_product
          ON {$this->prefix}catalog_category_product.category_id = cce.entity_id
          WHERE related_id is NOT NULL
          GROUP BY cce.entity_id
          having count({$this->prefix}catalog_category_product.product_id) > 0 "); */

        $enabled_rows = $this->db_connection->rows("SELECT cce.path FROM {$this->prefix}catalog_category_entity cce 
													LEFT JOIN {$this->prefix}catalog_category_product 
													ON {$this->prefix}catalog_category_product.category_id = cce.entity_id 
													LEFT JOIN {$this->prefix}catalog_product_entity_int st 
													ON st.entity_id = {$this->prefix}catalog_category_product.product_id 
													AND st.attribute_id = {$status_attribute} 
													AND st.value = 1 
													WHERE related_id is NOT NULL 
													{$use_multisite_where}
													GROUP BY cce.entity_id 
													HAVING SUM(st.value) IS NOT NULL ");
        $this->_var_dump($enabled_rows);
        if (!empty($enabled_rows))
        {
            /**
             * builds out a list of the ids that are enabled
             */
            foreach ($enabled_rows as $row)
            {
                $ids = explode("/", $row['path']);

                $ids = array_diff($ids, $enabled_list);

                $enabled_list = array_merge($enabled_list, $ids);
            }
            $this->_var_dump($enabled_list);
            $enable_list = array_unique($enabled_list);
        }

        $disable_list = array();

        /**
         * process the rows that need to be disabled
         */
        foreach ($rows as $row)
        {
            $disable_ids = explode(',', $row['ids']);

            $disable_ids = array_diff($disable_ids, $enabled_list);

            $disable_list = array_merge($disable_list, $disable_ids);
        }

        $ids = array_filter(array_unique($disable_list));

        if (!empty($ids))
        {
            $this->echo_message("Found " . count($ids) . " Categories to disable for not having enabled products.", 4);
            foreach (array_chunk($ids, 200) as $idss)
            {
                $this->db_connection->exec("update {$this->prefix}catalog_category_entity_int set value = 0 where attribute_id = {$is_active_id} and entity_id in (" . implode(",", $idss) . ")");
            }
        }
    }

    /**
     * Enables categories that have enabled products or children with categories enabled.
     * @todo Attribute Fixin
     */
    public function enable_used_categories()
    {
        $this->_echo(__FUNCTION__);

        $this->echo_message("Checking for categories to Enable with enabled products ", 3);
        /**
         * get the attribute type id
         */
        $entity_type_id = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_category'", 'entity_type_id');

        /**
         * get the is_active attribute_id 
         */
        $is_active_id = $this->attribute['is_active'];

        //$rows = $this->db_connection->rows($this->get_category_usage_query() . " having count({$this->prefix}catalog_category_product.product_id) > 0");
        $rows = $this->db_connection->rows($this->get_category_usage_query() . " HAVING SUM(st.value) IS NOT NULL ");

        $enabled_list = array();

        if (is_array($rows))
        {

            foreach ($rows as $row)
            {
                $ids = explode(",", $row['ids']);

                $ids = array_diff($ids, $enabled_list);

                $enabled_list = array_merge($enabled_list, $ids);
            }

            $ids = array_filter(array_unique($enabled_list));

            if (!empty($ids))
            {

                $this->echo_message("Found " . count($ids) . " Categories to enable for having enabled products.", 4);
                foreach (array_chunk($ids, 200) as $idss)
                {
                    $this->db_connection->exec("update {$this->prefix}catalog_category_entity_int set value = 1 where attribute_id = {$is_active_id} and entity_id in (" . implode(",", $idss) . ")");
                }
            }
        }
    }

    /**
     * Some customers will need to use the fact that the product is not in their catalog to remove/disable it from the site.
     * PMB 080162013
     * This will need to be called in an addon with global $cart->get_processor("rdi_cart_catalog_load")->product_status_without_category_load();
     * 
     * @global rdi_lib $cart
     * @global rdi_lib $pos
     * @global staging lib $db_lib
     * @todo Use the db_lib instead of creating a new lib that is the same thing already.
     * @todo Attribute Fixin
     */
    public function product_status_without_category_load()
    {
        global $cart, $pos, $db_lib;

        if ($db_lib->get_category_count() > 0)
        {
            $staging_lib = $pos->get_processor("rdi_staging_db_lib");

            $product_entity_type_id = $this->db_connection->cell("SELECT entity_type_id FROM {$this->prefix}eav_entity_type WHERE entity_type_id = 'catalog_product'", "entity_type_id");

            $related_parent_id = $this->db_connection->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_parent_id' and entity_type_id = {$product_entity_type_id}", 'attribute_id');

            $status = $this->db_connection->cell("SELECT attribute_id FROM {$this->prefix}eav_attribute WHERE attribute_code = 'status' AND entity_type_id = {$product_entity_type_id}", 'attribute_id');

            $sql = "update {$this->prefix}catalog_product_entity_int st
			join {$this->prefix}catalog_product_entity_varchar related_parent_id
			on related_parent_id.entity_id = st.entity_id
			and related_parent_id.attribute_id = {$related_parent_id}
			left join " . $staging_lib->get_table_name("in_catalog") . " c
			on c.style_sid = related_parent_id.value
			left join {$this->prefix}catalog_product_super_link sl
			on sl.product_id = st.entity_id
			SET st.value = 2
			where st.attribute_id = {$status} 
				and sl.product_id is null 
				and c.style_sid is null";

            $this->db_connection->exec($sql);

            $visibility = $this->db_connection->cell("SELECT attribute_id FROM {$this->prefix}eav_attribute WHERE attribute_code = 'visibility' AND entity_type_id = {$product_entity_type_id}", 'attribute_id');

            $sql = "update {$this->prefix}catalog_product_entity_int st
			join {$this->prefix}catalog_product_entity_varchar related_parent_id
			on related_parent_id.entity_id = st.entity_id
			and related_parent_id.attribute_id = {$related_parent_id}
			left join " . $staging_lib->get_table_name("in_catalog") . " c
			on c.style_sid = related_parent_id.value
			left join {$this->prefix}catalog_product_super_link sl
			on sl.product_id = st.entity_id
			SET st.value = 1
			where st.attribute_id = {$visibility} 
				and sl.product_id is null 
				and c.style_sid is null";

            $this->db_connection->exec($sql);
        }
    }

    /**
     * Getting the criteria for a product to be disabled for being out of stock. This could be moved to the products class and spread out to the places its needed. The avail update has it own version of this that could/should be shared.
     * 
     * @param type $join_table_name
     * @todo Rethink the order of is_in_stock criteria.
     */
    public function set_disable_for_stock_criteria($join_table_name)
    {
        global $disable_out_of_stock;
        //@setting $disable_out_of_stock [0-OFF, 1-ON] Disable a product for not being in stock. See function discription for more information.

        $this->disable_out_of_stock = array();

        $this->disable_out_of_stock['join'] = ((isset($disable_out_of_stock) && $disable_out_of_stock == 1) ? " INNER JOIN {$this->prefix}cataloginventory_stock_item csi
    ON csi.product_id = {$join_table_name}.entity_id " : '');
        $this->disable_out_of_stock['where'] = ((isset($disable_out_of_stock) && $disable_out_of_stock == 1) ? ' AND ((csi.is_in_stock = 1 
AND (csi.manage_stock = 1 || csi.use_config_manage_stock = 1 )) OR csi.manage_stock = 0) ' : '');
    }

    /**
     * If a simple if removed from a configurable or the equivalent in the POS, the products will be orphaned. Viewed as stand alone products, assigned to a category and visible on the site. This cleans them up. Although not the most elegante.
     * @return \rdi_cart_catalog_load
     * @todo A version of this attribute function needs to be shared.
     */
    public function clean_up_exclude_from_web()
    {

        $_attributes = $this->db_connection->cells("SELECT 
                                                         attribute_code,attribute_id FROM {$this->prefix}eav_attribute
                                                        INNER JOIN {$this->prefix}eav_entity_type on {$this->prefix}eav_entity_type.entity_type_id = {$this->prefix}eav_attribute.entity_type_id
                                                        WHERE attribute_code in('related_id','related_parent_id','status','visibility') 
                                                        AND {$this->prefix}eav_entity_type.entity_type_code = 'catalog_product'", "attribute_id", "attribute_code");

        $_sids = $this->db_connection->cells("CREATE TEMPORARY TABLE rdi_temp_sids_exclude_web (INDEX(sid)) SELECT DISTINCT `value` as sid FROM {$this->prefix}catalog_product_entity_varchar v
                                                                JOIN {$this->prefix}catalog_product_entity e
                                                                ON e.entity_id = v.entity_id
                                                                AND e.type_id = 'configurable'
                                                                WHERE v.attribute_id = {$_attributes['related_parent_id']}", 'sid');

        $sids = implode("','", $_sids);

        $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_int v
                                                        JOIN {$this->prefix}catalog_product_entity e
                                                        ON e.entity_id = v.entity_id
                                                        AND e.type_id = 'simple'
                                                        LEFT JOIN {$this->prefix}catalog_product_super_link sl
                                                        ON sl.product_id = e.entity_id
                                                        JOIN {$this->prefix}catalog_product_entity_varchar r
                                                        ON r.entity_id = v.entity_id
                                                        AND  r.attribute_id = {$_attributes['related_parent_id']}
                                                        JOIN rdi_temp_sids_exclude_web web
                                                        on web.sid = r.value
                                                        SET v.value = 1
                                                        WHERE v.attribute_id = {$_attributes['visibility']}
                                                        AND sl.product_id IS NULL");


        $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_int v
                                                        JOIN {$this->prefix}catalog_product_entity e
                                                        ON e.entity_id = v.entity_id
                                                        AND e.type_id = 'simple'
                                                        LEFT JOIN {$this->prefix}catalog_product_super_link sl
                                                        ON sl.product_id = e.entity_id
                                                        JOIN {$this->prefix}catalog_product_entity_varchar r
                                                        ON r.entity_id = v.entity_id
                                                        AND  r.attribute_id = {$_attributes['related_parent_id']}
                                                        JOIN rdi_temp_sids_exclude_web web
                                                        on web.sid = r.value
                                                        SET v.value = 2
                                                        WHERE v.attribute_id = {$_attributes['status']}
                                                        AND sl.product_id IS NULL");


        $this->db_connection->exec("DELETE cp.* FROM {$this->prefix}catalog_category_product cp
                                                        JOIN {$this->prefix}catalog_product_entity e
                                                        ON e.entity_id = cp.product_id
                                                        AND e.type_id = 'simple'
                                                        LEFT JOIN {$this->prefix}catalog_product_super_link sl
                                                        ON sl.product_id = e.entity_id
                                                        JOIN {$this->prefix}catalog_product_entity_varchar r
                                                        ON r.entity_id = cp.product_id
                                                        AND  r.attribute_id = {$_attributes['related_parent_id']}
                                                        JOIN rdi_temp_sids_exclude_web web
                                                        on web.sid = r.value
                                                        WHERE sl.product_id IS NULL");

        $this->db_connection->exec("DROP TABLE rdi_temp_sids_exclude_web");

        return $this;
    }

    public function set_root_category_id()
    {
        global $root_category_id;

        if (!isset($root_category_id) && strlen($root_category_id) == 0)
        {
            $GLOBALS['root_category_id'] = $this->db_connection->cell("SELECT root_category_id FROM {$this->prefix}core_website w
                                                                        JOIN {$this->prefix}core_store_group sg
                                                                        ON sg.website_id = w.website_id
                                                                         WHERE is_default = 1", "root_category_id");
        }
    }

    /**
     * Clean out old categories after they have been inactive for a while.
     * @global setting $clean_old_categories [0-OFF, >0 ON with the integer value being the number of inactive days before deletion.
     */
    public function clean_old_categories()
    {
        global $clean_old_categories;

        if (isset($clean_old_categories) && $clean_old_categories > 0)
        {
            $this->db_connection->cells("SELECT 
                                        c.entity_id 
                                FROM {$this->prefix}catalog_category_entity c
                                INNER JOIN {$this->prefix}catalog_category_entity_int ci 
                                ON ci.entity_id = c.entity_id
                                and ci.attribute_id = {$this->attribute['is_active']}
                                and ci.value = 0
                                WHERE c.rdi_inactive_date < ADDDATE(NOW(), INTERVAL - {$clean_old_categories} DAY)", 'entity_id');

            $categories = db_rows($sql);

            if (!empty($categories))
            {
                foreach ($categories as $category)
                {
                    $this->create_category_backup($category);

                    if (is_numeric($category))
                    {
                        $this->db_connection->exec("DELETE FROM {$this->prefix}catalog_category_entity WHERE entity_id = {$category}");
                    }
                }
            }
        }
    }

    /**
     * @todo This will save a file in the out archive with a json respresentation of the category that can be retrieved later
     */
    public function create_category_back_up()
    {
        return false;
    }

    /**
     * The terrible version lib goes to the version libs folder looking for the version setting. 
     * This should be an extended class from category and then that class is brought in and functions called from there. 
     * 
     * @global rdi_debug $debug
     * @todo kill the versioning functions with a pointy stick. Moving this to the get_processor lib in an extended class function.
     */
    private function check_catalog_lib_version()
    {
        global $catalog_cart_lib_ver;

        //@setting $catalog_cart_lib_ver Right now only 1.6.x is acceptable. All version diffs are handled by getting the version from Magento and checking in the few places that are different.
        //$debug->write("magento_rdi_cart_catalog_load.php", "check_catalog_lib_version", "checking cat lib version", 0, array("catalog_cart_lib_ver" => $catalog_cart_lib_ver));

        require_once "libraries/cart_libs/magento/version_libs/{$catalog_cart_lib_ver}/magento_rdi_catalog_lib.php";
    }

}

?>
