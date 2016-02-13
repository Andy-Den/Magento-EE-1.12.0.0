<?php
class SL_Signaturelink_Block_Adminhtml_Whitelist extends Mage_Adminhtml_Block_Widget_Grid_Container
{
	public function __construct()
	{
		$this->_controller = 'adminhtml_whitelist';
		$this->_blockGroup = 'signaturelink';
		$this->_headerText = Mage::helper('signaturelink')->__('SignatureLink Whitelist');

		parent::__construct();
	}

	protected function _prepareLayout()
	{
        $this->_addButton('addWhitelist', array(
            'label' => Mage::helper('signaturelink')->__('Add Whitelist Entry'),
            'class' => 'add add-whitelist'
        ), -100);

        $this->_removeButton('add');

		$this->setChild('grid',
			$this->getLayout()->createBlock($this->_blockGroup.'/'.$this->_controller.'_grid',
			$this->_controller . '.grid')->setSaveParametersInSession(true) );

		return parent::_prepareLayout();
	}
}
