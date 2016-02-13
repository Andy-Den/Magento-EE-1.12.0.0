<?php

/**
 *
 * /rdi/libraries/cart_libs/magento/tools/count_tabless.php
 */

chdir('../../../../');
include 'init.php';

global $cart, $pos_type, $db_lib, $action;

$db1 = $cart->get_db();

$out = array();


switch ($action)
{
	case "style":
		$out['Styles'] = $db_lib->get_product_count();
	break;
	case "category":
		$out['Categories'] = $db_lib->get_category_count();
	break;
}

foreach($out as $name => $o)
{
	echo "<li><h3>{$name}</h3><span id='{$name}'>{$o}</span></li>";
}


?>