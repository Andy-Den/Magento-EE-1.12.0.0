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
 * @package     Harapartners\Webservice\Model
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/Vendoroptions/Model/Mysql4/Vendoroptions/Configure.php
class Harapartners_Vendoroptions_Model_Mysql4_Vendoroptions_Configure extends Mage_Core_Model_Resource_Db_Abstract {
	
	protected function _construct() {
		$this->_init ( 'vendoroptions/vendoroptions_configure', 'entity_id' );
	}
	
	public function loadByCode($vendorCode){
        $readAdapter = $this->_getReadAdapter();
        $select = $readAdapter->select()
                ->from($this->getMainTable())
                ->where('code=:code');
        $result = $readAdapter->fetchRow($select, array('code' => $vendorCode));
        if (!$result) {
           $result = array(); 
        }
        return $result;
    }
	
}