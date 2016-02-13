<?php
class SL_Signaturelink_Block_Sales_Order_View extends Mage_Sales_Block_Order_View
{
	public function getPaymentInfoHtml()
	{
		return $this->getChildHtml('signaturelink_info');
	}
}
