<?php
/**
 * Download Customers
 * @package Core\Export\Download\Customers
 */
include_once "init.php";

$benchmarker->set_start(basename(__FILE__), "load");

$pos->get_processor("rdi_download_customers")->download_customers();

$benchmarker->set_end(basename(__FILE__), "load");
?>