<?php
/**
 * Download Orders
 * @package Core\Export\Download\Orders
 */
include_once "init.php";

$benchmarker->set_start(basename(__FILE__), "load");

$pos->get_processor("rdi_download_orders")->download_orders();

$return = $pos->get_processor("rdi_download_returns");

if($return)
{
    $return->download_returns();
}

$benchmarker->set_end(basename(__FILE__), "load");
?>