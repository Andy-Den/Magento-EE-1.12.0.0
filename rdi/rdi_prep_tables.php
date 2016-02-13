<?php
/**
 * Cleans out the staging table. Used before POST.
 * @package Core\Import
 */
include_once "init.php";

$db_lib->clean_in_staging_tables();

print("success");

?>
