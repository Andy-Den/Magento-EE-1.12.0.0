<?php

/*
 * Process simple image upload to magento
 * 
 * Place file directly into the rdi folder to use
 * 
 * put image files into the in/images
 */

$rdi_path = "";
$inPath = "in";
include "init.php";
include_once '../app/Mage.php';
umask(0);
Mage::app();


$images = array();

//see if there are any images in the in/images folder
$dirHandle = @opendir($rdi_path . $inPath . "/images");
if (!$dirHandle) 
{
    // if the directory cannot be opened skip upload
    echo 'Cannot open the directory' . $rdi_path . $inPath . "/images";
} 
else 
{
    $image_index = 0;

    while ($file = readdir($dirHandle))             
    {                
        if ($file != "."                             
                && (substr($file, -4) == '.jpg' || substr($file, -4) == '.png' || substr($file, -4) == '.gif')
                && file_exists($inPath . '/images/' . $file)
                && is_readable($inPath . '/images/' . $file)
            ) 
        {                                                
            //loop the images and pass off to the cart module for processing
            $images[] = $file;
        }                                
    }           
}

echo "images found: <br>";
print_r($images);

// Close directory handle
if($dirHandle)        
    closedir($dirHandle);

if(is_array($images))
{
    foreach($images as $image_index => $file_name)
    {
        echo "processing image: " . $file_name . "<br>";
        
        if(!file_exists('in/archive/images'))
        {
            mkdir("in/archive/images");
        }

        $product = Mage::getModel('catalog/product');

      
        //assume the file name is the related id
        //use it to find the product_id

        //get the file without the extension
        $info = pathinfo($file_name);
        $related_id =  basename($file_name,'.'.$info['extension']);

        //run the file name through the thumbnail descriptor, determines if its a thumbnail, and returns the name of the related id sans the thumbnail marking
        $thumbnail_id = thumbnail_descriptor($related_id);

        if($thumbnail_id != '')
        {
            $related_id = $thumbnail_id;
        }

        //sometimes there is a _index on the file names need to trim that off
        if(preg_match("/^(.*?)_/", $related_id, $matches) > 0)
        {
            $related_id = $matches[1];
        }          

        echo "using related id: {$related_id} <br>";
        
        //get the related id attribute id
        $related_id_attribute_id = $cart->get_db()->cell("select attribute_id from eav_attribute where attribute_code = 'related_id'");

        //get the entity id
        $product_id = $cart->get_db()->cell("select entity_id from catalog_product_entity_varchar where value = '{$related_id}' and attribute_id = {$related_id_attribute_id}");                       

        //if this is a a simple product we need to link it up under the configurable or it wont be seen
        if($product_id != '')
        {
            //get the product type
            $type = $cart->get_db()->row("select type_id, attribute_set_id from catalog_product_entity where entity_id = {$product_id}");

            //check if this type has any attributes defined, if it doesn then its really just a simple
            $sql = "select rdi_cart_class_map_fields.* from rdi_cart_class_map_fields 
                    inner join rdi_cart_class_mapping on rdi_cart_class_mapping.cart_class_mapping_id =
                    rdi_cart_class_map_fields.cart_class_mapping_id where rdi_cart_class_mapping.product_class_id = {$type['attribute_set_id']}";

            $criteria = $cart->get_db()->rows($sql);

            if($type['type_id'] == "simple" && is_array($criteria))
            {
                //get the configurable for this product
                $product_id = $cart->get_db()->cell("select parent_id from catalog_product_super_link where product_id = {$product_id}");
            }
        }
        
        if($product_id != '')
        {
            echo "found product: " . $product_id . "<br>";
            
            //echo $product_id;

            $srcPicture = 'in/images/' . $file_name;
            $destPicture = '../media/catalog/product/' . $file_name;     
            $archiveDest = 'in/archive/images/' . $file_name;

            $product->load($product_id);                                    

            $add = true;

            //need to check that the item doesnt already have the image 
            $galleryData = $product->getData('media_gallery');
            foreach ($galleryData['images'] as &$image) 
            {
                //echo basename($image['file']) . " --- " . $matches[1] . "<br>";
                //if(basename($image['file']) == $file_name)
                //if(strpos($image['file'], $matches[1]) > 0)
//                if(strpos($image['file'], basename($file_name)) > 0)
//                {
//                    $add = false;
//                    echo "product: " . $product_id . " already has this image " . $file_name . "<br>";
//                    break;
//                }      
                
                if(md5_File("../media/catalog/product/" . $image['file']) == md5_File($inPath . "/images/" . $file_name))
                {
                    $add = false;
                } 
            }

            if($add)
            {                
                if($thumbnail_id)
                {
                    $product->addImageToMediaGallery ($srcPicture, array ('thumbnail'), false, true);                    
                }
                else
                {
                    $product->addImageToMediaGallery ($srcPicture, array ('image', 'small_image'), false, false);                    
                }

                echo "assigning image file: {$file_name} to product id: {$product_id}";
                flush();
                
                if (file_exists($srcPicture)) { 
                        copy($srcPicture, $destPicture);
                        copy($srcPicture, $archiveDest);
                        unlink($srcPicture);
                }          

                $product->save(); 
            }

            echo "<br>";
        }
        else
        {
            echo "no product found for: " . $file_name . "<br>";
        }
        
        echo "<Br>--------------<br>";
        ob_flush();               
    }
}

//a regular expresion to be ran on the image file to determine if it is a thumbnail
//return the related id sans the thumbnail marking
//change as needed
function thumbnail_descriptor($file_name)
{
    if(preg_match("/(.*?)t$/", $file_name, $matches) > 0)
    {
        return $matches[1];
    }
    else
    {
        return '';
    }
}

?>
