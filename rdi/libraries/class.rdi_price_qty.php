<?php
//not ready yet.
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


    /**
     * Description of class
     *
     * @author PMBliss <pmbliss@retaildimensions.com>
     * @copyright (c) 2005-2014 Retail Dimensions Inc.
     * @package Core
     */
class rdi_price_qty extends rdi_general 
{

    private $stockModel;
    public $ids;
    public $related_id;

    public function __construct($db)
    {
        global $cart;

        $this->related_id = $cart->get_processor('rdi_cart_common')->get_attribute('related_id');
        $this->related_parent_id = $cart->get_processor('rdi_cart_common')->get_attribute('related_parent_id');

        parent::__construct($db);
    }

    public function update_price_field($cart_field, $pos_field, $sid, $view = false)
    {

        global $cart;

        $price_attribute = $cart->get_processor('rdi_cart_common')->get_attribute($cart_field);

        $table = "rpro_in" . ($view ? "_prices" : "") . "_priceqty";

        $this->db_connection->mysqli_link->multi_query("update  (SELECT @update_id := null) AS u,
		  {$table} item
		  JOIN {$this->prefix}catalog_product_entity_varchar r
			ON r.value = item.{$sid}
			AND r.attribute_id = {$this->related_id}
		  JOIN {$this->prefix}catalog_product_entity_decimal d
			ON d.entity_id = r.entity_id
			and d.attribute_id = {$price_attribute}
			SET d.value = {$pos_field}

			where d.value != {$pos_field}
			and
			( SELECT @update_id := IF(d.value != {$pos_field},CONCAT_WS(',', d.entity_id, @update_id),@update_id) );
			select @update_id;");


        $ids = "";
        do
        {
            if ($res = $this->db_connection->mysqli_link->store_result())
            {
                $_ids = $res->fetch_assoc();
                $ids .= $_ids['@update_id'];
                $res->free();
            }
            elseif ($cart->get_db()->mysqli_link->error)
            {
                echo "Error executing query: (" . $cart->get_db()->mysqli_link->errno . ") " . $cart->get_db()->mysqli_link->error;
            }
        } while ($cart->get_db()->mysqli_link->more_results() && $cart->get_db()->mysqli_link->next_result());

        return explode(",", $ids);
    }

    //A call back function set up below is called during the rows loop to return all q
    public function get_product_quantities_for_update($quantity_field)
    {
        global $db_lib;

        return $this->db_connection->rows("SELECT DISTINCT
											  r.entity_id,
											  (
												{$quantity_field}
											  ) AS qty,
											  (
												(
												  (
													{$quantity_field}
												  ) > min_qty
												  AND (qty <= min_qty OR is_in_stock = 0)
												  AND backorders = 0
												)
												OR (
												  (
												   {$quantity_field}
												  ) <= min_qty
												  AND(qty > min_qty OR is_in_stock = 1)
												  AND backorders = 0
												)
											  ) AS stock_status_changed
											FROM
											  rpro_in_priceqty item
											  JOIN {$this->prefix}catalog_product_entity_varchar r
												ON r.value = item.{$db_lib->get_item_sid()}
												AND r.attribute_id = {$this->related_id}
											  JOIN {$this->prefix}cataloginventory_stock_item i
												ON i.product_id = r.entity_id
											WHERE i.qty != (
												{$quantity_field}
											  )
											  AND i.manage_stock = 1", '', 'updateQuantity');
    }

    public function load()
    {
        global $ids, $db_lib;

        //-----------------------------------------------------------------------------
        //update qty
        //-----------------------------------------------------------------------------
        $quantity_field = $this->db_connection->cell("select mp.pos_field from rdi_field_mapping m
														join rdi_field_mapping_pos mp
														on mp.field_mapping_id = m.field_mapping_id
														where cart_field = 'qty' and entity_type = 'simple' and field_type = 'product'", 'pos_field');

        $products_count = $this->get_product_quantities_for_update($quantity_field);



        if (!empty($ids))
        {
            $_ids = implode(",", $ids);

            $this->db_connection->exec("UPDATE
									  rpro_in_priceqty item
									  JOIN {$this->prefix}catalog_product_entity_varchar r
										ON r.value = item.{$db_lib->get_item_sid()}
										AND r.attribute_id = {$this->related_id}
									  JOIN {$this->prefix}cataloginventory_stock_item i
										ON i.product_id = r.entity_id
										and i.product_id in({$_ids})
										SET i.qty = ({$quantity_field})

										");
        }

        //-----------------------------------------------------------------------------
        //UPDATE prices
        //-----------------------------------------------------------------------------
        $special_product_ids = array_merge($this->update_price_field('special_price', 'item.sale_price', $db_lib->get_item_sid()), $this->update_price_field('special_price', 'item.sale_price', $db_lib->get_style_sid(), true));

        $price_product_ids = array_merge($this->update_price_field('price', 'item.reg_price', $db_lib->get_item_sid()), $this->update_price_field('price', 'item.reg_price', $db_lib->get_style_sid(), true));


        $clean_cache = false;

        if (!empty($special_product_ids))
        {
            Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($price_product_ids);

            $clean_cache = true;
        }

        if (!empty($price_product_ids))
        {
            Mage::getResourceModel('catalog/product_indexer_price')->reindexProductIds($special_product_ids);
            $clean_cache = true;
        }

        if ($clean_cache)
        {
            Mage::app()->cleanCache();
        }
    }

}
