<?php
class Sl_Signaturelink_IndexController extends Mage_Core_Controller_Front_Action
{
    public function autherrorAction()
    {
        $this->loadLayout();
        $this->getLayout()->getBlock('head')->setTitle($this->__('Authentication Error'));
        $this->renderLayout();
    }
}