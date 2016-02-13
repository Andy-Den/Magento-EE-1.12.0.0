<?php

/*
 * Load images into the magento cache
 */

/**
 * Description of rdi_cart_image_load
 *
 * @author PBliss
 */
class rdi_pos_upsell_item_load extends rdi_upsell_item_load 
{
	const UPSELL_TABLE 		= "rpro_in_upsell_item";
	const UPSELL_ALIAS 		= "upsell_item";
	const UPSELL_PARENT 	= "style_sid";
	const UPSELL_PRODUCT	= "upsell_sid";
	const UPSELL_POSITION	= "order_no";
	
}

?>
