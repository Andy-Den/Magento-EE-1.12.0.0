<?php

/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * XML Import Styles Class
 *
 * Extendes rdi_import_xml. Functions specific for bringing in the style data.
 *
 * PHP version 5.3
 *
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * 
 * @package    Core\Import\PriceQty\RPro9
 */
class rdi_import_priceqty_xml extends rdi_import_xml {

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
     * Preload function with chaining     *
     * @name pos_import_styles_xml_pre_load
     * 
     * @global type $hook_handler
     * @return \rdi_import_styles_xml
     */
    public function pre_load()
    {
        global $hook_handler;

        $hook_handler->call_hook("pos_import_priceqty_xml_pre_load");

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


        $hook_handler->call_hook("pos_import_priceqty_xml_post_load");

        return $this;
    }

    /**
     * Main load function
     */
    public function load()
    {
        $this->pre_load()->upload_styles()->post_load();
    }

    /**
     * Upload Styles from specified folder and process xml into database staging tables.
     * 
     * @global type $inPath
     * @global type $rdi_path
     * @return \rdi_import_styles_xml
     */
    public function upload_styles()
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
            while ($file = readdir($dirHandle))
            {
                if ($file != "." && strpos(strtolower($file), "priceqty") === 0 && substr(strtolower($file), -4) == '.xml' && file_exists($inPath . '/' . $file) && is_readable($inPath . '/' . $file)
                )
                {
                    $this->load_from_file($rdi_path . $inPath . DIRECTORY_SEPARATOR . $file);


                    if ($this->root)
                    {
                        $this->load_style_nodes();


                        $this->print_success();
                    }

                    /**
                     * move the file to a backup folder
                     */
                    rename($rdi_path . $inPath . DIRECTORY_SEPARATOR . $file, $rdi_path . $inPath . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . $file);
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

            for ($i = 0; $i < $this->root->childNodes->item(0)->childNodes->length; $i++)
            {
                $node = $this->root->childNodes->item(0)->childNodes->item($i);

                if ($node->nodeName == "Style")
                {
                    $data = array();

                    $data['style_sid'] = $this->get_value($node, "StyleSID");

                    for ($j = 0; $j < $node->childNodes->item(0)->childNodes->length; $j++)
                    {
                        $child_node = $node->childNodes->item(0)->childNodes->item($j);

                        if ($child_node->nodeName == "Item")
                        {
                            $data['style_sid'] = $this->get_value($node, "StyleSID");
                            $data['item_sid'] = $this->get_value($child_node, "ItemSID");

                            $data['cost'] = $this->get_value($child_node, "Cost");
                            $data['price'] = $this->get_value($child_node, "Price");
                            $data['markdown_price'] = $this->get_value($child_node, "MarkdownPrice");
                            $data['reg_price'] = $this->get_value($child_node, "RegPrice");
                            $data['sale_price'] = $this->get_value($child_node, "SalePrice");
                            $data['msrp_price'] = $this->get_value($child_node, "MSRPPrice");
                            $data['wholesale_price'] = $this->get_value($child_node, "WholesalePrice");
                            $data['quantity'] = $this->get_value($child_node, "Quantity");
                            $data['so_committed'] = $this->get_value($child_node, "SOCommitted");
                            $data['open_po'] = $this->get_value($child_node, "OpenPO");

                            if ($this->db_connection)
                            {
                                $this->db_connection->insertAr($this->db_lib->get_table_name('in_priceqty'), $data, true);
                            }
                        }
                    }
                }
            }
        }
    }

}

?>
