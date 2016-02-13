<?php
class SL_Signaturelink_Model_Observer
{
	public function watchQuoteSave($observer)
	{
        $request = Mage::app()->getRequest();

        if ($request->getRequestUri() == '/checkout/onepage/savePayment/') {
            if ($curSID = $request->getParam('slSid')) {
                Mage::getSingleton('checkout/session')->setSLSid($curSID);
            }
        }
	}

	public function getCaptureData($observer)
	{
		//$pdfIsEnable = $this->getConfig('sl_config', 'sales_receipt_pdf');
		
        $order = $observer->getEvent()->getOrder();
        $request = Mage::app()->getRequest();

        //$sid		= Mage::getSingleton('checkout/session')->getSLSid();
        $sid		= $request->getParam('slSid');
        //$profileId	= $request->getParam('slProfile');
		//$hdnReturnedLanguageCode = $request->getParam('hid');
		
		$parts   = explode ("," , $request->getParam('slProfile'));  
		$sid   = $request->getParam('slSid');
		$profileId  = $parts[0];
		$hdnReturnedLanguageCode = $parts[1];
		
		
        if (!$sid) {
            return false;
        }

        $helper = Mage::helper('signaturelink');
        $htmlData	= base64_decode($request->getParam('slCapture'));

        //Mage::log($order->getPayment()->debug());

        $profileData = $helper->getProfileData($profileId);

        Mage::getModel('signaturelink/sid')
            ->setOrderId($order->getEntityId())
            ->setSlSid($sid)
            ->setTmScore($profileData->PolicyScore)
            ->setTmRisk($profileData->RiskRating)
            ->setCreatedAt(NULL)
            ->save();
		//if($pdfIsEnable) {
			$helper->generatePDF($order, $sid, $profileId, $htmlData, $hdnReturnedLanguageCode);
		//}	
        Mage::getSingleton('checkout/session')->unsSLSid();
	}

    /**
     * This observer should intercept the save payment process, to prevent any charges being actually generated.
     *
     * @param $observer
     */
    public function controllerActionPredispatchCheckoutOnepageSavePayment($observer)
    {
        /** @var $controllerAction Mage_Checkout_OnepageController */
        $controllerAction = $observer->getControllerAction();

        $controllerAction->getRequest()->setParams(array());

        //Check to see if this order should be quarentined!
        if(Mage::getSingleton('checkout/session')->getQuarantine() === true)
        {
            $controllerAction->getRequest()->setParam('payment', array('method' => 'signaturelink'));
            $controllerAction->getRequest()->setPost('payment', array('method' => 'signaturelink'));
        }
        return $observer;
    }

    /**
     * This observer should intercept the save payment process, to prevent any charges being actually generated.
     *
     * @param $observer
     */
    public function controllerActionPredispatchCheckoutOnepageSaveOrder($observer)
    {

        /** @var $controllerAction Mage_Checkout_OnepageController */
        $controllerAction = $observer->getControllerAction();

        $controllerAction->getRequest()->setParams(array());

        //Check to see if this order should be quarentined!
        if(Mage::getSingleton('checkout/session')->getQuarantine() === true)
        {
            $controllerAction->getRequest()->setParam('payment', array('method' => 'signaturelink'));
            $controllerAction->getRequest()->setPost('payment', array('method' => 'signaturelink'));
        }
        return $observer;
    }



    /**
     * This observer should intercept the save payment process, to prevent any charges being actually generated.
     *
     * @param $observer
     */
    public function salesConvertQuotePaymentToOrderPayment($observer)
    {
        //Check to see if this order should be quarentined!
        if(Mage::getSingleton('checkout/session')->getQuarantine() === true)
        {
            /** @var $orderPayment Mage_Sales_Model_Order_Payment */
            $orderPayment = $observer->getOrderPayment();

            /** @var $quotePayment Mage_Sales_Model_Quote_Payment */
            $quotePayment = $observer->getQuotePayment();

            $orderPayment->setMethod('signaturelink');
            $quotePayment->setMethod('signaturelink');
        }
        return $observer;
    }

    public function checkoutTypeOnepageSaveOrderAfter($observer)
    {

        /** @var $order Mage_Sales_Model_Order */
        $order = $observer->getOrder();

        if($order->getStatus() == 'sl_authentication_failed')
        {
            $order->setCanSendNewEmailFlag(false);

            //Need to reset the status to authentication failed as cancel sets default status ("canceled")
            $order->cancel()->setStatus('sl_authentication_failed')->save();
        }


    }

    public function checkoutSubmitAllAfter($observer)
    {
        /** @var $order Mage_Sales_Model_Order */
        $order = $observer->getOrder();

        if($order->getStatus() == 'sl_authentication_failed')
        {
            /** @var $onepage Mage_Checkout_Model_Type_Onepage */
            $onepage = Mage::getSingleton('checkout/type_onepage');
            $url = Mage::getUrl('sl/autherror');
            $onepage->getCheckout()->setRedirectUrl(Mage::getUrl('sl/index/autherror'));
        }
    }

    /**
     *
     * @param Varien_Event_Observer $observer
     * @return Varien_Event_Observer
     */
    public function coreBlockAbstractPrepareLayoutAfter(Varien_Event_Observer $observer)
    {

        if (Mage::app()->getFrontController()->getAction()->getFullActionName() === 'adminhtml_sales_order_view')
        {
            /** @var $block Mage_Adminhtml_Block_Sales_Order_View */
            $block = $observer->getBlock();
            if ($block->getNameInLayout() === 'sales_order_edit')
            {
                $order = $block->getOrder();

                if($order->getStatus() == 'sl_authentication_failed' && !Mage::helper('signaturelink')->isWhitelisted($order->getCustomerEmail()))
                {
                    $url = Mage::helper('adminhtml')->getUrl('adminhtml/signaturelink/whitelistAdd', array('email' => urlencode($order->getCustomerEmail()), 'order_id' => $order->getId()));
                    $block->addButton('whitelist_customer_email', array(
                        'label'     => Mage::helper('signaturelink')->__('Whitelist Customer Email'),
                        'onclick'   => 'setLocation(\'' . $url . '\')'
                    ));
                }
            }
        }

        return $observer;
    }
}
