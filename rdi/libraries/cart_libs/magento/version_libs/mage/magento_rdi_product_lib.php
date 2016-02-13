<?php

require_once '../app/Mage.php';
Mage::app();
 
//preps, inserts or updates a category record
function insertUpdateProductRecord($product_class_def, $product_type, $product_data, $referenced_entities = array(), $website_ids = array(1))
{         
    global $benchmarker;
    
    //print_r($product_class_def);
    //print_r($product_data);
    // instatiate Product
    $product = Mage::getModel('catalog/product');
        
     // if update
    if ($product_data['entity_id'] != '')
    {       
        //$benchmarker->set_start_time("magento_rdi_product_lib", "load product");
        $product->load($product_data['entity_id']);
        //$benchmarker->set_end_time("class.magento_rdi_product_lib", "load product");
    }
     
    //$benchmarker->set_start_time("magento_rdi_product_lib", "set basic data");
    $product->setWebsiteIds($website_ids);
    $product->setSku($product_data['sku']);
    $product->setPrice($product_data['list_price']);
    $product->setAttributeSetId($product_class_def['product_class_id']); 
    //$product->setCategoryIds(array(3));
    $product->setTypeId($product_type);
    $product->setName($product_data['name']);
    $product->setDescription($product_data['description']);
    $product->setShortDescription($product_data['short_description']);
    $product->setStatus($product_data['status'] == '' ? 1 : $product_data['status']);	
    $product->setTaxClassId($product_data['taxClassId'] == '' ? 1 : $product_data['taxClassId']);
    $product->setWeight($product_data['weight'] == '' ? 0 : $product_data['weight']);				
    $product->setCreatedAt(strtotime('now'));
    //$benchmarker->set_end_time("magento_rdi_product_lib", "set basic data");
    
    //set the product associations for the configurable products
    if($product_type == "configurable")
    {               
        //$benchmarker->set_start_time("magento_rdi_product_lib", "set configurable data");
        $associated_products = array();
        $attributes_used = array();
        $current_attr = $product->getTypeInstance()->getUsedProductAttributeIds();
        
        foreach($referenced_entities as $entity_record)
        {
            //$benchmarker->set_start_time("magento_rdi_product_lib", "set referenced product");
          
            $aproduct = Mage::getModel('catalog/product');
            $aproduct->load($entity_record['entity_id']);
            
            //loop through each of the attribute types
            $configurableAttribute = array();
            
            //print_r($product_class_def);
            
            foreach($product_class_def['field_data'] as $attr_type)
            {                                                   
                //$benchmarker->set_start_time("magento_rdi_product_lib", "set attribute");
                               
                $attribute_model        = Mage::getModel('eav/entity_attribute');                
                $attribute_code         = $attribute_model->getIdByCode('catalog_product', $attr_type['cart_field']);
                $attribute              = $attribute_model->load($attribute_code);
                //print_r($attribute);
                                                
                if($aproduct->getData($attr_type['cart_field']) != '')
                {                                        
                    //setup the attribute for addition to the product
                    $configurableAttribute[] = array(
                            'attribute_id'=>$attribute->getAttributeId(),
                            'label'=>$attr_type['cart_field'],
                            'value_index'=>$aproduct->getData($attr_type['cart_field']),
                            'is_percent'=>0,
                            'pricing_value'=>null
                    );           
                    
                    //add the attribute to the list used by the product
                    if(!in_array($attribute->getAttributeId(), $attributes_used) && !in_array($attribute->getAttributeId(), $current_attr))
                    {
                        $attributes_used[] = $attribute->getAttributeId();
                    }
                }
                
                //$benchmarker->set_end_time("magento_rdi_product_lib", "set attribute");
            }
            
            if(count($configurableAttribute) > 0)
                $associated_products[$entity_record['entity_id']] = $configurableAttribute;
            
            //$benchmarker->set_end_time("magento_rdi_product_lib", "set referenced product");
        }

        if(count($attributes_used) > 0)
        {
            //$benchmarker->set_start_time("magento_rdi_product_lib", "set attributes");
            
            $product->getTypeInstance()->setUsedProductAttributeIds($attributes_used);
            $attributes_array = $product->getTypeInstance()->getConfigurableAttributesAsArray();
            foreach($attributes_array as $key => $attribute_value) 
            {
                $attributes_array[$key]['label'] = $attribute_value['frontend_label'];
            }
            $product -> setConfigurableAttributesData($attributes_array);      
            
            //$benchmarker->set_end_time("magento_rdi_product_lib", "set attributes");
        }
        
        $product->setConfigurableProductsData($associated_products);
        $product->setCanSaveConfigurableAttributes(true);
        $product->setCanSaveCustomOptions(true);
        
        //$benchmarker->set_end_time("magento_rdi_product_lib", "set configurable data");
    }
    
    //set the mapped data, usually values such as size and color
    if(isset($product_class_def['field_data']) && is_array($product_class_def['field_data']))
    {   
        foreach($product_class_def['field_data'] as $field)
        {              
            if($product_data[$field['cart_field']] != '')
            {
                //check if the attribute already has this option, and get the id
                //if not add it and get the id  
                //$benchmarker->set_start_time("magento_rdi_product_lib", "add attribute");
                $option_id = add_attribute_value($field['cart_field'], $product_data[$field['cart_field']]);
                //$benchmarker->set_end_time("magento_rdi_product_lib", "add attribute");
                          
                //echo $field['cart_field'] . "->" . $option_id;
                
                $product->setData('size', $option_id);                
            }
        }
    }
            
    /* ADDITIONAL OPTIONS 

       $product->setCost();
       $product->setInDepth();
       $product->setKeywords();

    */
    
    //print_r($product);
    
    $product->setData('related_id', $product_data['related_id']);
            
    //$benchmarker->set_start_time("magento_rdi_product_lib", "save product");
    $product->save();
    //$benchmarker->set_end_time("magento_rdi_product_lib", "save product");
       
    // "Stock Item" still required regardless of whether inventory
    // control is used, or stock item error given at checkout!

    //$benchmarker->set_start_time("magento_rdi_product_lib", "stock item");
    $stockItem = Mage::getModel('cataloginventory/stock_item');
    $stockItem->assignProduct($product);
    $stockItem->setData('is_in_stock', 1);
    $stockItem->setData('stock_id', 1);
    $stockItem->setData('store_id', 1);
    $stockItem->setData('qty', $product_data['qty']);
    $stockItem->setData('manage_stock', 0);
    $stockItem->setData('use_config_manage_stock', 0);
    $stockItem->setData('min_sale_qty', 0);
    $stockItem->setData('use_config_min_sale_qty', 0);
    $stockItem->setData('max_sale_qty', 1000);
    $stockItem->setData('use_config_max_sale_qty', 0);
    $stockItem->save();
    //$benchmarker->set_end_time("magento_rdi_product_lib", "stock item");
    
   
    
    return $product->getEntityId();
}

function add_attribute_value($arg_attribute, $arg_value)
{    
    $attribute_model        = Mage::getModel('eav/entity_attribute');

    $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
    $attribute              = $attribute_model->load($attribute_code);

    if(!attribute_value_exists($arg_attribute, $arg_value))
    {
        $value['option'] = array($arg_value,$arg_value);
        $result = array('value' => $value, 'Default' => $arg_value);
                
        $attribute->setData('option',$result);
        $attribute->save();
    }

    $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;
    $attribute_table        = $attribute_options_model->setAttribute($attribute);
    $options                = $attribute_options_model->getAllOptions(false);

    foreach($options as $option)
    {
        if ($option['label'] == $arg_value)
        {
            return $option['value'];
        }
    }
    return false;
}

function attribute_value_exists($arg_attribute, $arg_value)
{
    $attribute_model        = Mage::getModel('eav/entity_attribute');
    $attribute_options_model= Mage::getModel('eav/entity_attribute_source_table') ;

    $attribute_code         = $attribute_model->getIdByCode('catalog_product', $arg_attribute);
    $attribute              = $attribute_model->load($attribute_code);

    $attribute_table        = $attribute_options_model->setAttribute($attribute);
    $options                = $attribute_options_model->getAllOptions(false);

    foreach($options as $option)
    {
        if ($option['label'] == $arg_value)
        {
            return $option['value'];
        }
    }

    return false;
}

//get the attribute set id, from the name used
//function get_attribute_set_id($attributeSetName)
//{
//    $entityTypeId = Mage::getModel('eav/entity')
//                ->setType('catalog_product')
//                ->getTypeId();
//   
//    $attributeSetId     = Mage::getModel('eav/entity_attribute_set')
//                        ->getCollection()
//                        ->setEntityTypeFilter($entityTypeId)
//                        ->addFieldToFilter('attribute_set_name', $attributeSetName)
//                        ->getFirstItem()
//                        ->getAttributeSetId();
//    
//    return $attributeSetId;    
//}

//function get_attribute_id($attribute_name)
//{    
    //return Mage::getModel('eav/entity_attribute')->getIdByCode($attribute_name, 'name'); 
//}

//type - catalog_category , catalog_product
    function get_attribute_id($type, $attribute_name)
    {    
        global $cart;
        return $cart->get_db()->cell("SELECT 
                                             attribute_id
                                            FROM eav_attribute
                                            INNER JOIN eav_entity_type on eav_entity_type.entity_type_id = eav_attribute.entity_type_id
                                            WHERE attribute_code = '{$type}' and eav_entity_type.entity_type_code = '{$attribute_name}'", "attribute_id");
    }
        
?>