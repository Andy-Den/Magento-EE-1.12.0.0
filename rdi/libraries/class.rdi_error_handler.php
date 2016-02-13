<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Error Handler Class
 *
 * Used to handle custom php error handling throughout the scripts
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     Tom Martin <tmartin@retaildimensions.com>
 * @copyright  2005-2011 Retail Dimensions Inc.
 * @version    1.0.0
 */
class rdi_error_handler extends rdi_general {

    protected $last_error_level = '';
    protected $last_error_code = '';
    protected $last_error_message = '';
    protected $last_error_file = '';
    protected $last_error_line = '';
    protected $last_error_context = '';
    protected $last_error_back_trace = '';
    protected $save_back_trace = false;
    protected $output = false;
 
    /**
     * Class constructor
     */
    public function rdi_error_handler($db = '')
    {
        if ($db)
            $this->set_db($db);        
    }

    public function __sleep()
	 {
		 return array();
	 }
    
    /**
     * Custom PHP error handling method
     *
     * @global rdi_db $db
     * @param integer $error_level
     * @param string $error_message
     * @param string $error_file
     * @param integer $error_line
     * @param string $error_context
     */
    public function error_handler($error_level, $error_message, $error_file, $error_line, $error_context)
    {     
        $this->last_ip_address = $_SERVER['REMOTE_ADDR'];
        $this->last_error_level = $error_level;
        $this->last_error_message = $error_message;
        $this->last_error_file = $error_file;
        $this->last_error_line = $error_line;

        $error_code = $this->find_error_code($error_level);
        $this->last_error_code = $error_code;

        // Find the backtrace
        if ($this->save_back_trace) {
            $error_back_trace = print_r(debug_backtrace(), true);
            $error_context = print_r($error_context, true);
        } else {
            $error_back_trace = '';
            $error_context = '';
        }

        $this->last_error_back_trace = $error_back_trace;
        $this->last_error_context = $error_context;

        $this->store_error_db();

        if ($this->output)
            $this->print_error();
    }

    /**
     * Inserts the error into the rdi_error_log db table if db object passed.
     *
     * @return boolean
     */
    protected function store_error_db()
    {

        if ($this->db_connection) {
            $ip_address = $this->db_connection->clean($this->last_ip_address);
            $error_level = $this->db_connection->clean($this->last_error_level);
            $error_code = $this->db_connection->clean($this->last_error_code);
            $error_file = $this->db_connection->clean($this->last_error_file);
            $error_line = $this->db_connection->clean($this->last_error_line);
            $error_message = $this->db_connection->clean($this->last_error_message);
            $error_context = $this->db_connection->clean($this->last_error_context);
            $error_back_trace = $this->db_connection->clean($this->last_error_back_trace);
            $sql = "INSERT INTO rdi_error_log
                        (datetime,
                        error_level,
                        error_file,
                        error_line,
                        error_message,
                        back_trace
                        )
                        VALUES
                        (NOW(),
                        '{$ip_address}|{$error_level}',
                        '{$error_file}',
                        '{$error_line}',
                        '{$error_code}|{$error_message}',
                        '{$error_back_trace}'
                        )";
                        
            try
            {
                $this->db_connection->insert($sql, false);
            }
            catch(Exception $ex)
            {
                
            }
                        
            //if ($this->db_connection->insert($sql))
//                return true;
//            else
//                return false;
        }
    }

    /**
     * Outputs the last error to the screen.
     */
    protected function print_error() {
        echo <<<EOT
<div style="width: 700px; border: 1px solid #000; background: #D7E2EA; margin: 10px;">
    <div style="background: #C5D5E0; border-bottom: 3px double #444; padding: 4px;">
        <b>{$this->last_error_code} : </b> {$this->last_error_file} on line: {$this->last_error_line}
    </div>
    <div style="padding: 4px;">
        {$this->last_error_message}
    </div>
</div>
EOT;
    }

    /**
     * Method for finding error code for the given error level
     *
     * @param integer $error_level
     * @return string
     */
    protected function find_error_code($error_level)
    {

        $error_code = '';

        $code_array = array(
            '1' => 'E_ERROR',
            '2' => 'E_WARNING',
            '4' => 'E_PARSE',
            '8' => 'E_NOTICE',
            '16' => 'E_CORE_ERROR',
            '32' => 'E_CORE_WARNING',
            '64' => 'E_COMPILE_ERROR',
            '128' => 'E_COMPILE_WARNING',
            '256' => 'E_USER_ERROR',
            '512' => 'E_USER_WARNING',
            '1024' => 'E_USER_NOTICE',
            '2048' => 'E_STRICT',
            '4096' => 'E_RECOVERABLE_ERROR',
            '8191' => 'E_ALL',
        );

        if (isset($code_array[$error_level]) && $code_array[$error_level])
            $error_code = $code_array[$error_level];

        return $error_code;
    }

    /**
     * Basic method for setting whether or not to store a backtrace
     *
     * @param boolean $bool
     * @return boolean
     */
    public function save_backtrace($bool = true)
    {   
        if ($bool)
            $this->save_back_trace = true;
        else
            $this->save_back_trace = false;

        return true;
    }

    /**
     * Method to set whether or not to output errors to the screen
     *
     * @param boolean $bool
     * @return boolean
     */
    public function output_errors($bool = true)
    {   
        if ($bool)
            $this->output = true;
        else
            $this->output = false;

        return true;
    }
}

?>
