<?php
/**
 * Magento Customer Version 1.6.x Library
 * @package Customer\Magento
 * 
 */

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
    global $helper_funcs;
    
    $return_data = array();

    if(is_array($mapping))
    {
        foreach($mapping as $field)
        {   
            //this may be useful later, it will break the address into 2 fields 
            //select distinct SUBSTRING_INDEX(SUBSTRING_INDEX( `street` , '\n', 2 ),'\n', 1) as address, SUBSTRING_INDEX(SUBSTRING_INDEX( `street` , '\n', 2 ),'\n', -1) address2 from sales_flat_order_address where entity_id = 3

            //since magento does this rather strange have to split up the address into its 2 fields here manually

            if($field['special_handling'] != '' && $field['special_handling'] != null)
                $data[$field['cart_field']] = $helper_funcs->process_special_handling($field['special_handling'], $data[$field['cart_field']], $data, 'customer');
            
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

function rdi_customer_load($customer_record)
{
    global $cart;
    
    require_once("../app/Mage.php");				// External script - Load magento framework
    Mage::app();
        
    if($customer_record['entity_id'] == null)
    {
        $customer = Mage::getModel('customer/customer');                            

        $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
        
//        if($customer_record['entity_id'] == '')
//        {            
//            $customer->loadById($customer_record['entity_id']);
//        }
//        if($custo)
//        {
//            $customer->loadByEmail($customer_record['email']);
//        }
        
        //if(!$customer->getId()) {

            $customer->setEmail($customer_record['email']);
            $customer->setFirstname($customer_record['firstname']);
            $customer->setLastname($customer_record['lastname']);
            if(isset($customer_record['password']))
            {
                $customer->setPassword($customer_record['password']);
            }
            else
            {
                $customer->setPassword($customer_record['firstname'] . $customer_record['lastname']);
            }
            
        //}

        try {
            $customer->save();
            $customer->setConfirmation(null);
            $customer->save();           
        }
        catch (Exception $ex) {
            Zend_Debug::dump($ex->getMessage());
        }

        //Build billing and shipping address for customer, for checkout
        $addr_data = explode(',', $customer_record['city']);

        $_custom_address = array (
            'firstname' => $customer_record['firstname'],
            'lastname' => $customer_record['lastname'],
            'street' => array (
                '0' => $customer_record['street'],
                '1' => $customer_record['street2'],                
            ),                                

            'city' => $addr_data[0],
            'region_id' => '',
            'region' => $addr_data[1],
            'postcode' => $customer_record['postcode'],
            'country_id' => $customer_record['country_id'],                                 
            'telephone' => $customer_record['telephone'],
        );

        $customAddress = Mage::getModel('customer/address');
        //$customAddress = new Mage_Customer_Model_Address();
        $customAddress->setData($_custom_address)
                    ->setCustomerId($customer->getId())
                    ->setIsDefaultBilling('1')
                    ->setIsDefaultShipping('1')
                    ->setSaveInAddressBook('1');

        try {
            $customAddress->save();
        }
        catch (Exception $ex) {
            Zend_Debug::dump($ex->getMessage());
        }
        
        $id = $customer->getId();
        
        if($id)
        {
            $cart->get_db()->exec("update {$cart->get_db()->get_db_prefix()}customer_entity set related_id = '{$customer_record['related_id']}' where entity_id = " . $id);

            Mage::getSingleton('checkout/session')->getQuote()->setBillingAddress(Mage::getSingleton('sales/quote_address')->importCustomerAddress($customAddress));
        }
    }    
}

function rdi_update_customer($customer_record)
{
    global $cart;
    
    require_once("../app/Mage.php");				// External script - Load magento framework
    Mage::app();        
    
    $customer = Mage::getModel('customer/customer');                            

    $customer->setWebsiteId(Mage::app()->getWebsite()->getId());

    if($customer_record['entity_id'] == '')
    {            
        $customer->load($customer_record['entity_id']);
    }
    else if($customer_record['email'])
    {
        $customer->loadByEmail($customer_record['email']);
    }

    //if(!$customer->getId()) {

        //$customer->setEmail($customer_record['email']);
        $customer->setFirstname($customer_record['firstname']);
        $customer->setLastname($customer_record['lastname']);
        //$customer->setPassword($password);
    //}

    try {
        $customer->save();
        $customer->setConfirmation(null);
        $customer->save();           
    }

    catch (Exception $ex) {
        Zend_Debug::dump($ex->getMessage());
    }

    $customerAddressId = $customer->getDefaultBilling();
    
    if($customerAddressId != null)
    {
        if ($customerAddressId){
               $_custom_address = Mage::getModel('customer/address')->load($customerAddressId);
        }

        $data = $_custom_address->getData();

    }
    else
    {
        $data = array();
    }
    
    //Build billing and shipping address for customer, for checkout
    $addr_data = explode(',', $customer_record['city']);

//    $_custom_address = array (
//        'firstname' => $customer_record['firstname'],
//        'lastname' => $customer_record['lastname'],
//        'street' => array (
//            '0' => $customer_record['street'],
//            '1' => $customer_record['street2'],                
//        ),                                
//
//        'city' => $addr_data[0],
//        'region_id' => '',
//        'region' => $addr_data[1],
//        'postcode' => $customer_record['postcode'],
//        'country_id' => $customer_record['country_id'],                                 
//        'telephone' => $customer_record['telephone'],
//    );

    $data['city'] = $addr_data[0];
    $data['postcode'] = $customer_record['postcode'];
    $data['region'] = $addr_data[1];
    $data['street'] = array (
                                '0' => $customer_record['street'],
                                '1' => $customer_record['street2'],                
                            );
    $data['telephone'] = $customer_record['telephone'];

    $customAddress = Mage::getModel('customer/address');
    //$customAddress = new Mage_Customer_Model_Address();
    $customAddress->setData($data)
                ->setCustomerId($customer->getId())
                ->setIsDefaultBilling('1')
                ->setIsDefaultShipping('1')
                ->setSaveInAddressBook('1');

    try {
        $customAddress->save();

        $cart->get_db()->exec("update {$cart->get_db()->get_db_prefix()}customer_entity set related_id = '{$customer_record['related_id']}' where entity_id = " . $customer->getId());
    }
    catch (Exception $ex) {
        Zend_Debug::dump($ex->getMessage());
    }


    ///Mage::getSingleton('checkout/session')->getQuote()->setBillingAddress(Mage::getSingleton('sales/quote_address')->importCustomerAddress($customAddress));

}

?>
