<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 */

class Slap_Aim_Model_Source_Cavvlist
{
    /**
     * Prepare CAVV list
     *
     * @return array
     */
    public function toOptionArray()
    {
		$options = array();
		$options[] = array('value'=>'0', 'label'=>'0 - CAVV not validated because erroneous data was submitted');
		$options[] = array('value'=>'1', 'label'=>'1 - CAVV failed validation');
		$options[] = array('value'=>'2', 'label'=>'2 - CAVV passed validation');
		$options[] = array('value'=>'3', 'label'=>'3 - CAVV validation could not be performed, issuer attempt incomplete');
		$options[] = array('value'=>'4', 'label'=>'4 - CAVV validation could not be performed, issuer system error');
		$options[] = array('value'=>'5', 'label'=>'5 - Reserved for future use');
		$options[] = array('value'=>'6', 'label'=>'6 - Reserved for future use');
		$options[] = array('value'=>'7', 'label'=>'7 - CAVV attempt failed validation issuer available(U.S.-issued card/non-U.S acquirer)');
		$options[] = array('value'=>'8', 'label'=>'8 - CAVV attempt passed validation issuer available(U.S.-issued card/non-U.S. acquirer)');
		$options[] = array('value'=>'9', 'label'=>'9 - failed validation issuer unavailable(U.S.-issued card/non-U.S. acquirer)');
		$options[] = array('value'=>'A', 'label'=>'A - passed validation issuer unavailable(U.S.-issued card/non-U.S. acquirer)');
		$options[] = array('value'=>'B', 'label'=>'B - CAVV passed validation, information only, no liability shift');
		return $options;
        
    }
}
