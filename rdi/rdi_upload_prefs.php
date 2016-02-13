<?php
/**
 * Process Preferences Data
 * @package Core\Import\Preferences
 */
include_once "init.php";

//get the processor for the import catalog function
$upload = $pos->get_processor("rdi_pos_upload")->upload('prefs');

?>