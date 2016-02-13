<?php
/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * XML Import Prefences Class
 *
 * Extends rdi_import_xml. Functions specific for bringing in the Prefences from RDice/Retail Pro.
 *
 * PHP version 5.3
 *
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * 
 * @package    Core\Import\Prefences\RPro9
 * @todo Import a Pref xml file from rdice/rpro
 */
class rdi_import_prefs_xml extends rdi_import_xml
{
    
    public function __construct($db = '')
    {
        parent::__construct($db);
        $this->hook_name = str_replace("rdi_", "", __CLASS__);
    }
	
    /**
     * Post load hook
     * pos_import_prefs_xml_post_load
     * 
     * @global type $hook_handler
     */
    public function post_load()
    {   
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_import_prefs_xml_post_load");
    }
    
    /**
     * Pre load hook
     * pos_import_prefs_xml_pre_load
     * 
     * @global type $hook_handler
     */
    public function pre_load()
    {   
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_import_prefs_xml_pre_load");
    }
    
    /**
     * Main load class
     */
    public function load()
    {
        
    }
}

?>
