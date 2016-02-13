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
 * @package    Core\Import\SOStatus\RPro9
 */
class rdi_import_gift_reg_xml extends rdi_import_xml
{
    public function __construct($db = '')
    {
        parent::__construct($db);
        $this->hook_name = str_replace("rdi_", "", __CLASS__);
    }
    /**
     * Main load function
     * 
     * @global type $inPath
     * @global type $rdi_path
     */
    public function load()
    {
         global $inPath, $rdi_path;
        
        $dirHandle = @opendir($rdi_path . $inPath);
        if (!$dirHandle) 
        {
            /**
             *  if the directory cannot be opened skip upload
             */
            trigger_error('Cannot open the directory' . $rdi_path . $inPath);
        } 
        else 
        {
            while ($file = readdir($dirHandle))             
            {
                if ($file != "."     
                        && strpos(strtolower($file), "gift_reg") === 0
                        && substr(strtolower($file), -4) == '.xml'
                        && file_exists($inPath . DIRECTORY_SEPARATOR . $file)
                        && is_readable($inPath . DIRECTORY_SEPARATOR . $file)
                   ) 
                {                                       
                    if (!$this->root)
                        $this->load_from_file($rdi_path . $inPath  . DIRECTORY_SEPARATOR . $file);
             
                    if($this->root)
                    {
                        $this->load_so_nodes();

                        $this->print_success();
                    }
                    
                    /**
                     * move the file to a backup folder
                     */
                    rename($rdi_path . $inPath  . DIRECTORY_SEPARATOR . $file, $rdi_path . $inPath  . DIRECTORY_SEPARATOR . 'archive'  . DIRECTORY_SEPARATOR . $file);
                }
            }           
        }
        
        /**
         *  Close directory handle
         */
        closedir($dirHandle);
    }
    
    /**
     * Processes a style node.
     */
    public function load_so_nodes()
    {        
		if ($this->root->childNodes)
        {
			var_dump($this->root->childNodes->item(0)->childNodes->length);
            for($i=0;$i< $this->root->childNodes->item(0)->childNodes->length ;$i++) 
            {
                $node = $this->root->childNodes->item(0)->childNodes->item($i);

                if($node->nodeName == "SO_HEADER") 
                {             
                    $data = array();
                    
                    $data['sid'] = $this->get_value($node,"so_sid");
                    $data['sbs_no'] = $this->get_value($node,"sbs_no");
                    $data['store_no'] = $this->get_value($node,"store_no");
                    $data['so_no'] = $this->get_value($node,"so_no");
                    $data['so_doc_no'] = $this->get_value($node,"so_doc_no");
                    $data['so_type'] = $this->get_value($node,"so_type");
                    $data['shipto_cust_sid'] = $this->get_value($node,"shipto_cust_sid");
                    $data['status'] = $this->get_value($node,"status");
                    $data['created_date'] = $this->get_value($node,"created_date");
                    $data['modified_date'] = $this->get_value($node,"modified_date");
                    $data['cancel_date'] = $this->get_value($node,"cancel_date");
                    $data['instruction1'] = $this->get_value($node,"instruction1");
                    $data['instruction2'] = $this->get_value($node,"instruction2");
                    $data['instruction3'] = $this->get_value($node,"instruction3");
                    $data['instruction4'] = $this->get_value($node,"instruction4");
                    $data['instruction5'] = $this->get_value($node,"instruction5");
                     
                    for($j=0;$j< $node->childNodes->item(0)->childNodes->length ;$j++)             
                    {
                        $child_node = $node->childNodes->item(0)->childNodes->item($j);
                        
                        if($child_node->nodeName == "SO_ITEM") 
                        {                                                  
                            $data['item_sid'] = $this->get_value($child_node,"item_sid");
                            $data['item_sid'] = $this->get_value($child_node,"item_sid");
                            $data['item_price'] = $this->get_value($child_node,"price") != null ? $this->get_value($child_node,"price") : 0;
                            $data['qty_shipped'] = $this->get_value($child_node,"qty") != null ? $this->get_value($child_node,"qty") : 0;
                            $data['item_ext_price'] = $this->get_value($child_node,"ext_price") != null ? $this->get_value($child_node,"ext_price") : 0;
                            $data['item_orig_price'] = $this->get_value($child_node,"orig_price") != null ? $this->get_value($child_node,"orig_price") : 0;
                            $data['item_tax'] = $this->get_value($child_node,"tax") != null ? $this->get_value($child_node,"tax") : 0;
							
                            $this->db_connection->insertAr($this->db_lib->get_table_name('in_gift_reg'), $data, true);
                        }
                    }
                }                
            }
        }
    }
}

?>
