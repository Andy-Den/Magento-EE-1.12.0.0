<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Generic functions class
 *
 * General class for handling what will be used in all the classes, the database connection, and some generalized functions
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    1.0.0
 */
class rdi_general {

    /**
     * Class Variables
     */
    protected $db_connection;
    public $prefix;
    public $rdi_prefix;

    /**
     * Class Constructor
     *
     * @param rdi_db $db
     */
    public function rdi_general($db = '')
    {
        if ($db)
            $this->set_db($db);

        if ($db && $db->get_db_prefix())
            $this->prefix = $db->get_db_prefix();
        
        $this->set_rdi_prefix();
    }

    public function __sleep()
    {
        return array();
    }

    /**
     * Simple method for printing success message to screen
     */
    public function print_success()
    {
        echo 'success';
    }

    /**
     * Method for setting the database connection to use.
     *
     * @param rdi_db $db
     * @return boolean
     */
    public function set_db($db)
    {
        if ($db)
            $this->db_connection = $db;
        else
            return false;

        $this->prefix = $this->db_connection->get_db_prefix();

        return true;
    }

    /**
     * Method for getting the database connection in use
     *
     * @param rdi_db $db
     * @return boolean
     */
    public function get_db()
    {
        if ($this->db_connection)
            return $this->db_connection;
        else
            return false;

        return true;
    }
    
    public function set_rdi_prefix()
    {
        global $rdi_prefix;
        if(isset($rdi_prefix))
        {
            $this->rdi_prefix = $rdi_prefix;
        }
        else
        {
            $this->rdi_prefix = "";
        }
    }

    static public function _echo($statement, $wrap = "h3")
    {
        global $verbose_queries;

        if (isset($verbose_queries) && $verbose_queries == 1)
        {
            echo "<{$wrap}>";

            echo $statement;

            echo "</{$wrap}>";
        }
    }

    static public function _print_r($array)
    {
        self::_echo(print_r($array, true), "pre");
    }

    static public function _var_dump($array)
    {
        self::_echo(var_export($array, true), "pre");
    }

    static public function _methods($class)
    {
        self::_print_r(get_class_methods($class));
    }

    static public function _filename($item, $function_name = false)
    {
        if (is_object($item) && !$function_name)
        {
            $r = new ReflectionClass($item);
            self::_echo($r->getFileName());
        }
        if (is_object($item))
        {
            $r = new ReflectionMethod($item, $function_name);
            self::_echo($r->getFileName());
        }
        else
        {
            self::_echo("Not an object");
        }
    }

    /**
     * Method echos a comment that is hidden when not using verbose_queries.
     *
     * @param string $value
     */
    static public function _comment($value, $header = "Comment")
    {
        self::_echo($header);
        self::_echo($value . "<br><br>", "em");
    }

    static public function _echo_count($string)
    {
        //use this for testing function and loops. 
        //$this->_echo_count(__CLASS__ .'|'. __FUNCTION__.'|'.__LINE__);
        global $_echo_count, $_echo_order;

        if (!isset($_echo_count))
        {
            $_echo_count = array();
        }

        if (!isset($_echo_order))
        {
            $_echo_order = array();
        }

        $_echo_order[] = $string;

        if (!isset($_echo_count[$string]))
        {
            $_echo_count[$string] = 1;
        }
        else
        {
            $_echo_count[$string] ++;
        }

        //$this->_echo("{$string}:{$_echo_count[$string]}");
    }

    //testing function that should not be used live.
    static public function _exit($message = '')
    {

        global $_echo_count, $_echo_order;

        self::_echo($message);

        self::_print_r($_echo_count);
        //$this->_print_r($_echo_order);
        file_put_contents("nodes.json", json_encode($_echo_order));


        exit;
    }

    static public function starts_with($haystack, $needle)
    {
        return (substr($haystack, 0, strlen($needle)) === $needle);
    }

    static public function ends_with($haystack, $needle)
    {
        if (strlen($needle) == 0)
        {
            return true;
        }

        return (substr($haystack, -strlen($needle)) === $needle);
    }

    public function set_constants()
    {
        $oClass = new ReflectionClass(get_class($this));
        $constants = $oClass->getConstants();
        foreach ($constants as $constant => $value)
        {
            $this->$constant = $value;
        }
    }

    /**
     * Echos a message to the main. Each level is 5 characters.
     * @param type $message
     * @param type $level
     */
    public function echo_message($message, $level = 1)
    {
        $hyphens = $level * 5;
        echo str_pad(str_repeat("-", $hyphens) . $message, 75, "-", STR_PAD_RIGHT) . "&nbsp;<br />";
        file_put_contents("status",$message);
    }

}

?>
