<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of magento_rdi_cart_attribute_option_value
 *
 * @author PMBliss
 */
/*

  include 'init.php';

  global $cart, $field_mapping;

  $time_start = microtime(true);

  $stock = new magento_rdi_cart_attribute_option_value($cart->get_db());

  //this could be done in mapping
  // $mapping[$cart_field] = $pos_field
  $mapping = array();
  $mapping["name"] = " UPPER(style.fldstylename) ";
  $mapping["description"] = " lower(style.fldstylelongdesc) ";
  $mapping["short_description"] = " lower(style.fldstylelongdesc) ";
  $mapping["price"] = " item.item_price1 + 1 ";
  $mapping["manufacturer"] = " style.flddesc1";

  $stock->set_mapping($mapping)->start();
  $stock->set_current_attribute('name')->insert_table();
  $stock->set_current_attribute('description')->set_special_handling_current('titlecase')->insert_table();
  $stock->set_current_attribute('short_description')->insert_table();
  $stock->set_current_attribute('price')->insert_table();
  $stock->set_current_attribute('manufacturer')->insert_table();

  $time_end = microtime(true);
  echo $time = $time_end - $time_start;
 */



//->set_special_handling_current('titlecase')
class magento_rdi_cart_attribute_option_value extends rdi_general
{
    /*
      PMB 20130710
      WARNING: this requires an update to the cells function on the rdi_db class to make it an associative array.
     */

    private $db_connection;
    private $fields;
    private $replace_fields;
    private $select_fields;
    private $pos_update_parameters;
    private $attribute_ids;
    private $mapping;
    private $mapping_current;
    private $related_id_join;
    private $table;
    private $current_attribute_id;
    private $current_attribute_type;
    private $current_attribute;
    private $special_handling_current;
    private $collation;
    private $entity_type_code;

    public function __construct($db = '')
    {
        $this->db_connection = $db;
        $this->table = '';
        $this->collation = array('varchar' => 'collate utf8_bin',
            'int' => 'collate utf8_bin',
            'text' => 'collate utf8_bin',
            'decimal' => '');
        $this->entity_type_code = '';
    }

    public function start()
    {
        $this->set_attribute_ids(array_keys($this->mapping));

        if($this->entity_type_code == '')
        {
            die("Please set entity_type_code to continue.");
        }
        
        foreach ($this->mapping AS $value => $map)
        {
            $this->set_current_attribute($value)->set_special_handling_current('')->insert_table();
        }

        return $this;
    }

    public function set_table()
    {

        $row = $this->db_connection->row("SELECT 
						CONCAT(et.entity_type_code,'_entity_',backend_type) AS table_name,
						backend_type
					FROM
					  eav_attribute ea 
					  JOIN eav_entity_type et 
						ON et.entity_type_id = ea.entity_type_id 
						AND et.entity_type_code = '{$this->entity_type_code}' 
					WHERE ea.attribute_code IN ('{$this->current_attribute}')");

        $this->table = $row['table_name'];
        $this->current_attribute_type = $row['backend_type'];

        return $this;
    }

    public function set_current_attribute($attribute)
    {
        $this->current_attribute = $attribute;

        $this->set_current_attribute_id()->set_mapping_current()->set_table()->set_related_id_join();
        ;

        return $this;
    }

    public function set_current_attribute_id()
    {
        $this->current_attribute_id = $this->attribute_ids[$this->current_attribute];

        return $this;
    }

    public function set_mapping_current()
    {
        //probably going to be value
        $this->mapping_current['value'] = $this->mapping[$this->current_attribute];

        return $this;
    }

    public function set_special_handling_current($special_handling)
    {

        $this->special_handling_current = $special_handling;

        return $this;
    }

    //will need to call this one before we start inserting tables
    //set all attribute ids to be used.
    //->set_attribute_ids(array('name'))
    //->set all currents

    public function insert_table()
    {
        if ($this->table == '')
        {
            echo "Please set the table";
            return $this;
        }
        echo "<h1>{$this->current_attribute_type}</h1>";
        if ($this->current_attribute_type == 'int')
        {
            $this->add_attribute_option();
        }


        //get all the values we are going to need
        $this->set_fields()->set_mapping_current()->set_string_replace_fields()->set_string_select_fields()->set_pos_update_parameters();

        $sql_head = " replace into {$this->table} ({$this->replace_fields}) ";

        $sql = " SELECT DISTINCT {$this->select_fields} FROM ";

        $sql .= " {$this->pos_update_parameters['join']} ";

        $sql .= " {$this->related_id_join} ";

        //if we are going an int will have to check this more.			
        $sql .= " LEFT JOIN {$this->table} `table`
						ON table.entity_id = related_id.entity_id
						AND table.attribute_id = {$this->current_attribute_id}
						";

        $sql .= " {$this->pos_update_parameters['where']} ";

        $sql .= $this->pos_update_parameters['where'] !== '' ? ' AND ' : '';

        $sql .= " table.value {$this->collation[$this->current_attribute_type]} != {$this->mapping_current['value']} {$this->collation[$this->current_attribute_type]} ";

        if ($this->special_handling_current == '')
        {
            echo $sql_head . $sql;
            echo "<br>";
            echo "<br>";
            // $updated = $this->db_connection->insert($sql_head . $sql);
        }
        else
        {
            if (is_array($_product_record) && count($_product_record) > 0)
            {
                $_product_record = $this->db_connection->rows($sql);
                $product_record_sql = $this->special_handling($_product_record);
                echo $sql_head . $product_record_sql;
                echo "<br>";
                echo "<br>";
            }
            else
            {
                echo "<br> nothing to update here <br>";
            }
        }

        $this->table = '';
        $this->special_handling_current = '';

        return $this;
    }

    //all the fields contained on that table;
    public function set_fields()
    {
        $this->fields = $this->db_connection->cells("SHOW COLUMNS FROM {$this->table} where Field not in ('value_id')", 'Field');

        return $this;
    }

    //all the fields fields in a string;
    public function set_string_replace_fields()
    {
        $this->replace_fields = implode(",", $this->fields);

        return $this;
    }

    public function set_string_select_fields()
    {
        $this->fields;
        $_fields_new = array();
        //process fields to select
        //need to check the name of the table here.


        foreach ($this->fields as $field)
        {
            if (array_key_exists($field, $this->mapping_current))
            {
                $_fields_new[] = "{$this->mapping_current[$field]} AS '{$field}'";
            }
            elseif ($field == 'attribute_id')
            {
                $_fields_new[] = "IFNULL(table.{$field},{$this->current_attribute_id}) as attribute_id";
            }
            else
            {
                $_fields_new[] = "IFNULL(table.{$field},related_id.{$field}) AS '{$field}'";
            }
        }

        $this->select_fields = implode(",", $_fields_new);

        return $this;
    }

    public function set_mapping($array)
    {
        $this->mapping = $array;

        return $this;
    }

    //this function should be in the POS and then update the private value here.
    public function set_pos_update_parameters()
    {
        global $pos;
        
        $this->pos_update_parameters['join'] = "rpro_in_styles style
                                            JOIN rpro_in_styles item
                                            ON item.fldstylesid = style.fldstylesid
                                            AND item.record_type = 'item'";

        $this->pos_update_parameters['where'] = "WHERE style.record_type = 'style'";

        return $this;
    }

    public function set_related_id_join()
    {
        $this->related_id_join = " JOIN catalog_product_entity_varchar related_id
											ON related_id.value = item.item_flditemsid
											AND related_id.attribute_id = {$this->attribute_ids['related_id']}
										JOIN catalog_product_entity_varchar related_parent_id
											ON related_parent_id.value = style.fldstylesid 
											AND related_parent_id.attribute_id = {$this->attribute_ids['related_parent_id']} ";
        return $this;
    }

    public function set_attribute_ids($_attribute)
    {
        $attributes = count($_attribute) > 0 ? "'" . implode("','", $_attribute) . "'" : "''";

        //this needs to be sorted a little better 

        $this->attribute_ids = $this->db_connection->cells("select attribute_code,attribute_id from eav_attribute ea
                                                join eav_entity_type et
                                                on et.entity_type_id = ea.entity_type_id
                                                and et.entity_type_code = 'catalog_product'
												where ea.attribute_code in('related_id','related_parent_id')
													OR 
													(ea.attribute_code IN({$attributes}))
                                                ", "attribute_id", "attribute_code");

        return $this;
    }

    public function apply_titlecase($value)
    {
        return ucwords(strtolower($value));
    }

    public function special_handling($_product_record)
    {
        $product_records = array();

        $_key_value = array_keys($this->mapping_current);

        foreach ($_product_record as $key => $product_record)
        {
            //make this cases later
            if ($this->special_handling_current == 'titlecase')
            {
                $product_record[$_key_value[0]] = $this->apply_titlecase($product_record[$_key_value[0]]);
            }
            $product_records[] = "('" . implode("','", $product_record) . "')";
        }

        $product_record_sql = implode(",", $product_records);

        return $product_record_sql;
    }

    public function add_attribute_option()
    {
        $option_id = $this->db_connection->cell("SELECT MAX(option_id) AS option_id FROM eav_attribute_option", 'option_id');

        $sql_option = "REPLACE INTO eav_attribute_option(attribute_id, option_id, sort_order)
										SELECT DISTINCT  IFNULL(eao.`attribute_id`,{$this->current_attribute_id}) AS attribute_id, IFNULL(eaov.option_id,@rownum:=@rownum+1) option_id, eao.`sort_order` FROM (SELECT @rownum:={$option_id}) r, 
										{$this->pos_update_parameters['join']}
										LEFT JOIN eav_attribute_option_value eaov
										ON eaov.`value` = {$this->mapping[$this->current_attribute]}
										LEFT JOIN `eav_attribute_option` eao
										ON eao.`option_id` = eaov.`option_id`
										AND eao.`attribute_id` = {$this->current_attribute_id}
										WHERE style.record_type = 'style'
										AND
										IFNULL({$this->mapping[$this->current_attribute]},'') {$this->collation['int']} != IFNULL(eaov.value,'')  {$this->collation['int']}
										{$this->pos_update_parameters['where']}
										ORDER BY VALUE";

        $sql_option_value = "REPLACE INTO eav_attribute_option_value (attribute_id, option_id, store_id, value)
										SELECT DISTINCT  IFNULL(eao.`attribute_id`,{$this->current_attribute_id}) AS attribute_id, IFNULL(eaov.option_id,@rownum:=@rownum+1) option_id,IFNULL(eaov.`store_id`,0) AS store_id,{$this->mapping[$this->current_attribute]} AS VALUE FROM (SELECT @rownum:={$option_id}) r, 
										{$this->pos_update_parameters['join']}
										LEFT JOIN eav_attribute_option_value eaov
										ON eaov.`value` = {$this->mapping[$this->current_attribute]}
										LEFT JOIN `eav_attribute_option` eao
										ON eao.`option_id` = eaov.`option_id`
										AND eao.`attribute_id` = {$this->current_attribute_id}
										WHERE style.record_type = 'style'
										AND
										IFNULL({$this->mapping[$this->current_attribute]},'') {$this->collation['int']} != IFNULL(eaov.value,'')  {$this->collation['int']}
										{$this->pos_update_parameters['where']}
										ORDER BY VALUE;";

        echo $sql_option . "<br><br>";

        echo $sql_option_value . "<br><br>";

        //$this->db_connection->exec($sql_option);
        //$this->db_connection->exec($sql_option_value);		


        return $this;
    }

    public function set_entity_type_code($entity_type_code)
    {
        $this->entity_type_code = $entity_type_code;
        return $this;
    }

    /*
      //set the option values
      //run this one second



      //run this one
      SELECT DISTINCT  IFNULL(eao.`attribute_id`,142) AS attribute_id, IFNULL(eaov.option_id,@rownum:=@rownum+1) option_id, eao.`sort_order` FROM (SELECT @rownum:=30) r, rpro_in_styles style
      JOIN rpro_in_styles item
      ON item.fldstylesid = style.fldstylesid
      AND item.record_type = 'item'
      LEFT JOIN eav_attribute_option_value eaov
      ON eaov.`value` = style.`fldinvnaux0`
      LEFT JOIN `eav_attribute_option` eao
      ON eao.`option_id` = eaov.`option_id`
      AND eao.`attribute_id` = 142
      WHERE style.record_type = 'style'
      AND
      IFNULL(style.`fldinvnaux0`,'') COLLATE utf8_bin != IFNULL(eaov.value,'')  COLLATE utf8_bin
      ORDER BY VALUE

      //then go back to the int table to match ids

     */
}
?>
    
