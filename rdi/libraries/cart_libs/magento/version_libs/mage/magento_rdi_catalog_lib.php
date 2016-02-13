<?php

//library of magento specific functions used for talking to magento

include_once '../app/Mage.php';
umask(0);
Mage::app();

//preps, inserts or updates a category record
function insertUpdateCategoryRecord($categoryData)
{              
    // get a new category object
    $category = Mage::getModel('catalog/category');
    $category->setStoreId(0);

    // if update
    if ($categoryData['entity_id'])
    {
        $category->load($categoryData['entity_id']);
    }
    
    //$categoryData['description'] = $categoryData['name'];
    
    //echo $categoryData['name'];
    
    $category->setPath($categoryData['path']);
    $category->setName($categoryData['name']);
    $category->setDescription($categoryData['description']);
    $category->setPosition($categoryData['order']);
    if($categoryData['url_key'] != '')
        $category->setUrlKey($categoryData['url_key']);
    
    if(isset($categoryData['is_active']))
        $category->setIsActive( $categoryData['is_active'] );
    else
        $category->setIsActive(1);
    
    $category->setDisplayMode("PRODUCTS");
    
    //print_r($categoryData);
      
    //massage the data if need be here
    
    //expected fields
//    $data['name'] = 
//    $data['path'] = ; // this is the catgeory path - normally 1/2 for root (default category)
//    $data['description'] = 
//    $data['meta_title'] = 
//    $data['meta_keywords'] = 
//    $data['meta_description'] = 
//    $data['landing_page'] = "";
//    $data['display_mode'] = "PRODUCTS";
//    $data['is_active'] = 1;
//    $data['is_anchor'] = 0;
//    $data['url_key'] = "";
                 
    $category->addData($categoryData);
   
    try 
    {
         $c = $category->save();
              
         return $c->getData('entity_id');                  
    }
    catch (Exception $e)
    {
        //todo change out for new error handler
        echo $e->getMessage();
    }
    
    return false;
}

//Add the specified category id to the list of of categories this product is assigned to
function set_product_category_relation($product_id, $category_id, $index = 0)
{
    $product = Mage::getModel('catalog/product');
    
    $product->load($product_id);
    
    $category_ids = $product->getCategoryIds();
    
    //if the category hasnt already been assigned to the product
    if(!in_array($category_id, $category_ids))
    {    
        //add the category to the array
        $category_ids[] = $category_id;

        $product->setCategoryIds($category_ids);

        $product->save();
    }
}

//Add the specified category id to the list of of categories this product is assigned to
function remove_product_category_relation($product_id, $category_id)
{
    $product = Mage::getModel('catalog/product');
    
    $product->load($product_id);
    
    $category_ids = $product->getCategoryIds();
    
    //if the category hasnt already been assigned to the product
    if(in_array($category_id, $category_ids))
    {    
        //remove the category from the array
        unset($category_ids[$category_id]);

        $product->setCategoryIds($category_ids);

        $product->save();
    }
}

?>