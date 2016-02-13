<?php 

/* NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 */ 
 
class Harapartners_AuthorizeNetCIM_Block_Adminhtml_Widget_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {
	
	public function __construct(){
    	parent::__construct();

        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'authorizenetcim';
        $this->_controller = 'adminhtml_widget';

        $this->_updateButton('save', 'label', Mage::helper('authorizenetcim')->__('Save Credit Card'));
        $this->_updateButton('delete', 'label', Mage::helper('authorizenetcim')->__('Delete Credit Card'));
    }
    
	public function getHeaderText() {
		if(Mage::app()->getRequest()->getParam('id', false)){
			return Mage::helper('authorizenetcim')->__('Edit Credit Card');
		}
        return Mage::helper('authorizenetcim')->__('Add Credit Card');
    }
    
	protected function _prepareLayout() {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('authorizenetcim')->__('Back'),
                    'onclick'   => "setLocation('".$this->getUrl('*/*/index')."')",
                    'class'   => 'back'
                ))
        );
        
        return parent::_prepareLayout();
    }
    
	public function getBackButtonHtml() {
        return $this->getChildHtml('back_button');
    }

    public function getSaveUrl(){
        return $this->getUrl('*/*/save', array('_current'=>true));
    }
    
}