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
 * @package     Harapartners\Vendoroptions\Block
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/Vendoroptions/Block/Adminhtml/Configure/Index/Grid.php
class Harapartners_Vendoroptions_Block_Adminhtml_Configure_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {

    public function __construct(){
        parent::__construct();
        $this->setId('vendoroptionsConfigureGrid');
    }

    protected function _prepareCollection(){
        $model = Mage::getModel('vendoroptions/vendoroptions_configure');
        $collection = $model->getCollection();
		$this->setCollection($collection);
        parent::_prepareCollection();
        return $this;
    }

    protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }

    protected function _prepareColumns(){ 
    	 $this->addColumn('entity_id', array(
            'header'        => Mage::helper('vendoroptions')->__('Entity ID'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'entity_id'
        ));
        
        $this->addColumn('code', array(
            'header'        => Mage::helper('vendoroptions')->__('Vendor Code'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'code'
        ));
        
        $this->addColumn('name', array(
            'header'        => Mage::helper('vendoroptions')->__('Vendor Name'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'name'
        ));
        
        $this->addColumn('account_number', array(
            'header'        => Mage::helper('vendoroptions')->__('Account Number'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'account_number'
        ));
              
        $this->addColumn('phone', array(
            'header'        => Mage::helper('vendoroptions')->__('Phone Number'),
            'align'         => 'right',
            'width'         => '50px',
            'index'         => 'phone'
        ));

        return parent::_prepareColumns();
    }

    public function getRowUrl($row){
        return $this->getUrl('*/*/edit', array(
	            'store'=>$this->getRequest()->getParam('store'),
	            'id'=>$row->getId()
        ));
    }
    
}