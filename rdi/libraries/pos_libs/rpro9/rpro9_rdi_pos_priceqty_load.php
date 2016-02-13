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
 * @version    2.0.0
 * @package     Core\Load\MultiStore\Smyth
 */
class rdi_pos_priceqty_load extends rdi_load {

    const PRICEQTY_TABLE = 'rpro_in_priceqty';
    const PRICEQTY_ALIAS = 'item';
    const PRICEQTY_KEY = 'item_sid';
    const PRICEQTY_PARENT = 'style_sid';
    const PRICEQTY_QUANTITY_FIELD = 'quantity - item.so_committed';
    const PRICEQTY_PRICE_FIELD = 'reg_price';
    const PRICEQTY_SPECIAL_PRICE_FIELD = 'sale_price';

    
    public function load()
    {
        global $pos;        
        $this->db_connection->trunc("rpro_in_priceqty");

        $pos->get_processor("rdi_import_priceqty_xml")->load();
        
        parent::load();
    }
    
    // functions for completeness
    public function pos_load()
    {
        return $this;
    }

}

?>
