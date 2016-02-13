<?php
class SL_SignatureLink_Model_Threatoptions
{
	public function toOptionArray()
	{
		$ret = Array(
			Array('value' => 'none',	'label' => 'Never Disable'),
			Array('value' => 'medium',	'label' => 'Disable on Medium Threat'),
			Array('value' => 'high',	'label' => 'Disable on High Threat')
		);
		return $ret;
	}
}
