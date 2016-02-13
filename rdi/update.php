<?php

include 'init.php';

global $db_lib, $cart;

$db = $cart->get_db();

//check that all tables exist defined in tables
$_tables = $db->rows("SHOW TABLES");
//$cart->_var_dump($_tables);
$cart->_var_dump(rdi_staging_db_lib::$tables);
foreach(rdi_staging_db_lib::$tables as $alias => $table)
{ 
	if(!in_array($table, $_tables))
	{
		echo "Table defined in Staging Library not in database. Use this statement to get the create for the table." . PHP_EOL;
		echo "SHOW CREATE TABLE {$table};" . PHP_EOL;
	}
}


?>