<?php
/**
 * Class File
 */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * General Upload function
 *
 * 
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @copyright  2005-2011 Retail Dimensions Inc.
 * @version    1.0.0
 * 
 * @package    Core
 * @subpackage ExportImport
 */
class rdi_upload extends rdi_general {    
    
   
    /**
     * Class Constructor
     *
     * @param rdi_db $db
     */
    public function rdi_upload($db = '')
    {
        if ($db)
        {            
            /**
             * set the database object
             */
            $this->set_db($db);                    
        }
    }
    
    /**
     * main function for each type of upload
     * @global rdi_lib $pos
     * @global rdi_staging_db_lib $db_lib
     * @param string $upload_type If this is '', the entire list of xml types will try uploading.
     */
    public function upload($upload_type = '')
    {
        global $pos, $db_lib;
        
        $db_lib->clean_in_staging_tables();
        
        if($upload_type == '')
        {
            $upload_type[] = 'rdi_import_catalog_xml';
            $upload_type[] = 'rdi_import_customers_xml';
            $upload_type[] = 'rdi_import_styles_xml';
            $upload_type[] = 'rdi_import_sostatus_xml';
        }
        
        
        if(is_array($upload_type))
        {
            foreach($upload_type as $type)
            {
                $pos->get_processor($type)->load();
            }
        }
        
        else
        {
            $pos->get_processor($upload_type)->load();                
        }
        
    }
}

?>
