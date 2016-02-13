<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Generic functions class
 *
 * General class for handling what will be used in all the classes, the database connection, and some generalized functions
 *
 * PHP version 5.3
 *
 * @author PBliss
 * @author     Paul Bliss <pbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Debug
 */
class rdi_debug extends rdi_general
{

    /**
     * Class Variables
     */
    protected $debug_enabled;
    protected $debug_lvl;
    protected $debug_script_data;
    protected $verbose_queries;
    protected $show_query_counts;
    protected $show_counter;
    protected $log_data;
    
    /**
     * Class Constructor
     *
     * @param rdi_debug $lib
     */
    public function rdi_debug($db, $enabled, $debug_level, $debug_scripts = array(), $verbose_query_areas = array(), $show_counts = false, $log_d = false)
    {
        global $purge_debug_on_load;
        
        $this->debug_enabled = $enabled;
        $this->debug_lvl = $debug_level;
        $this->debug_script_data = $debug_scripts;
        $this->verbose_queries = $verbose_query_areas;
        $this->show_query_counts = $show_counts;
        $this->show_counter = false;
        
        if($log_d == 0)
            $this->log_data = false;
        
        if($log_d == 1)
            $this->log_data = true;
        
        $this->log_data = $log_d;
        
        if ($db)
            $this->set_db($db);
        
        if($purge_debug_on_load)
        {
            $this->db_connection->trunc("rdi_debug_log");
        }
    }

    public function get_verbose_query_areas()
    {
        return $this->verbose_queries;
    }
    
    public function get_show_query_count()
    {
        return $this->show_query_counts;
    }
    
    //marked if a count is to be shown, keeps the counter from showing when the verbose isnt set to do so
    public function get_show_counter()
    {
        return $this->show_counter;         
    }
    
    public function set_show_counter($value)
    {
        $this->show_counter = $value;
    }
        
    public function show_query($area, $query, $data = '')
    {              
        if(is_array($this->verbose_queries) && in_array($area, $this->verbose_queries))
        {
            echo "<Br>";
            echo "Called From: {$area}";
            
            if ($data != '')
                echo "--- Debug Info: {$data}";
            
            echo "<Br>";
            
            if($this->verbose_queries !== true)
            {                
                echo $query;
                echo "<Br>";                               
            }
            
            $this->show_counter = true;
        }
        else if($this->verbose_queries == "update_insert")
        {
            echo "<Br>";
            echo "Called From: {$area}<Br>";
            
            if ($data != '')
                echo "--- Debug Info: {$data}";
            
            echo "<Br>";
                
            if($this->verbose_queries !== true)
            {                
                echo $query;
                echo "<Br>";                               
            }
            
            $this->show_counter = true;
        }            
    }
    
    /*
     *  Write a debug statement to the database, this method accepts an array of variables to write
     * they will write out as a serialize array 
     */

    public function write($script, $function, $message, $message_level = 0, $data = array())
    {
        if ($this->debug_enabled)
        {
            $d = array();
            foreach ($data as $name => $var)
            {
                $d[$name] = $var;
            }

            $this->write_message($script, $function, $message, $message_level = $message_level, serialize($d));
        }
    }

    public function write_message($script, $function, $message, $message_level = 0, $data = '')
    {
        if ($this->debug_enabled)
        {
            //write all messages to the database
            if ($this->debug_lvl == 0 || $this->debug_lvl >= $message_level)
            {
                if($data != '')
                    $data = addslashes($data); 
                
                //$data = str_replace("'", "\'", $data);
                
                $message = addslashes($message);
                $function = addslashes($function);
                
                //log data is the message_level is 99
                if(!$this->log_data && $message_level != 99)
                {                                                          
                    $data = '';
                }
                
                $this->db_connection->insert("Insert into rdi_debug_log (debug_message, func, script, level, data) values('{$message}', '{$function}', '{$script}', {$message_level}, '{$data}')");
            }
        }
    }

    //an echo statement that can be shut off globally
    //use the rdi_general _echo, _print_r, and _var_dump functions with verbose_queries=1 to see your outputs and hide them on loads.
    public function echo_($statement)
    {        
        if(array_key_exists('SERVER_ADMIN', $_SERVER) && $_SERVER['SERVER_ADMIN'] == "kcobun@retaildimensions.com")
        {
            echo $statement;
        }        
    }
    
    public function print_r_($variable)
    {
        if(array_key_exists('SERVER_ADMIN', $_SERVER) && $_SERVER['SERVER_ADMIN'] == "kcobun@retaildimensions.com")
        {
            print_r($variable);
        } 
    }
    
}