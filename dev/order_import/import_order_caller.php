<?php

// ========== Init setup ========== //
error_reporting ( 0 );
require_once ('../../app/Mage.php');
Mage::app ();

require_once ('./Import_order.php');

$importModel = new Harapartners_Shoemart_Model_Import_Orders ( );

$data = array ('website_id' => 1, 'store_id' => 1, 'customer_group' => 4 );
$importModel->importProcess ( 'ImportOrderComplete', './Orders_asc.csv', './Order_Items.csv', $data );
