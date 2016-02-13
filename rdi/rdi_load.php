<?php
/**
 * Rdi Load
 * @package Core\Load
 */

//prevent timeout
//ini_set('max_execution_time', 0);
ini_set('display_errors', 1);

//init the libraries and the settings
include_once("init.php");
$benchmarker->set_start(basename(__FILE__), "load");

//set the mapping

//$field_mapping = new $r($cart->get_db(), $GLOBALS['ignore_warnings']);
$field_mapping = rdi_lib::create_core_class('rdi_field_mapping',$cart->get_db(), $GLOBALS['ignore_warnings']);

//call the field mapping validation
if(isset($GLOBALS['skip_validation']) && $GLOBALS['skip_validation'] == 1)
{}else{$field_mapping->validate_mapping();}


//@setting skip_validation [0-OFF, 1-ON] Run the setting validation for the libraries, these may call an exit if they are failing
if(isset($GLOBALS['skip_validation']) && $GLOBALS['skip_validation'] == 1)
{}else{$pos->get_processor("rdi_pos_common")->validate_pos_settings();
$cart->get_processor("rdi_cart_common")->validate_cart_settings();}

$cart->echo_message("Fill log tables",1);
//move current data from staging tables into log tables
$db_lib->log_current_data();


$pos->get_processor("rdi_pos_common")->pre_load();
$cart->get_processor("rdi_cart_common")->pre_load();

// ---------------------------------------------------------------------
//	Cleanup log tables and copy staging table data into log tables
// ---------------------------------------------------------------------

// load returns before SOs in an attempt to do it before SOs get captured.
// Only support for one cart and one POS right now. New POS can go in as the same table formating.
if(isset($load_return) && $load_return == 1 )
{
	$cart->echo_message("Beginning Returns",1);
    
    $cart->get_processor("rdi_cart_return")->load();
    
}

// ---------------------------------------------------------------------
//	Load style preferences (attributes)
// ---------------------------------------------------------------------
//echo "Beginning preferences <br>";

// ---------------------------------------------------------------------
//	Loads the sales order status information
// ---------------------------------------------------------------------
//@setting load_so_status [0-OFF, 1-ON]
if($GLOBALS['load_so_status'])
{
    $cart->echo_message("Beginning SO",1);
    /**
     * hit the preload functions for the libraries
     */
    $pos->get_processor("rdi_pos_so_status_load")->pre_load();
    $cart->get_processor("rdi_cart_so_status_load")->pre_load();

    if($db_lib->get_so_count() > 0)
    {
        $debug->write_message("rdi_load.php", "load", "Found so to load");

        $so_load = rdi_lib::create_core_class('rdi_so_status_load', $cart->get_db());
        $so_load->load_so_statuses();
    }

    /**
     * hit the post load functions
     */
    $pos->get_processor("rdi_pos_so_status_load")->post_load();
    $cart->get_processor("rdi_cart_so_status_load")->post_load();

}
//@hook core_so_status_load Called after. And should be used to replace the normal library by turning off the setting.
$hook_handler->call_hook("core_so_status_load");


// ---------------------------------------------------------------------
//	Loads the Gift Registry information
// ---------------------------------------------------------------------
//all of this is handled in the cart module.
if(isset($load_gift_reg) && $load_gift_reg == 1)
{
    $cart->echo_message("Beginning Gift Registry",1);
    $cart->get_processor('rdi_cart_gift_reg')->load();

    $hook_handler->call_hook("core_gift_reg_load");
}
// ---------------------------------------------------------------------
//	Load products and style information
// ---------------------------------------------------------------------

//@setting load_so_status [0-OFF, 1-ON] Loads Products and Upsell Items.
if($GLOBALS['load_products'])
{
    $cart->echo_message("Beginning Styles",1);
    /**
     * hit the preload functions for the libraries
     */
    $pos->get_processor("rdi_pos_product_load")->pre_load();
    $cart->get_processor("rdi_cart_product_load")->pre_load();

    if($db_lib->get_product_count() > 0)
    {
        $debug->write_message("rdi_load.php", "load", "Found products to load");

        $product_load = rdi_lib::create_core_class('rdi_product_load', $cart->get_db());
        $product_load->load_products();
    }

    /*if($db_lib->get_upsell_count() > 0)
    {
        $debug->write_message("rdi_load.php", "load", "Found products upsell to load");

        if(!isset($product_load))
            $product_load = rdi_lib::create_core_class('rdi_product_load', $cart->get_db());

        $product_load->process_upsell();
    }*/

    /**
     * hit the post load functions
     */
    $pos->get_processor("rdi_pos_product_load")->post_load();
    $cart->get_processor("rdi_cart_product_load")->post_load();
}

//@hook core_product_load Called after. And should be used to replace the normal library by turning off the setting.
$hook_handler->call_hook("core_product_load");

$cart->echo_message("Beginning Upsell Item",1);
if($db_lib->get_upsell_count() > 0)
{
	$debug->write_message("rdi_load.php", "load", "Found products upsell to load");

	$upsell_item_load = rdi_load::include_libs($cart->get_db(), "upsell_item");
	$upsell_item_load->load();
}
ob_flush();


ob_flush();
// ---------------------------------------------------------------------
//	Load the categories into tables
// ---------------------------------------------------------------------
//@setting load_categories [0-OFF, 1-ON] Called after. And should be used to replace the normal library by turning off the setting.
if($GLOBALS['load_categories'])
{
    $cart->echo_message("Beginning Categories",1);
    /**
     * hit the preload functions for the libraries
     */
    if($db_lib->get_category_count() > 0)
    {
        $pos->get_processor("rdi_pos_catalog_load")->pre_load();
        $cart->get_processor("rdi_cart_catalog_load")->pre_load();

        $debug->write_message("rdi_load.php", "load", "Found categories to load");

        $catalog_load = rdi_lib::create_core_class('rdi_catalog_load', $cart->get_db());
        $catalog_load->load_categories();

        /**
         * hit the post load functions
         */
        $pos->get_processor("rdi_pos_catalog_load")->post_load();
        $cart->get_processor("rdi_cart_catalog_load")->post_load();
    }
}

//@hook core_so_status_load Called after. And should be used to replace the normal library by turning off the setting.
$hook_handler->call_hook("core_category_load");

ob_flush();
// ---------------------------------------------------------------------
//	Load the image paths to data tables
// ---------------------------------------------------------------------
//echo "Beginning Images <br />";

// ---------------------------------------------------------------------
//	Loads the item images
// ---------------------------------------------------------------------
//$cart->echo_message("Beginning Item images",1);
//@setting load_images [0-OFF, 1-ON] Called after. And should be used to replace the normal library by turning off the setting.

    //$image_load = rdi_lib::create_core_class('rdi_image_load', $cart->get_db());
    //$image_load->load();
    $image_load = rdi_load::include_libs($cart->get_db(), "image");
    $image_load->load();


//@hook core_customer_load Called after. And should be used to replace the normal library by turning off the setting.
$hook_handler->call_hook("core_image_load");

// ---------------------------------------------------------------------
//	Load the customer data into tables
// ---------------------------------------------------------------------
//@setting load_customers [0-OFF, 1-ON] Called after. And should be used to replace the normal library by turning off the setting.
if($GLOBALS['load_customers'])
{
    $cart->echo_message("Beginning Customers",1);
    /**
     * hit the preload functions for the libraries
     */
    $pos->get_processor("rdi_pos_customer_load")->pre_load();
    $cart->get_processor("rdi_cart_customer_load")->pre_load();

    if($db_lib->get_customer_count() > 0)
    {
        $debug->write_message("rdi_load.php", "load", "Found customers to load");

        $customer_load = rdi_lib::create_core_class('rdi_customer_load', $cart->get_db());
        $customer_load->load_customers();
    }

    /**
     * hit the post load functions
     */
    $pos->get_processor("rdi_pos_customer_load")->post_load();
    $cart->get_processor("rdi_cart_customer_load")->post_load();
}

//@hook core_customer_load Called after. And should be used to replace the normal library by turning off the setting.
$hook_handler->call_hook("core_customer_load");


// ---------------------------------------------------------------------
//	Loads the Multistore QTY information
// ---------------------------------------------------------------------
//@setting load_multistore [0-OFF, 1-ON] The load is called inside of the load library, but included here to prevent bad updates to this file.
if($GLOBALS['load_multistore']){
    $cart->echo_message("Beginning Multistore",1);
    $multistore = rdi_load::include_libs($cart->get_db(), "multistore");
    $multistore->load();
}
//@hook core_multistore_status_load This is not used.
//$hook_handler->call_hook("core_multistore_status_load");

// ---------------------------------------------------------------------
//	Finial RPro clean up code
// ---------------------------------------------------------------------
$cart->echo_message("Beginning Cleanup",1);
//@hook core_cleanup Called before running any clean up functions. Indexers, etc.
$hook_handler->call_hook("core_cleanup");
//clear the staging tables
//$db_lib->get_db()->trunc("in_catalog");
$db_lib->clean_in_log_tables();

$pos->get_processor("rdi_pos_common")->post_load();
$cart->get_processor("rdi_cart_common")->post_load();


//@hook core_pos_loaded The last possible hook. Called after everything is done.
$hook_handler->call_hook("core_pos_loaded");

$cart->echo_message("Successfully loaded website",1);
$benchmarker->set_end(basename(__FILE__), "load");

?>