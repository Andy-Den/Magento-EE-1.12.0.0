<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Benchmark
 *
 * Simple benchmarking
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @copyright  2005-2012 Retail Dimensions Inc.
 * @version    1.0.0
 */
class rdi_benchmark extends rdi_general {

    protected $benchmarks = array();
    protected $display_screen;
    protected $save_log_;

    /**
     * Class Constructor
     *
     * @param rdi_benchmark $db
     */
    public function rdi_benchmark($db = '', $global_display_screen = true, $global_save_log = true)
    {
        global $load_time_log_archive_length;
        $benchmarks = array();
        $this->display_screen = $global_display_screen;
        $this->save_log_ = $global_save_log;

        if ($db)
            $this->set_db($db);

        //check for the setting for days to keep the log data
        if (isset($load_time_log_archive_length) && $load_time_log_archive_length != -1)
        {
            $sql = "delete from rdi_loadtimes_log where datetime < DATE_SUB(NOW(), INTERVAL {$load_time_log_archive_length} day)";
            $this->db_connection->exec($sql);
        }
    }

    public function log_memory_usage($script, $action)
    {
        global $log_memory_usage;

        if (isset($log_memory_usage) && $log_memory_usage == 1)
        {

            $usage = memory_get_usage();

            $this->db_connection->exec("INSERT INTO rdi_loadtimes_log
                                                        (script,
                                                        action,
                                                        datetime,
                                                        duration
                                                        )
                                                        VALUES
                                                        (\"{$script}\",
                                                        \"Current memory usage for: {$action}\",
                                                        NOW(),
                                                        \"{$usage}\"
                                                    )");
        }
    }

    public function set_start_time($script, $action)
    {
        global $benchmark_global_save_db;
        $this->benchmarks[$script][$action] = microtime(true);

        if (isset($benchmark_global_save_db) && $benchmark_global_save_db == 1)
        {
            //$this->db_connection;
            $this->db_connection->exec("INSERT INTO rdi_loadtimes_log
												(script,
												action,
												datetime,
												duration
												)
												VALUES
												(\"{$script}\",
												\"{$action}\",
												NOW(),
												0.0000
											)");
        }
    }

    public function set_end_time($script, $action, $print_screen = true, $save_log = true)
    {
        $dur = '';

        if (isset($this->benchmarks[$script][$action]))
        {
            //$dif = $ts - $this->benchmarks[$script][$action];
            //$mins = round($dif / 60);
            //$secs = $dif % 60;
            //$dur = $mins . ':' . $secs;

            $dur = number_format(microtime(true) - $this->benchmarks[$script][$action], 3);

            if ($print_screen && $this->display_screen)
            {
                echo "Script: {$script}  Action: {$action} Duration: {$dur}<br>";

                // flush the output buffer if possible
                @flush();
            }
            if ($save_log && $this->save_log_)
            {
                //$this->db_connection;
                $this->db_connection->exec("INSERT INTO rdi_loadtimes_log
                                                    (script,
                                                    action,
                                                    datetime,
                                                    duration
                                                    )
                                                    VALUES
                                                    (\"{$script}\",
                                                    \"{$action}\",
                                                    NOW(),
                                                    \"{$dur}\"
                                                )");
            }
        }
    }

    public function set_start($script, $action)
    {
        global $save_main_loadtimes;
        $this->benchmarks[$script][$action] = microtime(true);

        if (isset($save_main_loadtimes) && $save_main_loadtimes == 1)
        {
            //$this->db_connection;
            $this->db_connection->exec("INSERT INTO rdi_loadtimes_log
												(script,
												action,
												datetime,
												duration
												)
												VALUES
												(\"{$script}\",
												\"START\",
												NOW(),
												0.0000
											)");
        }
    }

    public function set_end($script, $action, $print_screen = false)
    {
        global $save_main_loadtimes;

        //$ts = microtime();
        $dur = '';

        if (isset($this->benchmarks[$script][$action]))
        {
            $dur = number_format(microtime(true) - $this->benchmarks[$script][$action], 3);

            if (isset($save_main_loadtimes) && $save_main_loadtimes == 1)
            {
                //$this->db_connection;
                $this->db_connection->exec("INSERT INTO rdi_loadtimes_log
                                                    (script,
                                                    action,
                                                    datetime,
                                                    duration
                                                    )
                                                    VALUES
                                                    (\"{$script}\",
                                                    \"END\",
                                                    NOW(),
                                                    \"{$dur}\"
                                                )");
            }
        }
    }

}

?>