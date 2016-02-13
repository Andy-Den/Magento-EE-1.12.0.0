<?php

$f = glob('in/o/*.xml');

if(count($f) > 0)
{
	rename($f[0],'in/' . basename($f[0])) ;
	rename($f[1],'in/' . basename($f[1])) ;
}


?>