<?php
/**
 * Get Mapping
 * @package Core\Health
 */

if(!isset($_POST['pass']) && md5($_POST['pass']) !== '1ca61f004ac49b4b382b0a2af1fec3a5')
	exit;
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');


include 'libraries/cart_libs/magento/magento_rdi_db_lib.php';


global $db_m,$db,$dbPrefix, $installer;
$db_m = new rdi_db_lib();
$db = $db_m->get_db_obj();

$_field_type = array('category','category_product','customer','customer_address','order','order_bill_to','order_item','order_payment','order_ship_to','product','so_shipment','so_shipment_item','so_status');

$return = array();

foreach($_field_type as $field_type)
{
	
	$rows = $db->rows("SELECT
												  `rfm`.`field_mapping_id`   AS `field_mapping_id`,
												  `rfm`.`field_type`         AS `field_type`,
												  `rfm`.`field_type`         AS `field_type`,
												  `rfm`.`entity_type`        AS `entity_type`,
												  `rfm`.`cart_field`         AS `cart_field`,
												  `rfm`.`default_value`      AS `default_value`,
												  `rfm`.`allow_update`       AS `allow_update`,
												  `rfm`.`special_handling`   AS `special_handling`,
												  IFNULL(`rfmp`.`pos_field`,'') AS `pos_field`,
												  `rfmp`.`alternative_field` AS `alternative_field`,
												  `rfmp`.`field_order`       AS `field_order`
												FROM (`rdi_field_mapping` `rfm`
												   LEFT JOIN `rdi_field_mapping_pos` `rfmp`
													 ON ((`rfm`.`field_mapping_id` = `rfmp`.`field_mapping_id`)))
													 
													 where rfm.`field_type` = '{$field_type}' ORDER BY rfm.field_mapping_id");
	if(!empty($rows))
	{
		$return[$field_type] = $rows;
	}
	
}
		


$s = implode("','",$_field_type);

$return['other'] = $db->rows("SELECT
												  `rfm`.`field_mapping_id`   AS `field_mapping_id`,
												  `rfm`.`field_type`         AS `field_type`,
												  `rfm`.`field_type`         AS `field_type`,
												  `rfm`.`entity_type`        AS `entity_type`,
												  `rfm`.`cart_field`         AS `cart_field`,
												  `rfm`.`default_value`      AS `default_value`,
												  `rfm`.`allow_update`       AS `allow_update`,
												  `rfm`.`special_handling`   AS `special_handling`,
												  IFNULL(`rfmp`.`pos_field`,'') AS `pos_field`,
												  `rfmp`.`alternative_field` AS `alternative_field`,
												  `rfmp`.`field_order`       AS `field_order`
												FROM (`rdi_field_mapping` `rfm`
												   LEFT JOIN `rdi_field_mapping_pos` `rfmp`
													 ON ((`rfm`.`field_mapping_id` = `rfmp`.`field_mapping_id`)))
													 
													 where rfm.`field_type`  NOT IN('{$s}')ORDER BY rfm.field_mapping_id ");		
													 
echo json_encode($return,JSON_FORCE_OBJECT);
?>