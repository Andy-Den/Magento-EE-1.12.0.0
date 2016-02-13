<?php

// ========== Init setup ========== //
include '../HaraTestFunctions.php';
$testFunction = new HaraTestFunctions();
$testFunction->initMage();
$testFunction->setTestName('EDI Testing: Sync Tracking Information To Magento');
$testFunction->outputStartMsg();

$params = Mage::app()->getRequest()->getParams();
if (true) {
    $testFunction->outputControlMessage('Defualt Full Process. Set location=MageRootRelativePath if you want a different directory than the config');
    $testFunction->outputWarningMessage('File Needs to match pattern');
    $testFunction->outputWarningMessage('File Will be moved to MAGEROOT/*configDir*/edi/in/complete/*');
    $testFunction->outputWarningMessage('Mage Root is the folder that has /app and /var');
    $testFunction->outputWarningMessage('If run from browser may timeout. It is ment to be run from the the Command Line via Cron. Use small files from Browser Run');
}

$isConfigDir = true;
$relativeDir = Mage::app()->getRequest()->getParam('location');
if ($relativeDir) {
    $isConfigDir = false;
}

// if is config is false then it uses relative Dir. To mage Root
$importModel = new Harapartners_ShoemartEdi_Model_Import();
$importModel->processEdiShipmentDocuments($isConfigDir, $relativeDir);

echo $importModel->getResultMsg();

$testFunction->outputEndMsg();