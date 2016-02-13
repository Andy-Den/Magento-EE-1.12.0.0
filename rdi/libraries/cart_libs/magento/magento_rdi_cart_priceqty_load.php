<?php

/**
 * This is going to untilize a new field_mapping class that exists as a parent of load/export.
 *
 * @package Core\Multistore\Magento
 */
class rdi_cart_priceqty_load extends rdi_pos_priceqty_load {

    const CART_STORE_TABLE = "rdi_storeinventory_inventory";
    const CART_STORE_ALIAS = "inventory";
    const CART_STORE_KEY = "store_code";
    const CART_STORE_ID = "entity_id";
    const CART_STORE_QTY = "qty";
    const CART_STORE_QTY_TABLE = "rdi_storeinventory_inventory_product";
    const CART_STORE_QTY_ALIAS = "inventory_product";
    const CART_STORE_QTY_PRODUCT_ID = "product_id";
    const CART_STORE_QTY_PARENT_ID = "inventory_id";
    const CART_PRODUCT_RELATED_ID_TABLE = "catalog_product_entity_varchar";
    const CART_PRODUCT_RELATED_ID_ALIAS = "related_id";
    const CART_PRODUCT_RELATED_ID_KEY = "value";
    const CART_PRODUCT_ID = "entity_id";
    const CART_QTY_TABLE = "cataloginventory_stock_item";
    const CART_QTY_KEY = "product_id";
    const CART_QTY_FIELD_QTY = "qty";
    const CART_QTY_ALIAS = "csi";
    const CART_PRICE_TABLE = "catalog_product_entity_decimal";
    const CART_PRICE_KEY = "entity_id";
    const CART_PRICE_FIELD = "value";
    const CART_PRICE_ALIAS = "price";
    const CART_PRICE_FIELD_NAME = "price";
    const CART_SPECIAL_PRICE_FIELD_NAME = "special_price";

    public $cart;

    //public $cart_mapping = array("fields" => "", "join" => "");
    //there is no insert.   
    public function insert()
    {
        global $cart;
        $this->cart = $cart->get_processor("cart_field_mapping");
        return $this;
    }

    public function update()
    {
        global $cart;

        if (!$this->test_setting(__FUNCTION__))
        {
            return $this;
        }

        $cart->get_processor("rdi_cart_common")->load_magento();
        include_once __DIR__ . DIRECTORY_SEPARATOR . 'magento_rdiInventoryUpdate.php';
        //this is a callback function for out rows query.
        $GLOBALS['stockModel'] = new rdiInventoryUpdate();
        $GLOBALS['ids'] = array();

        $this->update_inventory()->update_prices();


        return $this;
    }

    public function update_inventory()
    {
        global $ids;

        if (!$this->test_setting(__FUNCTION__))
        {
            return $this;
        }

        //This should be
        $this->related_id = $this->cart->get_attribute('related_id');

        $quantity_field = $this->look_up_pos_field('qty', 'simple', 'product');

        //testing update
        //$this->db_connection->exec("UPDATE {$this->PRICEQTY_TABLE} {$this->PRICEQTY_ALIAS} SET {$quantity_field} = {$quantity_field} + round(rand(10) * 10)");

        $products_count = $this->get_product_quantities_for_update($quantity_field);

        if (!empty($ids))
        {
            $_ids = implode(",", $ids);


            //$this->_echo("UPDATE
            $this->db_connection->exec("UPDATE
									  {$this->PRICEQTY_TABLE} {$this->PRICEQTY_ALIAS}
									  JOIN {$this->prefix}{$this->CART_PRODUCT_RELATED_ID_TABLE} {$this->CART_PRODUCT_RELATED_ID_ALIAS}
										ON {$this->CART_PRODUCT_RELATED_ID_ALIAS}.{$this->CART_PRODUCT_RELATED_ID_KEY} = {$this->PRICEQTY_ALIAS}.{$this->PRICEQTY_KEY}
										AND {$this->CART_PRODUCT_RELATED_ID_ALIAS}.attribute_id = {$this->related_id}
									  JOIN {$this->prefix}{$this->CART_QTY_TABLE} {$this->CART_QTY_ALIAS}
										ON {$this->CART_QTY_ALIAS}.{$this->CART_QTY_KEY} = {$this->CART_PRODUCT_RELATED_ID_ALIAS}.entity_id
										and {$this->CART_QTY_ALIAS}.{$this->CART_QTY_KEY} in({$_ids})
										SET {$this->CART_QTY_ALIAS}.{$this->CART_QTY_FIELD_QTY} = ({$quantity_field})");
        }


        return $this;
    }

    public function update_prices()
    {
        if (!$this->test_setting(__FUNCTION__))
        {
            return $this;
        }

        global $db_lib;

        //$this->db_connection->exec("UPDATE {$this->PRICEQTY_TABLE} {$this->PRICEQTY_ALIAS} SET {$this->PRICEQTY_PRICE_FIELD} = {$this->PRICEQTY_PRICE_FIELD} + round(rand(10) * 10)");

        $special_product_ids = array_merge($this->update_price_field($this->CART_SPECIAL_PRICE_FIELD_NAME, "{$this->PRICEQTY_ALIAS}.{$this->PRICEQTY_SPECIAL_PRICE_FIELD}", $db_lib->get_item_sid()), $this->update_price_field($this->CART_SPECIAL_PRICE_FIELD_NAME, "{$this->PRICEQTY_ALIAS}.{$this->PRICEQTY_SPECIAL_PRICE_FIELD}", $db_lib->get_style_sid()));

        $price_product_ids = array_merge($this->update_price_field($this->CART_PRICE_FIELD_NAME, "{$this->PRICEQTY_ALIAS}.{$this->PRICEQTY_PRICE_FIELD}", $db_lib->get_item_sid()), $this->update_price_field($this->CART_PRICE_FIELD_NAME, "{$this->PRICEQTY_ALIAS}.{$this->PRICEQTY_PRICE_FIELD}", $db_lib->get_style_sid()));

        // need to set the special date here to null if there is no special date or set it if there is. If the special price is set and its less than the price, need to remove the date.. ugh that's a lot.

        $this->_var_dump($price_product_ids);
        $this->_var_dump($special_product_ids);


        $clean_cache = false;

        $special_price_date_changes = $this->update_special_price_dates();

        if (!empty($special_price_date_changes))
        {
            $special_product_ids = array_merge($special_product_ids, $special_price_date_changes);
            $clean_cache = true;
        }

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

        return $this;
    }

    //everything called here is specific to magento. Should not be joining to the point of sale.
    public function cart_load()
    {
        //might need to do some stock avail updates, ext.
        //$this->add_update_parents_to_store();

        return $this;
    }

    //use this for other updates.
    public function add_update_parents_to_store()
    {
        //need a reverse mapping here to get the quantity field.

        $store_inventory_products = $this->db_connection->rows("SELECT DISTINCT 
																sl.parent_id as {$this->CART_STORE_QTY_PRODUCT_ID}, 
																{$this->CART_STORE_QTY_ALIAS}.{$this->CART_STORE_QTY_PARENT_ID},
																SUM({$this->CART_STORE_QTY_ALIAS}.{$this->CART_STORE_QTY}) as {$this->CART_STORE_QTY}
																FROM {$this->CART_STORE_QTY_TABLE} {$this->CART_STORE_QTY_ALIAS}
																JOIN catalog_product_super_link sl
																ON sl.product_id = {$this->CART_STORE_QTY_ALIAS}.{$this->CART_STORE_QTY_PRODUCT_ID}
																GROUP BY sl.parent_id");

        if (!empty($store_inventory_products))
        {
            foreach ($store_inventory_products as $store_inventory_product)
            {
                $this->db_connection->insertAr2("{$this->CART_STORE_QTY_TABLE}", $store_inventory_product, false, array(), false, false);
            }
        }
        return $this;
    }

    private $stockModel;
    public $ids;
    public $related_id;
    public $pos_type;

    public function update_price_field($cart_field, $pos_field, $sid, $view = false)
    {
        $price_attribute = $this->cart->get_attribute($cart_field);
        $this->_print_r($cart_field);
        $table = "{$this->PRICEQTY_TABLE}" . ($view ? "_prices" : "");

        $sql = "UPDATE  (SELECT @update_id := null) AS u,
		  {$table} {$this->PRICEQTY_ALIAS}
		  JOIN {$this->prefix}{$this->CART_PRODUCT_RELATED_ID_TABLE} {$this->CART_PRODUCT_RELATED_ID_ALIAS}
			ON {$this->CART_PRODUCT_RELATED_ID_ALIAS}.{$this->CART_PRODUCT_RELATED_ID_KEY} = {$this->PRICEQTY_ALIAS}.{$sid}
			AND {$this->CART_PRODUCT_RELATED_ID_ALIAS}.attribute_id = {$this->related_id}
		  LEFT JOIN {$this->prefix}{$this->CART_PRICE_TABLE} {$this->CART_PRICE_ALIAS}
			ON {$this->CART_PRICE_ALIAS}.{$this->CART_PRICE_KEY} = {$this->CART_PRODUCT_RELATED_ID_ALIAS}.{$this->CART_PRODUCT_ID}
			and {$this->CART_PRICE_ALIAS}.attribute_id = {$price_attribute}
			SET {$this->CART_PRICE_ALIAS}.{$this->CART_PRICE_FIELD} = {$pos_field}

			WHERE ifnull({$this->CART_PRICE_ALIAS}.{$this->CART_PRICE_FIELD},0.0000) != ifnull({$pos_field},0.0000)
			and
			( SELECT @update_id := IF(
				ifnull({$this->CART_PRICE_ALIAS}.{$this->CART_PRICE_FIELD},0.0000)  != ifnull({$pos_field},0.0000)
				,CONCAT_WS(',', {$this->CART_PRICE_ALIAS}.{$this->CART_PRICE_KEY} , @update_id),@update_id) );
			select @update_id;";

        $this->_echo($sql);

        $this->db_connection->mysqli_link->multi_query($sql);

        $ids = "";
        $this->_print_r($ids);
        do
        {
            if ($res = $this->db_connection->mysqli_link->store_result())
            {
                $_ids = $res->fetch_assoc();
                $this->_print_r($_ids);
                $ids .= $_ids['@update_id'];
                $res->free();
            }
            elseif ($this->db_connection->mysqli_link->error)
            {
                echo "Error executing query: (" . $cart->get_db()->mysqli_link->errno . ") " . $this->db_connection->mysqli_link->error;
            }
        } while ($this->db_connection->mysqli_link->more_results() && $this->db_connection->mysqli_link->next_result());

        return explode(",", $ids);
    }

    //A call back function set up below is called during the rows loop to return all q
    public function get_product_quantities_for_update($quantity_field)
    {
        global $db_lib;

        return $this->db_connection->rows("SELECT DISTINCT
		{$this->CART_PRODUCT_RELATED_ID_ALIAS}.{$this->CART_PRODUCT_ID},
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
											  {$this->PRICEQTY_TABLE} {$this->PRICEQTY_ALIAS}
											  JOIN {$this->prefix}{$this->CART_PRODUCT_RELATED_ID_TABLE} {$this->CART_PRODUCT_RELATED_ID_ALIAS}
												ON {$this->CART_PRODUCT_RELATED_ID_ALIAS}.{$this->CART_PRODUCT_RELATED_ID_KEY} = {$this->PRICEQTY_ALIAS}.{$db_lib->get_item_sid()}
												AND {$this->CART_PRODUCT_RELATED_ID_ALIAS}.attribute_id = {$this->related_id}
											  JOIN {$this->prefix}{$this->CART_QTY_TABLE} {$this->CART_QTY_ALIAS}
												ON {$this->CART_QTY_ALIAS}.{$this->CART_QTY_KEY} = {$this->CART_PRODUCT_RELATED_ID_ALIAS}.{$this->CART_PRODUCT_ID}
											WHERE {$this->CART_QTY_ALIAS}.{$this->CART_QTY_FIELD_QTY} != (
												{$quantity_field}
											  )
											  AND {$this->CART_QTY_ALIAS}.manage_stock = 1", '', 'updateQuantity');
    }

    public function load1()
    {
        global $ids, $db_lib, $cart, $pos_type;

        $this->pos_type = $pos_type;

        $this->related_id = $this->cart->get_attribute('related_id');
        $this->related_parent_id = $this->cart->get_attribute('related_parent_id');

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
									  {$this->pos_type}_in_priceqty item
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
        //todo change this to looking it up from the mapping.
        $special_product_ids = array_merge($this->update_price_field('special_price', 'item.special_price', $db_lib->get_item_sid()), $this->update_price_field('special_price', 'item.special_price', $db_lib->get_style_sid()));

        //todo check if they are using a view to get the prices.
        //$price_product_ids = array_merge($this->update_price_field('price', 'item.price', $db_lib->get_item_sid()), $this->update_price_field('price', 'item.price', $db_lib->get_style_sid(), true));
        $price_product_ids = array_merge($this->update_price_field('price', 'item.price', $db_lib->get_item_sid()), $this->update_price_field('price', 'item.price', $db_lib->get_style_sid()));

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

    public function update_special_price_dates()
    {
        if (!$this->test_setting(__FUNCTION__))
        {
            return $false;
        }

        $updated_ids = array();

        // update the special price end date from the data given. Removing where the field is not set in the POS. 
        $related_parent_id = $this->cart->get_attribute('related_parent_id');
        $related_id = $this->cart->get_attribute('related_id');
        $special_price = $this->cart->get_attribute('special_price');
        $special_from_date = $this->cart->get_attribute('special_from_date');
        $special_to_date = $this->cart->get_attribute('special_to_date');
        $price = $this->cart->get_attribute('price');


        //@todo this query needs to have the constants.
        $_product = $this->db_connection->rows("SELECT DISTINCT 
									  priceqty.special_price_end_date AS `pos_special_end_date`,
									  related_id.entity_id,
									  related_id.entity_type_id,
									  related_id.store_id,
									  IFNULL(special_price.value,999999999) AS special_price,
									  price.value AS price,
									  special_from_date.value AS special_from_date,
									  special_to_date.value AS special_to_date									  
									FROM
									{$this->prefix}catalog_product_entity_varchar related_id 
									  JOIN {$this->PRICEQTY_TABLE} priceqty 
										ON priceqty.sku_id = related_id.value
									  JOIN {$this->prefix}catalog_product_entity_decimal special_price
									  ON special_price.entity_id = related_id.entity_id
									  AND special_price.attribute_id = {$special_price}
									  JOIN {$this->prefix}catalog_product_entity_decimal price
									  ON price.entity_id = related_id.entity_id
									  AND price.attribute_id = {$price}
									  LEFT JOIN {$this->prefix}catalog_product_entity_datetime special_from_date
									  ON special_from_date.entity_id = related_id.entity_id
									  AND special_from_date.attribute_id = {$special_from_date}
									  LEFT JOIN {$this->prefix}catalog_product_entity_datetime special_to_date
									  ON special_to_date.entity_id = related_id.entity_id
									  AND special_to_date.attribute_id = {$special_to_date}
									  WHERE related_id.attribute_id = {$related_id}
									  AND IFNULL(special_to_date.value,'') != IFNULL(priceqty.special_price_end_date,'')
									  ");

        if (!empty($_product))
        {
            $time = $this->db_connection->cell("SELECT NOW() c", "c");

            foreach ($_product as $product)
            {
                $update_from = false;
                if ($product['special_price'] < $product['price'])
                {//set the from date to now
                    $product['special_from_date'] = $time;
                    $product['special_to_date'] = $product['pos_special_end_date'];
                    $update_from = true;
                }
                elseif ($product['special_price'] = $product['price'])
                {//remove both times
                    $product['special_to_date'] = '';
                    $product['special_from_date'] = '';
                }
                else
                {//remove both dates, but this could be an actual price.
                    $product['special_from_date'] = '';
                    $product['special_to_date'] = '';
                }


                $this->db_connection->insertAr2("{$this->prefix}catalog_product_entity_datetime", array('entity_id' => $product['entity_id'],
                    'entity_type_id' => $product['entity_type_id'],
                    'store_id' => $product['store_id'],
                    'attribute_id' => $special_to_date,
                    'value' => $product['special_to_date'])
                        , false, array(), array('value'));


                if ($update_from)
                {
                    $this->db_connection->insertAr2("{$this->prefix}catalog_product_entity_datetime", array('entity_id' => $product['entity_id'],
                        'entity_type_id' => $product['entity_type_id'],
                        'store_id' => $product['store_id'],
                        'attribute_id' => $special_from_date,
                        'value' => $product['special_from_date'])
                            , false, array(), array('value'));
                }
                $updated_ids[] = $product['entity_id'];
            }

            return $updated_ids;
        }
        return false;
    }

    public function fill_in_missing_special_prices()
    {
        $price_attribute = $this->cart->get_attribute($cart_field);
        $special_price_attribute = $this->cart->get_attribute($cart_field);

        $this->db_connection->exec("			
			INSERT INTO {$this->prefix}catalog_product_entity_decimal (entity_type_id, attribute_id, store_id, entity_id, VALUE)
			SELECT p.entity_type_id, {$special_price_attribute} AS attribute_id, 0 AS store_id, p.entity_id, NULL AS VALUE FROM {$this->prefix}catalog_product_entity_decimal p
			LEFT JOIN {$this->prefix}catalog_product_entity_decimal d
			ON d.entity_id = p.entity_id
			AND d.attribute_id = {$special_price_attribute}
			WHERE p.attribute_id = {$price_attribute}
			AND d.attribute_id IS NULL");
    }

}

function updateQuantity($product)
{
    global $stockModel, $ids, $cart;
    $cart->_print_r($product);
    if ($product['stock_status_changed'] == 1)
    {
        $stockModel->loadByProduct($product['entity_id'])->setQty($product['qty'])->setIsInStock(true)->setProcessIndexEvents($product['stock_status_changed'] == 1)->save();
    }
    else
    {
        $ids[] = $product['entity_id'];
    }
}

?>
