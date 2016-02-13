<?php

ini_set('memory_limit', '512M');

require 'app/Mage.php';

Mage::app('admin');
Mage::setIsDeveloperMode(true);

if (!Mage::app()->isInstalled()) {
    echo "Application is not installed yet, please complete install wizard first.";
    exit(1);
}

// Only for urls
// Don't remove this
$_SERVER['SCRIPT_FILENAME'] = 'index.php';

try
{
    // CLEAN CACHE
    Mage::app()->cleanCache();
    echo 'Cleared all caches' . "\n";
}
catch(Exception $e)
{
    
}
?>