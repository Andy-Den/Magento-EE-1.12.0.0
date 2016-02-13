<?php

/*
 * This script will setup a blank magento with default mapping and 
 * Default attribute set with color and size as the configurable attributes
 * 
 * Cart Magento 
 * 
 * POS Retail Pro 9
 * 
 * PMB 01312013 
 *
 */

/*
 * pass off the mage to add the required magento attributes and attribute groups
 * and to use their db setup to load our scripts.
 * 
 * 
 */

if (isset($_GET['continue']) && $_GET['continue'] == 2 )
{
    echo "<h1>Common Tasks</h1>";
    echo '<a href="add_ons/magento_rpro9_pos_common_pre_load.php?verbose_queries=1&install=1" target="_blank"><h3>Install Backup Module</h3></a>';
    echo '<a href="libraries/cart_libs/magento/tools/staging_stats.php" target="_blank"><h3>Create Staging Stats</h3></a>';       
    echo '<a href="rdi_upload_styles.php?verbose_queries=1" target="_blank"><h3>Upload Styles XML</h3></a>';       
    echo '<a href="rdi_upload_catalog.php?verbose_queries=1" target="_blank"><h3>Upload Catalog XML</h3></a>';       
    echo '<a href="libraries/cart_libs/magento/tools/magento_url_key_fix.php" target="_blank"><h3>Magento Url Key Fix(Advanced)</h3></a>'; 
    exit;
} 

foreach($_GET as $k => $v)
{
    $GLOBALS[$k] = $v;
}

$action = $_POST['action'];

include 'libraries/cart_libs/magento/magento_rdi_db_lib.php';
$rdi_path = '';
$db_m = new rdi_db_lib();
$db = $db_m->get_db_obj();
$dbPrefix = $db->get_db_prefix();

if($action == 'init')
{
    if(isset($dbPrefix) && $dbPrefix != '')
    {
        echo "<h2>There is a prefix!</h2>"; 
        echo "<em>There is a function in the install script to fix this. Fix the local.xml after.</em>";
	echo $dbPrefix;		
    }
    else
    {
        echo "<h2>There is no prefix!</h2>"; 
    }

    
	
    
    
    echo "<h1>Creating Directory Structure in rdi<h1>";
    if(!file_exists('in'))
    {
        mkdir('in', 0777);
        mkdir('in/images', 0777);
        mkdir('in/archive', 0777);
        mkdir('in/archive/images', 0777);
        
    }
    if(!file_exists('out'))
    {
        mkdir('out', 0777);
        mkdir('out/archive', 0777);
    }
    if(!file_exists('out/archive'))
    {
        mkdir('out/archive', 0777);
    }
    if(!file_exists('in/archive'))
    {
        mkdir('in/archive', 0777);
        mkdir('in/archive/images', 0777);
    }
    if(!file_exists('in/images'))
    {
        mkdir('in/images', 0777);
    }

    //add the RDI user
    echo "<br>";echo __LINE__; $db->exec("REPLACE INTO {$dbPrefix}admin_user (firstname,lastname,email,username,PASSWORD)
VALUES
('Retail','Dimensions','magento@retaildimensions.com','rdi',CONCAT(MD5('vgretail".date("y")."'),':vg'));");

    echo "<br>";echo __LINE__; $db->exec("REPLACE INTO {$dbPrefix}admin_role(parent_id,tree_level,sort_order,role_type,user_id,role_name)
    SELECT  1 AS parent_id, 2 AS treelevel, 0 AS sort_order, 'U' AS role_type, au.user_id, au.firstname AS role_name FROM {$dbPrefix}admin_user au
    WHERE au.email = 'magento@retaildimensions.com'");

    echo "<h1> Check the <a href='../" . $db_m->get_adminname() . "'>Admin</a> <h1>";

	exit;    
}// END action init
if($action == 'Tables')
{
	echo "create tables";
    $sql = "CREATE TABLE IF NOT EXISTS `rdi_tax_area_mapping` (
            `cart_type` VARCHAR(50) NULL DEFAULT NULL,
            `pos_type` VARCHAR(50) NULL DEFAULT NULL
    )
    ENGINE=MyISAM CHARSET=utf8;";

    echo "<br>";echo __LINE__; $db->exec($sql);

    $sql = "CREATE TABLE IF NOT EXISTS `rdi_tax_class_mapping` (
            `cart_type` VARCHAR(50) NULL DEFAULT NULL,
            `pos_type` VARCHAR(50) NULL DEFAULT NULL
    )

    ENGINE=MyISAM CHARSET=utf8;";

    echo "<br>";echo __LINE__; $db->exec($sql);
    //default mapping.
    $sql = "INSERT INTO rdi_tax_class_mapping (cart_type, pos_type) VALUES ('Taxable Goods',0),('None',1)";

    echo "<br>";echo __LINE__; $db->exec($sql);
    /*Table structure for table `rdi_capture_log` */
     //DROP TABLE IF EXISTS `rdi_capture_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_capture_log` (
      `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
      `orderid` int(11) unsigned NOT NULL,
      `response` varchar(32) NOT NULL,
      `original_price` decimal(10,2) NOT NULL,
      `capture_price` decimal(10,2) NOT NULL,
      `err_msg` varchar(255) NOT NULL,
      `warning_msg` varchar(255) NOT NULL,
      `capture_datetime` datetime DEFAULT NULL,
      `emailed_datetime` datetime DEFAULT NULL,
      `emailed` char(1) NOT NULL DEFAULT 'N',
      PRIMARY KEY (`uid`),
      KEY `response` (`response`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rdi_debug_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_debug_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_debug_log` (
      `debug_id` int(10) NOT NULL AUTO_INCREMENT,
      `level` int(10) NOT NULL DEFAULT '0',
      `datetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
      `script` varchar(50) DEFAULT NULL,
      `func` varchar(150) DEFAULT NULL,
      `debug_message` longtext,
      `data` longtext,
      UNIQUE KEY `debug_id` (`debug_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rdi_error_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); 


    //DROP TABLE IF EXISTS `rdi_error_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_error_log` (
      `uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
      `datetime` datetime DEFAULT NULL,
      `error_level` varchar(32) DEFAULT NULL,
      `error_file` varchar(255) DEFAULT NULL,
      `error_line` varchar(32) DEFAULT NULL,
      `error_message` text,
      `back_trace` text,
      PRIMARY KEY (`uid`)
    ) ENGINE=MyISAM AUTO_INCREMENT=4876 DEFAULT CHARSET=utf8";

    /*Table structure for table `rdi_loadtimes_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_loadtimes_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_loadtimes_log` (
      `uid` int(11) NOT NULL AUTO_INCREMENT,
      `script` varchar(50) DEFAULT NULL,
      `action` varchar(50) DEFAULT NULL,
      `datetime` datetime DEFAULT NULL,
      `duration` varchar(10) DEFAULT NULL,
      PRIMARY KEY (`uid`)
    ) ENGINE=MyISAM AUTO_INCREMENT=22412 DEFAULT CHARSET=utf8";

    /*Table structure for table `rdi_out_customers_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_out_customers_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_out_customers_log` (
      `customer_id` int(11) DEFAULT NULL,
      `rpro_cust_sid` varchar(100) NOT NULL DEFAULT '',
      `first_name` varchar(100) NOT NULL DEFAULT '',
      `last_name` varchar(100) NOT NULL DEFAULT '',
      `address1` varchar(200) NOT NULL DEFAULT '',
      `address2` varchar(200) NOT NULL DEFAULT '',
      `city` varchar(200) NOT NULL DEFAULT '',
      `state` varchar(50) DEFAULT NULL,
      `region` varchar(200) NOT NULL DEFAULT '',
      `zip` varchar(20) NOT NULL DEFAULT '',
      `country` varchar(200) NOT NULL DEFAULT '',
      `country_code` varchar(20) NOT NULL DEFAULT '',
      `phone` varchar(30) NOT NULL DEFAULT '',
      `email` varchar(100) NOT NULL DEFAULT '',
      `login_id` varchar(100) DEFAULT NULL,
      `PASSWORD` varchar(100) DEFAULT NULL,
      `orders_num` int(11) DEFAULT NULL,
      `has_so` int(11) DEFAULT NULL,
      `company` varchar(100) DEFAULT NULL,
      `rdi_export_date` datetime DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rdi_out_so_items_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_out_so_items_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_out_so_items_log` (
      `order_sid` varchar(50) DEFAULT NULL,
      `item_sid` varchar(50) DEFAULT NULL,
      `item_no` varchar(200) DEFAULT NULL,
      `productname` varchar(200) DEFAULT NULL,
      `tax_code` int(11) NOT NULL DEFAULT '0',
      `price` varchar(30) DEFAULT NULL,
      `orig_price` varchar(30) DEFAULT NULL,
      `qty_ordered` int(11) DEFAULT NULL,
      `tax_amount` decimal(10,4) NOT NULL DEFAULT '0.0000',
      `orig_tax_amount` decimal(10,4) NOT NULL DEFAULT '0.0000',
      `tax_percent` int(11) NOT NULL DEFAULT '0',
      `uid` int(11) DEFAULT NULL,
      `rdi_export_date` datetime DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rdi_out_so_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_out_so_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_out_so_log` (
      `date_inserted` datetime DEFAULT NULL,
      `order_sid` varchar(40) DEFAULT NULL,
      `so_number` varchar(40) DEFAULT NULL,
      `date_ordered` datetime DEFAULT NULL,
      `so_billto_cust_sid` varchar(40) DEFAULT NULL,
      `so_billto_rpro_cust_sid` varchar(40) DEFAULT NULL,
      `so_billto_date_created` varchar(20) DEFAULT NULL,
      `so_billto_first_name` varchar(80) DEFAULT NULL,
      `so_billto_last_name` varchar(80) DEFAULT NULL,
      `so_billto_company` varchar(255) DEFAULT NULL,
      `so_billto_address1` varchar(80) DEFAULT NULL,
      `so_billto_address2` varchar(80) DEFAULT NULL,
      `so_billto_city` varchar(80) DEFAULT NULL,
      `so_billto_state_or_province` varchar(20) DEFAULT NULL,
      `so_billto_state_short` varchar(20) DEFAULT NULL,
      `so_billto_country` varchar(80) DEFAULT NULL,
      `so_billto_country_short` varchar(80) DEFAULT NULL,
      `so_billto_postal_code` varchar(20) DEFAULT NULL,
      `so_billto_phone1` varchar(20) DEFAULT NULL,
      `so_billto_phone2` varchar(20) DEFAULT NULL,
      `so_billto_email` varchar(40) DEFAULT NULL,
      `so_billto_language` varchar(20) DEFAULT NULL,
      `so_billto_price_level` varchar(20) DEFAULT NULL,
      `so_shipto_cust_sid` varchar(40) DEFAULT NULL,
      `so_shipto_rpro_cust_sid` varchar(40) DEFAULT NULL,
      `so_shipto_date_created` datetime DEFAULT NULL,
      `so_shipto_title` varchar(80) DEFAULT NULL,
      `so_shipto_first_name` varchar(80) DEFAULT NULL,
      `so_shipto_last_name` varchar(80) DEFAULT NULL,
      `so_shipto_company` varchar(255) DEFAULT NULL,
      `so_shipto_address1` varchar(80) DEFAULT NULL,
      `so_shipto_address2` varchar(80) DEFAULT NULL,
      `so_shipto_city` varchar(80) DEFAULT NULL,
      `so_shipto_state_or_province` varchar(80) DEFAULT NULL,
      `so_shipto_state_short` varchar(20) DEFAULT NULL,
      `so_shipto_country` varchar(40) DEFAULT NULL,
      `so_shipto_country_short` varchar(40) DEFAULT NULL,
      `so_shipto_postal_code` varchar(20) DEFAULT NULL,
      `so_shipto_phone1` varchar(20) DEFAULT NULL,
      `so_shipto_phone2` varchar(20) DEFAULT NULL,
      `so_shipto_email` varchar(80) DEFAULT NULL,
      `so_shipto_language` varchar(20) DEFAULT NULL,
      `so_shipto_price_level` varchar(20) DEFAULT NULL,
      `shipping_method` varchar(80) DEFAULT NULL,
      `shipping_provider` varchar(80) DEFAULT NULL,
      `cc_type` varchar(20) DEFAULT NULL,
      `cc_name` varchar(80) DEFAULT NULL,
      `cc_number` varchar(20) DEFAULT NULL,
      `cc_expire` varchar(20) DEFAULT NULL,
      `cc_expireformat` varchar(20) DEFAULT NULL,
      `so_dateformat` varchar(20) DEFAULT NULL,
      `so_ref` varchar(200) DEFAULT NULL,
      `avs_code` varchar(20) DEFAULT NULL,
      `disc_percent` varchar(20) DEFAULT NULL,
      `ship_percent` varchar(20) DEFAULT NULL,
      `disc_amount` varchar(20) DEFAULT NULL,
      `ship_amount` varchar(20) DEFAULT NULL,
      `total_tax` varchar(200) DEFAULT NULL,
      `subtotal_used` varchar(200) DEFAULT NULL,
      `tax_area` varchar(200) DEFAULT NULL,
      `instruction` text,
      `gift_slip` varchar(20) DEFAULT NULL,
      `STATUS` varchar(20) DEFAULT NULL,
      `items_in` int(11) DEFAULT NULL,
      `so_origin` varchar(10) DEFAULT NULL,
      `uid` int(11) DEFAULT NULL,
      `handling_amount` decimal(12,4) DEFAULT NULL,
      `so_type` int(11) DEFAULT NULL,
      `rdi_export_date` datetime DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_in_categories` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_categories`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_in_categories` (
      `catalog_id` int(11) NOT NULL,
      `site_id` int(11) DEFAULT NULL,
      `parent_id` int(11) DEFAULT NULL,
      `category` varchar(255) DEFAULT NULL,
      `description` mediumtext,
      `sort_order` int(11) DEFAULT NULL,
      `meta_description` mediumtext,
      `meta_keywords` mediumtext,
      `meta_title` varchar(255) DEFAULT NULL,
      `image_path` varchar(255) DEFAULT NULL,
      `level` int(10) DEFAULT NULL,
      PRIMARY KEY (`catalog_id`),
      KEY `site_id` (`site_id`),
      KEY `parent_id` (`parent_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_in_categories_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_categories_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_in_categories_log` (
      `catalog_id` int(11) DEFAULT NULL,
      `site_id` int(11) DEFAULT NULL,
      `parent_id` int(11) DEFAULT NULL,
      `category` varchar(255) DEFAULT NULL,
      `description` mediumtext,
      `sort_order` int(11) DEFAULT NULL,
      `meta_description` mediumtext,
      `meta_keywords` mediumtext,
      `meta_title` varchar(255) DEFAULT NULL,
      `image_path` varchar(255) DEFAULT NULL,
      `level` int(11) DEFAULT NULL,
      `rdi_import_date` datetime DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC";

    /*Table structure for table `rpro_in_category_products` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_category_products`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_in_category_products` (
      `catalog_id` int(11) NOT NULL,
      `style_sid` varchar(64) COLLATE utf8_general_ci NOT NULL,
      `sort_order` int(11) DEFAULT NULL,
      `date_added` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      PRIMARY KEY (`catalog_id`,`style_sid`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8  ROW_FORMAT=DYNAMIC";

    /*Table structure for table `rpro_in_category_products_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_category_products_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_in_category_products_log` (
      `catalog_id` int(11) DEFAULT NULL,
      `style_sid` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `sort_order` int(11) DEFAULT NULL,
      `date_added` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `rdi_import_date` datetime DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8  ROW_FORMAT=DYNAMIC";

    /*Table structure for table `rpro_in_customers` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_customers`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_in_customers` (
      `fldcustsid` varchar(50) DEFAULT NULL,
      `fldtitle` varchar(50) DEFAULT NULL,
      `fldfname` varchar(50) DEFAULT NULL,
      `fldlname` varchar(50) DEFAULT NULL,
      `fldcompany` varchar(100) DEFAULT NULL,
      `fldaddr1` varchar(50) DEFAULT NULL,
      `fldaddr2` varchar(50) DEFAULT NULL,
      `fldaddr3` varchar(50) DEFAULT NULL,
      `fldzip` varchar(50) DEFAULT NULL,
      `fldphone1` varchar(50) DEFAULT NULL,
      `fldphone2` varchar(50) DEFAULT NULL,
      `fldcustid` varchar(50) DEFAULT NULL,
      `web_cust_sid` varchar(50) DEFAULT NULL,
      `email` varchar(100) DEFAULT NULL,
      `fldprclvl` varchar(50) DEFAULT NULL,
      `fldprclvl_i` varchar(50) DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_in_customers_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_customers_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_in_customers_log` (
      `fldcustsid` varchar(50) DEFAULT NULL,
      `fldtitle` varchar(50) DEFAULT NULL,
      `fldfname` varchar(50) DEFAULT NULL,
      `fldlname` varchar(50) DEFAULT NULL,
      `fldcompany` varchar(100) DEFAULT NULL,
      `fldaddr1` varchar(50) DEFAULT NULL,
      `fldaddr2` varchar(50) DEFAULT NULL,
      `fldaddr3` varchar(50) DEFAULT NULL,
      `fldzip` varchar(50) DEFAULT NULL,
      `fldphone1` varchar(50) DEFAULT NULL,
      `fldphone2` varchar(50) DEFAULT NULL,
      `fldcustid` varchar(50) DEFAULT NULL,
      `web_cust_sid` varchar(50) DEFAULT NULL,
      `email` varchar(100) DEFAULT NULL,
      `fldprclvl` varchar(50) DEFAULT NULL,
      `fldprclvl_i` varchar(50) DEFAULT NULL,
      `rdi_import_date` datetime DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_in_items` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_items`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_in_items` (
      `style_sid` varchar(64) COLLATE utf8_general_ci NOT NULL DEFAULT '',
      `item_sid` varchar(64) COLLATE utf8_general_ci NOT NULL DEFAULT '',
      `item_num` varchar(16) COLLATE utf8_general_ci DEFAULT '0',
      `alu` varchar(128) COLLATE utf8_general_ci DEFAULT NULL,
      `upc` varchar(128) COLLATE utf8_general_ci DEFAULT NULL,
      `ship_weight1` int(11) DEFAULT NULL,
      `ship_weight2` int(11) DEFAULT NULL,
      `oversized` int(1) DEFAULT '0',
      `ship_method` varchar(16) COLLATE utf8_general_ci DEFAULT NULL,
      `featured` int(1) DEFAULT '0',
      `height` varchar(16) COLLATE utf8_general_ci DEFAULT NULL,
      `length` varchar(16) COLLATE utf8_general_ci DEFAULT NULL,
      `width` varchar(16) COLLATE utf8_general_ci DEFAULT NULL,
      `dim_unit` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `weight_unit` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `desc3` VARCHAR(50) NULL DEFAULT NULL,
            `desc4` VARCHAR(50) NULL DEFAULT NULL,
      `text1` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text2` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text3` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text4` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text5` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text6` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text7` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text8` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text9` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text10` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf_date` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `udf_name` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf1` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf2` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf3` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf4` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf5` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf6` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf7` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf8` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf9` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf10` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf11` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf12` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf13` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf14` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
            `image1` VARCHAR(100) NULL DEFAULT '1',
            `image2` VARCHAR(100) NULL DEFAULT '1',
            `image3` VARCHAR(100) NULL DEFAULT '1',
            `image4` VARCHAR(100) NULL DEFAULT '1',
            `image5` VARCHAR(100) NULL DEFAULT '1',
            `image6` VARCHAR(100) NULL DEFAULT '1',
            `image7` VARCHAR(100) NULL DEFAULT '1',
            `image8` VARCHAR(100) NULL DEFAULT '1',
            `image9` VARCHAR(100) NULL DEFAULT '1',
            `image10` VARCHAR(100) NULL DEFAULT '1',
      `attr` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `size` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `attr_order` varchar(16) COLLATE utf8_general_ci DEFAULT NULL,
      `size_order` varchar(16) COLLATE utf8_general_ci DEFAULT NULL,
      `cost` decimal(10,2) DEFAULT NULL,
      `price` decimal(10,2) DEFAULT NULL,
      `markdown_price` decimal(10,2) DEFAULT NULL,
      `reg_price` decimal(10,2) DEFAULT NULL,
      `sale_price` decimal(10,2) DEFAULT NULL,
      `msrp_price` decimal(10,2) DEFAULT NULL,
      `wholesale_price` decimal(10,2) DEFAULT NULL,
      `quantity` int(11) DEFAULT '0',
      `comp_quantity` int(11) DEFAULT '0',
      `qty_per_case` int(11) DEFAULT '0',
      `tax_code` varchar(16) COLLATE utf8_general_ci DEFAULT NULL,
      `active` int(1) DEFAULT '1',
      PRIMARY KEY (`style_sid`,`item_sid`),
      KEY `style_sid` (`style_sid`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC";

    /*Table structure for table `rpro_in_items_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_items_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_in_items_log` (
      `style_sid` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `item_sid` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `item_num` varchar(16) COLLATE utf8_general_ci DEFAULT '0',
      `alu` varchar(128) COLLATE utf8_general_ci DEFAULT NULL,
      `upc` varchar(128) COLLATE utf8_general_ci DEFAULT NULL,
      `ship_weight1` int(11) DEFAULT NULL,
      `ship_weight2` int(11) DEFAULT NULL,
      `oversized` int(1) DEFAULT '0',
      `ship_method` varchar(16) COLLATE utf8_general_ci DEFAULT NULL,
      `featured` int(1) DEFAULT '0',
      `height` varchar(16) COLLATE utf8_general_ci DEFAULT NULL,
      `length` varchar(16) COLLATE utf8_general_ci DEFAULT NULL,
      `width` varchar(16) COLLATE utf8_general_ci DEFAULT NULL,
      `dim_unit` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `weight_unit` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `desc3` VARCHAR(50) NULL DEFAULT NULL,
            `desc4` VARCHAR(50) NULL DEFAULT NULL,
      `text1` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text2` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text3` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text4` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text5` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text6` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text7` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text8` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text9` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `text10` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf_date` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `udf_name` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf1` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf2` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf3` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf4` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf5` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf6` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf7` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf8` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf9` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf10` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf11` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf12` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf13` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf14` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
            `image1` VARCHAR(100) NULL DEFAULT '1',
            `image2` VARCHAR(100) NULL DEFAULT '1',
            `image3` VARCHAR(100) NULL DEFAULT '1',
            `image4` VARCHAR(100) NULL DEFAULT '1',
            `image5` VARCHAR(100) NULL DEFAULT '1',
            `image6` VARCHAR(100) NULL DEFAULT '1',
            `image7` VARCHAR(100) NULL DEFAULT '1',
            `image8` VARCHAR(100) NULL DEFAULT '1',
            `image9` VARCHAR(100) NULL DEFAULT '1',
            `image10` VARCHAR(100) NULL DEFAULT '1',
      `attr` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `size` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `attr_order` varchar(16) COLLATE utf8_general_ci DEFAULT NULL,
      `size_order` varchar(16) COLLATE utf8_general_ci DEFAULT NULL,
      `cost` decimal(10,2) DEFAULT NULL,
      `price` decimal(10,2) DEFAULT NULL,
      `markdown_price` decimal(10,2) DEFAULT NULL,
      `reg_price` decimal(10,2) DEFAULT NULL,
      `sale_price` decimal(10,2) DEFAULT NULL,
      `msrp_price` decimal(10,2) DEFAULT NULL,
      `wholesale_price` decimal(10,2) DEFAULT NULL,
      `quantity` int(11) DEFAULT '0',
      `comp_quantity` int(11) DEFAULT '0',
      `qty_per_case` int(11) DEFAULT '0',
      `tax_code` varchar(16) COLLATE utf8_general_ci DEFAULT NULL,
      `active` int(1) DEFAULT '1',
      `rdi_import_date` datetime DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC";

    /*Table structure for table `rpro_in_receipts` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_receipts`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_in_receipts` (
      `so_number` varchar(50) DEFAULT NULL,
      `receipt_number` varchar(50) DEFAULT NULL,
      `receipt_sid` varchar(50) DEFAULT NULL,
      `storestation` varchar(50) DEFAULT NULL,
      `receipt_date` varchar(50) DEFAULT NULL,
      `receipt_subtotal` varchar(50) DEFAULT NULL,
      `receipt_shipamount` varchar(50) DEFAULT NULL,
      `receipt_feeamount` varchar(50) DEFAULT NULL,
      `receipt_taxarea` varchar(50) DEFAULT NULL,
      `receipt_totaltax` varchar(50) DEFAULT NULL,
      `receipt_total` varchar(50) DEFAULT NULL,
      `receipt_item_number` varchar(50) DEFAULT NULL,
      `sid` varchar(50) DEFAULT NULL,
      `qty` varchar(50) DEFAULT NULL,
      `extprc` varchar(50) DEFAULT NULL,
      `extpwt` varchar(50) DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_in_receipts_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_receipts_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_in_receipts_log` (
      `so_number` varchar(50) DEFAULT NULL,
      `receipt_number` varchar(50) DEFAULT NULL,
      `receipt_sid` varchar(50) DEFAULT NULL,
      `storestation` varchar(50) DEFAULT NULL,
      `receipt_date` varchar(50) DEFAULT NULL,
      `receipt_subtotal` varchar(50) DEFAULT NULL,
      `receipt_shipamount` varchar(50) DEFAULT NULL,
      `receipt_feeamount` varchar(50) DEFAULT NULL,
      `receipt_taxarea` varchar(50) DEFAULT NULL,
      `receipt_totaltax` varchar(50) DEFAULT NULL,
      `receipt_total` varchar(50) DEFAULT NULL,
      `receipt_item_number` varchar(50) DEFAULT NULL,
      `sid` varchar(50) DEFAULT NULL,
      `qty` varchar(50) DEFAULT NULL,
      `extprc` varchar(50) DEFAULT NULL,
      `extpwt` varchar(50) DEFAULT NULL,
      `rdi_import_date` datetime DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_in_so` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_so`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_in_so` (
      `so_number` varchar(50) DEFAULT NULL,
      `so_doc_no` varchar(50) DEFAULT NULL,
      `sid` varchar(50) DEFAULT NULL,
      `invc_no` varchar(50) DEFAULT NULL,
      `status` varchar(50) DEFAULT NULL,
      `tender_amt` double DEFAULT NULL,
      `subtotal` double DEFAULT NULL,
      `tax_total` double DEFAULT NULL,
      `fee_amt` double DEFAULT NULL,
      `fee_type` varchar(50) DEFAULT NULL,
      `disc_amt` double DEFAULT NULL,
      `capture_fund` varchar(50) DEFAULT NULL,
      `qty_shipped` double DEFAULT NULL,
      `ship_date` varchar(50) DEFAULT NULL,
      `item_sid` varchar(50) DEFAULT NULL,
      `item_number` varchar(50) DEFAULT NULL,
      `item_tax` double DEFAULT NULL,
      `item_orig_price` double DEFAULT NULL,
      `item_ext_price` double DEFAULT NULL,
      `item_price` double DEFAULT NULL,
      `item_qty` double DEFAULT NULL,
      `tracking_number` varchar(100) DEFAULT NULL,
      `shipprovider` varchar(100) DEFAULT 'UPS',
      KEY `so_number` (`so_number`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_in_so_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_so_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_in_so_log` (
      `so_number` varchar(50) DEFAULT NULL,
      `so_doc_no` varchar(50) DEFAULT NULL,
      `sid` varchar(50) DEFAULT NULL,
      `invc_no` varchar(50) DEFAULT NULL,
      `status` varchar(50) DEFAULT NULL,
      `tender_amt` double DEFAULT NULL,
      `subtotal` double DEFAULT NULL,
      `tax_total` double DEFAULT NULL,
      `fee_amt` double DEFAULT NULL,
      `fee_type` varchar(50) DEFAULT NULL,
      `disc_amt` double DEFAULT NULL,
      `capture_fund` varchar(50) DEFAULT NULL,
      `qty_shipped` double DEFAULT NULL,
      `ship_date` varchar(50) DEFAULT NULL,
      `item_sid` varchar(50) DEFAULT NULL,
      `item_number` varchar(50) DEFAULT NULL,
      `item_tax` double DEFAULT NULL,
      `item_orig_price` double DEFAULT NULL,
      `item_ext_price` double DEFAULT NULL,
      `item_price` double DEFAULT NULL,
      `item_qty` double DEFAULT NULL,
      `tracking_number` varchar(100) DEFAULT NULL,
      `shipprovider` varchar(100) DEFAULT NULL,
      `rdi_import_date` datetime DEFAULT NULL,
      KEY `so_number` (`so_number`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_in_styles` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_styles`;

    $sql = "

    CREATE TABLE `rpro_in_styles` (
            `style_sid` VARCHAR(64) NOT NULL DEFAULT '',
            `scale` INT(6) NULL DEFAULT NULL,
            `scale_name` VARCHAR(64) NULL DEFAULT NULL,
            `dcs` VARCHAR(64) NULL DEFAULT NULL,
            `dcs_name` VARCHAR(128) NULL DEFAULT NULL,
            `department_code` VARCHAR(12) NULL DEFAULT NULL,
            `department_name` VARCHAR(64) NULL DEFAULT NULL,
            `class_code` VARCHAR(12) NULL DEFAULT NULL,
            `class_name` VARCHAR(64) NULL DEFAULT NULL,
            `subclass_code` VARCHAR(12) NULL DEFAULT NULL,
            `subclass_name` VARCHAR(64) NULL DEFAULT NULL,
            `vendor_code` VARCHAR(60) NULL DEFAULT NULL,
            `vendor` VARCHAR(255) NULL DEFAULT NULL,
            `vend_info1` VARCHAR(255) NULL DEFAULT NULL,
            `vend_info2` VARCHAR(255) NULL DEFAULT NULL,
            `desc1` VARCHAR(255) NULL DEFAULT NULL,
            `desc2` VARCHAR(255) NULL DEFAULT NULL,
            `desc3` VARCHAR(255) NULL DEFAULT NULL,
            `desc4` VARCHAR(255) NULL DEFAULT NULL,
            `eci` INT(6) NULL DEFAULT '0',
            `long_desc` TEXT NULL,
            `product_name` VARCHAR(200) NULL DEFAULT NULL,
            `alt1_desc` TEXT NULL,
            `alt2_desc` TEXT NULL,
            `meta_title` VARCHAR(200) NULL DEFAULT NULL,
            `meta_keywords` VARCHAR(500) NULL DEFAULT NULL,
            `meta_desc` TEXT(50) NULL DEFAULT NULL,
            `threshold` DECIMAL(10,2) NULL DEFAULT NULL,
            `avail` VARCHAR(50) NULL DEFAULT NULL,
            `out_of_stock_msg` VARCHAR(100) NULL DEFAULT NULL,
            `style_image` VARCHAR(64) NULL DEFAULT NULL,
            PRIMARY KEY (`style_sid`)
    )
    ENGINE=MYISAM  DEFAULT CHARSET=utf8;";

    /*Table structure for table `rpro_in_styles_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_styles_log`;

    $sql = "CREATE TABLE `rpro_in_styles_log` (
            `style_sid` VARCHAR(64) NULL DEFAULT '',
            `scale` INT(6) NULL DEFAULT NULL,
            `scale_name` VARCHAR(64) NULL DEFAULT NULL,
            `dcs` VARCHAR(64) NULL DEFAULT NULL,
            `dcs_name` VARCHAR(128) NULL DEFAULT NULL,
            `department_code` VARCHAR(12) NULL DEFAULT NULL,
            `department_name` VARCHAR(64) NULL DEFAULT NULL,
            `class_code` VARCHAR(12) NULL DEFAULT NULL,
            `class_name` VARCHAR(64) NULL DEFAULT NULL,
            `subclass_code` VARCHAR(12) NULL DEFAULT NULL,
            `subclass_name` VARCHAR(64) NULL DEFAULT NULL,
            `vendor_code` VARCHAR(64) NULL DEFAULT NULL,
            `vendor` VARCHAR(255) NULL DEFAULT NULL,
            `vend_info1` VARCHAR(255) NULL DEFAULT NULL,
            `vend_info2` VARCHAR(255) NULL DEFAULT NULL,
            `desc1` VARCHAR(255) NULL DEFAULT NULL,
            `desc2` VARCHAR(255) NULL DEFAULT NULL,
            `desc3` VARCHAR(255) NULL DEFAULT NULL,
            `desc4` VARCHAR(255) NULL DEFAULT NULL,
            `eci` INT(6) NULL DEFAULT '0',
            `long_desc` TEXT NULL,
            `product_name` VARCHAR(50) NULL DEFAULT NULL,
            `alt1_desc` TEXT NULL,
            `alt2_desc` TEXT NULL,
            `meta_title` VARCHAR(50) NULL DEFAULT NULL,
            `meta_keywords` VARCHAR(50) NULL DEFAULT NULL,
            `meta_desc` VARCHAR(50) NULL DEFAULT NULL,
            `threshold` DECIMAL(10,2) NULL DEFAULT NULL,
            `avail` VARCHAR(50) NULL DEFAULT NULL,
            `out_of_stock_msg` VARCHAR(100) NULL DEFAULT NULL,
            `style_image` VARCHAR(64) NULL DEFAULT NULL,
            `rdi_import_date` DATETIME NULL DEFAULT NULL
    )
    ENGINE=MYISAM  DEFAULT CHARSET=utf8;";

    /*Table structure for table `rpro_in_upsell_item` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_upsell_item`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_in_upsell_item` (
      `fldstylesid` varchar(50) DEFAULT NULL,
      `fldupsellsid` varchar(50) DEFAULT NULL,
      `fldorderno` varchar(50) DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_in_upsell_item_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_in_upsell_item_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_in_upsell_item_log` (
      `fldstylesid` varchar(50) DEFAULT NULL,
      `fldupsellsid` varchar(50) DEFAULT NULL,
      `fldorderno` varchar(50) DEFAULT NULL,
      `rdi_import_date` datetime DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_out_customers` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_out_customers`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_out_customers` (
      `customer_id` int(11) DEFAULT NULL,
      `rpro_cust_sid` varchar(100) NOT NULL DEFAULT '',
      `first_name` varchar(100) NOT NULL DEFAULT '',
      `last_name` varchar(100) NOT NULL DEFAULT '',
      `address1` varchar(200) NOT NULL DEFAULT '',
      `address2` varchar(200) NOT NULL DEFAULT '',
      `city` varchar(200) NOT NULL DEFAULT '',
      `state` varchar(50) DEFAULT NULL,
      `region` varchar(200) NOT NULL DEFAULT '',
      `zip` varchar(20) NOT NULL DEFAULT '',
      `country` varchar(200) NOT NULL DEFAULT '',
      `country_code` varchar(20) NOT NULL DEFAULT '',
      `phone` varchar(30) NOT NULL DEFAULT '',
      `email` varchar(100) NOT NULL DEFAULT '',
      `login_id` varchar(100) DEFAULT NULL,
      `PASSWORD` varchar(100) DEFAULT NULL,
      `orders_num` int(11) DEFAULT NULL,
      `has_so` int(11) DEFAULT NULL,
      `company` varchar(100) DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_out_customers_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_out_customers_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_out_customers_log` (
      `customer_id` int(11) DEFAULT NULL,
      `rpro_cust_sid` varchar(100) NOT NULL DEFAULT '',
      `first_name` varchar(100) NOT NULL DEFAULT '',
      `last_name` varchar(100) NOT NULL DEFAULT '',
      `address1` varchar(200) NOT NULL DEFAULT '',
      `address2` varchar(200) NOT NULL DEFAULT '',
      `city` varchar(200) NOT NULL DEFAULT '',
      `state` varchar(50) DEFAULT NULL,
      `region` varchar(200) NOT NULL DEFAULT '',
      `zip` varchar(20) NOT NULL DEFAULT '',
      `country` varchar(200) NOT NULL DEFAULT '',
      `country_code` varchar(20) NOT NULL DEFAULT '',
      `phone` varchar(30) NOT NULL DEFAULT '',
      `email` varchar(100) NOT NULL DEFAULT '',
      `login_id` varchar(100) DEFAULT NULL,
      `PASSWORD` varchar(100) DEFAULT NULL,
      `orders_num` int(11) DEFAULT NULL,
      `has_so` int(11) DEFAULT NULL,
      `company` varchar(100) DEFAULT NULL,
      `rdi_export_date` datetime DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_out_so` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_out_so`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_out_so` (
      `orderid` int(12) NOT NULL,
      `so_sid` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `store_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `station` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `so_no` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `so_type` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `orig_store_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `orig_station` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `trgt_store_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `trgt_station` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `cust_sid` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `addr_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `shipto_cust_sid` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `shipto_addr_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `cust_po_no` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `status` varchar(10) COLLATE utf8_general_ci DEFAULT '2',
      `priority` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `use_vat` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `disc_perc` varchar(12) COLLATE utf8_general_ci DEFAULT '',
      `disc_amt` varchar(12) COLLATE utf8_general_ci DEFAULT '',
      `disc_perc_spread` varchar(12) COLLATE utf8_general_ci DEFAULT '',
      `used_disc_amt` varchar(12) COLLATE utf8_general_ci DEFAULT '',
      `over_tax_perc` varchar(12) COLLATE utf8_general_ci DEFAULT '',
      `over_tax_perc2` varchar(12) COLLATE utf8_general_ci DEFAULT '',
      `created_date` varchar(32) COLLATE utf8_general_ci DEFAULT '',
      `modified_date` varchar(32) COLLATE utf8_general_ci DEFAULT '',
      `shipping_date` varchar(32) COLLATE utf8_general_ci DEFAULT '',
      `cancel_date` varchar(32) COLLATE utf8_general_ci DEFAULT '',
      `note` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `ref_so_sid` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `cms` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `active` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `verified` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `held` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `cms_post_date` varchar(32) COLLATE utf8_general_ci DEFAULT '',
      `pkg_no` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `doc_source` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `controller` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `orig_controller` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `elapsed_time` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `line_pos_seq` varchar(10) COLLATE utf8_general_ci DEFAULT '2',
      `used_subtotal` varchar(12) COLLATE utf8_general_ci DEFAULT '',
      `used_tax` varchar(12) COLLATE utf8_general_ci DEFAULT '',
      `activity_perc` varchar(12) COLLATE utf8_general_ci DEFAULT '100',
      `activity_perc2` varchar(12) COLLATE utf8_general_ci DEFAULT '',
      `activity_perc3` varchar(12) COLLATE utf8_general_ci DEFAULT '',
      `activity_perc4` varchar(12) COLLATE utf8_general_ci DEFAULT '',
      `activity_perc5` varchar(12) COLLATE utf8_general_ci DEFAULT '',
      `detax` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `empl_sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `empl_name` varchar(64) COLLATE utf8_general_ci DEFAULT 'SYSADMIN',
      `ship_method` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `tax_area_name` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `tax_area2_name` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `web_so_type` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `modifiedby_sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `modifiedby_empl_name` varchar(64) COLLATE utf8_general_ci DEFAULT 'SYSADMIN',
      `createdby_sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `createdby_empl_name` varchar(64) COLLATE utf8_general_ci DEFAULT 'SYSADMIN',
      `clerk_sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `clerk_name` varchar(64) COLLATE utf8_general_ci DEFAULT 'SYSADMIN',
      `clerk_sbs_no2` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `clerk_name2` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `clerk_sbs_no3` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `clerk_name3` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `clerk_sbs_no4` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `clerk_name4` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `clerk_sbs_no5` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `clerk_name5` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `customer_cust_sid` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `customer_cust_id` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `customer_store_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `customer_station` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `customer_first_name` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `customer_last_name` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `customer_price_lvl` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `customer_detax` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `customer_info1` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `customer_info2` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `customer_modified_date` varchar(32) COLLATE utf8_general_ci DEFAULT '',
      `customer_sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `customer_cms` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `customer_company_name` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `customer_title` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `customer_tax_area_name` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `customer_shipping` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `customer_address1` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `customer_address2` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `customer_address3` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `customer_address4` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `customer_address5` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `customer_address6` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `customer_zip` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `customer_phone1` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `customer_phone2` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `customer_email` varchar(128) COLLATE utf8_general_ci DEFAULT '',
      `customer_country_name` varchar(255) COLLATE utf8_general_ci DEFAULT 'UNITED STATES',
      `shipto_customer_cust_sid` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_cust_id` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_store_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `shipto_customer_station` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_first_name` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_last_name` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_price_lvl` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_detax` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `shipto_customer_info1` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_info2` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_modified_date` varchar(32) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `shipto_customer_cms` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `shipto_customer_company_name` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_title` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_tax_area_name` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_shipping` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `shipto_customer_address1` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_address2` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_address3` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_address4` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_address5` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_address6` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_zip` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_phone1` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_phone2` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_email` varchar(128) COLLATE utf8_general_ci DEFAULT '',
      `shipto_customer_country_name` varchar(255) COLLATE utf8_general_ci DEFAULT 'UNITED STATES',
      `tender_type` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `cardholder_name` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `crd_name` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `shipping_amt` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `shipping_tax` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      PRIMARY KEY (`orderid`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_out_so_items` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_out_so_items`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_out_so_items` (
      `itemid` INT(12) NOT NULL AUTO_INCREMENT,
      `orderid` int(12) DEFAULT NULL,
      `so_sid` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `item_sid` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `item_pos` int(12) DEFAULT '1',
      `orig_price` decimal(12,4) DEFAULT '0.0000',
      `orig_tax_amt` decimal(12,4) DEFAULT '0.0000',
      `price` decimal(12,4) DEFAULT '0.0000',
      `cost` decimal(12,4) DEFAULT '0.0000',
      `tax_code` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `tax_perc` decimal(12,4) DEFAULT '0.0000',
      `tax_amt` decimal(12,4) DEFAULT '0.0000',
      `tax_code2` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `tax_perc2` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `tax_amt2` decimal(12,4) DEFAULT '0.0000',
      `ord_qty` int(12) DEFAULT '0',
      `sent_qty` int(12) DEFAULT '0',
      `price_lvl` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `sched_no` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `comm_code` varchar(10) COLLATE utf8_general_ci DEFAULT '-1',
      `spif` varchar(32) COLLATE utf8_general_ci DEFAULT '',
      `scan_upc` varchar(128) COLLATE utf8_general_ci DEFAULT '',
      `serial_no` varchar(128) COLLATE utf8_general_ci DEFAULT '',
      `lot_number` varchar(32) COLLATE utf8_general_ci DEFAULT '',
      `kit_flag` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `pkg_item_sid` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `pkg_seq_no` varchar(32) COLLATE utf8_general_ci DEFAULT '',
      `orig_cmpnt_item_sid` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `detax` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `usr_disc_perc` decimal(12,4) DEFAULT '0.0000',
      `shipto_cust_sid` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `shipto_addr_no` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `pkg_no` varchar(32) COLLATE utf8_general_ci DEFAULT '',
      `udf_value1` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `udf_value2` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `udf_value3` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `udf_value4` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `activity_perc` varchar(4) COLLATE utf8_general_ci DEFAULT '100',
      `activity_perc1` varchar(4) COLLATE utf8_general_ci DEFAULT '',
      `activity_perc2` varchar(4) COLLATE utf8_general_ci DEFAULT '',
      `activity_perc3` varchar(4) COLLATE utf8_general_ci DEFAULT '',
      `activity_perc4` varchar(4) COLLATE utf8_general_ci DEFAULT '',
      `activity_perc5` varchar(4) COLLATE utf8_general_ci DEFAULT '',
      `orig_item_pos` int(12) DEFAULT '1',
      `promo_flag` varchar(10) COLLATE utf8_general_ci DEFAULT '0',
      `item_note1` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `item_note2` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `item_note3` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `item_note4` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `item_note5` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `item_note6` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `item_note7` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `item_note8` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `item_note9` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `item_note10` varchar(255) COLLATE utf8_general_ci DEFAULT '',
      `alt_upc` varchar(128) COLLATE utf8_general_ci DEFAULT '',
      `alt_alu` varchar(128) COLLATE utf8_general_ci DEFAULT '',
      `alt_cost` decimal(12,4) DEFAULT '0.0000',
      `alt_vend_code` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `empl_sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT '1',
      `empl_name` varchar(64) COLLATE utf8_general_ci DEFAULT 'SYSADMIN',
      `tax_area2_name` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `disc_reason_name` varchar(128) COLLATE utf8_general_ci DEFAULT '',
      `empl_sbs_no2` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `empl_name2` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `empl_sbs_no3` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `empl_name3` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `empl_sbs_no4` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `empl_name4` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `empl_sbs_no5` varchar(10) COLLATE utf8_general_ci DEFAULT '',
      `empl_name5` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      `ship_method` varchar(64) COLLATE utf8_general_ci DEFAULT '',
      PRIMARY KEY (`itemid`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_out_so_items_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_out_so_items_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_out_so_items_log` (
      `itemid` INT(12) NOT NULL ,
      `orderid` int(12) DEFAULT NULL,
      `so_sid` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `item_sid` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `item_pos` int(12) DEFAULT NULL,
      `orig_price` decimal(12,4) DEFAULT NULL,
      `orig_tax_amt` decimal(12,4) DEFAULT NULL,
      `price` decimal(12,4) DEFAULT NULL,
      `cost` decimal(12,4) DEFAULT NULL,
      `tax_code` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `tax_perc` decimal(12,4) DEFAULT NULL,
      `tax_amt` decimal(12,4) DEFAULT NULL,
      `tax_code2` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `tax_perc2` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `tax_amt2` decimal(12,4) DEFAULT NULL,
      `ord_qty` int(12) DEFAULT NULL,
      `sent_qty` int(12) DEFAULT NULL,
      `price_lvl` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `sched_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `comm_code` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `spif` varchar(32) COLLATE utf8_general_ci DEFAULT NULL,
      `scan_upc` varchar(128) COLLATE utf8_general_ci DEFAULT NULL,
      `serial_no` varchar(128) COLLATE utf8_general_ci DEFAULT NULL,
      `lot_number` varchar(32) COLLATE utf8_general_ci DEFAULT NULL,
      `kit_flag` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `pkg_item_sid` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `pkg_seq_no` varchar(32) COLLATE utf8_general_ci DEFAULT NULL,
      `orig_cmpnt_item_sid` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `detax` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `usr_disc_perc` decimal(12,4) DEFAULT NULL,
      `shipto_cust_sid` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_addr_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `pkg_no` varchar(32) COLLATE utf8_general_ci DEFAULT NULL,
      `udf_value1` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf_value2` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf_value3` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `udf_value4` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `activity_perc` varchar(4) COLLATE utf8_general_ci DEFAULT NULL,
      `activity_perc1` varchar(4) COLLATE utf8_general_ci DEFAULT NULL,
      `activity_perc2` varchar(4) COLLATE utf8_general_ci DEFAULT NULL,
      `activity_perc3` varchar(4) COLLATE utf8_general_ci DEFAULT NULL,
      `activity_perc4` varchar(4) COLLATE utf8_general_ci DEFAULT NULL,
      `activity_perc5` varchar(4) COLLATE utf8_general_ci DEFAULT NULL,
      `orig_item_pos` int(12) DEFAULT NULL,
      `promo_flag` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `item_note1` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `item_note2` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `item_note3` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `item_note4` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `item_note5` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `item_note6` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `item_note7` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `item_note8` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `item_note9` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `item_note10` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `alt_upc` varchar(128) COLLATE utf8_general_ci DEFAULT NULL,
      `alt_alu` varchar(128) COLLATE utf8_general_ci DEFAULT NULL,
      `alt_cost` decimal(12,4) DEFAULT NULL,
      `alt_vend_code` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `empl_sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `empl_name` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `tax_area2_name` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `disc_reason_name` varchar(128) COLLATE utf8_general_ci DEFAULT NULL,
      `empl_sbs_no2` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `empl_name2` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `empl_sbs_no3` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `empl_name3` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `empl_sbs_no4` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `empl_name4` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `empl_sbs_no5` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `empl_name5` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `ship_method` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `rdi_export_date` datetime DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Table structure for table `rpro_out_so_log` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_out_so_log`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_out_so_log` (
      `orderid` int(12) NOT NULL,
      `so_sid` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `store_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `station` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `so_no` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `so_type` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `orig_store_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `orig_station` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `trgt_store_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `trgt_station` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `cust_sid` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `addr_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_cust_sid` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_addr_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `cust_po_no` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `status` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `priority` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `use_vat` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `disc_perc` varchar(12) COLLATE utf8_general_ci DEFAULT NULL,
      `disc_amt` varchar(12) COLLATE utf8_general_ci DEFAULT NULL,
      `disc_perc_spread` varchar(12) COLLATE utf8_general_ci DEFAULT NULL,
      `used_disc_amt` varchar(12) COLLATE utf8_general_ci DEFAULT NULL,
      `over_tax_perc` varchar(12) COLLATE utf8_general_ci DEFAULT NULL,
      `over_tax_perc2` varchar(12) COLLATE utf8_general_ci DEFAULT NULL,
      `created_date` varchar(32) COLLATE utf8_general_ci DEFAULT NULL,
      `modified_date` varchar(32) COLLATE utf8_general_ci DEFAULT NULL,
      `shipping_date` varchar(32) COLLATE utf8_general_ci DEFAULT NULL,
      `cancel_date` varchar(32) COLLATE utf8_general_ci DEFAULT NULL,
      `note` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `ref_so_sid` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `cms` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `active` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `verified` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `held` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `cms_post_date` varchar(32) COLLATE utf8_general_ci DEFAULT NULL,
      `pkg_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `doc_source` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `controller` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `orig_controller` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `elapsed_time` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `line_pos_seq` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `used_subtotal` varchar(12) COLLATE utf8_general_ci DEFAULT NULL,
      `used_tax` varchar(12) COLLATE utf8_general_ci DEFAULT NULL,
      `activity_perc` varchar(12) COLLATE utf8_general_ci DEFAULT NULL,
      `activity_perc2` varchar(12) COLLATE utf8_general_ci DEFAULT NULL,
      `activity_perc3` varchar(12) COLLATE utf8_general_ci DEFAULT NULL,
      `activity_perc4` varchar(12) COLLATE utf8_general_ci DEFAULT NULL,
      `activity_perc5` varchar(12) COLLATE utf8_general_ci DEFAULT NULL,
      `detax` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `empl_sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `empl_name` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `ship_method` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `tax_area_name` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `tax_area2_name` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `web_so_type` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `modifiedby_sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `modifiedby_empl_name` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `createdby_sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `createdby_empl_name` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `clerk_sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `clerk_name` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `clerk_sbs_no2` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `clerk_name2` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `clerk_sbs_no3` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `clerk_name3` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `clerk_sbs_no4` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `clerk_name4` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `clerk_sbs_no5` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `clerk_name5` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_cust_sid` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_cust_id` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_store_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_station` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_first_name` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_last_name` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_price_lvl` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_detax` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_info1` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_info2` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_modified_date` varchar(32) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_cms` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_company_name` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_title` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_tax_area_name` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_shipping` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_address1` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_address2` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_address3` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_address4` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_address5` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_address6` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_zip` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_phone1` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_phone2` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_email` varchar(128) COLLATE utf8_general_ci DEFAULT NULL,
      `customer_country_name` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_cust_sid` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_cust_id` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_store_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_station` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_first_name` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_last_name` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_price_lvl` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_detax` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_info1` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_info2` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_modified_date` varchar(32) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_sbs_no` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_cms` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_company_name` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_title` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_tax_area_name` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_shipping` varchar(10) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_address1` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_address2` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_address3` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_address4` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_address5` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_address6` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_zip` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_phone1` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_phone2` varchar(64) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_email` varchar(128) COLLATE utf8_general_ci DEFAULT NULL,
      `shipto_customer_country_name` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `tender_type` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `cardholder_name` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `crd_name` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `shipping_amt` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `shipping_tax` varchar(255) COLLATE utf8_general_ci DEFAULT NULL,
      `rdi_export_date` datetime DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_attribute_sort`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_attribute_sort` (
      `uid` int(11) NOT NULL AUTO_INCREMENT,
      `attr` varchar(100) DEFAULT NULL,
      PRIMARY KEY (`uid`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Data for the table `rdi_attribute_sort` */

    /*Table structure for table `rdi_card_type_mapping` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_card_type_mapping`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_card_type_mapping` (
      `cart_type` varchar(50) DEFAULT NULL,
      `pos_type` varchar(50) DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
    echo "<br>";echo __LINE__; $db->exec($sql);
    /*Data for the table `rdi_card_type_mapping` */

    $sql = "INSERT INTO  `rdi_card_type_mapping`(`cart_type`,`pos_type`) values ('AE','AMEX'),('VI','VISA'),('MC','MASTER')";

    /*Table structure for table `rdi_cart_class_map_criteria` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_cart_class_map_criteria`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_cart_class_map_criteria` (
      `cart_class_mapping_id` int(10) DEFAULT NULL,
      `cart_field` varchar(80) DEFAULT NULL,
      `qualifier` varchar(150) DEFAULT NULL,
      KEY `cart_type_mapping_id` (`cart_class_mapping_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
    echo "<br>";echo __LINE__; $db->exec($sql);
    /*Data for the table `rdi_cart_class_map_criteria` */

    $sql = "INSERT INTO  `rdi_cart_class_map_criteria`(`cart_class_mapping_id`,`cart_field`,`qualifier`) values (1,'color','IS NOT NULL'),(1,'size','IS NOT NULL'),(2,'color','IS NOT NULL'),(2,'size','IS NULL'),(3,'color','IS NULL'),(3,'size','IS NOT NULL'),(4,'color','IS NULL'),(4,'size','IS NULL')";

    /*Table structure for table `rdi_cart_class_map_fields` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_cart_class_map_fields`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_cart_class_map_fields` (
      `cart_class_mapping_id` int(10) DEFAULT NULL,
      `cart_field` varchar(50) DEFAULT NULL,
      `position` int(10) NOT NULL DEFAULT '0',
      `label` varchar(50) DEFAULT NULL,
      KEY `cart_class_mapping_id` (`cart_class_mapping_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
    echo "<br>";echo __LINE__; $db->exec($sql);
    /*Data for the table `rdi_cart_class_map_fields` */

    $sql = "INSERT INTO  `rdi_cart_class_map_fields`(`cart_class_mapping_id`,`cart_field`,`position`,`label`) values (1,'color',0,NULL),(1,'size',1,NULL),(2,'color',0,NULL),(3,'size',0,NULL)";

    /*Table structure for table `rdi_cart_class_mapping` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_cart_class_mapping`";

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_cart_class_mapping` (
      `cart_class_mapping_id` int(11) NOT NULL AUTO_INCREMENT,
      `product_class_id` int(11) DEFAULT NULL,
      `product_class` varchar(50) DEFAULT NULL,
      PRIMARY KEY (`cart_class_mapping_id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8";
    echo "<br>";echo __LINE__; $db->exec($sql);
    /*Data for the table `rdi_cart_class_mapping` */

    $sql = "INSERT INTO  `rdi_cart_class_mapping`(`cart_class_mapping_id`,`product_class_id`,`product_class`) values (1,4,'Default'),(2,4,'Default'),(3,4,'Default'),(4,4,'Default')";

    /*Table structure for table `rdi_cart_product_types` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_cart_product_types`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_cart_product_types` (
      `cart_product_type_id` int(10) NOT NULL AUTO_INCREMENT,
      `cart_class_mapping_id` int(10) DEFAULT NULL,
      `product_type` varchar(50) NOT NULL,
      `visibility` varchar(50) DEFAULT NULL,
      `creation_order` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`cart_product_type_id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8";
    echo "<br>";echo __LINE__; $db->exec($sql);
    /*Data for the table `rdi_cart_product_types` */

    $sql = "INSERT INTO  `rdi_cart_product_types`(`cart_product_type_id`,`cart_class_mapping_id`,`product_type`,`visibility`,`creation_order`) values (1,1,'simple',NULL,0),(2,1,'configurable',NULL,1),(3,2,'simple',NULL,0),(4,2,'configurable',NULL,1),(5,3,'simple',NULL,0),(6,3,'configurable',NULL,1),(7,4,'simple',NULL,0)";

    /*Table structure for table `rdi_config` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_config`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_config` (
      `cfg_opt` varchar(50) NOT NULL DEFAULT '',
      `name` varchar(255) NOT NULL DEFAULT '',
      `value` varchar(255) NOT NULL DEFAULT ''
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Data for the table `rdi_config` */

    /*Table structure for table `rdi_field_mapping` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_field_mapping`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_field_mapping` (
      `field_mapping_id` int(10) NOT NULL AUTO_INCREMENT,
      `field_type` varchar(50) DEFAULT NULL COMMENT 'catalog, product, customer, order',
      `field_classification` varchar(11) DEFAULT NULL COMMENT 'attribute set',
      `entity_type` varchar(50) DEFAULT NULL COMMENT 'product type',
      `cart_field` varchar(50) DEFAULT NULL,
      `invisible_field` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'not shown in the admin',
      `default_value` varchar(50) DEFAULT NULL COMMENT 'when using, dont need a pos record, the default will be used, or if pos is used and null this is used',
      `allow_update` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'use this field in an update, 0 - no, 1 - yes',
      `special_handling` varchar(150) DEFAULT NULL COMMENT 'handling to do with this field value, commands line lower, no_space',
      `notes` varchar(255) DEFAULT NULL,
      PRIMARY KEY (`field_mapping_id`),
      KEY `attribute_set_id_attribute_code` (`field_type`,`field_classification`,`cart_field`)
    ) ENGINE=MyISAM AUTO_INCREMENT=1000 DEFAULT CHARSET=utf8";
    echo "<br>";echo __LINE__; $db->exec($sql);
    /*Data for the table `rdi_field_mapping` */

    $sql = "INSERT INTO  `rdi_field_mapping`(`field_mapping_id`,`field_type`,`field_classification`,`entity_type`,`cart_field`,`invisible_field`,`default_value`,`allow_update`,`special_handling`,`notes`) VALUES 
            (101,'product',NULL,NULL,'name',0,NULL,1,NULL,NULL),
        (102,'product',NULL,NULL,'description',0,NULL,1,NULL,NULL),
        (103,'product',NULL,NULL,'short_description',0,NULL,1,NULL,NULL),
        (104,'product',NULL,'configurable','sku',0,NULL,1,NULL,NULL),
        (105,'product',NULL,'configurable','related_id',1,NULL,1,NULL,NULL),
        (106,'product',NULL,NULL,'style_id',1,NULL,1,NULL,NULL),
        (107,'product',NULL,NULL,'url_path',0,NULL,0,'lower,no_space',NULL),
        (108,'product',NULL,NULL,'url_key',0,'',0,'lower,no_space',NULL),
        (109,'product',NULL,NULL,'weight',0,NULL,1,NULL,NULL),
        (110,'product',NULL,NULL,'msrp',0,'0',1,NULL,NULL),
        (111,'product',NULL,NULL,'price',0,NULL,1,NULL,NULL),
        (112,'product',NULL,NULL,'special_price',0,NULL,1,'zero_null',NULL),
        (114,'product',NULL,'configurable','status',0,'2',1,NULL,NULL),
        (115,'product',NULL,'simple','status',0,'1',1,NULL,NULL),
        (116,'product',NULL,NULL,'status',0,'1',1,NULL,NULL),
        (117,'product',NULL,'configurable','visibility',0,'4',1,NULL,NULL),
        (119,'product',NULL,NULL,'item_id',1,NULL,1,NULL,NULL),
        (120,'product',NULL,'simple','sku',0,NULL,1,NULL,NULL),
        (121,'product',NULL,'simple','related_id',1,NULL,1,NULL,NULL),
        (122,'product',NULL,'simple','color',0,NULL,1,NULL,NULL),
        (123,'product',NULL,NULL,'size',0,NULL,1,NULL,NULL),
        (124,'product',NULL,'simple','qty',0,NULL,1,NULL,NULL),
        (125,'product',NULL,NULL,'color_sort_order',0,NULL,1,NULL,NULL),
        (126,'product',NULL,NULL,'size_sort_order',0,NULL,1,NULL,NULL),
        (127,'product',NULL,'configurable','related_parent_id',0,NULL,1,NULL,NULL),
        (128,'product',NULL,'simple','related_parent_id',0,NULL,1,'IS(size:NULL;color:NULL;|style_id|)',NULL),
        (129,'product',NULL,'simple','visibility',0,'1',1,NULL,NULL),
        (130,'product',NULL,NULL,'is_in_stock',0,'1',1,NULL,NULL),
        (131,'product',NULL,'configurable','qty',0,NULL,1,NULL,NULL),
        (133,'product',NULL,'simple','visibility',0,'1',1,'is(size:null;color:null;|\'4\'|$)',NULL),
        (134,'product',NULL,NULL,'cost',0,'',1,NULL,NULL),
        (140,'product',NULL,NULL,'meta_keyword',0,'',1,NULL,NULL),
        (141,'product',NULL,NULL,'meta_title',0,'',1,NULL,NULL),
        (142,'product',NULL,NULL,'meta_description',0,'',1,NULL,NULL),
        (143,'product',NULL,NULL,'thumbnail',0,'no_selection',0,NULL,NULL),
        (144,'product',NULL,NULL,'small_image',0,'no_selection',0,NULL,NULL),
        (145,'product',NULL,NULL,'image',0,'no_selection',0,NULL,NULL),
        (146,'product',NULL,NULL,'itemnum',0,NULL,1,NULL,NULL),
        (150,'product',NULL,NULL,'manufacturer',0,NULL,1,NULL,NULL),
        (151,'product',NULL,NULL,'country_of_manufacture',0,NULL,1,NULL,NULL),
        (152,'product',NULL,NULL,'news_from_date',0,NULL,1,NULL,NULL),
        (153,'product',NULL,NULL,'news_to_date',0,NULL,1,NULL,NULL),
        (154,'product',NULL,NULL,'special_from_date',0,NULL,1,NULL,NULL),
        (155,'product',NULL,NULL,'special_to_date',0,NULL,1,NULL,NULL),
        (161,'product',NULL,NULL,'msrp_display_actual_price_type',0,'4',1,NULL,NULL),
        (162,'product',NULL,NULL,'rdi_last_updated',0,'NOW()',1,NULL,NULL),
        (160,'product',NULL,NULL,'msrp_enabled',0,'2',1,NULL,NULL),
        (171,'product',NULL,NULL,'custom_design',0,'',1,NULL,NULL),
        (172,'product',NULL,NULL,'options_container',0,'container2',1,NULL,NULL),
        (173,'product',NULL,NULL,'gift_message_available',0,'',1,NULL,NULL),
        (174,'product',NULL,NULL,'custom_layout_update',0,'',1,NULL,NULL),
        (175,'product',NULL,NULL,'custom_design_from',0,NULL,1,NULL,NULL),
        (176,'product',NULL,NULL,'custom_design_to',0,NULL,1,NULL,NULL),
        (181,'product',NULL,NULL,'tax_class_id',0,'2',1,NULL,NULL),
        (183,'product',NULL,NULL,'is_recurring',0,'0',1,NULL,NULL),
        (189,'product',NULL,NULL,'use_config_min_qty',0,'0',1,NULL,NULL),
        (182,'product',NULL,NULL,'is_qty_decimal',0,'1',1,NULL,NULL),
        (184,'product',NULL,NULL,'use_config_enable_qty_inc',0,'1',1,NULL,NULL),
        (185,'product',NULL,NULL,'use_config_backorders',0,'0',1,NULL,NULL),
        (187,'product',NULL,NULL,'use_config_max_sale_qty',0,'1',1,NULL,NULL),
        (186,'product',NULL,NULL,'use_config_manage_stock',0,'1',1,NULL,NULL),
        (188,'product',NULL,NULL,'low_stock_date',0,NULL,1,NULL,NULL),
        (190,'product',NULL,NULL,'use_config_min_sale_qty',0,'1',1,NULL,NULL),
        (191,'product',NULL,NULL,'stock_status_changed_auto',0,'1',1,NULL,NULL),
        (192,'product',NULL,NULL,'use_config_notify_stock_qty',0,'1',1,NULL,NULL),
        (193,'product',NULL,NULL,'use_config_qty_increments',0,'1',1,NULL,NULL),
        (198,'product',NULL,NULL,'product_image',0,NULL,1,NULL,NULL),

            (201,'category',NULL,NULL,'name',0,NULL,1,NULL,NULL),
        (202,'category',NULL,NULL,'description',0,NULL,1,NULL,NULL),
        (203,'category',NULL,NULL,'position',0,NULL,1,NULL,NULL),
        (204,'category',NULL,NULL,'url_key',0,NULL,1,'lower,no_space',NULL),
        (206,'category',NULL,NULL,'parent_id',0,NULL,1,NULL,NULL),
        (207,'category',NULL,NULL,'related_id',0,NULL,1,NULL,NULL),
        (208,'category',NULL,NULL,'is_active',0,'1',1,NULL,NULL),
        (209,'category',NULL,NULL,'is_anchor',0,'0',1,NULL,NULL),
        (220,'category',NULL,NULL,'meta_title',0,NULL,1,NULL,NULL),
        (221,'category',NULL,NULL,'meta_keywords',0,NULL,1,NULL,NULL),
        (222,'category',NULL,NULL,'meta_description',0,NULL,1,NULL,NULL),
        (230,'category',NULL,NULL,'display_mode',0,'PRODUCTS',1,NULL,NULL),
        (240,'category',NULL,NULL,'custom_apply_to_products',0,NULL,1,NULL,NULL),
        (241,'category',NULL,NULL,'custom_design',0,NULL,1,NULL,NULL),
        (242,'category',NULL,NULL,'custom_design_from',0,NULL,1,NULL,NULL),
        (243,'category',NULL,NULL,'custom_design_to',0,NULL,1,NULL,NULL),
        (244,'category',NULL,NULL,'custom_layout_update',0,NULL,1,NULL,NULL),
        (245,'category',NULL,NULL,'custom_use_parent_settings',0,NULL,1,NULL,NULL),
        (250,'category',NULL,NULL,'page_layout',0,NULL,1,NULL,NULL),
        (251,'category',NULL,NULL,'filter_price_range',0,NULL,1,NULL,NULL),
        (253,'category',NULL,NULL,'include_in_menu',0,'1',1,NULL,NULL),
        (252,'category',NULL,NULL,'available_sort_by',0,NULL,1,NULL,NULL),

        (301,'category_product',NULL,NULL,'entity_id',1,NULL,1,NULL,NULL),
        (302,'category_product',NULL,NULL,'related_id',1,NULL,1,NULL,NULL),
        (303,'category_product',NULL,NULL,'position',0,NULL,1,NULL,NULL),

        (501,'customer',NULL,NULL,'entity_id',0,NULL,1,NULL,NULL),
        (502,'customer',NULL,NULL,'entity_id',0,NULL,1,NULL,NULL),
        (503,'customer',NULL,NULL,'firstname',0,NULL,1,'upper',NULL),
        (504,'customer',NULL,NULL,'lastname',0,NULL,1,'upper',NULL),
        (505,'customer',NULL,NULL,'email',0,NULL,1,'upper',NULL),
        (506,'customerxx',NULL,NULL,'related_id',0,NULL,1,NULL,NULL),
        (520,'customer_address',NULL,NULL,'prefix',0,NULL,1,'upper',NULL),
        (521,'customer_address',NULL,NULL,'company',0,NULL,1,'upper',NULL),
        (522,'customer_address',NULL,NULL,'street',0,NULL,1,'upper',NULL),
        (523,'customer_address',NULL,NULL,'street2',0,NULL,1,'upper',NULL),
        (524,'customer_address',NULL,NULL,'city',0,NULL,1,'upper',NULL),
        (525,'customer_address',NULL,NULL,'country_id',0,NULL,1,'upper',NULL),
        (526,'customer_address',NULL,NULL,'region',0,NULL,1,'state_abv',NULL),
        (527,'customer_address',NULL,NULL,'postcode',0,NULL,1,NULL,NULL),
        (528,'customer_address',NULL,NULL,'telephone',0,NULL,1,NULL,NULL),
        (529,'customer_address',NULL,NULL,'fax',0,NULL,1,NULL,NULL),

        (600,'order',NULL,NULL,'increment_id',0,NULL,1,NULL,NULL),
        (601,'order',NULL,NULL,'created_at',0,NULL,1,'date',NULL),
        (602,'orderxxxxx',NULL,NULL,'customer_id',0,NULL,1,NULL,NULL),
        (603,'order_bill_to',NULL,NULL,'customer_id',0,NULL,1,NULL,NULL),
        (605,'order',NULL,NULL,'base_shipping_amount',0,NULL,1,NULL,NULL),
        (606, 'order', NULL, NULL, 'shipping_tax_amount', '0', NULL, '1', NULL, NULL),
        (607,'orderxxxx',NULL,NULL,'base_subtotal',0,NULL,1,NULL,NULL),
        (608,'order',NULL,NULL,'discount_amount',0,NULL,1,'abs',NULL),
        (609,'orderxxx',NULL,NULL,'tax_amount',0,NULL,1,NULL,NULL),
        (611,'order',NULL,NULL,'card_type',0,NULL,1,NULL,NULL),
        (613,'order',NULL,NULL,'shipping_method_id',0,NULL,1,NULL,NULL),
        (614,'order',NULL,NULL,'shipping_provider_id',0,NULL,1,NULL,NULL),
        (615,'orderxxx',NULL,NULL,'customer_id',0,NULL,1,NULL,NULL),
        (616,'order',NULL,NULL,'customer_id',0,NULL,1,NULL,NULL),
        (617,'order_item',NULL,NULL,'item_id',0,NULL,1,NULL,NULL),
        (620,'order_bill_to',NULL,NULL,'entity_id',0,NULL,1,NULL,NULL),
        (621,'order_bill_to',NULL,NULL,'firstname',0,NULL,1,'upper',NULL),
        (622,'order_bill_to',NULL,NULL,'lastname',0,NULL,1,'upper',NULL),
        (624,'order_bill_to',NULL,NULL,'company',0,NULL,1,'upper',NULL),
        (625,'order_bill_to',NULL,NULL,'street',0,NULL,1,'upper',NULL),
        (627,'order_bill_to',NULL,NULL,'city',0,NULL,1,NULL,NULL),
        (623,'order_bill_to',NULL,NULL,'email',0,NULL,1,'upper',NULL),
        (628,'order_bill_to',NULL,NULL,'postcode',0,NULL,1,NULL,NULL),
        (629,'order_bill_to',NULL,NULL,'region',0,NULL,1,'state_abv',NULL),
        (630,'order_bill_to',NULL,NULL,'telephone',0,NULL,1,NULL,NULL),
        (631,'order_bill_to',NULL,NULL,'country_id',0,NULL,1,NULL,NULL),
        (640,'order_ship_to',NULL,NULL,'entity_id',0,NULL,1,NULL,NULL),
        (641,'order_ship_to',NULL,NULL,'firstname',0,NULL,1,'upper',NULL),
        (642,'order_ship_to',NULL,NULL,'lastname',0,NULL,1,'upper',NULL),
        (643,'order_ship_to',NULL,NULL,'email',0,NULL,1,'upper',NULL),
        (644,'order_ship_to',NULL,NULL,'company',0,NULL,1,'upper',NULL),
        (645,'order_ship_to',NULL,NULL,'street',0,NULL,1,'upper',NULL),
        (647,'order_ship_to',NULL,NULL,'city',0,NULL,1,NULL,NULL),
        (648,'order_ship_to',NULL,NULL,'postcode',0,NULL,1,NULL,NULL),
        (649,'order_ship_to',NULL,NULL,'region',0,NULL,1,'state_abv',NULL),
        (650,'order_ship_to',NULL,NULL,'telephone',0,NULL,1,NULL,NULL),
        (651,'order_ship_to',NULL,NULL,'country_id',0,NULL,1,'upper',NULL),
        (660,'order_item',NULL,NULL,'sku',0,NULL,1,NULL,NULL),
        (661,'order_item',NULL,NULL,'name',0,NULL,1,NULL,NULL),
        (662,'order_item',NULL,NULL,'qty_ordered',0,NULL,1,NULL,NULL),
        (663,'order_item',NULL,NULL,'base_price',0,NULL,1,NULL,NULL),
        (664,'order_item',NULL,NULL,'tax_percent',0,NULL,1,NULL,NULL),
        (667,'order_item',NULL,NULL,'base_discount_amount',0,NULL,1,'subtract(base_price|base_discount_amount)',NULL),
        (665,'order_item',NULL,NULL,'tax_amount',0,NULL,1,NULL,NULL),
        (670,'order_item',NULL,NULL,'increment_id',0,NULL,1,NULL,NULL),
        (671,'order_item',NULL,NULL,'related_id',0,NULL,1,NULL,NULL),
        (680,'so_status',NULL,NULL,'increment_id',0,NULL,1,NULL,NULL),
        (681,'so_status',NULL,NULL,'rdi_cc_amount',0,NULL,1,NULL,NULL),
        (682,'so_status',NULL,NULL,'receipt_shipping',0,NULL,1,NULL,NULL),
        (683,'so_status',NULL,NULL,'receipt_tax',0,NULL,1,NULL,NULL),
        (690,'so_shipment',NULL,NULL,'carrier_code',0,NULL,1,NULL,NULL),
        (691,'so_shipment',NULL,NULL,'tracking_number',0,NULL,1,NULL,NULL),
        (653,'order_payment',NULL,NULL,'method',0,'2',1,'if(ccsave|2),if(checkmo|2),if(authorizenet|2),if(paypal_express|2|2)',NULL),
        (654,'order_payment',NULL,NULL,'cc_owner',0,NULL,1,NULL,NULL),
        (655,'order_payment',NULL,NULL,'cc_type',0,NULL,1,NULL,NULL),
        (632,'order_bill_to',NULL,NULL,'tax_area',0,NULL,1,'upper',NULL),
        (626,'order_bill_to',NULL,NULL,'street2',0,NULL,1,'upper',NULL),
        (646,'order_ship_to',NULL,NULL,'street2',0,NULL,1,'upper',NULL),
        (692,'so_shipment',NULL,NULL,'shipment_date',0,NULL,1,NULL,NULL),
        (693,'so_shipment',NULL,NULL,'increment_id',0,NULL,1,NULL,NULL),
        (694,'so_shipment',NULL,NULL,'email_sent',0,'0',1,NULL,NULL),
        (695,'so_shipment',NULL,NULL,'comment',0,'',1,NULL,NULL),
        (696,'so_shipment',NULL,NULL,'carrier_title',0,'UPS',1,NULL,NULL),

        (701,'so_shipment_item',NULL,NULL,'related_id',0,NULL,1,NULL,NULL),
        (700,'so_shipment_item',NULL,NULL,'qty',0,NULL,1,NULL,NULL),
        
        ('800', 'order', NULL, 'addr_no', NULL, '0', '1', '1', NULL, NULL),
        ('801', 'order', NULL, 'shipto_addr_no', NULL, '0', '1', '1', NULL, NULL),
        ('802', 'order', NULL, 'priority', NULL, '0', '1', '1', NULL, NULL),
        ('803', 'order', NULL, 'use_vat', NULL, '0', '0', '1', NULL, NULL),
        ('804', 'order', NULL, 'cms', NULL, '0', '1', '1', NULL, NULL),
        ('805', 'order', NULL, 'active', NULL, '0', '1', '1', NULL, NULL),
        ('806', 'order', NULL, 'verified', NULL, '0', '0', '1', NULL, NULL),
        ('807', 'order', NULL, 'held', NULL, '0', '0', '1', NULL, NULL),
        ('808', 'order', NULL, 'doc_source', NULL, '0', '0', '1', NULL, NULL),
        ('809', 'order', NULL, 'controller', NULL, '0', '1', '1', NULL, NULL),
        ('810', 'order', NULL, 'orig_controller', NULL, '0', '1', '1', NULL, NULL),
        ('811', 'order', NULL, 'elapsed_time', NULL, '0', '1', '1', NULL, NULL),
        ('812', 'order', NULL, 'line_pos_seq', NULL, '0', '2', '1', NULL, NULL),
        ('813', 'order', NULL, 'activity_perc', NULL, '0', '100', '1', NULL, NULL),
        ('814', 'order', NULL, 'detax', NULL, '0', '0', '1', NULL, NULL),
        ('815', 'order', NULL, 'shipto_customer_detax', NULL, '0', '0', '1', NULL, NULL),
        ('816', 'order', NULL, 'shipto_customer_cms', NULL, '0', '1', '1', NULL, NULL),
        ('817', 'order', NULL, 'shipto_customer_shipping', NULL, '0', '0', '1', NULL, NULL),
        ('818', 'order', NULL, 'so_type', NULL, '0', '1', '1', NULL, NULL),
        ('830', 'order_item', NULL, 'tax_code', NULL, '0', '0', '1', NULL, NULL),
        ('831', 'order_item', NULL, 'tax_code2', NULL, '0', '0', '1', NULL, NULL),
        ('832', 'order_item', NULL, 'tax_perc2', NULL, '0.0000', '0', '1', NULL, NULL),
        ('833', 'order_item', NULL, 'tax_amt2', NULL, '0', '0', '1', NULL, NULL),
        ('834', 'order_item', NULL, 'sent_qty', NULL, '0', '0', '1', NULL, NULL),
        ('835', 'order_item', NULL, 'price_lvl', NULL, '0', '1', '1', NULL, NULL),
        ('836', 'order_item', NULL, 'comm_code', NULL, '0', '-1', '1', NULL, NULL),
        ('837', 'order_item', NULL, 'kit_flag', NULL, '0', '0', '1', NULL, NULL),
        ('838', 'order_item', NULL, 'detax', NULL, '0', '0', '1', NULL, NULL),
        ('839', 'order_item', NULL, 'usr_disc_perc', NULL, '0', '0', '1', NULL, NULL),
        ('840', 'order_item', NULL, 'activity_perc', NULL, '0', '100', '1', NULL, NULL),
        ('841', 'order_item', NULL, 'promo_flag', NULL, '0', '0', '1', NULL, NULL),

        (5002,'product',NULL,NULL,'avail',0,NULL,1,NULL,NULL),
        (5003,'product',NULL,NULL,'manage_stock',0,'1',1,NULL,NULL)";

    /*Table structure for table `rdi_field_mapping_pos` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_field_mapping_pos`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_field_mapping_pos` (
      `field_mapping_id` int(10) DEFAULT NULL,
      `pos_field` varchar(250) DEFAULT NULL,
      `alternative_field` varchar(50) DEFAULT NULL,
      `field_order` int(11) NOT NULL DEFAULT '0',
      KEY `pos_field_id` (`field_mapping_id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
    echo "<br>";echo __LINE__; $db->exec($sql);
    /*Data for the table `rdi_field_mapping_pos` */

    $sql = "

    INSERT INTO  `rdi_field_mapping_pos`(`field_mapping_id`,`pos_field`,`alternative_field`,`field_order`) VALUES 
            (101,'style.product_name','style.desc1',0),
            (102,'style.long_desc',NULL,0),
            (103,'style.alt1_desc',NULL,0),
            (104,'style.desc1',NULL,0),
            (105,'style.style_sid',NULL,0),
            (106,'style.style_sid',NULL,0),
            (107,'style.desc1','style.desc2',0),
            (107,'\'.html\'','\'.html\'',1),
            (108,'style.desc2','style.desc1',0),
            (109,'item.ship_weight1',NULL,0),
            (111,'item.reg_price',NULL,0),
            (112,'item.sale_price',NULL,0),
            (119,'item.item_sid',NULL,0),
            (120,'item.alu','item.item_sid',0),
            (121,'item.item_sid',NULL,0),
            (122,'item.attr',NULL,0),
            (123,'item.size',NULL,0),
            (124,'item.quantity',NULL,0),
            (125,'item.attr_order',NULL,0),
            (126,'item.size_order',NULL,0),
            (127,'style.style_sid',NULL,0),
            (128,'item.style_sid',NULL,0),
            (134,'item.cost',NULL,0),
            (141,'style.desc2','style.desc1',0),
            (146,'item.item_num',NULL,0),
            (150,'style.vendor',NULL,0),
            (198,'style.style_image',NULL,0),

            (201,'category',NULL,0),
            (202,'description',NULL,0),
            (203,'sort_order',NULL,0),
            (204,'category',NULL,0),
            (206,'rpro_in_categories.parent_id',NULL,0),
            (207,'catalog_id',NULL,0),
            (220,'category',NULL,0),

            (301,'style_sid',NULL,0),
            (302,'rpro_in_category_products.catalog_id',NULL,0),
            (303,'rpro_in_category_products.sort_order',NULL,0),

            (600,'so_no',NULL,0),
            (600,'orderid',NULL,0),
            (600,'so_sid',NULL,0),
            (601,'created_date',NULL,0),
            (601,'modified_date',NULL,0),
            (601,'cms_post_date',NULL,0),
            (602,'cust_id',NULL,0),
            (603,'cust_sid',NULL,0),
            (605,'shipping_amt',NULL,0),
            (606, 'shipping_tax', NULL, 0),
            (607,'used_subtotal',NULL,0),
            (608,'disc_amt',NULL,0),
            (609,'used_tax',NULL,0),
            (611,'crd_name',NULL,0),
            (613,'ship_method',NULL,0),
            (615,'shipto_cust_sid',NULL,1),
            (615,'customer_cust_id',NULL,0),
            (616,'customer_cust_sid',NULL,0),
            (617,'item_pos',NULL,0),
            (621,'customer_first_name',NULL,0),
            (622,'customer_last_name',NULL,0),
            (623,'customer_email',NULL,0),
            (624,'customer_company_name',NULL,0),
            (625,'customer_address1',NULL,0),
            (626,'customer_address2',NULL,0),
            (627,'customer_address3','append(\\\ )',0),
            (628,'customer_zip',NULL,0),
            (629,'customer_address3','append( )',1),
            (630,'customer_phone1',NULL,0),
            (631,'customer_country_name',NULL,0),
            (632,'tax_area_name',NULL,0),
            (641,'shipto_customer_first_name',NULL,0),
            (642,'shipto_customer_last_name',NULL,0),
            (643,'shipto_customer_email',NULL,0),
            (644,'shipto_customer_company_name',NULL,0),
            (645,'shipto_customer_address1',NULL,0),
            (646,'shipto_customer_address2',NULL,0),
            (647,'shipto_customer_address3','append(\\\ )',0),
            (648,'shipto_customer_zip',NULL,0),
            (649,'shipto_customer_address3','append( )',1),
            (650,'shipto_customer_phone1',NULL,0),
            (651,'shipto_customer_country_name',NULL,0),
            (653,'tender_type',NULL,0),
            (654,'cardholder_name',NULL,0),
            (655,'crd_name',NULL,0),
            (662,'ord_qty',NULL,0),
            (663,'orig_price',NULL,0),
            (664,'tax_perc',NULL,0),
            (665,'orig_tax_amt',NULL,0),
            (665,'tax_amt',NULL,0),
            (667,'price',NULL,0),
            (670,'orderid',NULL,0),
            (671,'item_sid',NULL,0),
            (680,'rpro_in_so.so_number',NULL,0),
            (681,'rpro_in_so.tender_amt',NULL,0),
            (683,'rpro_in_so.tax_total',NULL,0),
            (690,'rpro_in_so.shipprovider',NULL,0),
            (691,'rpro_in_so.tracking_number',NULL,0),
            (692,'rpro_in_so.ship_date',NULL,0),
            (693,'rpro_in_so.sid',NULL,0),

            (696,'rpro_in_so.shipprovider',NULL,0),
            (700,'rpro_in_so.qty_shipped',NULL,0),
            (701,'rpro_in_so.item_sid',NULL,0),
            
            ('800', 'addr_no', NULL, '0'),
            ('801', 'shipto_addr_no', NULL, '0'),
            ('802', 'priority', NULL, '0'),
            ('803', 'use_vat', NULL, '0'),
            ('804', 'cms', NULL, '0'),
            ('805', 'active', NULL, '0'),
            ('806', 'verified', NULL, '0'),
            ('807', 'held', NULL, '0'),
            ('808', 'doc_source', NULL, '0'),
            ('809', 'controller', NULL, '0'),
            ('810', 'orig_controller', NULL, '0'),
            ('811', 'elapsed_time', NULL, '0'),
            ('812', 'line_pos_seq', NULL, '0'),
            ('813', 'activity_perc', NULL, '0'),
            ('814', 'detax', NULL, '0'),
            ('815', 'shipto_customer_detax', NULL, '0'),
            ('816', 'shipto_customer_cms', NULL, '0'),
            ('817', 'shipto_customer_shipping', NULL, '0'),
            ('817', 'so_type', NULL, '0'),
            ('830', 'tax_code', NULL, '0'),
            ('831', 'tax_code2', NULL, '0'),
            ('832', 'tax_perc2', NULL, '0'),
            ('833', 'tax_amt2', NULL, '0'),
            ('834', 'sent_qty', NULL, '0'),
            ('835', 'price_lvl', NULL, '0'),
            ('836', 'comm_code', NULL, '0'),
            ('837', 'kit_flag', NULL, '0'),
            ('838', 'detax', NULL, '0'),
            ('839', 'usr_disc_perc', NULL, '0'),
            ('840', 'activity_perc', NULL, '0'),
            ('841', 'promo_flag', NULL, '0'),
            
            (5002,'style.Avail',NULL,0)
            ";

    /*Table structure for table `rdi_item_image` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_item_image`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_item_image` (
      `productid` varchar(30) DEFAULT NULL,
      `style_sid` varchar(30) DEFAULT NULL,
      `item_sid` varchar(30) DEFAULT NULL,
      `item_num` varchar(20) DEFAULT NULL,
      `apply_to` varchar(255) DEFAULT NULL,
      `image_0` varchar(30) DEFAULT NULL,
      `image_1` varchar(30) DEFAULT NULL,
      `image_2` varchar(30) DEFAULT NULL,
      `image_3` varchar(30) DEFAULT NULL,
      `import_date` datetime DEFAULT NULL,
      `updated` char(1) DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Data for the table `rdi_item_image` */

    /*Table structure for table `rdi_out_customers` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_out_customers`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_out_customers` (
      `customer_id` int(11) DEFAULT NULL,
      `rdi_cust_sid` varchar(100) NOT NULL DEFAULT '',
      `first_name` varchar(100) NOT NULL DEFAULT '',
      `last_name` varchar(100) NOT NULL DEFAULT '',
      `address1` varchar(200) NOT NULL DEFAULT '',
      `address2` varchar(200) NOT NULL DEFAULT '',
      `city` varchar(200) NOT NULL DEFAULT '',
      `state` varchar(50) DEFAULT NULL,
      `region` varchar(200) NOT NULL DEFAULT '',
      `zip` varchar(20) NOT NULL DEFAULT '',
      `country` varchar(200) NOT NULL DEFAULT '',
      `country_code` varchar(20) NOT NULL DEFAULT '',
      `phone` varchar(30) NOT NULL DEFAULT '',
      `email` varchar(100) NOT NULL DEFAULT '',
      `login_id` varchar(100) DEFAULT NULL,
      `PASSWORD` varchar(100) DEFAULT NULL,
      `orders_num` int(11) DEFAULT NULL,
      `has_so` int(11) DEFAULT NULL,
      `company` varchar(100) DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Data for the table `rdi_out_customers` */

    /*Table structure for table `rdi_prefs_scales` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_prefs_scales`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_prefs_scales` (
      `scale_no` varchar(50) DEFAULT NULL,
      `scale_name` varchar(50) DEFAULT NULL,
      `scaleitem_no` varchar(50) DEFAULT NULL,
      `scaleitem_value` varchar(50) DEFAULT NULL,
      `scaleitem_type` varchar(50) DEFAULT NULL
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";

    /*Data for the table `rdi_prefs_scales` */

    /*Table structure for table `rdi_settings` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rdi_settings`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rdi_settings` (
      `setting_id` int(11) DEFAULT NULL,
      `setting` varchar(50) NOT NULL,
      `value` varchar(50) DEFAULT NULL,
      `group` varchar(50) NOT NULL,
      `not_notes` text DEFAULT NULL,
      `help` text,
      `cart_lib` varchar(50) DEFAULT NULL COMMENT 'right now just for reference, isnt actually used',
      `pos_lib` varchar(50) DEFAULT NULL COMMENT 'right now just for reference, isnt actually used'
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8";
    echo "<br>";echo __LINE__; $db->exec($sql);
    /*Data for the table `rdi_settings` */

    $sql = "INSERT INTO  `rdi_settings`(`setting_id`,`setting`,`value`,`group`,`help`,`cart_lib`,`pos_lib`) values 
    (1,'pos_type','rpro9','','',NULL,NULL),
    (100,'load_products','0','Core','Set to 1 to load products , 0 to skip',NULL,NULL),
    (101,'load_categories','0','Core','Set to 1 to load categories, 0 to skip',NULL,NULL),
    (102,'load_customers','0','Core','Set to 1 to load customers, 0 to skip',NULL,NULL),
    (103,'load_images','0','Core','Set to 1 to load images, 0 to skip',NULL,NULL),
    (104,'load_so_status','0','Core','Set to 1 to load so status 0 to skip',NULL,NULL),
    (104,'load_returns','0','Core','Set to 1 to load so status 0 to skip',NULL,NULL),
    (107,'scale_key','0','Rpro8','0 - name, 1 - index, The key the scales will be keying off for a match , RPRO8',NULL,NULL),
    (108,'load_price_variance','0','','set the price variances of the products based on the difference in price of the configurable\'s price',NULL,NULL),

    (200,'insert_products','1','','Perform product inserts, aka new products',NULL,NULL),
    (201,'insert_categories','1','','Insert New Categories',NULL,NULL),
    (205,'insert_upsell','0','','insert upsell products',NULL,NULL),
    (206,'use_single_product_criteria','4','product','USE the cart_class_mapping_id of the class that you want to allow creation of just simples. Usually 4.',NULL,NULL),(202,'insert_customers','0','',NULL,NULL,NULL),

    (300,'update_products',     '1','','Product Updates',NULL,NULL),
    (301,'update_categories',   '1','','Update categories ',NULL,NULL),
    (302,'update_customers',    '1','',NULL,NULL,NULL),
    (305,'update_upsell',       '0','','update upsell products',NULL,NULL),
    (306,'update_availability', '1','','Update of the stock availability, in stock / out of stock',NULL,NULL),
    (307,'hide_out_of_stock',   '1','','Hide items that are out of stock, and show ones that are in',NULL,NULL),
    (310,'product_to_categories','1','','Perform product to category relations  Disabled 8/16/2012\r\n',NULL,NULL),
    (311,'set_nonorphans_findable','1','','if a product is in a category it will be set to a status of searchable every run',NULL,NULL),
    (312,'hide_orphans',        '1','','Set products not in categories to a not findable status',NULL,NULL),
    (313,'enable_nonorphans',   '1','','enables any configurable or rpro_simple that is in a category, just be careful using as this will enable them all',NULL,NULL),
    (314,'disable_orphans',     '1',' ','disables products that are not in categories',NULL,NULL),
    (315,'category_tuning','1','Core','tuning of categories by turning disabling the ones that have no products',NULL,NULL),
    (316,'category_anchors','2','','The Tree level to set as anchors',NULL,NULL),
    (317,'disable_product_sort_order','0','','1 - This will disable the sort order coming from the catalog in Retail pro and default to the sort order currently in Magento. New products inserted into categories may be infront or behind old.',NULL,NULL),
    (318, 'brand_root_catalog_id', '0', '', 'Set the catalog id/related_id that you want to set to brands tree. Comma seperated for multiple ids.', NULL, NULL),
    (319,'product_require_image','0','product','disables products that do not have an image. Happens in the catalog load.',NULL,NULL),
    (320,'category_image_attribute_destination','image','category','image/thumbnail_image',NULL,NULL),

    (400,'export_orders','1','Core','Set to 1 to export orders 0 to skip',NULL,NULL),
    (401,'export_customers','1','Core','set to 1 to export customers 0 to skip',NULL,NULL),
    (410,'order_export_status','pending','Core','The order status that triggers and order download',NULL,NULL),
    (411,'use_card_type_mapping','1','Core','use the mapping set in the rdi_card_type_mapping table',NULL,NULL),
    (412,'default_card_type','VISA','Core',NULL,NULL,NULL),
    (418, 'default_shipping_method', 'Ground', 'Order', 'If mapping doesnt find a match, then this will be the shipping method.', NULL, NULL),
    (413, 'pos_so_type', '0', 'Order-Not used', '0-Customer Order, 1-,2-,3-,4-,5-WEB. The WEB so_type does not function the same as the customer order type in Retail Pro. Copying SOs is not possible and the committed quantities are lost from the interface. WEB is not recommend.', NULL, 'V9'),


    (600,'verbose_queries','0','Core','1 = true, 0 = false		turn on by either specifying true for all, or an array of the areas wanted to be verbose		options	);\n	comma seperated list - specify the area to show update or insert queries for	);\n	update_insert - show all update / insert queries	);\n	true - show all queries select, delete, insert, update all of them, just wont show the break down of where it came from	);\n	false - nothing	\n		\n	areas	\n	pos_get_categories	\n	pos_get_category_relations	\n	pos_get_category_relations_for_removal	\n	pos_set_category_relations	\n		\n	pos_get_products	\n	pos_get_products_relations	\n		\n	cart_update_product	\n	cart_insert_product	\n	cart_update_category	\n	cart_insert_category		',NULL,NULL),
    (601,'show_query_counts','0','Core','if you have queries echoing via verbose, this will show the row count for that query when ran',NULL,NULL),
    (603,'log_debug_data','0','','Enable the logging of variables with the debug statements',NULL,NULL),
    (604,'purge_debug_on_load','1','','Will clear out the debug log every load',NULL,NULL),
    (605,'benchmark_global_display_screen','0','Core','benchmarker global settings, these override the local settings of the values',NULL,NULL),
    (606,'benchmark_global_save_db','1','Core','benchmarker global settings, these override the local settings of the values',NULL,NULL),
    (607, 'save_main_loadtimes', '1', 'Core', 'Marks the start and finish times for the major scripts called.', NULL, NULL),
    (609,'debug_level','0','Core','0 - All 	\n	1 - basic debug info		2 - ^ and some general data		3 - ^ and important data		4 - ^ and critical data',NULL,NULL),
    (610,'debug_enabled','0','Core','1 to enable logging of database data to the rdi_debug_log table',NULL,NULL),
    (611,'archive_length','30','','number of days files will be kept in the archive folder',NULL,NULL),
    (612,'load_time_log_archive_length','30','','number of days the load times load table will keep its data',NULL,NULL),
    (613,'log_table_archive_length','30','','number of days to keep data in the log tables',NULL,NULL),

    (700,'order_cart_lib_ver','1.6.x','Core',NULL,NULL,NULL),
    (701,'product_cart_lib_ver','1.6.x','Core',NULL,NULL,NULL),
    (702,'customer_cart_lib_ver','1.6.x','Core',NULL,NULL,NULL),
    (703,'catalog_cart_lib_ver','1.6.x','Core',NULL,NULL,NULL),
    (709,'indexer_exec','mage','','shell to run from the shell, mage to use mage',NULL,NULL),
    (710,'update_type_product','query','','hash - use the hashes table to compare the hash values for a possible update 	\n	query - use sql queries to check the values directly',NULL,NULL),
    (711,'update_type_category','query','','hash - use the hashes table to compare the hash values for a possible update ',NULL,NULL),

    (800,'inPath','in','Core - Upload',NULL,NULL,NULL),
    (801,'out_path','out','',NULL,NULL,NULL),
    (802,'ignore_warnings','0','Core','some mapping may generate a warning, and the warning may not apply, in this case turn on ignore warning and it will stop warning',NULL,NULL),
    (803,'skip_validation','0','','Skip the field validation',NULL,NULL),
    (810,'store_id','0','','',NULL,NULL),
    (811,'default_site','1','','',NULL,NULL),
    (812,'payment_capture','delayed','','possible values - delayed, manual, order\r\nDelayed will make the call to settle\r\nManual will assign the tracking number, but not create an invoice\r\nOrder will assume the funds have already been captured and just assign the tracking numbe',NULL,NULL),
    (813,'capture_on_first_shipment','0','','This be going to capture on the first shipment sent up.',NULL,NULL),

    (903,'ship_all','0','','[0-OFF, 1-ON] Turn off to create a shipment for each invoice. Otherwise, create a new shipment with the first tracking number',NULL,NULL),
    (904,'disable_out_of_stock','1','',NULL,NULL,NULL),
    (905,'product_link_type','relation,up_sell','','the type of link we use for the link, as dictated in the catalog_product_link_type table',NULL,NULL),
    (912,'deactivated_delete_time','999','',NULL,NULL,NULL),
    (913,'order_prefix','00010','','This is the prefix of the SO number from Retail Pro',NULL,NULL),
    (914,'simple_url_key_format','{name}-{color}-{size}-{sku}','','the format used for standard simples for thier url key, this is to keep them unique and different from configurable\r\nnot using mapping since mapping cant determine if a size is null not to use it, it would clear out the entire url key\r\nand there can be multiple different permutations\r\nname_size_attr\r\nname_attr\r\nname_size\r\n\r\nand mapping only allows 2 possibilities\r\n\r\nset the stand alone simples and the configurables from the mapping\r\n\r\nusage\r\nwrap the field names in {}\r\n\r\nie\r\n{name}_{size}_{color}',NULL,NULL),
    (915,'configurable_url_key_format','{name}-{sku}','',NULL,NULL,NULL),
    (916,'spread_shipping_tax','0','',NULL,NULL,NULL),
    (917,'avail_stock_update','1',' ',' Lower stock from available records',NULL,NULL)";

    /*Table structure for table `rpro_mage_shipping` */
    echo "<br>";echo __LINE__; $db->exec($sql); //DROP TABLE IF EXISTS `rpro_mage_shipping`;

    $sql = "CREATE TABLE IF NOT EXISTS  `rpro_mage_shipping` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `rpro_provider_id` int(11) DEFAULT NULL,
      `rpro_method_id` int(11) DEFAULT NULL,
      `shipper` varchar(25) DEFAULT NULL,
      `ship_code` varchar(75) DEFAULT NULL,
      `ship_description` varchar(75) DEFAULT NULL,
      UNIQUE KEY `id` (`id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=88 DEFAULT CHARSET=utf8";

    echo "<br>";echo __LINE__; $db->exec($sql);

    $sql = "CREATE TABLE `rpro_in_return` (
	`so_number` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`so_doc_no` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`invc_sid` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`invc_no` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`disc_amt` DECIMAL(10,4) NULL DEFAULT NULL COMMENT 'Amount of all items less taxes',
	`shipping` DECIMAL(10,4) NULL DEFAULT NULL COMMENT 'Amount of Shipping',
	`comment1` TEXT NULL COLLATE 'utf8_general_ci',
	`comment2` TEXT NULL COLLATE 'utf8_general_ci',
	`refund_date` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`item_qty_returned` DOUBLE NULL DEFAULT NULL,
	`item_qty_ordered` DOUBLE NULL DEFAULT NULL,
	`item_sid` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`item_alu` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Secondary product identification.' COLLATE 'utf8_general_ci',
	`item_price` DOUBLE NULL DEFAULT NULL,
	`record_type` VARCHAR(25) NULL DEFAULT NULL COMMENT 'refund or item type' COLLATE 'utf8_general_ci'
)
COLLATE='utf8_general_ci'
ENGINE=MyISAM
    ";

    echo "<br>";echo __LINE__; $db->exec($sql);

    $sql = "CREATE TABLE `rpro_in_return_log` (
	`so_number` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`so_doc_no` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`invc_sid` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`invc_no` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`disc_amt` DECIMAL(10,4) NULL DEFAULT NULL COMMENT 'Amount of all items less taxes',
	`shipping` DECIMAL(10,4) NULL DEFAULT NULL COMMENT 'Amount of Shipping',
	`comment1` TEXT NULL COLLATE 'utf8_general_ci',
	`comment2` TEXT NULL COLLATE 'utf8_general_ci',
	`refund_date` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`item_qty_returned` DOUBLE NULL DEFAULT NULL,
	`item_qty_ordered` DOUBLE NULL DEFAULT NULL,
	`item_sid` VARCHAR(50) NULL DEFAULT NULL COLLATE 'utf8_general_ci',
	`item_alu` VARCHAR(50) NULL DEFAULT NULL COMMENT 'Secondary product identification.' COLLATE 'utf8_general_ci',
	`item_price` DOUBLE NULL DEFAULT NULL,
	`record_type` VARCHAR(25) NULL DEFAULT NULL COMMENT 'refund or item type' COLLATE 'utf8_general_ci',
	`rdi_import_date` DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00'
)
COLLATE='utf8_general_ci'
ENGINE=MyISAM;
    ";

    echo "<br>";echo __LINE__; $db->exec($sql);
    /*Data for the table `rpro_mage_shipping` */

    $sql = "INSERT INTO  `rpro_mage_shipping`(`id`,`rpro_provider_id`,`rpro_method_id`,`shipper`,`ship_code`,`ship_description`) values (1,NULL,NULL,'ups','1DM','Next Day Air Early AM'),(2,NULL,NULL,'ups','1DML','Next Day Air Early AM Letter'),(3,NULL,NULL,'ups','1DA','Next Day Air'),(4,NULL,NULL,'ups','1DAL','Next Day Air Letter'),(5,NULL,NULL,'ups','1DAPI','Next Day Air Intra (Puerto Rico)'),(6,NULL,NULL,'ups','1DP','Next Day Air Saver'),(7,NULL,NULL,'ups','1DPL','Next Day Air Saver Letter'),(8,NULL,NULL,'ups','2DM','2nd Day Air AM'),(9,NULL,NULL,'ups','2DML','2nd Day Air AM Letter'),(10,NULL,NULL,'ups','2DA','2nd Day Air'),(11,NULL,NULL,'ups','2DAL','2nd Day Air Letter'),(12,NULL,NULL,'ups','3DS','3 Day Select'),(13,NULL,NULL,'ups','GND','Ground'),(14,NULL,NULL,'ups','GNDCOM','Ground Commercial'),(15,NULL,NULL,'ups','GNDRES','Ground Residential'),(16,NULL,NULL,'ups','STD','Canada Standard'),(17,NULL,NULL,'ups','XPR','Worldwide Express'),(18,NULL,NULL,'ups','WXS','Worldwide Express Saver'),(19,NULL,NULL,'ups','XPRL','Worldwide Express Letter'),(20,NULL,NULL,'ups','XDM','Worldwide Express Plus'),(21,NULL,NULL,'ups','XDML','Worldwide Express Plus Letter'),(22,NULL,NULL,'ups','XPD','Worldwide Expedited'),(23,NULL,NULL,'fedex','PRIORITYOVERNIGHT','Priority Overnight'),(24,NULL,NULL,'fedex','STANDARDOVERNIGHT','Standard Overnight'),(25,NULL,NULL,'fedex','FIRSTOVERNIGHT','First Overnight'),(26,NULL,NULL,'fedex','FEDEX2DAY','2Day'),(27,NULL,NULL,'fedex','FEDEXEXPRESSSAVER','Express Saver'),(28,NULL,NULL,'fedex','INTERNATIONALPRIORITY','International Priority'),(29,NULL,NULL,'fedex','INTERNATIONALECONOMY','International Economy'),(30,NULL,NULL,'fedex','INTERNATIONALFIRST','International First'),(31,NULL,NULL,'fedex','FEDEX1DAYFREIGHT','1 Day Freight'),(32,NULL,NULL,'fedex','FEDEX2DAYFREIGHT','2 Day Freight'),(33,NULL,NULL,'fedex','FEDEX3DAYFREIGHT','3 Day Freight'),(34,NULL,NULL,'fedex','FEDEXGROUND','Ground'),(35,NULL,NULL,'fedex','GROUNDHOMEDELIVERY','Home Delivery'),(36,NULL,NULL,'fedex','INTERNATIONALPRIORITY FREIGHT',' Intl Priority Freight'),(37,NULL,NULL,'fedex','INTERNATIONALECONOMY FREIGHT',' Intl Economy Freight'),(38,NULL,NULL,'fedex','EUROPEFIRSTINTERNATIONALPRIORITY','Europe First Priority'),(39,NULL,NULL,'dhl','IE','International Express'),(40,NULL,NULL,'dhl','E SAT','Express Saturday'),(41,NULL,NULL,'dhl','E 10:30AM','Express 10:30 AM'),(42,NULL,NULL,'dhl','E','Express'),(43,NULL,NULL,'dhl','N','Next Afternoon'),(44,NULL,NULL,'dhl','S','Second Day Service'),(45,NULL,NULL,'dhl','G','Ground'),(46,NULL,NULL,'usps','Bound Printed Matter','Bound Printed Matter'),(47,NULL,NULL,'usps','Express Mail','Express Mail'),(48,NULL,NULL,'usps','Express Mail Flat Rate Envelope','Express Mail Flat Rate Envelope'),(49,NULL,NULL,'usps','Express Mail Flat Rate Envelope Hold For Pickup','Express Mail Flat Rate Envelope Hold For Pickup'),(50,NULL,NULL,'usps','Express Mail Flat-Rate Envelope Sunday/Holiday Guarantee','Express Mail Flat-Rate Envelope Sunday/Holiday Guarantee'),(51,NULL,NULL,'usps','Express Mail Hold For Pickup','Express Mail Hold For Pickup'),(52,NULL,NULL,'usps','Express Mail International','Express Mail International'),(53,NULL,NULL,'usps','Express Mail International Flat Rate Envelope','Express Mail International Flat Rate Envelope'),(54,NULL,NULL,'usps','Express Mail PO to PO','Express Mail PO to PO'),(55,NULL,NULL,'usps','Express Mail Sunday/Holiday Guarantee','Express Mail Sunday/Holiday Guarantee'),(56,NULL,NULL,'usps','First-Class Mail International Large Envelope','First-Class Mail International Large Envelope'),(57,NULL,NULL,'usps','First-Class Mail International Letters','First-Class Mail International Letters'),(58,NULL,NULL,'usps','First-Class Mail International Package','First-Class Mail International Package'),(59,NULL,NULL,'usps','First-Class','First-Class'),(60,NULL,NULL,'usps','First-Class Mail','First-Class Mail'),(61,NULL,NULL,'usps','First-Class Mail Flat','First-Class Mail Flat'),(62,NULL,NULL,'usps','First-Class Mail International','First-Class Mail International'),(63,NULL,NULL,'usps','First-Class Mail Letter','First-Class Mail Letter'),(64,NULL,NULL,'usps','First-Class Mail Parcel','First-Class Mail Parcel'),(65,NULL,NULL,'usps','Global Express Guaranteed (GXG)','Global Express Guaranteed (GXG)'),(66,NULL,NULL,'usps','Global Express Guaranteed Non-Document Non-Rectangular','Global Express Guaranteed Non-Document Non-Rectangular'),(67,NULL,NULL,'usps','Global Express Guaranteed Non-Document Rectangular','Global Express Guaranteed Non-Document Rectangular'),(68,NULL,NULL,'usps','Library Mail','Library Mail'),(69,NULL,NULL,'usps','Media Mail','Media Mail'),(70,NULL,NULL,'usps','Parcel Post','Parcel Post'),(71,NULL,NULL,'usps','Priority Mail','Priority Mail'),(72,NULL,NULL,'usps','Priority Mail Small Flat Rate Box','Priority Mail Small Flat Rate Box'),(73,NULL,NULL,'usps','Priority Mail Medium Flat Rate Box','Priority Mail Medium Flat Rate Box'),(74,NULL,NULL,'usps','Priority Mail Large Flat Rate Box','Priority Mail Large Flat Rate Box'),(75,NULL,NULL,'usps','Priority Mail Flat Rate Box','Priority Mail Flat Rate Box'),(76,NULL,NULL,'usps','Priority Mail Flat Rate Envelope','Priority Mail Flat Rate Envelope'),(77,NULL,NULL,'usps','Priority Mail International','Priority Mail International'),(78,NULL,NULL,'usps','Priority Mail International Flat Rate Box','Priority Mail International Flat Rate Box'),(79,NULL,NULL,'usps','Priority Mail International Flat Rate Envelope','Priority Mail International Flat Rate Envelope'),(80,NULL,NULL,'usps','Priority Mail International Small Flat Rate Box','Priority Mail International Small Flat Rate Box'),(81,NULL,NULL,'usps','Priority Mail International Medium Flat Rate Box','Priority Mail International Medium Flat Rate Box'),(82,NULL,NULL,'usps','Priority Mail International Large Flat Rate Box','Priority Mail International Large Flat Rate Box'),(83,NULL,NULL,'usps','USPS GXG Envelopes','USPS GXG Envelopes'),(84,1,1,'ups','03','UPS Ground'),(85,1,2,'ups','12','UPS Three-Day Select'),(86,1,3,'ups','02','UPS Second Day Air'),(87,1,4,'ups','01','UPS Next Day Air')";
    echo "<br>";echo __LINE__; $db->exec($sql);
}//END action tables

if($action == 'alterMagentoTables')
{
    /*
     * add the required columns to magento tables.
     * 
     */
     echo "<h1> Altering Tables <h1>"; 

    $sql ="ALTER IGNORE TABLE {$dbPrefix}sales_flat_order ADD COLUMN rdi_shipper_created INT(11) DEFAULT 0 NULL";

    echo "<br>";echo __LINE__; $db->exec($sql);

    $sql ="ALTER IGNORE TABLE {$dbPrefix}sales_flat_order ADD COLUMN rdi_upload_status INT(11) DEFAULT 0 NULL";

    echo "<br>";echo __LINE__; $db->exec($sql);

    $sql ="ALTER IGNORE TABLE `{$dbPrefix}sales_flat_order`   
  ADD COLUMN `rdi_upload_date` TIMESTAMP NULL  COMMENT 'RDi Upload Date' AFTER `rdi_upload_status`";

    echo "<br>";echo __LINE__; $db->exec($sql);

    $sql ="ALTER IGNORE TABLE {$dbPrefix}customer_entity ADD COLUMN related_id VARCHAR(50) NULL";

    echo "<br>";echo __LINE__; $db->exec($sql);

    $sql ="ALTER  IGNORE TABLE {$dbPrefix}catalog_category_entity ADD related_id VARCHAR(50)";

    echo "<br>";echo __LINE__; $db->exec($sql);

    $sql ="ALTER  IGNORE TABLE {$dbPrefix}catalog_category_entity ADD COLUMN rdi_inactive_date TIMESTAMP NULL";

    echo "<br>";echo __LINE__; $db->exec($sql);

    $sql ="ALTER  IGNORE TABLE {$dbPrefix}catalog_category_entity ADD INDEX `related_id` (`related_id`)";

    echo "<br>";echo __LINE__; $db->exec($sql);
    $sql ="ALTER  IGNORE TABLE {$dbPrefix}catalog_product_entity_varchar  ADD  INDEX IDX_RDi_Value (`value`)";

    echo "<br>";echo __LINE__; $db->exec($sql);

    $sql =" ALTER TABLE `rpro_in_items_log`   
      ADD COLUMN `so_committed` INT(11) DEFAULT 0  NULL AFTER `wholesale_price`;";

    echo "<br>";echo __LINE__; $db->exec($sql);

    $sql ="ALTER TABLE `rpro_in_items`   
      ADD COLUMN `so_committed` INT(11) DEFAULT 0  NULL AFTER `wholesale_price`;";

    echo "<br>";echo __LINE__; $db->exec($sql);

    /*
     * UPDATES TO THE MAPPING
     */

    //add the cost field for items
    $sql ="INSERT INTO `rdi_field_mapping` VALUES (666, 'order_item', NULL, NULL, 'base_cost', 0, NULL, 1, NULL, NULL);";

    echo "<br>";echo __LINE__; $db->exec($sql);

    $sql ="INSERT INTO `rdi_field_mapping_pos` VALUES (666, 'rpro_out_so_items.cost', NULL, 0);";

    echo "<br>";echo __LINE__; $db->exec($sql);
    unset($sql);
    $sql = array();


        $sql[] ="ALTER TABLE `rpro_out_so`   
      ADD COLUMN `pos_flag_3` VARCHAR(60) NULL AFTER `crd_name`;";
        $sql[] ="ALTER TABLE `rpro_out_so`   
      ADD COLUMN `pos_flag_2` VARCHAR(60) NULL AFTER `crd_name`;";
        $sql[] ="ALTER TABLE `rpro_out_so`   
      ADD COLUMN `pos_flag_1` VARCHAR(60) NULL AFTER `crd_name`;";
        $sql[] ="ALTER TABLE `rpro_out_so`   
      ADD COLUMN `instruction5` VARCHAR(60) NULL AFTER `crd_name`;";
      $sql[] ="ALTER TABLE `rpro_out_so`   
      ADD COLUMN `instruction4` VARCHAR(60) NULL AFTER `crd_name`;";
      $sql[] ="ALTER TABLE `rpro_out_so`   
      ADD COLUMN `instruction3` VARCHAR(60) NULL AFTER `crd_name`;";
      $sql[] ="ALTER TABLE `rpro_out_so`   
      ADD COLUMN `instruction2` VARCHAR(60) NULL AFTER `crd_name`;";
      $sql[] ="ALTER TABLE `rpro_out_so`   
      ADD COLUMN `instruction1` VARCHAR(60) NULL AFTER `crd_name`;";
      $sql[] ="ALTER TABLE `rpro_out_so`   
      ADD COLUMN `comment2` VARCHAR(60) NULL AFTER `crd_name`;";
      $sql[] ="ALTER TABLE `rpro_out_so`   
      ADD COLUMN `comment1` VARCHAR(60) NULL AFTER `crd_name`;";

      $sql[] ="ALTER TABLE `rpro_out_so_log`   
      ADD COLUMN `instruction5` VARCHAR(60) NULL AFTER `crd_name`;";
      $sql[] ="ALTER TABLE `rpro_out_so_log`   
      ADD COLUMN `instruction4` VARCHAR(60) NULL AFTER `crd_name`;";
      $sql[] ="ALTER TABLE `rpro_out_so_log`   
      ADD COLUMN `instruction3` VARCHAR(60) NULL AFTER `crd_name`;";
      $sql[] ="ALTER TABLE `rpro_out_so_log`   
      ADD COLUMN `instruction2` VARCHAR(60) NULL AFTER `crd_name`;";
      $sql[] ="ALTER TABLE `rpro_out_so_log`   
      ADD COLUMN `instruction1` VARCHAR(60) NULL AFTER `crd_name`;";
      $sql[] ="ALTER TABLE `rpro_out_so_log`   
      ADD COLUMN `comment2` VARCHAR(60) NULL AFTER `crd_name`;";
      $sql[] ="ALTER TABLE `rpro_out_so_log`   
      ADD COLUMN `comment1` VARCHAR(60) NULL AFTER `crd_name`;";

      $sql[] ="ALTER TABLE `rpro_out_so`   
      ADD COLUMN `crd_type` VARCHAR(60) NULL AFTER `crd_name`;";

    $sql[] ="ALTER TABLE rpro_in_category_products CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";
    $sql[] ="ALTER TABLE rpro_in_category_products_log CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";

    $sql[] ="ALTER TABLE rpro_in_categories CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";
    $sql[] ="ALTER TABLE rpro_in_categories_log CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";

    $sql[] ="ALTER TABLE rpro_in_items CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";

    $sql[] ="ALTER TABLE rpro_in_items_log CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";

    $sql[] ="ALTER TABLE rpro_in_styles CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";
    $sql[] ="ALTER TABLE rpro_in_styles_log CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci'; ";

    $sql[] ="ALTER TABLE rpro_in_category_products CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";
    $sql[] ="ALTER TABLE rpro_in_category_products_log CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";

    $sql[] ="ALTER TABLE rpro_in_categories CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";
    $sql[] ="ALTER TABLE rpro_in_categories_log CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";

    $sql[] ="ALTER TABLE rpro_in_items CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";

    $sql[] ="ALTER TABLE rpro_in_items_log CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";

    $sql[] ="ALTER TABLE rpro_in_styles CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";
    $sql[] ="ALTER TABLE rpro_in_styles_log CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";

    $sql[] ="ALTER TABLE rpro_out_so CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";
    $sql[] ="ALTER TABLE rpro_out_so_log CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";

    $sql[] ="ALTER TABLE rpro_out_so_items CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";
    $sql[] ="ALTER TABLE rpro_out_so_items_log CONVERT TO CHARACTER SET utf8 COLLATE 'utf8_general_ci';";
    $sql[] ="ALTER TABLE `rpro_out_so`     CHANGE `so_type` `so_type` VARCHAR(10) CHARSET utf8 COLLATE utf8_general_ci DEFAULT '0'";
    $sql[] ="
    UPDATE rdi_field_mapping_pos SET alternative_field = 'append(\\\, )' WHERE field_mapping_id IN(647,627)";


    foreach($sql as $s)
    {
       echo "<br>";echo __LINE__; $db->exec($s);
    }
 
     
      
 }//END TABLES

if($action == 'installAttributes')
{ 
	echo "<h1> Adding Attributes <h1>"; 
	
	$mage_path = file_exists('../app/Mage.php')?'../app/Mage.php':dirname(dirname($_SERVER['SCRIPT_FILENAME'])) . "/app/Mage.php";
	require_once($mage_path);   // External script - Load magento framework
	Mage::app();
    
        $installer = Mage::getResourceModel('catalog/setup', 'catalog_setup',''); //start the installer 

        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        echo "<br>";echo __LINE__; $writeConnection = $resource->getConnection('core_write');

	//add size if it is not already there
	if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'size')) {
	echo "<br>";echo __LINE__; 
	$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'size', array(
				'group'            			 => 'General',//adds the General group to every attribute set
							'type'                       => 'int',
							'label'                      => 'Size',
							'input'                      => 'select',
							'required'                   => false,
							'user_defined'               => true,
							'searchable'                 => true,
							'filterable'                 => true,
							'comparable'                 => true,
							'visible_in_advanced_search' => true,
							'attribute_set'              => 'Default',
							'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
						)
						);
						}
	//going to load color if it is there and update it into the general group                                        

			
	  
	//add related_id if it is not already there
	if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'related_id')) {
	echo "<br>";echo __LINE__; 
	$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'related_id', array(
				'group'            			 => 'RDI',//adds the RDI group to every attribute set
							'type'                       => 'varchar',
							'label'                      => 'Related ID',
							'input'                      => 'text',
							'required'                   => false,
							'user_defined'               => true,
							'searchable'                 => false,
							'filterable'                 => false,
							'comparable'                 => false,
							'visible_in_advanced_search' => false,
						   // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE, <- this can be used for size on the default
						)
						);
						}
						
	//add related_parent_id
	if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'related_parent_id')) {
	echo "<br>";echo __LINE__; 
	$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'related_parent_id', array(
							'group'             => 'RDI',
							'type'                       => 'varchar',
							'label'                      => 'Related Parent ID',
							'input'                      => 'text',
							'required'                   => false,
							'user_defined'               => true,
							'searchable'                 => false,
							'filterable'                 => false,
							'comparable'                 => false,
							'visible_in_advanced_search' => false,
						   // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
						)
						);
		}
		
	if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'itemnum')) {
	echo "<br>";echo __LINE__; 
	$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'itemnum', array(
							'group'             => 'RDI',
							'type'                       => 'varchar',
							'label'                      => 'Item Number',
							'input'                      => 'text',
							'required'                   => false,
							'user_defined'               => true,
							'searchable'                 => false,
							'filterable'                 => false,
							'comparable'                 => false,
							'visible_in_advanced_search' => false,
						   // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
						)
						);
		}

	if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'rdi_deactivated_date')) {
	echo "<br>";echo __LINE__; 
	$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'rdi_deactivated_date', array(
							'group'             => 'RDI',
							'type'                       => 'datetime',
							'label'                      => 'Deactivated Datetime',
							'input'                      => 'text',
							'required'                   => false,
							'user_defined'               => true,
							'searchable'                 => false,
							'filterable'                 => false,
							'comparable'                 => false,
							'visible_in_advanced_search' => false,
						   // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
						)
						);
		}	
		
	if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'rdi_last_updated')) {
	echo "<br>";echo __LINE__; 
	$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'rdi_last_updated', array(
							'group'             => 'RDI',
							'type'                       => 'datetime',
							'label'                      => 'RDI Last Updated',
							'input'                      => 'text',
							'required'                   => false,
							'user_defined'               => true,
							'searchable'                 => false,
							'filterable'                 => false,
							'comparable'                 => false,
							'visible_in_advanced_search' => false,
						   // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
						)
						);
		}

			//add rdi_avail
	if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'rdi_avail')) {
	echo "<br>";echo __LINE__; 
	$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, 'rdi_avail', array(
							'group'             => 'RDI',
							'type'                       => 'varchar',
							'label'                      => 'RDi Avail Field',
							'input'                      => 'text',
							'required'                   => false,
							'user_defined'               => true,
							'searchable'                 => false,
							'filterable'                 => false,
							'comparable'                 => false,
							'visible_in_advanced_search' => false,
						   // 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
						)
						);
		}
        $cart->_echo("FINISHED ATTRIBUTES");
}

 include 'init.php';
   
global $cart, $db_lib;


if($action == 'rproCategoryCount')
{
	$count = $db_lib->get_category_count();
	echo "<br>Categories:  {$count}";
}

if($action == 'rproStyleCount')
{	
	$count = $db_lib->get_product_count();
	echo "<br>Styles:  {$count}";
	
}

if($action == 'installRdiAvail')
{


        $cart->_echo("ADD rdi_avail mapping");
        $db = $cart->get_db();
	$avail_mapping_test = $db->cell("select count(*) as test from rdi_field_mapping where field_type = 'avail'", 'test');
	if(!isset($avail_mapping_test) || $avail_mapping_test <= 0)
	{
	
		$quantity_field = $pos_type == "rpro8"?"item_availquantity":"quantity";
		$threshold_field = $pos_type == "rpro8"?"fldavailthreshold":"threshold";
	echo "<br>";echo __LINE__; 
			//add the mapping
		$db->exec("INSERT INTO `rdi_field_mapping` (`field_mapping_id`, `field_type`, `field_classification`, `entity_type`, `cart_field`, `invisible_field`, `default_value`, `allow_update`, `special_handling`, `notes`) 
		VALUES 
		('10001', 'avail', NULL, 'simple', 'qty', '0', NULL, '1', NULL, NULL),
		('10002', 'avail', NULL, 'simple', 'min_qty', '0', NULL, '1', NULL, NULL),
		('10003', 'avail', NULL, 'simple', 'is_in_stock', '0', NULL, '1', NULL, NULL),
		('10004', 'avail', NULL, 'simple', 'use_config_min_qty', '0', '0', '1', NULL, NULL),
		('10005', 'avail', NULL, 'simple', 'manage_stock', '0', NULL, '1', NULL, NULL),
		('10006', 'avail', NULL, 'simple', 'use_config_manage_stock', '0', NULL, '1', NULL, NULL),
		('10007', 'avail', NULL, 'simple', 'backorders', '0', NULL, '1', NULL, NULL),
		('10008', 'avail', NULL, 'simple', 'use_config_backorders', '0', NULL, '1', NULL, NULL),
		('10009', 'avail', NULL, 'simple', 'qty_increments', '0', NULL, '1', NULL, NULL),
		('10010', 'avail', NULL, 'simple', 'use_config_enable_qty_inc', '0', NULL, '1', NULL, NULL),
		('10011', 'avail', NULL, 'simple', 'enable_qty_increments', '0', NULL, '1', NULL, NULL);");
echo "<br>";echo __LINE__; 
		$db->exec("INSERT INTO `rdi_field_mapping_pos` (`field_mapping_id`, `pos_field`, `alternative_field`, `field_order`)
		 VALUES 
		('10001', \"if(avail.value = 'Sell Never',0,item.{$quantity_field})\", NULL, '0'), 
		('10002', \"if(avail.value = 'Sell to Threshold',style.{$threshold_field},0)\", NULL, '0'), 
		('10003', \"if(avail.value = 'Sell Never',0,1)\", NULL, '0'), 
		('10004', \"0\", NULL, '0'), 
		('10005', \"if(avail.value = 'Sell Always',0,1)\", NULL, '0'), 
		('10006', \"if(avail.value = 'Sell Always',0,1)\", NULL, '0'), 
		('10007', \"if(avail.value = 'Allow Backorder',2,0)\", NULL, '0'), 
		('10008', \"if(avail.value = 'Allow Backorder',0,1)\", NULL, '0'), 
		('10009', \"0.0000\", NULL, '0'), 
		('10010', \"1\", NULL, '0'), 
		('10011', \"0\", NULL, '0')");



echo "<br>";echo __LINE__; 
		$db->exec("INSERT INTO `rdi_field_mapping` (`field_mapping_id`, `field_type`, `field_classification`, `entity_type`, `cart_field`, `invisible_field`, `default_value`, `allow_update`, `special_handling`, `notes`) VALUES 
		('10101', 'avail', NULL, 'configurable', 'qty', '0', NULL, '1', NULL, NULL),
		('10102', 'avail', NULL, 'configurable', 'min_qty', '0', NULL, '1', NULL, NULL),
		('10103', 'avail', NULL, 'configurable', 'is_in_stock', '0', NULL, '1', NULL, NULL),
		('10104', 'avail', NULL, 'configurable', 'use_config_min_qty', '0', NULL, '1', NULL, NULL),
		('10105', 'avail', NULL, 'configurable', 'manage_stock', '0', NULL, '1', NULL, NULL),
		('10106', 'avail', NULL, 'configurable', 'use_config_manage_stock', '0', NULL, '1', NULL, NULL),
		('10107', 'avail', NULL, 'configurable', 'backorders', '0', NULL, '1', NULL, NULL),
		('10108', 'avail', NULL, 'configurable', 'use_config_backorders', '0', NULL, '1', NULL, NULL),
		('10109', 'avail', NULL, 'configurable', 'qty_increments', '0', NULL, '1', NULL, NULL),
		('10110', 'avail', NULL, 'configurable', 'use_config_enable_qty_inc', '0', NULL, '1', NULL, NULL),
		('10111', 'avail', NULL, 'configurable', 'enable_qty_increments', '0', NULL, '1', NULL, NULL);");

echo "<br>";echo __LINE__; 
		$db->exec("INSERT INTO `rdi_field_mapping_pos` (`field_mapping_id`, `pos_field`, `alternative_field`, `field_order`) VALUES 
		('10101', \"0\", NULL, '0'), 
		('10102', \"0\", NULL, '0'), 
		('10103', \"if(avail.value = 'Sell Never',0,1)\", NULL, '0'), 
		('10104', \"0\", NULL, '0'), 
		('10105', \"if(avail.value = 'Sell Always',0,1)\", NULL, '0'), 
		('10106', \"if(avail.value = 'Sell Always',0,1)\", NULL, '0'), 
		('10107', \"if(avail.value = 'Allow Backorder',2,0)\", NULL, '0'), 
		('10108', \"if(avail.value = 'Allow Backorder',0,1)\", NULL, '0'), 
		('10109', \"0.0000\", NULL, '0'), 
		('10110', \"1\", NULL, '0'), 
		('10111', \"0\", NULL, '0')");
                
                
                //this is added to the mapping, but only turned on with request, so just going to uninstall for now.
                $db->exec("UPDATE rdi_field_mapping SET field_type = 'availx' WHERE field_type = 'avail'");
	}
   $cart->_echo("Finished rdi_avail mapping"); 
}        
  
if($action == 'Tables')
{
       $cart->_echo("Adding Views"); 
       echo "<br>";echo __LINE__; 
       $cart->get_db()->exec("CREATE VIEW `rpro_in_prices` AS (
    SELECT
      `rpro_in_items`.`style_sid` AS `style_sid`,
      MIN(`rpro_in_items`.`reg_price`) AS `reg_price`,
      MIN(`rpro_in_items`.`cost`) AS `cost`,
      MIN(`rpro_in_items`.`sale_price`) AS `sale_price`,
      MIN(`rpro_in_items`.`msrp_price`) AS `msrp_price`,
      MIN(`rpro_in_items`.`wholesale_price`) AS `wholesale_price`
    FROM `rpro_in_items`
    GROUP BY `rpro_in_items`.`style_sid`)");

    $cart->_echo("Finished Views");     
}

if($action = "add_attribute")
{
	if(isset($attribute_name) && strlen($attribute_name) > 1 && $attribute_name !== 'collection' && isset($attribute_label) && strlen($attribute_label) > 1)
	{
		//This could be done less ridigly. We are only going to allow additional configurable attributes and additional rdi attributes
		if($attribute_type == 'rdi_varchar')
		{
			if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, $attribute_name))
			{

				$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_name, array(
					'group' => 'RDI',
					'type' => 'varchar',
					'label' => $attribute_label,
					'input' => 'text',
					'required' => false,
					'user_defined' => true,
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'visible_in_advanced_search' => false,
						// 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
						)
				);
			}
		}
		else if($attribute_type == 'configurable_int')
		{
			if (!$installer->getAttributeId(Mage_Catalog_Model_Product::ENTITY, $attribute_name))
			{

				$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_name, array(
					'group' => 'General', //adds the General group to every attribute set
					'type' => 'int',
					'label' => $attribute_label,
					'input' => 'select',
					'required' => false,
					'user_defined' => true,
					'searchable' => true,
					'filterable' => true,
					'comparable' => true,
					'visible_in_advanced_search' => true,
					'attribute_set' => 'Default',
					'apply_to' => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
						)
				);
			}
		}
		else if($attribute_type == 'rdi_datetime')
		{
			$installer->addAttribute(Mage_Catalog_Model_Product::ENTITY, $attribute_name, array(
					'group' => 'RDI',
					'type' => 'datetime',
					'label' => '$attribute_label',
					'input' => 'text',
					'required' => false,
					'user_defined' => true,
					'searchable' => false,
					'filterable' => false,
					'comparable' => false,
					'visible_in_advanced_search' => false,
						// 'apply_to'                   => Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
						)
				);
		}
	}
}



$cart->_echo("Complete",'h1');    
        
        //extra functions for some common issues
function remove_prefix($prefix,$dbname)
{
	//get the prefix name from the local.xml might need to make something to grab this auto and to rename it accordingly.
   $resource = Mage::getSingleton('core/resource');
    $readConnection = $resource->getConnection('core_read');
    echo "<br>";echo __LINE__; $writeConnection = $resource->getConnection('core_write');
   $field_name = "Tables_in_{$dbname}";
   $_table = $readConnection->fetchAll("SHOW TABLES WHERE {$field_name} like '{$prefix}%'");
   //print_r($_table);
   foreach($_table as $table)
   {
       $table_new = str_replace($prefix, "",$table[$field_name]);
       echo "RENAME TABLE `{$table[$field_name]}` TO `{$table_new}`;";
       echo "<br>";
      echo "<br>";echo __LINE__; $db->exec("RENAME TABLE `{$table[$field_name]}` TO `{$table_new}`;");
      
   }
    
    
}

/**
to reinstall.
show tables like 'rdi%'
show tables like 'rpro%'


drop table	rdi_addons	;
drop table	rdi_attribute_sort	;
drop table	rdi_capture_log	;
drop table	rdi_card_type_mapping	;
drop table	rdi_cart_class_map_criteria	;
drop table	rdi_cart_class_map_fields	;
drop table	rdi_cart_class_mapping	;
drop table	rdi_cart_product_types	;
drop table	rdi_config	;
drop table	rdi_debug_log	;
drop table	rdi_error_log	;
drop table	rdi_field_mapping	;
drop table	rdi_field_mapping_pos	;
drop table	rdi_item_image	;
drop table	rdi_loadtimes_log	;
drop table	rdi_out_customers	;
drop table	rdi_out_customers_log	;
drop table	rdi_out_so_items_log	;
drop table	rdi_out_so_log	;
drop table	rdi_prefs_scales	;
drop table	rdi_settings	;
drop table	rdi_tax_area_mapping	;
drop table	rdi_tax_class_mapping	;
drop table	rpro_in_categories	;
drop table	rpro_in_categories_log	;
drop table	rpro_in_category_products	;
drop table	rpro_in_category_products_log	;
drop table	rpro_in_customers	;
drop table	rpro_in_customers_log	;
drop table	rpro_in_items	;
drop table	rpro_in_items_log	;
drop table	rpro_in_receipts	;
drop table	rpro_in_receipts_log	;
drop table	rpro_in_so	;
drop table	rpro_in_so_log	;
drop table	rpro_in_styles	;
drop table	rpro_in_styles_log	;
drop table	rpro_in_upsell_item	;
drop table	rpro_in_upsell_item_log	;
drop table	rpro_mage_shipping	;
drop table	rpro_out_customers	;
drop table	rpro_out_customers_log	;
drop table	rpro_out_so	;
drop table	rpro_out_so_items	;
drop table	rpro_out_so_items_log	;
drop table	rpro_out_so_log	;


*/
        
?>

