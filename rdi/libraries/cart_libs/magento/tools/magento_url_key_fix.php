<?php

/**
 * This is meant as a replacement to the unicify scripts and will run with looping php functions
 * and ignore the attempt to run with one sql script.
 * /rdi/libraries/cart_libs/magento/tools/magento_url_key_fix.php
 * if this all looks good on the initial run,
 * /rdi/libraries/cart_libs/magento/tools/magento_url_key_fix.php?run_sql=1
 */
chdir('../../../../');

$GLOBALS['verbose_queries'] = 1;

require "init.php";

global $cart, $converttable, $simple_url_key_format, $configurable_url_key_format, $verbose_queries;

$prefix = $cart->get_db()->get_db_prefix();

echo "Check to this code file to make sure the attributes that they have set to create configurables are the right ones below for color and size _attribute_id<br><br>";

$cart->_echo($simple_url_key_format,"h4");
$cart->_echo($configurable_url_key_format,"h4");

               
preg_match_all("/{(.*?)}/", $simple_url_key_format, $matches1);
$cart->_print_r($matches1);

preg_match_all("/{(.*?)}/", $configurable_url_key_format, $matches2);
$cart->_print_r($matches2);

$attributes = array_unique(array_merge($matches1[1],$matches2[1]));

$cart->_print_r($attributes);

$a = implode("','",$attributes);

$_attributes = $cart->get_db()->rows("SELECT ea.* FROM {$prefix}eav_attribute ea 
										JOIN {$prefix}eav_entity_type et
										on et.entity_type_id = ea.entity_type_id
										and et.entity_type_code = 'catalog_product'
										where ea.attribute_code in('{$a}')");


$cart->_print_r($_attributes);
										
$_sql = array();
$_field = array();
$_join = array();
$_where = array();
$_sql['field'] = '';
$_sql['join'] = '';
$_sql['where'] = '';


$sql_middle = '';

$_field[] = "'' as url_key, '' as url_path, name.store_id, p.type_id as `product_type`, p.entity_id";

foreach($_attributes as $_a)
{
	if($_a['backend_type'] == 'static')
	{
		$_field[] = "p.{$_a['attribute_code']} as `{$_a['attribute_code']}`";
		
		continue;
	}
	
	if($_a['backend_type'] == 'int')
	{
		$_field[] = "{$_a['attribute_code']}.value as `{$_a['attribute_code']}`";
		
		$_join[] = "LEFT JOIN (SELECT DISTINCT i.entity_id, v.value FROM {$prefix}catalog_product_entity_int i
							JOIN {$prefix}eav_attribute_option o
							  ON o.option_id = i.value
							  AND o.attribute_id = {$_a['attribute_id']}													
							JOIN {$prefix}eav_attribute_option_value v
							ON v.option_id = o.option_id
							AND v.store_id = 0
							WHERE i.attribute_id = {$_a['attribute_id']}
						) as `{$_a['attribute_code']}`
					ON {$_a['attribute_code']}.entity_id = p.entity_id";	
						
			
		continue;
	}
	
	$_field[] = "{$_a['attribute_code']}.value as `{$_a['attribute_code']}`";
		
	$_join[] = "LEFT JOIN {$prefix}catalog_product_entity_{$_a['backend_type']} as `{$_a['attribute_code']}`
				ON {$_a['attribute_code']}.entity_id = p.entity_id
				AND `{$_a['attribute_code']}`.attribute_id = {$_a['attribute_id']}";
	
	
}

$sql['field'] = implode(",",$_field);
$sql['join'] = implode(" \n ",$_join);

$sql = "SELECT {$sql['field']} FROM {$prefix}catalog_product_entity p 
		{$sql['join']}";



//this is from magento's url key helper.
$converttable = array(
        '&amp;' => 'and',   '@' => 'at',    '©' => 'c', '®' => 'r', 'À' => 'a',
        'Á' => 'a', 'Â' => 'a', 'Ä' => 'a', 'Å' => 'a', 'Æ' => 'ae','Ç' => 'c',
        'È' => 'e', 'É' => 'e', 'Ë' => 'e', 'Ì' => 'i', 'Í' => 'i', 'Î' => 'i',
        'Ï' => 'i', 'Ò' => 'o', 'Ó' => 'o', 'Ô' => 'o', 'Õ' => 'o', 'Ö' => 'o',
        'Ø' => 'o', 'Ù' => 'u', 'Ú' => 'u', 'Û' => 'u', 'Ü' => 'u', 'Ý' => 'y',
        'ß' => 'ss','à' => 'a', 'á' => 'a', 'â' => 'a', 'ä' => 'a', 'å' => 'a',
        'æ' => 'ae','ç' => 'c', 'è' => 'e', 'é' => 'e', 'ê' => 'e', 'ë' => 'e',
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
        'i' => 'i', '?' => 'ij','?' => 'ij','J' => 'j', 'j' => 'j', 'K' => 'k',
        'k' => 'k', '?' => 'k', 'L' => 'l', 'l' => 'l', 'L' => 'l', 'l' => 'l',
        'L' => 'l', 'l' => 'l', '?' => 'l', '?' => 'l', 'L' => 'l', 'l' => 'l',
        'N' => 'n', 'n' => 'n', 'N' => 'n', 'n' => 'n', 'N' => 'n', 'n' => 'n',
        '?' => 'n', '?' => 'n', '?' => 'n', 'O' => 'o', 'o' => 'o', 'O' => 'o',
        'o' => 'o', 'O' => 'o', 'o' => 'o', 'Œ' => 'oe','œ' => 'oe','R' => 'r',
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
        '?' => 'a', '?' => 'ae','?' => 'ae','?' => 'o', '?' => 'o', '?' => 'e',
        '?' => 'jo','?' => 'e', '?' => 'i', '?' => 'i', '?' => 'a', '?' => 'b',
        '?' => 'v', '?' => 'g', '?' => 'd', '?' => 'e', '?' => 'zh','?' => 'z',
        '?' => 'i', '?' => 'j', '?' => 'k', '?' => 'l', '?' => 'm', '?' => 'n',
        '?' => 'o', '?' => 'p', '?' => 'r', '?' => 's', '?' => 't', '?' => 'u',
        '?' => 'f', '?' => 'h', '?' => 'c', '?' => 'ch','?' => 'sh','?' => 'sch',
        '?' => '-', '?' => 'y', '?' => '-', '?' => 'je','?' => 'ju','?' => 'ja',
        '?' => 'a', '?' => 'b', '?' => 'v', '?' => 'g', '?' => 'd', '?' => 'e',
        '?' => 'zh','?' => 'z', '?' => 'i', '?' => 'j', '?' => 'k', '?' => 'l',
        '?' => 'm', '?' => 'n', '?' => 'o', '?' => 'p', '?' => 'r', '?' => 's',
        '?' => 't', '?' => 'u', '?' => 'f', '?' => 'h', '?' => 'c', '?' => 'ch',
        '?' => 'sh','?' => 'sch','?' => '-','?' => 'y', '?' => '-', '?' => 'je',
        '?' => 'ju','?' => 'ja','?' => 'jo','?' => 'e', '?' => 'i', '?' => 'i',
        '?' => 'g', '?' => 'g', '?' => 'a', '?' => 'b', '?' => 'g', '?' => 'd',
        '?' => 'h', '?' => 'v', '?' => 'z', '?' => 'h', '?' => 't', '?' => 'i',
        '?' => 'k', '?' => 'k', '?' => 'l', '?' => 'm', '?' => 'm', '?' => 'n',
        '?' => 'n', '?' => 's', '?' => 'e', '?' => 'p', '?' => 'p', '?' => 'C',
        '?' => 'c', '?' => 'q', '?' => 'r', '?' => 'w', '?' => 't', '™' => 'tm',
    );

/*
//get the attribute id for the url key
$url_key_attribute_id = $cart->get_db()->cell("select attribute_id from {$prefix}eav_attribute where attribute_code = 'url_key' and entity_type_id = {$product_entity_type_id}", "attribute_id");
$url_path_attribute_id = $cart->get_db()->cell("select attribute_id from {$prefix}eav_attribute where attribute_code = 'url_path' and entity_type_id = {$product_entity_type_id}", "attribute_id");
$name_attribute_id = $cart->get_db()->cell("select attribute_id from {$prefix}eav_attribute where attribute_code = 'name' and entity_type_id = {$product_entity_type_id}", "attribute_id");
$color_attribute_id = $cart->get_db()->cell("select attribute_id from {$prefix}eav_attribute where attribute_code = 'color' and entity_type_id = {$product_entity_type_id}", "attribute_id");
$size_attribute_id = $cart->get_db()->cell("select attribute_id from {$prefix}eav_attribute where attribute_code = 'size' and entity_type_id = {$product_entity_type_id}", "attribute_id");

$simple_url_key_format = $cart->get_db()->cell("select value from rdi_settings where setting = 'simple_url_key_format' ", "value");
$configurable_url_key_format = $cart->get_db()->cell("select value from rdi_settings where setting = 'configurable_url_key_format' ", "value");


$sql = "SELECT 
p.entity_id,
p.sku AS 'sku',
'' as 'url_key',
name.store_id as 'store_id',
name.value AS 'name',
color_value.value AS 'color',
size_value.value AS 'size',
p.type_id AS product_type
FROM {$prefix}catalog_product_entity p
JOIN {$prefix}catalog_product_entity_varchar `name`
  ON name.entity_id = p.entity_id
  AND name.attribute_id = {$name_attribute_id}
LEFT JOIN {$prefix}catalog_product_entity_int color
  ON p.entity_id = color.entity_id
  AND color.attribute_id = {$color_attribute_id}
JOIN {$prefix}eav_attribute_option_value color_value
ON color_value.option_id = color.value
LEFT JOIN {$prefix}catalog_product_entity_int size
  ON size.entity_id = p.entity_id
  AND size.attribute_id =  {$size_attribute_id}
JOIN {$prefix}eav_attribute_option_value size_value
ON size_value.option_id = size.value
UNION
SELECT 
p.entity_id,
p.sku AS 'sku',
name.store_id as 'store_id',
'' as 'url_key',
name.value AS 'name',
'' AS 'color',
'' AS 'size',
p.type_id AS product_type
FROM {$prefix}catalog_product_entity p
JOIN {$prefix}catalog_product_entity_varchar `name`
  ON name.entity_id = p.entity_id
  AND name.attribute_id = {$name_attribute_id}
  LEFT JOIN {$prefix}catalog_product_entity_int color
  ON p.entity_id = color.entity_id
  AND color.attribute_id = {$color_attribute_id}
LEFT JOIN {$prefix}catalog_product_entity_int size
  ON size.entity_id = p.entity_id
  AND size.attribute_id =  {$size_attribute_id}
WHERE size.entity_id IS NULL OR color.entity_id IS NULL";*/

$product_records = $cart->get_db()->rows($sql);
$sql_url = '';

unset($_attributes);


$product_entity_type_id = $cart->get_db()->cell("select entity_type_id from {$prefix}eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
$_attributes = $cart->get_db()->cells("SELECT ea.* FROM {$prefix}eav_attribute ea 
										JOIN {$prefix}eav_entity_type et
										on et.entity_type_id = ea.entity_type_id
										and et.entity_type_code = 'catalog_product'
										where ea.attribute_code in('url_key','url_path')",'attribute_id','attribute_code');

if(is_array($product_records))
{
            //the current style id we are working on
            //$previous_product = '';
            //$current_style_item_entity_ids = array();
                      
            foreach($product_records as $key => $product_record)
            {      
                $referenced_entities = array();
                
                //reformat the url_key for simples to match the format
//                if(isset($simple_url_key_format) && $simple_url_key_format != ''
//                        && $product_type['product_type'] == 'simple' && $product_record['related_parent_id'] == ''
//                        && array_key_exists("url_key", $product_record))      
                        
                if(isset($simple_url_key_format) && $simple_url_key_format != ''
                        && $product_record['product_type'] == 'simple' 
// PD 1/9/12  Not sure why we care about the related Parent                        
                        // && (array_key_exists('related_parent_id',$product_record) ? $product_record['related_parent_id'] == '' : false)
                       // && array_key_exists("url_key", $product_record)
                        )                                 
                {
                     $product_record = process_url_key_pattern($product_record, $simple_url_key_format);
                }
                
                if(isset($configurable_url_key_format) && $configurable_url_key_format != ''
                        && $product_record['product_type'] == 'configurable' 
                        //&& array_key_exists("url_key", $product_record)
                        )                                 
                {
                     $product_record = process_url_key_pattern($product_record, $configurable_url_key_format);
                }
                
                if($sql_url == "")
                {
                    $sql_url = "REPLACE INTO `{$prefix}catalog_product_entity_varchar` (entity_type_id,attribute_id,store_id,entity_id,VALUE) VALUES ";
                }
                
                if($product_record['url_key'] !== '')
                {
                    $store_ids = $cart->get_db()->rows("select distinct store_id from {$prefix}catalog_product_entity_varchar where attribute_id = {$_attributes['url_key']} and entity_id = {$product_record['entity_id']}");
					 
					
                    foreach($store_ids as $store_id)
                    {
                        $sql_url .= "\n( {$product_entity_type_id},{$_attributes['url_key']},{$store_id['store_id']},{$product_record['entity_id']},'{$product_record['url_key']}'),"; 
                        $sql_url .= "( {$product_entity_type_id},{$_attributes['url_path']},{$store_id['store_id']},{$product_record['entity_id']},'{$product_record['url_path']}'),"; 
                    }
                }
                
                $product_records[$key] = $product_record; 
            }

        }
        
        
    function process_url_key_pattern($product_record, $pattern)
    {          
		global $converttable;
         //replace the tokens with the field values                    
        preg_match_all("/{(.*?)}/", $pattern, $matches);

        if(is_array(($matches)))
        {
            $product_record["url_key"] = $pattern;

            for($i = 0; $i < sizeof($matches[0]); $i++)
            {
                if(array_key_exists($matches[1][$i], $product_record))
                {    
                    $product_field_replacing = str_replace(' ', '-', $product_record[$matches[1][$i]]);  
                    $product_field_replacing = preg_replace('#[^0-9a-z]+#i', '-', strtr($product_field_replacing, $converttable));
                    //$product_field_replacing = ereg_replace("[^A-Za-z0-9\-]", "", $product_field_replacing);
                    $product_record["url_key"] = str_replace($matches[0][$i], $product_field_replacing, $product_record["url_key"]);                                                                
                }
                else
                {
                    $product_record["url_key"] = str_replace($matches[0][$i], '', $product_record["url_key"]);                                                                
                }
            }

			$product_record["url_key"] = preg_replace('#[^0-9a-z]+#i', '-', strtr($product_record["url_key"], $converttable));
            $product_record["url_key"] = strtolower($product_record["url_key"]);
            $product_record["url_key"] = trim($product_record["url_key"], '-');
            
            //trim any excess spacers
            $product_record["url_key"] = str_replace('__', '_', $product_record["url_key"]);                     
            $product_record["url_key"] = str_replace('--', '-', $product_record["url_key"]); 
            $product_record["url_key"] = str_replace('--', '-', $product_record["url_key"]);  
            $product_record["url_key"] = str_replace("'", '', $product_record["url_key"]); 

            if(substr($product_record["url_key"], -strlen('_'))==='_')
            {
                $product_record["url_key"] = substr($product_record["url_key"],0,-1);
            }

            if(substr($product_record["url_key"], -strlen('-'))==='-')
            {
                $product_record["url_key"] = substr($product_record["url_key"],0,-1);
            }

            //drop to lower no spaces
            $product_record["url_key"] = strtolower($product_record["url_key"]);
            $product_record["url_key"] = str_replace(' ', '-', $product_record["url_key"]); 
            $product_record["url_key"] = trim($product_record["url_key"],"-"); 
            $product_record["url_path"] = $product_record["url_key"].".html"; 
        } 
        return $product_record;
    }

    $sql_url[strlen($sql_url)-1] = "";
    
	
    echo $sql_url;
    
    if(isset($run_sql) && $run_sql == 1)
    {
        $cart->get_db()->exec($sql_url);
    }
	
    /*echo "<pre>";
    print_r($product_records);
    echo "</pre>";*/
?>
