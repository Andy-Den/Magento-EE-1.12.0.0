<?php
/*
	Use this if the attribute is int/select for a find_replace.

*/


include 'init.php';

global $cart;

$command = $cart->get_db()->cell("select special_handling from rdi_field_mapping where cart_field = 'jewelry_type'","special_handling");

$pos_field = $cart->get_db()->cell("select pos_field from rdi_field_mapping m
									JOIN rdi_field_mapping_pos p
									on p.field_mapping_id = m.field_mapping_id
									where cart_field = 'jewelry_type'","pos_field");
									
$pos_field = str_replace("item.","",$pos_field);
$pos_field = str_replace("style.","",$pos_field);

//var_dump($command);
//var_dump($pos_field);

if(strpos($command, "find_replace_int(") !== false)
{
	//break down the parameters
	preg_match("/find_replace_int\((.*?)\)/", $command, $matches);
	
	
	var_dump($matches);
	
	$find_replace_array = unserialize($matches[1]);
	var_dump($find_replace_array);
	
	
	
	foreach($find_replace_array as $key => $out_value)
	{
		foreach($out_value	as $subject_value)
		{
			$update_sql = "UPDATE rpro_in_styles 
							SET {$pos_field} = '{$key}'
							WHERE {$pos_field} = '{$subject_value}';";
			//echo "<br>find replace: ";
			//echo $update_sql;
			//echo "<br>";		
			$cart->get_db()->exec($update_sql);
			  
		}
	
	}
	
}

?>