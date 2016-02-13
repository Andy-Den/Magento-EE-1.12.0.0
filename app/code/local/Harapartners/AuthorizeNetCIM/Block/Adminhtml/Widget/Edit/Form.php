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
 
class Harapartners_AuthorizeNetCIM_Block_Adminhtml_Widget_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
	
	protected function _prepareForm() {
        $form = new Varien_Data_Form(array(
            'id'        => 'edit_form',
            'action'    => $this->getData('action'),
            'method'    => 'post'
        ));
        
        $fieldset = $form->addFieldset('card_info', array('legend'=>Mage::helper('authorizenetcim')->__('Card Info')));

        $fieldset->addField('status', 'select', array(
            'label'     => Mage::helper('authorizenetcim')->__('Status'),
            'name'      => 'status',
            'values'    => Mage::getModel('authorizenetcim/profilemanager')->toOptionsArray(),
        ));
        
        $fieldset->addField('first_name', 'text', array(
            'label'     => Mage::helper('authorizenetcim')->__('First Name'),
            'name'      => 'first_name',
        	'required'	=> true
        ));
        
        $fieldset->addField('last_name', 'text', array(
            'label'     => Mage::helper('authorizenetcim')->__('Last Name'),
            'name'      => 'last_name',
        	'required'	=> true
        ));
        
        $fieldset->addField('customer_id', 'text', array(
            'label'     => Mage::helper('authorizenetcim')->__('Customer ID'),
            'name'      => 'customer_id',
        	'required'	=> true,
        	'note'		=> Mage::helper('authorizenetcim')->__('Please make sure to enter a valid customer ID'),
        ));
        
        $fieldset->addField('cc_number', 'text', array(
            'label'     => Mage::helper('authorizenetcim')->__('Card Number'),
            'name'      => 'cc_number',
        	'required'	=> true,
        ));
        
        $fieldset->addField('cc_cid', 'text', array(
            'label'     => Mage::helper('authorizenetcim')->__('Card CCV'),
            'name'      => 'cc_cid',
        	'required'	=> true,
        ));
        
        $fieldset->addField('expire_date', 'text', array(
            'label'     => Mage::helper('authorizenetcim')->__('Expire Date'),
            'name'      => 'expire_date',
        	'required'	=> true,
        	'note'		=> Mage::helper('authorizenetcim')->__('Format: YYYY-MM')
        ));
        
        $fieldset->addField('company', 'text', array(
            'label'     => Mage::helper('authorizenetcim')->__('Company'),
            'name'      => 'company',
        ));
        
        $fieldset->addField('address', 'text', array(
            'label'     => Mage::helper('authorizenetcim')->__('Address'),
            'name'      => 'address',
        	'required'	=> true,
        ));
        
        $fieldset->addField('city', 'text', array(
            'label'     => Mage::helper('authorizenetcim')->__('City'),
            'name'      => 'city',
        	'required'	=> true,
        ));
        
        $fieldset->addField('zipcode', 'text', array(
            'label'     => Mage::helper('authorizenetcim')->__('Zipcode'),
            'name'      => 'zipcode',
        	'required'	=> true,
        ));
        
        $fieldset->addField('phone_number', 'text', array(
            'label'     => Mage::helper('authorizenetcim')->__('Phone Number'),
            'name'      => 'phone_number',
        	'required'	=> true,
        ));
        
        $fieldset->addField('country', 'select', array(
            'label'     => Mage::helper('authorizenetcim')->__('Country'),
            'name'      => 'country_id',
            'values'    => Mage::getModel('authorizenetcim/profilemanager')->getCountryOptionsArray(),
        ));
        
        $fieldset->addField('region_id', 'select', array(
            'label'     => Mage::helper('authorizenetcim')->__('States'),
            'name'      => 'region_id',
            'values'    => Mage::getModel('authorizenetcim/profilemanager')->getRegionOptionsArray(),
        ));
        
        $fieldset->addField('entity_id', 'hidden', array(
            'label'     => Mage::helper('authorizenetcim')->__('Payment Profile Entity ID'),
            'name'      => 'profile_id',
        ));
        
        
//        $fieldset->addField('region', 'select', array(
//            'label'     => Mage::helper('authorizenetcim')->__('Region'),
//            'name'      => 'region_id',
//            'values'    => Mage::getModel('customer/entity_address_attribute_source_region'),
//        	'renderer'	=> Mage::getModel('adminhtml/customer_renderer_region')
//        ));

        if ( $cardInfoData = Mage::getSingleton('adminhtml/session')->getFormData() ){
            $form->setValues($cardInfoData);
            Mage::getSingleton('adminhtml/session')->setFormData(null);
        } elseif ( Mage::registry('authorizenetcim_cardinfo_data') ) {
            $form->setValues(Mage::registry('authorizenetcim_cardinfo_data')->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);
        return parent::_prepareForm();
    }
	
}