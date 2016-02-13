<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Product load class
 *
 * Handles the loading of the health data, does the traffic cop work on that part
 *
 * PHP version 5.3
 *
 * @author     Paul Bliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 */
class rdi_health extends rdi_general {

    public function rdi_health($db = '')
    {
        if ($db)
            $this->set_db($db);

        $this->health = array();
    }

    public function pre_load()
    {
        global $hook_handler;

        $hook_handler->call_hook(__CLASS__ . "_" . __FUNCTION__);

        return $this;
    }

    public function post_load()
    {
        global $hook_handler;

        $hook_handler->call_hook(__CLASS__ . "_" . __FUNCTION__);

        return $this;
    }

    public function load()
    {
        if ($this->get_search())
        {
            
        }
        else if (!$this->get_logs() && !$this->get_product() && !$this->get_category() && !$this->get_sales_flat() && !$this->get_history() && !$this->get_attribute() && !$this->get_sales_order_item() && !$this->get_prefs() && !$this->get_rdi_mapping())
        {
            //$this->pre_load()->load_cart()->load_pos()->post_load();
        }

        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        echo json_encode($this->health);

        return $this;
    }

    public function load_cart()
    {
        global $cart;

        $this->health['cart'] = $cart->get_processor("rdi_cart_health")->load()->get_class_return();

        return $this;
    }

    public function load_pos()
    {
        global $pos;

        $this->health['pos'] = $pos->get_processor("rdi_pos_health")->load()->get_class_return();

        return $this;
    }

    public function get_rdi_mapping()
    {
        if (isset($_POST['mapping']))
        {
            $mapping_type = $_POST['mapping'];

            switch ($mapping_type)
            {
                case 'field_mapping':

                    if (isset($_POST['field_mapping_id']) && is_numeric($_POST['field_mapping_id']))
                    {
                        $field_mapping_id = $_POST['field_mapping_id'];
                    }
                    else
                    {
                        return false;
                    }

                    $this->health['field_mapping'] = $this->db_connection->row("SELECT * FROM rdi_field_mapping WHERE field_mapping_id = '{$field_mapping_id}'");

                    $this->health['field_mapping_pos'] = $this->db_connection->rows("SELECT * FROM rdi_field_mapping_pos WHERE field_mapping_id = '{$field_mapping_id}' ORDER BY field_order");

                    break;

                case 'save_field_mapping':

                    if (isset($_POST['field_mapping_id']) && is_numeric($_POST['field_mapping_id']) && isset($_POST['entity_type']) && isset($_POST['form_data'])
                    )
                    {
                        $field_mapping_id = $_POST['field_mapping_id'];
                        $entity_type = $_POST['entity_type'];
                        parse_str($_POST['form_data'], $form_data);
                    }
                    else
                    {
                        return false;
                    }
                    /*
                      echo $field_mapping_id;
                      echo PHP_EOL;
                      echo $entity_type;
                      echo PHP_EOL;
                      print_r($form_data);
                      echo PHP_EOL;
                     */
                    //update the cart
                    //change null to NULL
                    foreach ($form_data['cart'] as &$cart_value)
                    {
                        $cart_value = $cart_value === 'null' ? strtoupper($cart_value) : $cart_value;
                    }

                    $this->db_connection->insertAr2('rdi_field_mapping', $form_data['cart'], false, array(), true);

                    if (isset($form_data['pos']) && !empty($form_data['pos']))
                    {
                        //clear out the pos table for this field_mapping_id
                        $this->db_connection->exec("DELETE FROM rdi_field_mapping_pos WHERE field_mapping_id = '{$this->db_connection->clean($field_mapping_id)}'");

                        foreach ($form_data['pos'] as $pos_row)
                        {
                            foreach ($pos_row as &$pos_value)
                            {
                                $pos_value = $pos_value === 'null' ? strtoupper($pos_value) : $pos_value;
                            }

                            $pos_row['field_mapping_id'] = $field_mapping_id;

                            $this->db_connection->insertAr2('rdi_field_mapping_pos', $pos_row, false, array(), true);
                        }
                    }

                    $this->health = "saved";


                    break;
            }
        }
    }

    public function get_search()
    {
        if (isset($_POST['query']) && isset($_POST['admin_name']))
        {
            $a = $this->db_connection->cells("SELECT ea.attribute_id, ea.attribute_code FROM {$this->prefix}eav_attribute ea
		join {$this->prefix}eav_entity_type et
		on et.entity_type_id = ea.entity_type_id
		and et.entity_type_code = 'catalog_product'", 'attribute_id', 'attribute_code');

            $data = array();

            $query = $_POST['query'];
            $admin_name = $_POST['admin_name'];

            $product_rows = $this->db_connection->rows("SELECT concat('product/1/',entity_id) as id, concat('/index.php/{$admin_name}/catalog_product/edit/id/',entity_id) as url, concat('[Product-',entity_id,']') as `div`, value, "
                    . "IF(attribute_id = {$a['related_id']},'Related ID','Related Parent ID') as name "
                    . "FROM {$this->prefix}catalog_product_entity_varchar WHERE value like '%{$query}%' and attribute_id in({$a['related_id']},{$a['related_parent_id']}) limit 30");

            if (!empty($product_rows))
            {
                $data = array_merge($data, $product_rows);
            }

            $sales_rows = $this->db_connection->rows("SELECT 'Order #' as name, concat('sales_order/1/',entity_id) as id, concat('/index.php/{$admin_name}/sales_order/view/order_id/',entity_id) as url, concat('[Order-',entity_id,']') as `div`, increment_id as value FROM {$this->prefix}sales_flat_order WHERE increment_id like '%{$query}%'  limit 30");

            if (!empty($sales_rows))
            {
                $data = array_merge($data, $sales_rows);
            }

            // echo "<ul>" . count($data) . "</ul>";exit;

            echo $this->toHtmlList($data);
            exit;


            return true;
        }

        return false;
    }

    public function get_logs()
    {
        if (isset($_POST['rdi_log']) && in_array($_POST['rdi_log'], array('debug', 'error', 'loadtimes')))
        {
            $this->health = $this->db_connection->rows("SELECT * FROM rdi_{$_POST['rdi_log']}_log ORDER BY 1 DESC limit 1000");

            if (!isset($this->health) || $this->health == null || $this->health == '')
            {
                $this->health = "No Data";
            }

            return true;
        }

        return false;
    }

    //this will return the pos history by style,item
    //in_so,in_so_item,out_so,out_so_item for this product.
    public function get_product()
    {
        global $db_lib, $pos_type;


        if (isset($_POST['get_product']) && isset($_POST['related_id']) && isset($_POST['related_parent_id']))
        {
            $this->item_quantity_history();

            $this->health['style'] = $this->db_connection->rows("SELECT distinct * FROM {$db_lib->get_table_name('in_styles_log')} style 
			WHERE {$db_lib->get_style_criteria()}
			AND style.{$db_lib->get_style_sid()} = '{$_POST['related_parent_id']}' 			
			ORDER BY rdi_import_date DESC");

            $this->health['item'] = $this->db_connection->rows("SELECT distinct * FROM {$db_lib->get_table_name('in_items')}_log item
			WHERE item.{$db_lib->get_style_sid()} = '{$_POST['related_parent_id']}'
			{$db_lib->get_item_criteria()}
			ORDER BY rdi_import_date DESC");

            $a = $this->db_connection->cells("SELECT ea.attribute_id, ea.attribute_code FROM {$this->prefix}eav_attribute ea
				join {$this->prefix}eav_entity_type et
				on et.entity_type_id = ea.entity_type_id
				and et.entity_type_code = 'catalog_product'", 'attribute_id', 'attribute_code');

            if ($pos_type == 'rpro9')
            {
                $this->health['categories'] = $this->db_connection->rows("SELECT distinct  cp.*,e.entity_id as 'category_id' FROM rpro_in_category_products_log cp
				left join {$this->prefix}catalog_category_entity e
				on e.related_id = cp.catalog_id
				left join {$this->prefix}catalog_product_entity_varchar rp
				on rp.value = cp.style_sid
				and rp.attribute_id = '{$a['related_parent_id']}'
				where cp.style_sid = '{$_POST['related_parent_id']}'
				ORDER BY rdi_import_date DESC, catalog_id");
            }


            if ($pos_type == 'rpro8')
            {
                $this->health['categories'] = $this->db_connection->rows("SELECT distinct  cp.*,e.entity_id as 'category_id' FROM rpro_in_catalog_log cp
				left join {$this->prefix}catalog_category_entity e
				on e.related_id = cp.SID
				left join {$this->prefix}catalog_product_entity_varchar rp
				on rp.value = cp.style_sid
				and rp.attribute_id = '{$a['related_parent_id']}'
				where cp.style_sid = '{$_POST['related_parent_id']}'
				and cp.caption IS NULL
				ORDER BY rdi_import_date DESC, SID");
            }


            /*

              $this->health['out_so'] = $this->db_connection->rows("SELECT so.* FROM {$db_lib->get_table_name('out_so_log')} so
              join {$db_lib->get_table_name('out_so_log')} so_item
              on so_item.order_id
              WHERE item.{$db_lib->get_style_sid()} = '{$_POST['related_parent_id']}'
              {$db_lib->get_item_criteria()}
              ORDER BY rdi_import_date DESC");
             */
            return true;
        }

        return false;
    }

    //this should be in the libraries for cart and pos, but done this way to get it working.
    public function get_category()
    {
        global $db_lib, $pos_type;

        $a = $this->db_connection->cells("SELECT ea.attribute_id, ea.attribute_code FROM {$this->prefix}eav_attribute ea
		join {$this->prefix}eav_entity_type et
		on et.entity_type_id = ea.entity_type_id
		and et.entity_type_code = 'catalog_product'", 'attribute_id', 'attribute_code');

        if (isset($_POST['get_category']) && isset($_POST['entity_id']))
        {
            if ($pos_type == 'rpro9')
            {
                $this->health['category'] = $this->db_connection->rows("SELECT distinct category.* FROM rpro_in_categories_log category 
				join {$this->prefix}catalog_category_entity e
				on e.related_id = category.catalog_id
				and e.entity_id = '{$_POST['entity_id']}' 			
				ORDER BY rdi_import_date DESC");

                $this->health['category_products'] = $this->db_connection->rows("SELECT distinct cp.*,rp.entity_id as 'product_id' FROM rpro_in_category_products_log cp
				join {$this->prefix}catalog_category_entity e
				on e.related_id = cp.catalog_id
				and e.entity_id = '{$_POST['entity_id']}'
				left join {$this->prefix}catalog_product_entity_varchar rp
				on rp.value = cp.style_sid
				and rp.attribute_id = '{$a['related_parent_id']}'
				ORDER BY rdi_import_date DESC, sort_order");
            }

            if ($pos_type == 'rpro8')
            {
                $this->health['category'] = $this->db_connection->rows("SELECT distinct category.* FROM rpro_in_catalog_log category 
				join {$this->prefix}catalog_category_entity e
				on e.related_id = category.SID
				and e.entity_id = '{$_POST['entity_id']}'
				WHERE caption is not null
				ORDER BY rdi_import_date DESC");

                $this->health['category_products'] = $this->db_connection->rows("SELECT distinct cp.*,rp.entity_id as 'product_id' FROM rpro_in_catalog_log cp
				join {$this->prefix}catalog_category_entity e
				on e.related_id = cp.SID
				and e.entity_id = '{$_POST['entity_id']}'
				left join {$this->prefix}catalog_product_entity_varchar rp
				on rp.value = cp.style_sid
				and rp.attribute_id = '{$a['related_parent_id']}'
				WHERE cp.caption is null
				ORDER BY rdi_import_date DESC, style_orderno");
            }


            $this->health['product_saleability'] = $this->db_connection->rows("SELECT distinct 
				cp.product_id as 'product_id', 
				ifnull(st.value,'Missing Record') as 'Status',
				ifnull(vis.value,'Missing Record') as 'Visibility',
				ifnull(csi.is_in_stock,'Missing Record') as 'In Stock',
				ifnull(csi.manage_stock,'Missing Record') as 'Manage Stock',
				ifnull(csi.backorders,'Missing Record') as 'Back Orders',
				ifnull(has_sl.parent_id,'No') as 'Has Parent',
				IF(sl.parent_id IS NULL,'No','Yes') as 'Is Parent',
				'1' as rdi_import_date
				FROM {$this->prefix}catalog_category_entity e
				join {$this->prefix}catalog_category_product cp
				on cp.category_id = e.entity_id
				
				left join {$this->prefix}catalog_product_entity_int st
				on st.entity_id = cp.product_id
				and st.attribute_id = '{$a['status']}'
				
				left join {$this->prefix}catalog_product_entity_int vis
				on vis.entity_id = cp.product_id
				and vis.attribute_id = '{$a['visibility']}'
				
				left join {$this->prefix}catalog_product_super_link has_sl
				on has_sl.product_id = cp.product_id
				
				left join {$this->prefix}catalog_product_super_link sl
				on sl.parent_id = cp.product_id
				
				left join {$this->prefix}cataloginventory_stock_item csi
				on csi.product_id = cp.product_id
				
				WHERE e.entity_id = '{$_POST['entity_id']}'
				ORDER BY cp.product_id");

            if (!empty($this->health['product_saleability']))
            {
                foreach ($this->health['product_saleability'] as &$product)
                {
                    if ($product['Is Parent'] == 'Yes')
                    {
                        $product['Simples Status'] = $this->db_connection->cell("SELECT count(*) as c from {$this->prefix}catalog_product_super_link sl
							
								join {$this->prefix}catalog_product_entity_int st
								on st.entity_id = sl.product_id
								and st.attribute_id = '{$a['status']}'
								and st.value = 1
														
								join {$this->prefix}cataloginventory_stock_item csi
								on csi.product_id = sl.product_id
								AND (csi.is_in_stock = 1 OR csi.manage_stock = 0 OR csi.backorders > 0)
								WHERE sl.parent_id = '{$product['product_id']}'
							", "c");
                    }
                    else
                    {
                        $product['Simples Status'] = 'No Children';
                    }
                }
            }


            /*

              $this->health['out_so'] = $this->db_connection->rows("SELECT so.* FROM {$db_lib->get_table_name('out_so_log')} so
              join {$db_lib->get_table_name('out_so_log')} so_item
              on so_item.order_id
              WHERE item.{$db_lib->get_style_sid()} = '{$_POST['related_parent_id']}'
              {$db_lib->get_item_criteria()}
              ORDER BY rdi_import_date DESC");
             */
            return true;
        }

        return false;
    }

    public function get_sales_flat()
    {
        global $db_lib, $pos_type;

        if (isset($_POST['get_sales_flat']) && isset($_POST['order_id']))
        {
            $this->health['upload_status'] = $this->db_connection->row("SELECT o.rdi_upload_status, o.rdi_shipper_created, o.rdi_upload_date, o.shipping_method, o.status, p.method as payment_method FROM {$this->prefix}sales_flat_order o
			left join {$this->prefix}sales_flat_order_payment p
			on p.parent_id = o.entity_id	
			WHERE o.entity_id = '{$_POST['order_id']}'");

            if ($pos_type == 'rpro9')
            {
                $this->health['rpro_out_so_log'] = $this->db_connection->rows("SELECT r.* FROM {$this->prefix}sales_flat_order o
				join rpro_out_so_log r
				on r.orderid = o.increment_id
				WHERE o.entity_id = '{$_POST['order_id']}'");

                $this->health['rpro_out_so_items_log'] = $this->db_connection->rows("SELECT r.* FROM {$this->prefix}sales_flat_order o
				join rpro_out_so_items_log r
				on r.orderid = o.increment_id
				WHERE o.entity_id = '{$_POST['order_id']}'");


                $this->health['rpro_in_so_log'] = $this->db_connection->rows("SELECT r.* FROM {$this->prefix}sales_flat_order o
				join rpro_in_so_log r
				on r.so_number like concat('%',o.increment_id)
				WHERE o.entity_id = '{$_POST['order_id']}'");
            }

            if ($pos_type == 'rpro8')
            {
                $this->health['rpro_out_so_log'] = $this->db_connection->rows("SELECT r.* FROM {$this->prefix}sales_flat_order o
				join rpro_out_so_log r
				on r.order_sid = o.increment_id
				WHERE o.entity_id = '{$_POST['order_id']}'");

                $this->health['rpro_out_so_items_log'] = $this->db_connection->rows("SELECT r.* FROM {$this->prefix}sales_flat_order o
				join rpro_out_so_items_log r
				on r.order_sid = o.increment_id
				WHERE o.entity_id = '{$_POST['order_id']}'");


                $this->health['rpro_in_so_log'] = $this->db_connection->rows("SELECT r.sid, r.* FROM {$this->prefix}sales_flat_order o
				join rpro_in_so_log r
				on r.sid = o.increment_id
				WHERE o.entity_id = '{$_POST['order_id']}'");

                $this->health['rpro_in_receipts_log'] = $this->db_connection->rows("SELECT distinct r.* FROM {$this->prefix}sales_flat_order o
				join rpro_in_so_log ro
				on ro.sid = o.increment_id
				join rpro_in_receipts r
				on r.so_number = ro.so_number			
				WHERE o.entity_id = '{$_POST['order_id']}'");
            }

            return true;
        }

        return false;
    }

    public function get_history()
    {
        global $pos;


        if (isset($_POST['get_history']))
        {
            $this->health = $pos->get_processor("rdi_pos_health")->staging_tables()->get_class_return();

            return true;
        }

        return false;
    }

    public function get_sales_order_item()
    {
        global $db_lib, $pos_type;


        if (isset($_POST['get_sales_order_item']) && isset($_POST['order_item_id']) && isset($_POST['order_item_id']))
        {
            $this->health['order_item'] = $this->db_connection->rows("SELECT * FROM {$this->prefix}sales_flat_order_item where item_id = {$_POST['order_item_id']}");

            return true;
        }

        return false;
    }

    public function get_attribute()
    {

        if (isset($_POST['get_attribute_code']))
        {
            $attribute_code = $_POST['get_attribute_code'];
            $this->health = $this->db_connection->row("SELECT ea.attribute_id FROM {$this->prefix}eav_attribute ea
									join {$this->prefix}eav_entity_type et
									on et.entity_type_id = ea.entity_type_id
									and et.entity_type_code = 'catalog_product'
									where ea.attribute_code = '{$attribute_code}'");

            return true;
        }

        return false;
    }

    public function get_process_list()
    {

        if (isset($_POST['get_process_list']))
        {
            $this->health = $this->db_connection->rows("SHOW FULL PROCESSLIST");

            return true;
        }

        return false;
    }

    public function get_product_sids()
    {

        if (isset($_POST['get_product_sids']) && isset($_POST['ids']))
        {
            $related_id = $this->get_attribute_id('related_id');

            $_ids = explode(",", $_POST['ids']);

            if (!empty($_ids))
            {

                $this->health = $this->db_connection->cells("select e.entity_id, v.value FROM {$this->prefix}catalog_product_entity e
																LEFT JOIN {$this->prefix}catalog_product_entity_varchar v
																ON v.entity_id = e.entity_id
																AND v.attribute_id = {$related_id}
																WHERE e.entity_id in({$_POST['ids']})", "value", "entity_id");

                return true;
            }
        }

        return false;
    }

    public function get_attribute_id($attribute_code)
    {
        return $this->db_connection->cell("SELECT ea.attribute_id FROM {$this->prefix}eav_attribute ea
								join {$this->prefix}eav_entity_type et
								on et.entity_type_id = ea.entity_type_id
								and et.entity_type_code = 'catalog_product'
								where ea.attribute_code = '{$attribute_code}'", 'attribute_id');
    }

    public function toHtmlList($data)
    {
        $_ul = array();

        if (!empty($data))
        {
            foreach ($data as $d)
            {
                $li = "<li id=\"{$d['id']}\" url=\"{$d['url']}\">";
                $li .= "	<div style=\"float:right; color:red; font-weight:bold;\">{$d['div']}</div>";
                $li .= "	<strong>{$d['name']}</strong><br/>";
                $li .= "	<span class=\"informal\">{$d['value']}</span>";
                $li .= "</li>";

                $_ul[] = $li;
            }
        }

        return "<ul>" . implode("", $_ul) . "</ul>";
    }

    public function get_prefs()
    {
        global $pos_type;

        if (isset($_POST['get_prefs']))
        {
            if ($pos_type == 'rpro8')
            {
                $tables = $this->db_connection->rows("SHOW TABLES LIKE 'rpro_in_prefs%'");

                foreach ($tables as $table)
                {
                    $keys = array_keys($table);
                    $table_name = $table[$keys[0]];

                    $this->health[$table_name] = $this->db_connection->rows("SELECT @id := @id + 1 AS id, {$table_name}.* FROM {$table_name},(SELECT @id:=0) AS c ");
                }
            }


            $this->health['tax_area'] = $this->db_connection->rows("SELECT @id := @id + 1 AS id, t.* FROM rdi_tax_area_mapping t,(SELECT @id:=0) AS c ");
            $this->health['tax_class'] = $this->db_connection->rows("SELECT @id := @id + 1 AS id, t.*  FROM rdi_tax_class_mapping t,(SELECT @id:=0) AS c ");
            $this->health['mage_shipping'] = $this->db_connection->rows("select t.*  FROM rpro_mage_shipping t");


            $this->health['shipping_examples'] = $this->db_connection->rows("SELECT DISTINCT @id := @id + 1 AS id, shipping_description, shipping_method, MAX(entity_id) 'Order Id', MAX(created_at) 'Last Used'  FROM {$this->prefix}sales_flat_order t,(SELECT @id:=0) c GROUP BY CONCAT(shipping_description, shipping_method) ORDER BY 4 DESC");

            return true;
        }
        return false;
    }

    public function item_quantity_history()
    {
        global $pos_type;

        if (!$this->db_connection->column_exists("{$this->prefix}sales_flat_order_item", "related_id"))
        {
            $this->health['item_history'] = "<h3>This is only compatible with a website setup with the sid on the sales_flat_order_item table. Its only a few lines of code to update.</h3>";
            return true;
        }

        $sid = $_POST['related_id'];

        if ($pos_type == "rpro8")
        {
            $sql = "SELECT 'inventory' AS 'Action', item_flditemsid, item_flditemnum AS 'SKU', item_fldupc 'UPC\nOrder', item_price1, item_price2, item_availquantity AS Quantity, rdi_import_date FROM rpro_in_styles_log WHERE item_flditemsid IN ('{$sid}')

                    UNION
                    SELECT 'order',related_id, sku,o.increment_id, '', '', qty_ordered, CONVERT_TZ(o.created_at,'+00:00','-8:00') FROM {$this->prefix}sales_flat_order_item i
                    JOIN {$this->prefix}sales_flat_order o
                    ON o.entity_id = i.order_id
                     WHERE i.related_id = '{$sid}'
                     UNION
                     SELECT 'order_export', item_sid, item_no,order_sid,price, orig_price, qty_ordered, rdi_export_date FROM rpro_out_so_items_log WHERE item_sid = '{$sid}'
                     UNION


                    SELECT 'receipts',i.sid,i.receipt_item_number, i.receipt_number,i.extprc, i.extpwt,i.qty, i.rdi_import_date  FROM rpro_in_receipts_log i
                    JOIN rpro_in_receipts_log r
                    ON r.receipt_number = i.receipt_number
                    AND r.receipt_type = 0
                    AND r.rdi_import_date = i.rdi_import_date
                     WHERE i.sid = '{$sid}'
                     ";
            if (!$this->db_connection->column_exists("rpro_in_receipts_log", "record_type"))
            {
                $sql .= " UNION SELECT 'returns',i.sid,i.receipt_item_number, i.receipt_number,i.extprc, i.extpwt,i.qty,  i.rdi_import_date  FROM rpro_in_receipts_log i
                        JOIN rpro_in_receipts_log r
                        ON r.receipt_number = i.receipt_number
                        AND r.receipt_type = 2
                        AND r.rdi_import_date = i.rdi_import_date
                         WHERE i.sid = '{$sid}' 
                         UNION
                        SELECT CONCAT('so-',r.status),i.item_sid,i.sid, i.receipt_number,i.shipdate, i.shipnumber,i.qtyshipped,  i.rdi_import_date  FROM rpro_in_so_log i
                        JOIN rpro_in_so_log r
                        ON r.sid = i.sid
                        AND r.rdi_import_date = i.rdi_import_date
                        AND r.record_type = 'So'
                         WHERE i.item_sid = '{$sid}'";
            }
        }

        //rpro9
        //rpro4web
        //cp


        $this->health['item_history'] = $this->db_connection->rows($sql);
    }

    public function toHtmlTable($rows, $header_columns = false)
    {
        $table = "<table>";

        if (!$header_columns)
        {
            $header_columns = array_keys($row[0]);
        }


        $table .= "<thead><tr class=\"headings\">";

        foreach ($header_columns as $header)
        {
            $table .= "<th><span class=\"nobr\"><a href=\"#\" name=\"date\" title=\"asc\" class=\"not-sort\"><span class=\"sort-title\">{$header}</span></a></span></th>";
        }

        $table .= "</tr></thead><tbody>";

        foreach ($rows as $id => $row)
        {
            $table .= "<tr id=\"row-{$id}\">";

            foreach ($row as $name => $field)
            {
                $table .= "<td class=\"title\"title=\"{$name}\" >{$field}</td>";
            }

            $table .= "</tr>";
        }

        $table = "</tbody></table>";

        return $table;
    }

}

?>