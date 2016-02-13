<?php

ini_set('memory_limit', '512M');

require '../Mage.php';

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

    // CATALOG REWRITES
    try {
        Mage::getSingleton('catalog/url')->refreshRewrites();
        echo 'Catalog Rewrites were refreshed successfully' . "\n";
    }
    catch (Mage_Core_Exception $e) {
        echo $e->getMessage() . "\n";
    }
    catch (Exception $e) {
        echo 'Error while refreshed Catalog Rewrites. Please try again later' . "\n";
    }

    // IMAGE CACHE
    try {
        Mage::getModel('catalog/product_image')->clearCache();
        echo 'Image cache was cleared succesfuly' . "\n";
    }
    catch (Mage_Core_Exception $e) {
        echo $e->getMessage() . "\n";
    }
    catch (Exception $e) {
        echo 'Error while cleared Image cache. Please try again later' . "\n";
    }

    // LAYERED NAV
    try {
        $flag = Mage::getModel('catalogindex/catalog_index_flag')->loadSelf();
        if ($flag->getState() == Mage_CatalogIndex_Model_Catalog_Index_Flag::STATE_RUNNING) {
            $kill = Mage::getModel('catalogindex/catalog_index_kill_flag')->loadSelf();
            $kill->setFlagData($flag->getFlagData())->save();
        }

        $flag->setState(Mage_CatalogIndex_Model_Catalog_Index_Flag::STATE_QUEUED)->save();
        Mage::getSingleton('catalogindex/indexer')->plainReindex();
        echo 'Layered Navigation Indices were refreshed successfully' . "\n";
    }
    catch (Mage_Core_Exception $e) {
        echo $e->getMessage() . "\n";
    }
    catch (Exception $e) {
        echo 'Error while refreshed Layered Navigation Indices. Please try again later' . "\n";
    }

    // SEARCH INDEX
    try {
        Mage::getSingleton('catalogsearch/fulltext')->rebuildIndex();
        echo 'Search Index was rebuilded successfully' . "\n";
    }
    catch (Mage_Core_Exception $e) {
        echo $e->getMessage() . "\n";
    }
    catch (Exception $e) {
        echo 'Error while rebuilded Search Index. Please try again later' . "\n";
    }

    // STOCK STATUS
    try {
        Mage::getSingleton('cataloginventory/stock_status')->rebuild();
        echo 'CatalogInventory Stock Status was rebuilded successfully' . "\n";
    }
    catch (Mage_Core_Exception $e) {
        echo $e->getMessage() . "\n";
    }
    catch (Exception $e) {
        echo 'Error while rebuilded CatalogInventory Stock Status. Please try again later' . "\n";
    }

    // CLEAN CACHE
    Mage::app()->cleanCache();
    echo 'Cleared all caches' . "\n";    

    echo  "\n" . 'Cache clear complete!' . "\n";

    exit(0);
}
catch (Exception $e) {
    Mage::printException($e);
}

exit(1);
?>
