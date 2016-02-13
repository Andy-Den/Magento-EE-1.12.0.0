<?php

include_once "init.php";
echo "<pre>";
$info = apc_cache_info();

//print_r($info);

$fileapc = $_GET['fileapc'];

echo $fileapc."\n";

foreach ($info['cache_list'] as $key => $obj) {
  
   if(strstr($obj['filename'],'/rdi/') || strstr($obj['filename'],'/rpro/') || strstr($obj['filename'],'subcategory_listing') || strstr($obj['filename'],$fileapc) )
   {
		apc_delete_file($obj['filename']);
   
		print 'Deleted: ' . $obj['filename'] . "<br>";
	}
}
echo "</pre>";
?>