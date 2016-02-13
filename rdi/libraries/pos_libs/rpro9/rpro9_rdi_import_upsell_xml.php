<?php
/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * XML Import upsell Class
 *
 * Extendes rdi_import_xml. Functions specific for bringing in the style data.
 *
 * PHP version 5.3
 *
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    1.0.0
 * 
 * @package    Core\Import\Upsell\RPro9
 */
class rdi_import_upsell_xml extends rdi_import_xml {

    /**
     * Class Variables
     */
    protected $style_sid;               // Stored for use in item nodes
    public $_style_sid = array();               // Stored for use in item nodes

    public function __construct($db = '')
    {
        parent::__construct($db);
        $this->hook_name = str_replace("rdi_", "", __CLASS__);
    }
    
    /**
     * Preload function with chaining     *
     * @name pos_import_styles_xml_pre_load
     * 
     * @global type $hook_handler
     * @return \rdi_import_styles_xml
     */
    public function pre_load()
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("{$this->hook_name}_pre_load");
        
        return $this;
    }
    
    /**
     * post load function with chaining
     * 
     * 
     * @global type $hook_handler
     * @return \rdi_import_styles_xml
     */
    public function post_load()
    {   
        global $hook_handler;
              
        
        $hook_handler->call_hook("{$this->hook_name}_post_load");
        
        return $this;
    }
    
    /**
     * Main load function
     */
    public function load()
    {
        $this->pre_load()->upload_upsells()->post_load();
    }
    
    /**
     * Upload Styles from specified folder and process xml into database staging tables.
     * 
     * @global type $inPath
     * @global type $rdi_path
     * @return \rdi_import_styles_xml
     */
    public function upload_upsells()
    {   
        global $inPath, $rdi_path;
        
        $dirHandle = @opendir($rdi_path . $inPath);
        if (!$dirHandle) 
        {
            /**
             *  if the directory cannot be opened skip upload
             */
            echo 'Cannot open the directory' . $rdi_path . $inPath;
        } 
        else 
        {
            /*while ($file = readdir($dirHandle))             
            {
                if ($file != "."     
                        && strpos(strtolower($file), "item") === 0
                        && substr(strtolower($file), -4) == '.xml'
                        && file_exists($inPath . '/' . $file)
                        && is_readable($inPath . '/' . $file)
                   ) */
            //This will load the items in order and replace as it goes.
            $files = array_reverse(glob($rdi_path . $inPath . DIRECTORY_SEPARATOR ."related_items_*.xml"));
                        
            if(!empty($files))
            {
                foreach($files as $file)           
                {                                                           
                    $this->load_from_file($file);
             
                    if($this->root)
                    {
                        $this->load_style_nodes();
                        
                        
                        $this->print_success();
                    }
                    
                    /**
                     * move the file to a backup folder
                     */
                    rename( $file, dirname($file) . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . basename($file));
                }
            }           
        }
        
        /**
         *  Close directory handle
         */
        closedir($dirHandle);
        
        return $this;
    }

    /**
     * Processes a style node.
     */
    public function load_style_nodes()
    {   
	
		if ($this->root->childNodes) 
        {        
            for($i=0;$i< $this->root->childNodes->item(0)->childNodes->length;$i++) 
            {
                $node = $this->root->childNodes->item(0)->childNodes->item($i);

                if($node->nodeName == "Style") 
                {             
                    $data = array();
                    
                    $data['style_sid'] = $this->get_value($node, "StyleSID");
                    
                    if(isset($this->_style_sid[$data['style_sid']]))
                    {
                        continue;
                    }
                    
                    $this->_style_sid[$data['style_sid']] = 1;
                    
                    $upsells = $this->get_value($node, "UpsellSID");
					
					$_upsells = strstr($upsells,",")?explode(",",$upsells):array($upsells);
					
					if(!empty($_upsells))
					{
						$order = 0;
						foreach($_upsells as $upsell)
						{
							$data['upsell_sid'] = $upsell;
							$data['order_no'] = $order;
							if($data['upsell_sid'] !== $data['style_sid'])
							{
								$this->db_connection->insertAr("rpro_in_upsell_item",$data);
								$order++;
							}
						}
					}
					
                }                
            }
        }
    }

}

?>
