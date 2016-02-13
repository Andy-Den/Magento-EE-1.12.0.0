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

// app/code/local/Harapartners/Vendoroptions/Model/Vendoroptions/Configure.php
class Harapartners_Vendoroptions_Model_Vendoroptions_Configure extends Mage_Core_Model_Abstract {
	
	protected function _construct() {
		$this->_init ( 'vendoroptions/vendoroptions_configure' );
	}
	
	public function loadByCode($vendorCode){
		$this->addData($this->getResource()->loadByCode($vendorCode));
        return $this;
	}
	
}