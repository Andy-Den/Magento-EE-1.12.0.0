<?php
/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Product load class
 *
 * Handles the loading of the catalog data
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Load\Product\Magento
 */
class rdi_cart_product_load extends rdi_general {

    public $_convertTable = array(
        '&amp;' => 'and', '@' => 'at', '©' => 'c', '®' => 'r', 'À' => 'a',
        'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'Å' => 'a', 'Æ' => 'ae', 'Ç' => 'c',
        'È' => 'e', 'É' => 'e', 'Ë' => 'e', 'Ì' => 'i', 'Í' => 'i', 'Î' => 'i',
        'Ï' => 'i', 'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o',
        'Ø' => 'o', 'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'Ý' => 'y',
        'ß' => 'ss', 'à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'å' => 'a',
        'æ' => 'ae', 'ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
        'ì' => 'i', 'í' => 'i', 'î' => 'i', 'ï' => 'i', 'ò' => 'o', 'ó' => 'o',
        'ô' => 'o', 'õ' => 'o', 'ö' => 'o', 'ø' => 'o', 'ù' => 'u', 'ú' => 'u',
        'û' => 'u', 'ü' => 'u', 'ý' => 'y', 'þ' => 'p', 'ÿ' => 'y', 'A' => 'a',
        'a' => 'a', 'A' => 'a', 'a' => 'a', 'A' => 'a', 'a' => 'a', 'C' => 'c',
        'c' => 'c', 'C' => 'c', 'c' => 'c', 'C' => 'c', 'c' => 'c', 'C' => 'c',
        'c' => 'c', 'D' => 'd', 'd' => 'd', 'Ð' => 'd', 'd' => 'd', 'E' => 'e',
        'e' => 'e', 'E' => 'e', 'e' => 'e', 'E' => 'e', 'e' => 'e', 'E' => 'e',
        'e' => 'e', 'E' => 'e', 'e' => 'e', 'G' => 'g', 'g' => 'g', 'G' => 'g',
        'g' => 'g', 'G' => 'g', 'g' => 'g', 'G' => 'g', 'g' => 'g', 'H' => 'h',
        'h' => 'h', 'H' => 'h', 'h' => 'h', 'I' => 'i', 'i' => 'i', 'I' => 'i',
        'i' => 'i', 'I' => 'i', 'i' => 'i', 'I' => 'i', 'i' => 'i', 'I' => 'i',
        'i' => 'i', '?' => 'ij', '?' => 'ij', 'J' => 'j', 'j' => 'j', 'K' => 'k',
        'k' => 'k', '?' => 'k', 'L' => 'l', 'l' => 'l', 'L' => 'l', 'l' => 'l',
        'L' => 'l', 'l' => 'l', '?' => 'l', '?' => 'l', 'L' => 'l', 'l' => 'l',
        'N' => 'n', 'n' => 'n', 'N' => 'n', 'n' => 'n', 'N' => 'n', 'n' => 'n',
        '?' => 'n', '?' => 'n', '?' => 'n', 'O' => 'o', 'o' => 'o', 'O' => 'o',
        'o' => 'o', 'O' => 'o', 'o' => 'o', 'Œ' => 'oe', 'œ' => 'oe', 'R' => 'r',
        'r' => 'r', 'R' => 'r', 'r' => 'r', 'R' => 'r', 'r' => 'r', 'S' => 's',
        's' => 's', 'S' => 's', 's' => 's', 'S' => 's', 's' => 's', 'Š' => 's',
        'š' => 's', 'T' => 't', 't' => 't', 'T' => 't', 't' => 't', 'T' => 't',
        't' => 't', 'U' => 'u', 'u' => 'u', 'U' => 'u', 'u' => 'u', 'U' => 'u',
        'u' => 'u', 'U' => 'u', 'u' => 'u', 'U' => 'u', 'u' => 'u', 'U' => 'u',
        'u' => 'u', 'W' => 'w', 'w' => 'w', 'Y' => 'y', 'y' => 'y', 'Ÿ' => 'y',
        'Z' => 'z', 'z' => 'z', 'Z' => 'z', 'z' => 'z', 'Ž' => 'z', 'ž' => 'z',
        '?' => 'z', '?' => 'e', 'ƒ' => 'f', 'O' => 'o', 'o' => 'o', 'U' => 'u',
        'u' => 'u', 'A' => 'a', 'a' => 'a', 'I' => 'i', 'i' => 'i', 'O' => 'o',
        'o' => 'o', 'U' => 'u', 'u' => 'u', 'U' => 'u', 'u' => 'u', 'U' => 'u',
        'u' => 'u', 'U' => 'u', 'u' => 'u', 'U' => 'u', 'u' => 'u', '?' => 'a',
        '?' => 'a', '?' => 'ae', '?' => 'ae', '?' => 'o', '?' => 'o', '?' => 'e',
        '?' => 'jo', '?' => 'e', '?' => 'i', '?' => 'i', '?' => 'a', '?' => 'b',
        '?' => 'v', '?' => 'g', '?' => 'd', '?' => 'e', '?' => 'zh', '?' => 'z',
        '?' => 'i', '?' => 'j', '?' => 'k', '?' => 'l', '?' => 'm', '?' => 'n',
        '?' => 'o', '?' => 'p', '?' => 'r', '?' => 's', '?' => 't', '?' => 'u',
        '?' => 'f', '?' => 'h', '?' => 'c', '?' => 'ch', '?' => 'sh', '?' => 'sch',
        '?' => '-', '?' => 'y', '?' => '-', '?' => 'je', '?' => 'ju', '?' => 'ja',
        '?' => 'a', '?' => 'b', '?' => 'v', '?' => 'g', '?' => 'd', '?' => 'e',
        '?' => 'zh', '?' => 'z', '?' => 'i', '?' => 'j', '?' => 'k', '?' => 'l',
        '?' => 'm', '?' => 'n', '?' => 'o', '?' => 'p', '?' => 'r', '?' => 's',
        '?' => 't', '?' => 'u', '?' => 'f', '?' => 'h', '?' => 'c', '?' => 'ch',
        '?' => 'sh', '?' => 'sch', '?' => '-', '?' => 'y', '?' => '-', '?' => 'je',
        '?' => 'ju', '?' => 'ja', '?' => 'jo', '?' => 'e', '?' => 'i', '?' => 'i',
        '?' => 'g', '?' => 'g', '?' => 'a', '?' => 'b', '?' => 'g', '?' => 'd',
        '?' => 'h', '?' => 'v', '?' => 'z', '?' => 'h', '?' => 't', '?' => 'i',
        '?' => 'k', '?' => 'k', '?' => 'l', '?' => 'm', '?' => 'm', '?' => 'n',
        '?' => 'n', '?' => 's', '?' => 'e', '?' => 'p', '?' => 'p', '?' => 'C',
        '?' => 'c', '?' => 'q', '?' => 'r', '?' => 'w', '?' => 't', '™' => 'tm',
        '>' => '', '<' => '', '"' => '', '(' => '', ')' => '', '$' => '',
    );
    public $library;

    /**
     * Class Constructor
     *
     * @param rdi_catalog_load $db
     */
    public function rdi_cart_product_load($db = '')
    {
        global $load_product_data;

        //This setting is to help with loading staging data XLSX
        if (isset($load_product_data) && $load_product_data == 1){  }else{ require_once "libraries/cart_libs/magento/magento_rdi_indexer_lib.php";}

        parent::rdi_general($db);

        $this->check_product_lib_version();
    }

    /**
     * 
     * @global rdi_setting $product_cart_lib_ver
     */
    private function check_product_lib_version()
    {
        global $product_cart_lib_ver;
        //@setting $product_cart_lib_ver

        require_once "libraries/cart_libs/magento/version_libs/{$product_cart_lib_ver}/magento_rdi_product_lib.php";
        $this->library = new magento_rdi_product_lib($this->db_connection);
    }

    /**
     * Pre Load Function
     * @hook cart_product_load_pre_load
     */
    public function pre_load()
    {
        global $hook_handler;

        $hook_handler->call_hook("cart_product_load_pre_load");
    }

    /**
     * Post load function
     * We check to see if there are any products in the staging table. Here is where the avail updates will start. 
     * This sets the status, visibility, in_stock, backorders, rdi_avail related methods such as Sell Always, Sell Never, display only.
     *
     * @global rdi_hook $hook_handler
     * @global stagingdb $db_lib
     * @hook cart_product_load_post_load
     */
    public function post_load()
    {
        global $hook_handler, $db_lib;



        //These are the products post load functions that require the staging table to be filled.
        if ($db_lib->get_product_count() > 0)
        {
            // the old terrible way of doing this.
            $this->update_availability();

            // changes visibility and status based on is/out stock
            $this->process_out_of_stock();

            $this->set_display_only();

            //disables if no image.
            $this->disable_product_for_image();

            $this->update_tax_class_id();

            $this->update_super_attributes();
            $this->update_super_attributes_values();

            $this->set_default_weights();
        }

        $hook_handler->call_hook("cart_product_load_post_load");
        $this->enterprise_url_key_update('catalog_product');
    }

    /**
     * An array of defined parameters to interject into the query so that they insert can occur only on records that need to be inserted
     * these values are parsed in the pos library to build the query used to get the products
     *
     * @global rdi_field_mapping $field_mapping
     * @global rdi_debug $debug
     * @global rdi_hook $hook_handler
     * @setting $do_not_update_old
     * @param string $product_class
     * @param string $product_type
     * @return string
     */
    public function get_product_insert_parameters($product_class, $product_type)
    {
        global $field_mapping, $hook_handler, $do_not_update_old;

        $product_entity_type_id = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
        $related_attribute_id = $this->db_connection->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");

        $join = "left join {$this->prefix}catalog_product_entity_varchar on {$this->prefix}catalog_product_entity_varchar.value = "
                . $field_mapping->map_field('product', 'related_id', $product_type['product_type'], $product_class['product_class'])
                . " and {$this->prefix}catalog_product_entity_varchar.attribute_id = {$related_attribute_id}
                   left join {$this->prefix}catalog_product_entity on {$this->prefix}catalog_product_entity.entity_id = {$this->prefix}catalog_product_entity_varchar.entity_id  and {$this->prefix}catalog_product_entity.type_id = '{$product_type['product_type']}'";

        $where = "{$this->prefix}catalog_product_entity_varchar.entity_id is null";
        if (isset($do_not_update_old) && $do_not_update_old == 1)
        {
            $where .= " AND {$this->prefix}catalog_product_entity.related_id is null";
        }
        $group_by = '';
        $order_by = '';

        if ($product_type['product_type'] == 'configurable' || $product_type['product_type'] == 'grouped')
        {
            $group_by = $field_mapping->map_field('product', 'style_id', $product_type['product_type'], $product_class['product_class']);
        }

        $product_insert_parameters = array(
            "join" => $join,
            "where" => $where,
            "group_by" => $group_by,
            "order_by" => $order_by
        );

        $hook_handler->call_hook("cart_get_product_insert_parameters", $product_insert_parameters, $product_type['product_type'], $product_class);

        return $product_insert_parameters;
    }

    /**
     * An array of defined parameters to interject into the query so that they updates occur based on only what needs update
     * returns an array of tests to perform
     * each record in the array will need to be iterated on, then the product data from the total would be the product list for updates
     * @global rdi_field_mapping $field_mapping
     * @global rdi_debug $debug
     * @global rdi_hook $hook_handler
     * @global rdi_helper $helper_funcs
     * @setting $pos_type The type of POS as defined in the settings. Tells the scripts which pos library to include.
     * @param string $product_class
     * @param string $product_type
     * @return string
     */
    public function get_product_update_parameters($product_class, $product_type)
    {
        global $field_mapping, $debug, $hook_handler, $helper_funcs, $pos_type, $default_stock_id;

        $product_update_parameters = array();

        /**
         * get all the fields used to map the data
         */
        $product_fields = $field_mapping->get_field_list('product', $product_type['product_type'], $product_class['product_class']);

        /**
         * loop all the product fields and see if they have changed from what is currently live
         */
        foreach ($product_fields as $field)
        {
            if (in_array($field['cart_field'], array('style_id', 'item_id')))
            {
                continue;
            }
            $mapped_field = '';
            /**
             * if there are , in the field name then its a field list and must be handled as such
             * @todo move this to the rdi_field_mapping function and make is general for getting data on any type from the field_mapping tables.
             * @todo Modify this to handle any SQL functions with commas in the mapping.
             */
            if (strpos($field['pos_field'], ',') > 0)
            {
                /**
                 * if the alt list is also a field list we have to use ifnull values to properly compare
                 */
                if (strpos($field['alternative_field'], ',') > 0)
                {
                    $new_field = $helper_funcs->sift_field_to_alt($field['pos_field'], $field['alternative_field']);

                    $pos_field = "concat({$new_field})";
                }
                else
                {
                    $pos_field = "concat({$field['pos_field']})";
                }
            }
            else
            {
                $pos_field = $field['pos_field'];
            }

            $mapped_field = $pos_field;

            if ($field['alternative_field'] != '')
            {
                /**
                 * if there are , in the field name then its a field list and must be handled as such
                 */
                if (strpos($field['alternative_field'], ',') > 0)
                {
                    /**
                     * if the alt list is also a field list we have to use ifnull values to properly compare
                     */
                    if (strpos($field['pos_field'], ',') > 0)
                    {
                        $new_field = $helper_funcs->sift_field_to_alt($field['pos_field'], $field['alternative_field']);

                        $alternative_field = "concat({$new_field})";
                    }
                    else
                    {
                        $alternative_field = "concat({$field['alternative_field']})";
                    }
                }
                else
                {
                    $alternative_field = $field['alternative_field'];
                }

                $mapped_field = " IFNULL({$pos_field},{$alternative_field}) ";
            }




            $param = $this->process_update_parameter($product_type['product_type'], $field['cart_field'], $product_class, $mapped_field, $field['allow_update'], $field['special_handling'], $pos_field);

            if (!empty($param))
            {
                $param['mapping'] = $field;
            }

            if ($param !== false)
            {
                $product_update_parameters[] = $param;
            }

            unset($mapped_field, $param, $alternative_field, $new_field);
        }

        ///////////////////////////////

        /**
         * @category avail
         * This is avail updates on v8 and v9, they are the same and using the avail mapping to check the fields wording that come back from the POS to figure out the avail name.
         */
        $avail_field = $field_mapping->map_field('product', 'avail', $product_type['product_type'], $product_class['product_class']);

        /*$_avail = array("special_handling" => null,
            "default_value" => null,
            "allow_update" => 1,
            "cart_field" => 'avail',
            "attribute_code" => null,
            "attribute_id" => null,
            "backend_type" => null,
            "frontend_input" => null,
            "pos_field" => $avail_field,
            "alternative_field" => null
        );*/

        
        /**
         * get 2 more for the avail updates
         */
        //get 2 more for the avail updates
        $product_update_parameters[] = array(
            "group_by" => '',
            "order_by" => '',
            "debug" => 'check the manage stock',
            "update_field" => 'avail',
            "join" => "INNER JOIN {$this->prefix}catalog_product_entity_varchar ON {$this->prefix}catalog_product_entity_varchar.value = "
            . $field_mapping->map_field('product', 'related_id', $product_type['product_type'], $product_class['product_class'])
            . " INNER JOIN {$this->prefix}catalog_product_entity ON {$this->prefix}catalog_product_entity.entity_id = {$this->prefix}catalog_product_entity_varchar.entity_id and {$this->prefix}catalog_product_entity.type_id = '{$product_type['product_type']}'"
            . " inner join {$this->prefix}cataloginventory_stock_item on {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_entity.entity_id",
            "where" => "({$this->prefix}cataloginventory_stock_item.manage_stock = 0 and (ifnull({$avail_field},'') != 'sell always' || {$avail_field} is null))");

        $product_update_parameters[] = array(
            "group_by" => '',
            "order_by" => '',
            "debug" => 'check the manage stock',
            "update_field" => 'avail',
            "join" => "INNER JOIN {$this->prefix}catalog_product_entity_varchar ON {$this->prefix}catalog_product_entity_varchar.value = "
            . $field_mapping->map_field('product', 'related_id', $product_type['product_type'], $product_class['product_class'])
            . " INNER JOIN {$this->prefix}catalog_product_entity ON {$this->prefix}catalog_product_entity.entity_id = {$this->prefix}catalog_product_entity_varchar.entity_id and {$this->prefix}catalog_product_entity.type_id = '{$product_type['product_type']}'"
            . " inner join {$this->prefix}cataloginventory_stock_item on {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_entity.entity_id",
            "where" => "({$this->prefix}cataloginventory_stock_item.manage_stock != 0 or {$this->prefix}cataloginventory_stock_item.use_config_manage_stock = 1)  and ifnull({$avail_field},'') = 'sell always'");

        $product_update_parameters[] = array(
            "group_by" => '',
            "order_by" => '',
            "debug" => 'check the backorders to turn on.',
            "update_field" => 'avail',
            "join" => "INNER JOIN {$this->prefix}catalog_product_entity_varchar ON {$this->prefix}catalog_product_entity_varchar.value = "
            . $field_mapping->map_field('product', 'related_id', $product_type['product_type'], $product_class['product_class'])
            . " INNER JOIN {$this->prefix}catalog_product_entity ON {$this->prefix}catalog_product_entity.entity_id = {$this->prefix}catalog_product_entity_varchar.entity_id and {$this->prefix}catalog_product_entity.type_id = '{$product_type['product_type']}'"
            . " inner join {$this->prefix}cataloginventory_stock_item on {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_entity.entity_id",
            "where" => "(({$this->prefix}cataloginventory_stock_item.backorders != 2)  and ifnull({$avail_field},'') = 'allow backorder')
															||
															(({$this->prefix}cataloginventory_stock_item.manage_stock = 1 || {$this->prefix}cataloginventory_stock_item.qty > 0)  and {$avail_field} = 'sell never')
															||
															(({$this->prefix}cataloginventory_stock_item.manage_stock = 0 )  and ifnull({$avail_field},'') = 'sell to threshold')


												 ");

        $product_update_parameters[] = array(
            "group_by" => '',
            "order_by" => '',
            "debug" => 'check the backorders to turn off.',
            "update_field" => 'avail',
            "join" => "INNER JOIN {$this->prefix}catalog_product_entity_varchar ON {$this->prefix}catalog_product_entity_varchar.value = "
            . $field_mapping->map_field('product', 'related_id', $product_type['product_type'], $product_class['product_class'])
            . " INNER JOIN {$this->prefix}catalog_product_entity ON {$this->prefix}catalog_product_entity.entity_id = {$this->prefix}catalog_product_entity_varchar.entity_id and {$this->prefix}catalog_product_entity.type_id = '{$product_type['product_type']}'"
            . " inner join {$this->prefix}cataloginventory_stock_item on {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_entity.entity_id",
            "where" => "({$this->prefix}cataloginventory_stock_item.backorders != 0)  and ifnull({$avail_field},'') != 'allow backorder'");

        $hook_handler->call_hook("cart_get_product_update_parameters", $product_update_parameters, $product_type['product_type'], $product_class);

        return $product_update_parameters;
    }

    /**
     * Master_opt is the pos_field, this would be the first option they chose for the field, we need to know this to properly evaluate.
     *
     * @todo This needs to be changed. Right after this point the data should be exactly how it needs to be added to the cart. And we only need to process each row. Either adding the new eav_attribute_option and inserting the option_ids or adding the varchar/text/decimal fields to the cart. Allowing for modification of special attributes like tax_class_code, visibility and status (on initial product load) and msrp, msrp_enabled. The array should be value_id, entity_type_id, store_id, attribute_id, value
     *
     * @global rdi_field_mapping $field_mapping
     * @global rdi_debug $debug
     * @global rdi_helper $helper_funcs
     * @param string $product_type
     * @param rdi_field_mapping $cart_field
     * @param rdi_field_mapping $product_class
     * @param rdi_field_mapping_pos $pos_field
     * @param rdi_field_mapping $allow_update
     * @param rdi_field_mapping $special_handling
     * @param string $master_opt Adds a criteria to all update strings.
     * @return boolean
     */
    public function process_update_parameter($product_type, $cart_field, $product_class, $pos_field, $allow_update, $special_handling, $master_opt = '')
    {
        global $field_mapping, $default_stock_id;

        $skip = false;
        $group_by = "";
        $order_by = "";
        $change_check = "";

        if ($special_handling != 'skip_checks')
        {
            if ($product_type == 'configurable' || $product_type == 'grouped')
            {
                /**
                 * if this field is one of the fields in the field data then skip the update test
                 * we dont want to test if attributes have updated on a configureable, as it will likely cause false positives
                 */
                if (is_array($product_class['field_data']))
                {
                    foreach ($product_class['field_data'] as $f)
                    {
                        if ($f['cart_field'] == $cart_field)
                        {
                            $skip = true;
                        }
                    }
                }

                $group_by = $field_mapping->map_field('product', 'style_id', $product_type, $product_class['product_class']);
            }

            $commands = explode(',', $special_handling);

            /**
             * Checking the entity_attribute_id is not needed any more.
             * We are checking all attributes when selecting the mapping before going into the loop
             */
            $entity_attribute_id = 999;
            /* @depricated.
              //check if the field even belongs to this attribute set
              $entity_attribute_id = $this->db_connection->cell("SELECT entity_attribute_id from {$this->prefix}eav_entity_attribute
              INNER JOIN {$this->prefix}eav_entity_type ON {$this->prefix}eav_entity_type.entity_type_id = {$this->prefix}eav_entity_attribute.entity_type_id AND {$this->prefix}eav_entity_type.entity_type_code = 'catalog_product'
              inner join {$this->prefix}eav_attribute_set on {$this->prefix}eav_attribute_set.attribute_set_id = {$this->prefix}eav_entity_attribute.attribute_set_id and {$this->prefix}eav_attribute_set.attribute_set_name = '{$product_class['product_class']}'
              inner join {$this->prefix}eav_attribute on {$this->prefix}eav_attribute.attribute_id = {$this->prefix}eav_entity_attribute.attribute_id and {$this->prefix}eav_attribute.attribute_code = '{$cart_field}'", 'entity_attribute_id');
             */

            if ($entity_attribute_id == '' && $cart_field != 'qty' && $cart_field != 'min_qty')
                $skip = true;

            if ($pos_field == '' && (!in_array('force_update', $commands)))
            {
                $skip = true;
            }

            if ($allow_update != '1')
            {
                $skip = true;
            }

            /**
             * any special handling cant be compared by sql so skip this
             */
            if ($special_handling != '' && !in_array('force_update', $commands))
            {
                /**
                 * this will only allow zero_null, nothing else
                 */
                if ($special_handling != "zero_null")
                {
                    $skip = true;
                }
            }
        }

        if (!$skip)
        {
            $where = '';

            $joins = "INNER JOIN {$this->prefix}catalog_product_entity_varchar ON {$this->prefix}catalog_product_entity_varchar.value = "
                    . $field_mapping->map_field('product', 'related_id', $product_type, $product_class['product_class'])
                    . " INNER JOIN {$this->prefix}catalog_product_entity ON {$this->prefix}catalog_product_entity.entity_id = {$this->prefix}catalog_product_entity_varchar.entity_id and {$this->prefix}catalog_product_entity.type_id = '{$product_type}'";

            if ($cart_field != '' && $cart_field != 'style_id' && $cart_field != 'qty' && $cart_field != 'min_qty')
            {
                $attribute_row = $this->db_connection->row("SELECT
                                                 attribute_id, backend_type, frontend_input
                                                FROM {$this->prefix}eav_attribute
                                                INNER JOIN {$this->prefix}eav_entity_type on {$this->prefix}eav_entity_type.entity_type_id = {$this->prefix}eav_attribute.entity_type_id
                                                WHERE attribute_code = '{$cart_field}' and {$this->prefix}eav_entity_type.entity_type_code = 'catalog_product'");

                if (is_array($attribute_row) && $attribute_row['backend_type'] != 'static')
                {
                    $joins .= " LEFT JOIN {$this->prefix}catalog_product_entity_{$attribute_row['backend_type']} cpe_{$cart_field} ON cpe_{$cart_field}.entity_id = {$this->prefix}catalog_product_entity.entity_id AND cpe_{$cart_field}.attribute_id = {$attribute_row['attribute_id']}";

                    if ($attribute_row['frontend_input'] == "select")
                    {
                        /**
                         * need to set the attribute value from the code provided
                         */
                        $change_check = "{$this->prefix}catalog_product_entity.type_id = '{$product_type}'";

                        if (!in_array('force_update', $commands))
                        {
                            /**
                             * Will add this later as a test
                             * @todo Add the mapped value to the criteria string.
                             */
                            //$change_check .= " and (eav_attribute_option_value.value is null || " . $field_mapping->map_field('product', $pos_field, $product_type, $product_class['product_class'])  . " != eav_attribute_option_value.value)";
                            $change_check .= " and ({$this->prefix}eav_attribute_option_value.value is null || IFNULL(" . $pos_field . ",'') != BINARY {$this->prefix}eav_attribute_option_value.value)";
							
							//added to stop both null on eav int fields from continual update.
							$change_check .= " and NOT ({$this->prefix}eav_attribute_option_value.value IS NULL AND {$pos_field} IS NULL)";
                        }

                        $where .= $change_check;

                        $joins .= " left join {$this->prefix}eav_attribute_option_value on {$this->prefix}eav_attribute_option_value.option_id = cpe_{$cart_field}.value";
                    }
                    else if ($attribute_row['frontend_input'] == "textarea" || $attribute_row['frontend_input'] == "text")
                    {
                        /**
                         * need to set the attribute value from the code provided
                         */
                        $change_check = "{$this->prefix}catalog_product_entity.type_id = '{$product_type}'";

                        if (!in_array('force_update', $commands))
                        {
                            /**
                             * Will add this later as a test
                             */
                            //$change_check .= " and (cpe_{$cart_field}.value is null || " . $field_mapping->map_field('product', $pos_field, $product_type, $product_class['product_class'])  . " != eav_attribute_option_value.value)";
                            $change_check .= " AND (IFNULL(cpe_{$cart_field}.value,'') != BINARY IFNULL({$pos_field},'')) ";
                        }

                        $where .= $change_check;
                    }
                    else if ($attribute_row['frontend_input'] == "price" || $attribute_row['backend_type'] == "decimal")
                    {
                        /**
                         * need to set the attribute value from the code provided
                         */
                        $change_check = "{$this->prefix}catalog_product_entity.type_id = '{$product_type}'";

                        if (!in_array('force_update', $commands))
                        {
                            /**
                             * Will add this later as a test
                             */
                            //$change_check .= " and (cpe_{$cart_field}.value is null || " . $field_mapping->map_field('product', $pos_field, $product_type, $product_class['product_class'])  . " != eav_attribute_option_value.value)";
                            $change_check .= " AND (IFNULL(cpe_{$cart_field}.value,'0.0000') != CAST(IFNULL({$pos_field},'0.0000') AS DECIMAL(10,4))) ";
                        }

                        $where .= $change_check;
                    }
                    else
                    {
                        //need to set the attribute value from the code provided
                        $change_check = "{$this->prefix}catalog_product_entity.type_id = '{$product_type}'";

                        if (!in_array('force_update', $commands))
                        {
                            /**
                             * Will add this later as a test
                             */
                            //$change_check .= " and (cpe_{$cart_field}.value is null || " . $field_mapping->map_field('product', $pos_field, $product_type, $product_class['product_class'])  . " != eav_attribute_option_value.value)";
                            $change_check .= " and (cpe_{$cart_field}.value is null || " . $pos_field . " != BINARY cpe_{$cart_field}.value || {$pos_field} is null)";
                        }

                        $where .= $change_check;
                    }
                }
                /**
                 * Static means its on the product entity record directly not an attribute
                 */
                else if ($attribute_row['backend_type'] == 'static')
                {
                    /**
                     * need to set the attribute value from the code provided
                     */
                    $change_check = "{$this->prefix}catalog_product_entity.type_id = '{$product_type}'";

                    if (!in_array('force_update', $commands))
                    {
                        //Will add this later as a test
                        //$change_check .= " and ({$pos_field} is not null || {$pos_field} != {$this->prefix}catalog_product_entity.{$cart_field})";
                        $change_check .= " and ( ifnull({$pos_field},'') != {$this->prefix}catalog_product_entity.{$cart_field})";
                    }

                    $where .= $change_check;
                }
            }
            else if (($cart_field == 'qty' || $cart_field == 'min_qty') && ($product_type != 'configurable' || $product_type != 'grouped'))
            {
                /**
                 * need to set the attribute value from the code provided
                 */
                $change_check = "{$this->prefix}catalog_product_entity.type_id = '{$product_type}' ";

                if (!in_array('force_update', $commands))
                {
                    $change_check .= " and {$pos_field} != {$this->prefix}cataloginventory_stock_item.{$cart_field}";
                }

                /**
                 * need to set the attribute value from the code provided
                 */
                //$change_check = $pos_field . " != cataloginventory_stock_item.{$cart_field} and {$this->prefix}catalog_product_entity.type_id = '{$product_type}'";
                $where .= $change_check;

                $joins .= " inner join {$this->prefix}cataloginventory_stock_item on {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_entity.entity_id
							AND {$this->prefix}cataloginventory_stock_item.stock_id = {$default_stock_id}";
            }

            if ($master_opt != '')
            {
                //$where .= ($where != "" ? " and " : "") . " {$master_opt} is null";
            }

            if ($where != '')
            {


                return array(
                    "join" => $joins,
                    "where" => $where,
                    "group_by" => $group_by,
                    "order_by" => $order_by,
                    "debug" => $change_check,
                    "update_field" => $cart_field,
                    "fields" => "{$this->prefix}catalog_product_entity.entity_id"
                );
            }
        }

        return false;
    }

    public function get_avail_stock_update_parameters($product_type, $product_class)
    {
        global $field_mapping, $default_stock_id;

        if ($product_type['product_type'] == 'simple')
        {
            $joins = "INNER JOIN {$this->prefix}catalog_product_entity_varchar ON {$this->prefix}catalog_product_entity_varchar.value = "
                    . $field_mapping->map_field('product', 'related_id', $product_type['product_type'], $product_class['product_class'])
                    . " INNER JOIN {$this->prefix}catalog_product_entity ON {$this->prefix}catalog_product_entity.entity_id = {$this->prefix}catalog_product_entity_varchar.entity_id and {$this->prefix}catalog_product_entity.type_id = '{$product_type['product_type']}'"
                    . " inner join {$this->prefix}cataloginventory_stock_item on {$this->prefix}cataloginventory_stock_item.product_id = {$this->prefix}catalog_product_entity.entity_id
						AND {$this->prefix}cataloginventory_stock_item.stock_id = {$default_stock_id}";
            $group_by = '';
            $where = "{$this->prefix}cataloginventory_stock_item.qty != " . $field_mapping->map_field('product', 'qty', $product_type['product_type'], $product_class['product_class']);
            $order_by = '';
            $table = '';
            $fields = $field_mapping->map_field('product', 'qty', $product_type['product_type'], $product_class['product_class']) . " as 'qty', " . $field_mapping->map_field('product', 'related_id', $product_type['product_type'], $product_class['product_class']) . " as 'related_id'";
            $update_field = 'qty';

            return array(
                "join" => $joins,
                "where" => $where,
                "group_by" => $group_by,
                "order_by" => $order_by,
                "fields" => $fields,
                "table" => $table,
                "update_field" => $update_field
            );
        }

        return array(
            "join" => '',
            "where" => '',
            "group_by" => '',
            "order_by" => '',
            "fields" => '',
            "table" => '',
            "update_field" => ''
        );
    }

    /**
     * Hashing parameters. Not used in Magento. This is a stub.
     * @param rdi_field_mapping $product_type
     * @param rdi_field_mapping $product_class
     * @return array
     */
    public function process_product_update_hash_parameter($product_type, $product_class)
    {
        $parameters = array();

        return $parameters;
    }

    /**
     * Hashing parameters. Not used in Magento. This is a stub.
     * makes adjustments or changes to the parameters list used from the product load, so that the proper data is gathered
     * @param rdi_field_mapping $product_class
     * @param rdi_field_mapping $product_type
     * @param rdi_field_mapping $product_field
     * @param array $parameters
     * @return array
     */
    public function process_product_update_get_hash_parameter($product_class, $product_type, $product_field, $parameters)
    {
        return $parameters;
    }

    /**
     * These are the must have for the system functions, not to be confused with magento upsell
     * @setting $product_link_type
     * @global rdi_lib $pos
     * @return array array(join, fields, where, group_by, order_by)
     * @todo attribute fixin
     * @todo Upsells should be its own class. So that is shows as a different Namespace in documentation.
     */
    public function get_upsell_insert_parameters()
    {
        global $product_link_type, $pos;

        $types = explode(',', $product_link_type);

        $parameters = array();
        $product_entity_type_id = $this->db_connection->cell("select entity_type_id from eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
        $related_attribute_id = $this->db_connection->cell("select attribute_id from eav_attribute where attribute_code = 'related_parent_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");

        foreach ($types as $type)
        {
            //NEED TO FIX THESE rpro_in_upsell... should be more using the $pos->get_processor("rdi_staging_db_lib")->get_table_name('in_upsell_item') system

            $joins = "inner join catalog_product_entity_varchar parent
                        on parent.value = rpro_in_upsell_item.fldstylesid
                        and parent.attribute_id = {$related_attribute_id}

                             LEFT JOIN catalog_product_super_link psl
                            ON psl.product_id = parent.entity_id

                        inner join catalog_product_entity_varchar product
                        on product.value = rpro_in_upsell_item.fldupsellsid
                        and product.attribute_id = {$related_attribute_id}

                            LEFT JOIN catalog_product_super_link sl
                            ON sl.product_id = product.entity_id

                        inner join catalog_product_link_type
                        on catalog_product_link_type.code = '{$type}'
                        left join catalog_product_link
                        on catalog_product_link.product_id = parent.entity_id
                        and catalog_product_link.linked_product_id = product.entity_id";


            $where = "catalog_product_link.link_id is null
                        AND psl.product_id IS NULL
                        AND sl.product_id IS NULL";
            $group_by = '';
            $order_by = '';
            $table = '';
            $fields = 'parent.entity_id as product_id, product.entity_id as linked_product_id, catalog_product_link_type.link_type_id';

            $parameters[] = array(
                "join" => $joins,
                "where" => $where,
                "group_by" => $group_by,
                "order_by" => $order_by,
                "fields" => $fields,
                "table" => $table
            );
        }

        if (count($parameters) == 1)
        {
            $parameters = $parameters[0];
        }

        return $parameters;
    }

    /**
     * These are the query parameters to update product upsells. Changing sort_order and the joins.
     * @todo attribute fixin
     * @setting $product_link_type This is the default link_type the POS product links should come from.
     * @return type
     */
    public function get_upsell_removal_parameters()
    {
        global $product_link_type, $db_lib;

        $upsell_table = $db_lib->get_table_name('in_upsell_item');
        $related_parent_field = $db_lib->get_style_sid();
        $upsell_related_parent_field = $db_lib->get_upsell_style_sid();

        $types = explode(',', $product_link_type);

        $types = implode("','", $types);


        $product_entity_type_id = $this->db_connection->cell("select entity_type_id from {$this->prefix}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
        $related_attribute_id = $this->db_connection->cell("select attribute_id from {$this->prefix}eav_attribute where attribute_code = 'related_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");
        $joins = "INNER JOIN {$this->prefix}catalog_product_entity_varchar parent
                    ON parent.entity_id = {$this->prefix}catalog_product_link.product_id
                    AND parent.attribute_id = {$related_attribute_id}
                    AND parent.entity_type_id = {$product_entity_type_id}
                    INNER JOIN {$this->prefix}catalog_product_entity_varchar product
                    ON product.entity_id = {$this->prefix}catalog_product_link.linked_product_id
                    AND product.attribute_id = {$related_attribute_id}
                    AND parent.entity_type_id = {$product_entity_type_id}
                    INNER JOIN {$this->prefix}catalog_product_link_type
                    ON {$this->prefix}catalog_product_link_type.code IN('{$types}')
                    LEFT JOIN {$upsell_table}
                    ON {$upsell_table}.{$related_parent_field} = parent.value
                    AND {$upsell_table}.{$upsell_related_parent_field} = product.value";

        $where = "{$upsell_table}.{$related_parent_field} is null";
        $group_by = '';
        $order_by = '';
        $fields = "{$this->prefix}catalog_product_link.link_id";
        $table = "{$this->prefix}catalog_product_link";

        return array(
            "join" => $joins,
            "where" => $where,
            "group_by" => $group_by,
            "order_by" => $order_by,
            "fields" => $fields,
            "table" => $table
        );
    }

    /**
     * Calls the process_link_insert function in the library.
     * @todo This is bad
     * @uses process_link_insert Description
     * @param type $link_data
     */
    public function upsell_insert($link_data)
    {
        $this->library->process_link_insert($link_data);
    }

    /**
     * Calls the process_link_insert function in the library.
     * @todo This is bad
     * @uses process_link_update Description
     * @param type $link_data
     */
    public function upsell_update($link_data)
    {
        $this->library->process_link_update($link_data);
    }

    /**
     * Calls the process_link_insert function in the library.
     * @todo This is bad
     * @uses process_link_removal Description
     * @param type $link_data
     */
    public function upsell_removal($link_data)
    {
        $this->library->process_link_removal($link_data);
    }

    /**
     * process the category for insert / update into magento
     * Update field is the field that was determined to have changed, also serves as a flag to tell us this is an update
     * @todo This is the function that should be removed. It works, but not like it should. We dont care if it is an update or an insert. It works all the same. At this point we just want to put the data into the table. We just need to know how to apply any special handling and how
     *
     * @global type $related_attribute_id
     * @global rdi_lib $pos
     * @global rdi_field_mapping $field_mapping
     * @global rdi_debug $debug
     * @global list $skus
     * @global benchmarker $benchmarker
     * @global rdi_hook $hook_handler
     *
     * @param rdi_field_mapping $product_class_def
     * @param rdi_field_mapping $product_type
     * @param array $product_records All the product records that will be updated.
     * @param array $update_parameter The field that we are about to update. The name is also the key on the field.(Probably)
     */
    public function process_product_records($product_class_def, $product_type, $product_records, $update_parameter = '')
    {
        global $related_attribute_id, $pos, $field_mapping, $skus, $benchmarker, $hook_handler, $simple_url_key_format, $configurable_url_key_format, $keep_configurable_associations, $product_associations_updated;

        //get the attribute if of the related id if need be
        if ($related_attribute_id == '')
        {
            $related_attribute_id = $this->db_connection->cell("SELECT attribute_id FROM {$this->prefix}eav_attribute ea
                                                                JOIN {$this->prefix}eav_entity_type et
                                                                ON et.entity_type_id = ea.entity_type_id
                                                                AND et.entity_type_code = 'catalog_product'
                                                                WHERE ea.attribute_code = 'related_id'", "attribute_id");
        }

        //print_r($product_records);
        //exit;
        if (is_array($product_records))
        {
            //the current style id we are working on
            //$previous_product = '';
            //$current_style_item_entity_ids = array();

            foreach ($product_records as $product_record)
            {
                if (isset($product_record['qty']) || $update_parameter !== '')
                {
                    $referenced_entities = array();

                    //reformat the url_key for simples to match the format
                    //                if(isset($simple_url_key_format) && $simple_url_key_format != ''
                    //                        && $product_type['product_type'] == 'simple' && $product_record['related_parent_id'] == ''
                    //                        && array_key_exists("url_key", $product_record))

                    if (isset($simple_url_key_format) && $simple_url_key_format != '' && $product_type['product_type'] == 'simple' && isset($product_record['sku'])
                            //&& (array_key_exists('related_parent_id',$product_record) ? $product_record['related_parent_id'] == '' : false)
                            && array_key_exists("url_key", $product_record))
                    {
                        //@setting $simple_url_key_format {name}-{size}-{color}-{itemnum} Insert attributes from the cart that are in this product's possession at the time of load. The url_key does not update. This formation should be correct on load and never changed.
                        $this->process_url_key_pattern($product_record, $simple_url_key_format);
                    }

                    if (isset($configurable_url_key_format) && $configurable_url_key_format != '' && $product_type['product_type'] == 'configurable' && array_key_exists("url_key", $product_record) && isset($product_record['sku']))
                    {
                        //@setting $configurable_url_key_format {name}-{sku}  Insert attributes from the cart that are in this product's possession at the time of load. The url_key does not update. This formation should be correct on load and never changed.
                        $this->process_url_key_pattern($product_record, $configurable_url_key_format);
                    }

                    if (!isset($product_record['entity_id']))
                    {
                        $product_record['entity_id'] = $this->db_connection->cell("SELECT {$this->prefix}catalog_product_entity.entity_id from {$this->prefix}catalog_product_entity
                                    inner join {$this->prefix}catalog_product_entity_varchar
                                    on {$this->prefix}catalog_product_entity_varchar.entity_id = {$this->prefix}catalog_product_entity.entity_id
                                     and type_id = '{$product_type['product_type']}' where attribute_id = {$related_attribute_id}
                                    and value = '{$product_record['related_id']}'", "entity_id");
                    }

                    /**
                     * handle the stock management code, it is in the avail record, and it should be hard coded here to handle it, mapping just makes it messy and isnt needed
                     */
                    if (array_key_exists('avail', $product_record))
                    {
                        switch (strtolower($product_record['avail']))
                        {
                            case "sell always":
                                {
                                    $product_record['manage_stock'] = 0;
                                    $product_record['use_config_manage_stock'] = 0;
                                    $product_record['backorders'] = 0;
                                    break;
                                }

                            case "sell never":
                                {
                                    $product_record['is_in_stock'] = 0;
                                    $product_record['qty'] = 0;
                                    $product_record['manage_stock'] = 1;
                                    $product_record['use_config_manage_stock'] = 1;
                                    $product_record['backorders'] = 0;
                                    break;
                                }
                            case "sell to threshold":
                                {
                                    $product_record['manage_stock'] = 1;
                                    $product_record['use_config_manage_stock'] = 1;
                                    $product_record['backorders'] = 0;

                                    if (array_key_exists('threshold', $product_record))
                                        $product_record['min_qty'] = $product_record['threshold'];
                                    break;
                                }
                            case "allow backorder":
                                {
                                    $product_record['manage_stock'] = 1;
                                    $product_record['use_config_manage_stock'] = 1;
                                    $product_record['is_in_stock'] = 1;
                                    $product_record['backorders'] = 2;

                                    if (array_key_exists('threshold', $product_record))
                                        $product_record['min_qty'] = $product_record['threshold'];
                                    break;
                                }

                            case "display only":
                                {
                                    $product_record['manage_stock'] = 1;
                                    $product_record['use_config_manage_stock'] = 1;
                                    $product_record['is_in_stock'] = 0;
                                    $product_record['backorders'] = 0;

                                    break;
                                }

                            default:
                                {
                                    $product_record['manage_stock'] = 1;
                                    $product_record['use_config_manage_stock'] = 1;
                                    $product_record['backorders'] = 0;
                                    break;
                                }
                        }
                    }

                    if ($product_type['product_type'] == "configurable" || $product_type['product_type'] == "grouped")
                    {
                        /**
                         * need to get the referenced entities
                         * the style_id passed should let us get a list of reference ids for this style
                          If we already have updated the product associations, no we do not need this list of ids. Just let it go.
                         */
                        if (!isset($product_associations_updated))
                        {
                            $product_associations_updated = array();
                        }

                        /** @todo delete this stupid thing.
                          if(!in_array($product_record['entity_id'],$product_associations_updated))
                          {
                          $this->_comment("Link products to configurables");

                          $parameters = array("join" => "LEFT JOIN {$this->prefix}eav_entity_type
                          ON {$this->prefix}eav_entity_type.entity_type_code = 'catalog_product'
                          LEFT JOIN {$this->prefix}eav_attribute
                          ON {$this->prefix}eav_attribute.attribute_code = 'related_id'
                          AND {$this->prefix}eav_attribute.entity_type_id = {$this->prefix}eav_entity_type.entity_type_id
                          LEFT JOIN {$this->prefix}catalog_product_entity_varchar
                          ON {$this->prefix}catalog_product_entity_varchar.value = "
                          . $field_mapping->map_field('product', 'item_id', $product_type['product_type'], $product_class_def['product_class'])
                          . " AND {$this->prefix}catalog_product_entity_varchar.attribute_id = {$this->prefix}eav_attribute.attribute_id
                          LEFT JOIN {$this->prefix}catalog_product_entity ON {$this->prefix}catalog_product_entity.entity_id = {$this->prefix}catalog_product_entity_varchar.entity_id AND {$this->prefix}catalog_product_entity.type_id = 'simple'",
                          "fields" => "{$this->prefix}catalog_product_entity.entity_id",
                          "where" => "",
                          "group_by" => "",
                          "order_by" => "");

                          //$product_associations_updated[] = $product_entity_id;
                          $referenced_entities = $pos->get_processor("rdi_pos_product_load")->get_related_item_ids($product_record['style_id'], $parameters);
                          set_product_associations($referenced_entities, $product_record['entity_id']);
                          } */
                    }

                    /**
                     * insert / update the product record
                     */
                    if ($update_parameter == '' && !$product_record['entity_id'])
                    {
                        $this->library->insertUpdateProductRecord($product_class_def, $product_type, $product_record, $referenced_entities);
                    }
                    else if ($update_parameter == "update_relation")
                    {
                        
                    }
                    else
                    {
                        $this->library->magento_update_field($product_record, $update_parameter, $product_type, $product_class_def);

                        if ($product_type['product_type'] == "configurable" || $product_type['product_type'] == "grouped")
                        {
                            //@setting $keep_configurable_associations [0-OFF,1-ON] This setting disables to the removable of the association for products to their parent. It should be used to turn off the default funcationality and replace it later in an addon.
                            if (isset($keep_configurable_associations) && $keep_configurable_associations == 1)
                            {
                                //if this is a configurable then we always pass the update related entities
                                $this->library->set_product_associations($referenced_entities, $product_record['entity_id'], false);
                            }
                            else
                            {
                                $this->library->set_product_associations($referenced_entities, $product_record['entity_id']);
                            }
                        }
                    }
                }
                unset($product_record, $referenced_entities);
            }

            $hook_handler->call_hook("process_product_records_post", $product_class_def, $product_type, $product_records, $update_parameter);

            /**
             * set the product indexes dirty
             */
            indexer_set_product_attributes_dirty();
            indexer_set_product_flat_data_dirty();
            indexer_set_product_prices_dirty();
            indexer_set_catalog_url_rewrites_dirty();
            indexer_set_stock_status_dirty();
            indexer_set_tag_aggregation_data_dirty();
            indexer_set_catalog_search_index_dirty();
        }

        if ($product_type['product_type'] == "configurable")
        {
            $this->link_products();
        }


        unset($product_records);
    }

    /**
     *
     * @param array $product_record The product record is passed by reference and then pushed into the cart.
     * @param type $pattern
     */
    function process_url_key_pattern(&$product_record, $pattern)
    {
        /**
         * replace the tokens with the field values
         */
        preg_match_all("/{(.*?)}/", $pattern, $matches);

        if (is_array(($matches)))
        {
            $product_record["url_key"] = $pattern;

            for ($i = 0; $i < sizeof($matches[0]); $i++)
            {
                if (array_key_exists($matches[1][$i], $product_record))
                {
                    $product_record["url_key"] = str_replace($matches[0][$i], $product_record[$matches[1][$i]], $product_record["url_key"]);
                }
                else
                {
                    $product_record["url_key"] = str_replace($matches[0][$i], '', $product_record["url_key"]);
                }
            }

            /**
             * trim any excess spacers
             */
            $product_record["url_key"] = str_replace('__', '_', $product_record["url_key"]);
            $product_record["url_key"] = str_replace('#', '', $product_record["url_key"]);
            $product_record["url_key"] = str_replace('--', '-', $product_record["url_key"]);
            $product_record["url_key"] = str_replace("'", '', $product_record["url_key"]);
            $product_record["url_key"] = str_replace("/", '', $product_record["url_key"]);
            $product_record["url_key"] = str_replace(".", '', $product_record["url_key"]);

            if (substr($product_record["url_key"], -strlen('_')) === '_')
            {
                $product_record["url_key"] = substr($product_record["url_key"], 0, -1);
            }

            if (substr($product_record["url_key"], -strlen('-')) === '-')
            {
                $product_record["url_key"] = substr($product_record["url_key"], 0, -1);
            }

            /**
             * drop to lower no spaces
             */
            $product_record["url_key"] = strtolower($product_record["url_key"]);
            $product_record["url_key"] = str_replace(' ', '-', $product_record["url_key"]);
            $product_record["url_key"] = str_replace(array_keys($this->_convertTable), $this->_convertTable, $product_record["url_key"]);
            $product_record["url_path"] = $product_record["url_key"] . '.html';
        }
    }

    /**
     * Process data at the end of the type class combo
     *
     * @param array $product_class_def
     * @param rdi_field_mapping $product_type
     * @todo attribute fixin
     */
    function post_product_group_processing($product_class_def, $product_type)
    {
        global $load_price_variance;
        //@setting $load_price_variance This setting does not work. Do not use it.

        if (isset($load_price_variance) && $load_price_variance == 1)
        {
            //after processing the records, tell it to process the pricing data
            //process_attribute_price_updates($product_class_def, $product_type);
        }
    }

    /**
     * Disables a product that does not have a main image assigned.
     *
     */
    public function disable_product_for_image()
    {
        global $product_require_image;

        //@setting $product_require_image [0-OFF, 1-ON] Turn off/on whether or not it has an image. See the category load for more information on this setting.
        if (isset($product_require_image) && $product_require_image == 1)
        {
            $entity_type_id = $this->db_connection->cell("SELECT entity_type_id FROM {$this->prefix}eav_entity_type WHERE entity_type_code = 'catalog_product'", 'entity_type_id');

            $image_attribute_id = $this->db_connection->cell("SELECT attribute_id FROM {$this->prefix}eav_attribute WHERE attribute_code = 'image' AND entity_type_id = {$entity_type_id}", 'attribute_id');

            $status_attribute_id = $this->db_connection->cell("SELECT attribute_id FROM {$this->prefix}eav_attribute WHERE attribute_code = 'status' AND entity_type_id = {$entity_type_id}", 'attribute_id');

            $visibility_attribute_id = $this->db_connection->cell("SELECT attribute_id FROM {$this->prefix}eav_attribute WHERE attribute_code = 'visibility' AND entity_type_id = {$entity_type_id}", 'attribute_id');

            $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_varchar image
            LEFT JOIN {$this->prefix}catalog_product_super_link  sl
            ON sl.product_id = image.entity_id
            JOIN {$this->prefix}catalog_product_entity_int stat
            ON stat.entity_id = image.entity_id
            AND stat.attribute_id = {$status_attribute_id}
            AND stat.value = 1
            SET stat.value = 2
            WHERE  image.attribute_id = {$image_attribute_id}
            AND sl.parent_id IS NULL AND image.value = 'no_selection'");

            $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_varchar image
            LEFT JOIN {$this->prefix}catalog_product_super_link  sl
            ON sl.product_id = image.entity_id
            JOIN {$this->prefix}catalog_product_entity_int stat
            ON stat.entity_id = image.entity_id
            AND stat.attribute_id = {$visibility_attribute_id}
            AND stat.value != 1
            SET stat.value = 1
            WHERE  image.attribute_id = {$image_attribute_id}
            AND sl.parent_id IS NULL AND image.value = 'no_selection'");

            indexer_set_product_flat_data_dirty();
            indexer_set_category_flat_data_dirty();
            indexer_set_catalog_search_index_dirty();
        }
    }

    /**
     * Tax Class has a different value and does not use the eav_attribute_options/values so we must update like the visibility and status.
     * @global rdi_staging_db_lib $db_lib Staging library for the current POS
     * @todo Add the tableAr2 db function as an option to apply these tax codes/ids
     */
    public function update_tax_class_id()
    {
        global $db_lib;

        $_attributes = $this->db_connection->cells("SELECT
                                                 attribute_code,attribute_id FROM {$this->prefix}eav_attribute
                                                INNER JOIN {$this->prefix}eav_entity_type on {$this->prefix}eav_entity_type.entity_type_id = {$this->prefix}eav_attribute.entity_type_id
                                                WHERE attribute_code in('related_id','related_parent_id','tax_class_id')
                                                AND {$this->prefix}eav_entity_type.entity_type_code = 'catalog_product'", "attribute_id", "attribute_code");


        $style_table = $db_lib->get_table_name("in_styles");
        $item_table = $db_lib->get_table_name("in_items");
        $style_sid = $db_lib->get_style_sid();
        $item_sid = $db_lib->get_item_sid();
        $style_criteria = $db_lib->get_style_criteria();
        $item_criteria = $db_lib->get_item_criteria();
        $_tax_fields = $db_lib->get_tax_class_codes();

        //get the class names from magento
        $tax_class_names = $this->db_connection->cells("SELECT class_id, class_name FROM {$this->prefix}tax_class WHERE class_type = 'PRODUCT' ", "class_id", "class_name");

        //set the Exempt to be 0 if it isnt created.
        $tax_class_names['Exempt'] = isset($tax_class_names['Exempt']) ? $tax_class_names['Exempt'] : 0;

        $tax_mapping_table = $this->db_connection->row("SHOW TABLES LIKE 'rdi_tax_class_mapping'");

        $tax_sql = array();

        if (is_array($tax_mapping_table) && !empty($tax_mapping_table))
        {
            $tax_sql['field'] = " IFNULL(tc.class_id,0) ";
            $tax_sql['join'] = " LEFT JOIN rdi_tax_class_mapping rtcm
                                    ON rtcm.pos_type = {$_tax_fields['field_name']}
				LEFT JOIN {$this->prefix}tax_class tc
									ON tc.class_name = rtcm.cart_type
									";
        }
        else
        {
            $tax_sql['field'] = " IF({$_tax_fields['field_name']} = {$_tax_fields['taxable']}, {$tax_class_names['Taxable Goods']},IF({$_tax_fields['field_name']} = {$_tax_fields['exempt']},{$tax_class_names['Exempt']},{$tax_class_names['Taxable Goods']})) ";

            $tax_sql['join'] = " ";
        }

        $this->db_connection->exec("INSERT INTO {$this->prefix}catalog_product_entity_int(entity_type_id, entity_id, store_id, attribute_id, `value`)
                SELECT related_id.entity_type_id, related_id.entity_id, related_id.store_id, {$_attributes['tax_class_id']} AS  `attribute_id`, {$tax_sql['field']} AS `value` FROM {$style_table} style
                JOIN {$item_table} item
                ON item.{$style_sid} = style.{$style_sid}
                {$item_criteria}

                {$tax_sql['join']}

                JOIN {$this->prefix}catalog_product_entity_varchar related_id
                ON related_id.value = item.{$item_sid}
                AND related_id.attribute_id = {$_attributes['related_id']}

                LEFT JOIN {$this->prefix}catalog_product_entity_int tax_class_id
                ON tax_class_id.entity_id = related_id.entity_id
                AND tax_class_id.attribute_id =  {$_attributes['tax_class_id']}

                WHERE {$style_criteria}
                ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);
                ");

        $this->db_connection->exec("INSERT INTO {$this->prefix}catalog_product_entity_int (value_id,entity_type_id,attribute_id,store_id,entity_id,`value`)
                                    SELECT tciP.value_id, tciP.entity_type_id, tciP.attribute_id, tciP.store_id, tciP.entity_id,MIN(tciC.value) AS `value` FROM {$this->prefix}catalog_product_entity_int tciP
                                    JOIN {$this->prefix}catalog_product_super_link sl
                                    ON sl.parent_id = tciP.entity_id
                                    JOIN {$this->prefix}catalog_product_entity_int tciC
                                    ON tciC.entity_id = sl.product_id
                                    AND tciC.attribute_id = {$_attributes['tax_class_id']}
                                    WHERE tciP.attribute_id = {$_attributes['tax_class_id']}
                                    GROUP BY tciP.entity_id
                                     ON DUPLICATE KEY UPDATE `value` = VALUES(`value`);");
    }

    //@todo need to add grouped for this part.
    /**
     * This is run at the end of each configurable product run.
     * @global stagingdb $db_lib
     * @todo pass in the product type to handle grouped products.
     */
    public function link_products()
    {
        global $db_lib, $keep_configurable_associations;

        $this->_echo(__FUNCTION__);

        $related_id = $this->db_connection->cell("SELECT attribute_id from {$this->prefix}eav_attribute ea
									join {$this->prefix}eav_entity_type et
									on et.entity_type_id = ea.entity_type_id
									and et.entity_type_code = 'catalog_product'
									where ea.attribute_code = 'related_id'", 'attribute_id');

        $this->db_connection->exec("CREATE TEMPORARY TABLE rdi_super_link_parents_temp (UNIQUE(entity_id)) AS SELECT DISTINCT parent.entity_id FROM {$db_lib->get_table_name('in_styles')} style
                                                            JOIN {$this->prefix}catalog_product_entity_varchar parent
                                                            ON parent.value = style.{$db_lib->get_style_sid()}
                                                            AND parent.attribute_id = {$related_id}
                                                            WHERE {$db_lib->get_style_criteria()}", "entity_id");

        //if(!empty($configs))
        //{
        //$parents = implode(',', $configs);
        //add super_links
        $this->db_connection->exec("INSERT INTO {$this->prefix}catalog_product_super_link (parent_id, product_id)
											SELECT DISTINCT
											  parent.entity_id AS parent_id,
											  child.entity_id AS product_id
											FROM
											  {$db_lib->get_table_name('in_styles')} style
											  INNER JOIN {$db_lib->get_table_name('in_items')} item
												ON style.{$db_lib->get_style_sid()} = item.{$db_lib->get_style_sid()}
												{$db_lib->get_item_avail_criteria()}
											JOIN {$this->prefix}catalog_product_entity_varchar parent
											ON parent.value = style.{$db_lib->get_style_sid()}
											AND parent.attribute_id = {$related_id}
											JOIN rdi_super_link_parents_temp slpt
											on slpt.entity_id = parent.entity_id
											JOIN {$this->prefix}catalog_product_entity_varchar child
											ON child.value = item.{$db_lib->get_item_sid()}
											AND child.attribute_id = {$related_id}
											LEFT JOIN {$this->prefix}catalog_product_super_link sl
											ON sl.product_id = child.entity_id
											AND sl.parent_id = parent.entity_id
											WHERE sl.parent_id IS  NULL");

        //add product_relations
        $this->db_connection->exec("INSERT INTO {$this->prefix}catalog_product_relation (parent_id, child_id)
											SELECT DISTINCT
											  parent.entity_id AS parent_id,
											  child.entity_id AS child_id
											FROM
											  {$db_lib->get_table_name('in_styles')} style
											  INNER JOIN {$db_lib->get_table_name('in_items')} item
												ON style.{$db_lib->get_style_sid()} = item.{$db_lib->get_style_sid()}
												{$db_lib->get_item_avail_criteria()}
											JOIN {$this->prefix}catalog_product_entity_varchar parent
											ON parent.value = style.{$db_lib->get_style_sid()}
											AND parent.attribute_id = {$related_id}
											JOIN rdi_super_link_parents_temp slpt
											on slpt.entity_id = parent.entity_id
											JOIN {$this->prefix}catalog_product_entity_varchar child
											ON child.value = item.{$db_lib->get_item_sid()}
											AND child.attribute_id = {$related_id}
											LEFT JOIN {$this->prefix}catalog_product_relation r
											ON r.child_id = child.entity_id
											AND r.parent_id = parent.entity_id
											WHERE r.parent_id IS  NULL");
        //@setting $keep_configurable_associations [0-OFF, 1-ON]
        if (isset($keep_configurable_associations) && $keep_configurable_associations == 1)
        {
            
        }
        else
        {
            // remove product links
            $this->db_connection->exec("DELETE sl.*
                                                            FROM {$this->prefix}catalog_product_super_link sl
                                                            JOIN {$this->prefix}catalog_product_entity_varchar parent
                                                            ON parent.entity_id = sl.parent_id
                                                            AND parent.attribute_id = {$related_id}
															JOIN rdi_super_link_parents_temp slpt
															on slpt.entity_id = parent.entity_id
                                                            JOIN {$this->prefix}catalog_product_entity_varchar child
                                                            ON child.entity_id = sl.product_id
                                                            AND child.attribute_id = {$related_id}
                                                            LEFT JOIN {$db_lib->get_table_name('in_items')} item
                                                            ON item.{$db_lib->get_style_sid()} = parent.value
                                                            AND item.{$db_lib->get_item_sid()} = child.value
                                                            {$db_lib->get_item_avail_criteria()}
                                                            WHERE item.{$db_lib->get_item_sid()} IS NULL");

            // remove product links
            $this->db_connection->exec("DELETE r.*
                                                        FROM {$this->prefix}catalog_product_relation r
                                                    JOIN {$this->prefix}catalog_product_entity_varchar parent
                                                    ON parent.entity_id = r.parent_id
                                                    AND parent.attribute_id = {$related_id}
													JOIN rdi_super_link_parents_temp slpt
													on slpt.entity_id = parent.entity_id
                                                    JOIN {$this->prefix}catalog_product_entity_varchar child
                                                    ON child.entity_id = r.child_id
                                                    AND child.attribute_id = {$related_id}
                                                    LEFT JOIN {$db_lib->get_table_name('in_items')} item
                                                    ON item.{$db_lib->get_style_sid()} = parent.value
                                                    AND item.{$db_lib->get_item_sid()} = child.value
                                                    {$db_lib->get_item_avail_criteria()}
                                                    WHERE item.{$db_lib->get_item_sid()} IS NULL");
        }

        $this->db_connection->exec("DROP TABLE rdi_super_link_parents_temp");



        //}
    }

    public function get_single_products_criteria($style_value)
    {

        $related_attribute_id = $this->db_connection->cell("SELECT
                                                         attribute_code,attribute_id FROM {$this->prefix}eav_attribute
                                                        INNER JOIN {$this->prefix}eav_entity_type on {$this->prefix}eav_entity_type.entity_type_id = {$this->prefix}eav_attribute.entity_type_id
                                                        WHERE attribute_code in('related_id')
                                                        AND {$this->prefix}eav_entity_type.entity_type_code = 'catalog_product'", "attribute_id", "attribute_code");

        $out = array();
        $out['join'] = "left join {$this->prefix}catalog_product_entity_varchar r
                        on r.value = {$style_value}
                        and r.attribute_id = {$related_attribute_id}";

        $out['where'] = "AND r.value IS NULL";

        return $out;
    }

    public function set_display_only()
    {
        $this->_echo("Set products that are set to display only in their rdi_avail field.", "h5");

        $attributes = $this->db_connection->cells("SELECT attribute_id, attribute_code FROM {$this->prefix}eav_attribute
                                                        INNER JOIN {$this->prefix}eav_entity_type on {$this->prefix}eav_entity_type.entity_type_id = {$this->prefix}eav_attribute.entity_type_id
                                                        WHERE {$this->prefix}eav_entity_type.entity_type_code = 'catalog_product'", "attribute_id", "attribute_code");

        if (isset($attributes['rdi_avail']))
        {
            //set out of stock
            $this->db_connection->exec("UPDATE {$this->prefix}cataloginventory_stock_item i
										JOIN {$this->prefix}catalog_product_entity_varchar v
										on v.entity_id = i.product_id
										and v.value IN('Sell Never', 'Display Only')
										and v.attribute_id = {$attributes['rdi_avail']}
										SET i.is_in_stock = 0, i.manage_stock = 0");

            //set enable
            $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_int i
										JOIN {$this->prefix}catalog_product_entity_varchar v
										on v.entity_id = i.entity_id
										and v.value = 'Display Only'
										and v.attribute_id = {$attributes['rdi_avail']}
										SET i.value = 1
										where i.attribute_id = {$attributes['status']}");

            $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_int i
										JOIN {$this->prefix}catalog_product_entity_varchar v
										on v.entity_id = i.entity_id
										and v.value = 'Sell Never'
										and v.attribute_id = {$attributes['rdi_avail']}
										SET i.value = 2
										where i.attribute_id = {$attributes['status']}");

            //set visible
            $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_int i
										JOIN {$this->prefix}catalog_product_entity_varchar v
										on v.entity_id = i.entity_id
										and v.value = 'Display Only'
										and v.attribute_id = {$attributes['rdi_avail']}
										SET i.value = 4
										where i.attribute_id = {$attributes['visibility']}");

            $this->db_connection->exec("UPDATE {$this->prefix}catalog_product_entity_int i
										JOIN {$this->prefix}catalog_product_entity_varchar v
										on v.entity_id = i.entity_id
										and v.value = 'Sell Never'
										and v.attribute_id = {$attributes['rdi_avail']}
										SET i.value = 1
										where i.attribute_id = {$attributes['visibility']}");
        }
    }

    /**
     *
     * @global type $cart
     * @return type
     */
    public function get_exclude_from_web_parameters()
    {
        global $cart;

        $parms = array();

        $parms['join'] = "JOIN {$this->prefix}catalog_product_entity_varchar r
                            ON r.value = i.item_sid
                            AND r.attribute_id = {$cart->get_processor("rdi_cart_common")->get_attribute("related_id")}
                            JOIN {$this->prefix}catalog_product_super_link sl
                            ON sl.product_id = r.entity_id";

        $params['fields'] = " sl.* ";

        return $parms;
    }

    public function set_default_weights()
    {
        return null;
        $attribute_names = "'related_id','related_parent_id','weight'";

        $entity_type_code = "catalog_product";

        $default_weight = $this->db_connection->cell("SELECT DISTINCT `default_value` FROM rdi_field_mapping WHERE cart_field = 'weight' AND field_type = 'product'", 'default_value');

        if (is_numeric($default_weight))
        {

            $_attributes = $this->db_connection->cells("SELECT
                                                                        attribute_code,attribute_id FROM {$this->prefix}eav_attribute
                                                                       INNER JOIN {$this->prefix}eav_entity_type on {$this->prefix}eav_entity_type.entity_type_id = {$this->prefix}eav_attribute.entity_type_id
                                                                       WHERE attribute_code in('related_id','related_parent_id',{$attribute_names})
                                                                       AND {$this->prefix}eav_entity_type.entity_type_code = '{$entity_type_code}'", "attribute_id", "attribute_code");

            $this->_comment("Set Weight to a default of {$default_weight}, there is a type conversion problem with updating weights.");

            $this->db_connection->exec("update {$this->prefix}catalog_product_entity_decimal d
                                                join {$this->prefix}catalog_product_entity e
                                                on e.entity_id = d.entity_id
                                                and e.type_id = 'simple'
                                                set d.value = {$default_weight}
                                                 where attribute_id = {$_attributes['weight']}
                                                 and ifnull(value,0) = 0");

            $r = $this->db_connection->rows("SELECT DISTINCT e.entity_id, e.entity_type_id, 0 AS store_id, {$_attributes['weight']} AS attribute_id, {$default_weight} AS `value` FROM {$this->prefix}catalog_product_entity e
                                                        LEFT JOIN  {$this->prefix}catalog_product_entity_decimal d
                                                        ON d.entity_id = e.entity_id
                                                        AND d.attribute_id = {$_attributes['weight']}
                                                         WHERE e.type_id = 'simple'
                                                         AND IFNULL(VALUE,0) = 0");

            if (!empty($r))
            {
                foreach ($r as $row)
                {
                    $this->db_connection->insertAr2("{$this->prefix}catalog_product_entity_decimal", $row, false, array(), array('value'));
                }
            }
        }

        return $this;
    }

    /**
     * Updated the super_attributes option_values if the update_products and update_super_attribute_values is turned on.
     * This can only be used with the proper mapping. With out the right mapping it will epically crash.
     * @global type $update_super_attribute_values
     * @global rdi_field_mapping $field_mapping
     * @global stagingdb $db_lib
     * @global type $update_products
     * @global type $attribute_ids
     * @global type $product_entity_type_id
     */
    public function update_super_attributes_values()
    {
        global $update_super_attribute_values, $field_mapping, $db_lib, $update_products, $attribute_ids, $product_entity_type_id, $updated_products;

        if (!isset($product_entity_type_id))
        {
            $product_entity_type_id = $this->db_connection->cell("SELECT entity_type_id FROM {$this->prefix}eav_entity_type WHERE entity_type_code = 'catalog_product'", "entity_type_id");
        }

        if (!isset($attribute_ids) || count($attribute_ids) == 0)
            $this->library->get_all_product_attribute_ids();


        if ((isset($update_products) && $update_products == 1) &&
                (isset($update_super_attribute_values) && $update_super_attribute_values == 1))
        {
            $related_attribute_id = $this->db_connection->cell("SELECT attribute_id FROM {$this->prefix}eav_attribute WHERE attribute_code = 'related_id' and entity_type_id = {$product_entity_type_id}", "attribute_id");

            $field_mapping->set_option_label_mapping();

            foreach ($field_mapping->_option_mapping as $i => $option_label)
            {
                $pos_field = $field_mapping->get_option_mapping($i);

                //might add this later. It doesnt appear to be the same sort order we are looking for from any of the POS
                //$sort_order_field 	= $field_mapping->get_option_sort_order_mapping($i);

                if (strstr($option_label, $db_lib->alias['in_styles']))
                {
                    $staging_tables = "JOIN {$db_lib->get_table_name('in_styles')} {$db_lib->alias['in_styles']}
												ON {$option_label} = mp.pos_field
												JOIN {$db_lib->get_table_name('in_items')} {$db_lib->alias['in_items']}
												ON {$db_lib->alias['in_items']}.{$db_lib->get_style_sid()} = {$db_lib->alias['in_styles']}.{$db_lib->get_style_sid()}";
                }
                else
                {
                    $staging_tables = "	JOIN {$db_lib->get_table_name('in_items')} {$db_lib->alias['in_items']}
												ON {$option_label} = mp.pos_field";
                }


                $updates = $this->db_connection->rows("SELECT DISTINCT r.entity_id, ea.attribute_code, ea.attribute_id, {$pos_field} as pos_field, 0 as position FROM rdi_field_mapping m
												JOIN rdi_field_mapping_pos mp
												ON mp.field_mapping_id = m.field_mapping_id
												JOIN {$this->prefix}eav_attribute ea
												ON ea.attribute_code = m.cart_field
												AND ea.entity_type_id = {$product_entity_type_id}
												{$staging_tables}
												JOIN {$this->prefix}catalog_product_entity_varchar r
												ON r.value = {$db_lib->alias['in_items']}.{$db_lib->get_item_sid()}
												AND r.attribute_id = {$related_attribute_id}
												LEFT JOIN {$this->prefix}catalog_product_entity_int i
												ON i.entity_id = r.entity_id
												AND i.attribute_id = ea.attribute_id
												LEFT JOIN {$this->prefix}eav_attribute_option o
												ON o.option_id = i.value
												AND o.attribute_id = ea.attribute_id
												LEFT JOIN {$this->prefix}eav_attribute_option_value ov
												ON ov.option_id = o.option_id
                                                                                                AND ov.store_id = 0
												WHERE m.field_type = 'attributes'
												AND m.allow_update = 1
												AND IFNULL({$pos_field},'') != BINARY IFNULL(ov.value,'')");

                if (!empty($updates))
                {
                    foreach ($updates as $product)
                    {
                        $option_id = $this->library->add_attribute_option_value($product['attribute_code'], $product['pos_field'], $product['position'], $product['entity_id']);
                        $updated_products[] = $product['entity_id'];
                        $this->library->update_product_attribute_field_table($product['entity_id'], 'int', '0', array($product['attribute_id'] => $option_id));
                    }
                }
            }
        }
    }
    
    public function update_super_attributes()
    {
        global $update_magento_super_attributes, $store_id;
        
        if(isset($update_magento_super_attributes) && $update_magento_super_attributes != '1')
        {
            return false;
        }
        
        global $field_mapping, $cart, $db_lib;
    $product_load = new rdi_product_load($this->db_connection);

        $cart_product_processor = $cart->get_processor('rdi_cart_product_load');

        $product_classes = $product_load->get_product_classes();

        //$field_mapping = new rdi_field_mapping($this->db_connection, $GLOBALS['ignore_warnings']);

        $bad_fields = array('custom_design',
                                    'custom_design_from',
                                    'custom_design_to',
                                    'custom_layout_update',
                                    'is_recurring',
                                    'low_stock_date',
                                    'options_container',
                                    'product_image',
                                    'rdi_last_updated',
                                    'related_parent_id',
                                    'short_description',
                                    'stock_status_changed_auto',
                                    'thumbnail',
                                    'url_path',
                                    'url_path',
                                    'use_config_backorders',
                                    'use_config_enable_qty_inc',
                                    'use_config_manage_stock',
                                    'use_config_max_sale_qty',
                                    'use_config_min_qty',
                                    'use_config_min_sale_qty',
                                    'use_config_notify_stock_qty',
                                    'use_config_qty_increments','color_sort_order','related_id','size_sort_order');

            $attributes = $this->db_connection->rows("select attribute_id,
                                                    attribute_code,
                                                    backend_type,
                                                    frontend_input,
                                                    source_model,
                                                    is_user_defined
                                                from {$this->prefix}eav_attribute ea
                                                                                            join {$this->prefix}eav_entity_type et
                                                                                            on et.entity_type_id = ea.entity_type_id
                                                                                            and et.entity_type_code = 'catalog_product'");

            $attribute_ids = array();

            foreach ($attributes as $attribute)
            {
                    $attribute_ids[$attribute['attribute_code']] = array("attribute_id" => $attribute['attribute_id'],
                            "type" => $attribute['backend_type'],
                            "front_type" => $attribute['frontend_input'],
                            "source_model" => $attribute['source_model'],
                            "attribute_code" => $attribute['attribute_code'],
                            "is_user_defined" => $attribute['is_user_defined']);
            }

        foreach($product_classes as $product_class)
        {
            foreach($product_class['product_types'] as $product_type)
            {
                            if($product_type['product_type'] == 'configurable')
                            {
                                    $product_insert_parameters = $cart->get_processor("rdi_cart_product_load")->get_product_insert_parameters($product_class, $product_type);

                                    $product_insert_parameters['index'] = 0;
                                    unset($product_insert_parameters['where']);
                                    $where = $db_lib->get_style_criteria();
                                    if(is_array($product_class['query_criteria']))
                                    {                            
                                            foreach($product_class['query_criteria'] as $record)
                                            {
                                                    if($record['qualifier'] != '')
                                                            $where .= " AND {$record['pos_field']} {$record['qualifier']}";
                                            }
                                    }

                                    $product_records = $this->db_connection->rows("SELECT DISTINCT {$db_lib::$alias['in_styles']}.{$db_lib->get_style_sid()} AS style_id, {$this->prefix}catalog_product_entity.entity_id
                                                                                                                            FROM {$db_lib->get_table_name('in_styles')} {$db_lib::$alias['in_styles']} 
                                                                                                                            INNER JOIN {$db_lib->get_table_name('in_items')} {$db_lib::$alias['in_items']}  
                                                                                                                            ON {$db_lib::$alias['in_styles']}.{$db_lib->get_style_sid()} = {$db_lib::$alias['in_items']}.{$db_lib->get_style_sid()}	
                                                                                                                            {$product_insert_parameters['join']}
                                                                                                                            WHERE {$where}
                                                                                                                            Group by {$product_insert_parameters['group_by']} 
                                                                                                                            order by style_id limit 0, 5000 ");
                                    if(!empty($product_records))
                                    {					
                                            foreach($product_records as $product_data)
                                            {
                                                    if (is_array($product_class['field_data']))
                                                    {
                                                            $super_attributes = array();

                                                            if (isset($use_super_attribute_mapping) && $use_super_attribute_mapping == 1)
                                                            {
                                                                    $super_attributes = $field_mapping->attribute_mapping_to_field_data($product_data['style_id']);
                                                            }
                                                            else
                                                            {
                                                                    $super_attributes = $product_class['field_data'];
                                                            }

                                                            if (!empty($super_attributes))
                                                            {
                                                                    $current_super_attributes = $this->db_connection->cells("SELECT position, attribute_id FROM `{$this->prefix}catalog_product_super_attribute` WHERE product_id = '{$product_data['entity_id']}'","attribute_id","position");
                                                                    $new_super_attributes = array();
                                                                    foreach ($super_attributes as $attr)
                                                                    {
                                                                            if (isset($attribute_ids[$attr['cart_field']]['attribute_id']))
                                                                            {
                                                                                    $new_super_attributes[$attr['position']] = $attribute_ids[$attr['cart_field']]['attribute_id'];
                                                                            }
                                                                    }

                                                                    if($current_super_attributes !== $new_super_attributes)
                                                                    {
                                                                            $this->db_connection->exec("DELETE FROM {$this->prefix}catalog_product_super_attribute WHERE product_id = {$product_data['entity_id']}");

                                                                            //tell the configurable that it is using these types of values for its options
                                                                            foreach ($super_attributes as $attr)
                                                                            {
                                                                                    if (isset($attribute_ids[$attr['cart_field']]['attribute_id']))
                                                                                    {
                                                                                            $product_super_attribute_id = $this->db_connection->insert("INSERT INTO `{$this->prefix}catalog_product_super_attribute` (`product_id`, `attribute_id`, `position`)
                                                                                                                            VALUES ({$product_data['entity_id']}, {$attribute_ids[$attr['cart_field']]['attribute_id']}, {$attr['position']})");

                                                                                            $attribute_label = $attr['label'] == null || $attr['label'] == '' ? $attr['cart_field'] : $attr['label'];

                                                                                            $this->db_connection->insert("INSERT INTO `{$this->prefix}catalog_product_super_attribute_label` (`product_super_attribute_id`, `store_id`, `use_default`, `value`)
                                                                                                                            VALUES ({$product_super_attribute_id}, {$store_id}, 0, '{$attribute_label}')");
                                                                                    }
                                                                            }									
                                                                    }								
                                                            }
                                                    }
                                            }
                                    }
                            }            
            }
        }
    }

    /**
     *
     */
    public function update_availability()
    {
        $updated = $this->library->update_availability();

        //if this did come changes, we want to mark the stock status indexer and the catalog_category_product.
        if ($updated)
        {
            indexer_set_category_products_dirty();
            indexer_set_stock_status_dirty();
        }
    }

    /**
     *
     */
    public function process_out_of_stock()
    {
        $updated = $this->library->process_out_of_stock();

        if ($updated)
        {
            indexer_set_category_products_dirty();
            indexer_set_stock_status_dirty();
        }
    }

    /**
     *
     * @global rdi_debug $debug
     * @param type $entity_type_code
     */
    public function enterprise_url_key_update($entity_type_code = 'catalog_product')
    {
        global $debug;

        $enterprise = $this->db_connection->rows("SHOW TABLES LIKE '{$this->prefix}enterprise%'");

        if (!empty($enterprise))
        {
            $url_key_attribute_id = $this->db_connection->cell("SELECT ea.attribute_id FROM {$this->prefix}eav_attribute ea
                    JOIN {$this->prefix}eav_entity_type et
                    on et.entity_type_id = ea.entity_type_id
                    AND et.entity_type_code = '{$entity_type_code}'
                    WHERE ea.attribute_code = 'url_key'", 'attribute_id');

            $urls = $this->db_connection->rows("SELECT DISTINCT v.entity_type_id,
         v.attribute_id,
         v.store_id,
         v.entity_id,
         v.value FROM {$this->prefix}{$entity_type_code}_entity_varchar v
                    LEFT JOIN {$this->prefix}{$entity_type_code}_entity_url_key u
                    ON u.entity_id = v.entity_id
                    AND u.attribute_id = v.attribute_id
                    AND u.store_id = v.store_id
                    WHERE v.attribute_id = {$url_key_attribute_id}
                    AND v.store_id = 0
                    AND  u.value IS NULL");

            if (!empty($urls))
            {
                $url_count = count($urls);

                $debug->write(basename(__FILE__), __FUNCTION__, "Found {$url_count} {$entity_type_code} urls to insert.");

                foreach ($urls as $url)
                {
                    $this->db_connection->insertAr2("{$this->prefix}{$entity_type_code}_entity_url_key", $url, false, array(), array('value'));
                }
            }
        }
    }

}

?>
