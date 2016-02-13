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
 
class Harapartners_AuthorizeNetCIM_ManageController extends Mage_Core_Controller_Front_Action{
	
	protected $_customerSession;
	
    protected function _getCustomerSession(){
    	if( ! $this->_customerSession ) {
    		$this->_customerSession = Mage::getSingleton('customer/session');
    	}
        return $this->_customerSession;
    }

    public function preDispatch(){
        parent::preDispatch();
        if (!$this->getRequest()->isDispatched()) {
            return;
        }
        if (!$this->_getCustomerSession()->authenticate($this)) {
            $this->setFlag('', 'no-dispatch', true);
        }
    }
	
	public function indexAction(){
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->renderLayout();
	}
	
	public function newAction(){
		$this->_forward( "edit" );
	}
	
	public function newPostAction(){
		$postData = $this->getRequest()->getParams();
		try{
			Mage::getModel("authorizenetcim/profilemanager")->addCreditCard($postData);
			$this->_getCustomerSession()->addSuccess( Mage::helper("authorizenetcim")->__( "Your credit card information has been saved" ) );
		}catch(Exception $e){
			$this->_getCustomerSession()->addError( Mage::helper("authorizenetcim")->__( $e->getMessage() ) );
		}
		$this->getResponse()->setRedirect(Mage::getUrl('*/*'));
	}
	
	public function deleteAction(){
		$postData = $this->getRequest()->getParams();
		$decodedDataArray = unserialize( Mage::helper( "authorizenetcim" )->uriDecode( isset($postData['key']) ? $postData['key'] : null ) );
		$profileId = isset( $decodedDataArray[ 'profile_id' ] ) ? $decodedDataArray[ 'profile_id' ] : null;
		$customerId = isset( $decodedDataArray[ 'customer_id' ] ) ? $decodedDataArray[ 'customer_id' ] : null;
		if( $customerId != $this->_getCustomerSession()->getCustomerId() ) {
			$this->_getCustomerSession()->addError(Mage::helper('authorizenetcim')->__("Invalid Customer"));
			$this->getResponse()->setRedirect(Mage::getUrl('*/*'));
			return;
		}
		try{
			Mage::getModel("authorizenetcim/profilemanager")->deleteCreditCard($profileId);
			$this->_getCustomerSession()->addSuccess( Mage::helper("authorizenetcim")->__( "Your saved credit card has been deleted" ) );
		}catch(Exception $e){
			$this->_getCustomerSession()->addError( Mage::helper("authorizenetcim")->__( $e->getMessage() ) );
		}
		$this->getResponse()->setRedirect(Mage::getUrl('*/*'));
	}
	
	public function editAction(){
		$postData = $this->getRequest()->getParams();
		if(isset($postData['key'])) {
			$decodedDataArray = unserialize( Mage::helper( "authorizenetcim" )->uriDecode( isset($postData['key']) ? $postData['key'] : null ) );
			$profileId = isset( $decodedDataArray[ 'profile_id' ] ) ? $decodedDataArray[ 'profile_id' ] : null;
			$customerId = isset( $decodedDataArray[ 'customer_id' ] ) ? $decodedDataArray[ 'customer_id' ] : null;
			if( $customerId != $this->_getCustomerSession()->getCustomerId() ) {
				$this->_getCustomerSession()->addError(Mage::helper('authorizenetcim')->__("Invalid Customer"));
				$this->getResponse()->setRedirect(Mage::getUrl('*/*'));
				return;
			}
			$paymentProfile = Mage::getModel('authorizenetcim/profilemanager')->load($profileId);
			if( $paymentProfile->getId() ) {
				Mage::register('payment_profile_object',$paymentProfile);
			}
		}
		$this->loadLayout()->_initLayoutMessages('customer/session')->renderLayout();
	}
	
	public function editPostAction(){
		$postData = $this->getRequest()->getParams();
		$profileId = $this->getRequest()->getParam('profile_id');
		if(!!$profileId){
			try{
				Mage::getModel("authorizenetcim/profilemanager")->editCreditCard($postData);
				$this->_getCustomerSession()->addSuccess( Mage::helper("authorizenetcim")->__( "Your credit card information has been updated." ) );
			}catch(Exception $e){
				$this->_getCustomerSession()->addError( Mage::helper("authorizenetcim")->__( $e->getMessage() ) );
			}
			
			$customerId = $this->_getCustomerSession()->getCustomerId();
			$encodedUri = "key/" . Mage::helper( 'authorizenetcim' )->uriEncode( serialize( array( 
				'profile_id' => $profileId, 
				'customer_id' => $customerId 
			)));
			
			$this->getResponse()->setRedirect(Mage::getUrl('*/*/edit') . $encodedUri);
		}else{
			$this->_getCustomerSession()->addError( Mage::helper("authorizenetcim")->__( "Invalid profile ID." ) );
			$this->getResponse()->setRedirect(Mage::getUrl('*/*'));
		}
	}
}