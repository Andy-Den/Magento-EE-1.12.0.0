<?php
/**
 * 
 */

chdir('../../../../');
include 'init.php';

global $cart;

$db = $cart->get_db();

$_attributes = $db->cells("SELECT ea.attribute_id, ea.attribute_code FROM {$cart->prefix}eav_attribute ea
JOIN {$cart->prefix}eav_entity_type et
ON et.entity_type_id = ea.entity_type_id
AND et.entity_type_code = 'catalog_product'
WHERE ea.attribute_code IN('size','color','manufacturer')",'attribute_id','attribute_code');
        

 if(!isset($_attributes['size']))
 {
     die("Size attribute is required to makge this cheat work.");
 }
 
 if(isset($_attributes['manufacturer']))
 {
        $db->exec("INSERT IGNORE INTO {$cart->prefix}eav_entity_attribute
                    (
                     entity_type_id,
                     attribute_set_id,
                     attribute_group_id,
                     attribute_id,
                     sort_order)
        SELECT entity_type_id, attribute_set_id, attribute_group_id, {$_attributes['manufacturer']} AS attribute_id, sort_order - 10 AS sort_order FROM {$cart->prefix}eav_entity_attribute WHERE attribute_id = {$_attributes['size']}");
 }

 if(isset($_attributes['color']))
 {
        $db->exec("INSERT IGNORE INTO {$cart->prefix}eav_entity_attribute
                    (
                     entity_type_id,
                     attribute_set_id,
                     attribute_group_id,
                     attribute_id,
                     sort_order)
        SELECT entity_type_id, attribute_set_id, attribute_group_id, {$_attributes['color']} AS attribute_id, sort_order - 10 AS sort_order FROM {$cart->prefix}eav_entity_attribute WHERE attribute_id = {$_attributes['size']}");
 }
?>

