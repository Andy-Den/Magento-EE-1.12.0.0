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
header('Content-Type: application/html');
echo "<div>";
if (isset($_GET['continue']) && $_GET['continue'] == 2)
{
    echo "<h1>Common Tasks</h1>";
    echo '<a href="add_ons/magento_rpro9_pos_common_pre_load.php?verbose_queries=1&install=1" target="_blank"><h3>Install Backup Module</h3></a>';
    echo '<a href="libraries/cart_libs/magento/tools/staging_stats.php" target="_blank"><h3>Create Staging Stats</h3></a>';
    echo '<a href="rdi_upload_styles.php?verbose_queries=1" target="_blank"><h3>Upload Styles XML</h3></a>';
    echo '<a href="rdi_upload_catalog.php?verbose_queries=1" target="_blank"><h3>Upload Catalog XML</h3></a>';
    echo '<a href="libraries/cart_libs/magento/tools/magento_url_key_fix.php" target="_blank"><h3>Magento Url Key Fix(Advanced)</h3></a>';
    exit;
}

foreach ($_GET as $k => $v)
{
    $GLOBALS[$k] = $v;
}

if (isset($_POST['action']))
{
    $action = $_POST['action'];
}

if (isset($_POST['test_install']))
{
    $test_install = $_POST['test_install'];
}

include 'libraries/cart_libs/magento/magento_rdi_db_lib.php';
$rdi_path = '';
$db_m = new rdi_db_lib();
$db = $db_m->get_db_obj();
$dbPrefix = $db->get_db_prefix();

include_once 'libraries/class.general.php';
include_once 'libraries/class.install.php';

$install = new rdi_install($db);

if ($action == 'init')
{
    if (isset($dbPrefix) && $dbPrefix != '')
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
    $install->create_folders();

    //add the RDI user
    $db->exec("REPLACE INTO {$dbPrefix}admin_user (firstname,lastname,email,username,PASSWORD)
VALUES
('Retail','Dimensions','magento@retaildimensions.com','rdi',CONCAT(MD5('vgretail" . date("y") . "'),':vg'));");

    $db->exec("REPLACE INTO {$dbPrefix}admin_role(parent_id,tree_level,sort_order,role_type,user_id,role_name)
    SELECT  1 AS parent_id, 2 AS treelevel, 0 AS sort_order, 'U' AS role_type, au.user_id, au.firstname AS role_name FROM {$dbPrefix}admin_user au
    WHERE au.email = 'magento@retaildimensions.com'");

    echo "<h1> Check the <a href='../" . $db_m->get_adminname() . "'>Admin</a> <h1>";

    exit;
}// END action init
if ($action == 'Tables')
{
     //Add rdi_tables
    $install->add_core_rdi_tables();
    $install->install_pos_staging_tables('rpro9');
   
    //default mapping.
    $install->exec_insert_into("INSERT INTO `rdi_tax_class_mapping` (cart_type, pos_type) VALUES ('Taxable Goods',0),('None',1)");

	
    //create view priceqty view
	if(!$bool_test_install){
     $db->exec("CREATE VIEW `rpro_in_prices_priceqty` AS 
(SELECT 
  `rpro_in_priceqty`.`style_sid` AS `style_sid`,
  MIN(`rpro_in_priceqty`.`reg_price`) AS `reg_price`,
  MIN(`rpro_in_priceqty`.`cost`) AS `cost`,
  MIN(
    `rpro_in_priceqty`.`sale_price`
  ) AS `sale_price`,
  MIN(
    `rpro_in_priceqty`.`msrp_price`
  ) AS `msrp_price`,
  MIN(
    `rpro_in_priceqty`.`wholesale_price`
  ) AS `wholesale_price` 
FROM
  `rpro_in_priceqty` 
GROUP BY `rpro_in_priceqty`.`style_sid`)");
     
     $db->exec("CREATE VIEW `rpro_in_prices` AS (
    SELECT
      `rpro_in_items`.`style_sid` AS `style_sid`,
      MIN(`rpro_in_items`.`reg_price`) AS `reg_price`,
      MIN(`rpro_in_items`.`cost`) AS `cost`,
      MIN(`rpro_in_items`.`sale_price`) AS `sale_price`,
      MIN(`rpro_in_items`.`msrp_price`) AS `msrp_price`,
      MIN(`rpro_in_items`.`wholesale_price`) AS `wholesale_price`
    FROM `rpro_in_items`
    WHERE rpro_in_items.excluded = 0
    GROUP BY `rpro_in_items`.`style_sid`)");
	}

    $install->exec_insert_into("INSERT INTO `rdi_card_type_mapping`(`cart_type`,`pos_type`) values ('AE','AMEX'),('VI','VISA'),
('DI','DISCOV'),('MC','MASTER')");

    $install->exec_insert_into("INSERT INTO `rdi_cart_class_map_criteria`(`cart_class_mapping_id`,`cart_field`,`qualifier`) values (1,'color','IS NOT NULL'),(1,'size','IS NOT NULL'),(2,'color','IS NOT NULL'),(2,'size','IS NULL'),(3,'color','IS NULL'),(3,'size','IS NOT NULL'),(4,'color','IS NULL'),(4,'size','IS NULL')");

   
    $install->exec_insert_into("INSERT INTO `rdi_cart_class_map_fields`(`cart_class_mapping_id`,`cart_field`,`position`,`label`) values (1,'color',0,NULL),(1,'size',1,NULL),(2,'color',0,NULL),(3,'size',0,NULL)");

    $install->exec_insert_into("INSERT INTO `rdi_cart_class_mapping`(`cart_class_mapping_id`,`product_class_id`,`product_class`) values (1,4,'Default'),(2,4,'Default'),(3,4,'Default'),(4,4,'Default')");

    $install->exec_insert_into("INSERT INTO `rdi_cart_product_types`(`cart_product_type_id`,`cart_class_mapping_id`,`product_type`,`visibility`,`creation_order`) values (1,1,'simple',NULL,0),(2,1,'configurable',NULL,1),(3,2,'simple',NULL,0),(4,2,'configurable',NULL,1),(5,3,'simple',NULL,0),(6,3,'configurable',NULL,1),(7,4,'simple',NULL,0)");

    //-----------------------------------------------
    //START product 
	
     $install->exec_insert_into("INSERT INTO `rdi_field_mapping` (field_mapping_id,field_type,field_classification,entity_type,cart_field,invisible_field,default_value,allow_update,special_handling,notes) VALUES 
    ('1000','product',NULL,NULL,'name','0',NULL,'1',NULL,NULL), 
    ('1001','product',NULL,NULL,'description','0',NULL,'1',NULL,NULL), 
    ('1002','product',NULL,NULL,'short_description','0',NULL,'1',NULL,NULL), 
    ('1003','product',NULL,'configurable','sku','0',NULL,'1',NULL,NULL), 
    ('1004','product',NULL,'configurable','related_id','1',NULL,'1',NULL,NULL), 
    ('1005','product',NULL,NULL,'style_id','1',NULL,'1',NULL,NULL), 
    ('1006','product',NULL,NULL,'url_path','0',NULL,'0','lower,no_space',NULL), 
    ('1007','product',NULL,NULL,'url_key','0','','0','lower,no_space',NULL), 
    ('1008','product',NULL,'simple','weight','0','1.0000','1',NULL,NULL), 
    ('1009','product',NULL,NULL,'msrp','0','0','1',NULL,NULL), 
    ('1010','product',NULL,'simple','price','0',NULL,'1',NULL,NULL), 
    ('1011','product',NULL,'simple','special_price','0',NULL,'1','zero_null',NULL), 
    ('1012','product',NULL,'configurable','price','0',NULL,'1','zero_null',NULL), 
    ('1013','product',NULL,'configurable','special_price','0',NULL,'1','zero_null',NULL), 
    ('1014','product',NULL,'configurable','status','0','2','1',NULL,NULL), 
    ('1015','product',NULL,'simple','status','0','1','1',NULL,NULL), 
    ('1016','product',NULL,NULL,'status','0','1','1',NULL,NULL), 
    ('1017','product',NULL,'configurable','visibility','0','4','1',NULL,NULL), 
    ('1018','product',NULL,NULL,'item_id','1',NULL,'1',NULL,NULL), 
    ('1019','product',NULL,'simple','sku','0',NULL,'1',NULL,NULL), 
    ('1020','product',NULL,'simple','related_id','1',NULL,'1',NULL,NULL), 
    ('1021','product',NULL,'simple','color','0',NULL,'1',NULL,NULL), 
    ('1022','product',NULL,'simple','size','0',NULL,'1',NULL,NULL), 
    ('1023','product',NULL,'simple','qty','0',NULL,'1',NULL,NULL), 
    ('1024','product',NULL,NULL,'color_sort_order','0',NULL,'1',NULL,NULL), 
    ('1025','product',NULL,NULL,'size_sort_order','0',NULL,'1',NULL,NULL), 
    ('1026','product',NULL,'configurable','related_parent_id','0',NULL,'1',NULL,NULL), 
    ('1027','product',NULL,'simple','related_parent_id','0',NULL,'1','IS(size:NULL;color:NULL;|style_id|)',NULL), 
    ('1028','product',NULL,'simple','visibility','0','1','1',NULL,NULL), 
    ('1029','product',NULL,NULL,'is_in_stock','0','1','1',NULL,NULL), 
    ('1030','product',NULL,'configurable','qty','0',NULL,'1',NULL,NULL), 
    ('1031','product',NULL,'simple','visibility','0','1','1','is(size:null;color:null;|\'4\'|$)',NULL), 
    ('1032','product',NULL,'simple','cost','0','','1',NULL,NULL), 
    ('1033','product',NULL,NULL,'meta_keyword','0','','1',NULL,NULL), 
    ('1034','product',NULL,NULL,'meta_title','0','','1',NULL,NULL), 
    ('1035','product',NULL,NULL,'meta_description','0','','1',NULL,NULL), 
    ('1036','product',NULL,NULL,'thumbnail','0','no_selection','0',NULL,NULL), 
    ('1037','product',NULL,NULL,'small_image','0','no_selection','0',NULL,NULL), 
    ('1038','product',NULL,NULL,'image','0','no_selection','0',NULL,NULL), 
    ('1039','product',NULL,'simple','itemnum','0',NULL,'1',NULL,NULL), 
    ('1040','product',NULL,NULL,'manufacturer','0',NULL,'1',NULL,NULL), 
    ('1041','product',NULL,NULL,'country_of_manufacture','0',NULL,'1',NULL,NULL), 
    ('1042','product',NULL,NULL,'news_from_date','0','NOW()','1',NULL,NULL), 
    ('1043','product',NULL,NULL,'news_to_date','0','NOW()','1',NULL,'UPDATE `rdi_field_mapping` SET `default_value` = \'ADDDATE(NOW(),30)\' ,`notes` = ADDDATE(NOW(),30) WHERE `field_mapping_id` = \'153\';'), 
    ('1044','product',NULL,NULL,'special_from_date','0',NULL,'1',NULL,NULL), 
    ('1045','product',NULL,NULL,'special_to_date','0',NULL,'1',NULL,NULL), 
    ('1046','product',NULL,NULL,'msrp_enabled','0','2','0',NULL,NULL), 
    ('1047','product',NULL,NULL,'msrp_display_actual_price_type','0','4','0',NULL,NULL), 
    ('1048','product',NULL,NULL,'rdi_last_updated','0','NOW()','1',NULL,NULL), 
    ('1049','product',NULL,NULL,'custom_design','0','','0',NULL,NULL), 
    ('1050','product',NULL,NULL,'options_container','0','container1','0',NULL,NULL), 
    ('1051','product',NULL,NULL,'gift_message_available','0','','1',NULL,NULL), 
    ('1052','product',NULL,NULL,'custom_layout_update','0','','1',NULL,NULL), 
    ('1053','product',NULL,NULL,'custom_design_from','0',NULL,'1',NULL,NULL), 
    ('1054','product',NULL,NULL,'custom_design_to','0',NULL,'1',NULL,NULL), 
    ('1055','product',NULL,NULL,'tax_class_id','0','2','1',NULL,NULL), 
    ('1056','product',NULL,NULL,'is_qty_decimal','0','1','1',NULL,NULL), 
    ('1057','product',NULL,NULL,'is_recurring','0','0','1',NULL,NULL), 
    ('1058','product',NULL,NULL,'use_config_enable_qty_inc','0','1','1',NULL,NULL), 
    ('1059','product',NULL,NULL,'use_config_backorders','0','0','1',NULL,NULL), 
    ('1060','product',NULL,NULL,'use_config_manage_stock','0','1','1',NULL,NULL), 
    ('1061','product',NULL,NULL,'use_config_max_sale_qty','0','1','1',NULL,NULL), 
    ('1062','product',NULL,NULL,'low_stock_date','0',NULL,'1',NULL,NULL), 
    ('1063','product',NULL,NULL,'use_config_min_qty','0','0','1',NULL,NULL), 
    ('1064','product',NULL,NULL,'use_config_min_sale_qty','0','1','1',NULL,NULL), 
    ('1065','product',NULL,NULL,'stock_status_changed_auto','0','1','1',NULL,NULL), 
    ('1066','product',NULL,NULL,'use_config_notify_stock_qty','0','1','1',NULL,NULL), 
    ('1067','product',NULL,NULL,'use_config_qty_increments','0','1','1',NULL,NULL), 
    ('1068','product',NULL,NULL,'product_image','0',NULL,'1',NULL,NULL), 
    ('1069','product',NULL,NULL,'avail','0',NULL,'1',NULL,NULL), 
    ('1070','product',NULL,NULL,'manage_stock','0','1','1',NULL,NULL), 
    ('1071','product',NULL,'simple','min_qty','0','1','1',NULL,NULL);");

    //pos 
     $install->exec_insert_into("INSERT INTO `rdi_field_mapping_pos` (field_mapping_id,pos_field,alternative_field,field_order) VALUES 
    ('1000','style.product_name','style.desc1','0'), 
    ('1001','style.long_desc',NULL,'0'), 
    ('1002','style.alt1_desc',NULL,'0'), 
    ('1003','style.desc1',NULL,'0'), 
    ('1004','style.style_sid',NULL,'0'), 
    ('1005','style.style_sid',NULL,'0'), 
    ('1006','style.desc1','style.desc2','0'), 
    ('1006','\'.html\'','\'.html\'','1'), 
    ('1007','style.desc2','style.desc1','0'), 
    ('1008','NULLIF(item.ship_weight1,0)',NULL,'0'), 
    ('1010','item.reg_price',NULL,'0'), 
    ('1011','item.sale_price',NULL,'0'), 
    ('1012','(SELECT rpro_in_prices.reg_price FROM rpro_in_prices WHERE style.style_sid = rpro_in_prices.style_sid)',NULL,'0'), 
    ('1013','(SELECT rpro_in_prices.sale_price FROM rpro_in_prices WHERE style.style_sid = rpro_in_prices.style_sid)',NULL,'0'), 
    ('1018','item.item_sid',NULL,'0'), 
    ('1019','item.alu','item.item_sid','0'), 
    ('1020','item.item_sid',NULL,'0'), 
    ('1021','item.attr',NULL,'0'), 
    ('1022','item.size',NULL,'0'), 
    ('1023','item.quantity - item.so_committed',NULL,'0'), 
    ('1024','item.attr_order',NULL,'0'), 
    ('1025','item.size_order',NULL,'0'), 
    ('1026','style.style_sid',NULL,'0'), 
    ('1027','item.style_sid',NULL,'0'), 
    ('1032','item.cost',NULL,'0'), 
    ('1034','style.desc2','style.desc1','0'), 
    ('1039','item.item_num','item.alu','0'), 
    ('1040','style.vendor',NULL,'0'), 
    ('1068','style.style_image',NULL,'0'), 
    ('1069','style.Avail',NULL,'0'), 
    ('1071','style.threshold',NULL,'0');");


    //END product
    //-----------------------------------------------
    //-----------------------------------------------
    //category 

     $install->exec_insert_into("INSERT INTO `rdi_field_mapping` (field_mapping_id,field_type,field_classification,entity_type,cart_field,invisible_field,default_value,allow_update,special_handling,notes) VALUES 
    ('2000','category',NULL,NULL,'name','0',NULL,'1',NULL,NULL), 
    ('2001','category',NULL,NULL,'description','0',NULL,'1',NULL,NULL), 
    ('2002','category',NULL,NULL,'position','0',NULL,'1',NULL,NULL), 
    ('2003','category',NULL,NULL,'url_key','0',NULL,'1','lower,no_space',NULL), 
    ('2004','category',NULL,NULL,'parent_id','0',NULL,'1',NULL,NULL), 
    ('2005','category',NULL,NULL,'related_id','0',NULL,'1',NULL,NULL), 
    ('2006','category',NULL,NULL,'is_active','0','1','1',NULL,NULL), 
    ('2007','category',NULL,NULL,'is_anchor','0','0','1',NULL,NULL), 
    ('2008','category',NULL,NULL,'meta_title','0',NULL,'1',NULL,NULL), 
    ('2009','category',NULL,NULL,'meta_keywords','0',NULL,'1',NULL,NULL), 
    ('2010','category',NULL,NULL,'meta_description','0',NULL,'1',NULL,NULL), 
    ('2011','category',NULL,NULL,'display_mode','0','PRODUCTS','0',NULL,NULL), 
    ('2012','category',NULL,NULL,'custom_apply_to_products','0',NULL,'0',NULL,NULL), 
    ('2013','category',NULL,NULL,'custom_design','0',NULL,'0',NULL,NULL), 
    ('2014','category',NULL,NULL,'custom_design_from','0',NULL,'0',NULL,NULL), 
    ('2015','category',NULL,NULL,'custom_design_to','0',NULL,'0',NULL,NULL), 
    ('2016','category',NULL,NULL,'custom_layout_update','0',NULL,'0',NULL,NULL), 
    ('2017','category',NULL,NULL,'custom_use_parent_settings','0',NULL,'0',NULL,NULL), 
    ('2018','category',NULL,NULL,'page_layout','0',NULL,'0',NULL,NULL), 
    ('2019','category',NULL,NULL,'filter_price_range','0',NULL,'0',NULL,NULL), 
    ('2020','category',NULL,NULL,'available_sort_by','0',NULL,'0',NULL,NULL), 
    ('2021','category',NULL,NULL,'include_in_menu','0','1','1',NULL,NULL);");

    //pos 
     $install->exec_insert_into("INSERT INTO `rdi_field_mapping_pos` (field_mapping_id,pos_field,alternative_field,field_order) VALUES 
    ('2000','category',NULL,'0'), 
    ('2001','description',NULL,'0'), 
    ('2002','sort_order',NULL,'0'), 
    ('2003','category',NULL,'0'), 
    ('2004','rpro_in_categories.parent_id',NULL,'0'), 
    ('2005','catalog_id',NULL,'0'), 
    ('2008','category',NULL,'0');");


    //END category
    //-----------------------------------------------
    //-----------------------------------------------
    //category_product 

     $install->exec_insert_into("INSERT INTO `rdi_field_mapping` (field_mapping_id,field_type,field_classification,entity_type,cart_field,invisible_field,default_value,allow_update,special_handling,notes) VALUES 
    ('2500','category_product',NULL,NULL,'entity_id','1',NULL,'1',NULL,NULL), 
    ('2501','category_product',NULL,NULL,'related_id','1',NULL,'1',NULL,NULL), 
    ('2502','category_product',NULL,NULL,'position','0',NULL,'1',NULL,NULL);");

    //pos 
     $install->exec_insert_into("INSERT INTO `rdi_field_mapping_pos` (field_mapping_id,pos_field,alternative_field,field_order) VALUES 
    ('2500','style_sid',NULL,'0'), 
    ('2501','rpro_in_category_products.catalog_id',NULL,'0'), 
    ('2502','rpro_in_category_products.sort_order',NULL,'0');");


    //END category_product
    //-----------------------------------------------
    //-----------------------------------------------
    //order 

     $install->exec_insert_into("INSERT INTO `rdi_field_mapping` (field_mapping_id,field_type,field_classification,entity_type,cart_field,invisible_field,default_value,allow_update,special_handling,notes) VALUES 
    ('3000','order',NULL,NULL,'increment_id','0',NULL,'1',NULL,NULL), 
    ('3001','order',NULL,NULL,'created_at','0',NULL,'1','date',NULL), 
    ('3002','order',NULL,NULL,'customer_id','0',NULL,'1',NULL,NULL), 
    ('3003','order',NULL,NULL,'base_shipping_amount','0',NULL,'1',NULL,NULL), 
    ('3004','order',NULL,NULL,'base_shipping_tax_amount','0',NULL,'1',NULL,NULL), 
    ('3005','order',NULL,'discount_amount',NULL,'0','0.0000','1','abs',NULL), 
    ('3006','order',NULL,NULL,'card_type','0',NULL,'1',NULL,NULL), 
    ('3007','order',NULL,NULL,'shipping_method_id','0',NULL,'1',NULL,NULL), 
    ('3008','order',NULL,NULL,'shipping_provider_id','0',NULL,'1',NULL,NULL), 
    ('3009','order',NULL,NULL,'customer_id','0',NULL,'1',NULL,NULL), 
    ('3010','order',NULL,NULL,'subtotal_incl_tax','0',NULL,'1',NULL,NULL), 
    ('3011','order',NULL,'addr_no',NULL,'0','1','1',NULL,NULL), 
    ('3012','order',NULL,'shipto_addr_no',NULL,'0','1','1',NULL,NULL), 
    ('3013','order',NULL,'priority',NULL,'0','1','1',NULL,NULL), 
    ('3014','order',NULL,'use_vat',NULL,'0','0','1',NULL,NULL), 
    ('3015','order',NULL,'cms',NULL,'0','1','1',NULL,NULL), 
    ('3016','order',NULL,'active',NULL,'0','1','1',NULL,NULL), 
    ('3017','order',NULL,'verified',NULL,'0','0','1',NULL,NULL), 
    ('3018','order',NULL,'held',NULL,'0','0','1',NULL,NULL), 
    ('3019','order',NULL,'doc_source',NULL,'0','0','1',NULL,NULL), 
    ('3020','order',NULL,'controller',NULL,'0','1','1',NULL,NULL), 
    ('3021','order',NULL,'orig_controller',NULL,'0','1','1',NULL,NULL), 
    ('3022','order',NULL,'elapsed_time',NULL,'0','1','1',NULL,NULL), 
    ('3023','order',NULL,'line_pos_seq',NULL,'0','2','1',NULL,NULL), 
    ('3024','order',NULL,'activity_perc',NULL,'0','100','1',NULL,NULL), 
    ('3025','order',NULL,'detax',NULL,'0','0','1',NULL,NULL), 
    ('3026','order',NULL,'shipto_customer_detax',NULL,'0','0','1',NULL,NULL), 
    ('3027','order',NULL,'shipto_customer_cms',NULL,'0','1','1',NULL,NULL), 
    ('3028','order',NULL,'shipto_customer_shipping',NULL,'0','0','1',NULL,NULL), 
    ('3029','order',NULL,'so_type',NULL,'0','1','1',NULL,NULL);");

    //pos 
     $install->exec_insert_into("INSERT INTO `rdi_field_mapping_pos` (field_mapping_id,pos_field,alternative_field,field_order) VALUES 
    ('3000','so_no',NULL,'0'), 
    ('3000','orderid',NULL,'0'), 
    ('3000','so_sid',NULL,'0'), 
    ('3001','created_date',NULL,'0'), 
    ('3001','modified_date',NULL,'0'), 
    ('3001','cms_post_date',NULL,'0'), 
    ('3002','cust_sid',NULL,'0'), 
    ('3003','shipping_amt',NULL,'0'), 
    ('3004','shipping_tax',NULL,'0'), 
    ('3005','disc_amt',NULL,'0'), 
    ('3006','crd_name',NULL,'0'), 
    ('3007','ship_method',NULL,'0'), 
    ('3009','customer_cust_sid',NULL,'0'), 
    ('3010','tender_taken',NULL,'0'), 
    ('3011','addr_no',NULL,'0'), 
    ('3012','shipto_addr_no',NULL,'0'), 
    ('3013','priority',NULL,'0'), 
    ('3014','use_vat',NULL,'0'), 
    ('3015','cms',NULL,'0'), 
    ('3016','active',NULL,'0'), 
    ('3017','verified',NULL,'0'), 
    ('3018','held',NULL,'0'), 
    ('3019','doc_source',NULL,'0'), 
    ('3020','controller',NULL,'0'), 
    ('3021','orig_controller',NULL,'0'), 
    ('3022','elapsed_time',NULL,'0'), 
    ('3023','line_pos_seq',NULL,'0'), 
    ('3024','activity_perc',NULL,'0'), 
    ('3025','detax',NULL,'0'), 
    ('3026','shipto_customer_detax',NULL,'0'), 
    ('3027','shipto_customer_cms',NULL,'0'), 
    ('3028','shipto_customer_shipping',NULL,'0'), 
    ('3028','so_type',NULL,'0');");


    //END order
    //-----------------------------------------------
    //-----------------------------------------------
    //order_bill_to 

     $install->exec_insert_into("INSERT INTO `rdi_field_mapping` (field_mapping_id,field_type,field_classification,entity_type,cart_field,invisible_field,default_value,allow_update,special_handling,notes) VALUES 
    ('3200','order_bill_to',NULL,NULL,'entity_id','0',NULL,'1',NULL,NULL), 
    ('3201','order_bill_to',NULL,NULL,'firstname','0',NULL,'1','upper',NULL), 
    ('3202','order_bill_to',NULL,NULL,'lastname','0',NULL,'1','upper',NULL), 
    ('3203','order_bill_to',NULL,NULL,'email','0',NULL,'1','upper',NULL), 
    ('3204','order_bill_to',NULL,NULL,'company','0',NULL,'1','upper',NULL), 
    ('3205','order_bill_to',NULL,NULL,'street','0',NULL,'1','upper',NULL), 
    ('3206','order_bill_to',NULL,NULL,'street2','0',NULL,'1','upper',NULL), 
    ('3207','order_bill_to',NULL,NULL,'city','0',NULL,'1',NULL,NULL), 
    ('3208','order_bill_to',NULL,NULL,'postcode','0',NULL,'1',NULL,NULL), 
    ('3209','order_bill_to',NULL,NULL,'region','0',NULL,'1','state_abv',NULL), 
    ('3210','order_bill_to',NULL,NULL,'telephone','0',NULL,'1',NULL,NULL), 
    ('3211','order_bill_to',NULL,NULL,'country_id','0',NULL,'1',NULL,NULL), 
    ('3212','order_bill_to',NULL,NULL,'tax_area','0',NULL,'1','upper',NULL);");

    //pos 
     $install->exec_insert_into("INSERT INTO `rdi_field_mapping_pos` (field_mapping_id,pos_field,alternative_field,field_order) VALUES 
    ('3201','customer_first_name',NULL,'0'), 
    ('3202','customer_last_name',NULL,'0'), 
    ('3203','customer_email',NULL,'0'), 
    ('3204','customer_company_name',NULL,'0'), 
    ('3205','customer_address1',NULL,'0'), 
    ('3206','customer_address2',NULL,'0'), 
    ('3207','customer_address3','append(\, )','0'), 
    ('3208','customer_zip',NULL,'0'), 
    ('3209','customer_address3','append( )','1'), 
    ('3210','customer_phone1',NULL,'0'), 
    ('3211','customer_country_name',NULL,'0'), 
    ('3212','tax_area_name',NULL,'0');");
	

    //END order_bill_to
    //-----------------------------------------------
    //-----------------------------------------------
    //order_ship_to 

     $install->exec_insert_into("INSERT INTO `rdi_field_mapping` (field_mapping_id,field_type,field_classification,entity_type,cart_field,invisible_field,default_value,allow_update,special_handling,notes) VALUES 
    ('3300','order_ship_to',NULL,NULL,'entity_id','0',NULL,'1',NULL,NULL), 
    ('3301','order_ship_to',NULL,NULL,'firstname','0',NULL,'1','upper',NULL), 
    ('3302','order_ship_to',NULL,NULL,'lastname','0',NULL,'1','upper',NULL), 
    ('3303','order_ship_to',NULL,NULL,'email','0',NULL,'1','upper',NULL), 
    ('3304','order_ship_to',NULL,NULL,'company','0',NULL,'1','upper',NULL), 
    ('3305','order_ship_to',NULL,NULL,'street','0',NULL,'1','upper',NULL), 
    ('3306','order_ship_to',NULL,NULL,'street2','0',NULL,'1','upper',NULL), 
    ('3307','order_ship_to',NULL,NULL,'city','0',NULL,'1',NULL,NULL), 
    ('3308','order_ship_to',NULL,NULL,'postcode','0',NULL,'1',NULL,NULL), 
    ('3309','order_ship_to',NULL,NULL,'region','0',NULL,'1','state_abv',NULL), 
    ('3310','order_ship_to',NULL,NULL,'telephone','0',NULL,'1',NULL,NULL), 
    ('3311','order_ship_to',NULL,NULL,'country_id','0',NULL,'1','upper',NULL);");

    //pos 
     $install->exec_insert_into("INSERT INTO `rdi_field_mapping_pos` (field_mapping_id,pos_field,alternative_field,field_order) VALUES 
    ('3301','shipto_customer_first_name',NULL,'0'), 
    ('3302','shipto_customer_last_name',NULL,'0'), 
    ('3303','shipto_customer_email',NULL,'0'), 
    ('3304','shipto_customer_company_name',NULL,'0'), 
    ('3305','shipto_customer_address1',NULL,'0'), 
    ('3306','shipto_customer_address2',NULL,'0'), 
    ('3307','shipto_customer_address3','append(\, )','0'), 
    ('3308','shipto_customer_zip',NULL,'0'), 
    ('3309','shipto_customer_address3','append( )','1'), 
    ('3310','shipto_customer_phone1',NULL,'0'), 
    ('3311','shipto_customer_country_name',NULL,'0');");


    //END order_ship_to
    //-----------------------------------------------
    //-----------------------------------------------
    //order_item 

     $install->exec_insert_into("INSERT INTO `rdi_field_mapping` (field_mapping_id,field_type,field_classification,entity_type,cart_field,invisible_field,default_value,allow_update,special_handling,notes) VALUES 
    ('3400','order_item',NULL,NULL,'item_id','0',NULL,'1',NULL,NULL), 
    ('3401','order_item',NULL,NULL,'sku','0',NULL,'1',NULL,NULL), 
    ('3402','order_item',NULL,NULL,'name','0',NULL,'1',NULL,NULL), 
    ('3403','order_item',NULL,NULL,'qty_ordered','0',NULL,'1',NULL,NULL), 
    ('3404','order_item',NULL,NULL,'base_price','0',NULL,'1',NULL,NULL), 
    ('3405','order_item',NULL,NULL,'tax_percent','0',NULL,'1',NULL,NULL), 
    ('3406','order_item',NULL,NULL,'base_tax_amount','0',NULL,'1',NULL,NULL), 
    ('3407','order_item',NULL,NULL,'base_cost','0',NULL,'1',NULL,NULL), 
    ('3408','order_item',NULL,NULL,'base_discount_amount','0',NULL,'1','subtract(base_price|base_discount_amount)',NULL), 
    ('3409','order_item',NULL,NULL,'increment_id','0',NULL,'1',NULL,NULL), 
    ('3410','order_item',NULL,NULL,'related_id','0',NULL,'1',NULL,NULL), 
    ('3411','order_item',NULL,'tax_code',NULL,'0','0','1',NULL,NULL), 
    ('3412','order_item',NULL,'tax_code2',NULL,'0','0','1',NULL,NULL), 
    ('3413','order_item',NULL,'tax_perc2',NULL,'0','0','1',NULL,NULL), 
    ('3414','order_item',NULL,'tax_amt2',NULL,'0','0','1',NULL,NULL), 
    ('3415','order_item',NULL,'sent_qty',NULL,'0','0','1',NULL,NULL), 
    ('3416','order_item',NULL,'price_lvl',NULL,'0','1','1',NULL,NULL), 
    ('3417','order_item',NULL,'comm_code',NULL,'0','-1','1',NULL,NULL), 
    ('3418','order_item',NULL,'kit_flag',NULL,'0','0','1',NULL,NULL), 
    ('3419','order_item',NULL,'detax',NULL,'0','0','1',NULL,NULL), 
    ('3420','order_item',NULL,'usr_disc_perc',NULL,'0','0','1',NULL,NULL), 
    ('3421','order_item',NULL,'activity_perc',NULL,'0','100','1',NULL,NULL), 
    ('3422','order_item',NULL,'promo_flag',NULL,'0','0','1',NULL,NULL);");

    //pos 
     $install->exec_insert_into("INSERT INTO `rdi_field_mapping_pos` (field_mapping_id,pos_field,alternative_field,field_order) VALUES 
    ('3400','item_pos',NULL,'0'), 
    ('3403','ord_qty',NULL,'0'), 
    ('3404','orig_price',NULL,'0'), 
    ('3405','tax_perc',NULL,'0'), 
    ('3406','orig_tax_amt',NULL,'0'), 
    ('3406','tax_amt',NULL,'0'), 
    ('3407','cost',NULL,'0'), 
    ('3408','price',NULL,'0'), 
    ('3409','orderid',NULL,'0'), 
    ('3410','item_sid',NULL,'0'), 
    ('3411','tax_code',NULL,'0'), 
    ('3412','tax_code2',NULL,'0'), 
    ('3413','tax_perc2',NULL,'0'), 
    ('3414','tax_amt2',NULL,'0'), 
    ('3415','sent_qty',NULL,'0'), 
    ('3416','price_lvl',NULL,'0'), 
    ('3417','comm_code',NULL,'0'), 
    ('3418','kit_flag',NULL,'0'), 
    ('3419','detax',NULL,'0'), 
    ('3420','usr_disc_perc',NULL,'0'), 
    ('3421','activity_perc',NULL,'0'), 
    ('3422','promo_flag',NULL,'0');");

    //END order_item
    //-----------------------------------------------
    //-----------------------------------------------
    //order_payment 
     
     $install->exec_insert_into("INSERT INTO `rdi_field_mapping` (field_mapping_id,field_type,field_classification,entity_type,cart_field,invisible_field,default_value,allow_update,special_handling,notes) VALUES 
    ('3500', 'order_payment', NULL, NULL, 'method', '0', '2', '1', 'if(ccsave|2),if(checkmo|2),if(authorizenet|2),if(paypal_express|2|2)', NULL),
('3501', 'order_payment', NULL, NULL, 'cc_owner', 0, NULL, '1', NULL, NULL),
('3502', 'order_payment', NULL, NULL, 'cc_type', 0, NULL, '1', NULL, NULL),
('3503', 'order_payment', NULL, 'currency_name', 0, 'DOLLARS', '2', '1', NULL, NULL);");
     
$install->exec_insert_into("INSERT INTO `rdi_field_mapping_pos` (field_mapping_id,pos_field,alternative_field,field_order) VALUES ('3500','tender_type',NULL,'0'),
('3501','cardholder_name',NULL,'0'),        
 ('3502','crd_name',NULL,'0'),              
 ('3503','currency_name',NULL,'0')  ;");
    //END order_payment
    //-----------------------------------------------
    //-----------------------------------------------
    //customer 

     $install->exec_insert_into("INSERT INTO `rdi_field_mapping` (field_mapping_id,field_type,field_classification,entity_type,cart_field,invisible_field,default_value,allow_update,special_handling,notes) VALUES 
    ('4000','customer',NULL,NULL,'entity_id','0',NULL,'1',NULL,NULL), 
    ('4001','customer',NULL,NULL,'entity_id','0',NULL,'1',NULL,NULL), 
    ('4002','customer',NULL,NULL,'firstname','0',NULL,'1','upper',NULL), 
    ('4003','customer',NULL,NULL,'lastname','0',NULL,'1','upper',NULL), 
    ('4004','customer',NULL,NULL,'email','0',NULL,'1','upper',NULL);");


    //END customer
    //-----------------------------------------------
    //-----------------------------------------------
    //customer_address 

     $install->exec_insert_into("INSERT INTO `rdi_field_mapping` (field_mapping_id,field_type,field_classification,entity_type,cart_field,invisible_field,default_value,allow_update,special_handling,notes) VALUES 
    ('4200','customer_address',NULL,NULL,'prefix','0',NULL,'1','upper',NULL), 
    ('4201','customer_address',NULL,NULL,'company','0',NULL,'1','upper',NULL), 
    ('4202','customer_address',NULL,NULL,'street','0',NULL,'1','upper',NULL), 
    ('4203','customer_address',NULL,NULL,'street2','0',NULL,'1','upper',NULL), 
    ('4204','customer_address',NULL,NULL,'city','0',NULL,'1','upper',NULL), 
    ('4205','customer_address',NULL,NULL,'country_id','0',NULL,'1','upper',NULL), 
    ('4206','customer_address',NULL,NULL,'region','0',NULL,'1','state_abv',NULL), 
    ('4207','customer_address',NULL,NULL,'postcode','0',NULL,'1',NULL,NULL), 
    ('4208','customer_address',NULL,NULL,'telephone','0',NULL,'1',NULL,NULL), 
    ('4209','customer_address',NULL,NULL,'fax','0',NULL,'1',NULL,NULL);");


    //END customer_address
    //-----------------------------------------------
    //-----------------------------------------------
    //so_status 

     $install->exec_insert_into("INSERT INTO `rdi_field_mapping` (field_mapping_id,field_type,field_classification,entity_type,cart_field,invisible_field,default_value,allow_update,special_handling,notes) VALUES 
    ('5000','so_status',NULL,NULL,'increment_id','0',NULL,'1',NULL,NULL), 
    ('5001','so_status',NULL,NULL,'rdi_cc_amount','0',NULL,'1',NULL,NULL), 
    ('5002','so_status',NULL,NULL,'receipt_shipping','0',NULL,'1',NULL,NULL), 
    ('5003','so_status',NULL,NULL,'receipt_tax','0',NULL,'1',NULL,NULL), 
    ('5004','so_status',NULL,NULL,'pos_status','0',NULL,'1',NULL,NULL);");

    //pos 
     $install->exec_insert_into("INSERT INTO `rdi_field_mapping_pos` (field_mapping_id,pos_field,alternative_field,field_order) VALUES 
    ('5000','rpro_in_so.so_number',NULL,'0'), 
    ('5001','rpro_in_so.tender_amt',NULL,'0'), 
    ('5003','rpro_in_so.tax_total',NULL,'0'), 
    ('5004','rpro_in_so.status',NULL,'0');");


    //END so_status
    //-----------------------------------------------
    //-----------------------------------------------
    //so_shipment 

     $install->exec_insert_into("INSERT INTO `rdi_field_mapping` (field_mapping_id,field_type,field_classification,entity_type,cart_field,invisible_field,default_value,allow_update,special_handling,notes) VALUES 
    ('5100','so_shipment',NULL,NULL,'carrier_code','0',NULL,'1',NULL,NULL), 
    ('5101','so_shipment',NULL,NULL,'tracking_number','0',NULL,'1',NULL,NULL), 
    ('5102','so_shipment',NULL,NULL,'shipment_date','0',NULL,'1',NULL,NULL), 
    ('5103','so_shipment',NULL,NULL,'increment_id','0',NULL,'1',NULL,NULL), 
    ('5104','so_shipment',NULL,NULL,'email_sent','0','0','1',NULL,NULL), 
    ('5105','so_shipment',NULL,NULL,'comment','0','','1',NULL,NULL), 
    ('5106','so_shipment',NULL,NULL,'carrier_title','0','UPS','1',NULL,NULL);");

    //pos 
     $install->exec_insert_into("INSERT INTO `rdi_field_mapping_pos` (field_mapping_id,pos_field,alternative_field,field_order) VALUES 
    ('5100','rpro_in_so.shipprovider',NULL,'0'), 
    ('5101','rpro_in_so.tracking_number',NULL,'0'), 
    ('5102','rpro_in_so.ship_date',NULL,'0'), 
    ('5103','rpro_in_so.sid',NULL,'0'), 
    ('5106','rpro_in_so.shipprovider',NULL,'0');");


    //END so_shipment
    //-----------------------------------------------
    //-----------------------------------------------
    //so_shipment_item 

     $install->exec_insert_into("INSERT INTO `rdi_field_mapping` (field_mapping_id,field_type,field_classification,entity_type,cart_field,invisible_field,default_value,allow_update,special_handling,notes) VALUES 
    ('5200','so_shipment_item',NULL,NULL,'qty','0',NULL,'1',NULL,NULL), 
    ('5201','so_shipment_item',NULL,NULL,'related_id','0',NULL,'1',NULL,NULL);");
    
     $install->exec_insert_into("INSERT INTO `rdi_field_mapping_pos` (field_mapping_id,pos_field,alternative_field,field_order) VALUES 
    ('5200','rpro_in_so.qty_shipped',NULL,0), 
    ('5201','rpro_in_so.item_sid',NULL,0);");
     
     
    //END so_shipment_item
    //-----------------------------------------------
    //-----------------------------------------------
    //creditmemo 
	$install->exec_insert_into("INSERT INTO `rdi_field_mapping`(`field_mapping_id`,`field_type`,`field_classification`,`entity_type`,`cart_field`,`invisible_field`,`default_value`,`allow_update`,`special_handling`,`notes`) VALUES ('6007','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'clerk_name4'),
('6008','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'clerk_sbs_no4'),
('6009','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'clerk_name3'),
('6010','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'clerk_sbs_no3'),
('6011','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'clerk_name2'),
('6012','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'clerk_sbs_no2'),
('6100','creditmemo','items',NULL,'creditmemo_item.sku','0',NULL,'1',NULL,NULL),
('6101','creditmemo','items',NULL,'creditmemo_item.related_id','0',NULL,'1',NULL,'order.related_id'),
('6102','creditmemo','items',NULL,'creditmemo_item.entity_id','0',NULL,'1',NULL,'item.item_id'),
('6200','creditmemo','billing',NULL,'billing.country_id','0',NULL,'1',NULL,'email'),
('6201','creditmemo','billing',NULL,'billing.email','0',NULL,'1',NULL,'firstname'),
('6300','creditmemo','shipping',NULL,'shipping.country_id','0',NULL,'1',NULL,'shipto_customer_country_name'),
('6301','creditmemo','shipping',NULL,'shipping.email','0',NULL,'1',NULL,'shipto_customer_email'),
('6103','creditmemo','items',NULL,'creditmemo_item_parent.base_price','0',NULL,'1','SQL[\$m/creditmemo_item.qty]','item.base_price'),
('6104','creditmemo','items',NULL,'creditmemo_item_parent.base_tax_amount','0',NULL,'1','SQL[\$m/creditmemo_item.qty]','item.base_tax_amount'),
('6105','creditmemo','items',NULL,'creditmemo_item_parent.base_price','0',NULL,'1','SQL[(\$m-creditmemo_item_parent.base_discount_amount)/creditmemo_item.qty]','item.base_price'),
('6106','creditmemo','items',NULL,'order_item_parent.base_cost','0',NULL,'1','SQL[\$m/creditmemo_item.qty]','item.cost'),
('6107','creditmemo','items',NULL,NULL,'0','0','1',NULL,'tax_code'),
('6108','creditmemo','items',NULL,NULL,'0','0.0000','1',NULL,'tax_perc'),
('6109','creditmemo','items',NULL,'creditmemo_item_parent.base_tax_amount','0',NULL,'1','SQL[\$m/creditmemo_item.qty]','tax_amt'),
('6110','creditmemo','items',NULL,NULL,'0','0','1',NULL,'tax_code2'),
('6111','creditmemo','items',NULL,NULL,'0','0.0000','1',NULL,'tax_perc2'),
('6112','creditmemo','items',NULL,NULL,'0','0.0000','1',NULL,'tax_amt2'),
('6113','creditmemo','items',NULL,'creditmemo_item_parent.qty','0',NULL,'1','SQL[(-1)*\$m]','ord_qty'),
('6114','creditmemo','items',NULL,'creditmemo_item_parent.qty','0',NULL,'1','SQL[(-1)*\$m]','sent_qty'),
('6115','creditmemo','items',NULL,NULL,'0','1','1',NULL,'price_lvl'),
('6116','creditmemo','items',NULL,NULL,'0','-1','1',NULL,'comm_code'),
('6117','creditmemo','items',NULL,NULL,'0','0','1',NULL,'kit_flag'),
('6118','creditmemo','items',NULL,NULL,'0','0','1',NULL,'detax'),
('6119','creditmemo','items',NULL,NULL,'0','0.0000','1',NULL,'usr_disc_perc'),
('6120','creditmemo','items',NULL,NULL,'0','100','1',NULL,'activity_perc'),
('6121','creditmemo','items',NULL,NULL,'0','0','1',NULL,'promo_flag'),
('6122','creditmemo','items',NULL,NULL,'0','0.0000','1',NULL,'alt_cost'),
('6123','creditmemo','items',NULL,NULL,'0','1','1',NULL,'empl_sbs_no'),
('6124','creditmemo','items',NULL,NULL,'0','WEB','1',NULL,'empl_name'),
('6125','creditmemo','items',NULL,NULL,'0',NULL,'1',NULL,'tax_area2_name'),
('6014','creditmemo','header',NULL,NULL,'0','1','1',NULL,'clerk_sbs_no'),
('6015','creditmemo','header',NULL,NULL,'0','SYSADMIN','1',NULL,'createdby_empl_name'),
('6016','creditmemo','header',NULL,NULL,'0','1','1',NULL,'createdby_sbs_no'),
('6017','creditmemo','header',NULL,NULL,'0','SYSADMIN','1',NULL,'modifiedby_empl_name'),
('6018','creditmemo','header',NULL,NULL,'0','1','1',NULL,'modifiedby_sbs_no'),
('6019','creditmemo','header',NULL,NULL,'0','0','1',NULL,'web_so_type'),
('6020','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'tax_area2_name'),
('6021','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'tax_area_name'),
('6022','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'ship_method'),
('6023','creditmemo','header',NULL,NULL,'0','WEB','1',NULL,'empl_name'),
('6024','creditmemo','header',NULL,NULL,'0','1','1',NULL,'empl_sbs_no'),
('6025','creditmemo','header',NULL,NULL,'0','0','1',NULL,'detax'),
('6026','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'activity_perc5'),
('6027','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'activity_perc4'),
('6028','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'activity_perc3'),
('6029','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'activity_perc2'),
('6030','creditmemo','header',NULL,NULL,'0','100','1',NULL,'activity_perc'),
('6031','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'used_tax'),
('6032','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'used_subtotal'),
('6033','creditmemo','header',NULL,NULL,'0','2','1',NULL,'line_pos_seq'),
('6034','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'elapsed_time'),
('6035','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'orig_controller'),
('6036','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'controller'),
('6037','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'doc_source'),
('6038','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'pkg_no'),
('6039','creditmemo','header',NULL,'creditmemo.created_at','0',NULL,'1','date','cms_post_date'),
('6040','creditmemo','header',NULL,NULL,'0','0','1',NULL,'held'),
('6041','creditmemo','header',NULL,NULL,'0','0','1',NULL,'verified'),
('6042','creditmemo','header',NULL,NULL,'0','1','1',NULL,'active'),
('6043','creditmemo','header',NULL,NULL,'0','1','1',NULL,'cms'),
('6044','creditmemo','header',NULL,'order.increment_id','0',NULL,'1','SQL[CONCAT(\$m,\'-CM\')]','ref_so_sid'),
('6045','creditmemo','header',NULL,'creditmemo.increment_id','0',NULL,'1',NULL,'note'),
('6046','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'cancel_date'),
('6047','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'shipping_date'),
('6048','creditmemo','header',NULL,'creditmemo.created_at','0',NULL,'1','date','modified_date'),
('6049','creditmemo','header',NULL,'creditmemo.created_at','0',NULL,'1','date','created_date'),
('6050','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'over_tax_perc2'),
('6051','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'over_tax_perc'),
('6052','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'used_disc_amt'),
('6053','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'disc_perc_spread'),
('6054','creditmemo','header',NULL,'0.0000','0','0.0000','1','SQL[0.0000]','disc_amt'),
('6055','creditmemo','header',NULL,NULL,'0','0.0000','1',NULL,'disc_perc'),
('6056','creditmemo','header',NULL,NULL,'0','0','1',NULL,'use_vat'),
('6057','creditmemo','header',NULL,NULL,'0','1','1',NULL,'priority'),
('6058','creditmemo','header',NULL,NULL,'0','2','1',NULL,'status'),
('6059','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'cust_po_no'),
('6060','creditmemo','header',NULL,NULL,'0','1','1',NULL,'shipto_addr_no'),
('6061','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'shipto_cust_sid'),
('6062','creditmemo','header',NULL,NULL,'0','1','1',NULL,'addr_no'),
('6063','creditmemo','header',NULL,NULL,'0',NULL,'1',NULL,'cust_sid'),
('6064','creditmemo','header',NULL,NULL,'0','1','1',NULL,'trgt_station'),
('6065','creditmemo','header',NULL,NULL,'0','1','1',NULL,'trgt_store_no'),
('6066','creditmemo','header',NULL,NULL,'0','1','1',NULL,'orig_station'),
('6067','creditmemo','header',NULL,NULL,'0','5','1',NULL,'orig_store_no'),
('6068','creditmemo','header',NULL,NULL,'0','2','1',NULL,'so_type'),
('6069','creditmemo','header',NULL,NULL,'0','1','1',NULL,'station'),
('6070','creditmemo','header',NULL,NULL,'0','1','1',NULL,'store_no'),
('6071','creditmemo','header',NULL,NULL,'0','1','1',NULL,'sbs_no'),
('6072','creditmemo','header',NULL,'creditmemo.increment_id','0',NULL,'1','SQL[CONCAT(\$m,\'-CM\')]','so_sid'),
('6202','creditmemo','billing',NULL,NULL,'0',NULL,'1',NULL,'customer_phone2'),
('6203','creditmemo','billing',NULL,'billing.telephone','0',NULL,'1',NULL,'customer_phone1'),
('6204','creditmemo','billing',NULL,'billing.postcode','0',NULL,'1',NULL,'customer_zip'),
('6205','creditmemo','billing',NULL,NULL,'0',NULL,'1',NULL,'customer_address6'),
('6206','creditmemo','billing',NULL,NULL,'0',NULL,'1',NULL,'customer_address5'),
('6207','creditmemo','billing',NULL,NULL,'0',NULL,'1',NULL,'customer_address4'),
('6208','creditmemo','billing',NULL,'CONCAT(billing.city,\', \',billing.region)','0',NULL,'1',NULL,'customer_address3'),
('6209','creditmemo','billing',NULL,NULL,'0',NULL,'1',NULL,'customer_address2'),
('6210','creditmemo','billing',NULL,'billing.street','0',NULL,'1',NULL,'customer_address1'),
('6211','creditmemo','billing',NULL,NULL,'0','0','1',NULL,'customer_shipping'),
('6212','creditmemo','billing',NULL,NULL,'0',NULL,'1',NULL,'customer_tax_area_name'),
('6213','creditmemo','billing',NULL,'billing.prefix','0',NULL,'1',NULL,'customer_title'),
('6214','creditmemo','billing',NULL,'billing.company','0',NULL,'1',NULL,'customer_company_name'),
('6215','creditmemo','billing',NULL,NULL,'0','1','1',NULL,'customer_cms'),
('6216','creditmemo','billing',NULL,NULL,'0','1','1',NULL,'customer_sbs_no'),
('6217','creditmemo','billing',NULL,NULL,'0',NULL,'1',NULL,'customer_modified_date'),
('6218','creditmemo','billing',NULL,NULL,'0',NULL,'1',NULL,'customer_info2'),
('6219','creditmemo','billing',NULL,NULL,'0',NULL,'1',NULL,'customer_info1'),
('6220','creditmemo','billing',NULL,NULL,'0','0','1',NULL,'customer_detax'),
('6221','creditmemo','billing',NULL,NULL,'0',NULL,'1',NULL,'customer_price_lvl'),
('6222','creditmemo','billing',NULL,'billing.lastname','0',NULL,'1',NULL,'customer_last_name'),
('6223','creditmemo','billing',NULL,'billing.firstname','0',NULL,'1',NULL,'customer_first_name'),
('6224','creditmemo','billing',NULL,NULL,'0',NULL,'1',NULL,'customer_station'),
('6225','creditmemo','billing',NULL,NULL,'0','1','1',NULL,'customer_store_no'),
('6226','creditmemo','billing',NULL,'billing.customer_id','0',NULL,'1',NULL,'customer_cust_id'),
('6227','creditmemo','billing',NULL,NULL,'0',NULL,'1','customer_related_id','customer_cust_sid'),
('6302','creditmemo','shipping',NULL,NULL,'0',NULL,'1',NULL,'shipto_customer_phone2'),
('6303','creditmemo','shipping',NULL,'shipping.telephone','0',NULL,'1',NULL,'shipto_customer_phone1'),
('6304','creditmemo','shipping',NULL,'shipping.postcode','0',NULL,'1',NULL,'shipto_customer_zip'),
('6305','creditmemo','shipping',NULL,NULL,'0',NULL,'1',NULL,'shipto_customer_address6'),
('6306','creditmemo','shipping',NULL,NULL,'0',NULL,'1',NULL,'shipto_customer_address5'),
('6307','creditmemo','shipping',NULL,NULL,'0',NULL,'1',NULL,'shipto_customer_address4'),
('6308','creditmemo','shipping',NULL,'CONCAT(shipping.city,\', \',shipping.region)','0',NULL,'1',NULL,'shipto_customer_address3'),
('6309','creditmemo','shipping',NULL,NULL,'0',NULL,'1',NULL,'shipto_customer_address2'),
('6310','creditmemo','shipping',NULL,'shipping.street','0',NULL,'1',NULL,'shipto_customer_address1'),
('6311','creditmemo','shipping',NULL,NULL,'0','0','1',NULL,'shipto_customer_shipping'),
('6312','creditmemo','shipping',NULL,NULL,'0',NULL,'1',NULL,'shipto_customer_tax_area_name'),
('6313','creditmemo','shipping',NULL,'shipping.prefix','0',NULL,'1',NULL,'shipto_customer_title'),
('6314','creditmemo','shipping',NULL,'shipping.company','0',NULL,'1',NULL,'shipto_customer_company_name'),
('6315','creditmemo','shipping',NULL,NULL,'0','1','1',NULL,'shipto_customer_cms'),
('6316','creditmemo','shipping',NULL,NULL,'0','1','1',NULL,'shipto_customer_sbs_no'),
('6317','creditmemo','shipping',NULL,NULL,'0',NULL,'1',NULL,'shipto_customer_modified_date'),
('6318','creditmemo','shipping',NULL,NULL,'0',NULL,'1',NULL,'shipto_customer_info2'),
('6319','creditmemo','shipping',NULL,NULL,'0',NULL,'1',NULL,'shipto_customer_info1'),
('6320','creditmemo','shipping',NULL,NULL,'0','0','1',NULL,'shipto_customer_detax'),
('6321','creditmemo','shipping',NULL,NULL,'0',NULL,'1',NULL,'shipto_customer_price_lvl'),
('6322','creditmemo','shipping',NULL,'shipping.firstname','0',NULL,'1',NULL,'shipto_customer_last_name'),
('6323','creditmemo','shipping',NULL,'shipping.lastname','0',NULL,'1',NULL,'shipto_customer_first_name'),
('6324','creditmemo','shipping',NULL,NULL,'0',NULL,'1',NULL,'shipto_customer_station'),
('6325','creditmemo','shipping',NULL,NULL,'0','1','1',NULL,'shipto_customer_store_no'),
('6326','creditmemo','shipping',NULL,NULL,'0',NULL,'1',NULL,'shipto_customer_cust_id'),
('6327','creditmemo','shipping',NULL,NULL,'0',NULL,'1',NULL,'shipto_customer_cust_sid'),
('6073','creditmemo','header',NULL,'order.increment_id','0',NULL,'1','SQL[concat(\$m,\'-CM\')]','comment1'),
('6074','creditmemo','header',NULL,'creditmemo.increment_id','0',NULL,'1','SQL[CONCAT(\$m,\'-CM\')]','so_no'),
('6075','creditmemo','header',NULL,'creditmemo.base_grand_total','0',NULL,'1','SQL[\$m]','given'),
('6076','creditmemo','header',NULL,'creditmemo.base_grand_total','0',NULL,'1','SQL[\$m*(-1)]','amt'),
('9077','creditmemo','header',NULL,NULL,'0',NULL,'1','SQL[\'RETURN\']','pos_field_1');
");
     
     
     $install->exec_insert_into("INSERT INTO `rdi_field_mapping_pos`(`field_mapping_id`,`pos_field`,`alternative_field`,`field_order`) values ('6205','customer_address6',NULL,'0'),
('6204','customer_zip',NULL,'0'),
('6203','customer_phone1',NULL,'0'),
('6202','customer_phone2',NULL,'0'),
('6201','customer_email',NULL,'0'),
('6200','customer_country_name',NULL,'0'),
('6124','empl_name',NULL,'0'),
('6125','tax_area2_name',NULL,'0'),
('6073','comment1',NULL,'0'),
('6123','empl_sbs_no',NULL,'0'),
('6122','alt_cost',NULL,'0'),
('6121','promo_flag',NULL,'0'),
('6120','activity_perc',NULL,'0'),
('6119','usr_disc_perc',NULL,'0'),
('6118','detax',NULL,'0'),
('6117','kit_flag',NULL,'0'),
('6116','comm_code',NULL,'0'),
('6115','price_lvl',NULL,'0'),
('6114','sent_qty','creditmemo_item.qty','0'),
('6113','ord_qty','creditmemo_item.qty','0'),
('6112','tax_amt2',NULL,'0'),
('6111','tax_perc2',NULL,'0'),
('6110','tax_code2',NULL,'0'),
('6109','tax_amt',NULL,'0'),
('6108','tax_perc',NULL,'0'),
('6107','tax_code',NULL,'0'),
('6106','cost','creditmemo_item.base_price','0'),
('6105','price','creditmemo_item.base_price','0'),
('6104','orig_tax_amt','creditmemo_item_parent.base_tax_amount','0'),
('6103','orig_price','creditmemo_item.base_price','0'),
('6102','item_pos',NULL,'0'),
('6101','item_sid',NULL,'0'),
('6000','so_no',NULL,'0'),
('6001','tracking_no',NULL,'0'),
('6002','shipping_tax',NULL,'0'),
('6003','shipping_amt',NULL,'0'),
('6004','customer_shipping',NULL,'0'),
('6005','clerk_name5',NULL,'0'),
('6006','clerk_sbs_no5',NULL,'0'),
('6007','clerk_name4',NULL,'0'),
('6008','clerk_sbs_no4',NULL,'0'),
('6009','clerk_name3',NULL,'0'),
('6010','clerk_sbs_no3',NULL,'0'),
('6011','clerk_name2',NULL,'0'),
('6012','clerk_sbs_no2',NULL,'0'),
('6013','clerk_name',NULL,'0'),
('6014','clerk_sbs_no',NULL,'0'),
('6015','createdby_empl_name',NULL,'0'),
('6016','createdby_sbs_no',NULL,'0'),
('6017','modifiedby_empl_name',NULL,'0'),
('6018','modifiedby_sbs_no',NULL,'0'),
('6019','web_so_type',NULL,'0'),
('6020','tax_area2_name',NULL,'0'),
('6021','tax_area_name',NULL,'0'),
('6022','ship_method',NULL,'0'),
('6023','empl_name',NULL,'0'),
('6024','empl_sbs_no',NULL,'0'),
('6025','detax',NULL,'0'),
('6026','activity_perc5',NULL,'0'),
('6027','activity_perc4',NULL,'0'),
('6028','activity_perc3',NULL,'0'),
('6029','activity_perc2',NULL,'0'),
('6030','activity_perc',NULL,'0'),
('6031','used_tax',NULL,'0'),
('6032','used_subtotal',NULL,'0'),
('6033','line_pos_seq',NULL,'0'),
('6034','elapsed_time',NULL,'0'),
('6035','orig_controller',NULL,'0'),
('6036','controller',NULL,'0'),
('6037','doc_source',NULL,'0'),
('6038','pkg_no',NULL,'0'),
('6039','cms_post_date',NULL,'0'),
('6040','held',NULL,'0'),
('6041','verified',NULL,'0'),
('6042','active',NULL,'0'),
('6043','cms',NULL,'0'),
('6044','ref_so_sid',NULL,'0'),
('6045','note',NULL,'0'),
('6046','cancel_date',NULL,'0'),
('6047','shipping_date',NULL,'0'),
('6048','modified_date',NULL,'0'),
('6049','created_date',NULL,'0'),
('6050','over_tax_perc2',NULL,'0'),
('6051','over_tax_perc',NULL,'0'),
('6052','used_disc_amt',NULL,'0'),
('6053','disc_perc_spread',NULL,'0'),
('6054','disc_amt',NULL,'0'),
('6055','disc_perc',NULL,'0'),
('6056','use_vat',NULL,'0'),
('6057','priority',NULL,'0'),
('6058','status',NULL,'0'),
('6059','cust_po_no',NULL,'0'),
('6060','shipto_addr_no',NULL,'0'),
('6061','shipto_cust_sid',NULL,'0'),
('6062','addr_no',NULL,'0'),
('6063','cust_sid',NULL,'0'),
('6064','trgt_station',NULL,'0'),
('6065','trgt_store_no',NULL,'0'),
('6066','orig_station',NULL,'0'),
('6067','orig_store_no',NULL,'0'),
('6068','so_type',NULL,'0'),
('6069','station',NULL,'0'),
('6070','store_no',NULL,'0'),
('6071','sbs_no',NULL,'0'),
('6072','so_sid',NULL,'0'),
('6207','customer_address4',NULL,'0'),
('6208','customer_address3',NULL,'0'),
('6209','customer_address2',NULL,'0'),
('6210','customer_address1',NULL,'0'),
('6211','customer_shipping',NULL,'0'),
('6212','customer_tax_area_name',NULL,'0'),
('6213','customer_title',NULL,'0'),
('6214','customer_company_name',NULL,'0'),
('6215','customer_cms',NULL,'0'),
('6216','customer_sbs_no',NULL,'0'),
('6217','customer_modified_date',NULL,'0'),
('6218','customer_info2',NULL,'0'),
('6219','customer_info1',NULL,'0'),
('6220','customer_detax',NULL,'0'),
('6221','customer_price_lvl',NULL,'0'),
('6222','customer_last_name',NULL,'0'),
('6223','customer_first_name',NULL,'0'),
('6224','customer_station',NULL,'0'),
('6225','customer_store_no',NULL,'0'),
('6226','customer_cust_id',NULL,'0'),
('6227','customer_cust_sid',NULL,'0'),
('6300','shipto_customer_country_name',NULL,'0'),
('6301','shipto_customer_email',NULL,'0'),
('6302','shipto_customer_phone2',NULL,'0'),
('6303','shipto_customer_phone1',NULL,'0'),
('6304','shipto_customer_zip',NULL,'0'),
('6305','shipto_customer_address6',NULL,'0'),
('6306','shipto_customer_address5',NULL,'0'),
('6307','shipto_customer_address4',NULL,'0'),
('6308','shipto_customer_address3',NULL,'0'),
('6309','shipto_customer_address2',NULL,'0'),
('6310','shipto_customer_address1',NULL,'0'),
('6311','shipto_customer_shipping',NULL,'0'),
('6312','shipto_customer_tax_area_name',NULL,'0'),
('6313','shipto_customer_title',NULL,'0'),
('6314','shipto_customer_company_name',NULL,'0'),
('6315','shipto_customer_cms',NULL,'0'),
('6316','shipto_customer_sbs_no',NULL,'0'),
('6317','shipto_customer_modified_date',NULL,'0'),
('6318','shipto_customer_info2',NULL,'0'),
('6319','shipto_customer_info1',NULL,'0'),
('6320','shipto_customer_detax',NULL,'0'),
('6321','shipto_customer_price_lvl',NULL,'0'),
('6322','shipto_customer_last_name',NULL,'0'),
('6323','shipto_customer_first_name',NULL,'0'),
('6324','shipto_customer_station',NULL,'0'),
('6325','shipto_customer_store_no',NULL,'0'),
('6326','shipto_customer_cust_id',NULL,'0'),
('6327','shipto_customer_cust_sid',NULL,'0'),
('6074','so_no',NULL,'0'),
('6075','given',NULL,'0'),
('6076','amt',NULL,'0'),
('6077','pos_flag_1',NULL,'0');
");
     
    //-----------------------------------------------

    $root_category_id = $db->cell("SELECT DISTINCT root_category_id FROM core_website w
                                                JOIN core_store s
                                                ON s.website_id = w.website_id
                                                JOIN core_store_group sg
                                                ON sg.group_id = s.group_id
                                                WHERE w.website_id = 1", 'root_category_id');
    
    if(is_null($root_category_id) || !is_int($root_category_id))
    {
        echo "Set the root_category_id, couldnt find one in magento. Its the ID of the category root. They may have multiples created.";
        $root_category_id = 2;
    }
    
        
    $install->exec_insert_into("INSERT INTO `rdi_settings`(`setting_id`,`setting`,`value`,`group`,`help`,`cart_lib`,`pos_lib`) values 
    (1,'pos_type','rpro9','','',NULL,NULL),
    (20,'rdi_protect_wait_time','0','','',NULL,NULL),
    (21,'rdi_upload_protect','0','','',NULL,NULL),
    (100,'load_products','0','Core','Set to 1 to load products , 0 to skip',NULL,NULL),
    (101,'load_categories','0','Core','Set to 1 to load categories, 0 to skip',NULL,NULL),
    (102,'load_customers','0','Core','Set to 1 to load customers, 0 to skip',NULL,NULL),
    (103,'load_image','0','Core','Set to 1 to load images, 0 to skip',NULL,NULL),
    (104,'load_so_status','0','Core','Set to 1 to load so status 0 to skip',NULL,NULL),
    (104,'load_returns','0','Core','Set to 1 to load so status 0 to skip',NULL,NULL),
    (104,'load_return','0','Core','Set to 1 to load so might be load_returns 0 to skip',NULL,NULL),
    (107,'scale_key','0','Rpro8','0 - name, 1 - index, The key the scales will be keying off for a match , RPRO8',NULL,NULL),
    (108,'load_price_variance','0','','set the price variances of the products based on the difference in price of the configurable\'s price',NULL,NULL),
    (109,'load_upsell_item','0','','Update related items. Uses product_link_type for the type of linking.',NULL,NULL),
    (111,'load_multistore','0','','',NULL,NULL),
    (112,'load_priceqty','0','','',NULL,NULL),

    (200,'insert_products','1','','Perform product inserts, aka new products',NULL,NULL),
    (201,'insert_categories','1','','Insert New Categories',NULL,NULL),
    (205,'insert_upsell_item','1','','insert upsell products',NULL,NULL),
    ('210','insert_image','0','load_image','insert upsell products',NULL,NULL),
    ('211','insert_product_image','0','load_image','insert upsell products',NULL,NULL),
    ('212','insert_color_image','0','load_image','insert upsell products',NULL,NULL),
    ('213','insert_swatch_image','0','load_image','insert upsell products',NULL,NULL),
    (206,'use_single_product_criteria','0','product','USE the cart_class_mapping_id of the class that you want to allow creation of just simples. Usually 4.',NULL,NULL),(202,'insert_customers','0','',NULL,NULL,NULL),

    (300,'update_products',     '1','','Product Updates',NULL,NULL),
    (301,'update_categories',   '1','','Update categories ',NULL,NULL),
    (302,'update_customers',    '1','',NULL,NULL,NULL),
    (305,'update_upsell_item',       '1','','update upsell products',NULL,NULL),
    (306,'update_availability', '1','','Update of the stock availability, in stock / out of stock',NULL,NULL),
    (306,'update_image', '0','','Removes images if they arent in the staging',NULL,NULL),
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
    (318, 'root_category_id', '{$root_category_id}', '', 'root category to start building categories from the POS', NULL, NULL),
    (319,'product_require_image','0','product','disables products that do not have an image. Happens in the catalog load.',NULL,NULL),
    (320,'category_image_attribute_destination','image','category','image/thumbnail_image',NULL,NULL),
    
    ('350','update_special_price_dates_priceqty','0','priceqty','',NULL,NULL),
    ('351','update_priceqty','0','priceqty','',NULL,NULL),
    ('352','update_inventory_priceqty','0','priceqty','',NULL,NULL),
    ('353','update_prices_priceqty','0','priceqty','',NULL,NULL),

    (400,'export_orders','1','Core','Set to 1 to export orders 0 to skip',NULL,NULL),
    (401,'export_customers','1','Core','set to 1 to export customers 0 to skip',NULL,NULL),
    (410,'order_export_status','pending','Core','The order status that triggers and order download',NULL,NULL),
    (411,'use_card_type_mapping','1','Core','use the mapping set in the rdi_card_type_mapping table',NULL,NULL),
    (412,'default_card_type','VISA','Core',NULL,NULL,NULL),
    (418, 'default_shipping_method', 'Ground', 'Order', 'If mapping doesnt find a match, then this will be the shipping method.', NULL, NULL),
    (413, 'pos_so_type', '0', 'Order-Not used', '0-Customer Order, 1-,2-,3-,4-,5-WEB. The WEB so_type does not function the same as the customer order type in Retail Pro. Copying SOs is not possible and the committed quantities are lost from the interface. WEB is not recommend.', NULL, 'V9'),
    (425, 'allow_no_tracking_number', '0', 'Order', '[1-Capture&Complete,0-OFF, 2-Capture Only] Capture even if they dont have a tracking number and go to complete.', 'magento', 'V8(mostly)'),
    (425, 'tracking_to_shipping_method', '0', 'Order', '[1-Capture&Complete,0-OFF, 2-Capture Only] Capture even if they dont have a tracking number and go to complete.', 'magento', 'V8(mostly)'),


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
    (814,'cleanXML','1','','Strip out characters that POSes hate',NULL,NULL),
	(815,'clean_old_categories','0','','Delete old categories',NULL,NULL),
	
	
	
    (903,'ship_all','0','','[0-OFF, 1-ON] Turn off to create a shipment for each invoice. Otherwise, create a new shipment with the first tracking number',NULL,NULL),
    (904,'disable_out_of_stock','1','',NULL,NULL,NULL),
    (905,'product_link_type','relation,up_sell','','the type of link we use for the link, as dictated in the catalog_product_link_type table',NULL,NULL),
    (912,'deactivated_delete_time','999','',NULL,NULL,NULL),
    (913,'order_prefix','00010','','This is the prefix of the SO number from Retail Pro',NULL,NULL),
    (914,'simple_url_key_format','{name}-{color}-{size}-{sku}','','the format used for standard simples for thier url key, this is to keep them unique and different from configurable\r\nnot using mapping since mapping cant determine if a size is null not to use it, it would clear out the entire url key\r\nand there can be multiple different permutations\r\nname_size_attr\r\nname_attr\r\nname_size\r\n\r\nand mapping only allows 2 possibilities\r\n\r\nset the stand alone simples and the configurables from the mapping\r\n\r\nusage\r\nwrap the field names in {}\r\n\r\nie\r\n{name}_{size}_{color}',NULL,NULL),
    (915,'configurable_url_key_format','{name}-{sku}','',NULL,NULL,NULL),
    (916,'spread_shipping_tax','0','',NULL,NULL,NULL),
    (917,'avail_stock_update','1',' ',' Lower stock from available records',NULL,NULL)");


    $install->exec_insert_into("INSERT INTO `rpro_mage_shipping`(`id`,`rpro_provider_id`,`rpro_method_id`,`shipper`,`ship_code`,`ship_description`) values (1,NULL,NULL,'ups','1DM','Next Day Air Early AM'),(2,NULL,NULL,'ups','1DML','Next Day Air Early AM Letter'),(3,NULL,NULL,'ups','1DA','Next Day Air'),(4,NULL,NULL,'ups','1DAL','Next Day Air Letter'),(5,NULL,NULL,'ups','1DAPI','Next Day Air Intra (Puerto Rico)'),(6,NULL,NULL,'ups','1DP','Next Day Air Saver'),(7,NULL,NULL,'ups','1DPL','Next Day Air Saver Letter'),(8,NULL,NULL,'ups','2DM','2nd Day Air AM'),(9,NULL,NULL,'ups','2DML','2nd Day Air AM Letter'),(10,NULL,NULL,'ups','2DA','2nd Day Air'),(11,NULL,NULL,'ups','2DAL','2nd Day Air Letter'),(12,NULL,NULL,'ups','3DS','3 Day Select'),(13,NULL,NULL,'ups','GND','Ground'),(14,NULL,NULL,'ups','GNDCOM','Ground Commercial'),(15,NULL,NULL,'ups','GNDRES','Ground Residential'),(16,NULL,NULL,'ups','STD','Canada Standard'),(17,NULL,NULL,'ups','XPR','Worldwide Express'),(18,NULL,NULL,'ups','WXS','Worldwide Express Saver'),(19,NULL,NULL,'ups','XPRL','Worldwide Express Letter'),(20,NULL,NULL,'ups','XDM','Worldwide Express Plus'),(21,NULL,NULL,'ups','XDML','Worldwide Express Plus Letter'),(22,NULL,NULL,'ups','XPD','Worldwide Expedited'),(23,NULL,NULL,'fedex','PRIORITYOVERNIGHT','Priority Overnight'),(24,NULL,NULL,'fedex','STANDARDOVERNIGHT','Standard Overnight'),(25,NULL,NULL,'fedex','FIRSTOVERNIGHT','First Overnight'),(26,NULL,NULL,'fedex','FEDEX2DAY','2Day'),(27,NULL,NULL,'fedex','FEDEXEXPRESSSAVER','Express Saver'),(28,NULL,NULL,'fedex','INTERNATIONALPRIORITY','International Priority'),(29,NULL,NULL,'fedex','INTERNATIONALECONOMY','International Economy'),(30,NULL,NULL,'fedex','INTERNATIONALFIRST','International First'),(31,NULL,NULL,'fedex','FEDEX1DAYFREIGHT','1 Day Freight'),(32,NULL,NULL,'fedex','FEDEX2DAYFREIGHT','2 Day Freight'),(33,NULL,NULL,'fedex','FEDEX3DAYFREIGHT','3 Day Freight'),(34,NULL,NULL,'fedex','FEDEXGROUND','Ground'),(35,NULL,NULL,'fedex','GROUNDHOMEDELIVERY','Home Delivery'),(36,NULL,NULL,'fedex','INTERNATIONALPRIORITY FREIGHT',' Intl Priority Freight'),(37,NULL,NULL,'fedex','INTERNATIONALECONOMY FREIGHT',' Intl Economy Freight'),(38,NULL,NULL,'fedex','EUROPEFIRSTINTERNATIONALPRIORITY','Europe First Priority'),(39,NULL,NULL,'dhl','IE','International Express'),(40,NULL,NULL,'dhl','E SAT','Express Saturday'),(41,NULL,NULL,'dhl','E 10:30AM','Express 10:30 AM'),(42,NULL,NULL,'dhl','E','Express'),(43,NULL,NULL,'dhl','N','Next Afternoon'),(44,NULL,NULL,'dhl','S','Second Day Service'),(45,NULL,NULL,'dhl','G','Ground'),(46,NULL,NULL,'usps','Bound Printed Matter','Bound Printed Matter'),(47,NULL,NULL,'usps','Express Mail','Express Mail'),(48,NULL,NULL,'usps','Express Mail Flat Rate Envelope','Express Mail Flat Rate Envelope'),(49,NULL,NULL,'usps','Express Mail Flat Rate Envelope Hold For Pickup','Express Mail Flat Rate Envelope Hold For Pickup'),(50,NULL,NULL,'usps','Express Mail Flat-Rate Envelope Sunday/Holiday Guarantee','Express Mail Flat-Rate Envelope Sunday/Holiday Guarantee'),(51,NULL,NULL,'usps','Express Mail Hold For Pickup','Express Mail Hold For Pickup'),(52,NULL,NULL,'usps','Express Mail International','Express Mail International'),(53,NULL,NULL,'usps','Express Mail International Flat Rate Envelope','Express Mail International Flat Rate Envelope'),(54,NULL,NULL,'usps','Express Mail PO to PO','Express Mail PO to PO'),(55,NULL,NULL,'usps','Express Mail Sunday/Holiday Guarantee','Express Mail Sunday/Holiday Guarantee'),(56,NULL,NULL,'usps','First-Class Mail International Large Envelope','First-Class Mail International Large Envelope'),(57,NULL,NULL,'usps','First-Class Mail International Letters','First-Class Mail International Letters'),(58,NULL,NULL,'usps','First-Class Mail International Package','First-Class Mail International Package'),(59,NULL,NULL,'usps','First-Class','First-Class'),(60,NULL,NULL,'usps','First-Class Mail','First-Class Mail'),(61,NULL,NULL,'usps','First-Class Mail Flat','First-Class Mail Flat'),(62,NULL,NULL,'usps','First-Class Mail International','First-Class Mail International'),(63,NULL,NULL,'usps','First-Class Mail Letter','First-Class Mail Letter'),(64,NULL,NULL,'usps','First-Class Mail Parcel','First-Class Mail Parcel'),(65,NULL,NULL,'usps','Global Express Guaranteed (GXG)','Global Express Guaranteed (GXG)'),(66,NULL,NULL,'usps','Global Express Guaranteed Non-Document Non-Rectangular','Global Express Guaranteed Non-Document Non-Rectangular'),(67,NULL,NULL,'usps','Global Express Guaranteed Non-Document Rectangular','Global Express Guaranteed Non-Document Rectangular'),(68,NULL,NULL,'usps','Library Mail','Library Mail'),(69,NULL,NULL,'usps','Media Mail','Media Mail'),(70,NULL,NULL,'usps','Parcel Post','Parcel Post'),(71,NULL,NULL,'usps','Priority Mail','Priority Mail'),(72,NULL,NULL,'usps','Priority Mail Small Flat Rate Box','Priority Mail Small Flat Rate Box'),(73,NULL,NULL,'usps','Priority Mail Medium Flat Rate Box','Priority Mail Medium Flat Rate Box'),(74,NULL,NULL,'usps','Priority Mail Large Flat Rate Box','Priority Mail Large Flat Rate Box'),(75,NULL,NULL,'usps','Priority Mail Flat Rate Box','Priority Mail Flat Rate Box'),(76,NULL,NULL,'usps','Priority Mail Flat Rate Envelope','Priority Mail Flat Rate Envelope'),(77,NULL,NULL,'usps','Priority Mail International','Priority Mail International'),(78,NULL,NULL,'usps','Priority Mail International Flat Rate Box','Priority Mail International Flat Rate Box'),(79,NULL,NULL,'usps','Priority Mail International Flat Rate Envelope','Priority Mail International Flat Rate Envelope'),(80,NULL,NULL,'usps','Priority Mail International Small Flat Rate Box','Priority Mail International Small Flat Rate Box'),(81,NULL,NULL,'usps','Priority Mail International Medium Flat Rate Box','Priority Mail International Medium Flat Rate Box'),(82,NULL,NULL,'usps','Priority Mail International Large Flat Rate Box','Priority Mail International Large Flat Rate Box'),(83,NULL,NULL,'usps','USPS GXG Envelopes','USPS GXG Envelopes'),(84,1,1,'ups','03','UPS Ground'),(85,1,2,'ups','12','UPS Three-Day Select'),(86,1,3,'ups','02','UPS Second Day Air'),(87,1,4,'ups','01','UPS Next Day Air')");
    
    $install->start_test_install_tables('rpro9');
}//END action tables

if ($action == 'alterMagentoTables')
{
    $install->alterTables();
}//end alter tables


if ($action == 'installAttributes')
{
    $install->installAttributes();
}//END add attributes.

include 'init.php';

global $cart, $db_lib;


if ($action == 'rproCategoryCount')
{
    $count = $db_lib->get_category_count();
    echo "<br>Categories:  {$count}";
}

if ($action == 'rproStyleCount')
{
    $count = $db_lib->get_product_count();
    echo "<br>Styles:  {$count}";
}

$install->echo_message("Finished Install Request");

?>

