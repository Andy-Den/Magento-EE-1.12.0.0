<?php

/**
 * Class File extends rdi_load class
 *
 */

/**
 * Image load class
 * Shared methods independent of cart or POS.
 *
 * @author PBliss
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\Image
 */
class rdi_image_load extends rdi_load {

    const NO_SOURCE_IMAGE = 1;
    const REMOVE_IMAGE = 2;
    const NEW_IMAGE = 3;
    const UPDATE_IMAGE = 4;
    const DO_NOTHING = 5;

    public $helper;
    public $test_count = true;
    public $image_types = array('thumbnail' => '', 'swatch' => '', 'detail' => '', 'hex' => '', 'color' => '');

    /**
     *
     * @global type $rdiPath
     * @global type $inPath
     * @global type $manager
     * @param type $prefix
     */
    public function unzip_images($prefix)
    {
        global $rdiPath, $inPath, $manager;

        $files = glob("in/{$prefix}*.zip");

        ////$this->_print_r($files);

        if (!empty($files))
        {
            foreach ($files as $file)
            {
                $manager->unzip_file($file, $rdi_path . $inPath . "/images/");
            }
        }
    }

    /**
     *
     * @global type $helper_funcs
     * @return type
     */
    public function pre_load()
    {
        global $helper_funcs;

        $this->helper = $helper_funcs;

        if (isset($this->zip_image_prefix))
        {
            $this->unzip_images($this->zip_image_prefix);
        }

        return parent::pre_load();
    }

    /**
     * returns true
     * if the images are different.
     * No image in cart, not file not found or database record missing.
     * NOT SURE about removing images.
     * modifies the current_image to the right path.
     * switch statement, Remove Image, No Image Found, Assign New Image, Update old Image.
     * @global type $rdiPath
     * @global type $inPath
     * @param array $image
     * @param type $path
     * @return type
     */
    public function move_image(&$image, $path)
    {
        global $rdiPath, $inPath;
        $move_file = false;


        $has_new_image = strlen(trim($image['new_image'])) > 0;
        $has_current_image = strlen(trim($image['current_image'])) > 0;


        //check if the file is there.
        $current_image = $has_current_image ? $path . $image['current_image'] : 'xxx';

        $new_image = $has_new_image ? $rdiPath . $inPath . "/images/" . $image['new_image'] : 'xxx';



        //by this point we have
        //$this->_echo("Has New Image");
        //$this->_var_dump($has_new_image);
        //$this->_var_dump($new_image);
        //$this->_echo("Has Current Image");
        //$this->_var_dump($has_current_image);
        //$this->_var_dump($current_image);
        //exit;
        //$this->_echo(__LINE__);
        //do nothing.
        if (!$has_new_image && !$has_current_image)
        { //$this->_echo("Do Nothing");
            return self::DO_NOTHING;
        }
        elseif (!$has_new_image && $has_current_image)
        { //$this->_echo("Remove image");
            return self::REMOVE_IMAGE;
        }
        elseif ($has_new_image && !$has_current_image)
        {//$this->_echo(__LINE__);
            //do new image stuff. below
        }
        elseif ($has_new_image && $has_current_image)
        {
            if (!file_exists($new_image))
            {
                return self::DO_NOTHING;
            }
            //$this->_echo(__LINE__);
            //check if images are the same MD5s
            //if there is a current image there, get and file exists get the MD5, otherwise its true.
            $md5_current_image = md5_file($current_image);
            $md5_new_image = md5_file($new_image);
            //$this->_echo($current_image);
            //$this->_echo($new_image);
            if ($md5_current_image !== $md5_new_image)
            {//$this->_echo("Image Changed. Copy to new place and rename. " . __LINE__);
                //copy to destination.
                copy($new_image, $current_image);
            }

            @rename($new_image, dirname(dirname($new_image)) . "/archive/images/" . basename($new_image));

            return self::DO_NOTHING;
        }

        //$this->_echo($new_image);
        if (!file_exists($new_image))
        {
            return self::DO_NOTHING;
        }
        //$this->_echo(__LINE__);
        //new image
        //add the extension
        $new_image_pathinfo = pathinfo($new_image);
        //$this->_print_r($new_image_pathinfo);
        $image['current_image'] = "{$image['image_base_name']}" . (isset($image['color']) ? "_{$image['color']}" : "") . "_{$image['position']}.{$new_image_pathinfo['extension']}";

        $current_image = $path . "/{$image['current_image']}";

        //$this->_echo($current_image);
        //$this->_echo($new_image);
        copy($new_image, $current_image);

        @rename($new_image, dirname(dirname($new_image)) . "/archive/images/" . basename($new_image));

        return self::NEW_IMAGE;
    }

    /**
     * Not used.
     * @global type $cart
     * @global type $pos
     * @global type $field_mapping
     * @global type $pos_type
     * @return \rdi_image_load
     */
    public function load_product_images()
    {
        global $cart, $pos, $field_mapping, $pos_type;

        //for now cp will do their images in post load module
        if ($pos_type == 'cp')
        {
            return $this;
        }

        //get the file path from the cart for the images folder
        $image_path = $cart->get_processor("rdi_cart_image_load")->get_image_path();

        //get a parameters list from the cart, it needs to basically link the current image into our query for the products in the staging table
        $image_load_parameters = $cart->get_processor("rdi_cart_image_load")->get_image_load_parameters();

        //add in the image field, product_image MUST BE MAPPED NOW
        $image_load_parameters['fields'] .= ", " . $field_mapping->map_field('product', 'product_image') . " as 'image'";

        //get a list of image files / related_id for the products from the pos
        $image_data = $pos->get_processor("rdi_pos_product_load")->get_data('', '', $image_load_parameters, 'image', false);

        //create the archive if it doesnt exist
        if (!file_exists('in/archive/images'))
        {
            mkdir("in/archive/images");
        }

        if (is_array($image_data))
        {
            if (isset($image_debug) && $image_debug == 1)
            {
                echo "<pre>";
                print_r($image_data);
                echo "</pre>";
            }

            foreach ($image_data as $image)
            {
                $image['source_image'] = 'in/images/' . $image['image'];
                $image['archive_image'] = 'in/archive/images/' . $image['image'];

                if (isset($image_debug) && $image_debug == 1)
                {
                    echo "<br>does source image exist:{$image['related_id']} -> [{$image['source_image']}] <br>";
                }

                //check for the file existance the || adds check for uppercase extensions and file_exists on a linux server.
                if (file_exists(trim($image['source_image'])) || file_exists(trim('in/images/' . strtoupper($image['image']))))
                {
                    //if there is a current_image value, then we need to check if there is need to update the image
                    if (isset($image[$image_load_parameters['current_image']]))
                    {
                        //check the current file exists
                        if (file_exists($image_path . $image[$image_load_parameters['current_image']]))
                        {
                            //md5 the files, find if there are new ones
                            if (md5_file('in/images/' . $image['image']) == md5_file($image_path . $image[$image_load_parameters['current_image']]))
                            {
                                //if no need to update call a continue on the loop
                                @rename($image['source_image'], $image['archive_image']);
                                continue;
                            }
                        }
                    }

                    //pass a list to the cart to process of the ones that are new


                    $cart->get_processor("rdi_cart_image_load")->load_image($image);
                }
            }
        }
        return $this;
    }

    /**
     *
     * @global type $cart
     * @global type $pos
     * @global type $benchmarker
     * @return \rdi_image_load
     */
    public function load_category_images()
    {
        global $cart, $pos, $benchmarker;

        $benchmarker->set_start_time(__CLASS__, __FUNCTION__);

        $cart_proc = $cart->get_processor("rdi_cart_image_load");

        $pos_proc = $pos->get_processor("rdi_pos_image_load");

        //get the file path from the cart for the images folder
        $cart_proc->get_image_path();

        //returns list and if not a list returns false.
        $_images_list = $pos_proc->get_category_images_list();



        if ($_images_list)
            $cart_proc->assign_category_images($_images_list);

        $benchmarker->set_end_time(__CLASS__, __FUNCTION__);

        return $this;
    }

}

?>
