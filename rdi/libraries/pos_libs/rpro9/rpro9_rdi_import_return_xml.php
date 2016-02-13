<?php

/*
 *
 *
 */

/**
 * Description of rpro9_rdi_import_refund_xml
 *
 * @author PBliss
 */
class rdi_import_return_xml extends rdi_import_xml {

    /**
     * Class Constructor
     *
     * @param rdi_db $db
     */
    public function __construct($db = '')
    {
        parent::__construct($db);
        $this->hook_name = str_replace("rdi_", "", __CLASS__);
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
                if ($file != "." && strpos(strtolower($file), "returns") === 0 
                        && substr(strtolower($file), -4) == '.xml' 
                        && file_exists($inPath  . DIRECTORY_SEPARATOR . $file) && is_readable($inPath  . DIRECTORY_SEPARATOR . $file)
                )
                {
                    if (!$this->root)
                        $this->load_from_file($rdi_path . $inPath  . DIRECTORY_SEPARATOR . $file);

                    if ($this->root)
                    {
                        $this->load_so_nodes();

                        $this->print_success();
                    }

                    //move the file to a backup folder
                    rename($rdi_path . $inPath  . DIRECTORY_SEPARATOR . $file, $rdi_path . $inPath  . DIRECTORY_SEPARATOR . 'archive'  . DIRECTORY_SEPARATOR . $file);
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
            for ($i = 0; $i < $this->root->childNodes->item(0)->childNodes->length; $i++)
            {
                $node = $this->root->childNodes->item(0)->childNodes->item($i);

                if ($node->nodeName == "RETURN")
                {

                    $data = array();

                    $data['so_number'] = $this->get_value($node, "so_no");
                    $data['so_doc_no'] = $this->get_value($node, "so_doc_no");
                    $data['invc_sid'] = $this->get_value($node, "invc_sid");
                    $data['invc_no'] = $this->get_value($node, "invc_no");
                    $data['refund_date'] = $this->get_value($node, "created_date");
                    $data['shipping'] = $this->get_value($node, "shipping");
                    $data['disc_amt'] = $this->get_value($node, "disc_amt");
                    $data['comment1'] = $this->get_value($node, "comment1");
                    $data['comment2'] = $this->get_value($node, "comment2");
                    $data['record_type'] = 'Return';

                    $this->db_connection->insertAr($this->db_lib->get_table_name('in_return'), $data, true);

                    for ($j = 0; $j < $node->childNodes->item(0)->childNodes->length; $j++)
                    {
                        $child_node = $node->childNodes->item(0)->childNodes->item($j);

                        if ($child_node->nodeName == "INVC_ITEM")
                        {
                            $data = array();
                            $data['so_number'] = $this->get_value($node, "so_no");
                            $data['so_doc_no'] = $this->get_value($node, "so_doc_no");
                            $data['invc_no'] = $this->get_value($node, "invc_no");
                            $data['invc_sid'] = $this->get_value($node, "invc_sid");

                            $data['item_sid'] = $this->get_value($child_node, "item_sid");
                            $data['item_alu'] = $this->get_value($child_node, "alu");
                            $data['item_price'] = $this->get_value($child_node, "price") != null ? $this->get_value($child_node, "price") : 0;
                            $data['item_qty_ordered'] = $this->get_value($child_node, "qty") != null ? $this->get_value($child_node, "qty_ordered") : 0;
                            $data['item_qty_returned'] = $this->get_value($child_node, "qty_returned") != null ? $this->get_value($child_node, "qty_returned") : 0;
                            $data['record_type'] = 'Item';

                            if ($this->db_connection)
                            {
                                $this->db_connection->insertAr($this->db_lib->get_table_name('in_return'), $data, true);
                            }
                        }
                    }
                }
            }
        }
    }

}

?>
