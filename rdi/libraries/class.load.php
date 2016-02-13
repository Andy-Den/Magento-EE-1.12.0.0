<?php

/**
 * Generic Load Class
 *
 * General class for handling what will be used in all the classes, the database connection, and some generalized functions
 *
 * PHP version 5.3
 *
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    1.0.0
 */
class rdi_load extends field_mapping {

    public $load_type;
    public $hook;
    public $test_count = false;

    /**
     * 
     * @param type $db
     * @param type $load_type
     */
    public function __construct($db, $load_type = false)
    {
        //set load type
        if ($load_type)
        {
            $this->load_type = $load_type;
        }

        $this->set_constants();

        //call parent
        parent::__construct($db);
    }

    /**
     * 
     * @global type $hook_handler
     * @return \rdi_load
     */
    public function pre_load()
    {
        global $hook_handler;

        $hook_handler->call_hook($this->hook . __FUNCTION__);
        return $this;
    }

    /**
     * 
     * @global type $hook_handler
     * @return \rdi_load
     */
    public function post_load()
    {
        global $hook_handler;

        $hook_handler->call_hook($this->hook . __FUNCTION__);
        return $this;
    }

    /**
     * 
     */
    public function load()
    {
        
        $this->hook = str_replace("rdi_", "", get_class($this));

        if (!isset($this->load_type))
        {
            //assume the load type is removing all "loads" and underscores;
            $this->load_type = str_replace("load", "", str_replace("_", "", $this->hook));
        }
        
        if($this->test_count)
        {
            global $db_lib;
            
            if($this->db_connection->count($db_lib->get_table_name('in_images')) > '0')
            {
                $this->echo_message("Beginning {$this->load_type}");                
            }
            else   
            {
                return $this;
            }
        }

        //Call order.
        // Call pre_load functions, could be rewritten in the pos class. and further in the cart.
        // call main load, including insert and update functions.
        // call load in the cart, which is any cart specific loading
        // post load hooks or checks on data validity.
        if ($this->test_setting(__FUNCTION__))
        {
            $this->pre_load()->pos_load()->main_load()->cart_load()->post_load();
        }
    }

    // called with $this->test_setting(__FUNCTION__)
    // 1 is ON and anything else is off. generally 0 off.
    // this should probably return the value if greater than 0. but in the future.
    /**
     * 
     * @param type $setting
     * @return type
     */
    public function test_setting($setting)
    {
        $setting_name = "{$setting}_{$this->load_type}";

        $this->_echo("Setting:{$setting_name}[{$GLOBALS[$setting_name]}]");

        return isset($GLOBALS[$setting_name]) && $GLOBALS[$setting_name] == '1';
    }

    // functions for completeness
    public function pos_load()
    {
        return $this;
    }

    public function main_load()
    {
        $this->insert()->update();

        return $this;
    }

    //copy these to the cart class
    public function insert()
    {
        if ($this->test_setting(__FUNCTION__))
        {
            $this->_echo(__CLASS__ . " " . __FUNCTION__ . " has not been implemented in the cart.");
        }

        return $this;
    }

    public function update()
    {
        if ($this->test_setting(__FUNCTION__))
        {
            $this->_echo(__CLASS__ . " " . __FUNCTION__ . " has not been implemented in the cart.");
        }

        return $this;
    }

    /**
     * 
     * @return \rdi_load
     */
    public function cart_load()
    {
        return $this;
    }

    /**
     * static class for initializing a load class structure.
     * can't use an auto load as this would interfere with Zend in Magento and other carts.
     * @global type $cart_type
     * @global type $pos_type
     * @param type $db
     * @param type $library
     * @return \class_name
     */
    public static function include_libs($db, $library)
    {
        global $cart_type;
        global $pos_type;

        if (!isset($cart_type))
        {
            die("Specify a cart_type in the init.php");
        }

        if (!isset($pos_type))
        {
            die("Specify a pos_type in the init.php");
        }

        if (!file_exists("libraries/pos_libs/{$pos_type}/{$pos_type}_rdi_pos_{$library}_load.php"))
        {
            die("Missing library file libraries/pos_libs/{$pos_type}/{$pos_type}_rdi_pos_{$library}_load.php");
        }

        if (!file_exists("libraries/cart_libs/{$cart_type}/{$cart_type}_rdi_cart_{$library}_load.php"))
        {
            die("Missing library file libraries/cart_libs/{$cart_type}/{$cart_type}_rdi_cart_{$library}_load.php");
        }

        require_once "libraries/pos_libs/{$pos_type}/{$pos_type}_rdi_pos_{$library}_load.php";

        if (!class_exists("rdi_pos_{$library}_load"))
        {
            die("Missing library class in libraries/cart_libs/{$cart_type}/{$cart_type}_rdi_cart_{$library}_load.php");
        }

        require_once "libraries/cart_libs/{$cart_type}/{$cart_type}_rdi_cart_{$library}_load.php";

        if (!class_exists("rdi_pos_{$library}_load"))
        {
            die("Missing library class in libraries/cart_libs/{$cart_type}/{$cart_type}_rdi_cart_{$library}_load.php");
        }

        //cart is the final child class.
        $class_name = "rdi_cart_{$library}_load";

        return new $class_name($db, $library);
    }

}
