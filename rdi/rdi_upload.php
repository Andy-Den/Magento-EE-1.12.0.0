<?php
/**
 * Process all Import Data
 * @package Core\Import
 */
include "init.php";

$benchmarker->set_start(basename(__FILE__), "load");
global $upload_type, $pos, $rdi_upload_protect, $rdi_protect_wait_time;

$upload = $pos->get_processor('rdi_pos_upload');

if(isset($rdi_upload_protect))
{
	$rdi_protect_wait_time = isset($rdi_protect_wait_time)?$rdi_protect_wait_time:0;
	
	if(isset($rdi_upload_protect) && $rdi_upload_protect + $rdi_protect_wait_time*60 < time())
	{
		$pos->get_db()->exec("UPDATE rdi_settings SET value = '".time()."' WHERE setting = 'rdi_upload_protect'");
		$upload->upload(null);
		$benchmarker->set_end(basename(__FILE__), "load");
		include "rdi_load.php";
		$pos->get_db()->exec("UPDATE rdi_settings SET value = '0' WHERE setting = 'rdi_upload_protect'");
	}
	else
	{
		$newtime = $rdi_upload_protect + 60;
		$waittill = $newtime + $rdi_protect_wait_time*60;
		$pos->get_db()->exec("UPDATE rdi_settings SET value = '{$newtime}' WHERE setting = 'rdi_upload_protect'");
		$pos->get_db()->exec("INSERT INTO `rdi_loadtimes_log` ( `script`, `action`, `datetime`, `duration`) VALUES ( 'rdi_upload.php', 'Waiting until {$waittill}', NOW(), '0.0000'); ");		
		$benchmarker->set_end(basename(__FILE__), "load");
	}
}
else //no wait time set
{
	$upload->upload(null);
	$benchmarker->set_end(basename(__FILE__), "load");
	include "rdi_load.php";
}




?>