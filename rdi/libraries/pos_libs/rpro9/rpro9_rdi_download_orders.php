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
class rdi_download_orders extends rdi_export_xml 
{
    /**
     * Constructor Class
     * @param rdi_db $db
     */
    public function rdi_download_orders($db = '')
    {
        if ($db)
            $this->set_db($db);        
    }
    
    /**
     * Main load function
     * @global rdi_db $debug
     * @global rdi_lib $db_lib
     */
    public function download_orders()
    {
        global $debug, $db_lib;
        
        $this->create_document('DOCUMENT');
        
        $records = $this->db_connection->rows('SELECT DISTINCT * FROM rpro_out_so');        
        $sos = $this->create_child('SOS');
        
        if(is_array($records) && count($records) > 0)
        {
            foreach($records as $record)            
            {            
                $fields = array(
                                    "so_sid",
                                    "sbs_no",
                                    "store_no",
                                    "station",
                                    "so_no",                
                                    "so_type",
                                    "orig_store_no",
                                    "orig_station",
                                    "trgt_store_no",
                                    "trgt_station",                
                                    "cust_sid",
                                    "addr_no",
                                    "shipto_cust_sid",
                                    "shipto_addr_no",
                                    "cust_po_no",                
                                    "status",
                                    "priority",
                                    "use_vat",
                                    "disc_perc",
                                    "disc_amt",                
                                    "disc_perc_spread",
                                    "used_disc_amt",
                                    "over_tax_perc",
                                    "over_tax_perc2",
                                    "created_date",                
                                    "modified_date",
                                    "shipping_date",
                                    "cancel_date",
                                    "note",
                                    "ref_so_sid",                
                                    "cms",
                                    "active",
                                    "verified",
                                    "held",
                                    "cms_post_date",                
                                    "pkg_no", 
                                    "doc_source", 
                                    "controller", 
                                    "orig_controller",                 
                                    "elapsed_time",                 
                                    "line_pos_seq", 
                                    "used_subtotal",                 
                                    "used_tax",                
                                    "activity_perc", 
                                    "activity_perc2", 
                                    "activity_perc3", 
                                    "activity_perc4",                 
                                    "activity_perc5",                 
                                    "detax", 
                                    "empl_sbs_no", 
                                    "empl_name",                
                                    "ship_method", 
                                    "tax_area_name", 
                                    "tax_area2_name", 
                                    "web_so_type",                 
                                    "modifiedby_sbs_no",                 
                                    "modifiedby_empl_name", 
                                    "createdby_sbs_no", 
                                    "createdby_empl_name",                
                                    "clerk_sbs_no", 
                                    "clerk_name", 
                                    "clerk_sbs_no2", 
                                    "clerk_name2",                 
                                    "clerk_sbs_no3",                 
                                    "clerk_name3", 
                                    "clerk_sbs_no4", 
                                    "clerk_name4",                
                                    "clerk_sbs_no5", 
                                    "clerk_name5", 
                                    "customer_shipping",
                                    "shipping_amt",
                                    "shipping_tax",
                                    "pos_flag_1",
                                    "pos_flag_2",
                                    "pos_flag_3",
                                    "comment1",
                                    "comment2",
                                    "comment3",
                                    "comment4",
                                    "comment5",
                                    "instruction1",
                                    "instruction2",
                                    "instruction3",
                                    "instruction4",
                                    "instruction5"
                                );
                $so = $this->append_record($sos, "SO", $record, $fields);
				
				$record['customer_empl_name'] = $record['createdby_empl_name'];
				
                $fields = array(
                                    "customer_cust_sid",                
                                    "customer_cust_id", 
                                    "customer_store_no", 
                                    "customer_station", 
                                    "customer_first_name",                 
                                    "customer_last_name",                 
                                    "customer_price_lvl", 
                                    "customer_detax",                 
                                    "customer_info1",                
                                    "customer_info2", 
                                    "customer_modified_date", 
                                    "customer_sbs_no", 
                                    "customer_cms",                 
                                    "customer_company_name",                 
                                    "customer_title", 
                                    "customer_tax_area_name", 
                                    "customer_shipping",                
                                    "customer_address1", 
                                    "customer_address2", 
                                    "customer_address3", 
                                    "customer_address4",                 
                                    "customer_address5",                 
                                    "customer_address6", 
                                    "customer_zip", 
                                    "customer_phone1",                
                                    "customer_phone2", 
                                    "customer_email", 
                                    "customer_country_name",
                                    "customer_empl_name"
                                );        
                $this->append_record($so, "CUSTOMER", $record, $fields);

                $fields = array(
                                    "shipto_customer_cust_sid",                
                                    "shipto_customer_cust_id", 
                                    "shipto_customer_store_no", 
                                    "shipto_customer_station", 
                                    "shipto_customer_first_name",                 
                                    "shipto_customer_last_name",                 
                                    "shipto_customer_price_lvl", 
                                    "shipto_customer_detax",                 
                                    "shipto_customer_info1",                
                                    "shipto_customer_info2", 
                                    "shipto_customer_modified_date", 
                                    "shipto_customer_sbs_no", 
                                    "shipto_customer_cms",                 
                                    "shipto_customer_company_name",                 
                                    "shipto_customer_title", 
                                    "shipto_customer_tax_area_name", 
                                    "shipto_customer_shipping",                
                                    "shipto_customer_address1", 
                                    "shipto_customer_address2", 
                                    "shipto_customer_address3", 
                                    "shipto_customer_address4",                 
                                    "shipto_customer_address5",                 
                                    "shipto_customer_address6", 
                                    "shipto_customer_zip", 
                                    "shipto_customer_phone1",                
                                    "shipto_customer_phone2", 
                                    "shipto_customer_email", 
                                    "shipto_customer_country_name"                               
                                );        
                $this->append_record($so, "SHIPTO_CUSTOMER", $record, $fields);

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
                $this->append_record($tenders, "SO_TENDER", $record, $fields);
                $this->append_record($so, "SO_DEPSTS", $record, array(" "));
                $this->append_record($so, "SO_HISTS", $record, array(" "));

                $so_items = $this->append_record($so, "SO_ITEMS", $record, array(" "));            
                $records = $this->db_connection->rows("SELECT * FROM rpro_out_so_items WHERE orderid = '{$record['orderid']}'");  
                               
                if(is_array($records))
                {
                    $pos = 1;
                    
                    foreach($records as $record)
                    {               
                        $record['item_pos'] = $pos;
                        $record['orig_item_pos']  = $pos;
                        
                        $fields = array(
                                        "item_pos",
                                        "item_sid",
                                        "orig_price",
                                        "orig_tax_amt",
                                        "price",
                                        "cost",
                                        "tax_code",
                                        "tax_perc",
                                        "tax_amt",
                                        "tax_code2",
                                        "tax_perc2",
                                        "tax_amt2",
                                        "ord_qty",
                                        "sent_qty",
                                        "price_lvl",
                                        "sched_no",
                                        "comm_code",
                                        "spif",
                                        "scan_upc",
                                        "serial_no",
                                        "lot_number",
                                        "kit_flag",
                                        "pkg_item_sid",
                                        "pkg_seq_no",
                                        "orig_cmpnt_item_sid",
                                        "detax",
                                        "usr_disc_perc",
                                        "shipto_cust_sid",
                                        "shipto_addr_no",
                                        "pkg_no",
                                        "udf_value1",
                                        "udf_value2",
                                        "udf_value3",
                                        "udf_value4",
                                        "activity_perc",
                                        "activity_perc2",
                                        "activity_perc3",
                                        "activity_perc4",
                                        "activity_perc5",
                                        "orig_item_pos",
                                        "promo_flag",
                                        "item_note1",
                                        "item_note2",
                                        "item_note3",
                                        "item_note4",
                                        "item_note5",
                                        "item_note6",
                                        "item_note7",
                                        "item_note8",
                                        "item_note9",
                                        "item_note10",
                                        "alt_upc",
                                        "alt_alu",
                                        "alt_cost",
                                        "alt_vend_code",
                                        "empl_sbs_no",
                                        "empl_name",
                                        "tax_area2_name",
                                        "disc_reason_name",
                                        "empl_sbs_no2",
                                        "empl_name2",
                                        "empl_sbs_no3",
                                        "empl_name3",
                                        "empl_sbs_no4",
                                        "empl_name4",
                                        "empl_sbs_no5",
                                        "empl_name5",
                                        "ship_method",
                                    );         
                        
                        
                        
                        $so_item = $this->append_record($so_items, "SO_ITEM", $record, $fields);

                        $fields = array("item_sid");
                        $this->append_record($so_item, "INVN_BASE_ITEM", $record, $fields);
                        
                        $pos++;
                    }
                }

            }
            
            $this->save_document("so");
        }
        else
        {
            echo "No customers to export.";
        }
        
        $db_lib->log_order_export_data();
    }
}
?>
