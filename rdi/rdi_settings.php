<?php

//if(!isset($_POST['pass']) && md5($_POST['pass']) !== '1ca61f004ac49b4b382b0a2af1fec3a5')
	//exit;
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

include 'libraries/cart_libs/magento/magento_rdi_db_lib.php';


global $db_m,$db,$dbPrefix, $installer;
$db_m = new rdi_db_lib();
$db = $db_m->get_db_obj();

$settings = $db->rows("select * from rdi_settings");

$out = "INSERT INTO rdi_settings_customer values";

$comma = FALSE;

if(isset($_POST['get_json']) && $_POST['get_json'] == 1)
{
	header('Content-Type: application/json');
	header('Access-Control-Allow-Origin: *');
    $out = json_encode($settings);
}
else
{
    foreach($settings as $setting)
    {
            if($comma)
                    $out .= ",";
            $out .= "(";
            $comma2 = FALSE;
            foreach($setting as $setting_value)
            {
                    if($comma2)
                            $out .= ",";

                            $out .= '"' . mysql_escape_string($setting_value) . '"';

                    $comma2 = TRUE;
            }
            $out .= ")";
            $comma = true;
    }
}
//echo '<input type="text" id="frame_text" value="' . $out . '">';


echo $out;

 


?>