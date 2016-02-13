<?php
/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * XML Import Cattree Class
 *
 * Extends rdi_import_xml. Functions specific for bringing in the cattree data.
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     Tom Martin <tmartin@retaildimensions.com>
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * 
 * @package    Core\Import\Catalog\RPro9
 */
class rdi_import_catalog_xml extends rdi_import_xml {

    /**
     * Class Variables
     */
    protected $style_sid;               // Stored for use in item nodes

	
    public function __construct($db = '')
    {
        parent::__construct($db);
        $this->hook_name = str_replace("rdi_", "", __CLASS__);
    }
	
    /**
     * Main Class load function
     * 
     * @global type $inPath
     * @global type $rdi_path
     */
    public function load()
    {   
        global $inPath, $rdi_path, $rdi_prefix;
                     //$this->_var_dump($rdi_prefix);exit;               
        //$dirHandle = @opendir($inPath);
//        if (!$dirHandle) 
//        {
//            // if the directory cannot be opened skip upload
//            trigger_error('Cannot open the directory' . $inPath);
//        } 
//        else 
//        {                
            $latest_ctime = 0;
            $file = '';    
            $files = array();

            $d = dir($rdi_path . $inPath);
            while (false !== ($entry = $d->read())) 
            {
                $filepath = $rdi_path . $inPath . DIRECTORY_SEPARATOR . "{$entry}";
           
                if (is_file($filepath) 
                    && $file != "."     
                    && filectime($filepath) > $latest_ctime
                    && strpos(basename(strtolower($filepath)), "cat") === 0
                    && substr(strtolower($filepath), -4) == '.xml'
                    && file_exists($filepath)
                    && is_readable($filepath))
                {                            
                  $latest_ctime = filectime($filepath);
                  $file = $entry;
                  $files[] = $entry;
                }
            }            
                 
            if ($file != "")                
            {                                                
                if (!$this->root)
                    $this->load_from_file($inPath . DIRECTORY_SEPARATOR . $file);

                if($this->root)
                {
                    $this->load_catalog_node($this->root, 1);

                    $this->print_success();
                }
                
            } 
            
            
            //moves all the category files to the archive
            foreach($files as $f)                        
            {
                rename($rdi_path . $inPath  . DIRECTORY_SEPARATOR . $f, $rdi_path . $inPath  . DIRECTORY_SEPARATOR .  'archive'  . DIRECTORY_SEPARATOR . $f);
            }            
       // }
        
        // Close directory handle
        //closedir($dirHandle);
    }
    
    
    /**
     * Processes a catalog node.
     * @param string $n
     * @param string $ix
     * @param string $parent
     */
    public function load_catalog_node($n, $ix, $parent = '')
    {                
        if ($n->nodeName == "category") 
        {        
            $data = array();

            $data['catalog_id'] = $this->get_value($n,"catalog_id");
            $data['site_id'] = 0;
            $data['parent_id'] = $this->get_value($n,"parent_id");                
            $data['category'] = $this->get_value($n,"name");                
            $data['description'] = $this->get_value($n,"description");                
            $data['sort_order'] = $this->get_value($n,"sort_order");                
            $data['meta_description'] = $this->get_value($n,"meta_description");                
            $data['meta_keywords'] = $this->get_value($n,"meta_keywords");                
            $data['meta_title'] = $this->get_value($n,"meta_title");                
            $data['image_path'] = '';
            $data['level'] = $this->get_value($n,"level"); 
            $unpublished  = $this->get_value($n,"unpublished");     
            $data['published'] = ($unpublish == 'True'?0:1);            
                        
            $this->db_connection->insertAr($this->db_lib->get_table_name("in_catalog"), $data, false, array(), true);
        }        
        else if($n->nodeName == "product")
        {            
            $product_data = array();

            $product_data['catalog_id'] = $parent->getAttribute("catalog_id");
            $product_data['style_sid'] = $this->get_value($n,"style_sid");
            $product_data['sort_order'] = $this->get_value($n,"sort_order");
            $product_data['date_added'] = $this->get_value($n,"date_added");
            
            if(strlen($product_data['catalog_id']) == 0)
            {
                $product_data['catalog_id'] = 0;
            }
                               
            $this->db_connection->insertAr($this->db_lib->get_table_name("in_catalog_product"), $product_data, false, array(), true);
        }
	
	$nodes = $n->childNodes;
	
	if ($nodes) {
		for($i=0;$i< $nodes->length ;$i++) {
			$node = $nodes->item($i);
			$this->load_catalog_node($node, $ix+1, $n);
		}
	}       
    }

}

?>
