<?php

/**
 * Calls addons placed in the add_on folder names with {$cart_type}_{$pos_type}_{$hook_name} naming convention. Includes functions to document comments from addons and place them in a table during the process. Every time an addon is called and verbose_queries is on "<h1>addons</h1>" is echod.
 * 
 * 
 * 
 * PHP version 5.3
 * 
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Hook
 */
class rdi_hook_handler extends rdi_general {

    /**
     * This is the basic structure for a hook or addon. Variables can be passed into the addon with args by reference.
     * Benchmarker is called for timing.
     * 
     * Returns true or false if the hook is found and executed.
     * 
     * @global string $rdi_path
     * @global rdi_lib $cart
     * @global rdi_lib $pos
     * @global type $debug
     * @global type $cart_type
     * @global type $pos_type
     * @global setting $addon_comments
     * @global setting $verbose_queries
     * @global rdi_benchmarker $benchmarker
     * @param string $hook_name
     * @param variable $arg
     * @param variable $arg2
     * @param variable $arg3
     * @param variable $arg4
     * @return boolean
     */
    public function call_hook($hook_name, &$arg = '', &$arg2 = '', &$arg3 = '', $arg4 = '')
    {
        global $rdi_path, $cart_type, $pos_type, $addon_comments, $benchmarker;

        if (file_exists("{$rdi_path}add_ons/{$cart_type}_{$pos_type}_{$hook_name}.php"))
        {
            $this->_echo("addons", "h1");

            if (isset($addon_comments) && $addon_comments == 1)
            {
                $this->record_comments($hook_name);
            }

            $benchmarker->set_start_time("{$rdi_path}add_ons/{$cart_type}_{$pos_type}_{$hook_name}.php", "Running Add-on");
            include "{$rdi_path}add_ons/{$cart_type}_{$pos_type}_{$hook_name}.php";
            $benchmarker->set_end_time("{$rdi_path}add_ons/{$cart_type}_{$pos_type}_{$hook_name}.php", "Running Add-on");
            return true;
        }

        return false;
    }

    public function call_basic_hooks()
    {
        
    }

    /**
     * returns the class that called the hook handler via a brack trace.
     * @global string $cart_type
     * @global string $pos_type
     * @global rdi_lib $pos
     * @global string $pos_type
     * @global rdi_lib $cart
     * @return string|null
     */
    public function get_parent_class()
    {
        global $cart_type, $pos_type;

        $trace = debug_backtrace();

        $parent_found = false;

        foreach ($trace as $key => $trace_file)
        {
            //never the first one
            if ($key == '0')
            {
                continue;
            }

            if ($parent_found)
            {
                $file = str_replace(".php", "", basename($trace_file['file']));
                $processor = str_replace("{$pos_type}_", "", $file);

                break;
            }

            if (strstr($trace_file['file'], 'class.rdi_hook_handler'))
            {
                $parent_found = true;
            }
        }

        if (!$parent_found)
        {
            return 'no parent';
        }

        if (strstr($file, "_pos_"))
        {
            global $pos;

            return $pos->get_processor($processor);
        }

        if (strstr($file, "_cart_"))
        {
            global $cart;

            $processor = str_replace("{$cart_type}_", "", $file);

            return $cart->get_processor($processor);
        }

        return null;
    }

    /**
     * Any comment containing "@comment" will be added to the table.
     * 
     * @date 04162013
     * 
     * @param string $find
     * @array array $array
     * 
     * 
     * Cleaning added to the class.rdi_db.php
     */
    public function record_comments($hook_name)
    {
        global $rdi_path, $cart, $pos, $debug, $cart_type, $pos_type, $addon_comments;
        echo "<h1>addons comments</h1>";
        $add_on_filename = "{$cart_type}_{$pos_type}_{$hook_name}.php";

        $source = file_get_contents("{$rdi_path}add_ons/{$cart_type}_{$pos_type}_{$hook_name}.php");

        $tokens = token_get_all($source);
        $comment = array(
            T_COMMENT, // All comments since PHP5
            T_ML_COMMENT, // Multiline comments PHP4 only
            T_DOC_COMMENT   // PHPDoc comments      
        );

        foreach ($tokens as $token)
        {
            if (in_array($token[0], $comment) && strstr($token[1], "@comment"))
            {
                // Do something with the comment
                $comments = mysql_escape_string($token[1]);
                //echo $comments;
                $sql = "REPLACE INTO rdi_addons (filename,pos_type,cart_type,comments) values('{$add_on_filename}','{$pos_type}','{$cart_type}','{$comments}')";

                $cart->get_db()->exec($sql);
            }
        }
    }

}

?>