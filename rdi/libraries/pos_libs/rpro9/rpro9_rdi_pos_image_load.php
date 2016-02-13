<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of rpro8_rdi_pos_image_load
 *
 * @author PMBliss
 */
class rdi_pos_image_load extends rdi_image_load {

    const PRODUCT_IMAGE_MODE = 'table'; //modes are table, file and none.
    const CATEGORY_IMAGE_MODE = 'file';
    const MAIN_TABLE = 'rpro_in_images';
    const MAIN_ALIAS = 'style_image';
    const MAIN_KEY = 'style_sid';
    const MAIN_FILE = 'image';
    const MAIN_SORT = 'sort';
    const MAIN_ORDER_BY = 'sort';
    const MAIN_TYPE = 'type';
    const MAIN_CRITERIA = "AND style_image.attr_code = '|STYLE|'  AND style_image.type IN('T','D')";
    const COLOR_TABLE = 'rpro_in_images';
    const COLOR_ALIAS = 'color_image';
    const COLOR_FIELD = 'attr_code'; //location of the name of the color associated to the color
    const COLOR_KEY = 'item_sid'; //Item id, used it they need to be assigned to a child product.
    const COLOR_PARENT_KEY = 'style_sid'; //parent id
    const COLOR_FILE = 'image';
    const COLOR_SORT = 'sort';
    const COLOR_LABEL = 'attr_code';
    const COLOR_CRITERIA = "AND color_image.attr_code != '|STYLE|' AND color_image.type NOT IN('H','S')";
    const COLOR_ORDER_BY = 'style_sid, attr_code';
    const COLOR_GROUP_BY = 'style_sid, attr_code, color_image.sort';
    const SWATCH_TABLE = 'rpro_in_images';
    const SWATCH_ALIAS = 'swatches';
    const SWATCH_KEY = 'style_sid';
    const SWATCH_FILE = 'swatch';
    const SWATCH_TYPE = 'type';
    const SWATCH_CRITERIA = "AND color_image.attr_code != '|STYLE|' AND color_image.type IN('H','S')";
    //not used yet.
    const CATEGORY_TABLE = 'smyth_in_graphics';
    const CATEGORY_ALIAS = 'graphics';
    const CATEGORY_KEY = 'style_id';
    const CATEGORY_FILE = 'file_name';
    const zip_image_prefix = 'images/img';

    public $image_types = array('thumbnail' => 'T', 'swatch' => 'S', 'detail' => 'D', 'hex' => 'H', 'color' => 'D');

    //image options to support
    /*
      load main images
      load color images
      load swatch images
      load category images
     */

    // functions for completeness
    public function pos_load()
    {
        return $this;
    }

    //get a list of images in categories.
    // return array("image"=>value, "related_id"=>value)
    public function get_category_images_list()
    {
        global $rdiPath, $inPath;

        $this->_echo("Load Category Images needs work");

        return false;

        $_file = glob("{$rdiPath}{$inPath}/images/cat_*.*");

        if (is_array($_file))
        {
            foreach ($_file as $key => $file)
            {
                list($related_id, $extension) = explode(".", str_replace("cat_", "", basename($file)));

                $_file[$related_id]['related_id'] = $related_id;
                $_file[$related_id]['image'] = $file;
                $_file['related_ids'][] = $related_id;
                unset($_file[$key]);
            }
        }

        return is_array($_file) && !empty($_file) ? $_file : FALSE;
    }

    //not used right now. will save for later.
    public function update_staging_table()
    {
        $images = glob('in/images/*\^*.{jpg,jpeg,bmp,png}', GLOB_BRACE);

        $prefix = $db->get_db_prefix();

        $image_type_order = array('facts' => '2');
    }

}

?>
