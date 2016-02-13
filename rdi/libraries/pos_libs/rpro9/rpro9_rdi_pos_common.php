<?php
/**
 * Class File
 */

/**
 * RPro9 POS Common Class
 * Common funcs for the cart pos, these will be called from the outside, so should be pretty generic, but may also be specific
 *
 * @author PBliss <pmbliss@retaildimensions.com
 * 
 * @package    Core\Common\RPro9
 */
class rdi_pos_common extends rdi_general
{
    /**
     * Class Constructor
     *
     * @param rdi_pos_common $db
     */
    public function rdi_pos_common($db = '')
    {
        if ($db)
            $this->set_db($db);        
    }
    
    /**
    *Pre load Function
     * @hook pos_common_pre_load
    */
    public function pre_load()
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_common_pre_load");
    }
    
    /**
     * Pre load Function for al
     * @hook pos_common_post_load
    */
    public function post_load()
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_common_post_load");
    }
    
    /**
     * Pre Export Function
     * @hook pos_common_post_load
     * @hook pos_common_pre_export
     */
    public function pre_export()
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_common_pre_export");
    }
    
    /**
     * Post Export Function
     * @hook pos_common_post_export
     */
    public function post_export()
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_common_post_export");
    }
    
    /**
     * Not used
     */
    public function validate_pos_field_mapping_existance()
    {
        
    }
    
    /**
     * Not used
     */
     public function validate_pos_field_mapping_warnings()
    {
        /**
         * these are a good idea to have this way but not going to be completely required
         */
        
        
    }
    /**
     * Not used
     */
    public function validate_pos_field_mapping_required()
    {
        /**
         * check that the fields that are essential are mapped right
         */
        
        
    }
    /**
     * Not used
     */
    public function validate_pos_settings()
    {
        
    }
    
    /**
     * a regular expresion to be ran on the image file to determine if it is a thumbnail
     * return the related id sans the thumbnail marking
     */
    public function thumbnail_descriptor()
    {
        
    }
}

?>
