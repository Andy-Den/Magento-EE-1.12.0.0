<?php
class SL_Signaturelink_Model_Resource_Sid_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
	public function _construct()
	{
		$this->_init('signaturelink/sid');
	}
}
