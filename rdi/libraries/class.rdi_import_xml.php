<?php
/**
 * Class File
 */

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * XML Import Class
 *
 * General class for xml import handling. Classes extended from this one
 * will handle the specific loading for each data type.
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Import
 */
class rdi_import_xml extends rdi_general {

    /**
     * Class Variables
     */
    protected $xml;                     // Stores the actual xml
    protected $xmldoc;                  // Stores an xml element
    protected $root;                  // Stores an xml element
    protected $files;                  // Stores an array of files to be loaded
    public $db_lib;
	
    /**
     * Class Constructor
     *
     * @param rdi_db $db
     */
    public function __construct($db = '')
    {    
        global $db_lib;
        
        $this->rdi_general($db);
        $this->db_lib = $db_lib;
		$this->set_website_in_path();
    }

    /**
     * Method for finding and assigning http raw post data to the xml variable
     */
    public function load_raw($log = false)
    {     
        // patch for php version 5.2.2
//        if (phpversion() == '5.2.2') {
//            $GLOBALS['HTTP_RAW_POST_DATA'] = file_get_contents('php://input');
//        }

        if (!isset($HTTP_RAW_POST_DATA))
            $this->xml = $HTTP_RAW_POST_DATA = file_get_contents("php://input"); 
        else
            $this->xml = $GLOBALS['HTTP_RAW_POST_DATA']; 
        
        //might need to add this later.
        //$this->xml = iconv("utf-8", "ASCII//IGNORE", $this->xml);
        if($log)
        {
            //$this->log_xml($this->xml);            
        }
    }

    /**
     * Method for loading raw data into an xml element.
     * @param boolean $log
     */
    public function load_raw_node($log = false)
    {    
        if (!$this->xml)
            $this->load_raw($log);

        if ($this->xml) {
            $this->xmldoc = new DOMDocument();
            $this->xmldoc->loadXML($this->xml);
            $this->root = $this->xmldoc->documentElement;
        }
    }  
    
    /**
     * method for loading the xml from a file
     * @param type $file
     */
    public function load_from_file($file)
    {        
        if(file_exists($file) && is_readable($file))
        {
			$this->echo_message("Uploading ".basename($file));
            $handle = fopen($file, "r");
            $this->xml = fread($handle, filesize($file));
            fclose($handle);
        
            $this->xmldoc = new DOMDocument();
            $this->xmldoc->loadXML($this->xml);
            $this->root = $this->xmldoc->documentElement;        
        }
    }  
     
    /**
     * Gets an attribute value from an xml node object.
     * @global rdi_helper $helper_funcs
     * @global rdi_debug $debug
     * @param element $node
     * @param string $attribute
     * @return XML_attribute
     */
    public function get_value($node, $attribute) 
    {
        global $helper_funcs, $debug, $encoding;
        
	$value = $node->getAttribute($attribute);
        
        //bad debug statement
        //$debug->write_message("class.rdi_import_xml.php", "get_value", $value);
        
        if($value == "")
        {
            $value = "NULL";
        }
        else
        {
            //$value = strip_tags($value);
            
            //$value = $helper_funcs->remove_non_ascii($value);
            $value = $encoding->toLatin1($value);
                     
            /**
             * remove all <!--[if ...]>....<![endif]--> comments, such as the office mso ones
             */
            $value = preg_replace('/<!--\[if[^\]]*]>.*?<!\[endif\]-->/i', '', $value);
            
            /**
             * more explicit mso removal
             */
            $value = preg_replace('(mso-[a-z-: ]+; )i', '', $value);
            
            $value = str_replace("class=MsoNormal", "", $value);
            
            //$value = $helper_funcs->quote($value);        
        }
	//bad debug statement in a production invironment        
        //$debug->write_message("class.rdi_import_xml.php", "get_value2", "");
	return $value;
    }
    
    /**
     * writes an array of data to a type json file with extension
     * @param string $type name of the file
     * @param array $data An array of data.
     */
    public function write_json($type,$data = array())
    {
        if(!isset($this->data_out))
        {
            $this->data_out = $data;
        }
        
        $ext = 1;
        $ext_check = true;
        
        while($ext_check)
        {
            $fname = "in/json/{$type}_" . date('Ymdhi') . "_{$ext}.json";
        
            $fileexists = file_exists($fname)?true:false;
        
            $ext_check = $fileexists && filesize($fname) > 5000000 ? true : false;
            $ext++;
        }
        $jsonData = json_encode($this->data_out);
        
        $fp = fopen($fname, "c");
       
        if ($fp) 
        {   
            if($fileexists)
            {
                fseek($fp, -1, SEEK_END);
                fwrite($fp,",");
                
                $jsonData = substr( trim($jsonData), 1 ,-1) . "]";
                fwrite($fp, $jsonData);
                
            }
            else 
            {
                file_put_contents($fname, $jsonData);
            }
            fclose($fp); 
        }
    }
    
    /**
     * Quotes of null for inserting into a database.
     * @param string $value
     * @return string
     */
    public function quote_or_null($value)
    {
        $value = $this->db_connection->clean($value);
        
        return $value==''?"NULL":"'{$value}'";        
    }
    
    /**
     * Returns a glob of a directory. Might be helpful to do more later.
     * @param type $search
     */
    public function get_files($search)
    {
        $this->files = glob($search);
    }
	
	public function set_website_in_path()
	{
		global $cart, $inPath, $use_multisite;
		
		if(isset($use_multisite) && $use_multisite == '1')
		{
			//this sets the current default_site and root_category_id
			$cart->get_processor("rdi_cart_common")->set_current_website();
			
			$var = "inPath{$GLOBALS['default_site']}";
			$inPath = $GLOBALS[$var];
		}
	}    
    
}

?>
