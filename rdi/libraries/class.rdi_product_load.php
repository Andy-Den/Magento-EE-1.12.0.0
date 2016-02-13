<?php

/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Product load class
 *
 * Handles the loading of the product data, does the traffic cop work on that part
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2012 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Load\Product
 */
class rdi_product_load extends rdi_general {

    /**
     * Constructor
     * @param rdi_db $db
     */
    public function rdi_product_load($db = '')
    {
        if ($db)
            $this->set_db($db);
    }

    /**
     * control the loading of products
     *
     * @global benchmarker $benchmarker
     * @global rdi_lib $pos
     * @global rdi_lib $cart
     * @setting $insert_products
     * @setting $update_products
     * @setting $update_type_product
     * @setting $field_mapping
     * @setting $insert_upsell
     * @setting $update_upsell
     * @setting $hook_handler
     * @setting $avail_stock_update
     */
    public function load_products()
    {
        global $benchmarker, $pos, $cart, $insert_products, $update_products, $update_type_product, $field_mapping, $hook_handler, $avail_stock_update, $use_attribute_mapping, $test_product_block_count;

        $benchmarker->log_memory_usage("class.rdi_product_load.php", "Starting product load");

        if (isset($use_attribute_mapping) && $use_attribute_mapping == '1')
        {
            $field_mapping->get_attributes_mapping();
        }

        /**
         * get the product type schemas from the cart module
         */
        //$product_type_mapping = $cart->get_processor("rdi_cart_product_load")->get_product_type_mapping();
        $product_classes = $this->get_product_classes();


        //@hook rdi_product_load_load_products_product_classes.php. Use this to add some kind of clean function. Product_classes is passed as reference.
        $hook_handler->call_hook(__CLASS__ . "_" . __FUNCTION__ . "_product_classes", $product_classes);

        //ob_flush();

        /**
         * loop through each type and get the products of that type
         */
        foreach ($product_classes as $product_class)
        {
			$product_class_name = $this->get_product_class_name($product_class);

			if(isset($test_product_block_count) && $test_product_block_count == 1)
			{
				$product_insert_parameters = $cart->get_processor("rdi_cart_product_load")->get_product_insert_parameters($product_class, $product_type);
				$product_insert_parameters['join'] = '';
				$product_insert_parameters['where'] = '';
				$product_insert_parameters['fields'] = 'count(*) as test_product_count';
				
				// count possibily send in a parameter to skip the field mapping build.
				$product_record_count = $pos->get_processor("rdi_pos_product_load")->get_product_data($product_class, $product_type['product_type'], $product_insert_parameters);
				
				if(empty($product_record_count) || $product_record_count[0]['test_product_count'] == 0)
				{					
					$this->echo_message("No Products for {$product_class_name}");
					
					continue;
				}
			}
			
            foreach ($product_class['product_types'] as $product_type)
            {
                //@setting $avail_stock_update  Lower stock from available records. If the setting is not used then we know the pos doesnt support it and its skipped. Handle simple qty updates, assuming the pos installed supports that, which is also why there is a setting here
				//this is not in the write spot at all!
                if (isset($avail_stock_update) && $avail_stock_update == 1)
                {
                    $update_parameters = $cart->get_processor("rdi_cart_product_load")->get_avail_stock_update_parameters($product_type, $product_class);

                    $product_stock_data = $pos->get_processor("rdi_pos_product_load")->get_data($product_class, $product_type, $update_parameters, 'available');

                    if (is_array($product_stock_data))
                        $cart->get_processor("rdi_cart_product_load")->process_product_records($product_class, $product_type, $product_stock_data, $update_parameters['update_field']);
                }
				
				
				
                //@setting $insert_products [0-OFF, 1-ON] Perform product inserts, aka new products
                if ($insert_products == 1)
                {
                    //get the product data based on that schema data from the pos library
                    $this->echo_message("Checking for new items to insert of type: {$product_class_name} - {$product_type['product_type']}", 2);
					
                    $product_insert_parameters = $cart->get_processor("rdi_cart_product_load")->get_product_insert_parameters($product_class, $product_type);

                    $product_insert_parameters['index'] = 0;

                    /**
                     * The index is always 0 and we will have a limit of 5k. If the count of product_records goes under 5k we will then exit the loop.
                     * Ex: 7500 products need to be loaded for this class of products. We would get 5000 on the first run.
                     * The count would have 5k then the next the limit is still 0,5000 the count returned is 2500 and then at the end of the do we would bail.
                     */
                    do
                    {
                        if (isset($product_records))
                            unset($product_records);

                        $benchmarker->set_start_time("class.rdi_product_load", "Checking for new items to insert of type: {$product_class_name} - {$product_type['product_type']} index:{$product_insert_parameters['index']}");

                        //get the products that need to be inserted
                        $product_records = $pos->get_processor("rdi_pos_product_load")->get_product_data($product_class, $product_type['product_type'], $product_insert_parameters);

                        $benchmarker->set_end_time("class.rdi_product_load", "Checking for new items to insert of type: {$product_class['product_class']} {$product_type['product_type']}");

                        $product_count = count($product_records);

                        if ($product_records != false && $product_count > 0)
                        {
                            $this->echo_message("Found " . $product_count . " {$product_class_name} - {$product_type['product_type']} products to insert", 4);
							
                            if (isset($use_attribute_mapping) && $use_attribute_mapping == '1')
                            {
                                $field_mapping->apply_attribute_mapping($product_records);
                            }

                            $benchmarker->set_start_time("class.rdi_product_load", "inserting " . count($product_records) . " {$product_class_name} - {$product_type['product_type']} product records");
                            $cart->get_processor("rdi_cart_product_load")->process_product_records($product_class, $product_type, $product_records, false);
                            $benchmarker->set_end_time("class.rdi_product_load", "inserting " . count($product_records) . " {$product_class['product_class']} - {$product_type['product_type']} product records");


                            //@setting $update_type_product [hash,query] we have to hash the product here or it will just update again in the update section when there isnt a hash for it. Ok, Peter.??
                            if ($update_type_product == "hash")
                            {
                                foreach ($product_records as $product_record)
                                {
                                    //rehash the product
                                    $rehash_parameters = $cart->get_processor("rdi_cart_product_load")->process_product_update_hash_parameter($product_type, $product_class);

                                    if (array_key_exists('where', $rehash_parameters))
                                    {
                                        if ($rehash_parameters['where'] != '')
                                            $rehash_parameters['where'] .= " and ";

                                        $rehash_parameters['where'] .= " item.item_flditemsid = '{$product_record['item_id']}'";
                                    }
                                    else
                                    {
                                        $rehash_parameters['where'] = " item.item_flditemsid = '{$product_record['item_id']}'";
                                    }

                                    $hook_handler->call_hook("cart_get_product_rehash_update_parameters", $rehash_parameters, $product_type, $product_class);

                                    $pos->get_processor("rdi_pos_product_load")->hash_products_table($product_type, $product_class, $rehash_parameters);

                                    $benchmarker->set_end_time("class.rdi_product_load", "Rehasing new product data");
                                }
                            }
                        }

                        $benchmarker->log_memory_usage("class.rdi_product_load.php", "end product insert range index: {$product_insert_parameters['index']}");

                        ob_flush();
                    } while ($product_count > 4999);

                    if (isset($product_records))
                        unset($product_records);
                }

                ob_flush();
                //@setting $update_products [0-OFF, 1-ON] Get the products that need to be updated
                if ($update_products == 1)
                {
                    $this->echo_message("Checking for updates for products of type: {$product_class_name} {$product_type['product_type']}", 2);
                    $hook_handler->call_hook("load_products_update_products", $product_class, $product_type);


                    //@setting $update_type_product [hash,query] Run the product updates based on a direct query match of products to update
                    if ($update_type_product == "query")
                    {
                        $product_update_parameters = $cart->get_processor("rdi_cart_product_load")->get_product_update_parameters($product_class, $product_type);
                        //the $product_update_parameters will be an array of parameter sets, these sets would represent a query to be run,
                        //doing it this way we break up the possible changes into more queries, hopefully less chance of deadlock, and have to anyways
                        //the fields have to have a little more logic done on them than can be done in one query
                        $benchmarker->set_start_time("class.rdi_product_load", "Checking for updates for products of type: {$product_class['product_class']} - {$product_type['product_type']}");

                        //$this->_print_r($product_update_parameters);

                        if (is_array($product_update_parameters))
                        {
                            foreach ($product_update_parameters as $product_update_parameter)
                            {
								$this->echo_product_update_statement("Checking the condition", $product_class, $product_update_parameter);
								
                                $u_products = (array) $pos->get_processor("rdi_pos_product_load")->get_product_data($product_class, $product_type['product_type'], $product_update_parameter, true);

                                //0 signifies an empty array, they keys are the style_id here
                                if (!in_array(0, $u_products))
                                {									
									$this->echo_product_update_statement("Found " . count($u_products) . " products for update with condition ", $product_class, $product_update_parameter);
									
                                    if (count($u_products) > 0)
                                    {
                                        $benchmarker->set_start_time("class.rdi_product_load", "updating " . count($u_products) . " products for update from condition " . $product_update_parameter['debug']);
                                        $cart->get_processor("rdi_cart_product_load")->process_product_records($product_class, $product_type, $u_products, $product_update_parameter['update_field']);
                                        $benchmarker->set_end_time("class.rdi_product_load", "updating " . count($u_products) . " products for update from condition " . $product_update_parameter['debug']);
                                    }
                                }

                                unset($u_products);

                                $benchmarker->log_memory_usage("class.rdi_product_load.php", "end product update range index: {$product_update_parameter['debug']}");

                                ob_flush();
                            }
                        }
                    }
                    //@setting $update_type_product [hash,query] Lets check again if we are using hashing for the update of products. Why not??
                    else if ($update_type_product == "hash")
                    {

                        //check for updated needed
                        //get a list of the mapping
                        $product_fields = $field_mapping->get_field_list('product', $product_type['product_type'], $product_class['product_class']);

                        //keep a list of the products we have already updated, for use in the select query
                        $updated = array();

                        //clear out the product criteria on the class def, hash wont use it
                        //and since this will be needed later need to clone and remove it
                        $p_class2 = $product_class;
                        //$p_class2['query_criteria'] = array();
                        //loop through and need to query the items based on the changes to the field data provided
                        foreach ($product_fields as $product_field)
                        {
                            //print_r($product_field);

                            if ($product_field['pos_field'] != '' && $product_field['allow_update'] == 1)
                            {
                                if (is_array($product_class['query_criteria']))
                                {
                                    //skip anything defined in the criteria
                                    foreach ($product_class['query_criteria'] as $q)
                                    {
                                        //skip the criteria fields
                                        if ($product_field['pos_field'] == $q['pos_field'])
                                        {
                                            //echo "SKIP {$product_field['pos_field']}";
                                            continue;
                                        }
                                    }
                                }

                                $pos_field = $product_field['pos_field'];

                                if (isset($product_field['alternative_field']))
                                {
                                    //make sure the alt field doesnt contain a '' or its just plain invalid
                                    if (strpos($product_field['alternative_field'], "'") === false)
                                    {
                                        $pos_field = "ifnull({$pos_field}, {$product_field['alternative_field']})";
                                    }
                                }

                                $parameters = array(
                                    "join" => "left join rdi_hash on rdi_hash.pos_field = CONCAT({$product_field['pos_field']}) and rdi_hash.related_id = "
                                    . $field_mapping->map_field('product', 'related_id', $product_type['product_type'], $p_class2['product_class']),
                                    "where" => "(rdi_hash.hash_value != md5(CONCAT({$pos_field}))
                                                                    and rdi_hash.field_type = CONCAT({$product_type['product_type']})
                                                                    and rdi_hash.pos_field = '{$product_field['pos_field']}') or rdi_hash.hash_value IS NULL)",
                                    "group_by" => '',
                                    "order_by" => '',
                                    "update_field" => $product_field['cart_field'] //passing through the field we are looking at for update, so the lib knows this is an update, and the field we are concered with incase there is special handling
                                );

                                //pass this generic set of parameters off to the cart lib so it can touch it up if needed
                                $parameters = $cart->get_processor("rdi_cart_product_load")->process_product_update_get_hash_parameter($product_class, $product_type, $product_field, $parameters);

                                //@hook cart_get_product_hash_update_parameters give the addons a stab at it
                                $hook_handler->call_hook("cart_get_product_hash_update_parameters", $parameters, $product_type, $product_class, $product_field);

                                //print_r($parameters);
                                //print_r($parameters);
                                ob_flush();


                                //@see rdi_pos_product_load:get_product_data() get the list of products that need update
                                $u_products = (array) $pos->get_processor("rdi_pos_product_load")->get_product_data($p_class2, $product_type['product_type'], $parameters);

                                //send products off for update
                                //
                                //0 signifies an empty array, they keys are the style_id here
                                if (!in_array(0, $u_products))
                                {
                                    //print_r($u_products);
                                    //add the items processed to the updated list, prevent dupes
                                    foreach ($u_products as $p)
                                    {
                                        if ($p['related_id'] != '' && !in_array("'{$p['related_id']}'", $updated))
                                        {
                                            $updated[] = "'{$p['related_id']}'";
                                        }
                                    }

                                    $benchmarker->set_start_time("class.rdi_product_load", "updating " . count($u_products) . " products for update. Field Changed " . $product_field['pos_field']);
                                    $m = "Found " . count($u_products) . " products for update changed field " . $product_field['pos_field'];

                                    $this->echo_message($m, 3);

                                    $cart->get_processor("rdi_cart_product_load")->process_product_records($product_class, $product_type, $u_products, $parameters['update_field']);

                                    $benchmarker->set_end_time("class.rdi_product_load", "updating " . count($u_products) . " products for update. Field Changed " . $product_field['pos_field']);
                                }
                            }
                        }

                        $benchmarker->set_start_time("class.rdi_product_load", "Rehasing new product data");

                        /**
                         * rehash the products
                         */
                        $rehash_parameters = $cart->get_processor("rdi_cart_product_load")->process_product_update_hash_parameter($product_type, $product_class);

                        $hook_handler->call_hook("cart_get_product_rehash_update_parameters", $rehash_parameters, $product_type, $product_class);

                        $pos->get_processor("rdi_pos_product_load")->hash_products_table($product_type, $product_class, $rehash_parameters);

                        $benchmarker->set_end_time("class.rdi_product_load", "Rehasing new product data");
                    }
                }

                $benchmarker->set_end_time("class.rdi_product_load", "Checking for updates for products of type: {$product_class_name} - {$product_type['product_type']}");

                //@see rdi_cart_product_load:post_product_group_processing() call a post processing function, to catch any data that is needing to be done once at the end of processing for this class / type
                $cart->get_processor("rdi_cart_product_load")->post_product_group_processing($product_class, $product_type);
            }
            ob_flush();
            unset($product_records);
        }

        unset($product_classes);
    }

    /**
     *
     * @global benchmarker $benchmarker
     * @global rdi_lib $pos
     * @global rdi_lib $cart
     * @global rdi_field_mapping $field_mapping
     *
     */
    public function process_upsell()
    {
        global $benchmarker, $pos, $cart, $field_mapping,
        $insert_upsell, $update_upsell, $hook_handler;

        //@setting $insert_upsell [0-OFF, 1-ON] do upsell inserts
        if ($insert_upsell == 1)
        {
            $benchmarker->log_memory_usage("class.rdi_product_load.php", "start product upsell insert");

            $this->echo_message("Inserting Upsell Product relations", 2);

            $benchmarker->set_start_time("class.rdi_product_load", "Inserting Upsell Product relations");

            //the the insert parameters
            $upsell_insert_parameters = $cart->get_processor("rdi_cart_product_load")->get_upsell_insert_parameters();

            //get the data
            $upsell_data = $pos->get_processor("rdi_pos_product_load")->get_upsell_data($upsell_insert_parameters);

            //print_r($upsell_data);
            //do the insert
            $cart->get_processor("rdi_cart_product_load")->upsell_insert($upsell_data);

            $benchmarker->set_end_time("class.rdi_product_load", "Inserting Upsell Product relations");

            $benchmarker->log_memory_usage("class.rdi_product_load.php", "end product upsell insert");
        }

        //@setting $update_upsell [0-OFF, 1-ON] do upsell updates
        if ($update_upsell == 1)
        {
            $benchmarker->log_memory_usage("class.rdi_product_load.php", "start product upsell update");

            $this->echo_message("Updating Upsell Product relations", 2);

            $benchmarker->set_start_time("class.rdi_product_load", "Updating Upsell Product relations");

            //get the update parameters
            $upsell_update_parameters = $cart->get_processor("rdi_cart_product_load")->get_upsell_update_parameters();

            //get the data
            $upsell_data = $pos->get_processor("rdi_pos_product_load")->get_upsell_data($upsell_update_parameters);

            //do the update
            $cart->get_processor("rdi_cart_product_load")->upsell_update($upsell_data);

            //get the delete parameters
            $upsell_removal_parameters = $cart->get_processor("rdi_cart_product_load")->get_upsell_removal_parameters();

            //get the data
            $upsell_data = $pos->get_processor("rdi_pos_product_load")->get_upsell_data($upsell_removal_parameters);

            //do the delete
            $cart->get_processor("rdi_cart_product_load")->upsell_removal($upsell_data);

            $benchmarker->set_end_time("class.rdi_product_load", "Updating Upsell Product relations");

            $benchmarker->log_memory_usage("class.rdi_product_load.php", "end product upsell insert");
        }
    }

    /**
     * load the product class data from the tables
     * @return array This is an array of all the products_classes that we will try to load. Check the class_mapping tables. Usually the class_mapping view will have 14.
     */
    public function get_product_classes()
    {
        $product_classes = array();

        $this->set_single_product_criteria();

        $product_classes = $this->db_connection->rows("Select * from rdi_cart_class_mapping");

        foreach ($product_classes as $id => $class)
        {
            $product_classes[$id]['query_criteria'] = $this->get_criteria($class['cart_class_mapping_id']);



            $product_classes[$id]['field_data'] = $this->db_connection->rows("SELECT DISTINCT
                                                                                mf.cart_field,
                                                                                mp.pos_field,
                                                                                mf.position,
                                                                                mf.label
                                                                              FROM
                                                                                rdi_cart_class_map_fields mf
                                                                                JOIN rdi_cart_class_mapping ccm
                                                                                  ON ccm.cart_class_mapping_id = mf.cart_class_mapping_id
                                                                                  AND ccm.cart_class_mapping_id = {$class['cart_class_mapping_id']}
                                                                                INNER JOIN rdi_field_mapping m
                                                                                  ON m.cart_field = mf.cart_field
                                                                                  AND (
                                                                                    m.field_classification = ccm.product_class
                                                                                    OR m.field_classification IS NULL
                                                                                  )
                                                                                INNER JOIN rdi_field_mapping_pos mp
                                                                                  ON mp.field_mapping_id = m.field_mapping_id
                                                                              WHERE mf.cart_class_mapping_id = {$class['cart_class_mapping_id']}");

            $product_classes[$id]['product_types'] = $this->db_connection->rows("Select * from rdi_cart_product_types where (cart_class_mapping_id = {$class['cart_class_mapping_id']} or cart_class_mapping_id is null) order by creation_order");
        }

        return $product_classes;
    }

    /**
     * Make sure we are not loading a multiproduct when it is really just a single product.
     *
     * @global rdi_lib $pos
     * @return \rdi_product_load
     */
    public function set_single_product_criteria()
    {
        global $use_single_product_criteria, $pos;
        //@setting $use_single_product_criteria the setting for this is going to be the cart_class_mapping_id of the stand alone single product. Usually 4. all with the id will get an IN criteria and a NOT IN for the others.
        //the setting for this is going to be the cart_class_mapping_id of the stand alone single product. Usually 4.
        //@todo might need to have two ids for this in the furture.
        //
        if (isset($use_single_product_criteria) && $use_single_product_criteria > 0)
        {
            $values = $pos->get_processor('rdi_pos_product_load')->get_single_products();

            $this->single_product_criteria['pos_field'] = $values['related_id_field'];
            $this->single_product_criteria['cart_field'] = "related_id";
            $this->single_product_criteria['qualifier'] = "IN('" . implode("','", $values['related_ids']) . "')";
        }

        return $this;
    }

    /**
     * Add the single product criteria to the product_class definitions.
     *
     * @param array $_criteria
     * @param int $cart_class_mapping_id
     * @return \rdi_product_load
     */
    public function add_single_product_criteria(&$_criteria, $cart_class_mapping_id)
    {
        global $use_single_product_criteria;

        //@setting $use_single_product_criteria
        if (isset($use_single_product_criteria) && $use_single_product_criteria > 0)
        {
            $_criteria[99] = $this->single_product_criteria;
        }

        //@setting $use_single_product_criteria
        if (isset($use_single_product_criteria) && $use_single_product_criteria > 0 && $use_single_product_criteria != $cart_class_mapping_id)
        {
            $_criteria[99]['qualifier'] = "NOT " . $_criteria[99]['qualifier'];
        }
        else
        {
            // $_criteria = array();
            // $_criteria[] = $this->single_product_criteria;
        }

        if (isset($use_single_product_criteria) && $use_single_product_criteria == $cart_class_mapping_id)
        {
            unset($_criteria[0]);
            unset($_criteria[1]);
        }

        return $this;
    }

    /**
     * Get the criteria for each class.
     *
     * @param int $cart_class_mapping_id
     * @return array product_class criteria array.
     */
    public function get_criteria($cart_class_mapping_id)
    {
        $_criteria = $this->db_connection->rows("SELECT DISTINCT
                                                        mp.pos_field,
                                                        mf.cart_field,
                                                        mf.qualifier
                                                      FROM
                                                        rdi_cart_class_map_criteria mf
                                                        JOIN rdi_cart_class_mapping ccm
                                                        ON ccm.cart_class_mapping_id = mf.cart_class_mapping_id
                                                        AND ccm.cart_class_mapping_id = {$cart_class_mapping_id}
                                                        INNER JOIN rdi_field_mapping m
                                                              ON m.cart_field = mf.cart_field
                                                              AND (m.field_classification = ccm.product_class OR m.field_classification IS NULL)
                                                        INNER JOIN rdi_field_mapping_pos mp
                                                              ON mp.field_mapping_id = m.field_mapping_id
                                                      WHERE mf.cart_class_mapping_id = {$cart_class_mapping_id}");

        $this->add_single_product_criteria($_criteria, $cart_class_mapping_id)->get_dcs_criteria($_criteria, $cart_class_mapping_id);

        return $_criteria;
    }

    /**
     * This does not work. Dont use it. Rather update to a field and map that into rdi_criteria.
     * @global array $use_dcs_product_criteria
     * @param array $_criteria class criteria
     * @param int $cart_class_mapping_id
     * @return \rdi_product_load
     */
    public function get_dcs_criteria(&$_criteria, $cart_class_mapping_id)
    {
        global $use_dcs_product_criteria;

        if (isset($use_dcs_product_criteria) && $use_dcs_product_criteria > 0)
        {

            $_dcs_fields = $this->db_connection->cells("SELECT dcs.dcs_field FROM rdi_cart_class_map_criteria_dcs dcs
                                        join  rdi_cart_class_mapping ccm
                                        on ccm.product_class_id = dcs.product_class_id
                                        WHERE cart_class_mapping_id = {$cart_class_mapping_id}", "dcs_field");

            $dcs_fields = implode("','", $_dcs_fields);

            $dcs_map = array();

            //need to get the dcs from the POS
            $dcs_map['pos_field'] = "flddcs";
            $dcs_map['cart_field'] = "rdi_dcs";
            $dcs_map['qualifier'] = "IN('{$dcs_fields}')";

            $_criteria[] = $dcs_map;
        }

        return $this;
    }
	
	public function get_product_class_name($product_class)
	{
		$name = $product_class['product_class'] . " ";
		$has_attributes = false;
		if(isset($product_class['query_criteria']) && !empty($product_class['query_criteria']))
		{
			foreach($product_class['query_criteria'] as $key => $fields)
			{
				if($fields['qualifier'] == 'IS NOT NULL')
				{
					$has_attributes = true;
					$name .= $fields['cart_field'] . ' ';
				}
			}
		}
		
		if(!$has_attributes)
		{
			$name .= 'NoFields';
		}
		
		return trim($name);
	}
	
	
	public function echo_product_update_statement($message = "", $product_class, $product_update_parameter)
	{
		if(isset($product_update_parameter['mapping']))
		{
			if(trim($product_update_parameter['mapping']['cart_field']) == '')
			{
				$this->echo_message("{$message} {$product_update_parameter['debug']}");
			}
			else
			{
				$this->echo_message("{$message} {$product_update_parameter['mapping']['cart_field']} is {$product_update_parameter['mapping']['pos_field']} "
									. (strlen($product_update_parameter['mapping']['alternative_field'])>0?'OR ' . $product_update_parameter['mapping']['alternative_field'] :''), 3);
			}
		}
	}
	

}

?>
