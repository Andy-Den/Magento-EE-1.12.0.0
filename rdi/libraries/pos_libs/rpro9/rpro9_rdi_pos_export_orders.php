<?php
/**
 * Class File
 */
/**
 * Catalog load class
 *
 * Handles the loading of the catalog data
 *
 * PHP version 5.3
 *
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * 
 * @package    Core\Export\Orders\RPro9
 */
class rdi_pos_export_orders extends rdi_general
{   
    /**
     * Constructor Class
     */
    public function pos_rdi_export_orders()
    {
         if ($db)
            $this->set_db($db);      
    }
    
    /**
     * Pre Load function
     * We create annoymous customers here.
     * 
     * @global type $hook_handler
     * @global rdi_lib $cart
     * @see rdi_cart_export_orders::create_annonymous_customers
     * @hook pos_export_orders_pre_load
     */
    public function pre_load()
    {
       global $hook_handler, $cart;
       
       //$cart->get_processor("rdi_cart_export_orders")->create_annonymous_customers();
        
       //$this->check_update();
       
       
        $hook_handler->call_hook("pos_export_orders_pre_load");
    }
    
    /**
     * Post Load function
     * Call a function to get customer notes.
     * 
     * @global type $hook_handler
     * @hook pos_export_orders_post_load
     */
    public function post_load()    
    {
        global $hook_handler;       
        
        $this->add_customer_notes();
        
        $this->default_export_item_sid();
        
        $this->db_connection->exec("UPDATE rpro_out_so SET customer_cust_sid = cust_sid WHERE LENGTH(cust_sid) > 0 AND LENGTH(customer_cust_sid) = 0");
        
        $hook_handler->call_hook("pos_export_orders_post_load");
    }
    
    /**
     * Clean and quote fields in order record.
     * 
     * @global rdi_helper $helper_funcs
     * @global rdi_debug $debug
     * @global rdi_lib $cart
     * @param key $section first key
     * @param key $field second sky
     * @param array $order_record data that represents an order.
     * @return string
     */
    public function process_field($section, $field, $order_record)
    {
        global $helper_funcs, $debug, $cart;
        
        if(isset($order_record[$section][$field]))            
            return  $helper_funcs->quote($order_record[$section][$field]);
        
        return '';
    }
    
    /**
     * Take order record and convert it to xml.
     * 
     * @global rdi_helper $helper_funcs
     * @global rdi_debug $debug
     * @global rdi_lib $cart
     * @param array $order_record order record in an array.
     */
    public function process_order_record($order_record)
    {
        global $helper_funcs, $debug, $cart, $pos_so_type;
        
        $fields = "";
        $so_type = isset($pos_so_type)?$pos_so_type:0;
        /**
         * default values
         */
        $note = str_pad((isset($order_record['base_data']['note'])?$order_record['base_data']['note']:''), 15, ' ', STR_PAD_LEFT);
        
        
        $data = array(
                        "note" => "'{$note}'"                                                        
                     );
      
        /**
         * bill to info
         */
        foreach($order_record['bill_to_data'] as $field => $value)
        {
            if(isset($data[$field]) && $value != '')
                continue;
            $value = str_replace("'","\'",$value);
            $data[$field] = "'{$value}'";                 
        }

        /**
         * ship to info
         */
        foreach($order_record['ship_to_data'] as $field => $value)
        {
            if(isset($data[$field]) && $value != '')
                continue;
            $value = str_replace("'","\'",$value);
            $data[$field] = "'{$value}'";                
        }
        
        /**
         * base info
         */
        /**
         * @todo These are not right PMB 20130205
         */
        foreach($order_record['base_data'] as $field => $value)
        {
            if($field == "")
            {
                $so_sid = "'" . str_pad(trim($order_record['base_data']['orderid']), 12, '0', STR_PAD_LEFT) . "'";
                $so_sid = str_pad('', 4, trim($orig_store_no), STR_PAD_LEFT) . $so_sid;                
            }
            else if($field == "")
            {
                $so_no = "'" . str_pad(trim($order_record['base_data']['orderid']), 10, '0', STR_PAD_LEFT) . "'";
                $so_no = str_pad(trim($orig_store_no), 4, '0', STR_PAD_LEFT) . $so_no;  
            }
            else if($field != '')
            {
               if(isset($data[$field]) && $value != '')
                    continue;
               $value = str_replace("'","\'",$value);
               $data[$field] = "'{$value}'";     
            }
        }        
        
        foreach($order_record['payment_data'] as $field => $value)
        {
            if(isset($data[$field]) && $value != '')
                continue;
            $value = str_replace("'","\'",$value);
            $data[$field] = "'{$value}'";                
        }
                
        
        $fields = '';
        $values = '';
        foreach($data as $field => $value)
        {
            if($field !== 'NULL')
            {
                    $fields .= "{$field},";
                    $values .= "{$value},";            
            }          
             //$data[$field] = "'" . htmlspecialchars($value, ENT_QUOTES|ENT_IGNORE, 'UTF-8') . "',";  
        }
        $fields = substr($fields,0,-1);
        $values = substr($values,0,-1);     
        
        $sql = "INSERT INTO rpro_out_so ({$fields}) values({$values})";
        $this->db_connection->exec($sql);
              
        unset($data);
                  
        /**
         * handle annoymous checkout
         * create annoymous customers
         */        

        /**
         * get criteria for updating staging table
         */
        $criteria = $cart->get_processor("rdi_cart_export_orders")->update_criteria_annonymous_customers();
        //$criteria['join'] = ' JOIN customer_entity ';
        //$criteria['on'] = ' customer_entity.email ';
        //$criteria['set'] = ' customer_entity.entity ';
        //not used $criteria['where'] = ' AND customer_entity.website_id IS NULL ';


        /**
        * Should be this query built
       $sql = "UPDATE rpro_out_so 
       JOIN customer_entity 
       ON rpro_out_so.customer_email = customer_entity.email 
       SET rpro_out_so.cust_sid = customer_entity.entity_id 
       WHERE  rpro_out_so.cust_sid = '' 
       AND customer_entity.website_id IS NULL";
         */

        $sql = "UPDATE rpro_out_so 
        {$criteria['join']}
        ON rpro_out_so.customer_email = {$criteria['on']} 
        SET rpro_out_so.cust_sid = {$criteria['set']} 
        WHERE  rpro_out_so.cust_sid = ''";
        
        /**
         * {$criteria['where']}";
         */

        $this->db_connection->exec($sql);
        
        
        
        foreach($order_record['item_data'] as $item)
        {
            $data = array(                                                             
                         );
            
            $data['so_sid'] = "'" . $order_record['base_data']['so_sid']."'";                      
            
            foreach($item as $field => $value)
            {
				// protection against style_sid = item_sid
				if($field == 'item_sid')
				{
					$value = str_replace("X","",$value);
				}
                if(isset($data[$field]) && $value != '')
                    continue;
                $value = str_replace("'","\'",$value);
                $data[$field] = "'{$value}'";     
            }     
            
            $data['orderid'] = "'" . $order_record['base_data']['orderid'] . "'";
                                    
            $fields = '';
            $values = '';
            foreach($data as $field => $value)
            {
                
                $fields .= "{$field},";
                $values .= "{$value},";            
            }
            $fields = substr($fields,0,-1);
            $values = substr($values,0,-1);     
            
            $sql = "INSERT INTO rpro_out_so_items ({$fields}) values({$values})";
            $this->db_connection->exec($sql);

        }
        
    }
    
    /**
     * Gets the customer notes from the cart lib export_orders and then updates the staging table.
     * 
     * @author pmbliss
     * @global setting $custom_notes
     * @global rdi_lib $cart
     * @date 01292014
     * @see rdi_cart_export_orders::get_customer_notes
     */
    public function add_customer_notes()
    {
        global $customer_notes, $cart;
        
        //@setting $customer_notes [0-OFF, 1-ON] Gets the customer notes from the order and fills them into the note field.
        if(isset($customer_notes) && $customer_notes == 1)
        {
            /**
             * get the data from the cart
             */
            $_notes = $cart->get_processor('cart_export_orders')->get_customer_notes("rpro_out_so", "orderid");
            
            if(!empty($_notes))
            {
                foreach($_notes as $note)
                {
                    if(trim($note['comment']) === trim($note['customer_note']))
                    {
                        $out_note = $note['comment'];
                    }
                    else
                    {
                        $out_note = $note['comment'] . ":" . $note['customer_note'];
                    }
                    
                    $out_note = $this->db_connection->clean($out_note);
                    
                    $this->db_connection->exec("UPDATE rpro_out_so set note = '{$out_note}' where orderid = {$note['orderid']}");
                }
            }
            
        }
        
    }
    
    public function get_parameters_for_export_status()
    {
        global $order_prefix;
        
        $parameters = array();
        $parameters['table'] = "rpro_out_so";
        $parameters['field'] = "so.orderid";
            
        return $parameters;
    }
   
    
    public function check_update()
    {
        //check to see if they have currency_name added because this will be required in RDice soon.
        if(!$this->db_connection->column_exists("rpro_out_so","currency_name"))
        {
            $this->db_connection->exec("ALTER TABLE `rpro_out_so`   
                                            ADD COLUMN `tender_taken` VARCHAR(60) NULL AFTER `cardholder_name`,
                                            ADD COLUMN `currency_name` VARCHAR(60) NULL AFTER `tender_taken`;
                                          ");
            
            $this->db_connection->exec("ALTER TABLE `rpro_out_so_log`   
                                            ADD COLUMN `tender_taken` VARCHAR(60) NULL AFTER `cardholder_name`,
                                            ADD COLUMN `currency_name` VARCHAR(60) NULL AFTER `tender_taken`;
                                          ");
            
        }
        
    }
    
    /**
     * Updates a v9 export rpro_out_so_items with the default item_sid if one is not provided.
     * @global setting $default_export_item_sid Updates a v9 export rpro_out_so_items with the default item_sid if one is not provided.
     */
    public function default_export_item_sid()
    {
        global $default_export_item_sid;

        if(isset($default_export_item_sid) && strlen($default_export_item_sid) > 0)
        {
                $this->db_connection->exec("UPDATE rpro_out_so_items set item_sid = '{$default_export_item_sid}' WHERE ifnull(trim(item_sid),'') = ''");
        }

    }
    
   
}

?>
