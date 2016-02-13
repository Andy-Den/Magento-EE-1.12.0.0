<?php

/**
 * This updates the status and visibility of products depending on settings. Respects the availability status from a POS.
 * 
 * @author  PMBLISS <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @date    12272013
 * @package    Core\Load\Product\Magento\Saleable
 */

class rdi_cart_product_is_saleable extends rdi_general
{
	private $attribute_ids;
	//private $prefix;
	//private $db_connection;

    public function rdi_cart_product_is_saleable($db = '')
    {
        if ($db)
            $this->set_db($db);     

		$this->attributes = array('related_id','status','visibility','related_parent_id','rdi_deactivated_date');
    }
	
    public function pre_load()
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("cart_product_status_pre_load");
        
        return $this;
    }
    
    public function post_load()    
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("cart_product_status_post_load");
        
        return $this;
    }

	public function load()
	{	
		$this->pre_load()->set_attribute_ids($this->attributes)->update_availability()->post_load();
		
		return $this;
	}
	
	public function set_prefix($prefix)
	{
		$this->prefix = $prefix;
		return $this;
	}
	
	public function set_indexers()
	{
		$this->db_connection->exec("UPDATE `{$this->prefix}index_process` SET `status` = 'require_reindex' WHERE process_id in(1,2,3,4)");
	}
	
	// this is not used and can be used later.
	public function update_price()
	{
	
		$pricing_field = " ROUND(item.item_price1 * 1.2,1) ";
		$config_price_field = " (SELECT {$pricing_field} FROM rpro_in_prices WHERE rpro_in_prices.fldstylesid = style.fldstylesid ) ";

		//configs
		$this->db_connection->exec("INSERT INTO {$this->prefix}catalog_product_entity_decimal (value_id, entity_type_id, store_id, attribute_id, entity_id, VALUE)
						SELECT cped.value_id, r.entity_type_id, r.store_id, {$this->attribute_ids['price']} AS attribute_id, r.entity_id, {$config_price_field} AS VALUE FROM rpro_in_styles style
						JOIN rpro_in_styles item
						ON item.fldstylesid = style.fldstylesid
						AND item.record_type IN('item')
		
						JOIN {$this->prefix}catalog_product_entity_varchar r
						ON r.value = style.fldstylesid
						AND r.attribute_id = {$this->attribute_ids['related_id']} 
						LEFT JOIN {$this->prefix}catalog_product_entity_decimal cped
						ON cped.entity_id = r.entity_id
						AND cped.store_id = 0
						AND cped.attribute_id = {$this->attribute_ids['price']}
						WHERE cped.value != ifnull(CAST( {$config_price_field} AS DECIMAL(10,4)),'')
						AND style.record_type = 'style'
						AND style.flddcs is not null
						GROUP BY style.fldstylesid
						ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");

		//simples
		$this->db_connection->exec("INSERT INTO {$this->prefix}catalog_product_entity_decimal (value_id, entity_type_id, store_id, attribute_id, entity_id, VALUE)
						SELECT cped.value_id, r.entity_type_id, r.store_id, {$this->attribute_ids['price']} AS attribute_id, r.entity_id, {$pricing_field} AS VALUE FROM rpro_in_styles style
						JOIN rpro_in_styles item
						ON item.fldstylesid = style.fldstylesid
						JOIN {$this->prefix}catalog_product_entity_varchar r
						ON r.value = item.item_flditemsid
						AND item.record_type IN('item')
						
						AND r.attribute_id = {$this->attribute_ids['related_id']} 
						LEFT JOIN {$this->prefix}catalog_product_entity_decimal cped
						ON cped.entity_id = r.entity_id
						AND cped.store_id = 0
						AND cped.attribute_id = {$this->attribute_ids['price']}
						AND IFNULL(cped.value,'') != CAST( {$pricing_field} AS DECIMAL(10,4))
						WHERE style.record_type = 'style'
						AND style.flddcs is not null
						ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");

	
		return $this;
	}
	
    public function update_availability()
    {
        global  $update_availability;

        //handle the availability, make it optional setting
        if(isset($update_availability) && $update_availability > 0)
        {                   
			//this is no longer handled here. See the avail_updates cart class.
           /* $this->db_connection->exec("UPDATE {$this->prefix}cataloginventory_stock_item 
                                            JOIN {$this->prefix}catalog_product_entity cpe ON cpe.entity_id = {$this->prefix}cataloginventory_stock_item.product_id
                                            SET is_in_stock = 0
                                              WHERE  cpe.type_id = 'simple' and {$this->prefix}cataloginventory_stock_item.manage_stock = 1
                                              AND   qty <= min_qty");


            $this->db_connection->exec("UPDATE {$this->prefix}cataloginventory_stock_item 
                                            JOIN {$this->prefix}catalog_product_entity cpe ON cpe.entity_id = {$this->prefix}cataloginventory_stock_item.product_id
                                            SET is_in_stock = 1
                                              WHERE  cpe.type_id = 'simple' and {$this->prefix}cataloginventory_stock_item.manage_stock = 1
                                              AND   qty > min_qty");
			*/
            
            //Configurables
            //turn these off
            //SET is_in_stock = 0 
            $sql_off = "SELECT DISTINCT SUM({$this->prefix}cataloginventory_stock_item.qty) AS qty,parent_id product_id,{$this->prefix}cataloginventory_stock_item.min_qty , configurable.is_in_stock FROM {$this->prefix}catalog_product_super_link 
                    INNER JOIN {$this->prefix}cataloginventory_stock_item 
                    ON {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_super_link.product_id
                    JOIN {$this->prefix}cataloginventory_stock_item configurable
                    ON configurable.product_id = {$this->prefix}catalog_product_super_link.parent_id
                    WHERE configurable.is_in_stock = 1 
					AND (configurable.manage_stock = 1
						AND configurable.backorders = 0)
                    GROUP BY {$this->prefix}catalog_product_super_link.parent_id
                    HAVING COUNT({$this->prefix}cataloginventory_stock_item.qty <= {$this->prefix}cataloginventory_stock_item.min_qty) > 0";

            $_ids = $this->db_connection->cells($sql_off,"product_id");	

            if(!empty($_ids))
            {
                $ids = implode(",",$_ids);
                $sql = "UPDATE {$this->prefix}cataloginventory_stock_item 
                                SET is_in_stock = 0
                              WHERE  product_id IN({$ids}) 
                                AND {$this->prefix}cataloginventory_stock_item.manage_stock = 1 
                                AND is_in_stock = 1";

                $this->db_connection->exec($sql);
            }

            unset($ids, $_ids, $sql);

            // turn these on
            //SET is_in_stock = 1 
            $sql = "SELECT DISTINCT  SUM({$this->prefix}cataloginventory_stock_item.qty) AS qty,parent_id product_id,{$this->prefix}cataloginventory_stock_item.min_qty , configurable.is_in_stock FROM {$this->prefix}catalog_product_super_link 
                    INNER JOIN {$this->prefix}cataloginventory_stock_item 
                    ON {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_super_link.product_id
                    JOIN {$this->prefix}cataloginventory_stock_item configurable
                    ON configurable.product_id = {$this->prefix}catalog_product_super_link.parent_id
                    WHERE configurable.is_in_stock = 0 
					AND (configurable.manage_stock = 1
						OR configurable.backorders > 0)
					
                    GROUP BY {$this->prefix}catalog_product_super_link.parent_id
                    HAVING COUNT({$this->prefix}cataloginventory_stock_item.qty > {$this->prefix}cataloginventory_stock_item.min_qty) > 0
                    ";

            $_ids = $this->db_connection->cells($sql,"product_id");	

            if(!empty($_ids))
            {
                $ids = implode(",",$_ids);
                $sql = "UPDATE {$this->prefix}cataloginventory_stock_item 
                                SET is_in_stock = 1
                              WHERE  product_id IN({$ids}) 
                                AND {$this->prefix}cataloginventory_stock_item.manage_stock = 1 
                                AND is_in_stock = 0";

                $this->db_connection->exec($sql);
            }

            $rows = $this->db_connection->rows($sql);

            unset($ids, $_ids, $sql);

        }
		return $this;
    }
    
    /*This can be done with the same queries as in the catalog load.
     * Will do this for now, but reduce the number of queries.
     */
    public function process_out_of_stock()
    {
        global $hide_out_of_stock, $disable_out_of_stock, $store_id, $field_mapping, $pos;

        //make out of stock not visible, or in stock visible
        if((isset($hide_out_of_stock) && $hide_out_of_stock == 1) || (isset($disable_out_of_stock) && $disable_out_of_stock == 1))
        {        
            //get the visibility attribute id        
            $related_parent_attribute_id = $this->attribute_ids['related_parent_id'];
            $related_attribute_id = $this->attribute_ids['related_id'];
            $visibility_attribute_id = $this->attribute_ids['visibility'];
            $status_attribute_id = $this->attribute_ids['status'];

            #check configurable qty

            $sql = "SELECT DISTINCT SUM(qty) AS qty,parent_id product_id,min_qty, manage_stock, backorders FROM {$this->prefix}catalog_product_super_link 
                            INNER JOIN {$this->prefix}cataloginventory_stock_item 
                            ON {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_super_link.product_id
                            INNER JOIN {$this->prefix}catalog_product_entity_varchar related_id
                            ON related_id.entity_id = {$this->prefix}catalog_product_super_link.parent_id
                            AND related_id.attribute_id = {$related_attribute_id}
                            INNER JOIN " . $pos->get_processor("rdi_staging_db_lib")->get_table_name('in_items') . " style on " .
                                           $field_mapping->map_field('product', 'related_id', 'configurable') . " = related_id.value                        
                            GROUP BY {$this->prefix}catalog_product_super_link.parent_id;";


            $inventory_array = $this->db_connection->rows($sql);

            $this->set_visibility_status_array($inventory_array);

            #stand alone simples qty
            
            $sql = "SELECT DISTINCT qty AS qty,{$this->prefix}catalog_product_entity.entity_id product_id,min_qty, manage_stock, backorders FROM {$this->prefix}catalog_product_entity 
                                    LEFT JOIN {$this->prefix}catalog_product_super_link
                                        ON {$this->prefix}catalog_product_entity.entity_id = {$this->prefix}catalog_product_super_link.product_id
                                        AND {$this->prefix}catalog_product_entity.type_id = 'simple'
                                    INNER JOIN {$this->prefix}cataloginventory_stock_item 
                                        ON {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_entity.entity_id                                
                                    INNER JOIN {$this->prefix}catalog_product_entity_varchar related_id
                                        ON related_id.entity_id = {$this->prefix}catalog_product_entity.entity_id
                                        AND related_id.attribute_id = {$related_attribute_id}
                                    INNER JOIN " . $pos->get_processor("rdi_staging_db_lib")->get_table_name('in_items') . " item on " .
                                                  $field_mapping->map_field('product','related_id','simple') . " = related_id.value
                                    WHERE {$this->prefix}catalog_product_super_link.product_id IS NULL
                                        AND {$this->prefix}catalog_product_entity.type_id = 'simple';";

            $inventory_array = $this->db_connection->rows($sql);

            $this->set_visibility_status_array($inventory_array);
        }
    }
    
    public function set_visibility_status_array($product_array)
    {
        global $hide_out_of_stock, $disable_out_of_stock, $store_id;

        #get visibility and status attribute
		$related_parent_attribute_id = $this->attribute_ids['related_parent_id'];
		$related_attribute_id = $this->attribute_ids['related_id'];
		$visibility_attribute_id = $this->attribute_ids['visibility'];
		$status_attribute_id = $this->attribute_ids['status'];
		$deactivated_date_attribute = $this->attribute_ids['rdi_deactivated_date'];

        //status and visibility arrays
        $visibility_on = array();
        $visibility_on_else = array();
        $visibility_off = array();
        $status_on_else = array();
        $status_on = array();
        $status_off = array();
        
        
        if(is_array($product_array))
        {
            foreach($product_array as $row)
            {
				//out of stock
                if((($row['qty'] < $row['min_qty']) || $row['qty'] == 0) && $row['manage_stock'] == 1 && $row['backorders'] == 0)
                {
                    if(!in_array($row['product_id'],$visibility_off))
                    {
                        $visibility_off[] = $row['product_id'];
                    }
                    
                    if(!in_array($row['product_id'],$status_off))
                    {
                        $status_off[] = $row['product_id'];
                    }
                    //update visibility
                    
                }
                else if (($row['qty'] > $row['min_qty']) && $row['manage_stock'] == 1 && $row['backorders'] > 0)
                {
                    if(!in_array($row['product_id'],$visibility_on))
                    {
                        $visibility_on[] = $row['product_id'];
                    }
                    if(!in_array($row['product_id'],$status_on))
                    {    
                        $status_on[] = $row['product_id'];
                    }
                }
                else 
                {
                    if(!in_array($row['product_id'],$visibility_on_else))
                    {
                        $visibility_on_else[] = $row['product_id'];
                    }
                    if(!in_array($row['product_id'],$status_on_else))
                    {
                         $status_on_else[] = $row['product_id'];
                    }
                }
            }
            
            $this->process_visibility_status_array($visibility_off,$status_off,$visibility_on,$status_on,$visibility_on_else,$status_on_else);
        }	
    }
    
    
    public function process_visibility_status_array($visibility_off,$status_off,$visibility_on,$status_on,$visibility_on_else,$status_on_else)
    {
        global $cart,$disable_out_of_stock, $hide_out_of_stock;
        
        $visibility_attribute_id = $cart->get_processor('rdi_cart_common')->set_attribute('visibility')->get_attribute_id('visibility');
        $status_attribute_id = $cart->get_processor('rdi_cart_common')->set_attribute('status')->get_attribute_id('status');
       
        
        if(isset($hide_out_of_stock) && $hide_out_of_stock == 1)
        {
            if(!empty($visibility_off))
            {
                $visibility_off_ = implode(",",$visibility_off);
                
                $sql = "UPDATE {$this->prefix}catalog_product_entity_int 
                        SET {$this->prefix}catalog_product_entity_int.value = 1 
                        WHERE {$this->prefix}catalog_product_entity_int.entity_id IN({$visibility_off_})
                        AND {$this->prefix}catalog_product_entity_int.attribute_id = {$visibility_attribute_id}
                        AND value != 1";
                    
                $this->db_connection->exec($sql);
            }
        }
        
        #update enabled?
        if(isset($disable_out_of_stock) && $disable_out_of_stock == 1)
        {
            if(!empty($status_off))
            {
                $status_off_ = implode(",",$status_off);
                
                $sql = "UPDATE {$this->prefix}catalog_product_entity_int 
                        SET {$this->prefix}catalog_product_entity_int.value = 2 
                        WHERE {$this->prefix}catalog_product_entity_int.entity_id IN({$status_off_})
                        AND {$this->prefix}catalog_product_entity_int.attribute_id = {$status_attribute_id}
                        AND {$this->prefix}catalog_product_entity_int.value != 2 "; 
                
                $this->db_connection->exec($sql); 
            }
        }
        
        #update visibility
        if(isset($hide_out_of_stock) && $hide_out_of_stock == 1)
        {
            if(!empty($visibility_on))
            {
                $visibility_on_ = implode(",",$visibility_on);
              
                $sql = "UPDATE {$this->prefix}catalog_product_entity_int 
                        SET {$this->prefix}catalog_product_entity_int.value = 4
                        WHERE {$this->prefix}catalog_product_entity_int.entity_id IN({$visibility_on_})
                        AND {$this->prefix}catalog_product_entity_int.attribute_id = {$visibility_attribute_id}
                        AND {$this->prefix}catalog_product_entity_int.value != 4";
                       
                $this->db_connection->exec($sql); 
            }
        }
        
        #update enabled?
        if(isset($disable_out_of_stock) && $disable_out_of_stock == 1)
        {
            if(!empty($status_on))
            {
                $status_on_ = implode(",",$status_on);
                
                $sql = "UPDATE {$this->prefix}catalog_product_entity_int 
                        SET {$this->prefix}catalog_product_entity_int.value = 1
                        WHERE {$this->prefix}catalog_product_entity_int.entity_id IN({$status_on_})
                        AND {$this->prefix}catalog_product_entity_int.attribute_id = {$status_attribute_id}
                        AND {$this->prefix}catalog_product_entity_int.value != 1 "; 
                
                $this->db_connection->exec($sql); 
            } 
        }
        
        #update visibility
        if(isset($hide_out_of_stock) && $hide_out_of_stock == 1)
        {
            if(!empty($visibility_on_else))
            {
                $visibility_on_else_ = implode(",",$visibility_on_else);
              
                $sql = "UPDATE {$this->prefix}catalog_product_entity_int 
                        SET {$this->prefix}catalog_product_entity_int.value = 4
                        WHERE {$this->prefix}catalog_product_entity_int.entity_id IN({$visibility_on_else_})
                        AND {$this->prefix}catalog_product_entity_int.attribute_id = {$visibility_attribute_id}
                        AND {$this->prefix}catalog_product_entity_int.value != 4";
                        
                $this->db_connection->exec($sql); 
            }
        }
        
        #update enabled?
        if(isset($disable_out_of_stock) && $disable_out_of_stock == 1)
        {
            if(!empty($status_on_else))
            {
                $status_on_else_ = implode(",",$status_on_else);
                
                $sql = "UPDATE {$this->prefix}catalog_product_entity_int 
                        SET {$this->prefix}catalog_product_entity_int.value = 1
                        WHERE {$this->prefix}catalog_product_entity_int.entity_id IN({$status_on_else_})
                        AND {$this->prefix}catalog_product_entity_int.attribute_id = {$status_attribute_id}
                        AND {$this->prefix}catalog_product_entity_int.value != 1 "; 
                
                $this->db_connection->exec($sql); 
            } 
        }
        
    }
	
	public function set_attribute_ids($_attribute)
	{
		$attributes = "'" . implode("','", $_attribute) . "'";
		$this->attribute_ids = $this->db_connection->cells("select attribute_code,attribute_id from {$this->prefix}eav_attribute ea
											join {$this->prefix}eav_entity_type et
											on et.entity_type_id = ea.entity_type_id
											where ea.attribute_code in({$attributes})
											and entity_type_code = 'catalog_product'","attribute_id","attribute_code");
											
		return $this;
	}
	
	
	
}

?>