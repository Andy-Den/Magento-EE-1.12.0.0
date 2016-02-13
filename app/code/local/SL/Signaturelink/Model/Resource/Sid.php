<?php
class SL_Signaturelink_Model_Resource_Sid extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct() {
		$this->_init('signaturelink/sid', 'signature_id');
	}
}
