<?php

include_once "init.php";
echo "<pre>";
$info = apc_cache_info();

//print_r($info);

foreach ($info['cache_list'] as $key => $obj) {
  
   if(strstr($obj['filename'],'/rdi/') || strstr($obj['filename'],'/rpro/') )
   {
		apc_delete_file($obj['filename']);
   
		print 'Deleted: ' . $obj['filename'] . "<br>";
	}
}
echo "</pre>";
?>