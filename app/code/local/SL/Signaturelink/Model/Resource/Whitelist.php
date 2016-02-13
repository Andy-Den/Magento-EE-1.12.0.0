<?php
class SL_Signaturelink_Model_Resource_Whitelist extends Mage_Core_Model_Mysql4_Abstract
{
	public function _construct() {
		$this->_init('signaturelink/whitelist', 'entity_id');
	}
}
