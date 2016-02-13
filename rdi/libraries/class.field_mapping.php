<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Handle the preping of sql statements for so they get mapped to perform the correct select statement
 * 
 * concantencation of the POS field is always the case.
 * 
 * PHP version 5.3
 *
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    2.0.0
 * @package    Core\FieldMapping
 */
//left as field_mapping until the normal global can be phased out.
class field_mapping extends rdi_general {

    protected $ignore_warn;
    //entire storage
    public $mapped_fields;
    //last mapped_fields
    public $_mapped_fields;
    //generic cart mapping array, overwritten in the cart class if needed.
    //fields should end with a comma
    public $cart_mapping = array("fields" => "", "join" => "");
    public $cart;
    public $pos;

    /**
     * Class Constructor
     *
     * @param rdi_db $db
     */
    public function __construct($db = '')
    {
        $this->mapped_fields = array();

        parent::__construct($db);

        $this->load_library_field_mapping();
    }

    public function load_library_field_mapping()
    {
        global $cart_type, $pos_type;

        if (file_exists("libraries/cart_libs/{$cart_type}/{$cart_type}_field_mapping.php"))
        {
            include_once "libraries/cart_libs/{$cart_type}/{$cart_type}_field_mapping.php";

            $this->cart = new cart_field_mapping($this->db_connection);
        }

        if (file_exists("libraries/cart_libs/{$pos_type}/{$pos_type}_field_mapping.php"))
        {
            include_once "libraries/cart_libs/{$pos_type}/{$pos_type}_field_mapping.php";

            $this->pos = new pos_field_mapping($this->db_connection);
        }
    }

    private function validate_mapping()
    {
        $this->cart_validate_mapping()->pos_validate_mapping();
    }

    //place holder for the cart
    private function cart_validate_mapping()
    {
        return $this;
    }

    //place holder for the pos
    private function pos_validate_mapping()
    {
        return $this;
    }

    private function store_mapping($field_type, $field_classification, $entity_type)
    {
        if (!isset($this->mapped_fields[$field_type]))
        {
            $this->mapped_fields[$field_type] = array();
        }

        //false could be the key.
        if (!isset($this->mapped_fields[$field_type][$field_classification]))
        {
            $this->mapped_fields[$field_type][$field_classification] = array();
        }

        //false could be the key.
        if (!isset($this->mapped_fields[$field_type][$field_classification][$entity_type]))
        {
            $this->mapped_fields[$field_type][$field_classification][$entity_type] = array();
        }

        $this->mapped_fields[$field_type][$field_classification][$entity_type] = $this->_mapped_fields;
    }

    private function get_stored_mapping($field_type, $field_classification, $entity_type)
    {
        if (isset($this->mapped_fields[$field_type][$field_classification][$entity_type]))
        {
            //last mapped_fields
            $this->_mapped_fields = $this->mapped_fields[$field_type][$field_classification][$entity_type];
            return true;
        }

        return false;
    }

    //get the insert mapping for a field_type, field_classification, entity_type(all fields)
    public function map_field_type($field_type, $field_classification = false, $entity_type = false)
    {
        if ($this->get_stored_mapping($field_type, $field_classification, $entity_type))
        {
            
        }
        else
        {
            //this does make me think the nulls should be removed if the not null exists.
            $field_classification_sql = $field_classification ? " AND (f.field_classification = '{$field_classification}' OR f.field_classification IS NULL) " : " AND f.field_classification IS NULL ";

            $entity_type_sql = $entity_type ? " AND (f.entity_type = '{$entity_type}' OR f.entity_type IS NULL) " : " AND f.entity_type IS NULL ";

            $this->_mapped_fields = $this->db_connection->rows("SELECT f.special_handling, f.default_value, f.allow_update, f.cart_field,{$this->cart_mapping['fields']} 
										(SELECT GROUP_CONCAT(IFNULL(pos_field,'') ORDER BY field_order SEPARATOR '|' )
												FROM rdi_field_mapping_pos 
												WHERE field_mapping_id = f.field_mapping_id) AS pos_field,
										(SELECT GROUP_CONCAT(IFNULL(alternative_field,'')  ORDER BY field_order SEPARATOR '|')					
												FROM rdi_field_mapping_pos
												WHERE field_mapping_id = f.field_mapping_id) AS alternative_field 
										FROM rdi_field_mapping f
										{$this->cart_mapping['join']}
										WHERE f.field_type = '{$field_type}'{$field_classification_sql}{$entity_type_sql}
											ORDER BY f.field_mapping_id");

            //store this info
            $this->store_mapping($field_type, $field_classification, $entity_type);
        }

        return $this;
    }

    //returns a list of fields that can be used in a select statement.
    public function fields_list()
    {
        if (!empty($this->_mapped_fields))
        {
            $fields = array();
            foreach ($this->_mapped_fields as $field)
            {
                $pos_field = $this->mapping_to_field($field['pos_field'], $field['alternative_field']);
                $default_value = $this->default_value_to_sql($field['default_value']);

                if ($default_value)
                {
                    if (strlen(trim($pos_field)) > 0)
                    {
                        $field_sql = $this->_echo("IFNULL({$pos_field},{$default_value}) as {$field['cart_field']}");
                    }
                    else
                    {
                        $field_sql = "{$default_value} as {$field['cart_field']}";
                    }
                }
                else
                {
                    if (strlen(trim($pos_field)) > 0)
                    {
                        $field_sql = "{$pos_field} as {$field['cart_field']}";
                    }
                    else
                    {
                        $field_sql = "NULL as {$field['cart_field']}";
                    }
                }

                $fields[] = $field_sql;
            }

            return implode(",", $fields);
        }
        return false;
    }

    public function fields_list_export()
    {
        if (!empty($this->_mapped_fields))
        {
            $fields = array();
            foreach ($this->_mapped_fields as $field)
            {
                //$this->_print_r($field);

                $cart_field = $this->mapping_to_field($field['cart_field'], $field['alternative_field']);
                //$this->_print_r($cart_field);
                $default_value = $this->default_value_to_sql($field['default_value']);

                if (strlen($field['pos_field']) == 0)
                {
                    continue;
                }

                if ($default_value)
                {
                    if (strlen(trim($cart_field)) > 0)
                    {
                        $field_sql = "IFNULL({$cart_field},{$default_value}) as {$field['pos_field']}";
                    }
                    else
                    {
                        $field_sql = "{$default_value} as {$field['pos_field']}";
                    }
                }
                else
                {
                    if (strlen(trim($cart_field)) > 0)
                    {
                        $field_sql = "{$cart_field} as {$field['pos_field']}";
                    }
                    else
                    {
                        $field_sql = "NULL as {$field['pos_field']}";
                    }
                }

                $this->apply_sql_special_handling($field_sql, $field);

                $fields[] = $field_sql;
            }

            //@todo apply SQL special handling.

            return implode(",", $fields);
        }
        return false;
    }

    //4 possible variables
    //$m is mapped, $c cart_field, $p pos_field, $a alternative_field
    public function apply_sql_special_handling(&$_mapped, $mapping)
    {
        list($mapped, $as) = explode(' as ', $_mapped);

        if (strpos($mapping['special_handling'], "SQL[") !== false)//right  keeps the right n most characters
        {
            //break down the parameters
            preg_match("/SQL\[(.*?)\]/", $mapping['special_handling'], $matches);

            $success = false;
            $out = $matches[1];
            if (strpos($out, "\$m") !== false)
            {
                $out = str_replace("\$m", $mapped, $out);
                $success = true;
            }

            if (strpos($out, "\$p") !== false)
            {
                $out = str_replace("\$p", $mapping['pos_field'], $out);
                $success = true;
            }

            if (strpos($out, "\$c") !== false)
            {
                $out = str_replace("\$c", $mapping['cart_field'], $out);
                $success = true;
            }

            if (strpos($out, "\$a") !== false)
            {
                $out = str_replace("\$a", $mapping['alternative_field'], $out);
                $success = true;
            }

            if ($success)
            {
                $_mapped = "{$out} as {$as}";
            }
        }
    }

    public function mapping_to_field($pos_field, $alternative_field)
    {
        $out_field = "{$pos_field}";
        //if there is a pos_field and it has a pipe
        if (strlen($pos_field) > 0 && strstr($pos_field, "|"))
        {
            $_pos_field = explode("|", $pos_field);
            $_alternative_field = explode("|", $alternative_field);
            if (count($_pos_field) == count($_alternative_field))
            {
                $_out = array();

                foreach ($_pos_field as $key => $field)
                {
                    //check for null, if there is multi line mapping, we will shove in the blank case for a null.
                    $alt_field = strlen($_alternative_field[$key]) > 0 ? $_alternative_field[$key] : "''";
                    $_out[$key] = "IFNULL({$field},{$alt_field})";
                }

                $out_field = "CONCAT(" . implode(",", $_out) . ")";
            }
        }
        elseif (strlen($pos_field) > 0 && strlen($alternative_field) > 0)
        {
            $out_field = "IFNULL({$pos_field},{$alternative_field})";
        }

        return $out_field;
    }

    public function default_value_to_sql($value)
    {
        //if has a paran call as function
        if (strstr($value, "("))
        {
            
        }
        else if ($value == null)
        {
            return false;
        }
        else
        {
            $value = "'{$this->db_connection->clean($value)}'";
        }

        return $value;
    }

    /*
     * Perform the mappign between the fields

     * 
     * field_types
     * product
     * customer
     * category
     */

    public function map_fields($field_type, $field_list = array(), $field_classification = 'null', $entity_type = 'null')
    {
        $new_fields = array();

        foreach ($field_list as $field)
        {
            if (is_array($field))
            {
                $new_fields[] = $this->map_field($field_type, $field['field'], isset($field['entity_type']) ? $field['entity_type'] : '', isset($field['concat']) ? $field['concat'] : true, isset($field['field_classification_id']) ? $field['field_classification_id'] : '');
            }
            else
            {
                $new_fields[] = $this->map_field($field_type, $field);
            }
        }

        return $new_fields;
    }

    //return true if this field is specified in the field mapping
    public function is_mapped($field_type, $field, $entity_type = '', $field_classification = '')
    {
        $sql = "select pos_field from rdi_field_mapping
                inner join rdi_field_mapping_pos on rdi_field_mapping_pos.field_mapping_id = rdi_field_mapping.field_mapping_id
                where field_type = '{$field_type}' and cart_field = '{$field}'{$field_classification}{$entity_type}
                order by field_order";
    }

    /*
     * map a single field
     * @param field_type $field_type - the type of field this is, product, category, category_product, customer, order, order_product
     * @param field_classification_id $field_classification_id - the classification of this field, ie attribute set id, or similar, can be blank
     * @param field $field - field to map
     * @param entity_type $entity_type - the type of entity used in this product, simple, configurable, or similar, product_type etc

     */

    public function map_field($field_type, $field, $entity_type = '', $field_classification = '', $concat = true)
    {

        if (isset($this->mapped_fields[$field_type][$field][$entity_type][$field_classification]))
        {
            $fields = $this->mapped_fields[$field_type][$field][$entity_type][$field_classification];
        }
        else
        {
            //these get changed on the load..
            $in_field_type = $field_type;
            $in_field = $field;
            $in_entity_type = $entity_type;
            $in_field_classification = $field_classification;

            if ($field_classification != '')
                $field_classification = " and (field_classification = '{$field_classification}' or field_classification is null)";

            if ($entity_type != '' && !is_array($entity_type))
                $entity_type = " and (entity_type = '{$entity_type}' or entity_type is null)";

            $sql = "select pos_field from rdi_field_mapping
                    inner join rdi_field_mapping_pos on rdi_field_mapping_pos.field_mapping_id = rdi_field_mapping.field_mapping_id
                    where field_type = '{$field_type}' and cart_field = '{$field}'{$field_classification}{$entity_type}
                    order by field_order";

            //echo "<br><br><br>" . $sql . "<Br><br>";


            $fields = $this->db_connection->rows($sql);

            $this->mapped_fields[$in_field_type][$in_field][$in_entity_type][$in_field_classification] = $fields;
        }

        if (count($fields) > 1)
        {
            if ($concat)
            {
                $new_field = "CONCAT(";
            }
            foreach ($fields as $f)
            {
                $new_field .= $f['pos_field'] . ",";
            }

            //remove the trailing
            $new_field = substr($new_field, 0, -1);

            if ($concat)
            {
                $new_field .=") as '{$field}'";
            }
        }
        else
        {
            $new_field = $fields[0]['pos_field'];
        }


        return $new_field;
    }

    //find the cart field from the pos field
    public function map_reverse($pos_field, $entity_type = '', $field_classification = '')
    {
        if ($field_classification != '')
            $field_classification = " and (field_classification = '{$field_classification}' or field_classification is null)";

        if ($entity_type != '')
            $entity_type = " and (entity_type = '{$entity_type}' or entity_type is null)";

        $sql = "select distinct f.special_handling, f.default_value, f.allow_update, f.cart_field, p.pos_field              
                    from rdi_field_mapping_pos p
                      inner join rdi_field_mapping f on p.field_mapping_id = f.field_mapping_id
                    where p.pos_field = '{$pos_field}'
                        {$field_classification}{$entity_type} order by f.field_mapping_id";

        return $this->db_connection->row($sql);
    }

    //get a list of fields that match this mapping  
    public function get_field_list($field_type, $entity_type = '', $field_classification = '', $image_field = false)
    {
        global $cart;

        $fc = is_array($field_classification) ? $field_classification['product_class'] : $field_classification;

        $image_criteria = '';

        if ($field_classification != '')
            $field_classification = " and (field_classification = '" . (is_array($field_classification) ? $field_classification['product_class'] : $field_classification) . "' or field_classification is null)";

        if ($entity_type != '')
            $entity_type = !is_array($entity_type) ? " and (entity_type = '{$entity_type}' or entity_type is null)" : " and (entity_type = '{$entity_type['product_type']}' or entity_type is null)";

        if ($image_field)
        {
            $image_criteria = " AND (cart_field = 'product_image') ";
        }

        //return a list of fields and a join
        $cart_mapping = $cart->get_processor("rdi_cart_common")->get_mapping_field_list($fc, $field_type);

        $sql = "select f.special_handling, f.default_value, f.allow_update, f.cart_field,{$cart_mapping['fields']}, 
                    (select GROUP_CONCAT(pos_field order by field_order) 
                            from rdi_field_mapping_pos 
                            where field_mapping_id = f.field_mapping_id) as pos_field,
                    (SELECT GROUP_CONCAT(alternative_field order by field_order)					
                            FROM rdi_field_mapping_pos
                            WHERE field_mapping_id = f.field_mapping_id) AS alternative_field 
                    from rdi_field_mapping f
					{$cart_mapping['join']}
                    where f.field_type = '{$field_type}'{$field_classification}{$entity_type}{$image_criteria}
                        order by f.field_mapping_id";

        return $this->db_connection->rows($sql);
    }

    public function get_field_list_expanded($field_type, $entity_type = '', $field_classification = '')
    {
        if ($field_classification != '')
        {
            $field_classification = " and (field_classification = '" . (is_array($field_classification) ? $field_classification['product_class'] : $field_classification) . "' or field_classification is null)";
        }

        if ($entity_type != '')
        {
            $entity_type = !is_array($entity_type) ? " and (entity_type = '{$entity_type}' or entity_type is null)" : " and (entity_type = '{$entity_type['product_type']}' or entity_type is null)";
        }

        $sql = "select f.special_handling, f.default_value, f.allow_update, f.cart_field, pos_field, alternative_field, field_order 
                    from rdi_field_mapping f
                    inner join rdi_field_mapping_pos on rdi_field_mapping_pos.field_mapping_id  = f.field_mapping_id
                    where f.field_type = '{$field_type}'{$field_classification}{$entity_type}
                    order by field_order";

        return $this->db_connection->rows($sql);
    }

    /*
     * does a pretty common scrub of the parameter data, feeding into it the field list for this type, as well clean up the paramters
     */

    public function prep_query_parameters($field_list_type, $parameter_list)
    {
        //get the field list
        $field_list = $this->get_field_list($field_list_type);

        //make sure there was something mapped
        if (is_array($field_list))
        {
            $fields = '';

            //build out the list of fields to query, but only the ones that are mapped to something
            foreach ($field_list as $mapping)
            {
                if ($mapping['cart_field'] != '')
                {
                    //its possible not to have a pos field assigned
                    if ($mapping['pos_field'] == '')
                    {
                        $fields .= "'{$mapping['default_value']}' as '{$mapping['cart_field']}',";
                    }
                    else
                    {
                        if (strpos($mapping['pos_field'], ',') > 0)
                        {
                            $mapping['pos_field'] = "CONCAT({$mapping['pos_field']})";
                        }

                        if (strpos($mapping['alternative_field'], ',') > 0)
                        {
                            $mapping['alternative_field'] = "CONCAT({$mapping['alternative_field']})";
                        }

                        if ($mapping['alternative_field'] != null || $mapping['alternative_field'] != '')
                        {
                            $fields .= "ifnull({$mapping['pos_field']}, {$mapping['alternative_field']}) as '{$mapping['cart_field']}',";
                        }
                        else if ($mapping['default_value'] != null || $mapping['default_value'] != '')
                        {
                            $fields .= "ifnull({$mapping['pos_field']}, '{$mapping['default_value']}') as '{$mapping['cart_field']}',";
                        }
                        else
                        {
                            $fields .= $mapping['pos_field'] . " as '{$mapping['cart_field']}',";
                        }
                    }
                }
            }

            $fields = substr($fields, 0, -1);
        }

        $where = '';

        if (isset($parameter_list['fields']) && $parameter_list['fields'] != '')
            $parameter_list['fields'] = "{$fields}, {$parameter_list['fields']}";
        else
            $parameter_list['fields'] = $fields;

        if (isset($parameter_list['where']) && $parameter_list['where'] != '')
            $parameter_list['where'] = " and {$parameter_list['where']}";

        if (isset($parameter_list['group_by']) && $parameter_list['group_by'] != '')
            $parameter_list['group_by'] = " Group by {$parameter_list['group_by']} ";

        if (isset($parameter_list['order_by']) && $parameter_list['order_by'] != '')
            $parameter_list['order_by'] = " order by {$parameter_list['order_by']} ";

        return $parameter_list;
    }

    public function map_image_field($field_type, $field, $get = '', $entity_type = '', $field_classification = '', $concat = true)
    {
        if ($field_classification != '')
            $field_classification = " and (field_classification = '{$field_classification}' or field_classification is null)";

        if ($entity_type != '')
            $entity_type = " and (entity_type = '{$entity_type}' or entity_type is null)";

        $sql = "select pos_field from rdi_field_mapping
                inner join rdi_field_mapping_pos on rdi_field_mapping_pos.field_mapping_id = rdi_field_mapping.field_mapping_id
                where field_type = '{$field_type}' and cart_field = '{$field}'{$field_classification}{$entity_type}
                order by field_order";

        //echo "<br><br><br>" . $sql . "<Br><br>";
        $new_field = '';
        $fields = $this->db_connection->cells($sql, 'pos_field');
        if ($get == 'fields')
        {
            $count = 0;
            foreach ($fields as $field)
            {
                $new_field .= $field . " as 'image" . ($count == 0 ? "" : $count) . "',";
                $count++;
            }
            $new_field = substr($new_field, 0, -1);
        }
        else
        {
            $new_field = "(" . implode(" IS NOT NULL OR ", $fields) . " IS NOT NULL)";

            //we have to replace the first OR with an AND to look for the item record with the images on it.

            $new_field = str_replace("$fields[0] IS NOT NULL OR ", "$fields[0] IS NOT NULL AND ", $new_field);
        }

        return $new_field;
    }

    //this is going to return all the field_mapping and the pos_field.
    public function map_field_expanded($field_type, $field, $entity_type = '', $field_classification = '', $concat = true)
    {

        if (isset($this->mapped_fields_expanded[$field_type][$field][$entity_type][$field_classification]))
        {
            $fields = $this->mapped_fields_expanded[$field_type][$field][$entity_type][$field_classification];
        }
        else
        {
            //these get changed on the load..
            $in_field_type = $field_type;
            $in_field = $field;
            $in_entity_type = $entity_type;
            $in_field_classification = $field_classification;

            if ($field_classification != '')
                $field_classification = " and (field_classification = '{$field_classification}' or field_classification is null)";

            if ($entity_type != '' && !is_array($entity_type))
                $entity_type = " and (entity_type = '{$entity_type}' or entity_type is null)";

            $fields = $this->db_connection->rows("select rdi_field_mapping.*, pos_field,alternative_field  from rdi_field_mapping
                    inner join rdi_field_mapping_pos on rdi_field_mapping_pos.field_mapping_id = rdi_field_mapping.field_mapping_id
                    where field_type = '{$field_type}' and cart_field = '{$field}'{$field_classification}{$entity_type}
                    order by field_order");

            $this->mapped_fields[$in_field_type][$in_field][$in_entity_type][$in_field_classification] = $fields;
        }

        if (count($fields) > 1)
        {
            $new_field = array();

            foreach ($fields as $f)
            {
                if (isset($f['alternative_field']) && strlen($f['alternative_field']) > 0)
                {
                    $new_field[] = "IFNULL({$f['pos_field']},{$f['alternative_field']})";
                }
                else
                {
                    $new_field[] = $f['pos_field'];
                }
            }

            $fields[0]['pos_field'] = "CONCAT(" . implode(',', $new_field) . ")";
        }

        if ($fields[0]['default_value'] !== null)
        {
            $fields[0]['pos_field'] = "IFNULL({$fields[0]['pos_field']},'{$fields[0]['default_value']}')";
        }

        return $fields[0];
    }

    //returns the first pos_field.
    public function look_up_pos_field($cart_field, $entity_type, $field_type)
    {
        return $this->db_connection->cell("select mp.pos_field from rdi_field_mapping m
														join rdi_field_mapping_pos mp
														on mp.field_mapping_id = m.field_mapping_id
														AND mp.field_order = 0
														where cart_field = '{$cart_field}' and entity_type = '{$entity_type}' and field_type = '{$field_type}'", 'pos_field');
    }

}
