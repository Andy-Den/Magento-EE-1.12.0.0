<?php

/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Catalog load class
 *
 * Handles the loading of the catalog data
 *
 * PHP version 5.3
 *
 * @author     Paul Bliss <pbliss@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    1.1.0
 * @package    Core\Load\Catalog
 */
class rdi_catalog_load extends rdi_general {

    /**
     * Class Constructor
     *
     * @param rdi_catalog_load $db
     */
    public function rdi_catalog_load($db)
    {
        global $cart, $pos;

        $this->cart = $cart->get_processor('rdi_cart_catalog_load');
        $this->pos = $pos->get_processor('rdi_pos_catalog_load');

        parent::__construct($db);
    }

    /**
     * Class Constructor
     * 
     * Load categories from the pos into the cart.
     * 
     * @global rdi_benchmark $benchmarker
     * @global rdi_lib $pos
     * @global rdi_lib $cart
     * @global rdi_debug $debug
     * @global setting $insert_categories
     * @global setting $update_categories
     * @global setting $product_to_categories
     * @global setting $category_tuning
     * @global rdi_hookhandler $hook_handler
     */
    public function load_categories()
    {
        global $benchmarker, $insert_categories, $update_categories, $product_to_categories, $category_tuning, $hook_handler, $helper_funcs;

        ob_flush();
        //******************************
        //handles new categories
        if (isset($insert_categories) && $insert_categories == 1)
        {
            $this->echo_message("Processing New Categories and structure changes", 2);

            $benchmarker->set_start_time("rdi_catalog_load", "Processing New Categories");
            //get the base level categories
            //call the pos library to get the fields
            //$base_categories = $this->pos->get_base_level_categories();
            $base_categories = $this->pos->get_categories('base', $this->cart->get_category_insert_parameters());

            $hook_handler->call_hook("core_catalog_load_base_categories", $base_categories);

            //start the loop, priming with the base level categories
            $this->walk_categories($base_categories, '');
            $benchmarker->set_end_time("rdi_catalog_load", "Processing New Categories");
        }
        //******************************
        ob_flush();
        //Handles the updates
        if (isset($update_categories) && $update_categories == 1)
        {
            $this->echo_message("Updating Categories", 2);
            $benchmarker->set_start_time("rdi_catalog_load", "Updating Categories");
            //get the categories that are needing update
            $categories_for_update = $this->pos->get_categories('', $this->cart->get_category_update_parameters());

            //loop the category list
            if (!empty($categories_for_update))
            {
                $benchmarker->set_start_time("class.rdi_catalog_load", "updating " . count($categories_for_update) . " category records");
                $this->echo_message("Found " . count($categories_for_update) . " Categories for update", 4);
                foreach ($categories_for_update as $category)
                {
                    //pass the categories off to the library for update, pass update in the arguments
                    $this->cart->process_category_record($category, 'update');
                }

                $benchmarker->set_end_time("class.rdi_catalog_load", "updating " . count($categories_for_update) . " category records");
            }
        }

        $benchmarker->set_end_time("rdi_catalog_load", "Updating Categories");
        //******************************

        if (isset($product_to_categories) && $product_to_categories == 1)
        {
            $this->echo_message("Assigning Products to Categories", 2);

            $benchmarker->set_start_time("rdi_catalog_load", "Assinging Products to Categories");
            //assign products to categories        
            $this->assign_product_category();
            $benchmarker->set_end_time("rdi_catalog_load", "Assinging Products to Categories");
        }
        $hook_handler->call_hook("core_product_to_categories");


        /**
         * @param setting $category_tuning the tuning of categories by turning disabling the ones that have no products
         */
        if (isset($category_tuning) && $category_tuning == 1)
        {
            $this->echo_message("Processing Category Availability", 2);

            $benchmarker->set_start_time("rdi_catalog_load", "Processing Category availability");

            $this->cart->disable_unused_categories();
            $this->cart->enable_used_categories();

            $benchmarker->set_end_time("rdi_catalog_load", "Processing Category availability");
        }
    }

    /**
     * step through the category list processing and recursing each 
     * 
     * @global rdi_benchmark $benchmarker
     * @global rdi_lib $pos
     * @global rdi_lib $cart
     * @param array $category_list
     * @param type $arg
     */
    function walk_categories($category_list, $arg)
    {
        if (is_array($category_list))
        {
            //loop the category list
            foreach ($category_list as $category)
            {
                $new_arg = $this->cart->process_category_record($category, $arg);

                //get a list of children            
                $children = $this->pos->get_categories($category['related_id'], $this->cart->get_category_insert_parameters());

                if (count($children) > 0)
                {
                    $this->walk_categories($children, $new_arg);
                }
            }
        }
    }

    /**
     * Assign products to Categories
     * @global rdi_benchmark $benchmarker
     * @global rdi_lib $pos
     * @global rdi_lib $cart
     */
    function assign_product_category()
    {
        //if these return false then the handling will happen totally in the cart lib
        $parameters = $this->cart->get_category_product_insert_parameters();

        if ($parameters != false)
        {
            $this->pos->set_category_product_relations($parameters);
        }

        //call the cart module so it can do anything it needs to do
        $this->cart->set_product_category_relation();
    }

    /**
     * assign the products to categories, does so on a per basis
     * @global rdi_benchmark $benchmarker
     * @global rdi_lib $pos
     * @global rdi_lib $cart
     */
    function assign_product_category_per()
    {
        //get the list of products that are missing assignment        
        $category_product_relations = $this->pos->get_category_product_relations($this->cart->get_category_product_insert_parameters());

        //print_r($category_product_relations);
        //pass the list to the cart library for processing
        $this->cart->set_product_category_relations($category_product_relations);

        //get the list that is needing removal
        $category_product_relations_for_removal = $this->pos->get_category_product_relations_for_removal($this->cart->get_category_product_removal_parameters());

        //process the list of relations to remove
        $this->cart->remove_product_category_relations($category_product_relations_for_removal);
    }

    //disable the categories that have products with 0 stock
    function disable_categories_without_stock()
    {
        
    }

    //disable the categories that are without products
    function disable_empty_categories()
    {
        
    }

}

?>
