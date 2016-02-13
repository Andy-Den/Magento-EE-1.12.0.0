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
 */

class Harapartners_AuthorizeNetCIM_Model_Source_Customer_Group {
	
    protected $_options;
    protected $_isGuestGroupAllowed = true;

    public function toOptionArray() {
        if (!$this->_options) {
            $customerGroupCollection = Mage::getResourceModel('customer/group_collection');
            if(!$this->_isGuestGroupAllowed){
            	$customerGroupCollection->setRealGroupsFilter();
            }
            $this->_options = $customerGroupCollection->loadData()->toOptionArray();
            array_unshift($this->_options, array('value'=> '', 'label'=> Mage::helper('adminhtml')->__('-- Please Select --')));
        }
        return $this->_options;
    }
    
}