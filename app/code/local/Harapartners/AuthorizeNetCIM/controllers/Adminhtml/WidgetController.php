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
 
class Harapartners_AuthorizeNetCIM_Adminhtml_WidgetController extends Mage_Adminhtml_Controller_Action{
	public function indexAction(){
		$this->loadLayout()
			->_setActiveMenu('authorizenetcim/widget')
			->_addContent($this->getLayout()->createBlock('authorizenetcim/adminhtml_widget_index'))
			->renderLayout();
	}
	
	public function newAction(){
		$this->loadLayout()
			->_setActiveMenu('authorizenetcim/widget')
			->_addContent($this->getLayout()->createBlock('authorizenetcim/adminhtml_widget_edit'))
			->renderLayout();
	}
	
	public function saveAction(){
        $postData = $this->getRequest()->getParams();
        $postData['admin'] = 1; //Important flag for AuthnetCIM account creation logic
		try{
			if(empty($postData['id'])){
				Mage::getModel("authorizenetcim/profilemanager")->addCreditCard($postData);
				Mage::getSingleton('adminhtml/session')->addSuccess( Mage::helper("authorizenetcim")->__( "Your credit card information has been saved." ) );
			}else{
				Mage::getModel("authorizenetcim/profilemanager")->editCreditCard($postData);
				Mage::getSingleton('adminhtml/session')->addSuccess( Mage::helper("authorizenetcim")->__( "Your credit card information has been updated." ) );
			}
			
		}catch(Exception $e){
			Mage::getSingleton('adminhtml/session')->addError( Mage::helper("authorizenetcim")->__( $e->getMessage() ) );
		}
		$this->getResponse()->setRedirect(Mage::getUrl('*/*'));
    }
    
	public function editAction(){
		$id = $this->getRequest()->getParam('id');
		$creditCard  = Mage::getModel('authorizenetcim/profilemanager')->load($id);

		if ($creditCard->getId() || $id == 0) {
			$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			if (!empty($data)) {
				$creditCard->setData($data);
			}

			Mage::register('authorizenetcim_cardinfo_data', $creditCard);
			$this->loadLayout()->_setActiveMenu('authorizenetcim/widget');
			$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			$this->_addContent($this->getLayout()->createBlock('authorizenetcim/adminhtml_widget_edit'));
			$this->renderLayout();
		} else {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('authorizenetcim')->__('Card does not exist'));
			$this->_redirect('*/*/');
		}
    }
    
    public function deleteAction(){
    	$profileId = $this->getRequest()->getParam('id');
    	$creditCard  = Mage::getModel('authorizenetcim/profilemanager')->load($profileId);
		try{
			if ($creditCard->getId() || $profileId == 0) {
				$creditCard->deleteCreditCard($creditCard->getId());
				Mage::getSingleton('adminhtml/session')->addSuccess( Mage::helper("authorizenetcim")->__( "Your credit card information has been removed." ) );
			} else {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('authorizenetcim')->__('Card does not exist'));
				$this->_redirect('*/*/');
			}
		} catch (Exception $e){
			Mage::getSingleton('adminhtml/session')->addError( Mage::helper("authorizenetcim")->__( $e->getMessage() ) );
		}
		$this->getResponse()->setRedirect(Mage::getUrl('*/*'));
		
    }
	
	
}