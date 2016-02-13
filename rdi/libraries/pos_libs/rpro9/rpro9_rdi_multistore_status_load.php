<?php
/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Retail Pro 9 SoStatus load class
 *
 * Handles the loading of the Multistatus data
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Load\Multistore\RPro9
 */
class rdi_multistore_status_load extends rdi_general 
{
    /**
     * Class Constructor
     *
     * @param rdi_catalog_load $db
     */
    public function rdi_multistore_status_load($db = '')
    {
        if ($db)
            $this->set_db($db);        
    }
       
    /**
     * Pre Load function
     * @global hook $hook_handler
     */
    public function pre_load()
    {
       global $hook_handler;       
        
        $hook_handler->call_hook("pos_so_status_pre_load");
    }
    /**
     * Post Load function
     * @global hook $hook_handler
     */
    public function post_load()    
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_so_status_post_load");
    }
    
    
    
    
}

?>


?>
