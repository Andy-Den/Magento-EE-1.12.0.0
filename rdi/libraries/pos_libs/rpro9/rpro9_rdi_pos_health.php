<?php
/**
 * Class File
 */
/**
 * Retail Pro v9 Health load class
 *
 * Handles the loading of the catalog data
 *
 * PHP version 5.3
 *
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * 
 * @package    Core\Health\RPro9
 */
class rdi_pos_health extends rdi_general {
    
    
    /**
     * Class Constructor
     *
     * @param rdi_db $db
     */
    public function rdi_pos_health($db = '')
    {
        if ($db)
            $this->set_db($db); 

		$this->class_return = array();
    }
     
    /**
     * Pre Load Function
     * 
     * @global type $hook_handler
     * @return \rdi_pos_health
     * @hookname pos_health_pre_load
     */
    public function pre_load()
    {
        global $hook_handler; 
                
        $hook_handler->call_hook("pos_health_pre_load");
        
        return $this;
    }
    
    /**
     * Post Load Function
     * 
     * @global type $hook_handler
     * @return \rdi_pos_health
     * @hook pos_health_post_load
     */
    public function post_load()    
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_health_post_load");
		
		return $this;
    }
	
    /**
     * Returns all the data from this class.
     * @return array lots of data has this array
     */
    public function get_class_return()
    {
        return $this->class_return;
    }
    
    /**
     * Main Load Class
     * @return \rdi_pos_health
     */
    public function load()    
    {
        $this->pre_load()->staging_tables()->post_load();
		
        return $this;
    }
    
    /**
     * Get the staging table history.
     * @return \rdi_pos_health
     */
    public function staging_tables()
    {
        $this->_echo(__CLASS__ . ": " . __FUNCTION__);
	
        $this->set_staging_array("rpro_in_styles_log")->set_staging_array("rpro_in_items_log")->set_staging_array("rpro_in_categories_log")->set_staging_array("rpro_in_category_products_log")->set_staging_array("rpro_in_so_log")->set_staging_array("rpro_out_so_log")->set_staging_array("rpro_out_so_items_log");		

        return $this;
    }
    
    /**
     * Queries the staging table for the given name.
     * 
     * @param string $table_name name of the staging table log.
     * @return \rdi_pos_health
     */
    public function set_staging_array($table_name)
    {
        $test = explode("_",$table_name);

        $importexport = $test[1] == 'in'?'import':"export";

        $this->_echo("{$table_name}");
        $this->class_return[$table_name] = $this->db_connection->rows("SELECT COUNT(*), rdi_{$importexport}_date FROM {$table_name} GROUP BY rdi_{$importexport}_date order by rdi_{$importexport}_date desc");

        $regular_table_name = str_replace("_log", "", $table_name);
        
        $_cols = $this->db_connection->cells("SHOW COLUMNS FROM {$regular_table_name}","Field");
        
        $columns = implode(',',$_cols);
        
        $this->class_return[$table_name]['sql'] = "INSERT INTO {$regular_table_name} ({$columns}) SELECT {$columns} FROM {$table_name} WHERE rdi_{$importexport}_date ";
        
        //$this->_print_r($this->class_return[$table_name]);

        return $this;
    }
    
}

?>
