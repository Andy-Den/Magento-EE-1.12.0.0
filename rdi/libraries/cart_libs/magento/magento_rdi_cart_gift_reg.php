<?php
/**
 * Class File
 */

/**
 * Description of magento_rdi_gift_reg_load
 * This works with the adjgiftreg module.
 *
 * ALTER TABLE `pmb`.`adjgiftreg_item_option` DROP INDEX `FK_GIFT_ITEM_OPTION_PRODUCT`, ADD UNIQUE INDEX `FK_GIFT_ITEM_OPTION_PRODUCT` (`product_id`, `item_id`, `code`); 
 * CREATE TABLE rdi_gift_reg_ref AS (
SELECT DISTINCT so_shipto_rpro_cust_sid, so_number FROM rpro_in_gift_reg 
WHERE so_shipto_rpro_cust_sid IS NOT NULL limit 0)
 * 
 * 
 * @author PMBliss <pmbliss@retaildimensions.com>
 * @copyright (c) 2005-2014 Retail Dimensions Inc.
 * @package Core/GiftReg/Magento
 *
 * 
 * @todo The table names and values for rpro_in_gift_reg should come from the POS class.
 */
class rdi_cart_gift_reg extends rdi_general {

    private $super_attributes;
    
    public function rdi_cart_gift_reg($db = '')
    {
        if ($db)
            $this->set_db($db);
        
        $this->super_attributes = array();
        $this->_attribute_id = array();
    }

    /**
     * Pre Load Function for rdi_cart_gift_reg
     * @global rdi_hook $hook_handler
     * @hook magento_rdi_gift_reg_pre_load
     */
    public function pre_load()
    {
        global $hook_handler;

        $hook_handler->call_hook('magento_rdi_gift_reg_pre_load');

        return $this;
    }

    /**
     * Post Load Function for rdi_cart_gift_reg
     * @global rdi_hook $hook_handler
     * @hook magento_rdi_gift_reg_post_load
     */
    public function post_load()
    {
        global $hook_handler;

        $hook_handler->call_hook('magento_rdi_gift_reg_post_load');

        return $this;
    }

    /**
     * Main Load Function for rdi_cart_gift_reg
     *  @return \rdi_cart_gift_reg
     */
    public function load()
    {
		global $load_gift_reg, $db_lib;
		
		//@setting $load_gift_reg [0-OFF, 1-ON] Turns on/of the loading of gift registries. Only supported in ECI/V8.
		if(isset($load_gift_reg) && $load_gift_reg == 1 && $db_lib->get_gift_reg_count() > 0)
		{		
			echo "------------Beginning Gift Registry------------ <br />";
			$this->pre_load()->load_magento_rdi_gift_reg_load()->post_load();
		}
		
        return $this;
    }

    /**
     * Working Function for magento_rdi_gift_reg_load
     * @return \rdi_cart_gift_reg
     */
    public function load_magento_rdi_gift_reg_load()
    {
        $this->get_attributes()->load_event()->load_item()->load_option()->remove_item()->remove_option();
        
        return $this;
    }
    
    /**
     * Set all the attributes for customer and catalog_product entity_types
     * @return \magento_rdi_gift_reg_load
     */
    public function get_attributes() 
    {
        //going to be greedy and get all of the attributes for both types.
        $this->_attribute_id['catalog_product'] = $this->db_connection->cells("SELECT 
                                                                                    ea.attribute_code,ea.attribute_id
                                                                                    FROM {$this->prefix}eav_attribute ea
                                                                                   INNER JOIN {$this->prefix}eav_entity_type et
                                                                                   on et.entity_type_id = ea.entity_type_id 
                                                                                   AND et.entity_type_code = 'catalog_product'
                                                                                   ", "attribute_id", "attribute_code");

        $this->_attribute_id['customer'] = $this->db_connection->cells("SELECT 
                                                                                    ea.attribute_code,ea.attribute_id
                                                                                    FROM {$this->prefix}eav_attribute ea
                                                                                   INNER JOIN {$this->prefix}eav_entity_type et
                                                                                   on et.entity_type_id = ea.entity_type_id 
                                                                                   AND et.entity_type_code = 'customer'
                                                                                   ", "attribute_id", "attribute_code");
        return $this;
    }
	
    /**
     * Fill in the so_number for this customer's gift registry
     * @return \rdi_cart_gift_reg
     */
    public function fill_rdi_gift_reg_ref()
    {
            $this->db_connection->exec("REPLACE INTO rdi_gift_reg_ref (so_shipto_rpro_cust_sid, so_number) 
                                                                    select distinct so_shipto_rpro_cust_sid, so_number from rpro_in_gift_reg where so_shipto_rpro_cust_sid is not null");
            return $this;
    }
    
    /**
     * Load the _event table for all gift registries.
     * @return \magento_rdi_gift_reg_load
     */
    public function load_event()
    {
        $events = $this->db_connection->rows("SELECT DISTINCT
                    ae.event_id,
                    e.entity_id AS customer_id,
                    ad.value AS address_id,
                    0 AS type_id,
                    IF((NOW() > rg.date_ordered AND NOW() < ADDDATE(rg.cancel_date,INTERVAL + 1 DAY)) OR rg.cancel_date=rg.date_ordered,
                            'active',
                            'expired') AS `status`,
                    MD5(concat(rg.so_number,e.email)) AS sharing_code,
                    rg.date_ordered AS `date`,
                    0 AS search_allowed,
                    '' AS pass,
                    'My New Gift Registry' AS title,
                    firstname.value AS fname,
                    lastname.value AS lname,
                    '' AS fname2,
                    '' AS lname2,
                    e.email AS emails
                     FROM rpro_in_gift_reg rg
                    JOIN {$this->prefix}customer_entity e
                    ON e.related_id = so_shipto_cust_sid
                    JOIN {$this->prefix}customer_entity_int ad
                    ON ad.entity_id = e.entity_id
                    AND ad.attribute_id = {$this->_attribute_id['customer']['default_shipping']}
                    LEFT JOIN {$this->prefix}adjgiftreg_event ae
                    ON ae.customer_id = e.entity_id
                    AND ae.address_id = ad.value

                    JOIN {$this->prefix}customer_entity_varchar firstname
                    ON firstname.entity_id = e.entity_id
                    AND firstname.attribute_id = {$this->_attribute_id['customer']['firstname']}
                    JOIN {$this->prefix}customer_entity_varchar lastname
                    ON lastname.entity_id = e.entity_id
                    AND lastname.attribute_id = {$this->_attribute_id['customer']['lastname']}
                    WHERE rg.so_shipto_rpro_cust_sid IS NOT NULL");
        
        foreach($events as $event)
        {
            $this->process_event($event);
        }
        
        return $this;
    }
    
    /**
     * Load the _item table.
     * @return \magento_rdi_gift_reg_load
     */
    public function load_item()
    {
        $items = $this->db_connection->rows("SELECT DISTINCT
                                ai.item_id,
                                ae.event_id,
                                IFNULL(sl.parent_id,r.entity_id) AS product_id,
                                0 AS store_id,
                                0 AS hide,
                                item.qty_due - item.qty_ordered AS num_has,
                                item.qty_due AS num_wants,
                                row_num AS priority,
                                NOW() AS added_at,
                                '' AS descr,
                                '' AS buy_request,
                                IFNULL(sl.parent_id,r.entity_id)AS sku_product_id,
                                 1 AS num_total,
                                 0 AS num_user_set,
								 item.row_num as rdi_row_num,
								 item.qty_ordered,								 
                                IFNULL(sl.product_id, r.entity_id) AS ar_product 
                                FROM rpro_in_gift_reg item
                                JOIN {$this->prefix}customer_entity e
                                ON e.related_id = item.so_shipto_cust_sid
                                JOIN {$this->prefix}adjgiftreg_event ae
                                ON ae.customer_id = e.entity_id
                                JOIN {$this->prefix}catalog_product_entity_varchar r
                                ON r.value = item.item_sid
                                AND r.attribute_id = {$this->_attribute_id['catalog_product']['related_id']}
                                LEFT JOIN {$this->prefix}catalog_product_super_link sl
                                ON sl.product_id = r.entity_id
                                LEFT JOIN {$this->prefix}adjgiftreg_item ai
                                ON ai.product_id = IFNULL(sl.parent_id, r.entity_id) 
                                AND ai.event_id = ae.event_id
								and ai.rdi_row_num = item.row_num									
                                WHERE item.so_shipto_rpro_cust_sid IS NULL
								
								");
        
        foreach($items as $item)
        {
            $this->process_item($item);
        }
        return $this;
    }
   
    /**
     * Load the _item_option table.
	 I might have to add a column here to make give the row_num
     * @return \magento_rdi_gift_reg_load
     */
    public function load_option()
    {
        $options = $this->db_connection->rows("SELECT DISTINCT
                                                    ai.item_id,
                                                    r.entity_id AS _product_id,
                                                    sl.parent_id AS _parent_id,
                                                    item.qty_ordered AS qty,
                                                    '' AS gift_next
                                                     FROM rpro_in_gift_reg item
                                                    JOIN {$this->prefix}customer_entity e
                                                    ON e.related_id = item.so_shipto_cust_sid
                                                    JOIN {$this->prefix}adjgiftreg_event ae
                                                    ON ae.customer_id = e.entity_id
                                                    JOIN {$this->prefix}catalog_product_entity_varchar r
                                                    ON r.value = item.item_sid
                                                    AND r.attribute_id = {$this->_attribute_id['catalog_product']['related_id']}
                                                    LEFT JOIN {$this->prefix}catalog_product_super_link sl
                                                    ON sl.product_id = r.entity_id
                                                    JOIN {$this->prefix}adjgiftreg_item ai
                                                    ON ai.product_id = IFNULL(sl.parent_id, r.entity_id)
                                                    AND ai.event_id = ae.event_id
													AND ai.priority = item.row_num
                                                    WHERE item.so_shipto_cust_sid IS NOT NULL");
        
        foreach($options as $option)
        {
            $this->process_option($option);
        }
        
        return $this;
    }
    
    /**
     * Do any additional processing to the data that will go into the event. We will update 'fname','lname','emails','status'
     * @param array $data
     */
    public function process_event($data)
    {
        $this->db_connection->insertAr2("{$this->prefix}adjgiftreg_event", $data, false, array(),array('fname','lname','emails','status'),false);
    }
    
    /**
     * Do any additional processing to the data that will go into the event. We will update 'descr','priority','num_has','num_wants','buy_request'. There is a seriallized array here for buy_request.
     * @param array $data
     */
    public function process_item($data)
    {
        $buy_request = array();
        $buy_request['product'] = $data['ar_product'];
        $buy_request['related_product'] = '';
        $buy_request['super_attribute'] = $this->get_super_attribute($data['ar_product']);
        $buy_request['qty'] = (int) $data['qty_ordered'];
        $buy_request['gift_next'] = '';
              $this->_print_r($buy_request);
        $data['buy_request'] = serialize($buy_request);
        
        unset($buy_request);
        
        $this->db_connection->insertAr2("{$this->prefix}adjgiftreg_item", $data, false, array('ar_product','qty_ordered'),array('descr','priority','num_has','num_wants','buy_request'),false);
    }
    
    /**
     * Do any additional processing to the data that will go into the event. We will update 'descr','priority','num_has','num_wants','buy_request'. There is a seriallized array here for buy_request.
     * There are 5 lines added here for products with configurables and 2 if its a standalone simple
     * @param array $data
     */
    public function process_option($data)
    {
        //there are 5 of these. 3 product and 2 conf
        //1. info_buyRequest parent_id/product_id
        $data_insert = $data;
        $data_insert['product_id'] = isset($data['_parent_id'])?$data['_parent_id']:$data['_product_id'];
        $data_insert['code'] = 'info_buyRequest';
            
            $buy_request = array();
            $buy_request['product'] = $data['_product_id'];
            $buy_request['event']   = '0';
            $buy_request['related_product'] = '';
            $buy_request['super_attribute'] = $this->get_super_attribute($data['_product_id']);
            $buy_request['qty'] = (int) $data['qty'];
            $buy_request['gift_next'] = $data['gift_next'];
        
        $data_insert['value'] = serialize($buy_request);
        
        $this->db_connection->insertAr2("{$this->prefix}adjgiftreg_item_option", $data_insert, false, array('_parent_id','_product_id','gift_next'),array('qty','code','value'));
        
        //2. product_qty_ product_id
        $data_insert = $data;
        $data_insert['product_id'] = $data['_product_id'];
        $data_insert['code'] = "product_qty_{$data['_product_id']}";
        $data_insert['value'] = "1";
        
        $this->db_connection->insertAr2("{$this->prefix}adjgiftreg_item_option", $data_insert, false, array('_parent_id','_product_id','gift_next'),array('qty','code','value'));
        
        if(isset($data['_parent_id']) && $data['_parent_id'] !== null)
        {
            //3. simple_product product_id
            $data_insert['code'] = "simple_product";
            $data_insert['value'] = $data['_product_id'];

            $this->db_connection->insertAr2("{$this->prefix}adjgiftreg_item_option", $data_insert, false, array('_parent_id','_product_id','gift_next'),array('qty','code','value'));

            //4. parent_product_id parent_id
            $data_insert['code'] = "parent_product_id";
            $data_insert['value'] = $data['_parent_id'];

            $this->db_connection->insertAr2("{$this->prefix}adjgiftreg_item_option", $data_insert, false, array('_parent_id','_product_id','gift_next'),array('qty','code','value'));

            //5. attributes parent_id
			$data_insert['product_id'] = $data['_parent_id'];
            $data_insert['code'] = "attributes";
            $data_insert['value'] = serialize($buy_request['super_attribute']);


            $this->db_connection->insertAr2("{$this->prefix}adjgiftreg_item_option", $data_insert, false, array('_parent_id','_product_id','gift_next'),array('qty','code','value'));
        }
        
        
    }
    
    /**
     * Get the super attribute ids. Attribute_id/Option if from the int table.
     * @param int $product_id Product id that we want to get more information about their configurable attributes. Used three times per product.
     * @return type
     */
    public function get_super_attribute($product_id)
    {
        if(!isset($this->super_attributes[$product_id]))
        {
            $this->super_attributes[$product_id] = $this->db_connection->cells("SELECT sa.attribute_id, i.value FROM {$this->prefix}catalog_product_super_attribute sa
                                                                JOIN {$this->prefix}catalog_product_super_link sl
                                                                ON sl.parent_id = sa.product_id
                                                                AND sl.product_id = {$product_id}
                                                                JOIN {$this->prefix}catalog_product_entity_int i
                                                                ON i.entity_id = sl.product_id
                                                                AND i.attribute_id = sa.attribute_id",'value','attribute_id');
        }
        
        return $this->super_attributes[$product_id];
    }
	
    /**
     * Remove the item level. The product_id(id for the simple is locateg on the option level and we join down to get it
     * @return \rdi_cart_gift_reg
     */
    public function remove_item()
    {
            $this->db_connection->exec("delete 
                                            ai.* 
                                          FROM
                                            adjgiftreg_item ai 
                                            JOIN adjgiftreg_event ae 
                                                  ON ae.event_id = ai.event_id 
                                            JOIN customer_entity e 
                                                  ON e.entity_id = ae.customer_id
                                                  JOIN adjgiftreg_item_option aio
                                                  ON aio.item_id = ai.item_id
                                                  AND aio.code = 'simple_product'
                                            JOIN catalog_product_entity_varchar v 
                                                  ON v.entity_id = aio.value
                                                  AND v.attribute_id = 135 
                                            JOIN rpro_in_gift_reg gr 
                                                  ON gr.so_shipto_rpro_cust_sid = e.related_id 
                                            LEFT JOIN rpro_in_gift_reg item 
                                                  ON item.item_sid = v.value 
                                          WHERE item.item_sid IS NULL ");
            return $this;
    }
    
    /**
     * Remove the option level.
     * @return \rdi_cart_gift_reg
     */
    public function remove_option()
    {
        $this->db_connection->exec("DELETE aio.* FROM adjgiftreg_item_option aio
                                        LEFT JOIN adjgiftreg_item ai
                                        ON ai.item_id = aio.item_id
                                        WHERE ai.item_id IS NULL");


        return $this;
    }
	
    //These are functions in the so export ----------------------
    /**
     * Updates the so_ref on orders. Fill only do this if the load_gift_reg setting is turned on
     * @todo May make this into a seperate setting.
     * @global setting $load_gift_reg
     */
    public function update_so_ref()
    {
        global $load_gift_reg;

        //@setting $load_gift_reg [0-OFF, 1-ON] Turns on/of the loading of gift registries. Only supported in ECI/V8. This is using the setting around updating so_ref on out_so
        if(isset($load_gift_reg) && $load_gift_reg == 1)
        {
            $this->db_connection->exec("UPDATE rpro_out_so so
                                            JOIN {$this->prefix}sales_flat_order o
                                            ON o.increment_id = so.order_sid
                                            JOIN {$this->prefix}adjgiftreg_order ao
                                            ON ao.order_id = o.entity_id
                                            JOIN {$this->prefix}adjgiftreg_event ae
                                            ON ae.event_id = ao.event_id
                                            JOIN {$this->prefix}customer_entity ce
                                            ON ce.entity_id = ae.customer_id
                                            JOIN rdi_gift_reg_ref rr
                                            ON rr.so_shipto_rpro_cust_sid = ce.related_id
                                            SET so.so_ref = rr.so_number");
        }
    }

}
