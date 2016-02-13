<?php
class SL_SignatureLink_Model_Thresholdoptions
{
	public function toOptionArray()
	{
		$ret = Array(
            Array('value' => 'low','label' => 'Disable on Low Threat'),
            Array('value' => 'neutral',	'label' => 'Disable on Neutral Threat'),
			Array('value' => 'medium',	'label' => 'Disable on Medium Threat'),
			Array('value' => 'high',	'label' => 'Disable on High Threat')
		);
		return $ret;
	}
}
