<?php


/**
 * Description of class
 *
 * @author PMBliss <pmbliss@retaildimensions.com>
 * @copyright (c) 2005-2015 Retail Dimensions Inc.
 * @package Core
 */
class rdi_upload extends rdi_general 
{    
    
   
    public $_upload_libraries = array(
                                        "catalog"       =>  "",
                                        "customers"     =>  "",
                                        "gift_reg"      =>  "",
                                        "item_images"   =>  "",
                                        "multistore"    =>  "",
                                        "prefs"         =>  "",
                                        "priceqty"      =>  "",
                                        "return"        =>  "",
                                        "sostatus"      =>  "",
                                        "styles"        =>  "",
                                        "giftcards"     =>  ""
        );
    
    /**
     * Class Constructor
     *
     * @param rdi_db $db
     */
    public function __construct($db = '')
    {
        parent::rdi_general($db);
    }
       
    /**
     * main function for each type of upload 
     * @global type $pos
     * @global type $db_lib
     * @param type $upload_type
     */
    public function upload($upload_type = '')
    {
        global $pos, $db_lib, $hook_handler;
        
        
        if(!isset($upload_type))
        {
			
            $db_lib->clean_in_staging_tables();
		
            $upload_type = array();
            
            foreach($this->_upload_libraries as $name => $upload)
            {
                if(strlen($upload) > 0)
                {
                    $upload_type[$name] = $upload;
                }
            }
            //allows for a custom load method outside the normal load.
            $hook_handler->call_hook("pos_import_post_load");
        }
        
        
        if(is_array($upload_type) && !empty($upload_type))
        {
            foreach($upload_type as $name => $type)
            {
                $this->echo_message("Uploading {$name}");
                $pos->get_processor($type)->load();
            }
        }        
        else
        {
            //if the library exists
            if(@isset($this->_upload_libraries[$upload_type]) && strlen($this->_upload_libraries[$upload_type]) > 0)
            {
                $pos->get_processor($this->_upload_libraries[$upload_type])->load();exit;
            }
        }
        
    }
}

?>
