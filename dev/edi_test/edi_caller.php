<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

define('MAGENTO', realpath(dirname(__FILE__)));
ini_set('memory_limit', '32M');
set_time_limit(0);
$mageAppPath = MAGENTO . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
require_once $mageAppPath;
Mage::app();

if (false) {
    try {
        //$location = '856 1109160410.txt';
        $location = '856 1109160491_cust.txt'; // This is to Cust
        $trackingResult = Mage::getModel('shoemartedi/import')->processSingleShipmentEdiDoc($location);
        
    //	$location = '846 FLO20130328120028.txt';
    //	$invfeedResult = Mage::getModel('shoemartedi/edi')->getProcessedInvFeedData($location);
    

    } catch (Exception $e) {
        echo $e->getMessage();
    }
}
if (false) {
    $model = new Harapartners_ShoemartEdi_Model_Outgoing();
    $reult = $model->uploadFilesToEndpoint();
}

if (false) {
    $model = new Harapartners_ShoemartEdi_Model_Import();
    $model->processEdiInventoryDocuments(false, dirname(__FILE__));
}

if (true) {
    $order = Mage::getModel('sales/order')->load(93885);
    foreach ($order->getAllItems() as $item) {
        $lineItems[$item->getId()] = $item->getQtyOrdered();
    }
    $model = new Harapartners_ShoemartEdi_Model_Export_Order();
    $model->processOrder($order, $lineItems);
}

$breakpoint = 2222;
