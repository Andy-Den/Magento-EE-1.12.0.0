<?php

/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * XML Import Customer Class
 *
 * Extends rdi_import_xml. Functions specific for bringing in the Customer data.
 *
 * PHP version 5.3
 *
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 *
 * @package    Core\Import\Customers\RPro9
 */
class rdi_import_customers_xml extends rdi_import_xml {

    public $has_customers = false;

	public function __construct($db = '')
    {
        parent::__construct($db);
        $this->hook_name = str_replace("rdi_", "", __CLASS__);
    }
	
    /**
     * Main Class function
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
            echo 'Cannot open the directory' . $rdi_path . $inPath;
        }
        else
        {
            while ($file = readdir($dirHandle))
            {
                if ($file != "." && strpos(strtolower($file), "cust") === 0 && substr(strtolower($file), -4) == '.xml' && file_exists($inPath . DIRECTORY_SEPARATOR . $file) && is_readable($inPath . DIRECTORY_SEPARATOR . $file)
                )
                {
                    $this->load_from_file($rdi_path . $inPath . DIRECTORY_SEPARATOR . $file);

                    if ($this->root)
                    {
                        $this->load_customer_nodes();

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

        $this->post_load();
    }

    /**
     * Processes a customer node.
     */
    public function load_customer_nodes()
    {
        if ($this->root->childNodes)
        {
            for ($i = 0; $i < $this->root->childNodes->length; $i++)
            {
                $node = $this->root->childNodes->item($i);

                if ($node->nodeName == "Customer")
                {
                    $this->has_customers = true;
                    $data = array();

                    $data['fldCustSID'] = $this->get_value($node, "fldCustSID");
                    //$data['fldTitle'] = $this->get_value($node, "fldTitle");
                    $data['fldFName'] = $this->get_value($node, "fldFName");
                    $data['fldLName'] = $this->get_value($node, "fldLName");
                    //$data['fldCompany'] = $this->get_value($node, "fldCompany");
                    //$data['fldAddr1'] = $this->get_value($node, "fldAddr1");
                    //$data['fldAddr2'] = $this->get_value($node, "fldAddr2");
                    //$data['fldAddr3'] = $this->get_value($node, "fldAddr3");
                    //$data['fldZIP'] = $this->get_value($node, "fldZIP");
                    //$data['fldPhone1'] = $this->get_value($node, "fldPhone1");
                    //$data['fldPhone2'] = $this->get_value($node, "fldPhone2");
                    $data['fldCustID'] = $this->get_value($node, "fldCustID");
                    //$data['web_cust_sid'] = $this->get_value($node, "web_cust_sid");
                    $data['email'] = $this->get_value($node, "fldEmail");
                    //$data['fldPrcLv1'] = $this->get_value($node, "fldPrcLv1");		  *Note: is that a 1 or L in variable name?
                    //$data['fldPrcLv1_i'] = $this->get_value($node, "fldPhone1_i");

                    if ($this->db_connection)
                    {
                        $this->db_connection->insertAr($this->db_lib->get_table_name('in_customers'), $data, true);
                    }
                }
            }
        }
    }

    /**
     * Post load hook
     * pos_import_customers_xml_post_load
     *
     * @global type $hook_handler
     */
    public function post_load()
    {
        global $hook_handler;

        if (!$this->db_connection->column_exists('rdi_customer_email', 'email'))
        {
            $this->db_connection->exec("CREATE TABLE rdi_customer_email (UNIQUE(email)) AS
                        SELECT fldcustsid, email, fldcustid FROM {$this->db_lib->get_table_name('in_customers')} LIMIT 0");
        }

        if ($this->has_customers)
        {
            //insert new customers into the rdi_customer_email
            $this->db_connection->exec("INSERT INTO rdi_customer_email (related_id, email)
										SELECT c.fldcustsid, c.email FROM {$this->db_lib->get_table_name('in_customers')} c
										LEFT JOIN rdi_customer_email e
										on e.email = c.email
										WHERE e.email IS NULL
										GROUP by c.email");
        }

        $hook_handler->call_hook("pos_import_customers_xml_post_load");
    }

    /**
     * Pre load hook
     * pos_import_customers_xml_pre_load
     *
     * @global type $hook_handler
     */
    public function pre_load()
    {
        global $hook_handler;

        $hook_handler->call_hook("pos_import_customers_xml_pre_load");
    }

}

?>