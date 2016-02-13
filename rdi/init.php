<?php

if(!function_exists("master_shutdown"))
{
    function master_shutdown()
    {
     $error = error_get_last();
//@todo write this better
     if(!empty($error) && !strstr($error['file'],'class.rdi_db') && $error['type'] !== 8192)
     {
            file_put_contents(__DIR__."/error", time(). print_r($error,true) . "\n" , FILE_APPEND);
     }
    }
    register_shutdown_function('master_shutdown');
}

/**
 * Rdi Load
 * @package Core\Init
 */
set_time_limit(0);
ini_set('memory_limit','256M');

//echo "INIT";

//Set which libraries are used here, these are not and can not be set in the settings table, as the cart type is needed to be know before we can even access the database
$cart_type = include 'cart_type.inc';
//$pos_type = "rpro9";

$GLOBALS['cart_type'] = $cart_type;
$GLOBALS['pos_type'] = $pos_type;

//set the path if not already
if(!isset($rdi_path))
    $rdi_path = "";

if(!isset($root_path))
    $root_path = getcwd();

//load the libraries
require_once $rdi_path . "libraries/class.general.php";
require_once $rdi_path . "libraries/class.field_mapping.php";
require_once $rdi_path . "libraries/class.load.php";
require_once $rdi_path . "libraries/class.export.php";
require_once $rdi_path . "libraries/class.rdi_lib.php";
require_once $rdi_path . "libraries/class.rdi_db.php";
require_once $rdi_path . "libraries/class.rdi_field_mapping.php";
require_once $rdi_path . "libraries/class.rdi_upload.php";
require_once $rdi_path . "libraries/class.rdi_catalog_load.php";
require_once $rdi_path . "libraries/class.rdi_product_load.php";
require_once $rdi_path . "libraries/class.rdi_image_load.php";
require_once $rdi_path . "libraries/class.rdi_so_status_load.php";
require_once $rdi_path . "libraries/class.rdi_customer_load.php";
require_once $rdi_path . "libraries/class.rdi_benchmark.php";
require_once $rdi_path . "libraries/class.rdi_debug.php";
require_once $rdi_path . "libraries/class.rdi_helper_funcs.php";
require_once $rdi_path . "libraries/class.rdi_settings_handler.php";
require_once $rdi_path . "libraries/class.rdi_import_xml.php";
require_once $rdi_path . "libraries/class.rdi_export_orders.php";
require_once $rdi_path . "libraries/class.rdi_export_customers.php";
require_once $rdi_path . "libraries/class.rdi_export_xml.php";
require_once $rdi_path . "libraries/class.rdi_hook_handler.php";
require_once $rdi_path . "libraries/class.rdi_error_handler.php";
require_once $rdi_path . "libraries/class.rdi_file_manage.php";
require_once $rdi_path . "libraries/class.rdi_encoding.php";
require_once $rdi_path . "libraries/class.rdi_upsell_item_load.php";

//setup the cart library object
$cart = new rdi_lib("cart", $cart_type);

$cart->get_db()->_rdi_loader();

$error_handler = new rdi_error_handler();

//setup filemanager
$manager = new file_manage();

//setup encoding
$encoding = new rdi_encoding();

//take any get variables and make then global
foreach($_GET as $k => $v)
{
    $GLOBALS[$k] = $v;
}

//settings handler
$settings_handler = new rdi_settings_handler($cart->get_db());

//hook handler for addons
$hook_handler = new rdi_hook_handler($cart->get_db());

//load all the settings into memory
$settings_handler->load_all();

global $pos_type;

//here are the settings used for this alt db system
/*
 * INSERT INTO `rdi_settings` (`setting_id`, `setting`, `value`, `group`, `help`, `cart_lib`, `pos_lib`) VALUES (906, 'allow_alt_database', '1', '', NULL, NULL, NULL);
INSERT INTO `rdi_settings` (`setting_id`, `setting`, `value`, `group`, `help`, `cart_lib`, `pos_lib`) VALUES (907, 'alt_db_host_1', 'localhost', '', NULL, NULL, NULL);
INSERT INTO `rdi_settings` (`setting_id`, `setting`, `value`, `group`, `help`, `cart_lib`, `pos_lib`) VALUES (908, 'alt_db_user_1', 'root', '', NULL, NULL, NULL);
INSERT INTO `rdi_settings` (`setting_id`, `setting`, `value`, `group`, `help`, `cart_lib`, `pos_lib`) VALUES (909, 'alt_db_pass_1', 'r3tail', '', NULL, NULL, NULL);
INSERT INTO `rdi_settings` (`setting_id`, `setting`, `value`, `group`, `help`, `cart_lib`, `pos_lib`) VALUES (910, 'alt_db_database_1', 'pmb', '', NULL, NULL, NULL);
INSERT INTO `rdi_settings` (`setting_id`, `setting`, `value`, `group`, `help`, `cart_lib`, `pos_lib`) VALUES (911, 'alt_db_prefix_1', '', '', NULL, NULL, NULL);

 */
if(isset($allow_alt_database) && $allow_alt_database == 1 && isset($alt_db_id))
{
    //get the alt db to use from the url parameter
    $h = "alt_db_host_" . $alt_db_id;
    $u = "alt_db_user_" . $alt_db_id;;
    $p = "alt_db_pass_" . $alt_db_id;;
    $d = "alt_db_database_" . $alt_db_id;;
    $dp = "alt_db_prefix_" . $alt_db_id;;
    
    $cart->set_db(new rdi_db($$h, $$u, $$p, $$d, $$dp));
    
    //need to reload all of these
    //settings handler
    $settings_handler = new rdi_settings_handler($cart->get_db());

    //hook handler for addons
    $hook_handler = new rdi_hook_handler();

    //load all the settings into memory
    $settings_handler->load_all();
}

if($GLOBALS['verbose_queries'] != 1 && $GLOBALS['verbose_queries'] != 0)
    $verbose_queries = explode(",", $GLOBALS['verbose_queries']);

//enable the debugger and set the debug level
$debug = new rdi_debug($cart->get_db(), $GLOBALS['debug_enabled'], $GLOBALS['debug_level'], array(), $GLOBALS['verbose_queries'], $GLOBALS['show_query_counts'], (isset($GLOBALS['log_debug_data']) ? $GLOBALS['log_debug_data'] : 1));

$debug->write_message("rdi_load.php", "init", "Initializing the system");

//call time benchmarking
$benchmarker = new rdi_benchmark($cart->get_db(), $GLOBALS['benchmark_global_display_screen'], $GLOBALS['benchmark_global_save_db']);


//set up the pos library object
//this one you must pass the databse connection that was used in the cart object
$pos = new rdi_lib("pos", $pos_type, $cart->get_db());

if(!isset($inPath))
    $inPath = $settings_handler->get_setting("inPath");

//get the processor for the staging db lib functions
$db_lib = $pos->get_processor("rdi_staging_db_lib");

//setup an instance of the helper funcs, these are common funcs that will be used over multiple areas
$helper_funcs = new rdi_helper_funcs();

//handle purging of archive data after $archival_length
$helper_funcs->pruge_archive();

//record addons comments, truncate table

$cart->get_db()->clean_addon_table();


?>
