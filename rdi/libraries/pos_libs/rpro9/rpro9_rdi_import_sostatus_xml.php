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
class rdi_import_sostatus_xml extends rdi_import_xml
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
                        && strpos(strtolower($file), "sostatus") === 0
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
            for($i=0;$i< $this->root->childNodes->length ;$i++) 
            {
                $node = $this->root->childNodes->item($i);

                if($node->nodeName == "SO") 
                {             
                    $data = array();
                    
                    $data['sid'] = $this->get_value($node,"so_sid");
                    $data['so_number'] = $this->get_value($node,"so_no");
                    $data['so_doc_no'] = $this->get_value($node,"so_doc_no");
                    $data['status'] = $this->get_value($node,"status");
                    $data['invc_no'] = $this->get_value($node,"invc_no");
                    $data['subtotal'] = $this->get_value($node,"subtotal");
                    $data['disc_amt'] = $this->get_value($node,"disc_amt");
                    $data['tax_total'] = $this->get_value($node,"tax");
                    $data['fee_type'] = $this->get_value($node,"fee_type");
                    $data['fee_amt'] = $this->get_value($node,"fee_amt");
                    $data['tender_amt'] = $this->get_value($node,"tender_amt");
                    $data['tracking_number'] = $this->get_value($node,"tracking_no");
                    $data['ship_date'] = $this->get_value($node,"ship_date");                                                               //$data['ship_provider'] = $this->get_value($node,"ship_provider");
                     
                    /**
                     * process import format to 4 decimals
                     */
                    $data['fee_type']   =  number_format(abs($data['fee_type']),4,".","");
                    $data['tender_amt'] =  number_format(abs($data['tender_amt']),4,".","");
                    $data['subtotal']   =  number_format(abs($data['subtotal']),4,".","");
                    $data['disc_amt']   =  number_format(abs($data['disc_amt']),4,".","");
                    
                    
                    for($j=0;$j< $node->childNodes->length ;$j++)             
                    {
                        $child_node = $node->childNodes->item($j);
                        
                        if($child_node->nodeName == "SO_ITEM") 
                        {                                                  
                            $data['item_sid'] = $this->get_value($child_node,"item_sid");
                            $data['item_price'] = $this->get_value($child_node,"price") != null ? $this->get_value($child_node,"price") : 0;
                            $data['qty_shipped'] = $this->get_value($child_node,"qty") != null ? $this->get_value($child_node,"qty") : 0;
                            $data['item_ext_price'] = $this->get_value($child_node,"ext_price") != null ? $this->get_value($child_node,"ext_price") : 0;
                            $data['item_orig_price'] = $this->get_value($child_node,"orig_price") != null ? $this->get_value($child_node,"orig_price") : 0;
                            $data['item_tax'] = $this->get_value($child_node,"tax") != null ? $this->get_value($child_node,"tax") : 0;
                                                                         
                            if ($this->db_connection) {                    
                                $this->db_connection->insertAr($this->db_lib->get_table_name('in_so'), $data, true);
                            }
                        }
                    }
                }                
            }
        }
    }
}

?>
