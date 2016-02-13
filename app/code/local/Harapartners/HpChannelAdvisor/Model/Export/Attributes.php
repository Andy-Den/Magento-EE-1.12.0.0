<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 * @package     Harapartners_HpChannelAdvisor_Model_Export
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/HpChannelAdvisor/Model/Export/Attributes.php
class Harapartners_HpChannelAdvisor_Model_Export_Attributes extends Mage_Core_Model_Abstract
{
    const PATH_SUFFIX_MAP_DIR = 'ca_maps';
    
    protected $_caMaps = array();

    /**
     * This uses {var}/ca_maps/{attributeSetName}.csv
     *
     * @param unknown_type $product
     * @return unknown
     */
    public function getCaAttributeSetFromProduct($product)
    {
        $attributeSetName = Mage::getModel('eav/entity_attribute_set')->load($product->getAttributeSetId())->getAttributeSetName();
        $map = $this->getCaMap($attributeSetName);
        return $map;
    }

    protected function getCaMap($attributeSetName)
    {
        if (! isset($this->_caMaps[$attributeSetName])) {
            $this->_caMaps[$attributeSetName] = $this->_initMap($attributeSetName);
        }
        
        return $this->_caMaps[$attributeSetName];
    }

    protected function _initMap($attributeSetName)
    {
        $pathDir = Mage::getBaseDir('var') . DS . self::PATH_SUFFIX_MAP_DIR;
        $filename = $attributeSetName . '.csv';
        
        $varienIo = new Varien_Io_File();
        $varienIo->setAllowCreateFolders(false);
        
        try {
            // Base Save
            $varienIo->open(array(
                'path' => $pathDir
            ));
            
            // Open In Append Mode
            $varienIo->streamOpen($filename, 'r');
            
            // Read All Lines
            $lines = array();
            while($line = $varienIo->streamReadCsv()){
            	// Normilize it
            	list ($oldName, $newName) = $line;
            	$lines[$oldName] = $newName;
            }
            
            return $lines;
        } catch (Exception $e) {
            Mage::logException($e);
            return false;
        }
    }
}
