<?php
class SL_Signaturelink_Model_Sid extends Mage_Core_Model_Abstract
{
	public function _construct() {
		parent::_construct();
		$this->_init('signaturelink/sid');
	}
}
