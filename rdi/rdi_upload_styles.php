<?php
/**
 * Process Preferences Data
 * @package Core\Import\Styles
 */
include_once "init.php";

global $pos;

//execute the upload 
//@todo add the translation of this library to the cp library.
$upload = $pos->get_processor("rdi_pos_upload")->upload('styles');


?>