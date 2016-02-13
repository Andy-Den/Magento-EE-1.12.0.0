<?php
/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * MultiStore status load
 * Description of rpro9_rdi_pos_multistore_status_load
 * Not supported and only here for completeness and to halt any errors.
 *
 * Extends rdi_import_xml. Functions specific for writing order data.
 *
 * PHP version 5.3
 *
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    1.0.1
 * @package     Core\Load\MultiStore\RPro9
 */
class rdi_pos_multistore_load extends rdi_load 
{
    const STORE_TABLE       = 'rpro_in_store';
    const STORE_ALIAS 		= 'store';
    const STORE_KEY   		= 'store_code';
    const STORE_QTY_TABLE   = 'rpro_in_store_qty';
    const STORE_QTY_ALIAS   = 'store_qty';
    const STORE_QTY_KEY     = 'item_sid';
    
	
	// functions for completeness
	public function pos_load()
	{
		return $this;
	}
}

?>
