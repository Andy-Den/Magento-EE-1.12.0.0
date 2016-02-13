<?php
class SL_SignatureLink_Model_Fraudstatuses
{
	public function toOptionArray()
	{
		$ret = Array(
			Array('value' => 'fraud',	'label' => 'Suspected Fraud'),
			Array('value' => 'holded',	'label' => 'On Hold')
		);
		return $ret;
	}
}
