<?php
/**
 * Class File
 */

/**
 * Magento Avail Updates
 * This updates quantity, stock and the statuses related with the stock.
 * 
 * @author  PMBLISS <pmbliss@retaildimensions.com>
 * @copyright (c)  2005-2014 Retail Dimensions Inc.
 * @date    12272013
 * @package Core\Load\Product\Avail\Magento
 */
class rdi_cart_avail_updates extends rdi_general {

    //private $db_connection;
    private $fields;
    private $replace_fields;
    private $select_fields;
    private $pos_update_parameters;
    private $attribute_ids;
    //private $prefix;
    private $mapping;
    private $on_duplicate_update;
    private $error;
    private $entity_type;

    //private $prefix;

    /**
     * Constructor Function
     * @param type $db
     */
    public function rdi_cart_avail_updates($db = '')
    {
        if ($db)
            $this->set_db($db);

        $this->error = false;
        $this->entity_type = 'simple';
        //$this->prefix = $db->get_db_prefix();
    }

    /**
     * Pre Load Function
     * @global rdi_hook_handler $hook_handler
     * @return \rdi_cart_avail_updates
     */
    public function pre_load()
    {
        global $hook_handler;

        //@hook avail_updates_pre_load
        $hook_handler->call_hook("avail_updates_pre_load");

        return $this;
    }

    /**
     * Post Load Function
     * @global rdi_hook_handler $hook_handler
     * @return \rdi_cart_avail_updates
     */
    public function post_load()
    {
        global $hook_handler, $cart;

        //@hook avail_update_post_load
        $hook_handler->call_hook("avail_updates_post_load");

        return $this;
    }

    /**
     * Check if there are any products before going into this class, but it also checks a little bit earlier. This could be run seperately and we must check if there are products in the staging. rdi_avail is a required product attribute in magento.
     * @global rdi_staging_db_lib $db_lib
     * @return \rdi_cart_avail_updates
     */
    public function load()
    {
        global $db_lib;

        if ($db_lib->get_product_count() > 0 && !$this->error)
        {
            $this->pre_load()->set_mapping()->update_available_stock_qty()->post_load();
        }

        return $this;
    }

    /**
     * Update the stock and all fields on the cataloginventory_stock_item table. This uses the avail mapping which can be found in the install scripts for each of the point of sales.
     * @global rdi_debug $debug
     * @return \rdi_cart_avail_updates
     * @todo This should probably use insertAr2 and not a bulk query. 
     */
    public function update_available_stock_qty()
    {
        global $debug;

        /**
         * mapping is not set so lets just skip it
         */
        if (empty($this->mapping))
        {
            $debug->write(basename(__FILE__), __CLASS__, __FUNCTION__, 0, "Add Mapping to Continue with Avail Updates");
            return $this;
        }

        /**
         * get all the values we are going to need
         */
        $this->set_fields()->set_on_duplicate_update()->set_string_replace_fields()->set_string_select_fields()->set_pos_update_parameters()->set_attribute_ids(array('related_id', 'rdi_avail'))->set_comparison_query();

        /**
         * need to bail if rdi_avail isnt mapped
         */
        if (isset($this->attribute_ids['rdi_avail']) && $this->attribute_ids['rdi_avail'] > 0)
        {

            $sql = " INSERT INTO {$this->prefix}cataloginventory_stock_item ({$this->replace_fields}) ";
            $sql .= " SELECT {$this->select_fields} FROM ";
            $sql .= " {$this->pos_update_parameters['join']} ";

            $sql .= " JOIN {$this->prefix}catalog_product_entity_varchar rp
						ON rp.value = {$this->pos_update_parameters['connect_on']}
						AND rp.attribute_id = {$this->attribute_ids['related_id']}
					  JOIN {$this->prefix}catalog_product_entity_varchar avail
						ON avail.entity_id = rp.entity_id
						AND avail.attribute_id = {$this->attribute_ids['rdi_avail']}
						JOIN {$this->prefix}cataloginventory_stock_item csi
						ON csi.product_id = rp.entity_id";

            $sql .= $this->comparison;
            $sql .= " {$this->pos_update_parameters['where']} ";

            $sql .= $this->on_duplicate_update;

            $updated = $this->db_connection->insert($sql);
        }
        else
        {
            $debug->write(basename(__FILE__), __CLASS__, __FUNCTION__, 0, "Please add the attribute rdi_avail.");
        }

        return $this;
    }

    //
    /**
     * All the fields contained on that table;
     * @return \rdi_cart_avail_updates
     */
    public function set_fields()
    {
        $this->fields = $this->db_connection->cells("SHOW COLUMNS FROM {$this->prefix}cataloginventory_stock_item", 'Field');

        return $this;
    }

    
    /**
     * all the fields fields in a string;
     * @return \rdi_cart_avail_updates
     */
    public function set_string_replace_fields()
    {
        $this->replace_fields = implode(",", $this->fields);

        return $this;
    }

    /**
     * Set the fields that will be in the table we are about to select.
     * @return \rdi_cart_avail_updates
     */
    public function set_string_select_fields()
    {
        $this->fields;
        $_fields_new = array();
        /**
         * process fields to select
         */

        foreach ($this->fields as $field)
        {
            if (array_key_exists($field, $this->mapping))
            {
                $_fields_new[] = "{$this->mapping[$field]} AS '$field'";
            }
            else
            {
                $_fields_new[] = "csi.$field";
            }
        }

        $this->select_fields = implode(",", $_fields_new);

        return $this;
    }
    /**
     * Set the avail mapping for this product type.
     * @return \rdi_cart_avail_updates
     */
    public function set_mapping()
    {
        if ($this->entity_type == 'simple' || $this->entity_type == 'configurable')
        {
            $this->mapping = $this->db_connection->cells("SELECT cart_field, pos_field FROM rdi_field_mapping m
															JOIN rdi_field_mapping_pos mp
															ON mp.field_mapping_id = m.field_mapping_id
															WHERE m.field_type = 'avail' 
															AND (entity_type = '{$this->entity_type}' OR entity_type IS NULL)", "pos_field", "cart_field");
        }
        else
        {
            $this->error = true;
        }

        return $this;
    }

    
    /**
     * Set the POS update parameters for avail. Works for Retail Pro Versions.
     * 
     * @todo This function should be in the POS and then update the private value here.
     * @global type $pos
     * @global rdi_staging_db_lib $db_lib
     * @return \rdi_cart_avail_updates
     */
    public function set_pos_update_parameters()
    {
        global $pos, $db_lib;

        $this->pos_update_parameters['join'] = "{$db_lib->get_table_name('in_styles')} style
                                            JOIN {$db_lib->get_table_name('in_items')} item
                                            ON item.{$db_lib->get_style_sid()} = style.{$db_lib->get_style_sid()}
                                            {$db_lib->get_item_criteria()}";

        if (!isset($this->pos_update_parameters['connect_on']))
        {
            $this->pos_update_parameters['connect_on'] = "item.{$db_lib->get_item_sid()}";
        }

        $this->pos_update_parameters['where'] = "WHERE {$db_lib->get_style_criteria()}";

        return $this;
    }

    /**
     * Get the attribute_ids and save them in the class.
     * @param array $_attribute All attributes we will need for products.
     * @return \rdi_cart_avail_updates
     */
    public function set_attribute_ids($_attribute)
    {
        $attributes = "'" . implode("','", $_attribute) . "'";
        $this->attribute_ids = $this->db_connection->cells("select attribute_code,attribute_id from {$this->prefix}eav_attribute ea
                                                join {$this->prefix}eav_entity_type et
                                                on et.entity_type_id = ea.entity_type_id
                                                where ea.attribute_code in({$attributes})
                                                and entity_type_code = 'catalog_product'", "attribute_id", "attribute_code");

        return $this;
    }

    /**
     * Set the database prefix in the class.  It should already be set from the extension off rdi_general.
     * @param type $prefix
     * @return \rdi_cart_avail_updates
     */
    public function set_prefix($prefix)
    {
        $this->prefix = $this->db_connection->prefix;
        return $this;
    }

    /**
     * This will go through and our fields that we are allowing to update on the insert into the inventory table.
     * @return \rdi_cart_avail_updates
     * @todo This function will be handled by insertAr2
     * @todo Use rdi_field_mapping allow_update to set which fields are updated on the insert.
     */
    public function set_on_duplicate_update()
    {
        $this->on_duplicate_update = "";

        if (isset($this->mapping) && !empty($this->mapping))
        {
            $this->on_duplicate_update .= " ON DUPLICATE KEY UPDATE ";

            $_sql_tail = array();

            foreach ($this->mapping as $field => $value)
            {
                $_sql_tail[] = "`{$field}` = VALUES(`{$field}`)";
            }
            $this->on_duplicate_update .= implode(",", $_sql_tail);
        }

        return $this;
    }

    /**
     * Non Stock Items are configurables, bundles, grouped(in most cases)
     * @global type $pos
     * @return \rdi_cart_avail_updates
     */
    public function set_nonstock_items()
    {
        global $pos;

        $staging = $pos->get_processor('rdi_staging_db_lib');

        $this->entity_type == 'configurable';

        $this->pos_update_parameters['connect_on'] = "style.{$staging->get_style_sid()}";



        return $this;
    }

    /**
     * Sets the main comparison clause in the inventory stock query. We use a binary comparison on more fields.
     */
    public function set_comparison_query()
    {
        $this->comparison = "";

        if (!empty($this->mapping))
        {
            $_comparison = array();

            foreach ($this->mapping as $field => $map)
            {
                $_comparison[] = "csi.{$field} != BINARY {$map}";
            }

            $this->comparison = " AND (" . implode(" OR ", $_comparison) . ") ";
        }
    }

}

?>
