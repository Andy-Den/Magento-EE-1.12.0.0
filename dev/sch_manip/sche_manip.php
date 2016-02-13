<?php
///ini_set('memory_limit','4293918720');
// === INIT === //
echo PHP_EOL . 'Init!!!' . PHP_EOL;
define ( 'MAGENTO', realpath ( dirname ( dirname ( dirname ( __FILE__ ) ) ) ) );
set_time_limit ( 0 );
//$mageAppPath = MAGENTO . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
$mageAppPath = MAGENTO . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'Mage.php';
require_once $mageAppPath;
Mage::app ();
echo "MAGE Loaded" . PHP_EOL;
$chdirCode = chdir ( MAGENTO );
echo 'Changed Working Dir to ' . MAGENTO . PHP_EOL;
echo '    (This Matches the Working Dir that the file import expects.)' . PHP_EOL;
echo 'END Init!!! ' . PHP_EOL;
// === END INIT === //


// === CONFIG === //
define ( 'SCHEDULE_ID', 8 );
// === END CONFIG === //


// === LOCAL PREP === //
$operation = Mage::getModel ( 'enterprise_importexport/scheduled_operation' )->load ( SCHEDULE_ID );
$fileInfoArray = $operation->getData ( 'file_info' );
$fileDirectory = $fileInfoArray ['file_path']; // dirname ( $fileInfoArray ['file_path'] );
$fileDirectoryCleaned = str_replace ( '/', DS, $fileDirectory );
$fileArray = glob ( MAGENTO . DS . $fileDirectoryCleaned . DS . '*' );
echo PHP_EOL . 'There are ' . count($fileArray) . ' Files in ' . $fileDirectoryCleaned . PHP_EOL;
// === END LOCAL PREP === //


// === LOOP ON THE FILES === //
$successCount = 0;
$failCount = 0;
echo PHP_EOL . 'ENTERING RUN!!!' . PHP_EOL;
foreach ( $fileArray as $filePath ) {
	$fileName = basename ( $filePath );
	$fileInfoArray ['file_name'] = $fileName;
	$operation->setData ( 'file_info', $fileInfoArray );
	echo 'MEMORY_GET_USAGE: ' . memory_get_usage(true). PHP_EOL;
	echo 'RUNNING ' . $fileName . PHP_EOL;
	$resultCode = $operation->run ();
	echo '    RESULT FOR: ' . $fileName . ' IS: ' . $resultCode . PHP_EOL;
	if ($resultCode) {
		$successCount ++;
	} else {
		$failCount ++;
	}
}
echo 'THERE were ' . $successCount . ' Successes. And ' . $failCount . ' Failures.' . PHP_EOL;
echo 'EXITING RUN!!!' . PHP_EOL;
// === END LOOP ON THE FILES === //


//$operation->run ();
echo PHP_EOL . 'EXITING!!!' . PHP_EOL;