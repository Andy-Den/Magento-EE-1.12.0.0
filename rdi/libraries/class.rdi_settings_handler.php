<?php
/**
 * Class File
 */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Settings handler class
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Settings
 */
class rdi_settings_handler extends rdi_general {
       
    
    /**
     * Get the Setting Value
     * @param string $key
     * @return string The setting stored for this key
     */
    public function get_setting($key)
    {
        return $this->db_connection->cell("Select value from {$this->rdiprefix}rdi_settings where setting = '{$key}'", 'value');
    }
    
    /**
     * Set a setting. Not used.
     * @param string $key
     * @param string $value
     */
    public function set_setting($key, $value)
    {
        
    }
    
    /**
     * Load all the setting used when applying a second database with the same code base.
     * @global rdi_db $allow_alt_database
     * @global key_for_settings $alt_db_id
     */
    public function load_all()
    {
        global $allow_alt_database, $alt_db_id;
        
        $settings = $this->db_connection->rows("Select * from {$this->rdi_prefix}rdi_settings");
       
        if(is_array(($settings)))
        {
            foreach($settings as $setting)
            {   
                if($allow_alt_database == 1 && isset($alt_db_id))
                {
                    //forces an override if we are using an alt db, so the url paramter overrides wont work
                    $GLOBALS[$setting['setting']] = $setting['value'];   
                }                
                //this will let us override the setting getting set here
                else if(!isset($GLOBALS[$setting['setting']]))
                    $GLOBALS[$setting['setting']] = $setting['value'];     
                
            }
        }
    }
}

?>
