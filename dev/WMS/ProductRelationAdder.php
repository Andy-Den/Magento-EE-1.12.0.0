<?php

// ========== Init setup ========== //
error_reporting(- 1);
require_once ('../../app/Mage.php');
Mage::app();

$relator = new RelationAdder();
$relator->addRelations();

class RelationAdder
{
    protected $_fileName = 'relatedProducts.csv';
    protected $_reader = null;
    protected $_nextRows = array();
    protected $_overRunLine = null;

    public function addRelations()
    {
        $this->_initFile();
        while ($this->_getNextSkuRows()) {
            $this->_saveRelations();
        }
        
        if ($this->_overRunLine) {
            $this->_nextRows = $this->_overRunLine;
            $this->_saveRelations();
        }
    }

    protected function _saveRelations()
    {
        $rows = $this->_nextRows;
        $sku = $rows[0][0];
        $productId = $this->_productIdBySku($sku);
        if (! $productId) {
            echo 'Parent Does Not Exist: ' . $sku . PHP_EOL;
            return false;
        }
        
        foreach ($rows as $count => $data) {
            list ($sku, $relatedSku, $pos) = $data;
            $childId = $this->_productIdBySku($relatedSku);
            if ($childId) {
                $info[$childId] = array(
                    'position' => $pos
                );
            } else {
                echo 'Child  Does Not Exist: ' . $relatedSku . '. Parent: ' . $sku . PHP_EOL;
            }
        }
        
        if (count($info)) {
            $product = Mage::getModel('catalog/product')->setId($productId);
            $product->setRelatedLinkData($info);
            $product->save();
        }
    }

    protected function _productIdBySku($sku)
    {
        return Mage::getModel('catalog/product')->getIdBySku($sku);
    }

    protected function _getNextSkuRows()
    {
        if ($this->_overRunLine) {
            $rows[] = $this->_overRunLine;
            $sku = $this->_overRunLine[0];
        } else {
            $rows = array();
            $sku = '';
        }
        
        $this->_overRunLine = null;
        while ($row = $this->_reader->streamReadCsv()) {
            if ($sku == $row[0]) {
                $rows[] = $row;
                $sku = $row[0];
            } else {
                $this->_overRunLine = $row;
                break;
            }
        }
        
        $this->_nextRows = $rows;
        return $row ? true : false;
    }

    protected function _initFile()
    {
        $reader = new Varien_Io_File();
        $reader->cd(dirname(__FILE__));
        $reader->streamOpen($this->_fileName, 'r');
        $header = $reader->streamReadCsv();
        $this->_reader = $reader;
        $this->_getNextSkuRows(); // Need to Ready the first row
    }
}
