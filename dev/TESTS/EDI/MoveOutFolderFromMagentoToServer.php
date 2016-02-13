<?php

// ========== Init setup ========== //
include '../HaraTestFunctions.php';
$ediPath = '/var/www/magento/shoemart/incoming/edi';

$testFunction = new HaraTestFunctions();
$testFunction->initMage();
$testFunction->setTestName('Moves all files from Magento Out Folder to Server Out Folder');
$testFunction->outputStartMsg();

$params = Mage::app()->getRequest()->getParams();
if (true) {
    $testFunction->outputControlMessage('Moves all files from Magento Out Folder to Server Out Folder.');
    $testFunction->outputWarningMessage('Deletes existing files after moving.');
}

$mageBaseDirectory = realpath(Mage::getBaseDir('base') . DS . Mage::getStoreConfig('shoemart_edi/sync_setting/base_dir') . DS);
$smEdiBase = realpath($ediPath);

// Set From => TO
$fromDir = realpath($mageBaseDirectory . DS . 'out' . DS);
$toDir = $smEdiBase . DS . 'out' . DS;

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
