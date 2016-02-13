<?php 
require '../app/Mage.php';
Mage::app();

$catalogEavSetup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup();

$imageAttrId = $catalogEavSetup->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'image');
$mediaGalleryAttrId = $catalogEavSetup->getAttributeId(Mage_Catalog_Model_Product::ENTITY, 'media_gallery');

$resource = Mage::getSingleton('core/resource');
$writeConnection = $resource->getConnection('core_write');

//Old data clean up logic
echo "Warning, clear old data.<br/>\n";
$query = "SELECT MIN(`entity_id`) FROM `catalog_product_entity`;";
$minProductEntityId = $writeConnection->fetchOne($query);
$cleanQuery = "DELETE FROM `catalog_product_entity_datetime` WHERE `entity_id` < $minProductEntityId;";
$writeConnection->query($cleanQuery);
$cleanQuery = "DELETE FROM `catalog_product_entity_decimal` WHERE `entity_id` < $minProductEntityId;";
$writeConnection->query($cleanQuery);
$cleanQuery = "DELETE FROM `catalog_product_entity_int` WHERE `entity_id` < $minProductEntityId;";
$writeConnection->query($cleanQuery);
$cleanQuery = "DELETE FROM `catalog_product_entity_media_gallery` WHERE `entity_id` < $minProductEntityId;";
$writeConnection->query($cleanQuery);
$cleanQuery = "DELETE FROM `catalog_product_entity_text` WHERE `entity_id` < $minProductEntityId;";
$writeConnection->query($cleanQuery);
$cleanQuery = "DELETE FROM `catalog_product_entity_varchar` WHERE `entity_id` < $minProductEntityId;";
$writeConnection->query($cleanQuery);


echo "Starting gallery image patch.<br/>\n";
$batchOffset = 0;
$batchSize = 2000;
do{
	$query = "SELECT `entity_id`, `value` FROM `catalog_product_entity_varchar` WHERE `attribute_id` = $imageAttrId AND `value` != 'no_selection' LIMIT $batchOffset, $batchSize;";
	$imageDataArray = $writeConnection->fetchAll($query);
	
	foreach($imageDataArray as $imageData){
		$selectQuery = "SELECT * FROM `catalog_product_entity_media_gallery` WHERE `value` = '{$imageData['value']}';";
		$mediaGalleryData = $writeConnection->fetchAll($selectQuery);
		if(!$mediaGalleryData){
			$insertQuery = "INSERT INTO `catalog_product_entity_media_gallery` (`attribute_id`, `entity_id`, `value`) VALUES ($mediaGalleryAttrId, {$imageData['entity_id']}, '{$imageData['value']}');";
			$writeConnection->query($insertQuery);
			$lastInsertId = $writeConnection->lastInsertId();
			if(!!$lastInsertId){
				// "/a/h/ahn_af2289-blk__black__1d.jpg" => "AHN_AF2289-BLK__BLACK__1D"
				$imageLabelData = explode("/", $imageData['value']);
				$imageLabelFileName = strtoupper(trim($imageLabelData[count($imageLabelData) - 1]));
				$imageLabelFileNameData = explode('.', $imageLabelFileName);
				unset($imageLabelFileNameData[count($imageLabelFileNameData) - 1]);
				$imageLabel = implode('.', $imageLabelFileNameData);
				$insertQuery = "INSERT INTO `catalog_product_entity_media_gallery_value` (`value_id`, `store_id`, `label`, `position`, `disabled`) VALUES ($lastInsertId, 0, '$imageLabel', 0, 0);";
				$writeConnection->query($insertQuery);
			}
		}
	}
	$batchOffset += $batchSize;
	echo count($imageDataArray) . " processed. Starting from " . $batchOffset . "<br/>\n";

}while(count($imageDataArray) >= $batchSize);

echo 'DONE!';