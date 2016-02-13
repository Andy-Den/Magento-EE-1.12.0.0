<?php

/*
 * Load images into the magento cache
 */

/**
 * Description of rdi_cart_image_load
 *
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    1.0.0
 */
class rdi_cart_image_load extends rdi_pos_image_load {

    const CART_STORE_TABLE = "rdi_storeinventory_inventory";
    const CART_STORE_ALIAS = "inventory";
    const CART_STORE_KEY = "store_code";
    const CART_STORE_ID = "entity_id";
    const CART_STORE_QTY = "qty";
    const CART_STORE_QTY_TABLE = "rdi_storeinventory_inventory_product";
    const CART_STORE_QTY_ALIAS = "inventory_product";
    const CART_STORE_QTY_PRODUCT_ID = "product_id";
    const CART_STORE_QTY_PARENT_ID = "inventory_id";
    const CART_PRODUCT_RELATED_PARENT_ID_TABLE = "catalog_product_entity_varchar";
    const CART_PRODUCT_RELATED_PARENT_ID_ALIAS = "related_parent_id";
    const CART_PRODUCT_RELATED_PARENT_ID_KEY = "value";
    const CART_PRODUCT_ID = "entity_id";
    const CART_PRODUCT_IMAGE_TABLE = "catalog_product_entity_media_gallery";
    const CART_PRODUCT_IMAGE_ALIAS = "cart_image";
    const CART_PRODUCT_IMAGE_KEY = "value_id";
    const CART_PRODUCT_IMAGE_PRODUCT_ID = "entity_id";
    const CART_PRODUCT_IMAGE_FILE = "value";
    const CART_PRODUCT_IMAGE_CRITERIA = " AND mg.value NOT LIKE '/%/%/%'";
    const CART_PRODUCT_IMAGE_SORT = "position";
    const CART_PRODUCT_IMAGE_LABEL = "label";

    public $update_product_flat = false;
    public $non_simple_related_parent_list = array();

    //public $cart_mapping = array("fields" => "", "join" => "");

    public function insert()
    {
        global $cart;
        $this->cart_mapping = $cart->get_processor("cart_field_mapping");

        $this->update_product_flat = false;
        $this->set_image_path();

        //I have a way to extend for this..
        $this->attributes = array(
            "related_parent_id" => $this->cart_mapping->get_attribute('related_parent_id'),
            "url_key" => $this->cart_mapping->get_attribute('url_key'),
            "image" => $this->cart_mapping->get_attribute('image'),
            "small_image" => $this->cart_mapping->get_attribute('small_image'),
            "thumbnail" => $this->cart_mapping->get_attribute('thumbnail'),
            "media_gallery" => $this->cart_mapping->get_attribute('media_gallery'));

        $this->get_related_parent_list();

        if ($this->test_setting(__FUNCTION__))
        {
            $this->helper->echo_message("Checking for Image Changes", 2);
            $this->insert_product()->insert_color()->insert_swatch()->insert_category();
        }

        //if we change the radio images we have to update the flat table.
        if ($this->update_product_flat)
        {
            $this->db_connection->exec("UPDATE {$this->prefix}index_process SET status = 'require_reindex' WHERE indexer_code = 'catalog_product_flat'");
        }

        return $this;
    }

    public function insert_product()
    {
        if (!$this->test_setting(__FUNCTION__))
        {
            return $this;
        }

        $type = isset($this->MAIN_TYPE) ? "{$this->MAIN_ALIAS}.{$this->MAIN_TYPE}" : 'D';

        $this->helper->echo_message("Checking for Image Product Changes", 3);
        //the subquery here will probably not be anywhere close between carts.
        $sql = "SELECT DISTINCT
													{$this->CART_PRODUCT_IMAGE_ALIAS}.value_id,
												  {$this->MAIN_ALIAS}.{$this->MAIN_KEY} AS {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS},
												  {$this->MAIN_ALIAS}.{$this->MAIN_FILE} as 'new_image',
												  {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.entity_id,
												  {$this->CART_PRODUCT_IMAGE_ALIAS}.{$this->CART_PRODUCT_IMAGE_FILE} as 'current_image',
												  {$this->MAIN_ALIAS}.{$this->MAIN_SORT} as 'position',
													  {$type} as 'type',
													  url_key.value as image_base_name,
													  e.type_id
												FROM
												{$this->MAIN_TABLE} {$this->MAIN_ALIAS}
												  INNER JOIN {$this->prefix}{$this->CART_PRODUCT_RELATED_PARENT_ID_TABLE} {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}
													ON {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.{$this->CART_PRODUCT_RELATED_PARENT_ID_KEY} = {$this->MAIN_ALIAS}.{$this->MAIN_KEY}
													AND {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.attribute_id = {$this->attributes['related_parent_id']}
													JOIN {$this->prefix}catalog_product_entity e
													on e.entity_id = {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.entity_id
												  LEFT JOIN (SELECT mg.*, mgv.position FROM {$this->prefix}catalog_product_entity_media_gallery mg
																JOIN {$this->prefix}catalog_product_entity_media_gallery_value mgv
																ON mgv.value_id = mg.value_id
																WHERE mg.value NOT LIKE '/%/%/%' AND length(mgv.label) = 0
																) {$this->CART_PRODUCT_IMAGE_ALIAS}
													ON {$this->CART_PRODUCT_IMAGE_ALIAS}.entity_id = {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.entity_id
													AND {$this->CART_PRODUCT_IMAGE_ALIAS}.{$this->CART_PRODUCT_IMAGE_SORT} = {$this->MAIN_ALIAS}.{$this->MAIN_SORT}
												  LEFT JOIN {$this->prefix}catalog_product_super_link sl
													ON sl.product_id = {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.{$this->CART_PRODUCT_ID}
													JOIN {$this->prefix}catalog_product_entity_varchar url_key
													on url_key.entity_id = {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.entity_id
													AND url_key.attribute_id =  {$this->attributes['url_key']}
													AND url_key.store_id = 0
												WHERE sl.product_id IS NULL
												{$this->MAIN_CRITERIA}
												ORDER BY {$this->MAIN_ORDER_BY}";

        $images = $this->db_connection->rows($sql);

        if (!empty($images))
        {
            $this->helper->echo_message("Found " . count($images) . " for Image Product Update", 4);

            foreach ($images as $image)
            {
                //handle excluded simples. dont want to assign images to these products. This will remove the image before it can be assigned to the right product.
                if ($image['type_id'] == 'simple' && !$this->is_stand_alone_simple($image[$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS]))
                {
                    $image['entity_id'] = $this->get_real_id_for_related_parent_id($image[$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS]);
                }

                $this->add_image($image, 'catalog_product', true);
            }
        }

        return $this;
    }

    public function update()
    {
        if (!$this->test_setting(__FUNCTION__))
        {
            return $this;
        }

        $this->remove_product_images()->remove_color_images()->remove_swatch_images();
    }

    public function remove_product_images()
    {
        $remove_images = $this->db_connection->rows("SELECT mg.value_id FROM {$this->prefix}catalog_product_entity_media_gallery mg
					JOIN {$this->prefix}catalog_product_entity_media_gallery_value mgv
					ON mgv.value_id = mg.value_id
					and LENGTH(mgv.label) = 0
					join {$this->prefix}catalog_product_entity_varchar rp
					on rp.entity_id = mg.entity_id
					and rp.attribute_id = {$this->attributes['related_parent_id']}
					left join {$this->prefix}catalog_product_super_link sl
					on sl.product_id = rp.entity_id
					left join rpro_in_images style_image
					on style_image.style_sid = rp.value
					and	style_image.sort = mgv.position
					and style_image.attr_code = '|Style|'
					WHERE mg.value NOT LIKE '/%/%/%' and sl.product_id is null and style_image.style_sid is null");

        if (!empty($remove_images))
        {
            foreach ($remove_images as $remove_image)
            {
                $this->db_connection->exec("DELETE FROM {$this->prefix}catalog_product_entity_media_gallery WHERE value_id = {$remove_image['value_id']}");
            }
        }

        return $this;
    }

    public function remove_color_images()
    {
        $remove_images = $this->db_connection->rows("SELECT mg.value_id FROM {$this->prefix}catalog_product_entity_media_gallery mg
					JOIN {$this->prefix}catalog_product_entity_media_gallery_value mgv
					ON mgv.value_id = mg.value_id
					and LENGTH(mgv.label) > 0
					and mgv.label not like '%-swatch'
					join {$this->prefix}catalog_product_entity_varchar rp
					on rp.entity_id = mg.entity_id
					and rp.attribute_id = {$this->attributes['related_parent_id']}
					left join {$this->prefix}catalog_product_super_link sl
					on sl.product_id = rp.entity_id
					left join rpro_in_images style_image
					on style_image.style_sid = rp.value
					and	style_image.sort = mgv.position
					and style_image.attr_code = mgv.label
					WHERE mg.value NOT LIKE '/%/%/%' and sl.product_id is null and style_image.style_sid is null
					");

        if (!empty($remove_images))
        {
            foreach ($remove_images as $remove_image)
            {
                $this->db_connection->exec("DELETE FROM {$this->prefix}catalog_product_entity_media_gallery WHERE value_id = {$remove_image['value_id']}");
            }
        }

        return $this;
    }

    public function remove_swatch_images()
    {
        $remove_images = $this->db_connection->rows("SELECT mg.value_id FROM {$this->prefix}catalog_product_entity_media_gallery mg
					JOIN {$this->prefix}catalog_product_entity_media_gallery_value mgv
					ON mgv.value_id = mg.value_id
					and LENGTH(mgv.label) > 0
					and mgv.label like '%-swatch'
					join {$this->prefix}catalog_product_entity_varchar rp
					on rp.entity_id = mg.entity_id
					and rp.attribute_id = {$this->attributes['related_parent_id']}
					left join {$this->prefix}catalog_product_super_link sl
					on sl.product_id = rp.entity_id
					left join rpro_in_images style_image
					on style_image.style_sid = rp.value
					and	style_image.sort = mgv.position
					and concat(style_image.attr_code,'-swatch') = mgv.label
					WHERE mg.value NOT LIKE '/%/%/%' and sl.product_id is null and style_image.style_sid is null
					");

        if (!empty($remove_images))
        {
            foreach ($remove_images as $remove_image)
            {
                $this->db_connection->exec("DELETE FROM {$this->prefix}catalog_product_entity_media_gallery WHERE value_id = {$remove_image['value_id']}");
            }
        }

        return $this;
    }

    public function insert_color()
    {
        if (!$this->test_setting(__FUNCTION__))
        {
            return $this;
        }

        $this->helper->echo_message("Checking for Image Color Changes", 3);
        //the subquery here will probably not be anywhere close between carts.
        $images = $this->db_connection->rows("SELECT DISTINCT
													{$this->CART_PRODUCT_IMAGE_ALIAS}.value_id,
												  {$this->COLOR_ALIAS}.{$this->COLOR_PARENT_KEY} AS {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS},
												  {$this->COLOR_ALIAS}.{$this->COLOR_FILE} as 'new_image',
												  {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.entity_id,
												  {$this->CART_PRODUCT_IMAGE_ALIAS}.{$this->CART_PRODUCT_IMAGE_FILE} as 'current_image',
												  {$this->COLOR_SORT} as 'position',
												  {$this->COLOR_ALIAS}.{$this->COLOR_LABEL} as 'label',
												  {$this->CART_PRODUCT_IMAGE_ALIAS}.{$this->CART_PRODUCT_IMAGE_LABEL} as 'cart_label',
													  url_key.value as image_base_name,
													  e.type_id
												FROM
												{$this->COLOR_TABLE} {$this->COLOR_ALIAS}
												  INNER JOIN {$this->prefix}{$this->CART_PRODUCT_RELATED_PARENT_ID_TABLE} {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}
													ON {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.{$this->CART_PRODUCT_RELATED_PARENT_ID_KEY} = {$this->COLOR_ALIAS}.{$this->COLOR_PARENT_KEY}
													AND {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.attribute_id = {$this->attributes['related_parent_id']}
													JOIN {$this->prefix}catalog_product_entity e
													on e.entity_id = {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.entity_id
												  LEFT JOIN (SELECT mg.*, mgv.position, mgv.label FROM {$this->prefix}catalog_product_entity_media_gallery mg
																JOIN {$this->prefix}catalog_product_entity_media_gallery_value mgv
																ON mgv.value_id = mg.value_id
																WHERE mg.value NOT LIKE '/%/%/%'
																) {$this->CART_PRODUCT_IMAGE_ALIAS}
													ON {$this->CART_PRODUCT_IMAGE_ALIAS}.entity_id = {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.entity_id
													AND {$this->CART_PRODUCT_IMAGE_ALIAS}.{$this->CART_PRODUCT_IMAGE_LABEL} = {$this->COLOR_ALIAS}.{$this->COLOR_LABEL}
													AND {$this->CART_PRODUCT_IMAGE_ALIAS}.{$this->CART_PRODUCT_IMAGE_SORT} = {$this->COLOR_ALIAS}.{$this->MAIN_SORT}

												  LEFT JOIN {$this->prefix}catalog_product_super_link sl
													ON sl.product_id = {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.{$this->CART_PRODUCT_ID}
													JOIN {$this->prefix}catalog_product_entity_varchar url_key
													on url_key.entity_id = {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.entity_id
													AND url_key.attribute_id =  {$this->attributes['url_key']}
													AND url_key.store_id = 0
												WHERE sl.product_id IS NULL
												{$this->COLOR_CRITERIA}
												GROUP BY {$this->COLOR_GROUP_BY}
												ORDER BY {$this->COLOR_ORDER_BY}");
//													AND {$this->CART_PRODUCT_IMAGE_ALIAS}.{$this->CART_PRODUCT_IMAGE_SORT} = {$this->COLOR_SORT}
        //$this->_print_r($images); exit;
        if (!empty($images))
        {
            $this->helper->echo_message("Found " . count($images) . " for Image Color Update", 4);

            foreach ($images as $image)
            {
                //handle excluded simples. dont want to assign images to these products. This will remove the image before it can be assigned to the right product.
                if ($image['type_id'] == 'simple' && !$this->is_stand_alone_simple($image[$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS]))
                {
                    $image['entity_id'] = $this->get_real_id_for_related_parent_id($image[$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS]);
                }

                $this->_print_r($image);

                $image['color'] = preg_replace("/[^A-Za-z0-9 ]/", '', $image['label']);

                $this->add_image($image, 'catalog_product', false);
            }
        }

        return $this;
    }

    public function insert_swatch()
    {
        global $create_swatches_from_color_images;

        if (!$this->test_setting(__FUNCTION__))
        {
            return $this;
        }

        //experimental swatch creation code.
        if (isset($create_swatches_from_color_images) && $create_swatches_from_color_images == 1)
        {
            $_swatches = $this->db_connection->rows("SELECT mgc.attribute_id, mgc.entity_id, mgc.value, mgcv.* FROM {$this->prefix}catalog_product_entity_media_gallery mgc
									JOIN {$this->prefix}catalog_product_entity_media_gallery_value mgcv
									ON mgcv.value_id = mgc.value_id
									AND mgcv.label IS NOT NULL
									AND mgcv.label NOT LIKE '%-swatch'
									LEFT JOIN (SELECT mgc.value, mgc.entity_id, mgcv.* FROM {$this->prefix}catalog_product_entity_media_gallery mgc
									JOIN {$this->prefix}catalog_product_entity_media_gallery_value mgcv
									ON mgcv.value_id = mgc.value_id
									AND mgcv.label IS NOT NULL AND mgcv.label LIKE '%-swatch') swatch
									ON swatch.entity_id  = mgc.entity_id
									AND REPLACE(swatch.label,'-swatch','') = mgcv.label
									WHERE swatch.value IS NULL");
        }
        else
        {
            $this->helper->echo_message("Checking for Image Swatch Changes", 3);
            $_swatches = $this->db_connection->rows("SELECT DISTINCT
													{$this->CART_PRODUCT_IMAGE_ALIAS}.value_id,
												  {$this->COLOR_ALIAS}.{$this->COLOR_PARENT_KEY} AS {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS},
												  {$this->COLOR_ALIAS}.{$this->COLOR_FILE} as 'new_image',
												  {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.entity_id,
												  {$this->CART_PRODUCT_IMAGE_ALIAS}.{$this->CART_PRODUCT_IMAGE_FILE} as 'current_image',
												  {$this->COLOR_SORT} as 'position',
												  {$this->COLOR_ALIAS}.{$this->COLOR_LABEL} as 'label',
												  {$this->CART_PRODUCT_IMAGE_ALIAS}.{$this->CART_PRODUCT_IMAGE_LABEL} as 'cart_label',
												  {$this->COLOR_ALIAS}.{$this->SWATCH_TYPE} as type,
													  url_key.value as image_base_name,
													  e.type_id
												FROM
												{$this->COLOR_TABLE} {$this->COLOR_ALIAS}
												  INNER JOIN {$this->prefix}{$this->CART_PRODUCT_RELATED_PARENT_ID_TABLE} {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}
													ON {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.{$this->CART_PRODUCT_RELATED_PARENT_ID_KEY} = {$this->COLOR_ALIAS}.{$this->COLOR_PARENT_KEY}
													AND {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.attribute_id = {$this->attributes['related_parent_id']}
													JOIN {$this->prefix}catalog_product_entity e
													on e.entity_id = {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.entity_id
												  LEFT JOIN (SELECT mg.*, mgv.position, mgv.label FROM {$this->prefix}catalog_product_entity_media_gallery mg
																JOIN {$this->prefix}catalog_product_entity_media_gallery_value mgv
																ON mgv.value_id = mg.value_id
																WHERE mg.value NOT LIKE '/%/%/%'
																) {$this->CART_PRODUCT_IMAGE_ALIAS}
													ON {$this->CART_PRODUCT_IMAGE_ALIAS}.entity_id = {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.entity_id
													AND {$this->CART_PRODUCT_IMAGE_ALIAS}.{$this->CART_PRODUCT_IMAGE_LABEL} = CONCAT({$this->COLOR_ALIAS}.{$this->COLOR_LABEL},'-swatch')

												  LEFT JOIN {$this->prefix}catalog_product_super_link sl
													ON sl.product_id = {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.{$this->CART_PRODUCT_ID}
													JOIN {$this->prefix}catalog_product_entity_varchar url_key
													on url_key.entity_id = {$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS}.entity_id
													AND url_key.attribute_id =  {$this->attributes['url_key']}
													AND url_key.store_id = 0
												WHERE sl.product_id IS NULL
												{$this->SWATCH_CRITERIA}
												GROUP BY {$this->COLOR_GROUP_BY}
												ORDER BY {$this->COLOR_ORDER_BY}");
        }

        //$this->_var_dump($_swatches); exit;

        if (!empty($_swatches))
        {
            $this->helper->echo_message("Found " . count($_swatches) . " for Image Swatch Update", 4);

            foreach ($_swatches as $image)
            {
                //handle excluded simples. dont want to assign images to these products. This will remove the image before it can be assigned to the right product.
                if ($image['type_id'] == 'simple' && !$this->is_stand_alone_simple($image[$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS]))
                {
                    $image['entity_id'] = $this->get_real_id_for_related_parent_id($image[$this->CART_PRODUCT_RELATED_PARENT_ID_ALIAS]);
                }

                $this->add_swatch_from_color_image($image, 'catalog_product');
            }
        }



        return $this;
    }

    public function insert_category()
    {
        return $this;
    }

    public function add_image($image, $entity_type_code = '', $radios = false)
    {
        //move file
        $this->_print_r($image);
        //update database
        $moved = $this->move_image($image, $this->image_path[$entity_type_code]);

        switch ($moved)
        {
            case self::NO_SOURCE_IMAGE:
                $this->_echo("Missing File for Image");
                //log some error.
                break;
            case self::REMOVE_IMAGE:
                $this->db_connection->exec("DELETE FROM {$this->prefix}catalog_product_entity_media_gallery WHERE entity_id = '{$image['entity_id']}' AND value_id = '{$image['value_id']}'");
                break;
            case self::UPDATE_IMAGE:
                break;
            case self::NEW_IMAGE:
                switch ($entity_type_code)
                {
                    case 'catalog_product':
                        $this->load_product_image($image);
                        if ($radios)
                        {
                            $this->update_radios($image);
                        }
                        break;
                    case 'catalog_category':
                        $this->load_category_image($image);
                        break;
                }
                break;
            case self::DO_NOTHING:
                if ($radios)
                {
                    $this->update_radios($image);
                }
                break;
        }



        /* if($moved)
          {

          }
          elseif(strlen($image['new_image']) == 0 && strlen($image['current_image']) > 0)
          {//remove the image assignment

          //$this->_echo("DELETE FROM catalog_product_entity_media_gallery WHERE entity_id = '{$image['new_image']}' AND value_id = '{$image['value_id']}'");
          }

          //update image label
          if(strlen($image['value_id']) > 0 && $image['label'] != $image['cart_label'])
          {
          $this->db_connection->exec("UPDATE catalog_product_entity_media_gallery_value SET label = '{$this->db_connection->clean($image['label'])}' WHERE value_id = '{$image['value_id']}'");
          //$this->_echo("UPDATE catalog_product_entity_media_gallery_value SET label = '{$this->db_connection->clean($image['label'])}' WHERE value_id = '{$image['value_id']}'");
          } */
    }

    public function add_swatch_from_color_image($image, $entity_type_code = '')
    {
        require_once('libraries/class.rdi_swatch.php');

        $r = new rdi_swatch();
        //$this->_var_dump($image); exit;
        //a hex RGB was given
        if ($this->image_types['hex'] == $image['type'])
        {

            $file = $r->create_swatch($r->hex2rgb($image['new_image']), 5, 5, "in/images/hex{$image['new_image']}-swatch.png");

            $file_name_label = preg_replace("/[^A-Za-z0-9 ]/", '', $image['label']);

            $image['image_base_name'] = "/{$image['image_base_name']}-{$file_name_label}-swatch";
            $image['new_image'] = "/" . basename($file);
        }

        //$image['current_image'] = basename($image['new_image']);
        $image['label'] .= '-swatch';

        $this->add_image($image, 'catalog_product', false);
    }

    public function load_product_image($image)
    {
        if (!isset($image['label']) || strlen($image['label']) === 0)
        {
            $image['label'] = '';
        }



        if (isset($image['current_image']) && strlen($image['current_image']) > 0)
        {
            //make sure there is leading /
            $image['current_image'] = (substr($image['current_image'], 0, 1) == '/' ? '' : '/') . $image['current_image'];

            if (strlen($image['value_id']) == 0)
            {
                $value_id = $this->db_connection->insert("INSERT INTO `{$this->prefix}catalog_product_entity_media_gallery` (`attribute_id`, `entity_id`, `value`) VALUES ({$this->attributes['media_gallery']}, {$image['entity_id']}, '{$image['current_image']}')");

                if (isset($value_id) && strlen($image['current_image']) > 0)
                {
                    $this->db_connection->exec("INSERT INTO `{$this->prefix}catalog_product_entity_media_gallery_value`(value_id, store_id, label, position) values({$value_id}, 0, '{$this->db_connection->clean($image['label'])}', {$image['position']})");
                }
            }
            else
            {
                $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_media_gallery_value SET label = '{$this->db_connection->clean($image['label'])}' WHERE value_id = '{$image['value_id']}'");
            }
        }
    }

    public function update_radios($image)
    {
        //defaulting the first image to the radios
        if (isset($image['position']) && $image['position'] == 1)
        {
            $this->update_product_flat = true;
            //we are pretty save on the image records being set to no_selection.
            if (strlen($image['current_image']) > 0)
            {
                
		$image_file = (substr($image['current_image'], 0, 1) == '/'?"":"/") . $image['current_image'];
                $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_varchar SET value = '{$image_file}' WHERE entity_id = {$image['entity_id']} AND attribute_id in({$this->attributes['image']},{$this->attributes['thumbnail']},{$this->attributes['small_image']})");
            }
        }

        //thumb will be at the position 5 if its here.
        if (isset($image['type']) && $image['type'] == $this->image_types['thumbnail'])
        {
            global $thumbnail_is_small_image;

            $attributes = isset($thumbnail_is_small_image) && $thumbnail_is_small_image == '1' ? "{$this->attributes['thumbnail']},{$this->attributes['small_image']}" : "{$this->attributes['thumbnail']}";

            $this->update_product_flat = true;
            //we are pretty save on the image records being set to no_selection.
            $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_varchar SET value = '{$image['current_image']}' WHERE entity_id = {$image['entity_id']} AND attribute_id in({$attributes})");
        }
    }

    //this is to handle excluded simples
    public function get_related_parent_list()
    {
        $this->non_simple_related_parent_list = $this->db_connection->cells("SELECT DISTINCT rp.entity_id, rp.value FROM {$this->prefix}catalog_product_entity_varchar rp
																	JOIN {$this->prefix}catalog_product_entity e
																	on e.entity_id = rp.entity_id
																	and e.type_id != 'simple'
																	WHERE rp.attribute_id = {$this->attributes['related_parent_id']}", 'entity_id', 'value');
    }

    public function is_stand_alone_simple($related_parent_id)
    {
        return !isset($this->non_simple_related_parent_list[$related_parent_id]);
    }

    public function get_real_id_for_related_parent_id($related_parent_id)
    {
        return $this->non_simple_related_parent_list[$related_parent_id];
    }

    public function load_category_image($image)
    {
        //SQL
    }

    public function set_image_path()
    {
        global $image_path;

        if (isset($image_path))
        {
            $this->image_path['catalog_product'] = $image_path;
        }
        else
        {
            $this->image_path = array(
                "catalog_product" => "../media/catalog/product/",
                "catalog_category" => "../media/catalog/category/"
            );
        }
    }

    public function get_image_load_parameters()
    {
        global $field_mapping, $debug, $helper_funcs, $field_mapping, $cart;

        //get the entity_id for this product
        $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');

        //get the related id attribute id
        $related_id_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");

        //get the related_parent_id
        $related_parent_id_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_parent_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");

        $joins = "inner join {$this->prefix}catalog_product_entity_varchar on {$this->prefix}catalog_product_entity_varchar.value = " . $field_mapping->map_field('product', 'related_id', 'configurable', '') . " and {$this->prefix}catalog_product_entity_varchar.attribute_id IN({$related_parent_id_attribute_id},{$related_id_attribute_id})
                  left join {$this->prefix}catalog_product_entity_media_gallery on {$this->prefix}catalog_product_entity_media_gallery.entity_id = {$this->prefix}catalog_product_entity_varchar.entity_id
                   AND {$this->prefix}catalog_product_entity_media_gallery.value NOT LIKE '/%/%/%'
        LEFT JOIN {$this->prefix}catalog_product_super_link sl
    ON sl.product_id = {$this->prefix}catalog_product_entity_varchar.entity_id";

        $fields = "{$this->prefix}catalog_product_entity_varchar.entity_id, {$this->prefix}catalog_product_entity_media_gallery.value as 'current_image', {$this->prefix}catalog_product_entity_varchar.value as 'related_id'";

        return array(
            "join" => $joins,
            "where" => $field_mapping->map_field('product', 'product_image') . ' IS NOT NULL  AND sl.product_id IS NULL ',
            "group_by" => '',
            "order_by" => '',
            "fields" => $fields,
            "table" => '',
            "current_image" => 'current_image'
        );
    }

    public function load_image($image_record)
    {
        global $cart, $pos, $inPath, $image_debug, $hook_handler, $store_id;

        //get the entity_id for this product
        $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');

        //get the related id attribute id
        $related_id_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");

        //get the related_parent_id
        $related_parent_id_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_parent_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");



        //if entity_id is null then this image is not assigned to a product, so we need to look up the entity_id
        if (!isset($image_record['entity_id']))
        {
            //Looking for entity_id assuming this is a configurable
            $image_record['entity_id'] = $cart->get_db()->cell("SELECT {$this->prefix}catalog_product_entity_varchar.entity_id from {$this->prefix}catalog_product_entity_varchar
			JOIN {$this->prefix}catalog_product_entity
			ON {$this->prefix}catalog_product_entity_varchar.entity_id = {$this->prefix}catalog_product_entity.entity_id
			WHERE attribute_id = {$related_id_attribute_id} and value = '{$image_record['related_id']}'
			and {$this->prefix}catalog_product_entity.type_id = 'configurable'");
        }



        if (!isset($image_record['entity_id']) || (isset($image_record['entity_id']) && $image_record['entity_id'] == '' ))
        {
            //didn't find the configurable, Looking for entity_id assuming this is a simple
            $image_record['entity_id'] = $cart->get_db()->cell("select entity_id from {$this->prefix}catalog_product_entity_varchar where attribute_id = {$related_parent_id_attribute_id} and value = '{$image_record['related_id']}'", "entity_id");
        }



        $add = true;

        $url_key_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'url_key' and entity_type_id = {$product_entity_type_id}", "attribute_id");
        $url_key = $cart->get_db()->cell("select value from {$this->prefix}catalog_product_entity_varchar where entity_id = {$image_record['entity_id']} and attribute_id = {$url_key_attribute_id}");

        $url_key = str_replace("#", "", $url_key);
        $url_key = str_replace('"', "", $url_key);
        $url_key = str_replace("'", "", $url_key);
        $url_key = str_replace("/", "", $url_key);
        $url_key = preg_replace('#[^0-9a-z]+#i', '-', strtr($url_key, $this->converttable));
        $url_key = $url_key . '_' . $image_record['entity_id'];



        $ext = pathinfo($image_record['image']);


        $image_count = 0;
        if ($add)
        {

            $image_count++;

            $sub_fix = '';

            if ($image_count > 1)
            {
                $sub_fix = "_{$image_count}";
            }
            $destFile = $url_key . "{$sub_fix}." . $ext['extension'];

            if ($url_key . $sub_fix == '')
                return;

            $destPicture = '../media/catalog/product/' . $destFile;

            //echo "{$srcPicture} SET IMAGE {$product_id}<Br>";
            $media_gallery_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'media_gallery' and entity_type_id = {$product_entity_type_id}", "attribute_id");

            $cart->get_db()->exec("delete from {$this->prefix}catalog_product_entity_media_gallery where entity_id = {$image_record['entity_id']} and attribute_id = {$media_gallery_attribute_id}" . " AND `value` NOT LIKE '/%/%/%'");

            //escape the name of the file so that it doesnt blow the query up
            $filename = addslashes($destFile);

            $value_id = $cart->get_db()->insert("INSERT INTO `{$this->prefix}catalog_product_entity_media_gallery` (`attribute_id`, `entity_id`, `value`) VALUES ({$media_gallery_attribute_id}, {$image_record['entity_id']}, '{$filename}')");

            $disabled = 0;

            //mark 1 if want to use the main image as the thumb
            $main_image_thumb = 1;

            //get the attribute id for the media image, and small image
            $image_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'image' and entity_type_id = {$product_entity_type_id}", "attribute_id");
            $small_image_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'small_image' and entity_type_id = {$product_entity_type_id}", "attribute_id");

            $cart->get_db()->insert("INSERT INTO `{$this->prefix}catalog_product_entity_varchar` (`entity_type_id`, `attribute_id`, `entity_id`, `value`) VALUES ({$product_entity_type_id}, {$image_attribute_id}, {$image_record['entity_id']}, '{$destFile}') ON DUPLICATE KEY UPDATE `value` = '{$destFile}'");
            $cart->get_db()->insert("INSERT INTO `{$this->prefix}catalog_product_entity_varchar` (`entity_type_id`, `attribute_id`, `entity_id`, `value`) VALUES ({$product_entity_type_id}, {$small_image_attribute_id}, {$image_record['entity_id']}, '{$destFile}') ON DUPLICATE KEY UPDATE `value` = '{$destFile}'");

            if ($main_image_thumb == 1)
            {
                $thumbnail_gallery_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'thumbnail' and entity_type_id = {$product_entity_type_id}", "attribute_id");
                $cart->get_db()->insert("INSERT INTO `{$this->prefix}catalog_product_entity_varchar` (`entity_type_id`, `attribute_id`, `entity_id`, `value`) VALUES ({$product_entity_type_id}, {$thumbnail_gallery_attribute_id}, {$image_record['entity_id']}, '{$destFile}') ON DUPLICATE KEY UPDATE `value` = '{$destFile}'");
            }
            //}

            $cart->get_db()->insert("INSERT IGNORE INTO `{$this->prefix}catalog_product_entity_media_gallery_value` (`value_id`, `store_id`, `label`, `position`, `disabled`) VALUES ({$value_id}, {$store_id}, '', {$image_count}, {$disabled})");

            $hook_handler->call_hook("cart_image_processed", $image['source_image'], $image_record['entity_id'], $value_id);

            if (file_exists($image_record['source_image']))
            {
                if ($image_debug == 1)
                    echo "assigning image file: {$destFile} to product id: {$image_record['entity_id']}<br>";

                copy($image_record['source_image'], $destPicture);
                copy($image_record['source_image'], $image_record['archive_image']);
                if (!unlink($image_record['source_image']))
                {
                    //insert an error
                    $cart->get_db()->insert("insert into rdi_error_log (datetime, error_level, error_file, error_message) values(now(), 0, 'magento_rdi_cart_image_load.php', 'Error deleteing image file: {$image_record['source_image']}')");
                }
            }
        }
        else
        {
            //archive and remove ones that dont assign
            if (file_exists($image_record['source_image']))
            {
                copy($image_record['source_image'], $image_record['archive_image']);
                if (!unlink($image_record['source_image']))
                {
                    $cart->get_db()->insert("insert into rdi_error_log (datetime, error_level, error_file, error_message) values(now(), 0, 'magento_rdi_cart_image_load.php', 'Error deleteing image file: {$image_record['source_image']}')");
                }
            }
        }
    }

    public function process_image($file_name, $image_index, $sku = '', $product_id = '')
    {
        global $cart, $pos, $inPath, $image_debug, $hook_handler, $store_id, $image_folder;

        if ($image_debug == 1)
            echo "processing image: " . $file_name . "<br>";

        if (!file_exists('in/archive/images'))
        {
            mkdir("in/archive/images");
        }

        if (isset($image_folder) && $image_folder !== "" && !file_exists("../media/catalog/product/{$image_folder}"))
        {
            mkdir("..media/catalog/product/{$image_folder}");
        }

        //$product = Mage::getModel('catalog/product');

        if ($sku == '' && $product_id == '')
        {
            //assume the file name is the related id
            //use it to find the product_id
            //get the file without the extension
            $info = pathinfo($file_name);
            $related_id = basename($file_name, '.' . $info['extension']);

            //run the file name through the thumbnail descriptor, determines if its a thumbnail, and returns the name of the related id sans the thumbnail marking
            $thumbnail_id = $pos->get_processor("rdi_pos_common")->thumbnail_descriptor($related_id);

            if ($thumbnail_id != '')
            {
                //delete the thumb
                unlink('in/images/' . $file_name);

                //THIS WILL CAUSE IT TO SKIP THE T
                return false;

                $related_id = $thumbnail_id;
            }

            //sometimes there is a _index on the file names need to trim that off
            if (preg_match("/(.*?)_/", $related_id, $matches) > 0)
            {
                $related_id = $matches[1];
            }

            if ($image_debug == 1)
                echo "using related id: {$related_id} <br>";

            //print_r($matches);

            $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');

            //get the related id attribute id
            $related_id_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");

            //get the related_parent_id
            $related_parent_id_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_parent_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");

            //attempt to get the value from the related_parent_id
            //CONSIDER USING THE product_image in the mapping for this mapping for how we relate the image files?
            //try to relate from the parent id, ie this is a simple and the related id is in the related_parent_id field
            $product_id = $cart->get_db()->cell("select entity_id from {$this->prefix}catalog_product_entity_varchar where value = '{$related_id}' and attribute_id = {$related_parent_id_attribute_id}");

            //if we didnt find a relation try by the related id
            if ($product_id == '')
            {
                //get the entity id
                $product_id = $cart->get_db()->cell("select entity_id from {$this->prefix}catalog_product_entity_varchar where value = '{$related_id}' and attribute_id = {$related_id_attribute_id}");
            }

            //if this is a a simple product we need to link it up under the configurable or it wont be seen
            if ($product_id != '') // && ($related_parent_id == '' || $related_parent_id == null))
            {
                if ($image_debug == 1)
                    echo "found product: " . $product_id . "<br>";

                //get the product type
                $type = $cart->get_db()->row("select type_id, attribute_set_id from {$this->prefix}catalog_product_entity where entity_id = {$product_id}");

                //check if this type has any attributes defined, if it doesn then its really just a simple
                $sql = "select rdi_cart_class_map_fields.* from rdi_cart_class_map_fields
                        inner join rdi_cart_class_mapping on rdi_cart_class_mapping.cart_class_mapping_id =
                        rdi_cart_class_map_fields.cart_class_mapping_id where rdi_cart_class_mapping.product_class_id = {$type['attribute_set_id']}";

                $criteria = $cart->get_db()->rows($sql);

                if ($type['type_id'] == "simple" && is_array($criteria))
                {
                    //get the configurable for this product
                    $product_id = $cart->get_db()->cell("select parent_id from {$this->prefix}catalog_product_super_link where product_id = {$product_id}");
                }
            }
        }


        if ($product_id != '')
        {
            //echo $product_id;
            //get the url key so we can rename the image file for seo purposes
            $url_key_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'url_key' and entity_type_id = {$product_entity_type_id}", "attribute_id");
            $url_key = $cart->get_db()->cell("select value from {$this->prefix}catalog_product_entity_varchar where entity_id = {$product_id} and attribute_id = {$url_key_attribute_id}");

            $url_key = str_replace("#", "", $url_key);
            $url_key = str_replace('"', "", $url_key);


            $ext = pathinfo($file_name);

            $srcPicture = 'in/images/' . $file_name;
            //$destPicture = '../media/catalog/product/' . $url_key . ".". $ext['extension'];
            $archiveDest = 'in/archive/images/' . $file_name;

            $add = true;

            $image_files = $cart->get_db()->cells("select value from {$this->prefix}catalog_product_entity_media_gallery where entity_id = {$product_id}", "value");

            $image_count = -1;

            //if($galleryData['images'])
            if (is_array($image_files))
            {
                //foreach ($galleryData['images'] as &$image)
                foreach ($image_files as $image_file)
                {

                    $image_check_location = (substr($image_file, 1) == '/' ? "../media/catalog/product" . $image_file :
                                    "../media/catalog/product/" . $image_file);

                    //if(md5_File("../media/catalog/product/" . $image_file) == md5_File($inPath . "/images/" . $file_name))
                    if (md5_File($image_check_location) == md5_File($inPath . "/images/" . $file_name))
                    {
                        $add = false;

                        if ($image_debug == 1)
                            echo "skipping image, product already has it assigned<br>";
                    }
                }

                $image_count = count($image_files);
            }
//

            if ($add)
            {
                $image_count++;

                $sub_fix = '';

                if ($image_count > 1)
                {
                    $sub_fix = "_{$image_count}";
                }

                $destFile = (isset($image_folder) && $image_folder == "" ? "" : "/{$image_folder}/") . $url_key . "{$sub_fix}." . $ext['extension'];

                $destPicture = '../media/catalog/product' . (substr($destFile, 1) == '/' ? "" : "/") . $destFile;

                //echo "{$srcPicture} SET IMAGE {$product_id}<Br>";
                $media_gallery_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'media_gallery' and entity_type_id = {$product_entity_type_id}", "attribute_id");

                //escape the name of the file so that it doesnt blow the query up
                $filename = addslashes($destFile);

                $value_id = $cart->get_db()->insert("INSERT INTO `{$this->prefix}catalog_product_entity_media_gallery` (`attribute_id`, `entity_id`, `value`) VALUES ({$media_gallery_attribute_id}, {$product_id}, '{$filename}')");

                $disabled = 0;

                //mark 1 if want to use the main image as the thumb
                $main_image_thumb = 1;

                if ($thumbnail_id && $main_image_thumb == 0)
                {

                    //get the attribute id for the thumb
                    $thumbnail_gallery_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'thumbnail' and entity_type_id = {$product_entity_type_id}", "attribute_id");

                    $cart->get_db()->insert("INSERT INTO `{$this->prefix}catalog_product_entity_varchar` (`entity_type_id`, `attribute_id`, `entity_id`, `value`) VALUES ({$product_entity_type_id}, {$thumbnail_gallery_attribute_id}, {$product_id}, '{$filename}') ON DUPLICATE KEY UPDATE `value` = '{$filename}'");

                    $disabled = 1;
                }
                else
                {
                    //get the attribute id for the media image, and small image
                    $image_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'image' and entity_type_id = {$product_entity_type_id}", "attribute_id");
                    $small_image_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'small_image' and entity_type_id = {$product_entity_type_id}", "attribute_id");

                    $cart->get_db()->insert("INSERT INTO `{$this->prefix}catalog_product_entity_varchar` (`entity_type_id`, `attribute_id`, `entity_id`, `value`) VALUES ({$product_entity_type_id}, {$image_attribute_id}, {$product_id}, '{$destFile}') ON DUPLICATE KEY UPDATE `value` = '{$destFile}'");
                    $cart->get_db()->insert("INSERT INTO `{$this->prefix}catalog_product_entity_varchar` (`entity_type_id`, `attribute_id`, `entity_id`, `value`) VALUES ({$product_entity_type_id}, {$small_image_attribute_id}, {$product_id}, '{$destFile}') ON DUPLICATE KEY UPDATE `value` = '{$destFile}'");

                    if ($main_image_thumb == 1)
                    {
                        $thumbnail_gallery_attribute_id = $cart->get_db()->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'thumbnail' and entity_type_id = {$product_entity_type_id}", "attribute_id");
                        $cart->get_db()->insert("INSERT INTO `{$this->prefix}catalog_product_entity_varchar` (`entity_type_id`, `attribute_id`, `entity_id`, `value`) VALUES ({$product_entity_type_id}, {$thumbnail_gallery_attribute_id}, {$product_id}, '{$destFile}') ON DUPLICATE KEY UPDATE `value` = '{$destFile}'");
                    }
                }

                $cart->get_db()->insert("INSERT IGNORE INTO `{$this->prefix}catalog_product_entity_media_gallery_value` (`value_id`, `store_id`, `label`, `position`, `disabled`) VALUES ({$value_id}, {$store_id}, '', {$image_count}, {$disabled})");

                $hook_handler->call_hook("cart_image_processed", $srcPicture, $product_id, $value_id);

                if (file_exists($srcPicture))
                {


                    if ($image_debug == 1)
                        echo "assigning image file: {$destFile} to product id: {$product_id}<br>";

                    copy($srcPicture, $destPicture);
                    copy($srcPicture, $archiveDest);
                    if (!unlink($srcPicture))
                    {
                        //insert an error
                        $cart->get_db()->insert("insert into rdi_error_log (datetime, error_level, error_file, error_message) values(now(), 0, 'magento_rdi_cart_image_load.php', 'Error deleteing image file: {$srcPicture}')");
                    }
                }
            }
            else
            {
                //archive and remove ones that dont assign
                if (file_exists($srcPicture))
                {
                    copy($srcPicture, $archiveDest);
                    if (!unlink($srcPicture))
                    {
                        $cart->get_db()->insert("insert into rdi_error_log (datetime, error_level, error_file, error_message) values(now(), 0, 'magento_rdi_cart_image_load.php', 'Error deleteing image file: {$srcPicture}')");
                    }
                }
            }
        }

        if ($image_debug == 1)
            echo "<br>";

        ob_flush();
    }

    //main category image loag function for the cart.
    public function assign_category_images($_images_list)
    {
        if (is_array($_images_list) && !empty($_images_list))
        {
            $this->category_image_path = "../media/catalog/category/";

            $this->set_category_attributes()->get_categories_images($_images_list['related_ids'])->update_insert_category_images($_images_list);
        }

        return $this;
    }

    public function set_category_attributes()
    {
        if (!isset($category_image_attribute_destination))
        {
            $category_image_attribute_destination = 'image';
        }


        $this->category_attributes = $this->db_connection->cells("SELECT attribute_id, attribute_code FROM {$this->prefix}eav_attribute a
                                                                        JOIN {$this->prefix}eav_entity_type et
                                                                        ON et.entity_type_id = a.entity_type_id
                                                                        AND et.entity_type_code = 'catalog_category'
                                                                        WHERE a.attribute_code IN('{$category_image_attribute_destination}','url_path')", "attribute_id", "attribute_code");

        return $this;
    }

    public function get_categories_images($_related_ids)
    {
        $related_ids = "'" . implode("','", $_related_ids) . "'";

        $this->image_data = $this->db_connection->rows("SELECT DISTINCT e.related_id, e.entity_id, v.value AS 'current_image',
                                                        REPLACE(REPLACE(url_path.value,'/','-'),'.html','') AS image_name
                                                        FROM {$this->prefix}catalog_category_entity e
                                                        LEFT JOIN {$this->prefix}catalog_category_entity_varchar v
                                                        ON v.entity_id = e.entity_id
                                                        AND v.attribute_id = {$this->category_attributes['image']}
                                                        JOIN {$this->prefix}catalog_category_entity_varchar url_path
                                                        ON url_path.entity_id = e.entity_id
                                                        AND url_path.attribute_id = {$this->category_attributes['url_path']}
                                                        WHERE e.related_id IN({$related_ids})");

        return $this;
    }

    public function update_insert_category_images($_images_list)
    {
        if (is_array($this->image_data))
        {
            foreach ($this->image_data as $image)
            {
                // is there an image?
                $file1 = $image['current_image'];
                $file2 = $_images_list[$image['related_id']]['image'];

                //does the current image match the old image.
                if (!$this->check_md5_2files($file1, $file2))
                {
                    $this->set_category_value($image)->copy_category_image($_images_list[$image['related_id']]['image'], $image['image_name'] . '.jpg');
                }


                $this->archive_image($_images_list[$image['related_id']]['image']);
            }
        }
    }

    public function check_md5_2files($file1, $file2)
    {
        if ($file1 !== '' && $file1 != null)
        {
            return md5_file($file1) == md5_file($file2);
        }

        return false;
    }

    public function set_category_value($image)
    {
        $this->db_connection->exec("INSERT INTO {$this->prefix}catalog_category_entity_varchar (entity_type_id, entity_id, attribute_id, store_id, value)
            VALUES (3,{$image['entity_id']}, {$this->category_attributes['image']}, 0, '{$image['image_name']}.jpg' )
            ON DUPLICATE KEY UPDATE value = '{$image['image_name']}.jpg'");

        return $this;
    }

    public function copy_category_image($old_image, $new_image)
    {
        copy($old_image, $this->category_image_path . $new_image);
    }

    public function archive_image($image_name)
    {
        rename($image_name, dirname(dirname($image_name)) . "/archive/images/" . basename($image_name, '.jpg'));
    }

}

?>
