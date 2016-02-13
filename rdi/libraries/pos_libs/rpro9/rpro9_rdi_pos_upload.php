<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Staging table databse functions
 *
 * 
 * PHP version 5.3
 *
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2015 Retail Dimensions Inc.
 * @version    1.0.0
 */
class rdi_pos_upload extends rdi_upload {

    public $_upload_libraries = array(
        "styles" => "rdi_import_styles_xml",
        "catalog" => "rdi_import_catalog_xml",
        "customers" => "rdi_import_customers_xml",
        "gift_reg" => "rdi_import_gift_reg_xml",
        "item_images" => "rdi_import_images_xml",
        "multistore" => "rdi_import_multistore_xml",
        "prefs" => "rdi_import_prefs_xml",
        "priceqty" => "",
        "return" => "rdi_import_return_xml",
        "sostatus" => "rdi_import_sostatus_xml",
        "upsell_item" => "rdi_import_upsell_xml"
    );

    /**
     * Class Constructor
     *
     * @param rdi_db $db
     */
    public function __construct($db = '')
    {
        parent::__construct($db);
    }

    /**
     * 
     * @param type $upload_type
     */
    public function upload($upload_type)
    {
        parent::upload($upload_type);
    }

}

?>
