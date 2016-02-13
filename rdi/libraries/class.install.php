<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Generic Methods for Installing or testing installs.
 *
 * @author PMBliss <pmbliss@retaildimensions.com>
 * @copyright (c) 2005-2015 Retail Dimensions Inc.
 * @package Core
 */
class rdi_install extends rdi_general {

    public static $core_tables = array(
"rdi_attribute_sort"=>"CREATE TABLE `rdi_attribute_sort` (
                                    `uid` int(11) NOT NULL AUTO_INCREMENT,
                                    `attr` varchar(100) DEFAULT NULL,
                                    PRIMARY KEY (`uid`)
                                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8",

        "rdi_capture_log" => "CREATE TABLE `rdi_capture_log` (
                                    `uid` int(11) unsigned NOT NULL AUTO_INCREMENT,
                                    `orderid` int(11) unsigned NOT NULL,
                                    `response` varchar(32) NOT NULL,
                                    `original_price` decimal(10,2) NOT NULL,
                                    `capture_price` decimal(10,2) NOT NULL,
                                    `err_msg` varchar(255) NOT NULL,
                                    `warning_msg` varchar(255) NOT NULL,
                                    `capture_datetime` datetime DEFAULT NULL,
                                    `emailed_datetime` datetime DEFAULT NULL,
                                    `emailed` char(1) NOT NULL DEFAULT 'N',
                                    PRIMARY KEY (`uid`),
                                    KEY `response` (`response`)
                                  ) ENGINE=MyISAM DEFAULT CHARSET=utf8",

        "rpro_mage_shipping" => "CREATE TABLE `rpro_mage_shipping` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `rpro_provider_id` int(11) DEFAULT NULL,
      `rpro_method_id` varchar(30) DEFAULT NULL,
      `shipper` varchar(25) DEFAULT NULL,
      `ship_code` varchar(75) DEFAULT NULL,
      `ship_description` varchar(75) DEFAULT NULL,
      UNIQUE KEY `id` (`id`)
    ) ENGINE=MyISAM AUTO_INCREMENT=88 DEFAULT CHARSET=utf8",

        "rdi_card_type_mapping" => "CREATE TABLE `rdi_card_type_mapping` (
  `cart_type` varchar(50) DEFAULT NULL,
  `pos_type` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8",

        "rdi_cart_class_map_criteria" => "CREATE TABLE `rdi_cart_class_map_criteria` (
  `cart_class_mapping_id` int(10) DEFAULT NULL,
  `cart_field` varchar(80) DEFAULT NULL,
  `qualifier` varchar(150) DEFAULT NULL,
  KEY `cart_type_mapping_id` (`cart_class_mapping_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8",
        "rdi_cart_class_map_fields" => "CREATE TABLE `rdi_cart_class_map_fields` (
  `cart_class_mapping_id` int(10) DEFAULT NULL,
  `cart_field` varchar(50) DEFAULT NULL,
  `position` int(10) NOT NULL DEFAULT '0',
  `label` varchar(50) DEFAULT NULL,
  KEY `cart_class_mapping_id` (`cart_class_mapping_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8",
        "rdi_cart_class_mapping" => "CREATE TABLE `rdi_cart_class_mapping` (
  `cart_class_mapping_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_class_id` int(11) DEFAULT NULL,
  `product_class` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`cart_class_mapping_id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8",
        "rdi_cart_product_types" => "CREATE TABLE `rdi_cart_product_types` (
  `cart_product_type_id` int(10) NOT NULL AUTO_INCREMENT,
  `cart_class_mapping_id` int(10) DEFAULT NULL,
  `product_type` varchar(50) NOT NULL,
  `visibility` varchar(50) DEFAULT NULL,
  `creation_order` int(11) NOT NULL DEFAULT '0',
  PRIMARY KEY (`cart_product_type_id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8",
        "rdi_color_size_codes" => "CREATE TABLE `rdi_color_size_codes` (
  `related_parent_id` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `related_id` varchar(64) CHARACTER SET utf8 NOT NULL DEFAULT '',
  `color` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `color_code` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  `size` varchar(255) CHARACTER SET utf8 DEFAULT NULL,
  `size_code` varchar(50) CHARACTER SET utf8 DEFAULT NULL,
  PRIMARY KEY (`related_id`),
  UNIQUE KEY `rdi_related_parent_id_idx` (`related_parent_id`,`related_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8",
        "rdi_config" => "CREATE TABLE `rdi_config` (
  `cfg_opt` varchar(50) NOT NULL DEFAULT '',
  `name` varchar(255) NOT NULL DEFAULT '',
  `value` varchar(255) NOT NULL DEFAULT ''
) ENGINE=MyISAM DEFAULT CHARSET=utf8",
        "rdi_customer_email" => "CREATE TABLE `rdi_customer_email` (
  `email` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
  `related_id` varchar(50) DEFAULT NULL,
  KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8",
        "rdi_debug_log" => "CREATE TABLE `rdi_debug_log` (
  `debug_id` int(10) NOT NULL AUTO_INCREMENT,
  `level` int(10) NOT NULL DEFAULT '0',
  `datetime` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `script` varchar(255) DEFAULT NULL,
  `func` varchar(255) DEFAULT NULL,
  `debug_message` longtext,
  `data` longtext,
  UNIQUE KEY `debug_id` (`debug_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8",
        "rdi_error_log" => "CREATE TABLE `rdi_error_log` (
  `uid` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `datetime` datetime DEFAULT NULL,
  `error_level` varchar(32) DEFAULT NULL,
  `error_file` varchar(255) DEFAULT NULL,
  `error_line` varchar(32) DEFAULT NULL,
  `error_message` text,
  `back_trace` text,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8",
        "rdi_field_mapping" => "CREATE TABLE `rdi_field_mapping` (
  `field_mapping_id` int(10) NOT NULL AUTO_INCREMENT,
  `field_type` varchar(50) DEFAULT NULL COMMENT 'catalog, product, customer, order',
  `field_classification` varchar(50) DEFAULT NULL COMMENT 'attribute set',
  `entity_type` varchar(50) DEFAULT NULL COMMENT 'product type',
  `cart_field` varchar(50) DEFAULT NULL,
  `invisible_field` tinyint(4) NOT NULL DEFAULT '0' COMMENT 'not shown in the admin',
  `default_value` varchar(50) DEFAULT NULL COMMENT 'when using, dont need a pos record, the default will be used, or if pos is used and null this is used',
  `allow_update` tinyint(4) NOT NULL DEFAULT '1' COMMENT 'use this field in an update, 0 - no, 1 - yes',
  `special_handling` varchar(150) DEFAULT NULL COMMENT 'handling to do with this field value, commands line lower, no_space',
  `notes` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`field_mapping_id`),
  KEY `attribute_set_id_attribute_code` (`field_type`,`field_classification`,`cart_field`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8",
        "rdi_field_mapping_pos" => "CREATE TABLE `rdi_field_mapping_pos` (
  `field_mapping_id` int(10) DEFAULT NULL,
  `pos_field` varchar(250) DEFAULT NULL,
  `alternative_field` varchar(250) DEFAULT NULL,
  `field_order` int(11) NOT NULL DEFAULT '0',
  KEY `pos_field_id` (`field_mapping_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8",
        "rdi_loadtimes_log" => "CREATE TABLE `rdi_loadtimes_log` (
  `uid` int(11) NOT NULL AUTO_INCREMENT,
  `script` varchar(50) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `datetime` datetime DEFAULT NULL,
  `duration` varchar(10) DEFAULT NULL,
  PRIMARY KEY (`uid`)
) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8",
        "rdi_prefs_scales" => "CREATE TABLE `rdi_prefs_scales` (
  `scale_no` varchar(50) DEFAULT NULL,
  `scale_name` varchar(50) DEFAULT NULL,
  `scaleitem_no` varchar(50) DEFAULT NULL,
  `scaleitem_value` varchar(50) DEFAULT NULL,
  `scaleitem_type` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8",
        "rdi_settings" => "CREATE TABLE `rdi_settings` (
  `setting_id` int(11) DEFAULT NULL,
  `setting` varchar(50) NOT NULL,
  `value` varchar(50) DEFAULT NULL,
  `group` varchar(50) NOT NULL,
  `not_notes` text,
  `help` text,
  `cart_lib` varchar(50) DEFAULT NULL COMMENT 'right now just for reference, isnt actually used',
  `pos_lib` varchar(50) DEFAULT NULL COMMENT 'right now just for reference, isnt actually used'
) ENGINE=MyISAM DEFAULT CHARSET=utf8",
        "rdi_tax_area_mapping" => "CREATE TABLE `rdi_tax_area_mapping` (
  `cart_type` varchar(50) DEFAULT NULL,
  `pos_type` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8",
        "rdi_tax_class_mapping" => "CREATE TABLE `rdi_tax_class_mapping` (
  `cart_type` varchar(50) DEFAULT NULL,
  `pos_type` varchar(50) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8",
);
    
    
    public $table_prefix = "";

    const temp_table_prefix = "TEMP123_";

    public $temp_table = "";
    public $bool_test_install;
    public $cart = false;

    public function __construct($db)
    {
        global $test_install;

        $this->bool_test_install = (isset($test_install) && $test_install == '1');

        if ($this->bool_test_install)
        {
            $this->temp_table = " TEMPORARY ";
            $this->table_prefix = self::temp_table_prefix;
        }

        parent::rdi_general($db);
    }
	
	public function echo_message($message, $level = 1)
    {
        file_put_contents("status",$message);
    }
	
	public function getTableName($str)
	{
		$from = "`";
		$to = "`";
		$sub = substr($str, strpos($str,$from)+strlen($from),strlen($str));
		return substr($sub,0,strpos($sub,$to));
	}
    
    public function create_folders()
    {
        //Create the directory Structure
        $this->echo_message("Creating Directory Structure in rdi");
        if (!file_exists('in'))
        {
            mkdir('in', 0777);
            mkdir('in/images', 0777);
            mkdir('in/archive', 0777);
            mkdir('in/archive/images', 0777);
        }
        if (!file_exists('out'))
        {
            mkdir('out', 0777);
            mkdir('out/archive', 0777);
        }
        if (!file_exists('out/archive'))
        {
            mkdir('out', 0777);
        }
        if (!file_exists('in/archive'))
        {
            mkdir('in/archive', 0777);
            mkdir('in/archive/images', 0777);
        }
        if (!file_exists('in/images'))
        {
            mkdir('in/images', 0777);
        }
    }
    
    public function get_cart_install()
    {
        if(!$this->cart)
        {
            $cart_type = include 'cart_type.inc';

            include_once "libraries/cart_libs/{$cart_type}/{$cart_type}_rdi_install.php";

            if (!class_exists("rdi_cart_install"))
            {
                die("Could not find the CART {$cart_type} library where the staging tables are defined");
            }

            $this->cart = new rdi_cart_install($this->db_connection);
        }
    }
    
    /**
     * Alter tables in the cart.
     */
    public function alterTables()
    {
		$this->echo_message(__FUNCTION__);
        $this->get_cart_install();
        $this->cart->alter_cart_tables();
    }
    
    /**
     * Add Attributes/Fields to the cart
     */
    public function installAttributes()
    {
		$this->echo_message(__FUNCTION__);
        $this->get_cart_install();
        $this->cart->install_cart_attributes();       
    }

//extra functions for some common issues
    public function remove_prefix($prefix, $dbname)
    {
        //get the prefix name from the local.xml might need to make something to grab this auto and to rename it accordingly.

        $field_name = "Tables_in_{$dbname}";
        $_table = $this->db_connection->rows("SHOW TABLES WHERE {$field_name} like '{$prefix}%'");
        //print_r($_table);
        foreach ($_table as $table)
        {
            $table_new = str_replace($prefix, "", $table[$field_name]);
            echo "RENAME TABLE `{$table[$field_name]}` TO `{$table_new}`;";
            echo "<br>";
            $this->db_connection->exec("RENAME TABLE `{$table[$field_name]}` TO `{$table_new}`;");
        }
    }

    public function create_log_table($table_name, $echo = false)
    {
        global $test_install;
        $bool_test_install = (isset($test_install) && $test_install == '1');

        $a = explode("_", $table_name);

        $date_name = $a[1] == 'in' ? 'rdi_import_date' : 'rdi_export_date';
        if ($echo)
        {
            echo ("\n\nCREATE TABLE {$table_name}_log (index(`{$date_name}`)) as \nselect *, now() as {$date_name} from {$table_name} limit 0");
            return;
        }
        if ($bool_test_install)
        {
            return;
        }
        $this->db_connection->exec("CREATE TABLE {$table_name}_log (index(`{$date_name}`)) as select *, now() as {$date_name} from {$table_name} limit 0");
    }

    public function test_table($table)
    {
        $temp_table_prefix = 'TEMP123_';
        if (strstr($table, "_log") || strlen($table) == 0 || $table == 'rpro_config')
        {
            return;
        }
        $r = $this->db_connection->rows("EXPLAIN {$table}", 'Field');


        $r_temp = $this->db_connection->rows("EXPLAIN {$temp_table_prefix}{$table}", 'Field');

        if (empty($r))
        {
            echo "<h4>Table [{$table}] is missing.</h4><pre>";
            $c = $this->db_connection->row("SHOW CREATE TABLE {$temp_table_prefix}{$table}");
            print_r(str_replace(' TEMPORARY', '', str_replace($temp_table_prefix, '', $c['Create Table'])));
            $this->create_log_table($table, true);
            echo "</pre>";
            return;
        }

        //additional fields in their install
        $additional_fields = array_diff_assoc($r, $r_temp);
        if (!empty($additional_fields))
        {
            echo "<h4>More fields on their install [{$table}]</h4>";
            echo $this->build_table(array($additional_fields));
        }
        //missing fields from their install
        $missing_fields = array_diff_assoc($r_temp, $r);
        if (!empty($missing_fields))
        {
            echo "<h4>Missing fields [{$table}]</h4>";
            echo $this->build_table(array($missing_fields));
        }

        foreach ($r as $key => $field)
        {
            //fields that are created differently
            if (isset($r_temp[$key]))
            {
                $diff = array_diff_assoc($field, $r_temp[$key]);
                if (!empty($diff))
                {
                    echo "<h4>Field[{$table}.{$key}] is not the same as the install.</h4>";
                    echo "<h5>Site</h5>";
                    $field['type'] = 'Site';
                    $r_temp[$key]['type'] = 'Install';
                    echo $this->build_table(array($field, $r_temp[$key]));
                    echo "</pre><h5>Diff</h5>";
                    echo $this->build_table(array($diff));
                }
            }
        }
    }

    public function process_field_mapping($prefix = '')
    {
        $out = array();
        $fm = $this->db_connection->rows("SELECT * FROM {$prefix}rdi_field_mapping");

        if (!empty($fm))
        {
            foreach ($fm as $m)
            {
                $fmp = $this->db_connection->rows("SELECT pos_field, alternative_field, field_order FROM {$prefix}rdi_field_mapping_pos WHERE field_mapping_id = '{$m['field_mapping_id']}' order by field_order asc");
                unset($m['field_mapping_id']);
                $key = serialize($m);
                $out[$key] = serialize($fmp);
            }
        }
        return $out;
    }

    public function get_field_and($field)
    {
        return strlen($field) == 0 || is_null($field) ? 'IS NULL' : "= '{$this->db_connection->clean($field)}'";
    }

    public function get_field_mapping($fm, $prefix = '')
    {
        $field_type = $this->get_field_and($fm['field_type']);
        $field_classification = $this->get_field_and($fm['field_classification']);
        $entity_type = $this->get_field_and($fm['entity_type']);
        $cart_field = $this->get_field_and($fm['cart_field']);
        $mapping = $this->db_connection->rows("SELECT * FROM {$prefix}rdi_field_mapping WHERE field_type {$field_type} AND field_classification {$field_classification} AND entity_type {$entity_type} AND cart_field {$cart_field}");
        return empty($mapping) ? 'No Mapping found' : $mapping;
    }

    public function test_field_mapping()
    {
        global $cart;
        $current_field_mapping = $this->process_field_mapping();
        $install_field_mapping = $this->process_field_mapping($this->table_prefix);

        $hr = "<hr style='width:95%'>";
        //print_r($install_field_mapping);
        //print_r($current_field_mapping);
        //check for on the install, not in the current
        foreach ($install_field_mapping as $fm => $fmp)
        {
            if (!isset($current_field_mapping[$fm]))
            {
                $field_mapping = unserialize($fm);
                $field_mapping_pos = unserialize($fmp);
                echo "<fieldset style=\"max-width:200\">
			<legend>The install mapping does not match</legend>
			";
                echo "<h5>Install Mapping</h5>";
                //_print_r($field_mapping);
                echo $this->build_table(array($field_mapping));
                //_print_r($field_mapping_pos);
                echo $this->build_table($field_mapping_pos);
                echo "<h5>Current Mapping</h5>
			";
                //_print_r(get_field_mapping($field_mapping, $db));
                $curfmrow = $this->get_field_mapping($field_mapping);
                if (is_array($curfmrow))
                {
                    echo $this->build_table($this->get_field_mapping($field_mapping));
                }
                else
                {
                    echo "<span>" . $curfmrow . "</span>
				";
                }
                echo "</fieldset>
			";
            }
            else
            {
                if ($fmp !== $current_field_mapping[$fm])
                {
                    $field_mapping = unserialize($fm);
                    $field_mapping_pos = unserialize($fmp);
                    $current_field_mapping_pos = unserialize($current_field_mapping[$fm]);
                    echo "<fieldset  style=\"max-width: 200\"><legend>The POS field mapping differs for this mapping on the current.</legend>
				";

                    echo $this->build_table(array($field_mapping));
                    echo "<h5>Install</h5>";
                    echo $this->build_table($field_mapping_pos);
                    echo "<h5>Current</h5>";
                    echo $this->build_table($current_field_mapping_pos);
                    //_print_r($field_mapping_pos); 
                    //_print_r($current_field_mapping_pos); 
                    echo "</fieldset>
				";
                }
            }
        }
    }

    public function test_settings()
    {
        $install_settings = $this->db_connection->rows("SELECT * FROM {$this->table_prefix}rdi_settings", 'setting');
        $current_settings = $this->db_connection->rows("SELECT * FROM rdi_settings", 'setting');

        $missing_settings = array_diff_assoc($install_settings, $current_settings);
        $extra_settings = array_diff_assoc($current_settings, $install_settings);

        sort($missing_settings);
        if (!empty($missing_settings))
        {
            echo "<h4>Missing Settings</h4>";
            echo $this->build_table($missing_settings);
        }

        sort($extra_settings);
        if (!empty($extra_settings))
        {
            echo "<h4>Extra Settings</h4>";
            echo $this->build_table($extra_settings);
        }
    }
    
    public function test_tables()
    {        
        if (!class_exists("rdi_staging_db_lib"))
        {
            include_once 'init.php';
            //global $cart;
            $cart->get_processor("rdi_staging_db_lib");
            if (!class_exists("rdi_staging_db_lib"))
            {
                include_once "libraries/pos_libs/{$pos_type}/{$pos_type}_rdi_staging_db_lib.php";

                if (!class_exists("rdi_staging_db_lib"))
                {
                    die("<h1>Could not complete test. Could not find staging library!</h1>");
                }
            }
        }

        echo "<h4>Testing tables!</h4>";
        $tables = rdi_staging_db_lib::$tables;
        foreach ($tables as $table)
        {
            $this->test_table($table);
        }
        echo "<h4>Finished Testing Tables!</h4>";
    }

    public function build_table($array)
    {

        // start table
        if (empty($array))
        {
            return "";
        }
        $html = '<table class="pure-table">
	';

        // header row

        $html .= '<tr>';
        foreach ($array[0] as $key => $value)
        {

            $html .= '<th>' . $key . '</th>
			';
        }

        $html .= '</tr>
	';

        // data rows

        foreach ($array as $key => $value)
        {

            $html .= '<tr>
		';

            foreach ($value as $key2 => $value2)
            {
				if(is_array($value2))
				{
					$html .= $this->build_table(array($value2));
				}
				else
				{
					$html .= '<td> ' . htmlentities($value2) . ' </td>
			';
				}
            }

            $html .= '</tr>
		';
        }

        // finish table and return it

        $html .= '</table>
	';

        return $html;
    }

    public function exec_create_table($sql)
    {
		$this->echo_message("Create Table {$this->table_prefix}" . $this->getTableName($sql));
        $this->db_connection->exec(str_replace("CREATE TABLE `", "CREATE {$this->temp_table} TABLE `{$this->table_prefix}", $sql));
    }

    public function exec_insert_into($sql)
    {
		$this->echo_message("Inserting data {$this->table_prefix}" . $this->getTableName($sql));
        $this->db_connection->exec(str_replace("INSERT INTO `", "INSERT INTO `{$this->table_prefix}", $sql));
    }

    public function exec_alter_table($sql)
    {
		$this->echo_message("Altering Tables {$this->prefix}" . $this->getTableName($sql));
        $this->db_connection->exec(str_replace("ALTER TABLE `", "ALTER TABLE `{$this->prefix}", $sql));
    }

    /**
     * @todo Need to make this a static array.
     */
    public function add_core_rdi_tables()
    {
        if(!empty(self::$core_tables))
        {
            foreach(self::$core_tables as $table => $sql)
            {
				$this->echo_message("Adding table {$table}");
                $this->exec_create_table($sql);
            }
        }
    }

    public function start_test_install_tables($pos_type)
    {
        if ($this->bool_test_install)
        {
            $this->test_tables();
            $this->test_field_mapping();
            $this->test_settings();
        }
    }

    public function install_pos_staging_tables($pos_type)
    {
        include_once "libraries/pos_libs/{$pos_type}/{$pos_type}_rdi_install.php";

        if (!class_exists("rdi_pos_install"))
        {
            die("Could not find the POS {$pos_type} library where the staging tables are defined [libraries/pos_libs/{$pos_type}/{$pos_type}_rdi_install.php]");
        }
        
        if (!empty(rdi_pos_install::$pos_tables))
        {
            foreach (rdi_pos_install::$pos_tables as $table => $sql)
            {                
                $this->exec_create_table($sql);

                if (strstr($table, "_in_") || strstr($table, "_out_"))
                {
                    $this->create_log_table($table);
                }
            }
        }
    }

}
