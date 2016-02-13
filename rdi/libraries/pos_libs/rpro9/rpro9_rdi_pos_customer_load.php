<?php
/**
 * Class File
 */
/**
 * Retail Pro 9 Customer Load
 *
 * @author PMBliss<pmbliss@retaildimensions.com>
 * @copyright Retail Dimensionsl 2005-2014
 * 
 * @package    Core\Load\Customer\RPro9
 */
class rdi_pos_customer_load extends rdi_general 
{
    /**
     * Class Constructor
     *
     * @param rdi_db $db
     */
    public function rdi_pos_customer_load($db = '')
    {
        if ($db)
            $this->set_db($db);        
    }
     
    /**
     * Pre Load Function
     * @global rdi_hook $hook_handler
     * @hook pos_customer_load_pre_load
     */
    public function pre_load()
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_customer_load_pre_load");
    }
    
    /**
     * Post Load Function
     * @global rdi_hook $hook_handler
     * @hook pos_customer_load_post_load
     */
    public function post_load()    
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_customer_load_post_load");
    }
    
    /**
     * Combines cart parameters to build queries to get customer data.
     * 
     * @global rdi_field_mapping $field_mapping
     * @global rdi_debug $debug
     * @global string $scale_key
     * @param array $parameters fields, where, join
     * @return boolean
     */
    public function get_customer_data($parameters = array())
    {
        global $field_mapping, $debug, $scale_key;
        
        /**
         * build out the query to get the product records
         */

        /**
         * get the field list
         */
        $customer_fields = $field_mapping->get_field_list('customer_in');
                
        /**
         * make sure there was something mapped
         */
        if(is_array(  $customer_fields )) 
        {        
            $fields = '';
            
            /**
             * build out the list of fields to query, but only the ones that are mapped to something
             */
            foreach($customer_fields as $mapping)
            {
//                //this will limit the fields returned on an update to just the field to update
//                //but skips certain others like related_id, so more work needed               
//                if($mapping['cart_field'] != 'related_id' && isset($parameters['update_field']) && $parameters['update_field'] != '')
//                {
//                    if($parameters['update_field'] != $mapping['cart_field'])
//                        continue;                    
//                }
                
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
                            $p_fields = explode(",", $mapping['pos_field']);
                            $alt_fields = explode(",", $mapping['alternative_field']);

                            $new_field = "CONCAT(";

                            foreach($p_fields as $idx => $f)
                            {
                                $new_field .= "ifnull({$f}, {$alt_fields[$idx]}),";
                            }

                            $new_field = substr($new_field,0,-1);

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
                
                /**
                 * size and attr are special fields they have a specified sort order that we can tap
                 * if they are specified in the mapping here then try and get that sort order
                 * else use 0
                 */
                
                
                if(!isset($parameters['update_field']))
                    $parameters['update_field'] = '';
                                
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
            
            if(isset($parameters['fields']) && $parameters['fields'] != '')
                $fields .= ", {$parameters['fields']}";
                        
            /**
             * build out the query            
             */
            $sql = "Select distinct                            
                        {$fields}                    
                        FROM rpro_in_customers                        
                        {$parameters['join']}
                        {$where}   
                        {$parameters['where']} 
                        {$parameters['group_by']}
                        {$parameters['having']}
                        {$parameters['order_by']}
                        {$parameters['index']}    
                        ";
                        
            unset($parameters, $customer_fields);
            
            return $this->db_connection->rows($sql);                                    
        }
        
        unset($parameters, $customer_fields);
        
        return false;
    }
    
    /**
     * Set the customer id from retail pro to the cart.
     * @param array $parameters join, fields
     * @return boolean This might be NULL or an object of the last query run data.
     */
    public function set_customer_relation($parameters)
    {   
         $sql = "UPDATE rpro_in_customers
                {$parameters['join']}
                SET 
                {$parameters['fields']}
            ";
                
        return $this->db_connection->exec($sql);
    }
}

?>
