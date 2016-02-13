<?php
/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * XML Download Orders Class
 *
 * Extends rdi_import_xml. Functions specific for writing order data.
 *
 * PHP version 5.3
 *
 * @author     Peter Bliss <pbliss@retaildimensions.com>
 * @author     Tom Martin <tmartin@retaildimensions.com>
 * @author     PMBliss <tmartin@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * @package     Core\Export\Download\Orders\RPro9
 */
class rdi_download_returns extends rdi_export_xml 
{
    /**
     * Constructor Class
     * @param rdi_db $db
     */
    public function rdi_download_returns($db = '')
    {
        if ($db)
            $this->set_db($db);        
    }
    
    /**
     * Main load function
     * @global rdi_db $debug
     * @global rdi_lib $db_lib
     */
    public function download_returns()
    {
        global $debug, $db_lib;
        
        $this->create_document('DOCUMENT');
        
        $records = $this->db_connection->rows('SELECT DISTINCT * FROM rpro_out_returns');        
        $sos = $this->create_child('SOS');
        
        if(is_array($records) && count($records) > 0)
        {
            foreach($records as $record)            
            {   
				$orderid = $record['orderid'];
				
				unset( $record['orderid']);
				
                $so = $this->append_record($sos, "SO", $record, array_keys($record));
				
				$customer = $this->db_connection->row("SELECT * FROM rpro_out_return_customer WHERE orderid = '{$this->db_connection->clean($orderid)}'");
				
                $this->append_record($so, "CUSTOMER", $customer, array_keys($customer));
				
				$shipto_customer = $this->db_connection->row("SELECT * FROM rpro_out_return_shipto_customer WHERE orderid = '{$this->db_connection->clean($orderid)}'");
				$this->_var_dump($shipto_customer);
				if(empty($shipto_customer) || count($shipto_customer) < 3)
				{
					$shipto_customer = $customer;
				}
                $this->append_record($so, "SHIPTO_CUSTOMER", $shipto_customer, array_keys($shipto_customer));

                $this->append_record($so, "SO_SUPPLS", $record, array(" "));        
                $this->append_record($so, "SO_COMMENTS", $record, array(" "));        
                $this->append_record($so, "SO_INSTRS", $record, array(" "));
                //add this later
                 
				 
                $this->append_record($so, "SO_FEES", $record, array(" "));            
                $tenders = $this->append_record($so, "SO_TENDERS", $record, array(" "));
                
                /**
                 * Tender Types: 0 = Cash, 1 = Check, 2 = Credit Card, 3 = COD, 4 = Charge,
                              5 = Store Credit, 6 = Split, 7 = Deposit, 8 = Payments,
                              9 = Gift Certificate, 10 = Gift Card, 11 = Debit Card, 
                              12 = Foreign Currency, 13 = Traveler's Chk, 14 = Foreign Check
                 */
                
                
                /**
                 * determine the tender type from the field value
                 */
                
                
                $fields = array(
                                    "tender_type",
                                    "crd_type",
                                    "crd_name",
                                    "cardholder_name"
                                );       
                $this->append_record($tenders, "SO_TENDER", array("tender_type" => "2",
                                    "crd_type" => "",
                                    "crd_name" => "VISA",
                                    "cardholder_name" => ""), $fields);
                $this->append_record($so, "SO_DEPSTS", $record, array(" "));
                $this->append_record($so, "SO_HISTS", $record, array(" "));

                $so_items = $this->append_record($so, "SO_ITEMS", $record, array(" "));            
				
				$items = $this->db_connection->rows("SELECT * FROM rpro_out_returns_items WHERE orderid = '{$this->db_connection->clean($orderid)}'"); 
                               
                if(is_array($items))
                {
                    $pos = 1;
                    
                    foreach($items as $record)
                    {               
                        $record['item_pos'] = $pos;
                        $record['orig_item_pos']  = $pos;
                        
                        $so_item = $this->append_record($so_items, "SO_ITEM", $record, array_keys($record));

                        $fields = array("item_sid");
                        $this->append_record($so_item, "INVN_BASE_ITEM", $record, $fields);
                        
                        $pos++;
                    }
                }

            }
            // so for download as negative receipt, return for when rdice is updated.
            $this->save_document("so");
        }
        else
        {
            echo "No customers to export.";
        }
        
        //$db_lib->log_order_export_data();
    }
}
?>
