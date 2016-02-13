<?php
/*
Migrate X-Cart customers to Magento
Original by Spydor at: http://www.magentocommerce.com/boards/viewthread/30894/
Modified by B00MER at: http://www.molotovbliss.com 3/15/2009 3:49:23 AM
Modifications include:
X-Cart 3.5 compatibility, possibly lower versions as well.
Optimization for large number of records to avoid memory limit errors
*/
@set_time_limit(2700);
# Include core functions
if (!require("../admin/auth.php"))
	require("./auth.php");
function resolveState($b_state,$b_country,$s_state,$s_country){
	global $states;
	$result = array();
	foreach($states as $key=>$value){
		// Billing
		if(($value['state_code']==$b_state) && ($value['country_code']==$b_country))
			 $result['billing'] = $value['state'];
		// Shipping
		if(($value['state_code']==$s_state) && ($value['country_code']==$s_country))
			 $result['shipping'] = $value['state'];
	}
	if(empty($result['billing']))
		$result['billing'] = $b_state;
	if(empty($result['shipping']))
		$result['shipping'] = $s_state;
	return $result;
}
function func_export_csv($data,$title) {
	$output = $data;
	$size_in_bytes = strlen($output);
	   header("Content-type: application/vnd.ms-excel");
	   header("Content-disposition: csv; filename=".$title . '_' . date("Y-m-d") . ".csv; size=$size_in_bytes");
	   return $output;
}
# Define new line
$newline = "\n";
# Define CSV fields
$output =  '"website","email","group_id","prefix","firstname","middlename","lastname","suffix","password_hash","taxvat","billing_prefix","billing_firstname","billing_middlename","billing_lastname","billing_suffix","billing_street_full","billing_city","billing_region","billing_country","billing_postcode","billing_telephone","billing_company","billing_fax","shipping_prefix","shipping_firstname","shipping_middlename","shipping_lastname","shipping_suffix","shipping_street_full","shipping_city","shipping_region","shipping_country","shipping_postcode","shipping_telephone","shipping_company","shipping_fax","created_in","is_subscribed"';
$output .= "$newline";
$states = func_query("SELECT $sql_tbl[states] .state, $sql_tbl[states] .code AS state_code, $sql_tbl[states] .country_code FROM $sql_tbl[states], $sql_tbl[countries] WHERE $sql_tbl[states] .country_code=$sql_tbl[countries] .code AND $sql_tbl[countries] .active='Y'");
# total customers
$sql = "SELECT COUNT(*) AS total_results FROM $sql_tbl[customers] where login NOT LIKE 'anonymous%'";
$results = func_query($sql);
$total_results=$results[0]["total_results"];
# number of records to return each loop
$num_of_records=150;
# start for loop to feed out data
for($i=0; $i<=$total_results; $i+=$num_of_records) {
  #$q = "SELECT * FROM xcart_customers where login NOT LIKE 'anonymous%' "; // Memory Hog with SELECT *
  $q = "SELECT email,title,firstname,lastname,password,title,b_address,b_city,b_country,b_zipcode,phone,company,fax,s_address,s_city,s_country,s_zipcode FROM xcart_customers where login NOT LIKE 'anonymous%' LIMIT $i,$num_of_records ";
  $result = func_query($q);
  foreach($result as $key=>$value){
       # Uncomment for version 4.0+ of X-Cart
  	   $result[$key]['password_hash'] = md5(text_decrypt($value['password']));
       # for version 3.5 and below of X-Cart
  	   #$result[$key]['password_hash'] = text_decrypt($value['password']);
  	   $realstates = resolveState($value['b_state'], $value['b_country'], $value['s_state'], $value['s_country']);
  	   $result[$key]['b_real_state'] = $realstates['billing'];
  	   $result[$key]['s_real_state'] = $realstates['shipping'];
  }
  foreach($result as $key => $value) {
    #func_print_r($result);exit;
  	$output .= '"base",';
  	$output .= '"' . $value['email'] . '",';
  	$output .= '"General",';
  	$output .= '"' . $value['title'] . '",';
  	$output .= '"' . $value['firstname'] . '",';
  	$output .= '"",';
  	$output .= '"' . $value['lastname'] . '",';
  	$output .= '"",';
  	$output .= '"' . $value['password_hash'] . '",';
  	$output .= '"",';
  	# Billing info
  	$output .= '"' . $value['title'] . '",';
  	$output .= '"' . $value['firstname'] . '",';
  	$output .= '"",';
  	$output .= '"' . $value['lastname'] . '",';
  	$output .= '"",';
  	$output .= '"' . $value['b_address'] . '",';
  	$output .= '"' . $value['b_city'] . '",';
  	$output .= '"' . $value['b_real_state'] . '",';
  	$output .= '"' . $value['b_country'] . '",';
  	$output .= '"' . $value['b_zipcode'] . '",';
  	$output .= '"' . $value['phone'] . '",';
  	$output .= '"' . $value['company'] . '",';
  	$output .= '"' . $value['fax'] . '",';
  	# Shipping Info
  	$output .= '"' . $value['title'] . '",';
  	$output .= '"' . $value['firstname'] . '",';
  	$output .= '"",';
  	$output .= '"' . $value['lastname'] . '",';
  	$output .= '"",';
  	$output .= '"' . $value['s_address'] . '",';
  	$output .= '"' . $value['s_city'] . '",';
  	$output .= '"' . $value['s_real_state'] . '",';
  	$output .= '"' . $value['s_country'] . '",';
  	$output .= '"' . $value['s_zipcode'] . '",';
  	$output .= '"' . $value['phone'] . '",';
  	$output .= '"' . $value['company'] . '",';
  	$output .= '"' . $value['fax'] . '",';
  	$output .= '"default",';
  	$output .= '"0"';
  	$output .= "$newline";
  }
}
#func_print_r($output); // uncomment to see output
print func_export_csv($output,"xcart_customers");
?>
