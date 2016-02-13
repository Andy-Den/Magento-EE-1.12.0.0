<?php 
require '../app/Mage.php';
Mage::app('admin');

$batchOffset = 0;
$batchSize = 200;
do{
	$confProductCollection = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToSelect(array('name', 'shoe_color_manu', 'vendor_name'))
			->addAttributeToFilter('type_id', array('eq' => 'configurable'));
	$confProductCollection->getSelect()->limit($batchSize, $batchOffset);
	
	foreach($confProductCollection as $confProduct){
		$manuColor = $confProduct->getData("shoe_color_manu");
		if($manuColor){
			$newName = preg_replace("/$manuColor/i", "", $confProduct->getName());
			$newName = $confProduct->getData('vendor_name') . " " . $newName;
			$newName = trim($newName); //In case some items are empty
			$confProduct->setName($newName);
			$confProduct->getResource()->saveAttribute($confProduct, 'name');
		}
	}
	
	$batchOffset += $batchSize;
	echo count($confProductCollection) . " processed. Starting from " . $batchOffset . "<br/>\n";

}while(count($confProductCollection) >= $batchSize);

echo 'DONE!';