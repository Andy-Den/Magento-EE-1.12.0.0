<?php
class SL_Signaturelink_Adminhtml_SignaturelinkController extends Mage_Adminhtml_Controller_Action
{

	protected function _initAction()
	{
		$this->loadLayout()->_setActiveMenu('signaturelink/items');

		return $this;
	}

	public function indexAction() {
		//$this->_initAction()->renderLayout();
		$this->_initAction();

		$this->_addContent($this->getLayout()->createBlock('signaturelink/adminhtml_signaturelink'));
		$this->renderLayout();
	}

    public function whitelistAction()
    {
        $request = $this->getRequest();
        $ajax = $this->getRequest()->isAjax();
        $params = $request->getParams();

        if($ajax){
            $pagesGrid = $this->getLayout()->createBlock('signaturelink/adminhtml_whitelist_grid');
            $this->getResponse()->setBody($pagesGrid->toHtml());
        }
        else
        {
            $this->_title($this->__('SignatureLink Whitelist Management'));
            $this->loadLayout();
            $this->renderLayout();
        }

    }

    public function whitelistAddAction()
    {
        $params = $this->getRequest()->getParams();

        /** @var $whitelist SL_Signaturelink_Model_Whitelist */
        $whitelist = Mage::getModel('signaturelink/whitelist');

        $whitelist->setWhitelistEmail(strtolower(trim($params['email'])));

        $adminUsername = Mage::getSingleton("admin/session")->getUser()->getUsername();
        $whitelist->setAdminResponsible($adminUsername);


        $whitelist->setActive(true);

        $whitelist->save();

        $url = Mage::helper('adminhtml')->getUrl('*/sales_order/view', array('order_id' => $params['order_id']));

        $this->getResponse()->setRedirect($url);

    }

    /**
     * Process the editing/deleting/adding of new Whitelist entries via ajax
     */
    public function whitelistProcessEditAction()
    {

        $params = $this->getRequest()->getParams();

        $success = true;
        $message = '';

        /** @var $whitelist SL_Signaturelink_Model_Whitelist */
        $whitelist = Mage::getModel('signaturelink/whitelist');

        //Are we editing an existing whitelist entry?
        if(array_key_exists('action', $params) && $params['action'] == 'edit')
        {
            if(!array_key_exists('id', $params))
            {
                $success = false;
                $message = 'Unable to edit the whitelist entry. Please try again.';
            }

            else
            {
                try{
                    $adminUsername = Mage::getSingleton("admin/session")->getUser()->getUsername();
                    $whitelist->load($params['id']);
                    $whitelist->setWhitelistEmail(strtolower(trim($params['email'])));
                    $whitelist->setAdminResponsible($adminUsername);
                    $whitelist->save();

                    $message = 'Whitelist entry successfully updated';

                }catch(Exception $e){
                    //Something happened beyong our control
                    $success = false;
                    $message = 'There was an error editing your whitelist entry.';
                }
            }
        }
        //Else we may be creating a new whitelist entry
        else if(array_key_exists('action', $params) && $params['action'] == 'add')
        {
            try{
                $adminUsername = Mage::getSingleton("admin/session")->getUser()->getUsername();
                $whitelist->setWhitelistEmail(strtolower(trim($params['email'])));
                $whitelist->setAdminResponsible($adminUsername);
                $whitelist->setActive(true);
                $whitelist->save();

                $message = 'Whitelist entry successfully added.';

            }catch(Exception $e){
                //Something happened beyong our control
                $success = false;
                $message = 'There was an error adding your whitelist entry.';
            }
        }
        //Else we may be deleting an entry
        else if(array_key_exists('action', $params) && $params['action'] == 'delete')
        {
            if(!array_key_exists('id', $params))
            {
                $success = false;
                $message = 'Unable to edit the whitelist entry. Please try again.';
            }

            else
            {
                try{
                    $whitelist->load($params['id']);
                    $whitelist->setActive(false);
                    $whitelist->save();

                    $message = 'Whitelist entry successfully removed.';

                }catch(Exception $e){
                    //Something happened beyong our control
                    $success = false;
                    $message = 'There was an error editing your whitelist entry.';
                }
            }
        }
        else
        {
            $success = false;
            $message = "Unable to process your request. Please try again.";
        }

        $result = array(
            'success' => $success,
            'message' => $message
        );

        $this->getResponse()->setBody(json_encode($result));
        return;

    }

}
