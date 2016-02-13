<?php

ini_set('memory_limit', '2G');
ini_set('display_errors', 1);

echo 'Tesing send mail.' . '<br/><br/>' . "\n\n";

//==== Test #01 =================================================================================//
echo 'Test #01: send sample email using Zend_Mail' . '<br/>' . "\n";

require_once( '../app/Mage.php' );
Mage::app();

$fromAddress = 'service@harapartners.com';
$toAddress = 'j.zhao@harapartners.com';

$mail = new Zend_Mail();
$mail->setBodyText('This is a test for send mail.');
$mail->setFrom($fromAddress, 'Harapartners Service');
$mail->addTo($toAddress, 'Developer');
$mail->setSubject('This is a test for send mail.');


echo 'Start time: ' . time() . '<br/>' . "\n";
try{
	$mail->send();
	echo 'Success. Mail sent from ' . $fromAddress. ' to ' . $toAddress . '. Please also double check trash or phishing folder.<br/>' . "\n";
}catch(Exception $e){
	echo 'Failed. Error message: <br/>' . "\n";
	echo $e->getMessage() . '<br/>' . "\n";
}
echo 'End time: ' . time() . '<br/>' . "\n";
echo '<br/><br/>' . "\n\n";