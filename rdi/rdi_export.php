<?php
/**
 * Export Orders and Customers
 * @package Core\Export
 */
//init the libraries and the settings
include "init.php";
$benchmarker->set_start(basename(__FILE__), "load");

//so the response of the export is the current time on the server, for rdice to know
if($pos_type == "rpro9")
    echo "Time: [" . gmdate("m/d/Y H:i:s", time()) . "]";

//set the mapping
$field_mapping = new rdi_field_mapping($cart->get_db(), $GLOBALS['ignore_warnings']);

//call the field mapping validation
if(isset($GLOBALS['skip_validation']) && $GLOBALS['skip_validation'] == 1)
{}else{$pos->get_processor("rdi_pos_common")->validate_pos_settings();
$cart->get_processor("rdi_cart_common")->validate_cart_settings();}


//setup an instance of the helper funcs, these are common funcs that will be used over multiple areas
$helper_funcs = new rdi_helper_funcs();

//run the setting validation for the libraries, these may call an exit if they are failing
//$pos->get_processor("rdi_pos_common")->validate_pos_settings();
//$cart->get_processor("rdi_cart_common")->validate_cart_settings();

$pos->get_processor("rdi_pos_common")->pre_load();
$cart->get_processor("rdi_cart_common")->pre_load(false);

//clean the export staging tables
$db_lib->clean_out_staging_tables();

// ---------------------------------------------------------------------
//	Export customers
// ---------------------------------------------------------------------
if($GLOBALS['export_customers'])
{
    //echo "Export customers <br>";
    $debug->write_message("rdi_export.php", "export", "Run export customers");
    $export_customers = new rdi_export_customers($cart->get_db());
    $export_customers->export_customers();
}

// ---------------------------------------------------------------------
//	Export orders
// ---------------------------------------------------------------------
if($GLOBALS['export_orders'])
{
    //echo "Export orders <br>";
    $debug->write_message("rdi_export.php", "export", "Run export orders");
    $export_orders = new rdi_export_orders($cart->get_db());
    $export_orders->export_orders();
}

$db_lib->clean_out_log_tables();

if($pos_type == "rpro4web")
{
    include "rpro_download_orders.php";
    include "rpro_download_customers.php";
}

$benchmarker->set_end(basename(__FILE__), "load");
?>