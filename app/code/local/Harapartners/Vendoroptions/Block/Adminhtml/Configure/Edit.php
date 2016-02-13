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
 * @package     Harapartners\Vendoroptions\controllers
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/Vendoroptions/Block/Adminhtml/Configure/Edit.php
class Harapartners_Vendoroptions_Block_Adminhtml_Configure_Edit extends Mage_Adminhtml_Block_Widget_Form_Container {	
	
	public function __construct(){
    	parent::__construct();

        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'vendoroptions';
        $this->_controller = 'adminhtml_configure';

        $this->_updateButton('save', 'label', Mage::helper('vendoroptions')->__('Save configure Rule'));
        $this->_updateButton('delete', 'label', Mage::helper('vendoroptions')->__('Delete configure Rule'));
    }

    public function getHeaderText() {
        if( Mage::registry('vendoroptions_configure_data') && Mage::registry('vendoroptions_configure_data')->getId() ) {
            return Mage::helper('vendoroptions')->__('Edit Rule');
        } else {
            return Mage::helper('vendoroptions')->__('Add Rule');
        }
    }
    
    protected function _prepareLayout() {
        $this->setChild('back_button',
            $this->getLayout()->createBlock('adminhtml/widget_button')
                ->setData(array(
                    'label'     => Mage::helper('vendoroptions')->__('Back'),
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