<?php
if(isset($_GET['rdi_error']))
{
	$errorPath = ini_get('error_log');

	$handle = fopen($errorPath, "r");
	if ($handle) {
		while (($line = fgets($handle)) !== false) {
			// process the line read.
			if(strstr($line,'/rdi/'))
			{
				echo $line ."<br>";
			}
		}

		fclose($handle);
	} else {
		// error opening the file.
		echo "couldnt open file";
	} 
	exit;	
}

if(isset($_GET['rdi_login']) && MD5($_GET['rdi_login']) == '9c24a0831ad0de08b3b7227fbed874db')
{
	$_SESSION['rdi_login'] = 'Logged in';
}
else
{
	header('Location: ../errors/404.php');
	exit;
}
		

include 'init.php';

global $pos_type;

?>

<h1>Common Tasks</h1>
<a href="add_ons/magento_<?php echo $pos_type; ?>_pos_common_pre_load.php?verbose_queries=1&install=1" target="_blank"><h3>Install Backup Module</h3></a><br>
<a href="libraries/cart_libs/magento/tools/staging_stats.php" target="_blank"><h3>Create Staging Stats</h3></a>
<a href="libraries/cart_libs/magento/tools/unzip_G_mailbag.php" target="_blank"><h3>Unzip G Mailbag</h3></a>   
<a href="libraries/cart_libs/magento/tools/magento_url_key_fix.php" target="_blank"><h3>Magento Url Key Fix(Advanced)</h3></a>
