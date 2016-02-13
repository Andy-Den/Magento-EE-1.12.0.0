<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Handle the preping of sql statements for so they get mapped to perform the correct select statement
 * 
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\FieldMapping
 */

class rdi_field_mapping extends rdi_general
{ 
    protected $ignore_warn;
    private $mapped_fields;
    
    /**
     * Class Constructor
     *
     * @param rdi_db $db
     */
    public function rdi_field_mapping($db = '', $ignore_warnings)
    { 
         if ($db)
            $this->set_db($db);
         else             
         {
             //get the database object from the library object             
             $this->set_db($this->get_processor("rdi_db_lib")->get_db_obj());                          
         }        
         
         $ignore_warn = $ignore_warnings;
         $this->mapped_fields = array();
    }  
    
    public function validate_mapping()
    {   
        global $pos, $cart;
        
        $error_message = '';
        $warning_message = '';
        
        $issue = false;
        $pos_non_existant = array();
        $cart_non_existant = array();
        
        //find the fields that just dont exist
        $pos_non_existant = $pos->get_processor("rdi_pos_common")->validate_pos_field_mapping_existance();
                
        $cart_non_existant = $cart->get_processor("rdi_cart_common")->validate_cart_field_mapping_existance();
        
        //find that the mapping that must exist is there
        $cart_minimum_missing = $cart->get_processor("rdi_cart_common")->validate_cart_field_mapping_minimum_required();                     
             
        $pos_required = $pos->get_processor("rdi_pos_common")->validate_pos_field_mapping_required();
                
        if(!$this->ignore_warn)
        {
            $pos_warnings = $pos->get_processor("rdi_pos_common")->validate_pos_field_mapping_warnings();
            $cart_warnings = $cart->get_processor("rdi_cart_common")->validate_cart_field_mapping_warnings();
        }
        
        if(isset($pos_warnings) && is_array($pos_warnings) && count($pos_warnings) > 0)
        {
            $warning_message .= "This configuration is not advised please double check";
            foreach($pos_warnings as $pos_warning)
            {
                $warning_message .= $pos_warning . "<br>";
            }
        }
        
        if(isset($cart_warnings) && is_array($cart_warnings) && count($cart_warnings) > 0)
        {
            $warning_message .= "This configuration is not advised please double check";
            foreach($cart_warnings as $cart_warning)
            {
                $warning_message .= $cart_warning . "<br>";
            }
        }
        
        if(is_array($pos_non_existant) && count($pos_non_existant) > 0)
        {
            $error_message .= "These pos fields do not exist: ";

        }

        if(is_array($cart_minimum_missing['category']) && count($cart_minimum_missing['category']) > 0)
        {
            $error_message .= "<Br>Mapping for these cart category fields do not exist and are required: <Br>";
            foreach($cart_minimum_missing['category'] as $field)
            {
                $error_message .= $field . "<Br>";
            }
        }
        
        if(is_array($cart_minimum_missing['product']) && count($cart_minimum_missing['product']) > 0)
        {
            $error_message .= "<Br>Mapping for these cart category fields do not exist and are required: <Br>";
            foreach($cart_minimum_missing['product'] as $field)
            {
                $error_message .= $field . "<Br>";
            }
        }
        
        if(is_array($cart_non_existant['category']) && count($cart_non_existant['category']) > 0)
        {
            $error_message .= "<Br>These cart category fields do not exist: <Br>";
            foreach($cart_non_existant['category'] as $field)
            {
                $error_message .= $field['cart_field'] . "<Br>";
            }
        }

        if(is_array($cart_non_existant['product']) && count($cart_non_existant['product']) > 0)
        {
            $error_message .= "<Br>These cart product fields do not exist: <Br>";
            foreach($cart_non_existant['product'] as $field)
            {
                $error_message .= $field['cart_field'] . "<Br>";
            }
        }
        
        if($warning_message != '')
        {
             echo "<br><h1>Warnings with your Field Mapping (rdi_field_mapping)</h1>{$warning_message}
                  <br><span style='color:red'>This can continue but is recommended you check on these</span>";
        }
             
        if($error_message != '')     
        {
            echo "<br><h1>Error with your Field Mapping (rdi_field_mapping)</h1>{$error_message}
                  <br><span style='color:red'>Can not continue till this is fixed</span>";
            exit;
        }
    }
        
    /*
     * Perform the mappign between the fields
     
     * 
     * field_types
     * product
     * customer
     * category
     */
    public function map_fields($field_type, $field_list)
    {   
        $new_fields = array();
        
        foreach($field_list as $field)
        {
            if(is_array($field))
            {
                $new_fields[] = $this->map_field($field_type, $field['field'], isset($field['entity_type']) ? $field['entity_type'] : '', isset($field['concat']) ? $field['concat'] : true, isset($field['field_classification_id']) ? $field['field_classification_id'] : '');
            }
            else 
            {
                $new_fields[] = $this->map_field($field_type, $field, true);
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
     public function map_field($field_type, $field, $entity_type = '', $field_classification = '', $concat = false)
    {             
        if(isset($this->mapped_fields[$field_type][$field][$entity_type][$field_classification]))
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
            
            if($field_classification != '')
                $field_classification = " and (field_classification = '{$field_classification}' or field_classification is null)";

            if($entity_type != '' && !is_array($entity_type))
                $entity_type = " and (entity_type = '{$entity_type}' or entity_type is null)";

            $sql = "select pos_field, default_value from rdi_field_mapping
                    left join rdi_field_mapping_pos on rdi_field_mapping_pos.field_mapping_id = rdi_field_mapping.field_mapping_id
                    where field_type = '{$field_type}' and cart_field = '{$field}'{$field_classification}{$entity_type}
                    order by field_order";             

            $fields = $this->db_connection->rows($sql);
            
            $this->mapped_fields[$in_field_type][$in_field][$in_entity_type][$in_field_classification] = $fields;
        }
        
        if(count($fields) > 1)
        {
            if($concat)
            {
                $new_field = "CONCAT(";
            }
            foreach($fields as $f)
            {
                $new_field .= $f['pos_field'] . ",";
            }

            //remove the trailing
            $new_field = substr($new_field,0,-1);

            if($concat)
            {
                $new_field .=")";
            }
        }
        else
        {
            $new_field = $fields[0]['pos_field'];
        }
        
        if($fields[0]['default_value'] !== null)
        {
            if(strlen($new_field) > 0)
            {
                $new_field = "IFNULL(".$new_field.",'{$fields[0]['default_value']}')";
            }
            else
            {
                $new_field = "'{$fields[0]['default_value']}'";
            }
        }
        else
        {
            if(strlen($new_field) == 0)
            {
                $new_field = 'NULL';
            }
        }
        
        if($concat)
        {
            $new_field .= " as '{$field}'";
        }
        
        return $new_field;
              
    }
    
    //find the cart field from the pos field
    public function map_reverse($pos_field, $entity_type = '', $field_classification = '')
    {
        if($field_classification != '')
            $field_classification = " and (field_classification = '{$field_classification}' or field_classification is null)";
        
        if($entity_type != '')
            $entity_type = " and (entity_type = '{$entity_type}' or entity_type is null)";
        
        $sql = "select distinct f.special_handling, f.default_value, f.allow_update, f.cart_field, p.pos_field              
                    from rdi_field_mapping_pos p
                      inner join rdi_field_mapping f on p.field_mapping_id = f.field_mapping_id
                    where p.pos_field = '{$pos_field}'
                        {$field_classification}{$entity_type} order by f.field_mapping_id";
            
        return $this->db_connection->row($sql);
    }
    
    //get a list of fields that match this mapping  
    public function get_field_list($field_type, $entity_type = '', $field_classification = '',$image_field = false)
    {        
        global $cart;
                
        $fc = is_array($field_classification)?$field_classification['product_class']:$field_classification;
            
        $image_criteria = '';
        
        if($field_classification != '')
            $field_classification = " and (field_classification = '" . (is_array($field_classification)?$field_classification['product_class']:$field_classification) . "' or field_classification is null)";
        
        if($entity_type != '')
            $entity_type = !is_array($entity_type)?" and (entity_type = '{$entity_type}' or entity_type is null)":" and (entity_type = '{$entity_type['product_type']}' or entity_type is null)";
            
        if($image_field)
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
  
        return $this->db_connection->rows($sql,'cart_field');
    }
    
    public function get_field_list_expanded($field_type, $entity_type = '', $field_classification = '')
    {     
        if($field_classification != '')
            $field_classification = " and (field_classification = '" . (is_array($field_classification)?$field_classification['product_class']:$field_classification) . "' or field_classification is null)";
        
        if($entity_type != '')
            $entity_type = !is_array($entity_type)?" and (entity_type = '{$entity_type}' or entity_type is null)":" and (entity_type = '{$entity_type['product_type']}' or entity_type is null)";
        
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
        if(is_array($field_list))
        {   
            $fields = '';
            
            //build out the list of fields to query, but only the ones that are mapped to something
            foreach($field_list as $mapping)
            {
                if($mapping['cart_field'] != '')
                {
                    //its possible not to have a pos field assigned
                    if($mapping['pos_field'] == '')
                    {                        
                        $fields .= "'{$mapping['default_value']}' as '{$mapping['cart_field']}',";
                    }
                    else
                    {                    
                        if(strpos($mapping['pos_field'], ',') > 0)
                        {
                            $mapping['pos_field'] = "CONCAT({$mapping['pos_field']})";
                        }

                        if(strpos($mapping['alternative_field'], ',') > 0)
                        {
                            $mapping['alternative_field'] = "CONCAT({$mapping['alternative_field']})";
                        }

                        if($mapping['alternative_field'] != null || $mapping['alternative_field'] != '')
                        {
                            $fields .= "ifnull({$mapping['pos_field']}, {$mapping['alternative_field']}) as '{$mapping['cart_field']}',";                  
                        }
                        else if($mapping['default_value'] != null || $mapping['default_value'] != '')
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
            
            $fields = substr($fields,0,-1);
        }
            
        $where = '';

        if(isset($parameter_list['fields']) && $parameter_list['fields'] != '')
            $parameter_list['fields'] = "{$fields}, {$parameter_list['fields']}";
        else
            $parameter_list['fields'] = $fields;
            
        if(isset($parameter_list['where']) && $parameter_list['where'] != '')
            $parameter_list['where'] = " and {$parameter_list['where']}";

        if(isset($parameter_list['group_by']) && $parameter_list['group_by'] != '')
            $parameter_list['group_by'] = " Group by {$parameter_list['group_by']} ";

        if(isset($parameter_list['order_by']) && $parameter_list['order_by'] != '')
            $parameter_list['order_by'] = " order by {$parameter_list['order_by']} ";
            
        return $parameter_list;
    }
    
    public function map_image_field($field_type, $field, $get = '', $entity_type = '', $field_classification = '', $concat = true)
    {     
        if($field_classification != '')
            $field_classification = " and (field_classification = '{$field_classification}' or field_classification is null)";
        
        if($entity_type != '')
            $entity_type = " and (entity_type = '{$entity_type}' or entity_type is null)";
            
        $sql = "select pos_field from rdi_field_mapping
                inner join rdi_field_mapping_pos on rdi_field_mapping_pos.field_mapping_id = rdi_field_mapping.field_mapping_id
                where field_type = '{$field_type}' and cart_field = '{$field}'{$field_classification}{$entity_type}
                order by field_order";             
          
        //echo "<br><br><br>" . $sql . "<Br><br>";
        $new_field = '';        
        $fields = $this->db_connection->cells($sql,'pos_field');
		if($get == 'fields')
		{
			$count = 0;
			foreach($fields as $field)
			{
				$new_field .= $field . " as 'image" . ($count==0?"":$count) . "',";
				$count++;
			}
			$new_field = substr($new_field,0,-1);
        }
		else	
		{
			$new_field = "(" . implode(" IS NOT NULL OR ", $fields) . " IS NOT NULL)";
                        
                        //we have to replace the first OR with an AND to look for the item record with the images on it.
                        
                        $new_field = str_replace("$fields[0] IS NOT NULL OR ", "$fields[0] IS NOT NULL AND ",$new_field);
                        
		}
             
        return $new_field;       
    }
    
    
	public function set_option_label_mapping()
	{
		if(!$this->_option_mapping)
		{
			$this->_option_mapping = $this->db_connection->cells("SELECT DISTINCT 
				  REPLACE(REPLACE(m.cart_field,'option', ''),'_label', '') AS k,
				  mp.pos_field 
				FROM
				  rdi_field_mapping m 
				  JOIN rdi_field_mapping_pos mp 
					ON mp.field_mapping_id = m.field_mapping_id 
				WHERE m.field_type = 'product' 
				  AND m.entity_type = 'simple' 
				  AND m.cart_field REGEXP '^option._label$' 
				ORDER BY m.cart_field ","pos_field","k");
		}
	}
	
	public function get_option_mapping($id)
	{
		return  $this->db_connection->cell("SELECT DISTINCT mp.pos_field FROM rdi_field_mapping m
													JOIN rdi_field_mapping_pos mp
													ON mp.field_mapping_id = m.field_mapping_id
													WHERE m.field_type = 'product' 
													AND m.entity_type = 'simple'   
													AND m.cart_field = 'option{$id}' 
													order by m.cart_field","pos_field");
	}
	
	public function get_attributes_mapping()
	{
		global $db_lib;
	
		$style_table = $db_lib->get_table_name("in_styles");
		$item_table = $db_lib->get_table_name("in_items");
		$style_field = "{$db_lib->alias['in_styles']}.{$db_lib->get_style_sid()}";
		$item_style_field = "{$db_lib->alias['in_items']}.{$db_lib->get_style_sid()}";
		
		list($_pos_name,$style_table_alias) = explode("_in_",$style_table);
	
		$this->set_option_label_mapping();
		
		$_field = array();
		$_join = array();
					
		foreach($this->_option_mapping as $i => $field_map)
		{
			$_field[] = "opt{$i}.cart_field AS option{$i}";
			$_join[] = "LEFT JOIN (SELECT DISTINCT m.cart_field,mp.pos_field FROM rdi_field_mapping m
								JOIN rdi_field_mapping_pos mp
								ON mp.field_mapping_id = m.field_mapping_id
								WHERE m.field_type = 'attributes') opt{$i}
								ON opt{$i}.pos_field = {$field_map} ";
		}
		
		$fields = implode(",",$_field);
		$join = implode(" ",$_join);
		
		/*
		"SELECT DISTINCT p.item_no, opt1.cart_field AS option1,
							opt2.cart_field AS option2,
							opt3.cart_field AS option3 FROM cp_in_products p
							LEFT JOIN (SELECT DISTINCT m.cart_field,mp.pos_field FROM rdi_field_mapping m
								JOIN rdi_field_mapping_pos mp
								ON mp.field_mapping_id = m.field_mapping_id
								WHERE m.field_type = 'attributes') opt1
								ON opt1.pos_field = p.dim_tag_1
							LEFT JOIN (SELECT DISTINCT m.cart_field,mp.pos_field FROM rdi_field_mapping m
								JOIN rdi_field_mapping_pos mp
								ON mp.field_mapping_id = m.field_mapping_id
								WHERE m.field_type = 'attributes') opt2
								ON opt2.pos_field = p.dim_tag_2
							LEFT JOIN (SELECT DISTINCT m.cart_field,mp.pos_field FROM rdi_field_mapping m
								JOIN rdi_field_mapping_pos mp
								ON mp.field_mapping_id = m.field_mapping_id
								WHERE m.field_type = 'attributes') opt3
								ON opt3.pos_field = p.dim_tag_3";*/
								
		
		$sql = "SELECT DISTINCT {$style_field}, {$fields} FROM {$style_table} {$db_lib->alias['in_styles']} 
			JOIN {$item_table} {$db_lib->alias['in_items']}
			on {$item_style_field} = {$style_field}
		
		{$join}";
		
		$this->attribute_mapping = $this->db_connection->rows($sql,$db_lib->get_style_sid());
		
	}
	
	//multiple
	public function apply_attribute_mapping(&$product_records)
	{		
		//add setting use_attribute_mapping
		foreach($product_records as &$product_record)
		{
			$this->apply_attribute_mapping_to_record($product_record);
		}
			
	}
	
	//single
	public function apply_attribute_mapping_to_record(&$product_record, $add_attribute_code = false)
	{					
		if(isset($this->attribute_mapping[$product_record['style_id']]))
		{
			//loop through the mapping fields
			foreach($this->attribute_mapping[$product_record['style_id']] as $option => $attribute_code)
			{			
				//not the style node, not blank mapped and in the product record.
								
				if(($option !== 'style_id') 
					&& strlen($attribute_code) > 0 
					&& isset($product_record[$option]))
				{					
					//used in staging stats
					if($add_attribute_code)
						$product_record[$option.'_label'] = $attribute_code;
						
					$product_record[$attribute_code] = $product_record[$option];
					$product_record[$attribute_code.'_sort_order'] = $product_record[$option.'_sort_order'];
					
				}
				if(isset($product_record[$option]))
				{
					unset($product_record[$option],$product_record[$option.'_sort_order']);
				}
				
			}
		}
	}
	
	public function attribute_mapping_to_field_data($style_id)
	{
		global $helper_funcs;
		$_super_attribute = array();
			
		$row = 0;	
		
		foreach($this->attribute_mapping[$style_id] as $key => $attribute)
		{
			if($row == 0)
			{
				$row++;
				continue;
			}
			
			
			$super_attribute = array();
			$super_attribute['cart_field'] 	= $attribute;
			$super_attribute['position'] 	= $row - 1;
			$super_attribute['label'] 		= $helper_funcs->title_case($attribute);
			
			$_super_attribute[] = $super_attribute;
			
			unset($super_attribute);
			$row++;
		}
		
		return $_super_attribute;
	
	}
	
    
    
//    public function get_cart_pos($list_type = '')
//    {               
//        if($list_type == '')
//            return $this->cart_pos;
//        
//        if(isset($this->cart_pos[$list_type]))
//        {
//            return $this->cart_pos[$list_type];
//        }
//        
//        return false;
//    }
//    
//    public function get_pos_cart($list_type = '')
//    {     
//        if($list_type == '')
//            return $this->pos_cart;
//
//        if(isset($this->pos_cart[$list_type]))
//        {
//            return $this->pos_cart[$list_type];
//        }
//        
//        return false;
//    }
}
