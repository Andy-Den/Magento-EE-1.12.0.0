<?php
/**
 * Adapted by Christopher Shennan 
 * http://www.chrisshennan.com
 * 
 * Date: 20/04/2011
 * 
 * Adaptered from original post by Srinigenie
 * Original Post - http://www.magentocommerce.com/boards/viewthread/9391/
 */


define('MAGENTO', realpath(dirname(__FILE__)));
ini_set('memory_limit', '32M');
set_time_limit (0);
$mageAppPath = MAGENTO . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app'. DIRECTORY_SEPARATOR . 'Mage.php';
require_once $mageAppPath;
Mage::app();

try {
  //$obj = new Mage_Eav_Model_Import(MAGENTO);
  include_once ('Import.php');
  $obj = new Mage_Eav_Model_Import(MAGENTO);

  foreach(glob(MAGENTO . DS . 'csv' . DS . '*.csv') as $fn) {
    $name = str_replace('.csv', '', basename($fn));
    
    $id = reset(explode('_', $name));
    
    $obj->saveOptionValues($id, $fn); //80 for color // 122 for size
  }

  } catch (Exception $e) {
    echo $e->getMessage();
}