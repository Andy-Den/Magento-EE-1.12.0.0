<?php
/**
 * Class File
 */
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * XML Import Styles Class
 *
 * Extendes rdi_import_xml. Functions specific for bringing in the style data.
 *
 * PHP version 5.3
 *
 * @author     Tom Martin <tmartin@retaildimensions.com>
 * @author     PMBliss <pmbliss@retaildimensions.com>
 * @copyright  2005-2014 Retail Dimensions Inc.
 * @version    1.0.0
 * 
 * @package    Core\Import\Styles\RPro9
 */
class rdi_import_styles_xml extends rdi_import_xml {

    /**
     * Class Variables
     */
    protected $style_sid;               // Stored for use in item nodes
    public $_style_sid = array();               // Stored for use in item nodes

    public function __construct($db = '')
    {
        parent::__construct($db);
        $this->hook_name = str_replace("rdi_", "", __CLASS__);
    }
    
    /**
     * Preload function with chaining     *
     * @name pos_import_styles_xml_pre_load
     * 
     * @global type $hook_handler
     * @return \rdi_import_styles_xml
     */
    public function pre_load()
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("pos_import_styles_xml_pre_load");
        
        return $this;
    }
    
    /**
     * post load function with chaining
     * 
     * 
     * @global type $hook_handler
     * @return \rdi_import_styles_xml
     */
    public function post_load()
    {   
        global $hook_handler;
              
        
        $hook_handler->call_hook("pos_import_styles_xml_post_load");
        
        return $this;
    }
    
    /**
     * Main load function
     */
    public function load()
    {
        $this->pre_load()->upload_styles()->post_load();
    }
    
    /**
     * Upload Styles from specified folder and process xml into database staging tables.
     * 
     * @global type $inPath
     * @global type $rdi_path
     * @return \rdi_import_styles_xml
     */
    public function upload_styles()
    {   
        global $inPath, $rdi_path;
        
        $dirHandle = @opendir($rdi_path . $inPath);
        if (!$dirHandle) 
        {
            /**
             *  if the directory cannot be opened skip upload
             */
            echo 'Cannot open the directory' . $rdi_path . $inPath;
        } 
        else 
        {
            /*while ($file = readdir($dirHandle))             
            {
                if ($file != "."     
                        && strpos(strtolower($file), "item") === 0
                        && substr(strtolower($file), -4) == '.xml'
                        && file_exists($inPath . '/' . $file)
                        && is_readable($inPath . '/' . $file)
                   ) */
            //This will load the items in order and replace as it goes.
            $files = array_reverse(glob($rdi_path . $inPath . DIRECTORY_SEPARATOR ."item*.xml"));
                        
            if(!empty($files))
            {
                foreach($files as $file)           
                {                                                           
                    $this->load_from_file($file);
             
                    if($this->root)
                    {
                        $this->load_style_nodes();
                        
                        
                        $this->print_success();
                    }
                    
                    /**
                     * move the file to a backup folder
                     */
                    rename( $file, dirname($file) . DIRECTORY_SEPARATOR . 'archive' . DIRECTORY_SEPARATOR . basename($file));
                }
            }           
        }
        
        /**
         *  Close directory handle
         */
        closedir($dirHandle);
        
        return $this;
    }

    /**
     * Processes a style node.
     */
    public function load_style_nodes()
    {   
	
	if ($this->root->childNodes) 
        {        
            for($i=0;$i< $this->root->childNodes->length ;$i++) 
            {
                $node = $this->root->childNodes->item($i);

                if($node->nodeName == "Style") 
                {             
                    $data = array();
                    
                    $data['style_sid'] = $this->get_value($node, "StyleSID");
                    
                    if(isset($this->_style_sid[$data['style_sid']]))
                    {
                        continue;
                    }
                    
                    $this->_style_sid[$data['style_sid']] = 1;
                    
                    $data['scale'] = $this->get_value($node, "Scale") != '' ? $this->get_value($node, "Scale") : 0;
                    $data['scale_name'] = $this->get_value($node, "ScaleName");
                    $data['dcs'] = $this->get_value($node, "DCS");
                    $data['dcs_name'] = $this->get_value($node, "DCSName");
                    $data['department_code'] = $this->get_value($node, "DepartmentCode");
                    $data['department_name'] = $this->get_value($node, "DepartmentName");
                    $data['class_code'] = $this->get_value($node, "ClassCode");
                    $data['class_name'] = $this->get_value($node, "ClassName");
                    $data['subclass_code'] = $this->get_value($node, "SubclassCode");
                    $data['vendor'] = $this->get_value($node, "Vendor");
                    $data['vendor_code'] = $this->get_value($node, "VendorCode");
                    $data['vend_info1'] = $this->get_value($node, "VendInfo1");
                    $data['vend_info2'] = $this->get_value($node, "VendInfo2");
                    $data['desc1'] = $this->get_value($node, "Desc1");
                    $data['desc2'] = $this->get_value($node, "Desc2");
                    $data['desc3'] = $this->get_value($node, "Desc3");
                    $data['desc4'] = $this->get_value($node, "Desc4");
                    $data['eci'] = $this->get_value($node, "ECI");
                    $data['long_desc'] = $this->get_value($node, "LongDesc");                                       
                    $data['product_name'] = $this->get_value($node, "ProductName");  
                    $data['style_image'] = $this->get_value($node, "StyleImage"); 
                    $data['alt1_desc'] = $this->get_value($node, "Alt1Desc");
                    $data['alt2_desc'] = $this->get_value($node, "Alt2Desc");
                    $MetaTtile = $this->get_value($node, "MetaTtile");
					
                    if(isset($MetaTtile) && $MetaTtile !== null && $MetaTtile !== '' && strtolower($MetaTtile) !== 'null')
                    {
                            $data['meta_title'] = $MetaTtile;
                    }
                    else
                    {
                            $data['meta_title'] = $this->get_value($node, "MetaTitle");
                    }
                    $data['meta_keywords'] = $this->get_value($node, "MetaKeywords");
                    $data['meta_desc'] = $this->get_value($node, "MetaDesc");
                    $data['threshold'] = $this->get_value($node, "Threshold");
                    $data['avail'] = $this->get_value($node, "Avail");
                    $data['out_of_stock_msg'] = $this->get_value($node, "OutOfStock");
                    
                    if ($this->db_connection) {                    
                        $this->db_connection->insertAr($this->db_lib->get_table_name('in_styles'), $data, true);
                    }
                    
                    for($j=0;$j< $node->childNodes->length ;$j++)             
                    {
                        $child_node = $node->childNodes->item($j);
                        
                        if($child_node->nodeName == "Item") 
                        {
                            $cData = array();
                            
                            $cData['style_sid'] = $this->get_value($node,"StyleSID");
                            $cData['item_sid'] = $this->get_value($child_node,"ItemSID");
							//protection against item_sid = style_sid
							$cData['item_sid'] .= ($cData['item_sid']==$cData['style_sid']?'X':'');
                            $cData['item_num'] = $this->get_value($child_node,"ItemNum");
                            $cData['alu'] = $this->get_value($child_node,"ALU");
                            $cData['upc'] = $this->get_value($child_node,"UPC");
                            $cData['ship_weight1'] = $this->get_value($child_node,"ShipWeight1");
                            $cData['ship_weight2'] = $this->get_value($child_node,"ShipWeight2");
                            $cData['oversized'] = $this->get_value($child_node,"Oversized");
                            $cData['ship_method'] = $this->get_value($child_node,"ShipMethod");
                            $cData['featured'] = $this->get_value($child_node,"Featured");
                            $cData['height'] = $this->get_value($child_node,"Height");
                            $cData['length'] = $this->get_value($child_node,"Length");
                            $cData['width'] = $this->get_value($child_node,"Width");
                            $cData['dim_unit'] = $this->get_value($child_node,"DimUnit");
                            $cData['weight_unit'] = $this->get_value($child_node,"WeightUnit");
                            $cData['desc3'] = $this->get_value($child_node, "Description3");
                            $cData['desc4'] = $this->get_value($child_node, "Description4");
                            $cData['text1'] = $this->get_value($child_node,"Text1");
                            $cData['text2'] = $this->get_value($child_node,"Text2");
                            $cData['text3'] = $this->get_value($child_node,"Text3");
                            $cData['text4'] = $this->get_value($child_node,"Text4");
                            $cData['text5'] = $this->get_value($child_node,"Text5");
                            $cData['text6'] = $this->get_value($child_node,"Text6");
                            $cData['text7'] = $this->get_value($child_node,"Text7");
                            $cData['text8'] = $this->get_value($child_node,"Text8");
                            $cData['text9'] = $this->get_value($child_node,"Text9");
                            $cData['text10'] = $this->get_value($child_node,"Text10");
                            $cData['udf_date'] = $this->get_value($child_node,"UDFDate");
                            $cData['udf_name'] = $this->get_value($child_node,"UDFName");
                            $cData['udf1'] = $this->get_value($child_node,"UDF1");
                            $cData['udf2'] = $this->get_value($child_node,"UDF2");
                            $cData['udf3'] = $this->get_value($child_node,"UDF3");
                            $cData['udf4'] = $this->get_value($child_node,"UDF4");
                            $cData['udf5'] = $this->get_value($child_node,"UDF5");
                            $cData['udf6'] = $this->get_value($child_node,"UDF6");
                            $cData['udf7'] = $this->get_value($child_node,"UDF7");
                            $cData['udf8'] = $this->get_value($child_node,"UDF8");
                            $cData['udf9'] = $this->get_value($child_node,"UDF9");
                            $cData['udf10'] = $this->get_value($child_node,"UDF10");
                            $cData['udf11'] = $this->get_value($child_node,"UDF11");
                            $cData['udf12'] = $this->get_value($child_node,"UDF12");
                            $cData['udf13'] = $this->get_value($child_node,"UDF13");
                            $cData['udf14'] = $this->get_value($child_node,"UDF14");
                            $cData['attr'] = $this->get_value($child_node,"Attr");
                            $cData['attr_code'] = $this->get_value($child_node,"AttrCode");
                            $cData['size'] = $this->get_value($child_node,"Size");
                            $cData['size_code'] = $this->get_value($child_node,"SizeCode");
                            $cData['attr_order'] = $this->get_value($child_node,"AttrOrder");
                            $cData['size_order'] = $this->get_value($child_node,"SizeOrder");
                            $cData['cost'] = $this->get_value($child_node,"Cost");
                            $cData['price'] = $this->get_value($child_node,"Price");
                            $cData['markdown_price'] = $this->get_value($child_node,"MarkdownPrice");
                            $cData['reg_price'] = $this->get_value($child_node,"RegPrice");
                            $cData['sale_price'] = $this->get_value($child_node,"SalePrice");
                            $cData['msrp_price'] = $this->get_value($child_node,"MSRPPrice");
                            $cData['wholesale_price'] = $this->get_value($child_node,"WholesalePrice");
                            $cData['quantity'] = $this->get_value($child_node,"Quantity");
                            $cData['so_committed'] = $this->get_value($child_node,"SOCommitted");
                            $cData['open_po'] = $this->get_value($child_node,"OpenPO");
                            $cData['comp_quantity'] = $this->get_value($child_node,"CompQuantity");
                            $cData['qty_per_case'] = $this->get_value($child_node,"QtyPerCase");
                            $cData['tax_code'] = $this->get_value($child_node,"TaxCode");
                            $cData['active'] = $this->get_value($child_node,"Active");
                            $cData['excluded'] = $this->get_value($child_node,"IsExcluded");
                            
                            
                            
                            $this->db_connection->insertAr($this->db_lib->get_table_name('in_items'), $cData, true);
                            
                            $this->db_connection->insertAr('rdi_color_size_codes', array(
                                'related_id'=>$cData['item_sid'],
                                'related_parent_id'=>$cData['style_sid'],
                                'color' => $cData['attr'],
                                'color_code' => $cData['attr_code'],
                                'size' => $cData['size'],
                                'size_code' => $cData['size_code']
                                ), true
                            );
                        }
                    }
                }                
            }
        }
    }

}

?>
