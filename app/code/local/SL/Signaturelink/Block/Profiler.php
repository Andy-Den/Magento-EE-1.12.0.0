<?php 
class SL_Signaturelink_Block_Profiler extends Mage_Core_Block_Template
{
	public function isTMEnabled()
	{
		return Mage::helper('signaturelink')->isTMEnabled();
	}
}
