<?php
class SL_SignatureLink_Model_Securebuyoption
{
	public function toOptionArray()
	{
		$ret = Array(
			Array('value' => '1',	'label' => 'Always'),
			Array('value' => '2',	'label' => 'Based $'),
			Array('value' => '0',	'label' => 'Never')
		);
		return $ret;
	}
}
