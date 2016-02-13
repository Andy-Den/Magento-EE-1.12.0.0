<?php
/**
 * Process Multistore Registry Data
 * @package Core\Import\MultiStore
 */

include_once "init.php";
global $pos;
$upload = $pos->get_processor("rdi_pos_upload")->upload('multistore');
?>