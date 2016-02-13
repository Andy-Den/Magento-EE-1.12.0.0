<?php 

class Harapartners_HpReport_Adminhtml_SalesforchannelController extends Mage_Adminhtml_Controller_Action {
	
	public function indexAction(){
		$this->loadLayout()
			->_setActiveMenu('hpreport')
			->_addContent($this->getLayout()->createBlock('hpreport/adminhtml_sales_order_channels_grid'))			
			->renderLayout();
    }
    
	public function gridAction(){
		$this->getResponse()->setBody(
            $this->getLayout()->createBlock('hpreport/adminhtml_sales_order_channels_grid','adminhtml_sales_order_channels_grid')
                ->toHtml()
        );
    }
    
    public function exportAction(){
        $fileName = 'sales_for_channels_' .date("Ymd"). '.csv';
        $content = $this->getLayout()
					->createBlock('hpreport/adminhtml_statereport_view_grid')
					->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

}
