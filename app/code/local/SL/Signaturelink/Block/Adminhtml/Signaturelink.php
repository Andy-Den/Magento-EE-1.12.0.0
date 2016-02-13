<?php
class SL_Signaturelink_Block_Adminhtml_Signaturelink extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_signaturelink';
		$this->_blockGroup = 'signaturelink';
		$this->_headerText = Mage::helper('signaturelink')->__('SecureBuy History');
		parent::__construct();
	}
	
	protected function _prepareLayout()
	{
		$this->setChild('grid',
			$this->getLayout()->createBlock($this->_blockGroup.'/'.$this->_controller.'_grid',
			$this->_controller . '.grid')->setSaveParametersInSession(false) );

		return parent::_prepareLayout();
	}
}
