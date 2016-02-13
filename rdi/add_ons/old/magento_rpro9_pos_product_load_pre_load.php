<?php

global $cart;


    $db = $cart->get_db();
	
	
	$db->exec("UPDATE rpro_in_items SET udf8 = 1 WHERE IFNULL(udf8,'0') != '0'");
	$db->exec("UPDATE rpro_in_items SET udf9 = 1 WHERE IFNULL(udf9,'0') != '0'");
	$db->exec("UPDATE rpro_in_items SET udf10 = 1 WHERE IFNULL(udf10,'0') != '0'");
	$db->exec("UPDATE rpro_in_items SET udf11 = 1 WHERE IFNULL(udf11,'0') != '0'");
	
	//set the product name
	$db->exec("UPDATE rpro_in_styles style
				 JOIN rpro_in_items item
				 ON item.style_sid = style.style_sid
				 SET style.product_name = CONCAT_WS(' ',style.vendor, CASE LEFT(dcs,1)
				WHEN  'W' THEN 'Womens'
				WHEN 'M' THEN  'Mens'
				WHEN 'W' THEN 'Unisex'
				WHEN  'K' THEN 'Kids'
				ELSE ''
				END,
				item.text2)");
	
	

?>