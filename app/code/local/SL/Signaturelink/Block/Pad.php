<?php 
class SL_Signaturelink_Block_Pad extends Mage_Core_Block_Template
{
	public function isEnabled()
	{
		return Mage::helper('signaturelink')->isEnabled();
	}

	public function displayTCs()
	{
		return Mage::helper('signaturelink')->displayTCs();
	}
}
