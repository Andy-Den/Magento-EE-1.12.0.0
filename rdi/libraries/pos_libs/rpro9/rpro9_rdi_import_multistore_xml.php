<?php

/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Retail Pro 9 SoStatus load class
 *
 * Handles the loading of the Multistatus data
 *
 * PHP version 5.3
 *
 * @author     Paul Bliss
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    2.0.0
 * @package    Core\Import\Multistore\RPro9
 */
class rdi_import_multistore_xml extends rdi_import_xml {

    public $_item_sid = array();
    public $xml_reader;

    public function __construct($db = '')
    {
        parent::__construct($db);
        $this->hook_name = str_replace("rdi_", "", __CLASS__);
    }

    public function pre_load()
    {
        global $hook_handler;

        $hook_handler->call_hook($this->hook_name . "_" . __FUNCTION__);

        $count = $this->db_connection->cell("SELECT COUNT(*) c FROM {$this->db_lib->get_table_name('in_store_qty')}", "c");
        //init the _item_no array if there are already values in the staging table.
        if ($count > '0')
        {
            $this->_item_sid = $this->db_connection->cells("SELECT DISTINCT item_sid FROM {$this->db_lib->get_table_name('in_store_qty')}", "item_sid");
        }

        return $this;
    }

    public function post_load()
    {
        global $hook_handler;

        $hook_handler->call_hook($this->hook_name . "_" . __FUNCTION__);

        return $this;
    }

    public function load()
    {
        $this->pre_load()->get_multi_inventory_files()->post_load();

        return $this;
    }

    public function get_multi_inventory_files()
    {
        global $inPath, $rdi_path;

        $this->fileList = array();

        global $inPath, $rdi_path;

        $files = array_reverse(glob($rdi_path . $inPath . "/StoreQty*.xml"));

        foreach ($files as $file)
        {
            if (filesize($file) > 0)
            {
                $this->fileList[] = $file;
            }
        }

        $this->load_files();

        return $this;
    }

    public function load_files()
    {
        global $inPath;

        if (is_array($this->fileList) && !empty($this->fileList))
        {
            $this->xml_reader = new XMLReader;

            foreach ($this->fileList as $file)
            {
                // make sure the file exists
                if (file_exists($file))
                {
                    $this->load_file_xml($file);
                }
            }
        }
    }

    public function load_file_xml($file)
    {
        //open the xml
        $this->xml_reader->open($file);

        $doc = new DOMDocument;

        // move to the first <Store /> node
        while ($this->xml_reader->read() && $this->xml_reader->name !== 'Store');

        while ($this->xml_reader->name === 'Store')
        {
            $expanded_node = $this->xml_reader->expand();
            $store_node = simplexml_import_dom($doc->importNode($expanded_node, true));

            $store = array();
            $store['store_code'] = $store_node['StoreCode'];
            $store['store_name'] = $store_node['StoreName'];
            $store['address1'] = $store_node['Addr1'];
            $store['address2'] = $store_node['Addr2'];
            $store['zip'] = $store_node['Zip'];

            $this->db_connection->insertAr($this->db_lib->get_table_name('in_store'), $store, false);

            //$this->_var_dump($store_node->children());
            $_styles = $store_node->children();

            if (!empty($_styles))
            {
                foreach ($_styles as $style_name => $style)
                {
                    if ($style_name == 'Style')
                    {
                        $data = array('store_code' => $store['store_code'], 'style_sid' => $style['StyleSID']);

                        $_items = $style->children();

                        if (!empty($_items))
                        {
                            foreach ($_items as $item_name => $item)
                            {
                                if ($item_name == 'Item')
                                {
                                    if (in_array($item['ItemSID'], $this->_item_sid))
                                    {
                                        continue;
                                    }

                                    @$this->_item_sid[$item['ItemSID']] = 1;

                                    $data['item_sid'] = $item['ItemSID'];
                                    $data['quantity'] = $item['Quantity'];
                                    $data['so_committed'] = $item['SOCommitted'];

                                    $this->db_connection->insertAr($this->db_lib->get_table_name('in_store_qty'), $data, false);
                                }
                            }
                        }
                    }
                }
            }

            $this->xml_reader->next('Store');
        }
        rename($file, dirname($file) . '/archive/' . basename($file));
    }

}
?>
