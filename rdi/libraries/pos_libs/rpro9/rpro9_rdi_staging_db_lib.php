<?php

/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Retail Pro 9 Staging table database functions
 *
 * 
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\StagingDB\RPro9
 */
class rdi_staging_db_lib extends rdi_general {

    /**
     *  Translation of table type to table name
     * @var array 
     */
    static public $tables = array(
        "config" => "rpro_config",
        "in_catalog" => "rpro_in_categories",
        "in_catalog_product" => "rpro_in_category_products",
        "in_catalog_log" => "rpro_in_catalog_log",
        "in_customers" => "rpro_in_customers",
        "in_customers_log" => "rpro_in_customers_log",
        "in_gift_reg" => "rpro_in_gift_reg",
        "in_gift_reg_log" => "rpro_in_gift_reg_log",
        "in_images" => "rpro_in_images",
        "in_images_log" => "rpro_in_images_log",
        "in_receipts" => "rpro_in_receipts",
        "in_receipts_log" => "rpro_in_receipts_log",
        "in_so" => "rpro_in_so",
        "in_return" => "rpro_in_return",
        "in_refurn_log" => "rpro_in_return_log",
        "in_so_log" => "rpro_in_so_log",
        "in_styles" => "rpro_in_styles",
        "in_styles_log" => "rpro_in_styles_log",
        "in_items" => "rpro_in_items",
        "in_upsell_item" => "rpro_in_upsell_item",
        "in_upsell_item_log" => "rpro_in_upsell_item_log",
        "in_priceqty" => "rpro_in_priceqty",
        "in_priceqty_log" => "rpro_in_priceqty_log",
        "in_store" => "rpro_in_store",
        "in_multistore" => "rpro_in_store_qty",
        "item_image" => "",
        'in_giftcards' => '',
        'in_product_images' => '',
        'in_store' => 'rpro_in_store',
        'in_store_qty' => 'rpro_in_store_qty',
        'in_prefs' => '',
        'rdi_color_size_codes' => 'rdi_color_size_codes',
        "out_customers" => "rpro_out_customers",
        "out_customers_log" => "rpro_out_customers_log",
        "out_so" => "rpro_out_so",
        "out_so_log" => "rpro_out_so_log",
        "out_so_items" => "rpro_out_so_items",
        "out_so_items_log" => "rpro_out_so_items_log",
        "out_so_log" => "rpro_out_so_log",
        "customer_email" => "rdi_customer_email"
    );
    static public $alias = array(
        "config" => "config",
        "in_catalog" => "category",
        "in_customers" => "customer",
        "in_gift_reg" => "gift_reg",
        "in_images" => "image",
        "in_receipts" => "receipt",
        "in_so" => "so",
        "in_products" => "product",
        "in_styles" => "style",
        "in_product_images" => "product_image",
        "in_items" => "item",
        "in_upsell_item" => "upsell_item",
        "item_image" => "item_image",
        "out_customers" => "customers",
        "out_so" => "so",
        "out_so_items" => "so_item"
    );

    /**
     * Class Constructor
     *
     * @param rdi_db $db
     */
    public function rdi_staging_db_lib($db = '')
    {
        if ($db)
        {
            /**
             * set the database object
             */
            $this->set_db($db);
        }
    }

    public function require_related_id_value()
    {
        return false;
    }

    /**
     * get the pos specific table name from the generic name
     * @param string $generic_table Table name without a prefix in most cases.
     * @return string table name for v9
     */
    public function get_table_name($generic_table)
    {
        return self::$tables[$generic_table];
    }

    /**
     * Clean the older in log data.
     * 
     */
    public function clean_in_log_tables()
    {
        global $log_table_archive_length;
        //@setting $log_table_archive_length Number of days to keep in the staging tables data. Default is 10 days

        if (!isset($log_table_archive_length))
            $log_table_archive_length = 10;

        $this->db_connection->exec("delete from rpro_in_customers_log where rdi_import_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
        $this->db_connection->exec("delete from rpro_in_receipts_log where rdi_import_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
        $this->db_connection->exec("delete from rpro_in_so_log where rdi_import_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
        $this->db_connection->exec("delete from rpro_in_return_log where rdi_import_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
        $this->db_connection->exec("delete from rpro_in_styles_log where rdi_import_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
        $this->db_connection->exec("delete from rpro_in_items_log where rdi_import_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
        $this->db_connection->exec("delete from rpro_in_images_log where rdi_import_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
        $this->db_connection->exec("delete from rpro_in_categories_log where rdi_import_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
        $this->db_connection->exec("delete from rpro_in_category_products_log where rdi_import_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
        $this->db_connection->exec("delete from rpro_in_upsell_item_log where rdi_import_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
        //$this->db_connection->trunc('rpro_in_images_log where rdi_import_date < ADDDATE(NOW(), INTERVAL -10 DAY)");             

        if (count($this->db_connection->rows("SHOW TABLES like 'rpro_in_store_log'")) > 0)
        {
            //Multistore
            $this->db_connection->exec("delete from rpro_in_store_log where rdi_import_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
            $this->db_connection->exec("delete from rpro_in_store_qty_log where rdi_import_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
        }
    }

    /**
     * Clean the older out log data.
     * @setting $log_table_archive_length Number of days to keep in the staging tables data. Default is 10 days
     */
    public function clean_out_log_tables()
    {
        global $log_table_archive_length;
        //@setting $log_table_archive_length Number of days to keep in the staging tables data. Default is 10 days

        if (!isset($log_table_archive_length))
            $log_table_archive_length = 10;

        $this->db_connection->exec("delete from rpro_out_customers_log where rdi_export_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
        $this->db_connection->exec("delete from rpro_out_so_log where rdi_export_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
        $this->db_connection->exec("delete from rpro_out_so_items_log where rdi_export_date < ADDDATE(NOW(), INTERVAL -{$log_table_archive_length} DAY)");
    }

    /**
     * Moves order data to the log table.
     */
    public function log_order_export_data()
    {
        $this->db_connection->insert("INSERT rpro_out_so_log SELECT rpro_out_so.*, NOW() FROM rpro_out_so");
        $this->db_connection->insert("INSERT rpro_out_so_items_log SELECT rpro_out_so_items.*, NOW() FROM rpro_out_so_items");
    }

    /**
     * Moves customer data to the log table.
     */
    public function log_customer_export_data()
    {
        $this->db_connection->insert("INSERT rpro_out_customers_log SELECT rpro_out_customers.*, NOW() FROM rpro_out_customers");
    }

    /**
     * Truncates all the in staging tables.
     * @global rdi_Settings $settings_handler Is not used.
     * @setting $load_multistore 0-OFF, 1-ON Loading this does not work.
     */
    public function clean_in_staging_tables()
    {
        global $settings_handler, $load_multistore;
        //@setting $load_multistore [0-OFF, 1-ON] Loading this does not work. 

        $this->db_connection->trunc('rpro_in_customers');
        $this->db_connection->trunc('rpro_in_receipts');
        $this->db_connection->trunc('rpro_in_so');
        $this->db_connection->trunc('rpro_in_return');
        $this->db_connection->trunc('rpro_in_styles');
        $this->db_connection->trunc('rpro_in_items');
        $this->db_connection->trunc('rpro_in_categories');
        $this->db_connection->trunc('rpro_in_category_products');
        $this->db_connection->trunc('rpro_in_upsell_item');
        $this->db_connection->trunc('rpro_in_images');

        if (count($this->db_connection->rows("SHOW TABLES like 'rpro_in_store_log'")) > 0)
        {
            $this->db_connection->trunc('rpro_in_store');
            $this->db_connection->trunc('rpro_in_store_qty');
        }

        //calling this here so that I can piggie back and not have to call it somewhere else.
        $this->clean_in_archive();
    }

    /**
     * Removes Files older than so many days.
     * @global rdi_file_manager $manager
     * @string $inPath String for the path to the in files.
     * @setting $file_archive_length Default 90 days, -1 is off. Anything else to change the number of days to hold files.
     */
    public function clean_in_archive()
    {
        global $manager, $inPath, $file_archive_length;
        //@setting $inPath String for the path to the in files.
        //@setting $file_archive_length Default [90 days, -1 is off]. Anything else to change the number of days to hold files

        if (is_a($manager, 'file_manage'))
        {
            if (isset($file_archive_length) && $file_archive_length > -1)
            {
                $manager->delete_old_files($file_archive_length, $inPath . "/archive");
                $manager->delete_old_files($file_archive_length, $inPath . "/archive/images");
                if(file_exists($inPath . "/images/archive"))
                {
                    $manager->delete_old_files($file_archive_length, $inPath . "/archive/images");
                }
            }
            elseif ($file_archive_length == -1)
            {
                
            }
            else
            {
                /**
                 * archive for 90 days
                 */
                $manager->delete_old_files(90, $inPath . "/archive");
                $manager->delete_old_files(90, $inPath . "/archive/images");
                if(file_exists($inPath . "/images/archive"))
                {
                    $manager->delete_old_files($file_archive_length, $inPath . "/archive/images");
                }
            }
        }
    }

    /**
     * clean_in_customers
     */
    public function clean_in_customers()
    {
        $this->db_connection->trunc('rpro_in_customers');
    }

    /**
     * clean_in_customers
     */
    public function clean_in_styles()
    {
        $this->db_connection->trunc('rpro_in_styles');
        $this->db_connection->trunc('rpro_in_items');
    }

    /**
     * clean_in_catalog
     */
    public function clean_in_catalog()
    {
        $this->db_connection->trunc('rpro_in_categories');
        $this->db_connection->trunc('rpro_in_category_products');
    }

    /**
     * clean_in_so
     */
    public function clean_in_so()
    {
        $this->db_connection->trunc('rpro_in_so');
    }

    /**
     * clean_in_return
     */
    public function clean_in_return()
    {
        $this->db_connection->trunc('rpro_in_return');
    }

    /**
     * clean_in_images
     */
    public function clean_in_images()
    {
        $this->db_connection->trunc('rpro_in_images');
    }

    /**
     * clean_out_staging_tables
     */
    public function clean_out_staging_tables()
    {
        $this->db_connection->trunc("rpro_out_customers");
        $this->db_connection->trunc("rpro_out_so");
        $this->db_connection->trunc("rpro_out_so_items");
    }

    /**
     * log_current_data
     */
    public function log_current_data()
    {
        $website_name = $this->get_website_name();

        // Categories
        $this->db_connection->insert("INSERT rpro_in_categories_log SELECT rpro_in_categories.*, NOW() {$website_name} FROM rpro_in_categories");

        // Category Products
        $this->db_connection->insert("INSERT rpro_in_category_products_log SELECT rpro_in_category_products.*, NOW() {$website_name}  FROM rpro_in_category_products");

        // Customers
        $this->db_connection->insert("INSERT rpro_in_customers_log SELECT rpro_in_customers.*, NOW() {$website_name}  FROM rpro_in_customers");

        // Receipts
        $this->db_connection->insert("INSERT rpro_in_receipts_log SELECT rpro_in_receipts.*, NOW() {$website_name}  FROM rpro_in_receipts");

        // Sales Orders
        $this->db_connection->insert("INSERT rpro_in_so_log SELECT rpro_in_so.*, NOW() {$website_name}  FROM rpro_in_so");

        // 
        // Return
        $this->db_connection->insert("INSERT rpro_in_return_log SELECT rpro_in_return.*, NOW() {$website_name} FROM rpro_in_return");

        // Styles
        $this->db_connection->insert("INSERT rpro_in_styles_log SELECT rpro_in_styles.*, NOW() {$website_name}  FROM rpro_in_styles");

        // Item
        $this->db_connection->insert("INSERT rpro_in_items_log SELECT rpro_in_items.*, NOW() {$website_name}  FROM rpro_in_items");

        $this->db_connection->insert("INSERT rpro_in_upsell_item_log SELECT rpro_in_upsell_item.*, NOW() {$website_name}  FROM rpro_in_upsell_item");
        // multiple images
        //$this->db_connection->insert("INSERT rpro_in_images_log SELECT rpro_in_images.*, NOW() FROM rpro_in_images");


        if (count($this->db_connection->rows("SHOW TABLES like 'rpro_in_store_log'")) > 0)
        {
            $this->db_connection->insert("INSERT rpro_in_store_log SELECT rpro_in_store.*, NOW() FROM rpro_in_store");
            $this->db_connection->insert("INSERT rpro_in_store_qty_log SELECT rpro_in_store_qty.*, NOW() FROM rpro_in_store_qty");
        }
    }

    /**
     * get the count of the category table so we can check for a need to run the script 
     * @return int number of values in the staging table
     */
    public function get_category_count()
    {
        return $this->db_connection->count("rpro_in_categories");
    }

    /**
     * get the count of the style table so we can check for a need to run the script   
     * @return int number of values in the staging table
     */
    public function get_product_count()
    {
        return $this->db_connection->count("rpro_in_styles");
    }

    /**
     * 	get the count of the so table so we can check for a need to run the script 
     * @return  int number of values in the staging table
     */
    public function get_so_count()
    {
        return $this->db_connection->count("rpro_in_so");
    }

    /**
     * 	get the count of the return table so we can check for a need to run the script 
     * @return  int number of values in the staging table
     */
    public function get_return_count()
    {
        return $this->db_connection->count("rpro_in_return");
    }

    /**
     * does nothing
     * @return int
     * @todo do something
     */
    public function get_preferences_count()
    {
        return 0;
    }

    /**
     * Get customer count
     * @return int number of customers in staging
     */
    public function get_customer_count()
    {
        return $this->db_connection->count("rpro_in_customers");
    }

    /**
     * does nothing
     * @return int
     * @todo make this do something
     */
    public function get_image_count()
    {
        return $this->db_connection->count("rpro_in_images");
    }

    /**
     * Nothing in v9
     * @return int
     * @todo when upsells are in v9 we will add them.
     */
    public function get_upsell_count()
    {
        return $this->db_connection->count("rpro_in_upsell_item");
    }

    /**
     * Style sid name
     * @return string
     */
    public function get_style_sid()
    {
        return "style_sid";
    }

    /**
     * item sid name
     * @return string
     */
    public function get_item_sid()
    {
        return "item_sid";
    }

    /**
     * Style criteria for queries
     * @return string
     */
    public function get_style_criteria()
    {
        return " style.dcs IS NOT NULL ";
    }

    /**
     * Item criteria for queries
     * @return string
     */
    public function get_item_criteria()
    {
        return " AND item.active = 1 ";
    }

    /**
     * Item criteria for queries
     * @return string
     */
    public function get_item_avail_criteria()
    {
        return " AND item.active = 1 ";
    }

    /**
     * Default Tax Class codes
     * @return array array(field_name,taxable,exempt)
     */
    public function get_tax_class_codes()
    {
        $tax_codes = array();

        $tax_codes['field_name'] = "item.tax_code";
        $tax_codes['taxable'] = "0";
        $tax_codes['exempt'] = "1";

        return $tax_codes;
    }

    public function get_website_name()
    {
        global $use_multisite, $default_site;

        if (isset($use_multisite) && $use_multisite == 1 && $default_site !== 0)
        {
            $this->website_name = str_replace("in_", "", $GLOBALS['inPath' . $default_site]);
            return ", '{$this->website_name}' ";
        }

        return '';
    }
    
    // this will get big, but be worth it.
    public function check_update()
    {
        //rpro_in_items
        echo "check missing attr_code and size_code" . PHP_EOL;
        
        $rpro_in_items = $this->db_connection->columns('rpro_in_items');
        
        if(!in_array('attr_code'))
        {
            echo "missing attr_code on rpro_in_items!" . PHP_EOL;
        }
        
        if(!in_array('size_code'))
        {
            echo "missing size_code on rpro_in_items!" . PHP_EOL;
        }
                
    }

    /**
     * Method for moving the staging tables into the functions. Going to make installing scripts easier to manage.
     * @param type $table_name
     * @param type $temp
     * @return type
     */
    public function get_staging_table_create_table($table_name,$temp = false)
    {
        $tables = json_decode(gzuncompress(base64_decode($this->static_staging_tables)));
        
        if(isset($tables[$table_name]))
        {
            return str_replace("CREATE TABLE `{\$temp_table_prefix}", ($temp?"CREATE TABLE `TEMP123_":"") , $tables[$table_name]);
        }
    }
}

?>
