<?php
/**
 * Class File
 */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * PRoduct load class
 *
 * Handles the loading of the product data
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Load\Product\RPro9
 */
class rdi_pos_product_load extends rdi_general {

    /**
     * Class Constructor
     *
     * @param rdi_catalog_load $db
     */
    public function rdi_pos_product_load($db = '')
    {
        if ($db)
            $this->set_db($db);        
    }
           
    /**
     * Pre Load Function
     * @global type $hook_handler
     * @hook pos_product_load_pre_load
     */
    public function pre_load()
    {
        global $hook_handler; 
        
        $hook_handler->call_hook("pos_product_load_pre_load");
        
        $this->fix_product_grid();
        $this->exclude_from_web();
        $this->prep_product_data();
    }
    
    /**
     * Post Load Function
     * @global type $hook_handler
     * @hook pos_product_load_post_load
     */
    public function post_load()    
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_product_load_post_load");
    }
    
    public function get_upsell_data($parameters)
    {
        if (array_key_exists(1, $parameters))
        {
            $sql = '';

            foreach ($parameters as $parameter_list)
            {
                if (!isset($parameter_list['table']) || $parameter_list['table'] == '')
                    $parameter_list['table'] = 'rpro_in_upsell_item';

                if (isset($parameter_list['fields']) && $parameter_list['fields'] != '')
                    $parameter_list['fields'] = ", {$parameter_list['fields']}";
                else
                    $parameter_list['fields'] = '';

                if (isset($parameter_list['where']) && $parameter_list['where'] != '')
                    $parameter_list['where'] = " Where {$parameter_list['where']}";
                else
                    $parameter_list['where'] = '';

                if (isset($parameter_list['group_by']) && $parameter_list['group_by'] != '')
                    $parameter_list['group_by'] = " Group by {$parameter_list['group_by']} ";
                else
                    $parameter_list['group_by'] = '';

                if (!isset($parameter_list['join']))
                    $parameter_list['join'] = '';

                if (isset($parameter_list['order_by']) && $parameter_list['order_by'] != '')
                    $parameter_list['order_by'] = " Order by {$parameter_list['order_by']} ";
                else
                    $parameter_list['order_by'] = '';

                $sql .= "Select distinct rpro_in_upsell_item.fldorderno as position {$parameter_list['fields']}
                            from {$parameter_list['table']}
                            {$parameter_list['join']}
                            {$parameter_list['where']}
                            {$parameter_list['group_by']}
                            {$parameter_list['order_by']}
                        union ";
            }

            $sql = substr($sql, 0, -6);

            return $this->db_connection->rows($sql);
        }

        //single call
        if (!isset($parameters['table']) || $parameters['table'] == '')
            $parameters['table'] = 'rpro_in_upsell_item';

        if (isset($parameters['fields']) && $parameters['fields'] != '')
            $parameters['fields'] = ", {$parameters['fields']}";
        else
            $parameters['fields'] = '';

        if (isset($parameters['where']) && $parameters['where'] != '')
            $parameters['where'] = " Where {$parameters['where']}";
        else
            $parameters['where'] = '';

        if (isset($parameters['group_by']) && $parameters['group_by'] != '')
            $parameters['group_by'] = " Group by {$parameters['group_by']} ";
        else
            $parameters['group_by'] = '';

        if (!isset($parameters['join']))
            $parameters['join'] = '';

        if (isset($parameters['order_by']) && $parameters['order_by'] != '')
            $parameters['order_by'] = " Order by {$parameters['order_by']} ";
        else
            $parameters['order_by'] = '';

        $sql = "Select distinct rpro_in_upsell_item.fldorderno as position {$parameters['fields']}
                    from {$parameters['table']}
                    {$parameters['join']}
                    {$parameters['where']}
                    {$parameters['group_by']}
                    {$parameters['order_by']}";

        return $this->db_connection->rows($sql);
    }
    
    /**
     * calls before any product handing is done to prep the data
     * @setting use_so_committed does not work.
     */
    public function prep_product_data()
    {
         global $use_so_committed;
        
         /**
          * does not work.
          */
        if(isset($use_so_committed) && $use_so_committed == 1)
        {
            $update = $this->get_db()->cell("select if(pos_lib='so_committed not applied',1,0) as 'check' from rdi_settings WHERE setting = 'use_so_committed'",'check');
            
            if($update == 1)
            {
                $this->get_db()->exec("UPDATE rpro_in_items
                                                            SET quantity = IF(quantity - so_committed >0,quantity - so_committed, 0)
                                                           WHERE so_committed > 0");
            }
            
            $this->get_db()->exec("UPDATE rdi_settings
                                                    SET pos_lib = 'so_committed applied'
                                                   WHERE setting = 'use_so_committed'");
            
        }
        
        /**
         * set all inventory that is out of stock to a 0 qty
         */
        $this->db_connection->rows("UPDATE rpro_in_items set quantity = 0 where quantity < 0");                                       
    }
    
    
    /**
     * get the product data from the staging table that matches the supplied criteria
     * @global rdi_field_mapping $field_mapping
     * @global rdi_debug $debug
     * @global rdi_helper $helper_funcs
     * @param string $product_class For magento this is the attribute set
     * @param string $product_type for magento this is the simple, configurable
     * @param type $parameters
     * @return boolean
     */
    public function get_product_data($product_class, $product_type, $parameters, $update = false)
    {                 
        global $field_mapping, $helper_funcs;
                        
        //build out the query to get the product records
        //get the field list
        //print_r($product_class);
        
        $product_type = is_array($product_type)?$product_type['product_type']:$product_type;
        
        //get the field list
        if($update && isset($parameters['mapping']))
        {
            $product_fields = array('0' => $parameters['mapping']);
        }
        else
        {
            //updated PMB 03112013 This was bring back an array in product class
            if(is_array($product_class))
            {
                $product_fields = $field_mapping->get_field_list('product', $product_type, $product_class['product_class']);
            }
            else
            {
                $product_fields = $field_mapping->get_field_list('product', $product_type, $product_class);
            }
        
        }
        
                              
        /**
         * make sure there was something mapped, This is bad and is now changed. We already know the mapping from the cart.
         */
        if(is_array($product_fields)) //|| (!isset($parameters['field_override']) && $parameters['field_override'] != ''))
        {                             
            $fields = '';
            
            /**
             * build out the list of fields to query, but only the ones that are mapped to something
             */
            foreach($product_fields as $mapping)
            {
                /**
                 * this will limit the fields returned on an update to just the field to update
                 * but skips certain others like related_id, so more work needed
                 */
                if($mapping['cart_field'] != 'related_id' && isset($parameters['update_field']) && $parameters['update_field'] != '')
                {
                    if($parameters['update_field'] != $mapping['cart_field'])
                        continue;                    
                }
                
                if($mapping['cart_field'] != '')
                {
                    /**
                     * check and make sure the parameter field didnt override the field from the mapping
                     */
                    if (isset($parameters['fields']) && strpos($parameters['fields'], "as '{$mapping['cart_field']}'"))
                    {      
                        continue;
                    }   
                    
                    /**
                     * its possible not to have a pos field assigned
                     */
                    if($mapping['pos_field'] == '')
                    {                      
                        $fields .= "'{$mapping['default_value']}' as '{$mapping['cart_field']}',";                     
                    }
                    else
                    {    
                        /**
                         * if there is multiple fields and alternatives, need to handle it differently with the concat
                         */
                        if(strpos($mapping['pos_field'], ',') > 0 && strpos($mapping['alternative_field'], ',') > 0)
                        {
                            $new_field = "CONCAT(";

                            $new_field .= $helper_funcs->sift_field_to_alt($mapping['pos_field'], $mapping['alternative_field']);

                            $new_field .= ")";

                            $mapping['pos_field'] = $new_field;   

                            if($mapping['default_value'] != null || $mapping['default_value'] != '')
                            {
                                $fields .= "ifnull({$mapping['pos_field']}, '{$mapping['default_value']}') as '{$mapping['cart_field']}',";                  
                            }
                            else
                            {
                                $fields .= $mapping['pos_field'] . " as '{$mapping['cart_field']}',";                  
                            }
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
                            else if($mapping['default_value'] != null || $mapping['default_value'] != '')
                            {
                                $fields .= "ifnull({$mapping['pos_field']}, '{$mapping['default_value']}') as '{$mapping['cart_field']}',";                  
                            }
                            else
                            {
                                $fields .= $mapping['pos_field'] . " as '{$mapping['cart_field']}',";                  
                            }
                        }                        
                    }                    
                }
            }
            
            $fields = substr($fields,0,-1);
            
            $where = '';
            
            /**
             * build out the criteria
             */
            if(is_array($product_class['query_criteria']))
            {                            
                foreach($product_class['query_criteria'] as $record)
                {
                    if($record['qualifier'] != '')
                        $where .= " AND {$record['pos_field']} {$record['qualifier']}";
                }
            }
            
            
            if($parameters['where'] != '')
                $parameters['where'] = " and {$parameters['where']}";
            
            if($parameters['group_by'] != '')
                $parameters['group_by'] = " Group by {$parameters['group_by']} ";
            
            if($parameters['order_by'] != '')
                $parameters['order_by'] = " order by {$parameters['order_by']} ";
            else
                $parameters['order_by'] = " order by style_id ";
            
            if(isset($parameters['having']) && $parameters['having'] != '')
                $parameters['having'] = " having {$parameters['having']} ";
            else
            {
                $parameters['having'] = "";
            }
            
            if(isset($parameters['index']))
                    $parameters['index'] = " limit {$parameters['index']}, 5000";
            else
                    $parameters['index'] = '';
            
            
             /**
              * This is deciding if a comma is needed and should be done a lot easier.
              */
             if(isset($parameters['fields']) && $parameters['fields'] !== '')
             {
                $fields .= ", {$parameters['fields']}";
            }
			
            
            /**
             * if the fields is not blank then we will add our comma to the beginning
             */
            if(isset($fields) && trim($fields) !== '' && substr($fields,0,1) !== ',')
            {
                    $fields = ", {$fields}";
            }
            
            $fields = strstr($fields,", , ")?str_replace(", , ",", ",$fields):$fields;
            
            /**
             * build out the query            
             */
            return $this->db_connection->rows("Select distinct
                            style.style_sid AS style_id,
                            item.item_sid as item_id,
                            style.avail as avail
                            {$fields}                    
                    FROM rpro_in_styles style
                        INNER JOIN rpro_in_items item
                          ON style.style_sid = item.style_sid                                    
                        {$parameters['join']}
                        WHERE style.dcs IS NOT NULL and active = 1
                          {$where}   
                          {$parameters['where']}
                          {$parameters['group_by']}
                          {$parameters['having']}
                          {$parameters['order_by']}
                          {$parameters['index']}  
                        ");
        }
        
        return false;
    }
    
    /**
     * get the id of the items related to this related id, this this case the items assigned to this style
     * 
     * @global rdi_field_mapping $field_mapping
     * @global rdi_debug $debug
     * @param string $related_id the field from the cart that was connect too.
     * @param array $parameters query parameters from the cart. fields, where, group_by,order_by, having, index
     * @return array of products
     */
    public function get_related_item_ids($related_id, $parameters)
    {         
        if($parameters['fields'] != '')
            $parameters['fields'] = ", {$parameters['fields']}";

        if($parameters['where'] != '')
            $parameters['where'] = " and {$parameters['where']}";

        if($parameters['group_by'] != '')
            $parameters['group_by'] = " Group by {$parameters['group_by']} ";

        if($parameters['order_by'] != '')
            $parameters['order_by'] = " order by {$parameters['order_by']} ";
       
        if(isset($parameters['having']) && $parameters['having'] != '')
            $parameters['having'] = " having {$parameters['having']} ";
        else
        {
            $parameters['having'] = '';
        }
        
        if(isset($parameters['index']))
                $parameters['index'] = " limit {$parameters['index']}, 5000";
            else
                $parameters['index'] = '';
            
        /**
         * build out the query            
         */
        return $this->db_connection->rows("Select distinct
                    item.item_sid as 'pos_field'
                    {$parameters['fields']}                    
                 FROM rpro_in_styles style
                    INNER JOIN rpro_in_items item
                      ON style.style_sid = item.style_sid  
                {$parameters['join']}
                WHERE 
                  item.style_sid = '{$related_id}' AND item.item_sid IS NOT NULL
                  {$parameters['where']} 
                  {$parameters['group_by']}
                  {$parameters['having']}
                  {$parameters['order_by']}"); 
    }
    
    /**
     * a much more simplified verion of the get product data, doesnt do very much processing, and assumes the fields list is coming from the parameters
     * @global rdi_field_mapping $field_mapping
     * @global rdi_debug $debug
     * @global rdi_helper $helper_funcs
     * @param string $product_class
     * @param string $product_type
     * @param array $parameters
     * @param string $item_type
     * @param boolean $get_ids
     * @return boolean
     */
    public function get_data($product_class, $product_type, $parameters, $item_type = 'item', $get_ids = true)
    {                
        global $field_mapping, $helper_funcs;
                        
        /**
         * build out the query to get the product records
         * get the field list
         */
        $product_fields = $field_mapping->get_field_list('product', $product_type, $product_class);
        
       if($item_type == 'image')
        {
            $product_fields = $field_mapping->get_field_list('product', $product_type, $product_class, true);
        }
        else
        {
            $product_fields = $field_mapping->get_field_list('product', $product_type, $product_class);
        }
        
        /**
         * make sure there was something mapped
         */
        if(is_array($product_fields)) //|| (!isset($parameters['field_override']) && $parameters['field_override'] != ''))
        {                             
            $fields = '';
            
            /**
             * build out the list of fields to query, but only the ones that are mapped to something
             */
            foreach($product_fields as $mapping)
            {
                /**
                 * this will limit the fields returned on an update to just the field to update  
                 * but skips certain others like related_id, so more work needed             
                 */
                if($mapping['cart_field'] != 'related_id' && isset($parameters['update_field']) && $parameters['update_field'] != '')
                {
                    if($parameters['update_field'] != $mapping['cart_field'])
                        continue;                    
                }
                
                if($mapping['cart_field'] != '')
                {
                    /**
                     * check and make sure the parameter field didnt override the field from the mapping
                     */
                    if (isset($parameters['fields']) && strpos($parameters['fields'], "as '{$mapping['cart_field']}'"))
                    {      
                        continue;
                    }   
                    
                    /**
                     * its possible not to have a pos field assigned
                     */
                    if($mapping['pos_field'] == '')
                    {                      
                        $fields .= "'{$mapping['default_value']}' as '{$mapping['cart_field']}',";                     
                    }
                    else
                    {    
                        /**
                         * @todo if there is multiple fields and alternatives, need to handle it differently with the concat
                         */
                        if(strpos($mapping['pos_field'], ',') > 0 && strpos($mapping['alternative_field'], ',') > 0)
                        {
                            $new_field = "CONCAT(";

                            $new_field .= $helper_funcs->sift_field_to_alt($mapping['pos_field'], $mapping['alternative_field']);

                            $new_field .= ")";

                            $mapping['pos_field'] = $new_field;   

                            if($mapping['default_value'] != null || $mapping['default_value'] != '')
                            {
                                $fields .= "ifnull({$mapping['pos_field']}, '{$mapping['default_value']}') as '{$mapping['cart_field']}',";                  
                            }
                            else
                            {
                                $fields .= $mapping['pos_field'] . " as '{$mapping['cart_field']}',";                  
                            }
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
                            else if($mapping['default_value'] != null || $mapping['default_value'] != '')
                            {
                                $fields .= "ifnull({$mapping['pos_field']}, '{$mapping['default_value']}') as '{$mapping['cart_field']}',";                  
                            }
                            else
                            {
                                $fields .= $mapping['pos_field'] . " as '{$mapping['cart_field']}',";                  
                            }
                        }                        
                    }                    
                }
            }
            
            $fields = substr($fields,0,-1);
            
            $where = '';
            
            /**
             * build out the criteria
             */
            if(is_array($product_class['query_criteria']))
            {                            
                foreach($product_class['query_criteria'] as $record)
                {
                    if($record['qualifier'] != '')
                        $where .= " AND {$record['pos_field']} {$record['qualifier']}";
                }
            }
            
            
            if($parameters['where'] != '')
                $parameters['where'] = " and {$parameters['where']}";
            
            if($parameters['group_by'] != '')
                $parameters['group_by'] = " Group by {$parameters['group_by']} ";
            
            if($parameters['order_by'] != '')
                $parameters['order_by'] = " order by {$parameters['order_by']} ";
            else
                $parameters['order_by'] = " order by style_id ";
            
            if(isset($parameters['having']) && $parameters['having'] != '')
                $parameters['having'] = " having {$parameters['having']} ";
            else
            {
                $parameters['having'] = "";
            }
            
            if(isset($parameters['index']))
                $parameters['index'] = " limit {$parameters['index']}, 5000";
            else
                $parameters['index'] = '';
            
             /**
              * This is deciding if a comma is needed and should be done a lot easier.
              */
             if(isset($parameters['fields']) && $parameters['fields'] !== '')
             {
                $fields .= ", {$parameters['fields']}";
            }
			
	    /**
             * if the fields is not blank then we will add our comma to the beginning		
             */
            if(isset($fields) && trim($fields) !== '' && substr($fields,0,1) !== ',')
            {
                    $fields = ", {$fields}";
            }

            $item_id = $image_type='image'?'':"item.item_sid as item_id,";
            
            /**
             * build out the query            
             */
            return $this->db_connection->rows("Select distinct
                            style.style_sid AS style_id,
                            {$item_id}
                            style.avail as avail
                            {$fields}                    
                    FROM rpro_in_styles style
                        INNER JOIN rpro_in_items item
                          ON style.style_sid = item.style_sid                                    
                        {$parameters['join']}
                        WHERE style.dcs IS NOT NULL and active = 1
                          {$where}   
                          {$parameters['where']}
                          {$parameters['group_by']}
                          {$parameters['having']}
                          {$parameters['order_by']}
                          {$parameters['index']}  
                        ");
        }
        
        return false;
    }
    
    /**
     * Get Single Product Criteria from the POS
     * @setting use_single_products_criteria For making single products without relying on attr/size. The setting is located in another place.
     */
    public function get_single_products()
    {
        global $cart;
        
        $cart_criteria = $cart->get_processor('rdi_cart_product_load')->get_single_products_criteria('style_sid');
        
        //this may have an and
        $cart_criteria['where'] = str_replace("and","", strtolower($cart_criteria['where']));
        
        $sids =  $this->db_connection->cells("SELECT style_sid
                        FROM  rpro_in_items                        
                        {$cart_criteria['join']}
                        WHERE
                         {$cart_criteria['where']}
                        GROUP BY style_sid
                        HAVING COUNT(DISTINCT item_sid) = 1","style_sid");
        
       
       return array("related_ids"=>$sids,"related_id_field"=>"style.style_sid");
    }
        
    
    /**
     * This does not work and is not used for setting the tax code.
     * @global type $cart_type
     */
    public function set_tax_code()
    {
        
    }
    
    /**
     * 
     * @global rdi_lib $cart
     */
    public function exclude_from_web()
    {
        global $cart, $hook_handler;
        
        //Might need to double check for simples that need to be disabled.
        if($this->db_connection->column_exists("rpro_in_items","exclude_from_web") === 'exclude_from_web' )
        {
            $parms = $cart->get_processor("rdi_cart_product_load")->get_exclude_from_web_parameters();
                        
            //this should have a %s for the join to the item_sid.
            $parms['join'] = sprintf($parms,"i.item_sid");
            
            $hook_handler->call_hook("pos_exclude_from_web_pre_delete");
            
            $this->db_connection->exec("DELETE {$parms['fields']} FROM rpro_in_items i
                                        {$parms['join']}
                                        WHERE i.excluded = 1");
                                        
            $this->db_connection->exec("DELETE FROM rpro_in_items WHERE excluded = 1");
        }
    }
    
    /**
     * This will fix the staging before product load. Adding in a missing size/attr if another item has a value in the style.
     * @setting $fix_product_grid [0-OFF,1-ON] Fix missing attribute/sizes that could be missing from bulk importing products.
     */
    public function fix_product_grid()
    {
        global $fix_product_grid, $fix_product_grid_attr, $fix_product_grid_size;
        
        if(isset($fix_product_grid) && $fix_product_grid == 1)
        {
            $this->_echo(__FUNCTION__);
            
            //@setting $fix_product_grid_attr Value to be assigned to missing attr field. Blanks will be ignored.
            $this->_echo('ATTR:' . $fix_product_grid_attr );
            
            if(isset($fix_product_grid_attr) && trim($fix_product_grid_attr) !== '' &&  $fix_product_grid_attr !== NULL )
            {
               $_sids = $this->db_connection->cells("SELECT DISTINCT style_sid FROM rpro_in_items where attr is not null","style_sid"); 
               
               if(!empty($_sids))
               {
                   $sids = implode("','",$_sids);
                   unset($_sids);
                    
                   $this->db_connection->exec("UPDATE rpro_in_items SET attr = '{$this->db_connection->clean($fix_product_grid_attr)}' WHERE style_sid in('{$sids}') AND attr is null");
               }
            }
            
            //@setting $fix_product_grid_size Value to be assigned to missing size field. Blanks will be ignored.
            $this->_echo('SIZE:' . $fix_product_grid_size );
            
            if(isset($fix_product_grid_size) && trim($fix_product_grid_size) !== '' &&  $fix_product_grid_size !== NULL )
            {
                $_sids = $this->db_connection->cells("SELECT DISTINCT style_sid FROM rpro_in_items where size is not null","style_sid"); 
               
               if(!empty($_sids))
               {
                   $sids = implode("','",$_sids);
                   unset($_sids);
                    
                   $this->db_connection->exec("UPDATE rpro_in_items SET size = '{$this->db_connection->clean($fix_product_grid_size)}' WHERE style_sid in('{$sids}') AND size is null");
               }
            }
        }
    }
}

?>
