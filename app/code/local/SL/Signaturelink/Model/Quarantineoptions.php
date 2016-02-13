<?php
class SL_SignatureLink_Model_Quarantineoptions
{
	public function toOptionArray()
	{
		$ret = Array(
			Array('value' => '1',	'label' => 'Always'),
			Array('value' => '0',	'label' => 'Never'),
			Array('value' => '2',	'label' => 'Based on configuration')
		);
		return $ret;
	}
}
