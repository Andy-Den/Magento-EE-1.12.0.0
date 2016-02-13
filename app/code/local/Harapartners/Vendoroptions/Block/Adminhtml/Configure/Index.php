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

// app/code/local/Harapartners/Vendoroptions/Block/Adminhtml/Configure/Index.php
class Harapartners_Vendoroptions_Block_Adminhtml_Configure_Index extends Mage_Adminhtml_Block_Widget_Grid_Container {
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct ();
		$this->_blockGroup = 'vendoroptions';
		$this->_controller = 'adminhtml_configure_index';
		$this->_headerText = Mage::helper ( 'vendoroptions' )->__ ( 'Configure Edit' );
	}	
}