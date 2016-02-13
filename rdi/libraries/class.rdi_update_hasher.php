<?php
/**
 * Class File
 */


/**
 * handles the hashing of values used for update comparisons
 * not being used right now, moved to something better
 *  This is not used in general. It is lacking and creates too much overhead. The only use is for a pure api integration. Drupal commerce uses this.
 * 
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package    Core\UpdateHasher
 */
class rdi_update_hasher extends rdi_general
{    
    public function rdi_update_hasher($db = '')
    {
         if ($db)
            $this->set_db($db);  
    }
           
    public function store_field_hash($type, $cart_field, $related_id, $value)
    {                
        $this->db_connection->exec("replace into rdi_hash (field_type, cart_field, related_id, hash_value) values ('{$type}', '{$cart_field}', '{$related_id}', '" . md5($value) . "')");
    }        
    
    //loop through the list of fields and save data for the values
    public function hash_product($product)
    {
        foreach($product as $cart_field => $value)
        {
            //related id takes up different names, so have to check for it
            if($cart_field != "related_id" || $cart_field != "field_related_id")
            {
                if(isset($product['related_id']))                
                    $this->store_field_hash('product', $cart_field, $product['related_id'], $value);
                else if(isset($product['field_related_id']))
                    $this->store_field_hash('product', $cart_field, $product['field_related_id'], $value);                   
            }
        }
    }
    
    //loop through a list of products and save hash data for them
    public function hash_products($products)
    {        
        foreach($products as $product)
        {
            $this->hash_product($product);
        }
    }
}
?>
