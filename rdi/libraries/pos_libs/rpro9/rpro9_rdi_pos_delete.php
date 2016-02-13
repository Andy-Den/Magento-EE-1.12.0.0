<?php
/**
 * Class File
 */

/**
 * Retail Pro 9 Delete Products Class
 * Marks a product to be deleted, deletes a product and optionally tells the customer about the incoming deletion.
 *
 * @author PMBliss<pmbliss@retaildimensions.com>
 * @copyright Retail Dimensions Inc. 2005-2014
 * 
 * @package    Core\Delete\RPro9
 */
class rdi_pos_delete extends rdi_general
{
    
    /**
     *
     * @var type 
     */
    private $hook_name;
    
    /**
     * Class Constructor
     *
     * @param rdi_pos_common $db
     */
    public function rdi_pos_delete($db = '')
    {
        if ($db)
            $this->set_db($db);    
        
        $this->hook_name = str_replace("rdi_", "", __CLASS__);
    }
    
    /**
     * Pre load function
     * @global rdi_hook $hook_handler pos_delete_pre_load
    */
    public function pre_load()
    {
        global $hook_handler;       
        
        //@hook pos_delete_pre_load
        $hook_handler->call_hook('pos_delete_pre_load');
    }
    
    /**
     * Post load function
     * @global rdi_hook $hook_handler
    */
    public function post_load()
    {
        global $hook_handler;       
        
        //@hook pos_delete_post_load
        $hook_handler->call_hook('pos_delete_post_load');
    }
       
}

?>
