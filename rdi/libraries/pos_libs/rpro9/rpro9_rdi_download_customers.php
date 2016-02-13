<?php
/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * RPro V9 specific Download XML Class
 *
 * Extends rdi_export_xml. Functions specific for bringing in the cattree data.
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     Tom Martin <tmartin@retaildimensions.com>
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package     Core\Export\Customers\RPro9
 */
class rdi_download_customers extends rdi_export_xml 
{
    /**
     * Constructor Class
     * @param rdi_db $db
     */
    public function rdi_download_customers($db = '')
    {
        if ($db)
            $this->set_db($db);        
    }
    
    /**
     * Main download customers function.
     * Holder function that does nothing. The customer information is passed through in the so xml.
     * @global type $db_lib
     */
    public function download_customers()
    {
        global $db_lib;
        
        //Not needed, gets the customer from the so record
        
        //$this->create_document('root');
        
//        $records = $this->db_connection->rows('SELECT DISTINCT * FROM rpro_out_customers');                
//        $r = $this->create_child_with_value('result', 'success');
//        
//        $customers = $this->create_child('CUSTOMERS');
//        
//        if(is_array($records))
//        {
//            foreach($records as $record)            
//            {          
//                $fields = array(
//                                    "customer_id",
//                                    "rpro_cust_sid",
//                                    "first_name",
//                                    "last_name",
//                                    "company",                
//                                    "address1",
//                                    "address2",
//                                    "city",
//                                    "state",
//                                    "region",                
//                                    "zip",
//                                    "country",
//                                    "country_code",
//                                    "phone",
//                                    "email",                
//                                    "login_id",
//                                    "password",
//                                    "orders_num",
//                                    "has_so",
//                                );
//
//                $this->append_record($customers, "CUSTOMER", $record, $fields);                     
//
//                $this->display_document();
//            }
//        }
//        else
//        {
//            echo "No customers to export.";
//        }
//        
//        $db_lib->log_customer_export_data();
    }
}
?>
