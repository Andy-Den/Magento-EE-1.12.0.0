<?php
/**
 * Health
 * @package Core\Health
 */
if(!isset($_POST['pass']) && md5($_POST['pass']) !== '1ca61f004ac49b4b382b0a2af1fec3a5')
	exit;

include_once "init.php";

include_once 'libraries/class.rdi_health.php';

global $pos;

$health = new rdi_health($pos->get_db());      

$health->load();

?>