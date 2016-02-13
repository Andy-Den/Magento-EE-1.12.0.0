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
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * 
 * @package    Core\Load\Catalog\RPro9
 */
class rdi_pos_catalog_load extends rdi_general {

    /**
     * Class Constructor
     *
     * @param rdi_catalog_load $db
     */
    public function rdi_pos_catalog_load($db = '')
    {
        if ($db)
            $this->set_db($db);        
    }
     
    /**
     * Pre Load function
     * 
     * @hook pos_catalog_load_pre_load
     * @global type $hook_handler
     */
    public function pre_load()
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_catalog_load_pre_load");
    }
    
    /**
     * Post load Function
     * 
     * @hook pos_catalog_load_post_load
     * @global type $hook_handler
     */
    public function post_load()    
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_catalog_load_post_load");
    }
    
    /**
     *get the category entiries based on passed parameters
     * if the related parent id is base, will just just base categories, if its blank gets all categories reguardless of parent, otherwise will use value as the parent
     * parameters is the parameter list passed in from the cart library, these will contain the parameter for join, and where, it will be an array
    */
    /**
     * 
     * get the category entiries based on passed parameters
     * if the related parent id is base, will just just base categories, if its blank gets all categories reguardless of parent, otherwise will use value as the parent
     * parameters is the parameter list passed in from the cart library, these will contain the parameter for join, and where, it will be an array
     * @global class $field_mapping
     * @global rdi_debug $debug
     * @param string $related_parent_id The name of the field that contents cart categories to pos categories
     * @param array $parameters array of the parameter from the cart.
     * @return boolean
     */
    public function get_categories($related_parent_id, $parameters)
    {        
        global $field_mapping; 
       
        $where = '';
    
        if($related_parent_id == 'base')
        {
            $where = "AND rpro_in_categories.level = 0";        
        }
        else if($related_parent_id != '')
        {            
            $where = "and rpro_in_categories.parent_id = '{$related_parent_id}' AND rpro_in_categories.catalog_id != '{$related_parent_id}'";
        }
        
        if($parameters['where'] != '')
            $parameters['where'] = " and " . $parameters['where'];
        
        /**
         * get the field list
         */
        $category_fields = $field_mapping->get_field_list('category');
        
        if(count($category_fields) > 0)
        {        
            $fields = '';
            
            if(isset($parameters['fields']))
            {
                if($parameters['fields'] != '')
                    $fields = $parameters['fields'] . ",";
            }
            
            /**
             * build out the list of fields to query, but only the ones that are mapped to something
             */
            foreach($category_fields as $mapping)
            {                
                if($mapping['cart_field'] != '')
                {
                    /**
                     * its possible not to have a pos field assigned
                     */
                    if($mapping['pos_field'] == '')
                    {
                        $fields .= "'{$mapping['default_value']}' as '{$mapping['cart_field']}',";                        
                    }
                    else
                    {                    
                        if(strpos($mapping['pos_field'], ',') > 0)
                        {
                            $mapping['pos_field'] = "CONCAT({$mapping['pos_field']})";
                        }

                        if(strpos($mapping['alternative_field'], ',') > 0)
                        {
                            $mapping['alternative_field'] = "CONCAT({$mapping['alternative_field']})";
                        }

                        if($mapping['alternative_field'] != null || $mapping['alternative_field'] != '')
                        {
                            $fields .= "ifnull({$mapping['pos_field']}, {$mapping['alternative_field']}) as '{$mapping['cart_field']}',";                  
                        }
                        else
                        {
                            $fields .= $mapping['pos_field'] . " as '{$mapping['cart_field']}',";                  
                        }
                    }
                }
            }
            
            $fields = substr($fields,0,-1);
        
            $sql = "SELECT distinct
                                {$fields}                        
                                FROM
                                    rpro_in_categories                                
                                {$parameters['join']}
                                WHERE
                                    category IS NOT NULL
                                    {$where}
                                    {$parameters['where']}
                                ";                                                  
                            
            $rows = $this->db_connection->rows($sql);
            
            if(!$rows)
            {
                return false;
            }
            
            $this->echo_message("Found ".count($rows)." Categories for Update",4);
       
            return $rows;
        }
        return false;
    }     
    
    /**
     * Take the parameters from the class for products to categories and builds out a query.
     * 
     * @global rdi_debug $debug
     * @param array $parameters Parameters from the class. array(where, fields, join)
     * @return boolean
     */
    public function get_category_product_relations($parameters)
    {        
        if($parameters['where'] != '')
            $parameters['where'] = " and {$parameters['where']}";
        
        if($parameters['fields'] != '')
            $parameters['fields'] .= ',';
            
        $sql = "SELECT 
                        {$parameters['fields']}        
                        rpro_in_category_products.style_sid as 'pos_style_id',
                        rpro_in_category_products.catalog_id as 'pos_catalog_id'
                FROM rpro_in_category_products
                {$parameters['join']}
                WHERE 
                rpro_in_category_products.style_sid IS NOT NULL
                {$parameters['where']}";
        
        $rows = $this->db_connection->rows($sql);
        
        if(!$rows)
        {
            return false;
        }
       
        return $rows;
    }
    
    /**
     * Builds out a query for removing products from a category.
     * 
     * @global rdi_debug $debug
     * @param array $parameters For the query from the cart. array(where, fields, join)
     * @return boolean
     */
    public function get_category_product_relations_for_removal($parameters)
    {        
        if($parameters['where'] != '')
            $parameters['where'] = " and {$parameters['where']}";
        
        if($parameters['fields'] != '')
            $parameters['fields'] .= ',';
            
        $sql = "SELECT 
                        {$parameters['fields']}        
                        rpro_in_category_products.style_sid as 'pos_style_id',
                        rpro_in_category_products.catalog_id as 'pos_catalog_id'
                FROM rpro_in_category_products
                {$parameters['join']}
                WHERE 
                rpro_in_category_products.catalog_id IS NULL
                {$parameters['where']}";
        
        $rows = $this->db_connection->rows($sql);
        
        
        if(!$rows)
        {
            return false;
        }
        
        $this->echo_message("Found ".count($rows)." Categories for Removal",4);
       
        return $rows;
    }
    
    /**
     * Set Category Product Relations
     * Inserts category relations for a cart given a 1-1 table is present. This would need to be modified if categories are handled on a long flat table.
     * 
     * @global rdi_debug $debug
     * @param array $parameters array(where, fields, join, table)
     */
    public function set_category_product_relations($parameters)
    {        
        if(isset($parameters['where']) && $parameters['where'] != '')
            $parameters['where'] = " WHERE {$parameters['where']}";
        else 
            $parameters['where'] = '';
        
        //insert new ones
         $sql = "INSERT into {$parameters['table']}
                    select distinct {$parameters['fields']}  from rpro_in_category_products                                                                         
                    left JOIN rpro_in_categories ON rpro_in_categories.catalog_id = rpro_in_category_products.catalog_id
                {$parameters['join']}                                 
                {$parameters['where']}";

        $this->db_connection->exec($sql);          
    }
}

?>
