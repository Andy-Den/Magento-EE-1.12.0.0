<?php


include "init.php";
include_once '../app/Mage.php';

$query = "select * from rpro_in_customers";
# Define new line
$newline = "\n";
# Define CSV fields
$output =  '"_website","email","group_id","prefix","firstname","middlename","lastname","suffix","password_hash","taxvat","billing_prefix","billing_firstname","billing_middlename","billing_lastname","billing_suffix","billing_street_full","billing_city","billing_region","billing_country","billing_postcode","billing_telephone","billing_company","billing_fax","shipping_prefix","shipping_firstname","shipping_middlename","shipping_lastname","shipping_suffix","shipping_street_full","shipping_city","shipping_region","shipping_country","shipping_postcode","shipping_telephone","shipping_company","shipping_fax","created_in","is_subscribed"'.$newline;

function func_export_csv($data,$title) {
	$output = $data;
	$size_in_bytes = strlen($output);
	   header("Content-type: application/vnd.ms-excel");
	   header("Content-disposition: csv; filename=".$title . '_' . date("Y-m-d") . ".csv; size=$size_in_bytes");
	   return $output;
}


$customers = mysql_query($query);
$custs=0;
while ($value = mysql_fetch_array($customers))
{
	$custs++;
	//{$customer['fldprclvl']},{$customer['fldprclvl_i']},{$customer['web_cust_sid']},{$customer['fldcustsid']},{$customer['fldcustsid']},
	$fldaddr3 = explode(",",$value['fldaddr3']);
	$city = $fldaddr3[0];
	$state = $fldaddr3[1];
	$country = $fldaddr3[2];
	
	#func_print_r($result);exit;
  	$output .= '"base",';
  	$output .= '"' . $value['email'] . '",';
  	$output .= '"1",';
  	$output .= '"' . $value['fldtitle'] . '",';
  	$output .= '"' . $value['fldfname'] . '",';
  	$output .= '"",';
  	$output .= '"' . $value['fldlname'] . '",';
  	$output .= '"",';
	//' . $value['password_hash'] . '
  	$output .= '"",';
  	$output .= '"",';
  	# Billing info
  	$output .= '"' . $value['fldtitle'] . '",';
  	$output .= '"' . $value['fldfname'] . '",';
  	$output .= '"",';
  	$output .= '"' . $value['fldlname'] . '",';
  	$output .= '"",';
  	$output .= '"' . $value['fldaddr1'] . '",';
  	$output .= '"' . $city . '",';
  	$output .= '"' . $state . '",';
  	$output .= '"' . $country . '",';
  	$output .= '"' . $value['fldzip'] . '",';
  	$output .= '"' . $value['fldphone1'] . '",';
  	$output .= '"' . $value['fldcompany'] . '",';
	//' . $value['fax'] . '
  	$output .= '"",';
  	# Shipping Info
  	$output .= '"' . $value['fldtitle'] . '",';
  	$output .= '"' . $value['fldfname'] . '",';
  	$output .= '"",';
  	$output .= '"' . $value['fldlname'] . '",';
  	$output .= '"",';
  	$output .= '"' . $value['fldaddr1'] . '",';
  	$output .= '"' . $city . '",';
  	$output .= '"' . $state . '",';
  	$output .= '"' . $country . '",';
  	$output .= '"' . $value['fldzip'] . '",';
  	$output .= '"' . $value['fldphone1'] . '",';
  	$output .= '"' . $value['fldcompany'] . '",';
	//' . $value['fax'] . '
  	$output .= '"",';
  	$output .= '"default",';
  	$output .= '"0"';
  	$output .= "$newline";
}

print func_export_csv($output,"customers");


	
?>