<?php
/**
 * RDi Export Core Class
 * 
 * @author PBliss
 * @author     Paul Bliss <pbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Export
 */
class rdi_export_xml extends rdi_general {

    protected $xmldoc;
    protected $rootChildDoc;
    
     /**
     * Class Constructor
     *
     * @param rdi_db $db
     */
    public function rdi_export_xml($db = '')
    {    
        if ($db)
            $this->set_db($db);
    }

    /**
     * Create the xml document, the root child is the main child of the document
     * @param type $rootChild
     * @param type $attributes
     */
    public function create_document($rootChild, $attributes = array())
    {
        $this->xmldoc = new DOMDocument('1.0', 'UTF-8');

        $createdOn = @' Created on ' . date('Y-m-d') . 'T' . date('H:i:sP') . ' ';
        $comment = $this->xmldoc->createComment($createdOn);
        $this->xmldoc->appendChild($comment);

        
        $this->rootChildDoc = $this->xmldoc->createElement($rootChild);
        if(count($attributes) > 0)
        {
            foreach($attributes as $attribute_key => $attribute_value)
            {
                $domAttribute = $this->xmldoc->createAttribute($attribute_key);
                $domAttribute->value = $this->FixXML($attribute_value);
                $this->rootChildDoc->appendChild($domAttribute);
                
            }
        }

        

        $this->xmldoc->appendChild($this->rootChildDoc);
    }
    
    /**
     * create a child record under the root, 
     * Child name - name of the element
     * @param type $child_name
     * @param type $attributes
     * @return type
     */
    public function create_child($child_name, $attributes = array())
    {
        $child = $this->rootChildDoc->appendChild($this->xmldoc->createElement($child_name));
        
        if(count($attributes) > 0)
        {
            foreach($attributes as $attribute_key => $attribute_value)
            {
                $domAttribute = $this->xmldoc->createAttribute($attribute_key);
                $domAttribute->value = $this->FixXML($attribute_value);
                $this->rootChildDoc->appendChild($domAttribute);
            }
        }
                
        return $child;
    }
    
    public function create_child_with_value($child_name, $value, $attributes = array())
    {
        $child = $this->rootChildDoc->appendChild(new DOMElement($child_name, $value));
        
        if(count($attributes) > 0)
        {
            foreach($attributes as $attribute_key => $attribute_value)
            {
		$attrbute_clean = $this->FixXML($attribute_value);
				
                $child->setAttributeNode(new DOMAttr($attribute_key, $attrbute_clean));
            }
        }
        
        return $child;
    }
    
    public function append_record($child_node, $record_name, $record, $fields = array(), $mapping = array(), $record_value = null)
    {
        $child_record = $child_node->appendChild(new DOMElement($record_name, $record_value));
                
        //if there is a specified mapping use it
        if(count($mapping) > 0)
        {
            foreach($mapping as $record_field => $xml_name)
            {
                $child_record->setAttributeNode(new DOMAttr($xml_name, $record[$record_field]));
            }                
        }
        else if(count($fields) > 0)
        {
            foreach($record as $record_field => $value)
            {
                if(in_array($record_field, $fields))
                {
                    $domAttribute = $this->xmldoc->createAttribute($record_field);
                    $domAttribute->value = $this->FixXML($value);
                    //$this->rootChildDoc->appendChild($domAttribute);
                    
                    $child_record->setAttributeNode($this->rootChildDoc->appendChild($domAttribute));
                }
            }  
        }
        else //or the field name is just the same as the xml 
        {
            foreach($record as $record_field => $value)
            {
                $child_record->setAttributeNode(new DOMAttr($record_field, $value));
            }  
        } 
        
        return $child_record;
    }
    
    /*     
     * append records to a specified dom node
     * node to append to
     * Name of the record 
     * array of the records used to populate the data 
     * fields to use from the record for this xml node
     * mapping of the record data recordfield -> xml name 
     */     
    public function append_records($child_node, $record_name, $records, $fields = array(), $mapping = array())
    {           
        if (is_array($records)) 
        {
            foreach ($records as $record) 
            {
                append_record($child_node, $record_name, $records, $fields = array(), $mapping = array());
            }
        }
    }
        
    public function display_document()
    {
        echo $this->xmldoc->saveXML();                
    }
    
    public function save($path, $file_name)
    {               
        $dirError = false;
        
//        $dirHandle = @opendir(getcwd());
//        while ($file = readdir($dirHandle))             
//        { 
//            echo $file;
//        }
        
        // check that the directory exists and is writable. 
        if (!file_exists($path) || !is_dir($path)) {
            // if the directory cannot be found trigger error
            echo 'Cannot find the directory ' . $path;
            $dirError = true;
        }
        if (!is_writable($path)) {
            // attempt to set chmod
            if (!chmod($path, 0777)) {
                // if the directory is not writable trigger error
                echo 'Cannot find the directory ' . $path;
                $dirError = true;
            }
        }
        
        if(!(stripos(strrev($path), '/') === 0))
        {
            $path .= "/";
        }

        // if directory is available run upload scripts
        if (!$dirError) {
            $file = $path . $file_name;
            
            $handle = fopen($file, 'w');

            if ($handle) {

                $tmp = fwrite($handle, $this->xmldoc->saveXML(null, LIBXML_NOEMPTYTAG));
                //$tmp =  $this->xmldoc->save($file);

                if ($tmp) {
                    echo $file . '<br />';
                } else {
                    echo 'Could not output to ' . $file;
                }

                fclose($handle);
            } else {
                echo 'Cannot open ' . $file;
            }
        }
    }
    
    public function save_document($file_prefix)
    {
         global $out_path;
         
         $this->save($out_path, $file_prefix . "_" . date('Ymdhis') . ".xml");
    }
    
    
    /**
     * This is the function that cleans the xml for export. 
     * To add new characters open a php and paste in the echo ord(""); This will echo a number that an be added to the list.
     * 
     * @param string $value
     * @return string
     */
    public function FixXML($value) 
    {       
        //@setting $cleanXML [0-OFF, 1-ON] This cleans up bad chars from the xml on export.
        global $cleanXML, $encoding;
        
        if(isset($cleanXML) && $cleanXML == 1)
        {
            $value = str_replace("ù", "u", $value);
            $value = str_replace("ā", "a", $value);
            $value = str_replace("ě", "e", $value);
            //$value = str_replace("-", " ", $value);
            $value = str_replace(chr(34), "'", $value);
            $value = str_replace(chr(160), "", $value);
            $value = str_replace("&", "AND", $value);
            $value = str_replace("<", "&lt;", $value);
            $value = str_replace(chr(146), "apos;", $value);
            //$value = str_replace(Hex(93), "x", $value);
            $value = str_replace(">", "&gt;", $value);
            $value = str_replace("…", "..", $value);
            $value = str_replace(chr(147), "'", $value);
            $value = str_replace(chr(148), "..", $value);
            $value = str_replace("–", "..", $value);
            $value = str_replace("­", "..", $value);
            $value = str_replace(chr(16), " ", $value);
            $value = str_replace(chr(22), " ", $value);
            $value = str_replace(chr(17), " ", $value);
            $value = str_replace(chr(18), " ", $value);
            $value = str_replace(chr(5), " ", $value);
            $value = str_replace("·", ".", $value);
            $value = str_replace("é", "e", $value);
            $value = str_replace("ï", "i", $value);
            $value = str_replace("ù", "u", $value);
            $value = str_replace("ā", "a", $value);
            $value = str_replace("ě", "e", $value);
            $value = str_replace("®", "", $value);
            $value = str_replace("—", "", $value);
            $value = str_replace("ü", "", $value);
            $value = str_replace("’", "", $value);
            $value = str_replace("<DIV>", "", $value);
            $value = str_replace("</DIV>", "", $value);

// Extended Uppercase Characters
            $value = str_replace(chr(192), "A", $value);
            $value = str_replace(chr(193), "A", $value);
            $value = str_replace(chr(194), "A", $value);
            $value = str_replace(chr(195), "A", $value);
            $value = str_replace(chr(196), "A", $value);
            $value = str_replace(chr(197), "A", $value);
            $value = str_replace(chr(198), "AE", $value);
            $value = str_replace(chr(199), "C", $value);
            $value = str_replace(chr(200), "E", $value);
            $value = str_replace(chr(201), "E", $value);
            $value = str_replace(chr(202), "E", $value);
            $value = str_replace(chr(203), "E", $value);
            $value = str_replace(chr(204), "I", $value);
            $value = str_replace(chr(205), "I", $value);
            $value = str_replace(chr(206), "I", $value);
            $value = str_replace(chr(207), "I", $value);
            $value = str_replace(chr(208), "D", $value);
            $value = str_replace(chr(209), "N", $value);
            $value = str_replace(chr(210), "O", $value);
            $value = str_replace(chr(211), "O", $value);
            $value = str_replace(chr(212), "O", $value);
            $value = str_replace(chr(213), "O", $value);
            $value = str_replace(chr(214), "O", $value);
            $value = str_replace(chr(217), "U", $value);
            $value = str_replace(chr(218), "U", $value);
            $value = str_replace(chr(219), "U", $value);
            $value = str_replace(chr(220), "U", $value);
            $value = str_replace(chr(221), "Y", $value);

// Extended Lowercase Characters
            $value = str_replace(chr(224), "a", $value);
            $value = str_replace(chr(225), "a", $value);
            $value = str_replace(chr(226), "a", $value);
            $value = str_replace(chr(227), "a", $value);
            $value = str_replace(chr(228), "a", $value);
            $value = str_replace(chr(229), "a", $value);
            $value = str_replace(chr(230), "ae", $value);
            $value = str_replace("Ã", "a", $value);
            $value = str_replace("¼", "", $value);
            $value = str_replace("¤", "", $value);
            $value = str_replace(chr(231), "c", $value);
            $value = str_replace(chr(232), "e", $value);
            $value = str_replace(chr(233), "e", $value);
            $value = str_replace(chr(234), "e", $value);
            $value = str_replace(chr(235), "e", $value);
            $value = str_replace(chr(236), "i", $value);
            $value = str_replace(chr(237), "i", $value);
            $value = str_replace(chr(238), "i", $value);
            $value = str_replace(chr(239), "i", $value);
            $value = str_replace(chr(240), "o", $value);
            $value = str_replace(chr(241), "n", $value);
            $value = str_replace(chr(242), "o", $value);
            $value = str_replace(chr(243), "o", $value);
            $value = str_replace(chr(244), "o", $value);
            $value = str_replace(chr(245), "o", $value);
            $value = str_replace(chr(246), "o", $value);
            $value = str_replace(chr(249), "u", $value);
            $value = str_replace(chr(250), "u", $value);
            $value = str_replace(chr(251), "u", $value);
            $value = str_replace(chr(252), "u", $value);
            $value = str_replace(chr(253), "y", $value);
            $value = str_replace(chr(255), "y", $value);

            // Misc characters
            $value = str_replace(chr(131), " ", $value);	
            $value = str_replace(chr(137), " ", $value);	
            $value = str_replace(chr(145), "`", $value);
            $value = str_replace(chr(154), " ", $value);
            $value = str_replace(chr(163), " ", $value);
            $value = str_replace(chr(169), " ", $value);
            $value = str_replace(chr(171), " ", $value);
            $value = str_replace(chr(179), " ", $value);
            $value = str_replace(chr(182), " ", $value);
            $value = str_replace(chr(184), ",", $value);
            $value = str_replace(chr(188), " ", $value);
            $value = str_replace(chr(216), " ", $value);
            $value = str_replace(chr(248), " ", $value);
            $value = str_replace(chr(188), "", $value);
            $value = str_replace(chr(195), "A", $value);
            $value = str_replace(chr(164), "", $value);
            $value = str_replace('￿￿ß', "b", $value);
            $value = str_replace('￿￿ü', "b", $value);

            $value = str_replace(chr(186), "o", $value);
            $value = substr($value,0,40); // Fields need to be truncated to 40 chars, per PD. 

            $value = $encoding->toLatin1($value);
            
            return $value;
        }
        
        return $value;
    }
}

?>