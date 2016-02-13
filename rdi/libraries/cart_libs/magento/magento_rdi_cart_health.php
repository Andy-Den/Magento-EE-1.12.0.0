<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of magento_rdi_cart_health
 *
 * @author PMBliss
 */
class rdi_cart_health extends rdi_general {
    //put your code here
    
	private $class_return;
	
    /**
     * Class Constructor
     *
     * @param rdi_catalog_load $db
     */
    public function rdi_cart_health($db = '')
    {
        if ($db)
            $this->set_db($db);        
			
		$this->class_return = array();
    }
            
    public function pre_load()
    {
        global $hook_handler; 
                
        $hook_handler->call_hook("cart_health_pre_load");
        
		return $this;
    }
    
    public function post_load()    
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("cart_health_load_post_load");
		
		return $this;
    }
	
	public function get_class_return()
	{
		return $this->class_return;
	}
    
    public function load()    
    {
        $this->pre_load()->sales_total()->sostatus()->log_url_quote_sales()->core_url()->post_load();
		
		return $this;
	}
    
    public function sales_total()
    {
	
		$this->_echo(__CLASS__ . ": " . __FUNCTION__);
		
        $this->class_return['sales_totals_downloaded'] = $this->db_connection->rows("SELECT SUM(sales_flat_order.base_grand_total) as grand_total, 
                                    count(*) as orders_downloaded 
                                    FROM sales_flat_order WHERE rdi_upload_status > 0");
				
		$this->_print_r($this->class_return['sales_totals_downloaded']);
		
		$this->class_return['sales_totals_not_downloaded'] = $this->db_connection->rows("SELECT SUM(sales_flat_order.base_grand_total) as grand_total, 
                                    count(*) as orders_not_downloaded 
                                    FROM sales_flat_order WHERE rdi_upload_status = 0");
				
		$this->_print_r($this->class_return['sales_totals_not_downloaded']);
        
        $this->class_return['sales_totals_completed_and_downloaded'] = $this->db_connection->rows("SELECT SUM(sales_flat_order.base_grand_total) as grand_total, 
                                    count(*) as orders_completed_and_downloaded 
                                    FROM sales_flat_order WHERE rdi_upload_status > 1 and status = 'complete'");
		
		$this->_print_r($this->class_return['sales_totals_completed_and_downloaded']);
		
		$this->class_return['sales_totals_completed_not_downloaded'] = $this->db_connection->rows("SELECT SUM(sales_flat_order.base_grand_total) as grand_total, 
                                    count(*) as orders_completed_and_not_downloaded 
                                    FROM sales_flat_order WHERE rdi_upload_status = 0 and status = 'complete'");
		
		$this->_print_r($this->class_return['sales_totals_completed_not_downloaded']);
		
		return $this;
    }
	
	public function core_url()
    {
	
		$this->_echo(__CLASS__ . ": " . __FUNCTION__);
		
        $this->_echo("Core Url Stats");
        $this->class_return['url_stats'] = $this->db_connection->rows("SELECT 'products' AS rewrite_type, COUNT(*) as 'count' FROM core_url_rewrite WHERE product_id IS NOT NULL
														UNION
														SELECT 'bad products' AS rewrite_type, COUNT(*) as 'count' FROM core_url_rewrite WHERE product_id IS NOT NULL AND id_path LIKE '%\_%'
														UNION
														SELECT 'categories' AS rewrite_type, COUNT(*) as 'count' FROM core_url_rewrite WHERE category_id IS NOT NULL AND product_id IS NULL
														UNION
														SELECT 'bad categories' AS rewrite_type, COUNT(*) as 'count' FROM core_url_rewrite WHERE category_id IS NOT NULL AND product_id IS NULL AND id_path LIKE '%\_%'
														UNION 
														SELECT 'total' AS rewrite_type, COUNT(*) as 'count' FROM core_url_rewrite");
		
		//$this->table(json_encode($url_stats));
		$this->_print_r($this->class_return['url_stats']);
		
		
		$this->_echo("Duplicate SKUs");
		$this->class_return['duplicate_skus'] = $this->db_connection->rows("SELECT sku, COUNT(*) FROM catalog_product_entity GROUP BY sku HAVING COUNT(DISTINCT sku) > 1");
		
		$this->_print_r($this->class_return['duplicate_skus']);
		
		
		return $this;
    }
	
	public function sostatus()
	{
		global $pos_type;
	
		if($pos_type == 'rpro8')
		{
			$this->_echo(__CLASS__ . ": " . __FUNCTION__);
		
			$this->_echo("SO STATUSES");
			$this->class_return['sostatus'] = $this->db_connection->rows("SELECT DISTINCT sfo.state, sfo.status,pt.txn_type, sfo.increment_id, ADDDATE(sfo.created_at, INTERVAL - 6 HOUR) AS 'order_created_date' , ADDDATE(pt.created_at, INTERVAL - 6 HOUR) AS capture_status_date, ris.rdi_import_date AS so_status_date,rir.rdi_import_date AS receipt_import_date,  sfo.grand_total, sfo.base_total_due, sfo.base_total_invoiced,
		ris.CaptureFund, ris.STATUS, ris.Total,
		rir.receipt_number, rir.receipt_sid, rir.receipt_subtotal
		 FROM sales_flat_order sfo
		LEFT JOIN (SELECT * FROM rpro_in_so_log ORDER BY rdi_import_date DESC LIMIT 2000) AS  ris
		ON ris.SID = sfo.increment_id
		LEFT JOIN (SELECT * FROM rpro_in_receipts_log ORDER BY rdi_import_date DESC LIMIT 2000) AS  rir
		ON rir.so_number = ris.so_number
		LEFT JOIN sales_payment_transaction pt
		ON pt.order_id = sfo.entity_id
		ORDER BY sfo.increment_id DESC LIMIT 1000");
			
			$this->_print_r($this->class_return['sostatus']);
		}
		
		return $this;
		
	}
	
	public function log_url_quote_sales()
	{
		$this->_echo(__CLASS__ . ": " . __FUNCTION__);
		
        $this->_echo("Log URL");
        $this->class_return['Log URL'] = $this->db_connection->rows("select http_referer, count(*) as 'Count' From log_visitor_info vi 
																		where vi.http_referer is not null
																		group by vi.http_referer 
																		having count(http_referer) > 1
																		order by 2 desc");

		$this->_print_r($this->class_return['Log URL']);
																		
		$this->_echo("Log URL Quote Sale");
        $this->class_return['Log URL Quote Sale'] = $this->db_connection->rows("select vi.http_referer, 
																		q.created_at as 'Quote Date', q.items_qty, q.base_subtotal_with_discount as total, 
																		o.created_at as 'Order Date', o.increment_id,  o.base_subtotal from log_visitor_info vi
																		join log_quote lq
																		on lq.visitor_id = vi.visitor_id
																		join sales_flat_quote q
																		on q.entity_id = lq.quote_id
																		left join sales_flat_order o
																		on o.entity_id = q.orig_order_id
																		where vi.http_referer IS NOT NULL 
																		order by q.created_at desc
																		
																		limit 1000");
		
		$this->_print_r($this->class_return['Log URL Quote Sale']);
		
		return $this;
		
	}
	  
}

?>
