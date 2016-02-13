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
 
class Harapartners_AuthorizeNetCIM_Block_Adminhtml_Widget_Index extends Mage_Adminhtml_Block_Widget_Grid_Container{
	
	public function __construct(){
		$this->_blockGroup = 'authorizenetcim';
		$this->_controller = 'adminhtml_widget_index';
    	$this->_headerText = Mage::helper('authorizenetcim')->__('Cards Info in AuthorizeNetCIM Widget');
    	parent::__construct();
	}
	
}