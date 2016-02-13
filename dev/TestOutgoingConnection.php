<?php

ini_set('memory_limit', '2G');
ini_set('display_errors', 1);

echo 'Tesing outgoing connections: DNS, HTTP, HTTPS.' . '<br/><br/>' . "\n\n";

//==== Test #01 =================================================================================//
echo 'Test #01: DNS, resolve HOST name for Google' . '<br/>' . "\n";
$hostIp = gethostbyname("www.google.com");
if(!$hostIp){
	echo 'Failed.' . '<br/>' . "\n";
}else{
	echo 'Success. ' . $hostIp . '<br/>' . "\n";
}

echo 'Test #01-2: DNS, resolve HOST name for current host: ' . $_SERVER["HTTP_HOST"] . '<br/>' . "\n";
$hostIp = gethostbyname($_SERVER["HTTP_HOST"]);
if(!$hostIp){
	echo 'Failed.' . '<br/>' . "\n";
}else{
	echo 'Success. ' . $hostIp . '<br/>' . "\n";
}
echo '<br/><br/>' . "\n\n";


//==== Test #02 =================================================================================//
echo 'Test #02: HTTP connection to www.yahoo.com, using Zend_Http_Client' . '<br/>' . "\n";

require_once( '../app/Mage.php' );
Mage::app();

$client = new Zend_Http_Client();

$uri = 'http://www.yahoo.com';
$client->setUri($uri);
$client->setConfig(array(
		'maxredirects'=>0,
		'timeout'=>30,
		//'ssltransport' => 'tcp',
));
echo 'Start time: ' . time() . '<br/>' . "\n";
try{
	$response = $client->request();
	echo 'Success. Response body length: ' . strlen($response->getBody()) . '<br/>' . "\n";
}catch(Exception $e){
	echo 'Failed. Error message: <br/>' . "\n";
	echo $e->getMessage() . '<br/>' . "\n";
}
echo 'End time: ' . time() . '<br/>' . "\n";
echo '<br/><br/>' . "\n\n";


//==== Test #03 =================================================================================//
echo 'Test #03: HTTPS connection to Authorize.net, using Zend_Http_Client' . '<br/>' . "\n";
echo '    This is only to test HTTPS connection with dummy data. The actual transaction should not pass.' . '<br/>' . "\n";


$uri = 'https://secure.authorize.net/gateway/transact.dll';
$client->setUri($uri);
$client->setConfig(array(
		'maxredirects'=>0,
		'timeout'=>30,
		//'ssltransport' => 'tcp',
));
//Some data are binary, use base64 for safe storage
$postRawData = 'YTo0Mzp7czo5OiJ4X3ZlcnNpb24iO3M6MzoiMy4xIjtzOjEyOiJ4X2RlbGltX2RhdGEiO3M6NDoiVHJ1ZSI7czoxNjoieF9yZWxheV9yZXNwb25zZSI7czo1OiJGYWxzZSI7czoxNDoieF90ZXN0X3JlcXVlc3QiO3M6NToiRkFMU0UiO3M6NzoieF9sb2dpbiI7czoxNjoibBuZnS1PMx2UYK+ApshVqiI7czoxMDoieF90cmFuX2tleSI7czoxNjoieU0bT1ZUQ01s6HdKkHuFdyI7czo2OiJ4X3R5cGUiO3M6MTI6IkFVVEhfQ0FQVFVSRSI7czo4OiJ4X21ldGhvZCI7czoyOiJDQyI7czoxMzoieF9pbnZvaWNlX251bSI7czo5OiIxMDAwMDAwNjQiO3M6ODoieF9hbW91bnQiO3M6NzoiMTA3OS4zNyI7czoxNToieF9jdXJyZW5jeV9jb2RlIjtzOjM6IlVTRCI7czoyMDoieF9hbGxvd19wYXJ0aWFsX2F1dGgiO3M6NToiRmFsc2UiO3M6MTI6InhfZmlyc3RfbmFtZSI7czozOiJKdW4iO3M6MTE6InhfbGFzdF9uYW1lIjtzOjQ6IlpoYW8iO3M6OToieF9jb21wYW55IjtzOjA6IiI7czo5OiJ4X2FkZHJlc3MiO3M6NjoiMTIzMTIzIjtzOjY6InhfY2l0eSI7czo4OiJOZXcgWW9yayI7czo3OiJ4X3N0YXRlIjtzOjg6Ik5ldyBZb3JrIjtzOjU6InhfemlwIjtzOjU6IjEwMDI1IjtzOjk6InhfY291bnRyeSI7czoyOiJVUyI7czo3OiJ4X3Bob25lIjtzOjY6IjEyMzEyMyI7czo1OiJ4X2ZheCI7czowOiIiO3M6OToieF9jdXN0X2lkIjtzOjI6IjEwIjtzOjEzOiJ4X2N1c3RvbWVyX2lwIjtzOjk6IjEyNy4wLjAuMSI7czoxNzoieF9jdXN0b21lcl90YXhfaWQiO3M6MDoiIjtzOjc6InhfZW1haWwiO3M6MjM6Imouemhhb0BoYXJhcGFydG5lcnMuY29tIjtzOjE2OiJ4X2VtYWlsX2N1c3RvbWVyIjtzOjE6IjAiO3M6MTY6InhfbWVyY2hhbnRfZW1haWwiO3M6MDoiIjtzOjIwOiJ4X3NoaXBfdG9fZmlyc3RfbmFtZSI7czozOiJKdW4iO3M6MTk6Inhfc2hpcF90b19sYXN0X25hbWUiO3M6NDoiWmhhbyI7czoxNzoieF9zaGlwX3RvX2NvbXBhbnkiO3M6MDoiIjtzOjE3OiJ4X3NoaXBfdG9fYWRkcmVzcyI7czo2OiIxMjMxMjMiO3M6MTQ6Inhfc2hpcF90b19jaXR5IjtzOjg6Ik5ldyBZb3JrIjtzOjE1OiJ4X3NoaXBfdG9fc3RhdGUiO3M6ODoiTmV3IFlvcmsiO3M6MTM6Inhfc2hpcF90b196aXAiO3M6NToiMTAwMjUiO3M6MTc6Inhfc2hpcF90b19jb3VudHJ5IjtzOjI6IlVTIjtzOjg6InhfcG9fbnVtIjtzOjA6IiI7czo1OiJ4X3RheCI7czo1OiI4My4zNyI7czo5OiJ4X2ZyZWlnaHQiO3M6MToiMCI7czoxMDoieF9jYXJkX251bSI7czoxNjoiNDExMTExMTExMTExMTExMSI7czoxMDoieF9leHBfZGF0ZSI7czo3OiIwMi0yMDE1IjtzOjExOiJ4X2NhcmRfY29kZSI7czozOiIxMTEiO3M6MTI6InhfZGVsaW1fY2hhciI7czozOiIofikiO30=';
$postData = unserialize(base64_decode($postRawData));
$postData['x_login'] = 'test_merchant_id';
$postData['x_tran_key'] = 'test_tran_key';
$client->setParameterPost(unserialize(base64_decode($postRawData)));
$client->setMethod(Zend_Http_Client::POST);

echo 'Start time: ' . time() . '<br/>' . "\n";
try{
	$response = $client->request();
	echo 'Success. Response body length: ' . strlen($response->getBody()) . '<br/>' . "\n";
}catch(Exception $e){
	echo 'Failed. Error message: <br/>' . "\n";
	echo $e->getMessage() . '<br/>' . "\n";
}
echo 'End time: ' . time() . '<br/>' . "\n";
echo '<br/><br/>' . "\n\n";