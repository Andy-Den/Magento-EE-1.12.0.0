<?php
/**
 * Common useful functions that will be common among areas of the code
 */

/**
 * Helper Functions
 * No database interaction allowed here.
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\HelperFunctions
 */
class rdi_helper_funcs {

    /**
     *
     * @global type $field_mapping
     * @global type $cart
     * @global type $pos
     * @param type $special_handling_Data
     * @param type $value
     * @param type $record_data
     * @param type $field_type
     * @param type $field_class
     * @param type $entity_type
     * @return string
     */
    public function process_special_handling($special_handling_Data, $value, $record_data = array(), $field_type = '', $field_class = array(), $entity_type = array())
    {
        global $field_mapping, $cart, $pos;

        //swap out the comma that has been escaped
        $special_handling_Data = str_replace('\,', '[COMMA]', $special_handling_Data);

        $commands = explode(",", $special_handling_Data);

        foreach ($commands as $command)
        {
            //swap the comma back
            $command = str_replace('[COMMA]', ',', $command);

            switch ($command)
            {
//                case 'no_null'
//                    break;

                case 'null':
                    $value = 'null';
                    break;

                case 'no_quote' :
                    $value = str_replace("'", '', $value);
                    $value = str_replace('"', '', $value);
                    $value = str_replace('  ', ' ', $value);
                    break;

                case "lower":
                    $value = strtolower($value);
                    break;

                case "upper":
                    $value = strtoupper($value);
                    break;

                case "title":
                    $value = $this->title_case($value);
                    break;

                case "sentence":
                    $value = ucfirst(strtolower($value));
                    break;

                case "no_space":
                    $value = str_replace(' ', '-', $value);
                    break;

                case "rand":
                    $value = mt_rand();
                    break;

                case "abs":
                    $value = number_format(abs($value), 4, ".", "");
                    break;

                case "zero_null":
                    //if the value is 0 then set it null
                    if ($value == 0)
                        $value = 'null';
                    break;

                case "null_zero":
                    //if the value is 0 then set it null
                    if ($value == 'null')
                        $value = 0;
                    break;

                case "state_abv":
                    $value = $this->getStateAbbreviation($value);
                    break;

                case "date":
                    $value = str_replace(' ', 'T', trim($value)) . date('P');
                    break;

                case "gmdate":
                    $value = gmdate("Y-m-d H:i:s", $value);
                    break;

                case "telephone":
                    $value = $this->telephone($value);
                    break;

                case "telephone1":
                    $value = $this->telephone($value, "NO Parans");
                    break;

                case "removedoubledash":
                    $value = str_replace("--", "-", $value);
            }

            //special commands that have parameters
            if (strpos($command, "append(") !== false)
            {
                //break down the parameters
                preg_match("/append\((.*?)\)/", $command, $matches);

                $value .= $matches[1];
            }

            if (strpos($command, "find_replace(") !== false)
            {
                //break down the parameters
                preg_match("/find_replace\((.*?)\)/", $command, $matches);

                $find_replace_array = unserialize($matches[1]);

                if (!empty($find_replace_array))
                {
                    foreach ($find_replace_array as $input => $out_put)
                    {
                        $value = str_replace($input, $out_put, $value);
                    }
                }
                //echo $value;
            }

            if (strpos($command, "right(") !== false)//right  keeps the right n most characters
            {
                //break down the parameters
                preg_match("/right\((.*?)\)/", $command, $matches);

                $value = substr($value, -$matches[1]);
            }

            if (strpos($command, "left(") !== false)//left  keeps the left n most characters
            {
                //break down the parameters
                preg_match("/left\((.*?)\)/", $command, $matches);

                $value = substr($value, $matches[1]);
            }

            //strip characters, accepts paramters | delim characters to remove
            if (strpos($command, "strip_characters(") !== false)
            {
                //break down the parameters
                preg_match("/strip_characters\((.*?)\)/", $command, $matches);

                if (is_array($matches) && count($matches) > 0)
                {
                    //break down the parameters passed
                    $params = explode('|', $matches[1]);

                    foreach ($params as $param)
                    {
                        if ($param == "comma")
                        {
                            $value = str_replace(',', '', $value);
                        }
                        else
                        {
                            $value = str_replace($param, '', $value);
                        }
                    }
                }
            }

            if (strpos($command, "divide(") !== false)
            {
                //break down the parameters
                $parameters = preg_match("/divide\((.*?)\)/", $command, $matches);

                if (is_array($matches) && count($matches) > 0)
                {
                    //break down the parameters passed
                    $params = explode('|', $matches[1]);

                    if (array_key_exists($params[0], $record_data) && array_key_exists($params[1], $record_data))
                    {
                        $value = $record_data[$params[0]] / $record_data[$params[1]];
                    }
                }
            }

            if (strpos($command, "add(") !== false)
            {
                //break down the parameters
                preg_match("/add\((.*?)\)/", $command, $matches);

                $value .= $matches[1];

                if (is_array($matches) && count($matches) > 0)
                {
                    //break down the parameters passed
                    $params = explode('|', $matches[1]);

                    if (array_key_exists($params[0], $record_data) && array_key_exists($params[1], $record_data))
                        $value = $record_data[$params[0]] + $record_data[$params[1]];
                }
            }

            if (strpos($command, "subtract(") !== false)
            {
                //break down the parameters
                preg_match("/subtract\((.*?)\)/", $command, $matches);

                $value .= $matches[1];

                if (is_array($matches) && count($matches) > 0)
                {
                    //break down the parameters passed
                    $params = explode('|', $matches[1]);

                    if (array_key_exists($params[0], $record_data) && array_key_exists($params[1], $record_data))
                        $value = $record_data[$params[0]] - $record_data[$params[1]];
                }
            }

            if (strpos($command, "multiply(") !== false)
            {
                //break down the parameters
                preg_match("/multiply\((.*?)\)/", $command, $matches);

                $value .= $matches[1];

                if (is_array($matches) && count($matches) > 0)
                {
                    //break down the parameters passed
                    $params = explode('|', $matches[1]);

                    if (array_key_exists($params[0], $record_data) && array_key_exists($params[1], $record_data))
                        $value = $record_data[$params[0]] * $record_data[$params[1]];
                }
            }

            //combine command
            //this field really is the combination of a few other fields, this command tells us that we need to combine the defined fields to make this one work
            //combine(?| |field_dates_start_time-?| |field_dates_end_time)
            if (strpos($command, "combine(") !== false)
            {
                $orig_value = $value;

                $new_value = '';

                //break down the parameters
                preg_match("/combine\((.*?)\)/", $command, $matches);

                if (isset($matches[1]))
                {
                    //if the value is an array numeric values become the indexes for the value in the array
                    if (is_array($value)) //this is where it gets confusing, since arrays can have logical grouping
                    {
                        //break up the fields that will be combined
                        $groups = explode('-', $matches[1]);

                        for ($g = 0; $g < sizeof($groups); $g++)
                        {
                            $new_value = '';

                            //break up the fields that will be combined
                            $params = explode('|', $groups[$g]);

                            foreach ($params as $param)
                            {
                                if ($param == "?")
                                {
                                    if (isset($orig_value[$g]))
                                    {
                                        $new_value .= $orig_value[$g];
                                    }
                                }
                                else if (array_key_exists($param, $record_data))
                                {
                                    $new_value .= $record_data[$param];
                                }
                                else
                                {
                                    $new_value .= $param;
                                }
                            }

                            $value[$g] = $new_value;
                        }
                    }
                    else
                    {

                        //break up the fields that will be combined
                        $params = explode('|', $matches[1]);

                        foreach ($params as $param)
                        {
                            //? means this value

                            if ($param == "?")
                            {
                                $new_value .= $orig_value;
                            }
                            else if (array_key_exists($param, $record_data))
                            {
                                if (is_array($record_data[$param]))
                                {
                                    $new_value = $record_data[$param];
                                }
                                else
                                {
                                    $new_value .= $record_data[$param];
                                }
                            }
                            else
                            {
                                if (is_array($param))
                                {
                                    $new_value = $param;
                                }
                                else
                                {
                                    $new_value .= $param;
                                }
                            }
                        }

                        $value = $new_value;
                    }
                }
            }

            //insert command
            //insert the value at the specified index
            if (strpos($command, "insert(") !== false)
            {
                //break down the parameters
                preg_match("/insert\((.*?)\)/", $command, $matches);

                if (isset($matches[1]))
                {
                    $params = explode('|', $matches[1]);

                    //support arrays
                    if (is_array($value))
                    {
                        for ($i = 0; $i < sizeof($value); $i++)
                        {
                            $value[$i] = substr_replace($value[$i], $params[0], $params[1], 0);
                        }
                    }
                    else
                    {
                        $value = substr_replace($value, $params[0], $params[1], 0);
                    }
                }
            }

            //explode command
            //explode the value into an array based on the passed in value
            if (strpos($command, "explode(") !== false)
            {
                //break down the parameters
                preg_match("/explode\((.)\)/", $command, $matches);

                if (isset($matches[1]))
                {
                    //support arrays
                    if (is_array($value))
                    {
                        for ($i = 0; $i < sizeof($value); $i++)
                        {
                            $value[$i] = explode($matches[1], $value[$i]);
                        }
                    }
                    else
                    {
                        $value = explode($matches[1], $value);
                    }
                }
            }

            //is(fieldname:value|true|false)
            //is(field:value;field:value;field:value|true|false);
            //is(!field:value;field:value;field:value|true|false);
            //the field is the value in the array of the record that is passed in
            //result values will be either
            //a value in quotes ''
            //the original value passed through as $
            //a field name to use to get the value from, the name of the field
            //ie
            //is(field:value|$|'') so on false pass through nothing, but true use the original value

            if (strpos($command, "is(") !== false)
            {
                //break down the parameters
                preg_match("/is\((.*?)\)/", $command, $matches);

                if (is_array($matches) && count($matches) > 0)
                {
                    //break down the parameters passed
                    $params = explode('|', $matches[1]);

                    //default to looking for one value
                    $match_statements[] = $params[0];

                    //see if there is multiple and split them
                    if (strpos($params[0], ";") !== false)
                    {
                        $match_statements = array_filter(explode(';', $params[0]));
                    }

                    //regex match
                    if (strpos($command, "/") === 0 && stripos(strrev($command), '/') === 0)
                    {
                        //todo
                    }
                    else
                    {
                        $match = 0;

                        //validate the field comparisons find the missing values so we can query the data to get them all at once


                        foreach ($match_statements as $statement)
                        {

                            //break down the statement
                            $s = explode(':', $statement);

                            //see if the field we are comparing to exists
                            if (!array_key_exists($s[0], $record_data))
                            {
                                if ($s[0] != '')
                                {
                                    //doesnt, make an attempt to find the value

                                    if ($field_type == "product")
                                    {
                                        //get the cart insert parameters, as we will treat this as an insert
//                                        $product_insert_parameters = $cart->get_processor("rdi_cart_product_load")->get_product_insert_parameters($field_class, $entity_type);
//
//                                        //nuke the where clause
//                                        $product_insert_parameters['where'] = '';
//
//                                        //set this field as being the one that we need
//                                        $product_insert_parameters['update_field'] = $s[0];
//                                        $product_data = $pos->get_processor("rdi_pos_product_load")->get_product_data($field_class, $entity_type['product_type'], $product_insert_parameters);
//
//                                        if($product_data)
//                                        {
//                                            if(array_key_exists($s[0], $record_data))
//                                            {
//
//                                                //pass the value into our array
//                                                $record_data[$s[0]] = $product_data[0][$s[0]];
//                                            }
//                                        }
                                    }
                                    else if ($field_type == "catalog")
                                    {

                                    }
                                    else if ($field_type == "customer")
                                    {

                                    }
                                    else if ($field_type == "order")
                                    {

                                    }
                                }
                            }

                            if (isset($s[1]) && array_key_exists($s[0], $record_data))
                            {
                                if ($s[1] == 'null' && is_null($record_data[$s[0]]))
                                {
                                    $match++;
                                }
                                else if (isset($record_data[$s[0]]))
                                {
                                    if (substr($s[0], 1) == '!' && $record_data[$s[0]] != $s[1])
                                    {
                                        $match++;
                                    }
                                    else if ($record_data[$s[0]] == $s[1])
                                    {
                                        $match++;
                                    }
                                }
                            }
                        }

                        if (count($match_statements) == $match)
                        {
                            if ($params[1] != '$')
                            {
                                if (strpos($params[1], "'") === 0 && stripos(strrev($params[1]), "'") === 0)
                                {
                                    $value = substr($params[1], 1);
                                    $value = substr($value, 0, -1);
                                }
                                else if (isset($record_data[$params[1]]))
                                {
                                    $value = $record_data[$params[1]];
                                }
                            }
                        }
                        else
                        {
                            if ($params[2] != '$')
                            {
                                if (strpos($params[2], "'") === 0 && stripos(strrev($params[2]), "'") === 0)
                                {
                                    $value = substr($params[2], -1, 0);
                                    $value = substr($params[2], 0, -1);
                                }
                                else if (isset($record_data[$params[2]]))
                                {
                                    $value = $record_data[$params[2]];
                                }
                            }
                        }
                    }
                }
            }

            //to do update the if command to support the ! not character
            //compare the related pos field to the value from the cart, true if they are equal, pass the true value, false value otherwise, if there is one
            // if(test|true|false),if(test2|true2|false2),if(test3|true3|false3)
            if (strpos($command, "if(") !== false)
            {
                //break down the parameters
                preg_match("/if\((.*?)\)/", $command, $matches);

                if (is_array($matches) && count($matches) > 0)
                {
                    $params = explode('|', $matches[1]);

                    //regex compare
                    //only if we are wrapped in / / since regex must be
                    if (strpos($command, "/") === 0 && stripos(strrev($command), '/') === 0)
                    {
                        if (preg_match($params[0], $command) === 1)
                        {
                            $value = $params[1];
                            break;
                        }
                        else if (isset($params[2])) //prevent setting the alt value if there isnt one
                            $value = $params[2];
                    }
                    else
                    {
                        //straight compare
                        if ($params[0] == $value)
                        {
                            $value = $params[1];

                            //break from the command loop when we hit a positive result on this command,
                            //so keep in mind to structure things to work with this, if statements are last
                            break;
                        }
                        else if (isset($params[2])) //prevent setting the alt value if there isnt one
                            $value = $params[2];
                    }
                }
            }

//            if(strpos($command, "remap(") == 0)
//            {
//                preg_match("remap(.*?)", $command, $matches);
//
//
//            }
        }

        return $value;
    }

    //swaps out some common chars and removes all the rest that are bad
    public function remove_non_ascii($string)
    {
        //Specific string replaces for ellipsis, etc that you dont want removed but replaced
        $theBad = array("“", "”", "‘", "’", "…", "—", "–");
        $theGood = array("\"", "\"", "'", "'", "...", "-", "-");
        $string = str_replace($theBad, $theGood, $string);

        //$output = preg_replace('/[^(\x20-\x7F)]*/','', $string);

        return $string;
    }

    public function quote($value)
    {
        $value = "'" . str_replace("'", "''", $value) . "'";
        $value = str_replace("\''", "\'", $value);
        return $value;
    }

    //clear out the files from the archive that are older than the set time length
    public function pruge_archive()
    {
        global $archive_length, $rdi_path, $inPath;

        $dead_time = time() + ($archive_length * 24 * 60 * 60);

        if (is_dir($rdi_path . $inPath . "/archive"))
        {
            $d = dir($rdi_path . $inPath . "/archive");
            while (false !== ($entry = $d->read()))
            {
                $filepath = $rdi_path . $inPath . "/archive" . "/{$entry}";

                // could do also other checks than just checking whether the entry is a file
                if (is_file($filepath) && filectime($filepath) > $dead_time)
                {
                    //delete the file
                    unlink($filepath);
                }
            }
        }
    }

    //setup the ifnull string based on a list of 2 , fields
    public function sift_field_to_alt($pos_field, $alt_field)
    {
        $p_fields = explode(",", $pos_field);
        $alt_fields = explode(",", $alt_field);

        $new_field = "";

        foreach ($p_fields as $idx => $f)
        {
            $new_field .= "ifnull({$f}, {$alt_fields[$idx]}),";
        }

        return substr($new_field, 0, -1);
    }

    public function getStateAbbreviation($stateIn)
    {
        $stateArray = array(
            "ALABAMA" => "AL",
            "ALASKA" => "AK",
            "AMERICAN SAMOA" => "AS",
            "ARIZONA" => "AZ",
            "ARKANSAS" => "AR",
            "ARMED FORCES AFRICA" => "AE",
            "ARMED FORCES AMERICAS" => "AA",
            "ARMED FORCES CANADA" => "AE",
            "ARMED FORCES EUROPE" => "AE",
            "ARMED FORCES MIDDLE EAST" => "AE",
            "ARMED FORCES PACIFIC" => "AP",
            "CALIFORNIA" => "CA",
            "COLORADO" => "CO",
            "CONNECTICUT" => "CT",
            "DELAWARE" => "DE",
            "DISTRICT OF COLUMBIA" => "DC",
            "FEDERATED STATES OF MICRONESIA" => "FM",
            "FLORIDA" => "FL",
            "GEORGIA" => "GA",
            "GUAM" => "GU",
            "HAWAII" => "HI",
            "IDAHO" => "ID",
            "ILLINOIS" => "IL",
            "INDIANA" => "IN",
            "IOWA" => "IA",
            "KANSAS" => "KS",
            "KENTUCKY" => "KY",
            "LOUISIANA" => "LA",
            "MAINE" => "ME",
            "MARSHALL ISLANDS" => "MH",
            "MARYLAND" => "MD",
            "MASSACHUSETTS" => "MA",
            "MICHIGAN" => "MI",
            "MINNESOTA" => "MN",
            "MISSISSIPPI" => "MS",
            "MISSOURI" => "MO",
            "MONTANA" => "MT",
            "NEBRASKA" => "NE",
            "NEVADA" => "NV",
            "NEW HAMPSHIRE" => "NH",
            "NEW JERSEY" => "NJ",
            "NEW MEXICO" => "NM",
            "NEW YORK" => "NY",
            "NORTH CAROLINA" => "NC",
            "NORTH DAKOTA" => "ND",
            "NORTHERN MARIANA ISLANDS" => "MP",
            "OHIO" => "OH",
            "OKLAHOMA" => "OK",
            "OREGON" => "OR",
            "PALAU" => "PW",
            "PENNSYLVANIA" => "PA",
            "PUERTO RICO" => "PR",
            "RHODE ISLAND" => "RI",
            "SOUTH CAROLINA" => "SC",
            "SOUTH DAKOTA" => "SD",
            "TENNESSEE" => "TN",
            "TEXAS" => "TX",
            "UTAH" => "UT",
            "VERMONT" => "VT",
            "VIRGINIA" => "VA",
            "VIRGIN ISLANDS" => "VI",
            "WASHINGTON" => "WA",
            "WEST VIRGINIA" => "WV",
            "WISCONSIN" => "WI",
            "WYOMING" => "WY",
            "ALBERTA" => "AB",
            "BRITISH COLUMBIA" => "BC",
            "MANITOBA" => "MB",
            "NEW BRUNSWICK" => "NB",
            "NEWFOUNDLAND AND LABRADOR" => "NL",
            "NEWFOUNDLAND" => "NL",
            "NORTHWEST TERRITORIES" => "NT",
            "NOVA SSCOTIA" => "NS",
            "NUNAVIT" => "NU",
            "ONTARIO" => "ON",
            "PRINCE EDWARD ISLAND" => "PE",
            "QUEBEC" => "QC",
            "SASKATCHEWAN" => "SK",
            "YUKON" => "YT",
        );

        $key = strtoupper($stateIn);

        if (array_key_exists($key, $stateArray))
        {
            return( $stateArray[$key] );
        }
        else
        {
            return $stateIn;
        }
    }

    //This will change a time from server time to East Coast USA if not specified.
    public function changedatetime($time, $timezone_to = '', $timezone = '', $format = 'Y-m-d H:i:s')
    {
        if ($timezone == '')
        {
            $timezone = date_default_timezone_get();
        }


        if ($timezone_to == '' || $timezone_to == NULL)
        {
            $timezone = 'Etc/GMT+5';
        }

        $datetime = new DateTime($time, new DateTimeZone($timezone));


        //create new timezone with parameter 2 as timezone
        $time_zone_to = new DateTimeZone($timezone_to);
        $datetime->setTimeZone($time_zone_to);


        //set the format as parameter 3 and set the value as this.
        return $datetime->format($format);
    }

    /**
     * Transform a string to title case.
     * @param string $string
     * @param array $exceptions
     * @return string
     */
    public function title_case($string, $exceptions = array('to', 'a', 'the', 'and', 'this', 'from', 'of', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'iPhone', 'iMac'))
    {
        $preps = array('to', 'a', 'the', 'and', 'this', 'from', 'of');

        $words = explode(" ", $string);
        $newwords = array();

        foreach ($words as $key => $word)
        {
            if (!in_array($word, $exceptions))
            {
                $word = strtolower($word);
                $word = ucfirst($word);
            }

            if ($key > 0 && in_array(strtolower($word), $preps))
            {
                $word = strtolower($word);
            }

            array_push($newwords, $word);
        }

        return ucfirst(join(" ", $newwords));
    }

    public function makeDecimal($value)
    {
        $strip = rtrim(ltrim($value, '"'), '"');
        if (is_numeric($strip))
        {
            $one = explode(".", $strip);
            if (!isset($one[1]))
            {
                $one[1] = "00";
            }
            $go = implode(".", $one);
            return $go;
        }
        else
        {
            return $value;
        }
    }

    public function generate_keywords($str)
    {
        $returnArr = array();
        $returnStr = '';
        $stopWords = array('i', 'a', 'about', 'an', 'and', 'are', 'as', 'at', ' be', 'by', 'com', 'de', 'en', 'for', 'from', 'how', 'in', ' is', 'it', 'la', 'of', 'on', 'or', 'that', 'the', 'this', 'to', 'was', 'what', 'when', 'where', 'who', 'will', 'with', 'und', 'the', 'www', 'you');
        $str = trim($str);
        $str = $this->remove_non_ascii($str);
        $str = strtolower($str);

        preg_match_all('/\b(?:(?!%REGEX%)\w)+\b/i', $str, $matchWords);
        $matchWords = $matchWords[0];
        if ($matchWords)
        {
            foreach ($matchWords as $key => $item)
            {
                if ($item == '' || in_array($item, $stopWords) || strlen($item) <= 2)
                {
                    unset($matchWords[$key]);
                }
                else
                {
                    if (!in_array($matchWords[$key], $returnArr))
                    {
                        $returnArr[] = $matchWords[$key];
                    }
                }
            }
        }
        if ($returnArr)
        {
            $count = count($returnArr);
            for ($i = 0; $i < $count; $i++)
            {
                $returnStr .= $returnArr[$i];
                $tmp = $count - 1;
                if ($i != $tmp)
                {
                    $returnStr .= ',';
                }
            }
        }

        return $returnStr;
    }

    /*
     * This function returns an array with the tracking number, carrier_title, carrier_code
     * Attempts to resolve the Method from the tracking number alone.
     */

    public function tracking_to_method($tracking_number)
    {
        $tracking_number = str_replace(" ", "", $tracking_number);

        $shipping_data = array();
        $shipping_data['tracking_number'] = $tracking_number;

        if (
                (strlen($tracking_number) > 10) && (substr($tracking_number, 0, 2) == '1Z') ||
                (strlen($tracking_number) == 11) && (
                (substr($tracking_number, 0, 1) == 'T') ||
                (substr($tracking_number, 0, 1) == 'D')
                )
        )
        {
            $shipping_data['carrier_title'] = "United Parcel Service";
            $shipping_data['carrier_code'] = "ups";
            $shipping_data['tracking_url'] = "http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums={$tracking_number}";
        }
        elseif (strlen($tracking_number) == 11)
        {
            $shipping_data['carrier_title'] = "DHL Express";
            $shipping_data['carrier_code'] = "dhlint";
            $shipping_data['tracking_url'] = "http://track.dhl-usa.com/TrackByNbr.asp?ShipmentNumber={$tracking_number}";
        }
        elseif (strlen($tracking_number) == 12)
        {
            $shipping_data['carrier_title'] = "Federal Express";
            $shipping_data['carrier_code'] = "fedex";
            $shipping_data['tracking_url'] = "http://www.fedex.com/Tracking?ascend_header=1&clienttype=dotcom&cntry_code=us&language=english&action=track&tracknumbers={$tracking_number}";
        }
        elseif (strlen($tracking_number) == 22)
        {
            $shipping_data['carrier_title'] = "United States Postal Service";
            $shipping_data['carrier_code'] = "usps";
            $shipping_data['tracking_url'] = "https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1={$tracking_number}";
        }
        else
        {
            $shipping_data['carrier_title'] = "Custom";
            $shipping_data['carrier_code'] = "custom";
            $shipping_data['tracking_url'] = "";
        }

        return $shipping_data;
    }

    /**
     * @author PMB <pmbliss@retaildimensions.com>
     * @comment Creates a formated tellphone number.
     * @param string $number
     * @return string
     */
    public function telephone($number, $format = "")
    {
        if ($format == "NO Parans")
        {
            return preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', '$1 $2-$3', $number);
        }

        return preg_replace('~.*(\d{3})[^\d]*(\d{3})[^\d]*(\d{4}).*~', '($1) $2-$3', $number);
    }

    public function find_between_strings($string, $first, $second)
    {
        $startsAt = strpos($string, $first) + strlen($first);
        $endsAt = strpos($string, $second, $startsAt);
        $result = substr($string, $startsAt, $endsAt - $startsAt);
        return $result;
    }

    public function excel_date($number)
    {
        list($days, $fraction) = explode(".", $number);

        $date = @date("m-d-Y h:i:s", strtotime("+{$days} day", @strtotime('01-01-1900')));


        return $date;
    }

    public function echo_message($message, $level = 1)
    {
        $hyphens = $level * 5;
        echo str_pad(str_repeat("-", $hyphens) . $message, 75, "-", STR_PAD_RIGHT) . "&nbsp;<br />";
    }
    
    
    static public function str_to_url_key($string)
    {
        $string = str_replace("#", "", $string);
        $string = str_replace('"', "", $string);
        $string = str_replace("'", "", $string);
        $string = str_replace("/", "", $string);
        return preg_replace('#[^0-9a-z]+#i', '-', strtr($string, self::$_converttable));
    }

}

?>
