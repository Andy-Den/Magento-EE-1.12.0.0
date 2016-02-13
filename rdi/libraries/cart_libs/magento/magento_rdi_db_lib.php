<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Cart databse functions
 *
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package Core\StagingDB\Magento
 */
class rdi_db_lib {

    private $adminname;
    private $prefix;

    /**
     * Builds a database connection from the cart settings
     *
     * @param rdi_db $db
     * @return boolean
     */
    public function get_db_obj()
    {
        global $rdi_path;

        require_once $rdi_path . "libraries/class.rdi_db.php";

        /**
         * Get the connection info from magento settings
         * If we are symlinked to the current the magento app directory. 
         * We need to figure out the full path for the symlink and the use dirname, 
         * rather than ../ because this will return the real path and not the sym path.
         */
        $xmlFile = $rdi_path . '../app/etc/local.xml';

        if (!file_exists($xmlFile))
        {
            //one special case when calling tools
            if (strstr($_SERVER['SCRIPT_FILENAME'], 'libraries'))
            {
                list($dirname, $other) = explode("/libraries", $_SERVER['SCRIPT_FILENAME']);
                $dirname = dirname($dirname);
            }
            elseif (strstr($_SERVER['SCRIPT_FILENAME'], 'add_ons'))
            {
                list($dirname, $other) = explode("/add_ons", $_SERVER['SCRIPT_FILENAME']);
                $dirname = dirname($dirname);
            }
            else
            {
                $dirname = dirname(dirname($_SERVER['SCRIPT_FILENAME']));
            }

            $xmlFile = $dirname . "/app/etc/local.xml";
        }

        if (!file_exists($xmlFile))
        {
            echo "<h1>COULD NOT CONNECT TO MAGENTO for database access. {$xmlFile}</h1>";
            exit;
        }

        $xml = simplexml_load_file($xmlFile, NULL, LIBXML_NOCDATA);

        $dbHost = $xml->global->resources->default_setup->connection->host;
        $dbUser = $xml->global->resources->default_setup->connection->username;
        $dbPasswd = $xml->global->resources->default_setup->connection->password;
        $dbDatabase = $xml->global->resources->default_setup->connection->dbname;
        $dbPrefix = $xml->global->resources->db->table_prefix;
        $this->prefix = $xml->global->resources->db->table_prefix;
        $adminname = $xml->admin->routers->adminhtml->args->frontName;

        $this->adminname = $adminname;

        return new rdi_db($dbHost, $dbUser, $dbPasswd, $dbDatabase, $dbPrefix);
    }

    public function get_adminname()
    {
        return $this->adminname;
    }

    public function get_attribute_set_id($attribute_set_name)
    {
        global $cart;

        return $cart->get_db()->cell("SELECT attribute_set_id
                                            FROM {$this->prefix}eav_attribute_set
                                            WHERE attribute_set_name = '{$attribute_set_name}'", "attribute_set_id");
    }

    //type - catalog_category , catalog_product
    //returns an array with
    public function get_attribute_id($attribute_name, $entity_type_code = "catalog_product")
    {
        global $cart;

        return $cart->get_db()->row("SELECT {$this->prefix}eav_attribute.attribute_id, backend_type
                                            FROM {$this->prefix}eav_attribute
                                            INNER JOIN {$this->prefix}eav_entity_type
                                            ON {$this->prefix}eav_entity_type.entity_type_id = {$this->prefix}eav_attribute.entity_type_id
                                            AND {$this->prefix}eav_entity_type.entity_type_code = '{$entity_type_code}'
                                            WHERE {$this->prefix}eav_attribute.entity_type_code = '{$attribute_name}'
                                            ");
    }

}

?>
