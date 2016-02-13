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
 * @author     PMBliss <tmartin@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    1.0.1
 * @package     Core\Load\MultiStore\RPro9
 */
class rdi_pos_multistore_status_load extends rdi_general 
{
    const STORE_TABLE       = 'rpro_in_store';
    const STORE_TABLE_ALIAS = 'store';
    const STORE_TABLE_KEY   = 'store_code';
    const STORE_QTY_TABLE   = 'rpro_in_store_qty';
    const STORE_QTY_ALIAS   = 'store_qty';
    const STORE_QTY_KEY     = 'item_sid';
    
    /**
     * Class Constructor
     *
     * @param rdi_db $db
     */
    public function rdi_pos_multistore_status_load($db = '')
    {
        if ($db)
            $this->set_db($db);
    }
       /**
        * Pre Load Function
        * @global rdi_hook $hook_handler
        */     
    public function pre_load()
    {
       global $hook_handler;       
        
        $hook_handler->call_hook("pos_multistore_status_pre_load");
    }
    /**
        * Post Load Function
        * @global rdi_hook $hook_handler
        */ 
    public function post_load()    
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_multistore_status_post_load");
    }
}

?>
