<?php

// ========== Init setup ========== //
include '../HaraTestFunctions.php';
$ediPath = '/var/www/magento/shoemart/incoming/edi';

$testFunction = new HaraTestFunctions();
$testFunction->initMage();
$testFunction->setTestName('Moves all files from Server In Folder to Magento In Folder');
$testFunction->outputStartMsg();

$params = Mage::app()->getRequest()->getParams();
if (true) {
    $testFunction->outputControlMessage('Moves all files from Server In Folder to Magento In Folder.');
    $testFunction->outputWarningMessage('Deletes existing files after moving.');
}

$mageBaseDirectory = realpath(Mage::getBaseDir('base') . DS . Mage::getStoreConfig('shoemart_edi/sync_setting/base_dir') . DS);
$smEdiBase = realpath($ediPath);

// Set From => TO
$fromDir = realpath($smEdiBase . DS . 'in' . DS);
$toDir = $mageBaseDirectory . DS . 'in' . DS;

foreach (glob($fromDir . DS . '*.txt') as $fileLocation) {
    $endFileLocation = $toDir . DS . basename($fileLocation);
    $baseName = basename($fileLocation);
    $flocal = new Varien_Io_File();
    $flocal->rm($endFileLocation);
    if ($flocal->checkAndCreateFolder($toDir)) {
        if ($flocal->mv($fileLocation, $endFileLocation)) {
            echo "Moved: {$baseName}" . '<br>' . PHP_EOL;
        }
    }
}

$testFunction->outputEndMsg();
