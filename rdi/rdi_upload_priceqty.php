<?php

/**
 * Process Preferences Data
 * 
 * This is a V9 only script. It will pull in price and qty xml and load it immediately.
 * The call back version of the rdi_db:rows method and access to the mysqli db connection rdi_db->mysqli is required.
 * 
 * @package Core\Import\PriceQty
 */
include_once "init.php";

global $cart;

$priceqty = rdi_load::include_libs($cart->get_db(), "priceqty");
$priceqty->load();

?>