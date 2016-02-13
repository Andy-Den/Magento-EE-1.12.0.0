<?php
/**
 * The magento delete product class.
 */

/**
 * Description of rpro8_rdi_pos_common
 *
 * @settings $deactivated_delete_time
 * 
 * @author PBliss
 * @package Core\Delete\Magento
 */
class rdi_cart_delete extends rdi_general
{
      /**
     * Class Constructor
     *
     * @param rdi_cart_delete $db
     */
    
    private $hook_name;
    private $_attributes;
    private $product_entity_type_id;
    
    public function rdi_cart_delete($db = '')
    {
        if ($db)
            $this->set_db($db);    
        
        $this->hook_name = str_replace("rdi_", "", __CLASS__);
    }
    
     /*
    *Called pre load of the functions for the pos module
    */
    public function pre_load()
    {
        global $hook_handler;       
        
        $hook_handler->call_hook($this->hook_name . "_" . __FUNCTION__);
        
        return $this;
    }
    
    /*
    *Called pos load of the functions for the pos module
    */
    public function post_load()
    {
        global $hook_handler;       
        
        $hook_handler->call_hook($this->hook_name . "_" . __FUNCTION__);
        
        return $this;
    }
    
    /*
    *Called pos load of the functions for the pos module
    */
    public function load()
    {
        global $hook_handler;       
        
        $this->pre_load()->mark_to_delete()->create_to_delete_report()->post_load();
        
        return $this;
    }
    
    public function mark_to_delete()       
    {
        global $deactivated_delete_time;
        
        //here not worries about how long these have been there just using the setting to say start collecting data.
        if(isset($deactivated_delete_time) && $deactivated_delete_time > 0)
        {
            $this->get_attributes();
            
            // add these to the list
            $this->db_connection->exec("INSERT INTO {$this->prefix}catalog_product_entity_datetime (entity_id, entity_type_id, store_id, attribute_id, `value`)
                                        SELECT 
                                        e.entity_id,
                                        {$this->product_entity_type_id} as entity_type_id,
                                        0 as store_id,
                                        {$this->_attributes['rdi_deactivated_date']} as attribute_id,
                                        NOW() as value
                                        FROM {$this->prefix}catalog_product_entity e
                                        
                                        LEFT JOIN {$this->prefix}catalog_product_super_link sl
                                        ON sl.product_id = e.entity_id
                                        
                                        LEFT JOIN {$this->prefix}catalog_category_product cp
                                        ON cp.product_id = e.entity_id
                                        
                                        LEFT JOIN {$this->prefix}catalog_product_entity_datetime dt
                                        on dt.entity_id = e.entity_id
                                        and dt.attribute_id = {$this->_attributes['rdi_deactivated_date']}

                                        WHERE sl.product_id IS NULL
                                        AND cp.product_id IS NULL
                                        AND dt.entity_id IS NULL
                                        ");
                                        
            // delete these from the list
            $this->db_connection->exec("DELETE dt.*
                                        FROM {$this->prefix}catalog_product_entity e
                                        LEFT JOIN {$this->prefix}catalog_product_super_link sl
                                        ON sl.product_id = e.entity_id
                                        
                                        INNER JOIN {$this->prefix}catalog_category_product cp
                                        ON cp.product_id = e.entity_id
                                        
                                        JOIN {$this->prefix}catalog_product_entity_datetime dt
                                        ON dt.entity_id = e.entity_id
                                        AND dt.attribute_id = {$this->_attributes['rdi_deactivated_date']}

                                        WHERE sl.product_id IS NULL");
                                        
            
        }
        
        return $this;
    }
    
    public function create_to_delete_report()
    {
        global $create_delete_report, $create_delete_report_email, $deactivated_delete_time_post, $deactivated_delete_time;
        
        $month = date('M');
        $year = date('Y');
        
        if(isset($create_delete_report) && $create_delete_report > 0
                && $create_delete_report >= date('d')
                && !file_exists("out/todelete_{$month}_{$year}_{$create_delete_report}.csv"))
        {
           
           $this->get_attributes();  
           
           $vis_sql = "CASE true
                            WHEN vis.value = 4 THEN 'Catalog/Search'
                            WHEN vis.value = 3 THEN 'Search'
                            WHEN vis.value = 2 THEN 'Catalog'
                            WHEN vis.value = 1 THEN 'Not Individually'
                            ELSE 'N/A'
                        END
                            ";
           
           $st_sql = "CASE true
                            WHEN st.value = 1 THEN 'Enabled'
                            WHEN st.value = 2 THEN 'Disabled'
                            ELSE 'N/A'
                        END
                            ";
           
                    
            $sql = "SELECT e.sku 'Magento Sku',
                e.entity_id 'Magento ID',
                name.value 'name',
                itemnum.value AS 'retail pro item num',
                e.type_id,  
                dt.value as 'RDi Deactivated Date',
                IFNULL(slp.parent_id,0) AS 'Has Children', 
                r.value AS 'related_id', 
                rp.value AS 'related_parent_id',
                {$vis_sql} AS visibility, 
                {$st_sql} AS STATUS, 
				
				sum(csi.qty) as 'Total Quantity',
                GROUP_CONCAT(slp.product_id SEPARATOR '-' ) as 'Children',
				GROUP_CONCAT(csi.qty SEPARATOR '-') AS 'Children Quantities'
				
                FROM {$this->prefix}catalog_product_entity e
                LEFT JOIN {$this->prefix}catalog_product_super_link sl
                ON sl.product_id = e.entity_id
                LEFT JOIN {$this->prefix}catalog_category_product cp
                ON cp.product_id = e.entity_id
                JOIN {$this->prefix}catalog_product_entity_varchar `name`
                ON name.entity_id = e.entity_id
                AND name.attribute_id = {$this->_attributes['name']}
                LEFT JOIN {$this->prefix}catalog_product_super_link slp
                ON slp.parent_id = e.entity_id
                LEFT JOIN {$this->prefix}catalog_product_entity_varchar itemnum
                ON itemnum.entity_id = e.entity_id
                AND itemnum.attribute_id = {$this->_attributes['itemnum']}
                LEFT JOIN {$this->prefix}catalog_product_entity_varchar r
                ON r.entity_id = e.entity_id
                AND r.attribute_id = {$this->_attributes['related_id']}
                LEFT JOIN {$this->prefix}catalog_product_entity_varchar rp
                ON rp.entity_id = e.entity_id
                AND rp.attribute_id = {$this->_attributes['related_parent_id']}
                JOIN {$this->prefix}catalog_product_entity_int vis
                ON vis.entity_id = e.entity_id
                AND vis.attribute_id = {$this->_attributes['visibility']}
                JOIN {$this->prefix}catalog_product_entity_int st
                ON st.entity_id = e.entity_id
                AND st.attribute_id = {$this->_attributes['status']}
				
				JOIN cataloginventory_stock_item csi 
				ON csi.product_id = slp.product_id    
                
                JOIN {$this->prefix}catalog_product_entity_datetime dt
                ON dt.entity_id = e.entity_id
                AND dt.attribute_id = {$this->_attributes['rdi_deactivated_date']}
				AND dt.value < ADDDATE(NOW(), INTERVAL - {$deactivated_delete_time} DAY)
				
                WHERE sl.product_id IS NULL
                AND cp.product_id IS NULL
                GROUP BY e.entity_id";
            
            $to_delete = $this->db_connection->rows($sql);
            
            if(is_array($to_delete))
            {	
                $fp = fopen("out/todelete_{$month}_{$year}_{$create_delete_report}.csv", "w");
                
				fputcsv($fp, array("THESE PRODUCTS WILL DELETED IN {$deactivated_delete_time_post} Days"), ",", '"');
				
                $headers = array_keys($to_delete[0]);
                
                fputcsv($fp, $headers, ",", '"');
                
                foreach($to_delete as $fields)
                {
                    fputcsv($fp, $fields, ",", '"');
                }

                fclose($fp);
				
				$this->send_email("Delete Products Report","out/todelete_{$month}_{$year}_{$create_delete_report}.csv");
				
            }
        }
        
        return $this;
    }
    
    //get all attributes that might be used during this process.
    public function get_attributes()
    {
        $attribute_names = "'related_id','related_parent_id','name','itemnum','visibility','status','rdi_deactivated_date'";

        $entity_type_code = "catalog_product";

        if(!isset($this->_attributes) || empty($this->_attributes))
        {
        
            $this->_attributes        = $this->db_connection->cells("SELECT 
                                                         attribute_code,attribute_id FROM {$this->prefix}eav_attribute
                                                        INNER JOIN {$this->prefix}eav_entity_type on {$this->prefix}eav_entity_type.entity_type_id = {$this->prefix}eav_attribute.entity_type_id
                                                        WHERE attribute_code in('related_id','related_parent_id',{$attribute_names}) 
                                                        AND {$this->prefix}eav_entity_type.entity_type_code = '{$entity_type_code}'","attribute_id","attribute_code");
        }
        
        if(!isset($this->product_entity_type_id))
        {
            $this->product_entity_type_id = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = '{$entity_type_code}'");
        }
        
        return true;
    }
    
    /*
     * If we delete products have to check to see there is not any products on the flat table(s).
     */
    public function clean_product_flat()
    {
        $this->db_connection()->exec("select now()");
        
        return $this;        
    }
	
	public function send_email($subject,$filename)
	{
		global $create_delete_report_email, $create_delete_report, $deactivated_delete_time, $deactivated_delete_time_post;
		$this->_echo($create_delete_report_email);
        if(isset($create_delete_report_email) && $create_delete_report_email !== 0)
        {	
			$this->_echo("send email");
            //send email here from the cart. 
			if(!class_exists("magento_rdi_email_lib"))
			{
				include 'libraries/cart_libs/magento/magento_rdi_email_lib.php';
			}	
			
			$rdi_email = new magento_rdi_email_lib();
			$message = "
			Hello,<br><br>
			
			This report is supplied on the {$create_delete_report} of every month. Each product has been inactive and removed from point of sale catalog for at least {$deactivated_delete_time} days. If no action is taken, the products will be removed from the Magento catalog in {$deactivated_delete_time_post} days.\n\r
			Please Review the attached report.<br><br>
			Sincerely yours,<br><br>
			Retail Dimensions Support Team.	<br><br>
			5036445301
			[retaildimensions image]
			";
			$this->_print_r($message);
			$rdi_email->send_email($message,$subject,$filename);
        }
	}
            
       
}

?>
