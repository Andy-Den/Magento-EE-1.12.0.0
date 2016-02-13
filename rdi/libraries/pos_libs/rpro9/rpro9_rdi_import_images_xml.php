<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rdi_import_ecomm_xml
 *
 * @author PMBliss
 */
class rdi_import_images_xml extends rdi_import_xml {

    //put your code here

    public $fileList;
    public $hook_name;
    public $style_sids = array();
    const zip_image_prefix = 'images/img';

    public function __construct($db = '')
    {
        parent::__construct($db);
        $this->hook_name = str_replace("rdi_", "", __CLASS__);
    }

    public function pre_load()
    {
        global $hook_handler;

        $hook_handler->call_hook($this->hook_name . "_" . __FUNCTION__);

        return $this;
    }

    public function post_load()
    {
        global $hook_handler;

        $this->update_attr_code_to_word_mapped_value();

        $hook_handler->call_hook($this->hook_name . "_" . __FUNCTION__);

        return $this;
    }

    public function load()
    {
        $this->style_sids = $this->db_connection->cells("SELECT distinct style_sid FROM {$this->db_lib->get_table_name('in_images')}", 'style_sid');

        $this->pre_load()->get_files()->post_load();

        $this->unzip_images(self::zip_image_prefix);
        
        return $this;
    }

    public function get_files()
    {
        global $inPath;

        $this->fileList = array();

        global $inPath, $rdi_path;

        foreach (glob($rdi_path . $inPath . "/images_*.xml") as $filename)
        {
            $this->fileList[] = filesize($filename) > 0 ? $filename : $file;
        }

        $this->load_files();

        //moves all the images files to the archive
        foreach (glob($rdi_path . $inPath . "/images_*.xml") as $f)
        {
            $archive_path = str_replace($inPath . "/", $inPath . "/archive/", $f);
            rename($f, $archive_path);
        }
        // $row = 0;

        return $this;
    }

    public function load_files()
    {
        global $inPath;

        if (is_array($this->fileList) && !empty($this->fileList))
        {
            asort($this->fileList);

            foreach ($this->fileList as $file)
            {

                // make sure the file exists

                if (file_exists($file))
                {
                    //create a new xml reader
                    $xml_reader = new XMLReader;

                    //clean the file first, these bad characters keep showing up
                    //&#x1F;
                    //&#x1;
                    $_sql_body = array();
                    //open the xml
                    $xml_reader->open($file);

                    $doc = new DOMDocument;

                    // move to the first <Items /> node
                    while ($xml_reader->read() && $xml_reader->name !== 'Style');

                    //read all the item nodes in
                    while ($xml_reader->name === 'Style')
                    {

                        $expanded_node = $xml_reader->expand();

                        if ($expanded_node == null)
                        {
                            //there is an error with the xml, if we continue it will get caught in an infinate loop
                            //echo "error with the xml " . $item_no;
                            break;
                        }
                        else
                        {
                            //get the node from the reader and put into an simple xml object, to make things easier
                            $node = simplexml_import_dom($doc->importNode($expanded_node, true));

                            //one insert per record read
                            $data = array();
                            $data['style_sid'] = $node['style_sid'];

                            if (in_array($data['style_sid'], $this->style_sids))
                            {
                                $this->_echo($data['style_sid']);
                            }
                            else
                            {
                                $this->style_sids[] = $data['style_sid'];

                                $data['attr_code'] = '|STYLE|';

                                $data['type'] = 'T';
                                $data['thumbnail'] = $node['ThumbnailImage'];

                                $data['image1'] = $node['DetailImage1'];
                                $data['image2'] = $node['DetailImage2'];
                                $data['image3'] = $node['DetailImage3'];
                                $data['image4'] = $node['DetailImage4'];

                                $values = $this->data_to_bulk_insert($data);

                                if ($values)
                                {
                                    $this->db_connection->exec("INSERT INTO {$this->db_lib->get_table_name('in_images')} (style_sid, attr_code, type, image, sort) values {$values}");
                                }

                                //get the image images
                                if ($expanded_node->hasChildNodes())
                                {
                                    $this->load_item_attrs($expanded_node->getElementsByTagName('Image'), $data);
                                }
                            }

                            //get the next style
                            $xml_reader->next('Style');
                        }
                    }
                }
            }
        }
    }

    public function load_item_attrs($_xmls, $parent_data)
    {
        foreach ($_xmls as $node)
        {
            $data = array();

            $data['style_sid'] = $parent_data['style_sid'];
            $data['attr_code'] = $this->get_value($node, 'ItemAttr');
            $data['image1'] = $this->get_value($node, 'DetailImage1');
            $data['image2'] = $this->get_value($node, 'DetailImage2');
            $data['image3'] = $this->get_value($node, 'DetailImage3');
            $data['image4'] = $this->get_value($node, 'DetailImage4');
            $data['hex'] = str_replace('#', '', $this->get_value($node, 'SwatchHexValue'));
            $data['swatch'] = $this->get_value($node, 'SwatchImage');
            $data['use_hex'] = $this->get_value($node, 'UseHexValue') == 'True';
            $data['last_modified'] = $this->get_value($node, 'LastModified');

            $values = $this->data_to_bulk_insert($data);

            if ($values)
            {
                $this->db_connection->exec("INSERT INTO {$this->db_lib->get_table_name('in_images')} (style_sid, attr_code, type, image, sort) values {$values}");
            }

            //$this->db_connection->insertAr('rpro_in_images', $data, true);
        }
    }

    public function data_to_bulk_insert($data)
    {
        $_out = array();

        $attr = $this->db_connection->clean($data['attr_code']);

        if (isset($data['thumbnail'],$data['hex']) && strlen($data['thumbnail']) > 0 && $data['hex'] != 'NULL')
        {
            $_out[] = "('{$data['style_sid']}','{$attr}', 'T','{$this->db_connection->clean($data['thumbnail'])}',5)";
        }

        if (isset($data['hex']) && strlen($data['hex']) > 0 && $data['hex'] != 'NULL' && $data['use_hex'])
        {
            $_out[] = "('{$data['style_sid']}','{$attr}', 'H','{$this->db_connection->clean($data['hex'])}',6)";
        }

        if (isset($data['swatch']) && strlen($data['swatch']) > 0 && $data['swatch'] != 'NULL' && !$data['use_hex'])
        {
            $_out[] = "('{$data['style_sid']}','{$attr}', 'S','{$this->db_connection->clean($data['swatch'])}',6)";
        }

        for ($i = 1; $i < 5; $i++)
        {
            $image_field = "image{$i}";

            if (strlen($data[$image_field]) > 0 && $data[$image_field] != 'NULL')
            {
                $_out[] = "('{$data['style_sid']}','{$attr}', 'D','{$this->db_connection->clean($data[$image_field])}',{$i})";
            }
        }
        if (!empty($_out))
        {
            return implode(",", $_out);
        }
        else
        {
            return false;
        }
    }

    public function update_attr_code_to_word_mapped_value()
    {
        $this->db_connection->exec("UPDATE {$this->db_lib->get_table_name('in_images')} images
                                    join rdi_color_size_codes c
                                    on c.related_parent_id = images.style_sid
                                    and c.color_code = images.attr_code
                                    set images.attr_code = c.color
                                    where c.color is not null");
    }
    
    public function unzip_images($prefix)
    {
        global $rdiPath, $inPath, $manager;

        $files = glob("in/{$prefix}*.zip");

        $this->_print_r($files);

        if (!empty($files))
        {
            foreach ($files as $file)
            {
                $manager->unzip_file($file, $rdi_path . $inPath . "/images/");
		unlink($rdi_path . $inPath . "/images/archive/".basename($file));
            }
        }
    }

}

?>
