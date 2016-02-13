<?php

/*
 * 
 * less query driven uses some php
 */

require "init.php";

global $cart;

//get the attribut id for the url key
$product_entity_type_id = $cart->get_db()->cell("select entity_type_id from eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
$url_key_attribute_id = $cart->get_db()->cell("select attribute_id from eav_attribute where attribute_code = 'url_key' and entity_type_id = {$product_entity_type_id}", "attribute_id");
$name_attribute_id = $cart->get_db()->cell("select attribute_id from eav_attribute where attribute_code = 'name' and entity_type_id = {$product_entity_type_id}", "attribute_id");

$size_attribute_id = $cart->get_db()->cell("select attribute_id from eav_attribute where attribute_code = 'size' and entity_type_id = {$product_entity_type_id}", "attribute_id");
$attr_attribute_id = $cart->get_db()->cell("select attribute_id from eav_attribute where attribute_code = 'attribute' and entity_type_id = {$product_entity_type_id}", "attribute_id");

//this fixes the simples

    //get a list of the names start from that
    $sql = "SELECT distinct entity_id,
            REPLACE(
            REPLACE(
            REPLACE(
            REPLACE(
            REPLACE(
            REPLACE(
            REPLACE(
            REPLACE(
            REPLACE(
            REPLACE(
            REPLACE(
            REPLACE(
            REPLACE(
            REPLACE(
            REPLACE(value,'/',''),'{',''),'(',''),'.',''),'*',''),'?',''),')',''),'}',''),'/',''),'--','-') ,'__','_') ,'\'','') ,' ','-') ,'\"','') ,'.','') as value FROM catalog_product_entity_varchar cpv WHERE attribute_id = {$name_attribute_id}";

    echo $sql;

    echo "<BR><BR><BR>";
                
    $names = $cart->get_db()->rows($sql);
    
    foreach($names as $name)
    {
        //get the size value for this item
        
        $sql = "SELECT 
                    cpei.`entity_id`,
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(eaov.value,'/','')
                    ,'{','')
                    ,'(','')
                    ,'.','')
                    ,'*','')
                    ,'?','')
                    ,')','')
                    ,'}','')
                    ,'/','')
                    ,'--','-')
                    ,'__','_')
                    ,'\'','')
                    ,' ','-')
                    ,'\"','')
                    ,'.','') as value  
                  FROM
                    eav_attribute_option_value eaov 
                    INNER JOIN eav_attribute_option eao 
                      ON eao.`option_id` = eaov.`option_id` 
                    INNER JOIN catalog_product_entity_int cpei 
                      ON eao.`option_id` = cpei.`value` 
                      AND cpei.`attribute_id` = {$size_attribute_id}
                  where cpei.entity_id = {$name['entity_id']}";

        echo $sql;

        echo "<BR><BR><BR>";

        $size = $cart->get_db()->row($sql);
        
        //get the attr values for this item
        
         $sql = "SELECT 
                    cpei.`entity_id`,
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(
                    REPLACE(eaov.value,'/','')
                    ,'{','')
                    ,'(','')
                    ,'.','')
                    ,'*','')
                    ,'?','')
                    ,')','')
                    ,'}','')
                    ,'/','')
                    ,'--','-')
                    ,'__','_')
                    ,'\'','')
                    ,' ','-')
                    ,'\"','')
                    ,'.','') as value  
                  FROM
                    eav_attribute_option_value eaov 
                    INNER JOIN eav_attribute_option eao 
                      ON eao.`option_id` = eaov.`option_id` 
                    INNER JOIN catalog_product_entity_int cpei 
                      ON eao.`option_id` = cpei.`value` 
                      AND cpei.`attribute_id` = {$attr_attribute_id}
                  where cpei.entity_id = {$name['entity_id']}";

        echo $sql;

        echo "<BR><BR><BR>";

        $attr = $cart->get_db()->row($sql);
        
        $new_key = $name['value'] . ($size['value'] != '' ? '-' . $size['value'] : '') . ($attr['value'] != '' ? '-' . $attr['value'] : '') . "-" . $name['entity_id'];
        
        echo ">>>>>>>>>>>" . $new_key;
        
        echo "<BR><BR><BR>-----------------------------";        
        echo "<BR><BR><BR>";
        
        $sql = "update catalog_product_entity_varchar set value = '{$new_key}' where entity_id = {$name['entity_id']} and attribute_id = {$url_key_attribute_id}";
        
        echo $sql;
        
        $cart->get_db()->exec($sql);
    }
             
    //this will handle the configurables
    
    $csql = "UPDATE catalog_product_entity_varchar cpv_e
    INNER JOIN (
    SELECT entity_id, VALUE
    FROM catalog_product_entity_varchar cpv
    WHERE attribute_id = {$url_key_attribute_id}) AS v ON v.value = cpv_e.`value`
    INNER JOIN (
    SELECT entity_id,
    REPLACE(
    REPLACE(
    REPLACE(
    REPLACE(
    REPLACE(
    REPLACE(
    REPLACE(
    REPLACE(
    REPLACE(
    REPLACE(
    REPLACE(
    REPLACE(
    REPLACE(
    REPLACE(
    REPLACE(value,'/',''),'{',''),'(',''),'.',''),'*',''),'?',''),')',''),'}',''),'/',''),'--','-') ,'__','_') ,'\'','') ,' ','-') ,'\"','') ,'.','') as value FROM catalog_product_entity_varchar cpv WHERE attribute_id = {$name_attribute_id} ) AS name ON name.entity_id = cpv_e.`entity_id` INNER JOIN catalog_product_entity cpe ON cpe.`type_id` = 'configurable' AND cpe.`entity_id` = cpv_e.`entity_id`  set cpv_e.value = CONCAT( name.value, CONCAT('-', cpe.`entity_id`), concat('-', cpe.sku)) where cpv_e.entity_id = cpe.entity_id and cpv_e.attribute_id = {$url_key_attribute_id}";
    
    echo $csql;

    echo "<BR><BR><BR>";
    
    $cart->get_db()->exec($ssql);
    
    //update the stand alone simples
    
    $ssql = "UPDATE catalog_product_entity_varchar cpv_e                
                INNER JOIN 
                 (
                SELECT 
                 entity_id,
                 VALUE
                FROM
                 catalog_product_entity_varchar cpv
                WHERE attribute_id = {$url_key_attribute_id}) AS v ON v.value = cpv_e.`value`
                INNER JOIN 
                 (
                SELECT 
                 entity_id,
                REPLACE(
                REPLACE(
                REPLACE(
                REPLACE(
                REPLACE(
                REPLACE(
                REPLACE(
                REPLACE(
                REPLACE(
                REPLACE(
                REPLACE(
                REPLACE(
                REPLACE(
                REPLACE(
                REPLACE(value, '/', ''), '{', ''),
                 '(',
                 ''
                ),
                 '.',
                 ''
                ),
                 '*',
                 ''
                ),
                 '?',
                 ''
                ),
                 ')',
                 ''
                ),
                 '}',
                 ''
                ),
                 '/',
                 ''
                ),
                 '--',
                 '-'
                ),
                 '__',
                 '_'
                ),
                 '\'',
                              ''
                            ),
                            ' ',
                            '-'
                          ),
                          '\"',
                 ''
                ),
                 '.',
                 ''
                )
                AS value
                FROM
                 catalog_product_entity_varchar cpv
                WHERE attribute_id = {$name_attribute_id}) AS name ON name.entity_id = cpv_e.`entity_id`
                INNER JOIN catalog_product_entity cpe ON cpe.`type_id` = 'simple' AND cpe.`entity_id` = cpv_e.`entity_id`
                LEFT JOIN catalog_product_super_link l ON l.parent_id = cpe.entity_id
                set cpv_e.value = CONCAT(name.value, CONCAT('-', cpe.sku))
                WHERE cpv_e.entity_id = cpe.entity_id AND cpv_e.attribute_id = {$url_key_attribute_id} AND l.parent_id IS NULL";
    
    echo $ssql;

    //echo "<BR><BR><BR>";
    
    $cart->get_db()->exec($csql);
    
    //////////////////////////////////////////////////////////////////////
    
    //there is a chance there are some configurables that will be dupes also, as they have the same name
         
    
    //then should just unicify any others with an index just to be sure
    
    //this will get a list of the url_key values and their entity_id
//    $sql = "SELECT cpv_e.store_id, cpv_e.`value`, cpv_e.entity_id FROM catalog_product_entity_varchar cpv_e inner join (
//    SELECT VALUE FROM catalog_product_entity_varchar cpv
//    WHERE attribute_id = {$url_key_attribute_id} GROUP BY VALUE HAVING COUNT(VALUE) > 1) as v on v.value = cpv_e.`value`
//    inner join catalog_product_entity cpe on cpe.`entity_id` = cpv_e.`entity_id` order by cpv_e.`value` asc";
//    
//    $rows = $cart->get_db()->rows($sql);
//    
//    //loop these values and add the index value to the value
//    $index = 0;
//    $current_value = '';
//    
//    foreach($rows as $row)
//    {
//        if($current_value != $row['value'])
//        {
//            $index = 0;
//            $current_value = $row['value'];
//        }
//        else
//        {        
//            $sql = "update catalog_product_entity_varchar set value = '{$current_value}_{$index}' where store_id = {$row['store_id']} and entity_id = {$row['entity_id']} and attribute_id = {$url_key_attribute_id}";
//            //$cart->get_db()->exec($sql);
//        
//            //$index++;
//            echo $sql ."<BR>";
//        }
//        
//        $current_value = $row['value'];
//        $index++;
//    }
//}




?>

