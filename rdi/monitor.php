
<?php

include 'init.php';

global $cart, $last_error_check, $reset_error;

$db = $cart->get_db();

if(isset($reset_error) && $reset_error == '1')
{
	$db->exec("UPDATE `rdi_settings` SET `cart_lib` = NULL WHERE `setting` = 'last_error_check'");
	$db->exec("DELETE FROM rdi_error_log");
	die("Last Error Check Flag reset and rdi_error_log cleared");
}

$working = true;
$log = array();
$errors = array();
$data = array();
$data['message'] = '';

// loadtimes_log checks
$rows = $db->rows("SELECT * FROM rdi_loadtimes_log WHERE action in('START','END')");

if(!empty($rows))
{
	foreach($rows as $row)
	{
		if($row['action'] == 'END')//if this is an end
		{
			//we found an already marked for this, so we delete it. Good.
			if(isset($log[$row['action']]))
			{
				unset($log[$row['action']]);
			}
		}
		
		if($row['action'] == 'START')//if this is an end
		{
			//we found an already marked for this, not good. $
			if(isset($log[$row['action']]))
			{
				$working = false;
				$errors[] = "{$row['script']}|{$row['action']}|{$row['datetime']}";
				unset($log[$row['action']]);
			}
			else
			{
				$log[$row['action']] = $row['datetime'];
			}
		}
	}
}

// error_logs checks
// gets errors since the last time it ran, plus will keep erroring until the cart_lib is cleared from the setting.
if(!isset($last_error_check) || strlen($last_error_check) == 0)
{
	if(!isset($last_error_check))
	{
		$db->exec("INSERT INTO `rdi_settings` (`setting_id`, `setting`, `value`, `group`, `help`, `cart_lib`, `pos_lib`) VALUES ('9969', 'last_error_check', NULL, 'monitor', '', NULL, NULL)");
	}
	$last_error_check = '2013-01-01 00:00:00';
}
$now = $db->cell("SELECT now() c" ,"c");

$db->exec("update `rdi_settings` SET value = '{$now}' WHERE setting = 'last_error_check'");

if(isset($last_error_check) && strtotime($last_error_check) < strtotime($now))
{
	$count = $db->cell("SELECT count(*) c FROM rdi_error_log WHERE `datetime` > '{$last_error_check}'", 'c');

	if($count > 0)
	{
		$db->exec("update `rdi_settings` SET cart_lib = 'Has MySQL Errors' WHERE setting = 'last_error_check'");
		$data['message'] .= "There are {$count} Errors in the log since the last check. ";
			
		
	}
	
	if($db->cell("SELECT cart_lib FROM rdi_settings WHERE setting = 'last_error_check'", 'cart_lib') == 'Has MySQL Errors' )
	{
		$working = false;
		$latest_errors = $db->rows("SELECT distinct left(error_message,50) message FROM rdi_error_log WHERE `datetime` > '{$last_error_check}' order by uid desc limit 100");
		
		$data['message'] .= "There are errors. The flag has not been reset.";
		if(!empty($latest_errors))
		{
			$errors = array_merge($errors,$latest_errors);
		}
	}
}

if($working)
{
	die("working");
}
echo "|message|";
$data['message'] .= "Scripts Did not finish!";
$data['logs'] = $log;
$data['errors'] = $errors;
echo json_encode($data, true);

?>