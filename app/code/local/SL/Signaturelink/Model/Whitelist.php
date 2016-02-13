<?php
/**
 * @method string getAdminResponsible()
 * @method setAdminResponsible()
 * @method string getWhitelistEmail()
 * @method setWhitelistEmail()
 * @method boolean getActive()
 * @method setActive()
 * @method getCreatedAt()
 * @method setCreatedAt()
 */
class SL_Signaturelink_Model_Whitelist extends Mage_Core_Model_Abstract
{
	public function _construct() {
		parent::_construct();
		$this->_init('signaturelink/whitelist');
	}
}
