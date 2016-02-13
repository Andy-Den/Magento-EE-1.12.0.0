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
 * @package     Harapartners\Webservice\controllers
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/Vendoroptions/controllers/Adminhtml/Shipping.php
class Harapartners_Vendoroptions_Adminhtml_ConfigureController extends Mage_Adminhtml_Controller_Action {
	
	/**
	 * Check allow or not access to ths page
	 *
	 * @return bool - is allowed to access this menu
	 */
	protected function _isAllowed() {
		return Mage::getSingleton ( 'admin/session' )->isAllowed ( 'webservice/configure' );
	}
	
	/**
	 * View form action
	 */
	public function indexAction() {
		$this->loadLayout ();
		$this->_setActiveMenu ( 'vendoroptions/vendoroptions_configure' );
		//$this->_addBreadcrumb ( Mage::helper ( 'webservice' )->__ ( 'Shipping' ), Mage::helper ( 'webservice' )->__ ( 'Form' ) );
		$this->_addContent ( $this->getLayout ()->createBlock ( 'vendoroptions/adminhtml_configure_index' ) );
		$this->renderLayout ();
	}
	
	public function newAction()
	{
	    $this->editAction();
	}
	
	public function editAction() {
		$id = $this->getRequest ()->getParam ( 'id' );
		$model = Mage::getModel ( 'vendoroptions/vendoroptions_configure' )->load ( $id );
		
		if ($model->getId () || $id == 0) {
			$data = Mage::getSingleton ( 'adminhtml/session' )->getData ( 'vendoroptions_configure_data' );
			if (! empty ( $data )) {
				$model->setData ( $data );
			}
			
			Mage::register ( 'vendoroptions_configure_data', $model );
			
			$this->loadLayout ()->_setActiveMenu ( 'vendoroptions/vendoroptions_configure' );
			
			$this->_addBreadcrumb ( Mage::helper ( 'vendoroptions' )->__ ( 'Manage Buy X Rules' ), Mage::helper ( 'adminhtml' )->__ ( 'Manage Buy X Rules' ) );
			$this->_addBreadcrumb ( Mage::helper ( 'vendoroptions' )->__ ( 'Buy X Rule Configuration' ), Mage::helper ( 'adminhtml' )->__ ( 'Buy X Rule Configuration' ) );
			
			$this->getLayout ()->getBlock ( 'head' )->setCanLoadExtJs ( true );
			
			$this->_addContent ( $this->getLayout ()->createBlock ( 'vendoroptions/adminhtml_configure_edit' ) );
			
			$this->renderLayout ();
		} else {
			Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'vendoroptions' )->__ ( 'Vendor Id does not exist' ) );
			$this->_redirect ( '*/*/' );
		}
	}
	
	public function saveAction() {
		if ($data = $this->getRequest ()->getPost ()) {
			$model = Mage::getModel ( 'vendoroptions/vendoroptions_configure' );
			$model->setData ( $data )->setId ( $this->getRequest ()->getParam ( 'id' ) );
			
			try {
				$model->save ();
				Mage::getSingleton ( 'adminhtml/session' )->addSuccess ( Mage::helper ( 'vendoroptions' )->__ ( 'Vendor Details Saved' ) );
				Mage::getSingleton ( 'adminhtml/session' )->setData ( false );
				
				if ($this->getRequest ()->getParam ( 'back' )) {
					$this->_redirect ( '*/*/edit', array ('id' => $model->getId () ) );
					return;
				}
				$this->_redirect ( '*/*/' );
				return;
			} catch ( Exception $e ) {
				Mage::getSingleton ( 'adminhtml/session' )->addError ( $e->getMessage () );
				Mage::getSingleton ( 'adminhtml/session' )->setData ('vendoroptions_configure_data', $data );
				$this->_redirect ( '*/*/edit', array ('id' => $this->getRequest ()->getParam ( 'id' ) ) );
				return;
			}
		}
		Mage::getSingleton ( 'adminhtml/session' )->addError ( Mage::helper ( 'vendoroptions' )->__ ( 'Unable to find Vendor Options to save' ) );
		$this->_redirect ( '*/*/' );
	}
}
