<?php

/*
 * 
 * 
 */

/**
 * Description of rpro9_rdi_import_return_xml
 *
 * @author PMBliss
 */
class rdi_import_return_xml extends rdi_import_xml
{
     /**
     * Class Constructor
     *
     * @param rdi_db $db
     */
    public function rdi_import_return_xml($db = '')
    {
        if ($db)
            $this->set_db($db);        
    }
    
    public function load()
    {
         global $inPath, $rdi_path;
        
        $dirHandle = @opendir($rdi_path . $inPath);
        if (!$dirHandle) 
        {
            // if the directory cannot be opened skip upload
            trigger_error('Cannot open the directory' . $rdi_path . $inPath);
        } 
        else 
        {
            while ($file = readdir($dirHandle))             
            {
                if ($file != "."     
                        && strpos(strtolower($file), "return") === 0
                        && substr(strtolower($file), -4) == '.xml'
                        && file_exists($inPath . '/' . $file)
                        && is_readable($inPath . '/' . $file)
                   ) 
                {                                       
                    if (!$this->root)
                        $this->load_from_file($rdi_path . $inPath . "/" . $file);
             
                    if($this->root)
                    {
                        $this->load_so_nodes();

                        $this->print_success();
                    }
                    
                    //move the file to a backup folder
                    rename($rdi_path . $inPath . '/' . $file, $rdi_path . $inPath . '/archive/' . $file);
                }
            }           
        }
        
        // Close directory handle
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

                if($node->nodeName == "RETURN") 
                {             
                    $data = array();
                    
                    $data['sid'] = $this->get_value($node,"so_sid");
                    $data['so_number'] = $this->get_value($node,"so_no");
                    $data['so_doc_no'] = $this->get_value($node,"so_doc_no");
                    $data['status'] = $this->get_value($node,"status");
                    $data['invc_no'] = $this->get_value($node,"invc_no");
                    $data['subtotal_refund'] = $this->get_value($node,"subtotal_refund");
                    $data['items_refund'] = $this->get_value($node,"items_refund_flag");
                    $data['return_date'] = $this->get_value($node,"return_date");
                    $data['comment'] = $this->get_value($node,"comment");  
					$data['record_type'] = 'Refund';
                    
                    for($j=0;$j< $node->childNodes->length ;$j++)             
                    {
                        $child_node = $node->childNodes->item($j);
                        
                        if($child_node->nodeName == "SO_ITEM") 
                        {       
							$data = array();
							$data['so_number'] = $this->get_value($node,"so_no");
							$data['so_doc_no'] = $this->get_value($node,"so_doc_no");
							$data['invc_no'] = $this->get_value($node,"invc_no");
							$data['sid'] = $this->get_value($node,"so_sid");
                            $data['item_sid'] = $this->get_value($child_node,"item_sid");
                            $data['item_alu'] = $this->get_value($child_node,"item_alu");
                            $data['item_num'] = $this->get_value($child_node,"item_num");
                            $data['item_price'] = $this->get_value($child_node,"price") != null ? $this->get_value($child_node,"price") : 0;
                            $data['item_qty'] = $this->get_value($child_node,"qty") != null ? $this->get_value($child_node,"qty") : 0;
                            $data['item_qty_refunded'] = $this->get_value($child_node,"qty_refunded") != null ? $this->get_value($child_node,"qty_refunded") : 0;
                            $data['item_ext_price'] = $this->get_value($child_node,"ext_price") != null ? $this->get_value($child_node,"ext_price") : 0;
                            $data['item_orig_price'] = $this->get_value($child_node,"orig_price") != null ? $this->get_value($child_node,"orig_price") : 0;
                            $data['item_tax'] = $this->get_value($child_node,"tax") != null ? $this->get_value($child_node,"tax") : 0;
							$data['record_type'] = 'item';
								
                            if ($this->db_connection) {                    
                                $this->db_connection->insertAr('rpro_in_return', $data, true);
                            }
                        }
                    }
                }                
            }
        }
    }
}

?>
