<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * general lib Class
 *
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core
 */
class rdi_lib extends rdi_general {

    /**
     * Class Variables
     */
    protected $lib;                     // Stores the lib that is used
    protected $lib_type;                // Stores the type of lib that is used, pos or cart
    protected $processors = array();
    public $prefix;

    public function __sleep()
	 {
		 return array();
	 }
    
    /**
     * Class Constructor
     *
     * @global type $rdi_path
     * @param type $_lib_type
     * @param rdi_pos_lib $_lib
     * @param rdi_db $db
     */
    public function rdi_lib($_lib_type = '', $_lib = '', $db = '')
    {
        global $rdi_path;

        if (!is_dir($rdi_path . "libraries/" . $_lib_type . "_libs/" . $_lib))
        {
            //this is an error since that library doesnt exist
            echo "<br><h1>Error with your Library settings (init.php)</h1>
                  Library does not exist: {$_lib}
                  <br><span style='color:red'>Can not continue till this is fixed.</span>";
            exit;
        }

        $this->processors = array();

        if ($_lib_type)
            $this->set_lib_type($_lib_type);

        if ($_lib)
            $this->set_lib($_lib);

        if ($db)
            $this->set_db($db);
        else
        {
            //get the database object from the library object
            $this->set_db($this->get_processor("rdi_db_lib")->get_db_obj());
        }
    }

    /**
     * Method for setting the library type to be used
     *
     * @param rdi_lib $lib_type
     * @return boolean
     */
    public function set_lib_type($_lib_type)
    {
        if ($_lib_type)
        {
            $this->lib_type = $_lib_type;
        }
        else
            return false;

        return true;
    }

    /**
     * Method for setting the library to be used
     *
     * @param rdi_pos_lib $l
     * @return boolean
     */
    public function set_lib($_lib)
    {
        if ($_lib)
            $this->lib = $_lib;
        else
            return false;

        return true;
    }

    public function get_processor($class_name)
    {
        global $rdi_path;

        if ($class_name)
        {
            //check if we have already loaded this one
            if (in_array($class_name, $this->processors))
            {
                return $this->processors[$class_name];
            }
            else
            {
                //check the file we want exists
                if (file_exists($rdi_path . "libraries/" . $this->lib_type . "_libs/" . $this->lib . "/" . $this->lib . "_" . $class_name . ".php"))
                {
                    //include the file
                    require_once($rdi_path . "libraries/" . $this->lib_type . "_libs/" . $this->lib . "/" . $this->lib . "_" . $class_name . ".php");

                    if (file_exists($rdi_path . "add_ons/libraries/{$this->lib_type}_libs/{$this->lib}/{$this->lib}_{$class_name}.php"))
                    {
                        require_once($rdi_path . "add_ons/libraries/{$this->lib_type}_libs/{$this->lib}/{$this->lib}_{$class_name}.php");
                        $add_on_class_name = "{$class_name}_add_on";

                        return $this->processors[$class_name] = new $add_on_class_name($this->db_connection);
                    }

                    return $this->processors[$class_name] = new $class_name($this->db_connection);
                }
            }
        }

        return false;
    }

    static function include_core_class($class_name, $create = false, $db = false)
    {
        global $rdi_path, $core_class_list;

        $mapped_class_name = $class_name;

        if (!isset($core_class_list))
        {
            $core_class_list = array();
        }

        //get the name of the class, we can rewrite it.
        //if the class name has not been set, lets see if the file exists and then if there is an addon there for the class.
        if (!isset($core_class_list[$class_name]))
        {
            //check the file we want exists
            if (file_exists("{$rdi_path}libraries/class.{$class_name}.php"))
            {
                //include the file
                require_once("{$rdi_path}libraries/class.{$class_name}.php");

                if (file_exists("{$rdi_path}add_ons/libraries/class.{$class_name}.php"))
                {
                    require_once("{$rdi_path}add_ons/libraries/class.{$class_name}.php");

                    $mapped_class_name .= "_add_on";
                }

                $core_class_list[$class_name] = $mapped_class_name;
            }
        }
        else
        {
            return $core_class_list[$class_name];
        }

        if ($create)
        {
            if (!$db)
            {
                return new $core_class_list[$class_name]();
            }
            else
            {
                return new $core_class_list[$class_name]($db);
            }
        }
        else
        {
            return $core_class_list[$class_name];
        }
    }

    /**
      arg[0] = class_name
      arg[1],... arg[10] = construction vars for the class.
     */
    static function create_core_class()
    {
        global $cart;

        $args = func_get_args();

        $r = rdi_lib::include_core_class($args[0]);
        
        if (strlen($r) > 0)
        {
            switch (func_num_args())
            {
                case 2:
                    return new $r($args[1]);
                case 3:
                    return new $r($args[1], $args[2]);
                case 4:
                    return new $r($args[1], $args[2], $args[3]);
                default:
                    break;
            }
        }
    }

}

?>