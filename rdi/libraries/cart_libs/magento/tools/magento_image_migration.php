<?php

/*
 * This is a tool to help migrate image files between magento sites of the same product data, the site must be realted
 * usage
 * first dump the image data on the first site via magento_image_migration.php?dump
 * copy the image_data.txt from the rdi folder and place it into the rdi folder on the new server
 * Make sure the image files are copied over to the new server in the same location as they were before
 * now run the restore on the new server magento_image_migration.php?restore
 * 
 * and should really remove this file when not needed anymore
 * 
 * Images will be linked up, make sure the files are there in place too
 */
$rdi_path = "../../../";
$inPath = "../../../";
include "../../../init.php";
include_once '../../../../app/Mage.php';
umask(0);
Mage::app();
        
if(isset($_GET['dump']))
{
            
    $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
    
    //get the attribute id for the related id
    $related_id_attr_id = $cart->get_db()->cell("select attribute_id from eav_attribute where entity_type_id = {$product_entity_type_id} and attribute_code = 'related_id'");
    
    //get the image files for the products
    $sql = "SELECT catalog_product_entity.entity_id, catalog_product_entity_varchar.value
            FROM catalog_product_entity 
            inner join catalog_product_entity_varchar on catalog_product_entity_varchar.entity_id = catalog_product_entity.entity_id
            where catalog_product_entity_varchar.attribute_id = {$related_id_attr_id}";
    
    $products = $cart->get_db()->rows($sql);
        
    $product = Mage::getModel('catalog/product');
    
    $data = '';
    foreach($products as $pid)
    {
        $smallImage = '';
        $image_d = '';
        $galleryImages = '';
        
        $product->load($pid['entity_id']);
        
        $smallImage = $product->getData('small_image');
        $image_d = $product->getData('image');
             
        $galleryData = $product->getData('media_gallery');
       
        if(count($galleryData['images']) > 0)
        {
            foreach ($galleryData['images'] as &$image) 
            {
                $galleryImages .= $image['file'] . ";";
            }
            
            $galleryImages = substr($galleryImages,0,-1);
        }
        
        if($smallImage != '' || $image != '' || $galleryImages != '')
        {
            $data .= "{$pid['value']},{$smallImage},{$image_d},{$galleryImages}\r\n";
        }                                
    }     
    
    echo "<br><h1>Copy the image_data.txt file to the new server and run the restore <Br>  magento_image_migration.php?restore</h1></br>";
    echo $data;
    
    file_put_contents($rdi_path . "image_data.txt", $data);
}
else if(isset($_GET['restore']))
{
    //$img_load = new rdi_cart_image_load();
    
    $f = fopen($rdi_path . "image_data.txt", "r");

    while (($data = fgetcsv($f, 1000, ",")) !== FALSE) 
    {      
        $product = Mage::getModel('catalog/product');
        
        $product_entity_type_id = $cart->get_db()->cell("select entity_type_id from eav_entity_type where entity_type_code = 'catalog_product'", 'entity_type_id');
    
        //get the attribute id for the related id
        $related_id_attr_id = $cart->get_db()->cell("select attribute_id from eav_attribute where entity_type_id = {$product_entity_type_id} and attribute_code = 'related_id'");
            
        //get the entity_id
        $sql = "Select entity_id from catalog_product_entity_varchar where attribute_id = {$related_id_attr_id} and value = '{$data[0]}'";
        $entity_id = $cart->get_db()->cell($sql);

        if($entity_id != '')
        {
            $product->load($entity_id);

            $product->setSmallImage($data[1]);
            $product->setImage($data[2]);

            if($data[3] != '')
            {
                $gallery_data = explode(';', $data[3]);

                foreach($gallery_data as $img)
                {            
                   if($img != '')
                   {        
                        $product->addImageToMediaGallery ("../" . $rdi_path . "media/catalog/product" . $img, array ('image'), false, false);          
                   }
                }
            }

            $product->save();
        }
    }   
    
    fclose($f);
}
else
{
    echo "Usage
          <br>first dump the image data on the first site via magento_image_migration.php?dump
          <br>copy the image_data.txt from the rdi folder and place it into the rdi folder on the new server
          <br>Make sure the image files are copied over to the new server in the same location as they were before
          <br>now run the restore on the new server <br>magento_image_migration.php?restore
          <br>and should really remove this file when not needed anymore";
}
    

?>
