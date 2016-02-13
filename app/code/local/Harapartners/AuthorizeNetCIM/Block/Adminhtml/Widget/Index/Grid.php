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
 
class Harapartners_AuthorizeNetCIM_Block_Adminhtml_Widget_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	
	public function __construct(){
        parent::__construct();
        $this->setId('CreditCardsGrid');
    }
    
	protected function _prepareCollection(){
        $cardCollection = Mage::getModel('authorizenetcim/profilemanager')->getCollection();
        $cardCollection->getSelect()->join(
        		array('ce' => 'customer_entity'),
        		'ce.entity_id = main_table.customer_id',
        		array('customer_email' => 'ce.email')
        );
		$this->setCollection($cardCollection);
        parent::_prepareCollection();
        return $this;
    }
    
	protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
    
	public function getRowUrl($row){
        return $this->getUrl('*/*/edit', array(
	            'store'=>$this->getRequest()->getParam('store'),
	            'id'=>$row->getId()
        ));
    }
    
    protected function _prepareColumns(){        
        $this->addColumn('entity_id', array(
            'header'        => Mage::helper('authorizenetcim')->__('ID'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'entity_id'
        ));
        
        $this->addColumn('customer_id', array(
            'header'        => Mage::helper('authorizenetcim')->__('Customer ID'),
            'align'         => 'right',
            'width'         => '5px',
            'index'         => 'customer_id'
        ));
        
        $this->addColumn('customer_email', array(
            'header'        => Mage::helper('authorizenetcim')->__('Customer Email'),
            'align'         => 'right',
            'width'         => '80px',
            'index'         => 'customer_email'
        ));
        
        $this->addColumn('first_name', array(
            'header'        => Mage::helper('authorizenetcim')->__('First Name'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'first_name'
        ));
        
        $this->addColumn('last_name', array(
            'header'        => Mage::helper('authorizenetcim')->__('First Name'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'last_name'
        ));
        
        $this->addColumn('address', array(
            'header'        => Mage::helper('authorizenetcim')->__('Address'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'address'
        ));
        
        $this->addColumn('city', array(
            'header'        => Mage::helper('authorizenetcim')->__('City'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'city'
        ));
        
        $this->addColumn('region', array(
            'header'        => Mage::helper('authorizenetcim')->__('Region'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'region'
        ));
        
        $this->addColumn('country', array(
            'header'        => Mage::helper('authorizenetcim')->__('Country'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'country'
        ));
        
        $this->addColumn('phone_number', array(
            'header'        => Mage::helper('authorizenetcim')->__('Phone Number'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'phone_number'
        ));
        
        $this->addColumn('last4digits', array(
            'header'        => Mage::helper('authorizenetcim')->__('Last 4 digits'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'last4digits'
        ));
        
        $this->addColumn('expire_date', array(
            'header'        => Mage::helper('authorizenetcim')->__('Expire Date'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'expire_date'
        ));
        
        $this->addColumn('status', array(
            'header'        => Mage::helper('authorizenetcim')->__('Card Status'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'status',
            'type'			=> 'options',
            'options' => Mage::getModel('authorizenetcim/profilemanager')->toOptionsArray()
        ));
        
    }
    
}