<?php
/**
 * Description of rdi_staging_db_lib
 * Generic library to ensure all new POS libraries meet minimum functionality.
 *
 * @todo impliment this in all POS like I did for the upload scripts.
 * 
 * @author PMBliss <pmbliss@retaildimensions.com>
 * @copyright (c) 2005-2015 Retail Dimensions Inc.
 * @package Core
 */
class rdi_staging_db_lib extends rdi_general {

    static public $static_staging_tables = false;
    
    protected $tables = array(
                                "config" => "rdi_settings",
                                "error_log" => "rdi_error_log",
                                "in_catalog" => "",
                                "in_catalog_log" => "",
                                "in_customers" => "",
                                "in_customers_log" => "",
                                "in_gift_reg" => "",
                                "in_gift_reg_log" => "",    
                                "in_images" => "",
                                "in_images_log" => "",    
                                "in_receipts" => "",    
                                "in_receipts_log" => "",    
                                "in_so" => "",    
                                "in_so_log" => "",    
                                "in_products" => "",    
                                "in_styles" => "",    
                                "in_products_log" => "", 
                                "in_product_images_log" => "", 
                                "in_product_images" => "", 
                                "in_items" => "",
                                "in_items_log" => "",
                                "in_upsell_item" => "",    
                                "in_upsell_item_log" => "",    
                                "item_image" => "",    
                                "out_customers" => "",    
                                "out_customers_log" => "",    
                                "out_so" => "",    
                                "out_so_log" => "",    
                                "out_so_items" => "",    
                                "out_so_items_log" => "",    
                                "out_so_log" => "",
                                "out_payment" => "",
                                "out_payment_log" => "",
                                "out_fees" => "",
                                "out_fees_log" => "",
                                "out_so_misc_charge" => "",
                                "out_so_misc_charge_log" => "",
                                'in_catalog_product' => '',
                                'in_giftcards' => '',
                                'in_store' => '',
                                'in_store_qty' => '',
                                'in_prefs' => '',
                                'in_return' => '',
                             );
    public $alias = array(
                                "config" => "config",
                                "in_catalog" => "catalog",
                                "in_customers" => "customer",
                                "in_gift_reg" => "gift_reg",
                                "in_images" => "image", 
                                "in_receipts" => "receipt",
                                "in_so" => "so",
                                "in_products" => "product",    
                                "in_styles" => "product",   
                                "in_product_images" => "product_image", 
                                "in_items" => "item",
                                "in_upsell_item" => "upsell_item",   
                                "item_image" => "item_image",    
                                "out_customers" => "customers",       
                                "out_so" => "so",       
                                "out_so_items" => "so_item",
                                "out_so_misc_charge" => "so_misc_charge",
                                "out_so_payment" => "so_payment",
                                'in_catalog_product' => 'category_product',
                                'in_giftcards' => 'giftcard',
                                'in_store' => 'store',
                                'in_store_qty' => 'store_qty',
                                'in_prefs' => 'prefs',
                                'in_return' => 'return'
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
            //set the database object
            $this->set_db($db);                    
        }
    }
       
    //get the pos specific table name from the generic name
    public function get_table_name($generic_table)
    {
        return $this->tables[$generic_table];
    }
        
    /**
     * 
     * @param type $table_name
     * @param type $days
     * @param type $type
     */   
    public function clean_log_table($table_name, $days, $type = 'in')
    {
        $date_field = $type =='in'?"rdi_import_date":"rdi_export_date";
        
        if(isset($this->tables[$table_name]) && strlen($this->tables[$table_name]) > 0){
            $this->db_connection->exec("delete from {$this->tables[$table_name]} where {$date_field} < ADDDATE(NOW(), INTERVAL -{$days} DAY)");        
        }
    }
    
    public function clean_log_tables($type = 'in')
    {
        global $log_table_archive_length;
        
        if(!isset($log_table_archive_length))
            $log_table_archive_length = 10;
        
        //starts with a '' to auto bail on all not set
        $tables_processed = array(''=>1);
        //get the distint list of in table logs
        foreach($this->tables as $generic_name => $table_name)
        {
            if(!isset($tables_processed[$table_name]))
            {
                if($this->starts_with($generic_name,$type) && $this->ends_with($generic_name,'log'))
                {
                    $this->clean_log_table($table_name, $log_table_archive_length);
                }
            }
        }   
    }
    
    /**
    * 
    *
    * @param rdi_db $db
    * @return boolean
    */       
    public function clean_in_log_tables()
    {        
        $this->clean_log_tables('in');
    }
    
    /**
     * 
     * @global int $log_table_archive_length
     */
    public function clean_out_log_tables()
    {
        $this->clean_log_tables('out');
    }
    
    
    public function log_order_export_data()
    {
        $this->db_connection->insert("INSERT svl_out_so_log SELECT svl_out_so.*, NOW() FROM svl_out_so");
        $this->db_connection->insert("INSERT svl_out_so_line_items_log SELECT svl_out_so_line_items.*, NOW() FROM svl_out_so_line_items");
        $this->db_connection->insert("INSERT svl_out_so_misc_charge_log SELECT svl_out_so_misc_charge.*, NOW() FROM svl_out_so_misc_charge");
        $this->db_connection->insert("INSERT svl_out_so_payment_log SELECT svl_out_so_payment.*, NOW() FROM svl_out_so_payment");
    }
    
    public function log_customer_export_data()
    {
        $this->db_connection->insert("INSERT svl_out_customers_log SELECT svl_out_customers.*, NOW() FROM svl_out_customers");
    }
    
    public function clean_in_staging_tables()
    {        
        global $settings_handler, $load_multistore;
                
        //$this->db_connection->trunc('svl_in_customers');
        //$this->db_connection->trunc('svl_in_receipts');
        //$this->db_connection->trunc('svl_in_so');
        $this->db_connection->trunc('svl_in_products');
        //$this->db_connection->trunc('svl_in_product_images');
        $this->db_connection->trunc('svl_in_items');
        $this->db_connection->trunc('svl_in_categories');
        $this->db_connection->trunc('svl_in_category_products');
        //$this->db_connection->trunc('svl_in_upsell_item'); 
        //$this->db_connection->trunc('svl_in_images'); 
        
        if(isset($load_multistore) && $load_multistore == 1)
        {
            $this->db_connection->trunc('svl_in_multistore'); // added 1-16-13 KL 
        }
    }
    
     public function clean_in_customers()
    {
         $this->db_connection->trunc('svl_in_customers');
    }
    
    public function clean_in_products()
    {
        $this->db_connection->trunc('svl_in_products');
        $this->db_connection->trunc('svl_in_items');
    }
    
    public function clean_in_catalog()
    {
        $this->db_connection->trunc('svl_in_categories');
        $this->db_connection->trunc('svl_in_category_products');
        
    }
    
    public function clean_in_so()
    {        
        $this->db_connection->trunc('svl_in_so');
    }
    
    public function clean_in_images()
    {
        $this->db_connection->trunc('svl_in_product_images');  
    }
    
    public function clean_out_staging_tables()
    {        
        $this->db_connection->trunc("svl_out_so_customer");
        $this->db_connection->trunc("svl_out_so");
        $this->db_connection->trunc("svl_out_so_line_items");        
        $this->db_connection->trunc("svl_out_so_misc_charge");        
        $this->db_connection->trunc("svl_out_so_payment");        
    }
    
    /**
    * 
    *
    * @param rdi_db $db
    * @return boolean
    */
    public function log_current_data()
    {                
        // svl_in_category
	$this->db_connection->insert("INSERT svl_in_categories_log SELECT svl_in_categories.*, NOW() FROM svl_in_categories");
        	
        // Category Products
		$this->db_connection->insert("INSERT svl_in_category_products_log SELECT svl_in_category_products.*, NOW() FROM svl_in_category_products");
        
	// Customers
      	//$this->db_connection->insert("INSERT svl_in_customers_log SELECT svl_in_customers.*, NOW() FROM svl_in_customers");
	
	// Receipts
      	//$this->db_connection->insert("INSERT svl_in_receipts_log SELECT svl_in_receipts.*, NOW() FROM svl_in_receipts");
	
	// Sales Orders
       	//$this->db_connection->insert("INSERT svl_in_so_log SELECT svl_in_so.*, NOW() FROM svl_in_so");
	
	// Styles
        $this->db_connection->insert("INSERT svl_in_products_log SELECT svl_in_products.*, NOW() FROM svl_in_products");
        
        // Item
        $this->db_connection->insert("INSERT svl_in_items_log SELECT svl_in_items.*, NOW() FROM svl_in_items");
        
        //$this->db_connection->insert("INSERT svl_in_upsell_item_log SELECT svl_in_upsell_item.*, NOW() FROM svl_in_upsell_item");
	// multiple images
      //$this->db_connection->insert("INSERT svl_in_product_images_log SELECT svl_in_product_images.*, NOW() FROM svl_in_product_images");
        
        // multistore
       // $this->db_connection->insert("INSERT svl_in_multistore_log SELECT svl_in_multistore.*, NOW() FROM svl_in_multistore"); // added 1-16-13 KL
    }
    
    // ----------------------------------------------------------------------
    //	get the count of the category table so we can check for a need to run the script     
    // ----------------------------------------------------------------------
    public function get_category_count()
    {         
        return $this->db_connection->count("svl_in_categories");
    }
    
    // ----------------------------------------------------------------------
    //	get the count of the style table so we can check for a need to run the script     
    // ----------------------------------------------------------------------
    public function get_product_count()
    {
        return $this->db_connection->count("svl_in_products");
    }
    
    // ----------------------------------------------------------------------
    //	get the count of the so table so we can check for a need to run the script     
    // ----------------------------------------------------------------------
    public function get_so_count()
    {
        return $this->db_connection->count("svl_in_so");
    }
    
    public function get_preferences_count()
    {
        return $this->db_connection->count("");
    }
    
    public function get_customer_count()
    {
        return $this->db_connection->count("svl_in_customers");
    }
    
    public function get_product_images_count()
    {
        return $this->db_connection->count("SELECT count(*) FROM {$this->get_table('in_product_images')}");
    } 
    
    public function get_upsell_count()
    {
        return $this->db_connection->count("svl_in_upsell_item");
    }
    
    
    //these will need to be added to work with the other parts in the magento code.
    public function get_style_sid()
    {
        return "product_id";
    }
    
    
    public function get_item_sid()
    {
        return "item_id";
    }
    
    
    public function get_style_criteria()
    {
        return " 1 = 1 ";
    }
    
    public function get_item_criteria()
    {
        return " ";
    }
    public function get_item_avail_criteria()
    {
        return " ";
    }
    
    public function get_tax_class_codes()
    {
        $tax_codes = array();
        
        $tax_codes['field_name'] = "style.taxable";
        $tax_codes['taxable'] = "True";
        $tax_codes['exempt'] = "False";
        
        return $tax_codes;
    }
    
    /**
     * Method for moving the staging tables into the functions. Going to make installing scripts easier to manage.
     * @param type $table_name
     * @param type $temp
     * @return type
     */
    public function get_staging_table_create_table($table_name,$temp = false)
    {
        if($this->static_staging_tables)
        {
            $tables = json_decode(gzuncompress(base64_decode($this->static_staging_tables)));

            if(isset($tables[$table_name]))
            {
                return str_replace("CREATE TABLE `{\$temp_table_prefix}", ($temp?"CREATE TABLE `TEMP123_":"") , $tables[$table_name]);
            }
        }
    }
}
