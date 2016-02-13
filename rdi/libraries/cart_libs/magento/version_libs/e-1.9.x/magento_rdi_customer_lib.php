<?php

function rdi_customer_lib_process_customer($customer_record)
{   
    global $debug, $field_mapping, $cart;
    
    $return_data = array();
           
    $customer = rdi_customer_lib_process_data($field_mapping->get_field_list_expanded('customer'),  $customer_record);
    
    $address = rdi_customer_lib_process_data($field_mapping->get_field_list_expanded('customer_address'),  $customer_record);
    
    return $return_data = array("customer" => $customer, "customer_address" => $address);
}

function rdi_customer_lib_process_data($mapping, $data)
{
    $return_data = array();

    if(is_array($mapping))
    {
        foreach($mapping as $field)
        {   
            //this may be useful later, it will break the address into 2 fields 
            //select distinct SUBSTRING_INDEX(SUBSTRING_INDEX( `street` , '\n', 2 ),'\n', 1) as address, SUBSTRING_INDEX(SUBSTRING_INDEX( `street` , '\n', 2 ),'\n', -1) address2 from sales_flat_order_address where entity_id = 3

            //since magento does this rather strange have to split up the address into its 2 fields here manually

            if($field['cart_field'] == "street" || $field['cart_field'] == 'street2')
            {    
                if(isset($return_data[$field['pos_field']]) && $return_data[$field['pos_field']] != '')
                {
                    continue;
                }
                
                if(array_key_exists('street', $data) && $field['pos_field'] != '')
                {                            
                    $street_data = explode("\n", $data['street']);                                    

                    if($field['cart_field'] == "street")
                    {
                        if(isset($street_data[0]))
                            $return_data[$field['pos_field']] = $street_data[0];
                    }
                    else
                    {
                        if(isset($street_data[1]))
                            $return_data[$field['pos_field']] = $street_data[1];
                    }                                        
                }
                else if(!array_key_exists('street', $data) && $field['pos_field'] != '')
                {
                    //use the default
                    $return_data[$field['pos_field']] = $field['default_value'];
                }
            }
            else
            {               
                //lets us use the mapping in this manner
                //to do alterate cart mapping, use the same cart id, not the alternative field,
                //set the sort order to be 1 on the alt, if the first is null it will be overwrote when it checks the second
                //check and see if this field has already been set or it equals null
                if(isset($return_data[$field['pos_field']]) && $return_data[$field['pos_field']] != '')
                {
                    continue;
                }
                
                //see if this mapping field exists
                if(array_key_exists($field['cart_field'], $data) && $field['pos_field'] != '')
                {                
                    $return_data[$field['pos_field']] = $data[$field['cart_field']];
                }
                else if(array_key_exists($field['cart_field'], $data) && $field['alternative_field'] != '')
                {                
                    $return_data[$field['alternative_field']] = $data[$field['cart_field']];
                }
                else if(!array_key_exists($field['cart_field'], $data) && $field['pos_field'] != '')
                {
                    //use the default
                    $return_data[$field['pos_field']] = $field['default_value'];
                }
            }
        }
    }

    return $return_data;
}

?>
