<?php

// ========== Init setup ========== //
//error_reporting(0);
require_once ('../../app/Mage.php');
//Mage::app();
if (false) {
    $isSame = False;
    $word = '1111';
    $word_arr = array();
    for ($i = 0; $i < strlen($word); $i ++) {
        $word_arr[] = $word[$i];
        if ($word_arr[$i] == $word[$i])
            $isSame = True;
    }
    var_dump($isSame);
}

if (false) {
    $rmaImport = Mage::getModel('hpintwms/import')->syncRmas('now');
}

if (true) {
    $mapArray = array(
        'amz_material' => 'ca_amz_material' , 
        'amz_search_color' => 'ca_amz_search_color' , 
        'ebay_price' => 'ca_ebay_price' , 
        'ebay_search_color' => 'ca_ebay_search_color' , 
        'model' => 'name' , 
        'ship_weight' => 'weight' , 
        'web_search_color' => 'shoe_color_config' , 
        'color' => 'shoe_color_manu' , 
        'size' => 'shoe_size' , 
        'width' => 'shoe_width' , 
        'web_price' => 'price' , 
        'wh_qty' => 'orderflow_wms_stock' , 
        'vendor_qty' => 'orderflow_dropship_stock' , 
        'vendor_qty_last_update' => 'orderflow_dropship_stock_date' , 
        'web_sale_price' => 'special_price' , 
        'suggested_retail' => 'msrp' , 
        'web_sale_price' => 'special_price' , 
        'discontinued' => 'status' , 
        'ca_sku' => 'sku' , 
        'map_price' => 'msrp_enabled' , 
        'waterproof' => 'shoe_waterproof' , 
        'steel_toe' => 'shoe_steel_toe' , 
        'eco_friendly' => 'shoe_eco_friendly' , 
        'occupational' => 'shoe_occupational' , 
        'material' => 'shoe_material' , 
        'dept_code' => '_category'
    );
    
    $columnMap = array(
        'web_sale_price' => array(
            '0.00' => ''
        ) , 
        'discontinued' => array(
            '0' => '1' , 
            '1' => '2'
        ) , 
        'map_price' => array(
            '0' => 'No' , 
            '1' => 'Yes'
        ) , 
        'class' => array(
            'Stock' => 1 , 
            'Special' => 2 , 
            'Markdown' => 3 , 
            'PhaseOut' => 4 , 
            'eBay' => 5
        ) , 
        'inventory_control' => array(
            'Shoemart' => 1 , 
            'All' => 2 , 
            'Message' => 3 , 
            'Invisible' => 4
        ) , 
        'order_class' => array(
            'Default' => 1 , 
            'MakeUp' => 2 , 
            'NextCut' => 3 , 
            'NextCutSpecial' => 4
        ) , 
        'map_price' => array(
            array(
                'gt0' => array(
                    0 => 'No' , 
                    1 => 'Yes' // is enabled
                )
            )
        ) , 
        'dept_code' => array(
            'MXA' => 'Men/Accessories' , 
            'MXAB' => 'Men/Accessories/Belts' , 
            'MXAH' => 'Men/Accessories/Hats' , 
            'MXAI' => 'Men/Accessories/Innersoles' , 
            'MXAL' => 'Men/Accessories/Leather Goods' , 
            'MXAC' => 'Men/Accessories/Shoe Care' , 
            'MXAO' => 'Men/Accessories/Shoe Horns' , 
            'MXAT' => 'Men/Accessories/Shoe Trees' , 
            'MXAA' => 'Men/Accessories/Wallets' , 
            'MXAG' => 'Men/Bags/Bags' , 
            'MXC' => 'Men/Clothing' , 
            'MXAPC' => 'Men/Clothing/Compression' , 
            'MXAPCU' => 'Men/Clothing/Compression/Body Suit' , 
            'MXAPCP' => 'Men/Clothing/Compression/Pants' , 
            'MXAPCH' => 'Men/Clothing/Compression/Shirts' , 
            'MXAPCHL' => 'Men/Clothing/Compression/Shirts/Long Sleve' , 
            'MXAPCHS' => 'Men/Clothing/Compression/Shirts/Short Sleve' , 
            'MXAPCHT' => 'Men/Clothing/Compression/Shirts/Tank Top' , 
            'MXAPCO' => 'Men/Clothing/Compression/Shorts' , 
            'MXAPCV' => 'Men/Clothing/Compression/Sleeves' , 
            'MXAPCS' => 'Men/Clothing/Compression/Socks' , 
            'MXCJ' => 'Men/Clothing/Jackets' , 
            'MXCP' => 'Men/Clothing/Pants' , 
            'MXCS' => 'Men/Clothing/Shirts' , 
            'MXCSL' => 'Men/Clothing/Shirts/Long Sleve' , 
            'MXCSS' => 'Men/Clothing/Shirts/Short Sleve' , 
            'MXCH' => 'Men/Clothing/Shorts' , 
            'MXAS' => 'Men/Clothing/Socks' , 
            'MXCW' => 'Men/Clothing/Sweaters' , 
            'MXS' => 'Men/Shoes' , 
            'MXSA' => 'Men/Shoes/Athletic Shoes' , 
            'MXSAB' => 'Men/Shoes/Athletic Shoes/Basketball' , 
            'MXSAO' => 'Men/Shoes/Athletic Shoes/Bowling' , 
            'MXSAE' => 'Men/Shoes/Athletic Shoes/Cleats' , 
            'MXSAEB' => 'Men/Shoes/Athletic Shoes/Cleats/Baseball' , 
            'MXSAEF' => 'Men/Shoes/Athletic Shoes/Cleats/Football' , 
            'MXSAES' => 'Men/Shoes/Athletic Shoes/Cleats/Soccer' , 
            'MXSAC' => 'Men/Shoes/Athletic Shoes/Cross-Training' , 
            'MXSAG' => 'Men/Shoes/Athletic Shoes/Golf' , 
            'MXSAH' => 'Men/Shoes/Athletic Shoes/Hiking' , 
            'MXSAK' => 'Men/Shoes/Athletic Shoes/Hockey' , 
            'MXSAL' => 'Men/Shoes/Athletic Shoes/Lacrosse' , 
            'MXSAR' => 'Men/Shoes/Athletic Shoes/Running' , 
            'MXSARS' => 'Men/Shoes/Athletic Shoes/Running/Street' , 
            'MXSARA' => 'Men/Shoes/Athletic Shoes/Running/Track' , 
            'MXSART' => 'Men/Shoes/Athletic Shoes/Running/Trail' , 
            'MXSAT' => 'Men/Shoes/Athletic Shoes/Tennis' , 
            'MXSAU' => 'Men/Shoes/Athletic Shoes/Umpire' , 
            'MXSAV' => 'Men/Shoes/Athletic Shoes/Volleyball' , 
            'MXSAW' => 'Men/Shoes/Athletic Shoes/Walking' , 
            'MXSAS' => 'Men/Shoes/Athletic Shoes/Watersport' , 
            'MXSAI' => 'Men/Shoes/Athletic Shoes/Weight Lifting' , 
            'MXSAY' => 'Men/Shoes/Athletic Shoes/Yoga-Pilates' , 
            'MXSB' => 'Men/Shoes/Boots' , 
            'MXSBC' => 'Men/Shoes/Boots/Casual' , 
            'MXSBD' => 'Men/Shoes/Boots/Dress' , 
            'MXSBH' => 'Men/Shoes/Boots/Hiking' , 
            'MXSBU' => 'Men/Shoes/Boots/Hunting' , 
            'MXSBN' => 'Men/Shoes/Boots/Linesman' , 
            'MXSBL' => 'Men/Shoes/Boots/Logger' , 
            'MXSBI' => 'Men/Shoes/Boots/Military' , 
            'MXSBM' => 'Men/Shoes/Boots/Motorcycle' , 
            'MXSBR' => 'Men/Shoes/Boots/Rain' , 
            'MXSBS' => 'Men/Shoes/Boots/Snow' , 
            'MXSBE' => 'Men/Shoes/Boots/Western' , 
            'MXSBW' => 'Men/Shoes/Boots/Work' , 
            'MXSC' => 'Men/Shoes/Casual Shoes' , 
            'MXSCB' => 'Men/Shoes/Casual Shoes/Boat' , 
            'MXSCC' => 'Men/Shoes/Casual Shoes/Clog' , 
            'MXSCM' => 'Men/Shoes/Casual Shoes/Monk Strap' , 
            'MXSCMB' => 'Men/Shoes/Casual Shoes/Monk Strap/Bicycle-Toe' , 
            'MXSCMC' => 'Men/Shoes/Casual Shoes/Monk Strap/Cap-Toe' , 
            'MXSCMM' => 'Men/Shoes/Casual Shoes/Monk Strap/Moc-Toe' , 
            'MXSCMP' => 'Men/Shoes/Casual Shoes/Monk Strap/Plain-Toe' , 
            'MXSCMS' => 'Men/Shoes/Casual Shoes/Monk Strap/Split-Toe' , 
            'MXSCMW' => 'Men/Shoes/Casual Shoes/Monk Strap/Wing-Tip' , 
            'MXSCL' => 'Men/Shoes/Casual Shoes/Oxford' , 
            'MXSCLB' => 'Men/Shoes/Casual Shoes/Oxford/Bicycle-Toe' , 
            'MXSCLC' => 'Men/Shoes/Casual Shoes/Oxford/Cap-Toe' , 
            'MXSCLM' => 'Men/Shoes/Casual Shoes/Oxford/Moc-Toe' , 
            'MXSCLP' => 'Men/Shoes/Casual Shoes/Oxford/Plain-Toe' , 
            'MXSCLS' => 'Men/Shoes/Casual Shoes/Oxford/Split-Toe' , 
            'MXSCLW' => 'Men/Shoes/Casual Shoes/Oxford/Wing-Tip' , 
            'MXSCS' => 'Men/Shoes/Casual Shoes/Slip-On' , 
            'MXSCSB' => 'Men/Shoes/Casual Shoes/Slip-On/Bicycle-Toe' , 
            'MXSCSC' => 'Men/Shoes/Casual Shoes/Slip-On/Cap-Toe' , 
            'MXSCSM' => 'Men/Shoes/Casual Shoes/Slip-On/Moc-Toe' , 
            'MXSCSY' => 'Men/Shoes/Casual Shoes/Slip-On/Penny Loafer' , 
            'MXSCSP' => 'Men/Shoes/Casual Shoes/Slip-On/Plain-Toe' , 
            'MXSCSS' => 'Men/Shoes/Casual Shoes/Slip-On/Split-Toe' , 
            'MXSCST' => 'Men/Shoes/Casual Shoes/Slip-On/Tassle' , 
            'MXSCSW' => 'Men/Shoes/Casual Shoes/Slip-On/Wing-Tip' , 
            'MXSCI' => 'Men/Shoes/Casual Shoes/Slipper' , 
            'MXSCW' => 'Men/Shoes/Casual Shoes/Work' , 
            'MXSDM' => 'Men/Shoes/Dress Shoes/Monk Strap' , 
            'MXSDMB' => 'Men/Shoes/Dress Shoes/Monk Strap/Bicycle-Toe' , 
            'MXSDMC' => 'Men/Shoes/Dress Shoes/Monk Strap/Cap-Toe' , 
            'MXSDMF' => 'Men/Shoes/Dress Shoes/Monk Strap/Formal' , 
            'MXSCMM' => 'Men/Shoes/Dress Shoes/Monk Strap/Moc-Toe' , 
            'MXSCMP' => 'Men/Shoes/Dress Shoes/Monk Strap/Plain-Toe' , 
            'MXSCMS' => 'Men/Shoes/Dress Shoes/Monk Strap/Split-Toe' , 
            'MXSCMW' => 'Men/Shoes/Dress Shoes/Monk Strap/Wing-Tip' , 
            'MXSDL' => 'Men/Shoes/Dress Shoes/Oxford' , 
            'MXSDLB' => 'Men/Shoes/Dress Shoes/Oxford/Bicycle-Toe' , 
            'MXSDLC' => 'Men/Shoes/Dress Shoes/Oxford/Cap-Toe' , 
            'MXSDLF' => 'Men/Shoes/Dress Shoes/Oxford/Formal' , 
            'MXSDLM' => 'Men/Shoes/Dress Shoes/Oxford/Moc-Toe' , 
            'MXSDLP' => 'Men/Shoes/Dress Shoes/Oxford/Plain-Toe' , 
            'MXSDLS' => 'Men/Shoes/Dress Shoes/Oxford/Split-Toe' , 
            'MXSDLW' => 'Men/Shoes/Dress Shoes/Oxford/Wing-Tip' , 
            'MXSDS' => 'Men/Shoes/Dress Shoes/Slip-On' , 
            'MXSDSB' => 'Men/Shoes/Dress Shoes/Slip-On/Bicycle-Toe' , 
            'MXSDSC' => 'Men/Shoes/Dress Shoes/Slip-On/Cap-Toe' , 
            'MXSDSF' => 'Men/Shoes/Dress Shoes/Slip-On/Formal' , 
            'MXSDSM' => 'Men/Shoes/Dress Shoes/Slip-On/Moc-Toe' , 
            'MXSDSY' => 'Men/Shoes/Dress Shoes/Slip-On/Penny Loafer' , 
            'MXSDSP' => 'Men/Shoes/Dress Shoes/Slip-On/Plain-Toe' , 
            'MXSDSS' => 'Men/Shoes/Dress Shoes/Slip-On/Split-Toe' , 
            'MXSDST' => 'Men/Shoes/Dress Shoes/Slip-On/Tassle' , 
            'MXSDSW' => 'Men/Shoes/Dress Shoes/Slip-On/Wing-Tip' , 
            'MXSFD' => 'Men/Shoes/Factory Damage' , 
            'MXSS' => 'Men/Shoes/Sandals' , 
            'MXSSF' => 'Men/Shoes/Sandals/Flip-Flop' , 
            'MXSSS' => 'Men/Shoes/Sandals/Slide' , 
            'MXSSP' => 'Men/Shoes/Sandals/Sport' , 
            'MXSSR' => 'Men/Shoes/Sandals/Strappy' , 
            'MXSST' => 'Men/Shoes/Sandals/Thong' , 
            'WXA' => 'Woman/Accessories' , 
            'WXAB' => 'Woman/Accessories/Belts' , 
            'WXAH' => 'Woman/Accessories/Hats' , 
            'WXAI' => 'Woman/Accessories/Innersoles' , 
            'WXAE' => 'Woman/Accessories/Laces' , 
            'WXAL' => 'Woman/Accessories/Leather Goods' , 
            'WXAC' => 'Woman/Accessories/Shoe Care' , 
            'WXAH' => 'Woman/Accessories/Shoe Horns' , 
            'WXAT' => 'Woman/Accessories/Shoe Trees' , 
            'WXAA' => 'Woman/Accessories/Wallets' , 
            'WXAG' => 'Woman/Bags/Bags' , 
            'WXC' => 'Woman/Clothing' , 
            'WXAPC' => 'Woman/Clothing/Compression' , 
            'WXAPCU' => 'Woman/Clothing/Compression/Body Suit' , 
            'WXAPCB' => 'Woman/Clothing/Compression/Bras' , 
            'WXAPCP' => 'Woman/Clothing/Compression/Pants' , 
            'WXAPCH' => 'Woman/Clothing/Compression/Shirts' , 
            'WXAPCHL' => 'Woman/Clothing/Compression/Shirts/Long Sleve' , 
            'WXAPCHS' => 'Woman/Clothing/Compression/Shirts/Short Sleve' , 
            'WXAPCHT' => 'Woman/Clothing/Compression/Shirts/Tank Top' , 
            'WXAPCO' => 'Woman/Clothing/Compression/Shorts' , 
            'WXAPCV' => 'Woman/Clothing/Compression/Sleeves' , 
            'WXAPCS' => 'Woman/Clothing/Compression/Socks' , 
            'WXCJ' => 'Woman/Clothing/Jackets' , 
            'WXCP' => 'Woman/Clothing/Pants' , 
            'WXCS' => 'Woman/Clothing/Shirts' , 
            'WXCSL' => 'Woman/Clothing/Shirts/Long Sleve' , 
            'WXCSS' => 'Woman/Clothing/Shirts/Short Sleve' , 
            'WXCH' => 'Woman/Clothing/Shorts' , 
            'WXAS' => 'Woman/Clothing/Socks' , 
            'WXCW' => 'Woman/Clothing/Sweaters' , 
            'WXS' => 'Woman/Shoes' , 
            'WXSA' => 'Woman/Shoes/Athletic Shoes' , 
            'WXSAB' => 'Woman/Shoes/Athletic Shoes/Basketball' , 
            'WXSAO' => 'Woman/Shoes/Athletic Shoes/Bowling' , 
            'WXSAE' => 'Woman/Shoes/Athletic Shoes/Cleats' , 
            'WXSAEB' => 'Woman/Shoes/Athletic Shoes/Cleats/Baseball' , 
            'WXSAEF' => 'Woman/Shoes/Athletic Shoes/Cleats/Football' , 
            'WXSAES' => 'Woman/Shoes/Athletic Shoes/Cleats/Soccer' , 
            'WXSAC' => 'Woman/Shoes/Athletic Shoes/Cross-Training' , 
            'WXSAG' => 'Woman/Shoes/Athletic Shoes/Golf' , 
            'WXSAH' => 'Woman/Shoes/Athletic Shoes/Hiking' , 
            'WXSAK' => 'Woman/Shoes/Athletic Shoes/Hockey' , 
            'WXSAL' => 'Woman/Shoes/Athletic Shoes/Lacrosse' , 
            'WXSAR' => 'Woman/Shoes/Athletic Shoes/Running' , 
            'WXSARS' => 'Woman/Shoes/Athletic Shoes/Running/Street' , 
            'WXSARA' => 'Woman/Shoes/Athletic Shoes/Running/Track' , 
            'WXSART' => 'Woman/Shoes/Athletic Shoes/Running/Trail' , 
            'WXSAT' => 'Woman/Shoes/Athletic Shoes/Tennis' , 
            'WXSAU' => 'Woman/Shoes/Athletic Shoes/Umpire' , 
            'WXSAV' => 'Woman/Shoes/Athletic Shoes/Volleyball' , 
            'WXSAW' => 'Woman/Shoes/Athletic Shoes/Walking' , 
            'WXSAS' => 'Woman/Shoes/Athletic Shoes/Watersport' , 
            'WXSAI' => 'Woman/Shoes/Athletic Shoes/Weight Lifting' , 
            'WXSAY' => 'Woman/Shoes/Athletic Shoes/Yoga-Pilates' , 
            'WXSB' => 'Woman/Shoes/Boots' , 
            'WXSBC' => 'Woman/Shoes/Boots/Hiking' , 
            'WXSBD' => 'Woman/Shoes/Boots/Hunting' , 
            'WXSBH' => 'Woman/Shoes/Boots/Military' , 
            'WXSBU' => 'Woman/Shoes/Boots/Motorcycle' , 
            'WXSBN' => 'Woman/Shoes/Boots/Rain' , 
            'WXSBL' => 'Woman/Shoes/Boots/Snow' , 
            'WXSBI' => 'Woman/Shoes/Boots/Western' , 
            'WXSBM' => 'Woman/Shoes/Boots/Work' , 
            'WXSBR' => 'Woman/Shoes/Boots/Ankle' , 
            'WXSBS' => 'Woman/Shoes/Boots/Mid Calf' , 
            'WXSBE' => 'Woman/Shoes/Boots/Knee High' , 
            'WXSBW' => 'Woman/Shoes/Boots/Thigh High' , 
            'WXSC' => 'Woman/Shoes/Casual Shoes' , 
            'WXSCB' => 'Woman/Shoes/Casual Shoes/Boat' , 
            'WXSCC' => 'Woman/Shoes/Casual Shoes/Clogs' , 
            'WXSCE' => 'Woman/Shoes/Casual Shoes/Wedge' , 
            'WXSCF' => 'Woman/Shoes/Casual Shoes/Flats' , 
            'WXSCH' => 'Woman/Shoes/Casual Shoes/Heels' , 
            'WXSCI' => 'Woman/Shoes/Casual Shoes/Slippers' , 
            'WXSCL' => 'Woman/Shoes/Casual Shoes/Oxford' , 
            'WXSCS' => 'Woman/Shoes/Casual Shoes/Slip-On' , 
            'WXSCSD' => 'Woman/Shoes/Casual Shoes/Slip-On/Driving Moc' , 
            'WXSCSM' => 'Woman/Shoes/Casual Shoes/Slip-On/Mary Jane' , 
            'WXSCST' => 'Woman/Shoes/Casual Shoes/Slip-On/Tassle' , 
            'WXSCSY' => 'Woman/Shoes/Casual Shoes/Slip-On/Penny Loafer' , 
            'WXSCW' => 'Woman/Shoes/Casual Shoes/Work' , 
            'WXSD' => 'Woman/Shoes/Dress Shoes' , 
            'WXSDC' => 'Woman/Shoes/Dress Shoes/Clog' , 
            'WXSDE' => 'Woman/Shoes/Dress Shoes/Wedge' , 
            'WXSDF' => 'Woman/Shoes/Dress Shoes/Flats' , 
            'WXSDH' => 'Woman/Shoes/Dress Shoes/Heels' , 
            'WXSDL' => 'Woman/Shoes/Dress Shoes/Oxford' , 
            'WXSDS' => 'Woman/Shoes/Dress Shoes/Slip-On' , 
            'WXSDSM' => 'Woman/Shoes/Dress Shoes/Slip-On/Mary Jane' , 
            'WXSDST' => 'Woman/Shoes/Dress Shoes/Slip-On/Tassle' , 
            'WXSDSY' => 'Woman/Shoes/Dress Shoes/Slip-On/Penny Loafer' , 
            'WXSS' => 'Woman/Shoes/Sandals' , 
            'WXSSF' => 'Woman/Shoes/Sandals/Flip-Flop' , 
            'WXSSS' => 'Woman/Shoes/Sandals/Slide' , 
            'WXSSP' => 'Woman/Shoes/Sandals/Sport' , 
            'WXSSR' => 'Woman/Shoes/Sandals/Strappy' , 
            'WXSST' => 'Woman/Shoes/Sandals/Thong' , 
            'KBS' => 'Kids/Boys' , 
            'KBSA' => 'Kids/Boys/Athletic Shoes' , 
            'KBSB' => 'Kids/Boys/Boots' , 
            'KBSC' => 'Kids/Boys/Casual Shoes' , 
            'KBSD' => 'Kids/Boys/Dress Shoes' , 
            'KBSK' => 'Kids/Boys/Skate Shoes' , 
            'KBSS' => 'Kids/Boys/Sandals' , 
            'KGS' => 'Kids/Girls' , 
            'KGSA' => 'Kids/Girls/Athletic Shoes' , 
            'KGSB' => 'Kids/Girls/Boots' , 
            'KGSC' => 'Kids/Girls/Casual Shoes' , 
            'KGSD' => 'Kids/Girls/Dress Shoes' , 
            'KGSK' => 'Kids/Girls/Skate Shoes' , 
            'KGSS' => 'Kids/Girls/Sandals'
        )
    );
    
    $columnDelete = array(
        'season' ,  // REASON ALL SET TO None
        'sku' // REASON: Useing ca_sku
    );
    
    $columnAdd1 = array(
        '_attribute_set' => 'shoemart_shoe' , 
        '_root_category' => 'Shoemart' , 
        '_product_websites' => 'shoemart' , 
        'tax_class_id' => 2 , 
        '_media_attribute_id' => 88 , 
        '_media_image' => '' , 
        'image' => '' , 
        'thumbnail' => '' , 
        'small_image' => '' , 
        '_media_is_disabled' => ''
    );
    $columnAdd2 = array(
        'qty' => '' , 
        'is_in_stock' => '' , 
        'visibility' => '' , 
        'description' => ''
    );
    $columnAdd = $columnAdd1 + $columnAdd2;
    
    include_once @'J:\www\shoemart\dev\WMS\currentChopperSM.php';
    
    $class = new importManip();
    //    $fileName = @'C:\Users\Steven\Documents\Comps\sheoM\inv\width_split\ValidInventory.csv';
    //    $sortedFile = $class->fileSystemSort($fileName);
    //    $newFileName = $class->splitOnMissingColumn($sortedFile, 'width');
    //    $newFileName2 = $class->splitOnMissingColumn($newFileName, 'size');
    //    $newFileName3 = $class->splitOnMissingColumn($newFileName2, 'color');
    //    $newFileName4 = $class->splitOnMissingColumn($newFileName3, 'model');
    //    $newFileName4 = @'J:\www\shoemart\dev\WMS\files\ValidInventory-sorted_N_width.csv';
    //    $emptyCols = $class->findEmptyColumns($newFileName4);
    //    $newFileName5 = $class->manipFieldsAndHeader($newFileName4, $mapArray, $columnMap, array_unique(array_merge($emptyCols, $columnDelete)), $columnAdd);
    //$newFileName5 = @'C:\Users\Steven\Documents\Comps\sheoM\inv\width_split\ValidInventory-sorted_Y_width_Y_size_Y_color_Y_model-map.csv';
    //    $newFileName6 = $class->SMaddInImageLinks($newFileName5, @'J:\www\shoemart\dev\WMS\files\file_listing.txt');
    $newFileName6 = @'J:\www\shoemart\dev\WMS\files\ValidInventory-sorted_N_width-map-move.csv';
    $chopperClass = new Flattomageimport($newFileName6, @'J:\www\shoemart\dev\WMS\files\chopped', true);
    $chopperClass->masterFunction();
    $newFileName7 = $class->SMmanipAfterChop(@'J:\www\shoemart\dev\WMS\files\chopped\*.csv');
}

class importManip
{

    public function importAttributeBySku($attributeName, $fileLocation)
    {
        $varienFile = new Varien_File_Csv();
        $data = $varienFile->getData();
        
        $product = Mage::getModel('catalog/product');
        foreach ($data as $row) {
            list ($sku, $description) = $row;
            if ($id = $product->getIdBySku($sku)) {
                $product = Mage::getModel('catalog/product');
                $product->setId($id);
                $product->setDescription($description);
                $product->save();
            }
        }
    }

    public function SMaddInImageLinks($fileLocationIn, $imageFileLocation, $append = '-img')
    {
        echo PHP_EOL . 'ADDING IMAGES' . PHP_EOL;
        $mediaImage = '_media_image';
        $stockNumber = 'stock_number';
        $vendorCode = 'vendor_code';
        $color = 'shoe_color_manu';
        
        $fileLocationOut = $this->appendPreSuffix($fileLocationIn, $append, '.csv');
        $fileMoveCode = $this->appendPreSuffix($fileLocationIn, '-move', '.csv');
        $file = $this->_getFileIo($fileLocationIn, 'r');
        $imageFile = $this->_getFileIo($imageFileLocation, 'r');
        $fileOut = $this->_getFileIo($fileLocationOut, 'w+');
        $filemoveCodeOut = $this->_getFileIo($fileMoveCode, 'w+');
        
        $header = $file->streamReadCsv();
        $imageIndex = array_search($mediaImage, $header);
        $vendorCodeIndex = array_search($vendorCode, $header);
        $stockNumberIndex = array_search($stockNumber, $header);
        $colorIndex = array_search($color, $header);
        
        while (($imageLine = $imageFile->streamRead())) {
            $imageLineArray = explode('/', $imageLine);
            if (count($imageLineArray) == 3) {
                list ($imageDot, $imageVendorCode, $imageStockNumber) = explode('/', $imageLine);
                $imageStockNumber = trim($imageStockNumber);
                $imageStockNumber = trim($imageStockNumber, ':');
                while ($imageLine = $imageFile->streamRead()) {
                    if (strlen($imageLine) > 3) {
                        $imageLine = substr($imageLine, 0, strlen($imageLine) - 2);
                        $imageBuffer[$imageVendorCode][$imageStockNumber][] = $imageLine;
                    } else {
                        break 1;
                    }
                }
            }
        }
        $fileOut->streamWriteCsv($header);
        
        // Do rest
        $count = 0;
        $stockNumber = '';
        $vendorCode = '';
        while ($row = $file->streamReadCsv()) {
            if ($stockNumber != $row[$stockNumberIndex]) {
                $stockNumber = $row[$stockNumberIndex];
                $vendorCode = $row[$vendorCodeIndex];
                if (isset($imageBuffer[$vendorCode][$stockNumber])) {
                    $tempBuffer = array();
                    foreach ($imageBuffer[$vendorCode][$stockNumber] as $imageLine) {
                        if ($imageLine == 'Thumbs.db') {
                            continue;
                        }
                        $tempArray = explode('_', $imageLine);
                        if (! isset($tempArray[1])) {
                            echo 'Odd: ' . $tempArray[0] . PHP_EOL;
                            continue;
                        }
                        list ($type, $stockNumberExtra) = $tempArray;
                        $imageLocationString = $vendorCode . '/' . $stockNumber . '/' . $imageLine;
                        $rdyImageDir = "pics_rdy/{$vendorCode}/{$stockNumber}";
                        $rdyImageString = "{$rdyImageDir}/{$vendorCode}_{$stockNumber}__{$row[$colorIndex]}__{$type}.jpg";
                        
                        $tempBuffer[] = $rdyImageString;
                        $moveCode[] = "mkdir -p \"../{$rdyImageDir}\"; cp \"./{$imageLocationString}\" \"../{$rdyImageString}\"";
                    }
                    
                    $row[$imageIndex] = implode('|', $tempBuffer);
                }
            }
            
            $fileOut->streamWriteCsv($row);
        }
        
        foreach ($moveCode as $line) {
            $filemoveCodeOut->streamWrite($line . PHP_EOL);
        }
        
        echo PHP_EOL . 'DONE ADDING IMAGES' . PHP_EOL;
        return $fileLocationOut;
    }

    /**
     * This splits an import CSV on a missing column value
     *
     * @param string $fileLocation
     * @param string $columnName
     * 
     * @return string New File's Name
     */
    public function splitOnMissingColumn($fileLocation, $columnName)
    {
        echo PHP_EOL . 'SPLITTING ' . $fileLocation . ' on ' . $columnName . PHP_EOL;
        
        $fileLocationDir = dirname($fileLocation);
        
        $file = new Varien_Io_File();
        $file->cd($fileLocationDir);
        $file->streamOpen($fileLocation, 'r');
        $header = $file->streamReadCsv();
        $missingColumnIndex = array_search($columnName, $header);
        
        $outFileNameY = $this->appendPreSuffix($fileLocation, '_Y_' . $columnName, '.csv');
        $outFileNameN = $this->appendPreSuffix($fileLocation, '_N_' . $columnName, '.csv');
        
        $fileHaveMissing = new Varien_Io_File();
        $fileForMissing = new Varien_Io_File();
        $fileHaveMissing->cd($fileLocationDir);
        $fileForMissing->cd($fileLocationDir);
        $fileHaveMissing->streamOpen($outFileNameY);
        $fileForMissing->streamOpen($outFileNameN);
        $fileHaveMissing->streamWriteCsv($header);
        $fileForMissing->streamWriteCsv($header);
        
        while ($row = $file->streamReadCsv()) {
            if (empty($row[$missingColumnIndex])) {
                $fileForMissing->streamWriteCsv($row);
            } else {
                $fileHaveMissing->streamWriteCsv($row);
            }
        }
        
        return $outFileNameY;
    }

    /**
     * This calls the built in system Sort
     * Uses two temp Files
     * @todo Support Unix by checking and useing -o on sort
     * 
     * @param string $fileName The Starting File
     * @param bool $isDeleteAfter Clean up the two temp files
     * @return string New File's Name
     */
    public function fileSystemSort($fileName, $isDeleteAfter = true)
    {
        $fileLocationDir = dirname($fileName);
        $finalSortedSuffixName = $this->appendPreSuffix($fileName, '-sorted', '.csv');
        $tempNoHeader = $this->appendPreSuffix($fileName, '-temp', '.csv');
        
        // Move over
        $header = $this->_getOpenAndMove($fileName, $tempNoHeader, true);
        
        // Sort and -o should exist in most/all systems TODO -o may be /o
        $returnVar = 0;
        $sortedNoHeader = $this->appendPreSuffix($tempNoHeader, '-sorted', '.csv');
        system('sort "' . $tempNoHeader . '" /o "' . $sortedNoHeader . '"', $returnVar);
        
        // Move Back
        $this->_getOpenAndMove($sortedNoHeader, $finalSortedSuffixName, false, $header);
        
        // Clean Up
        if ($isDeleteAfter) {
            echo PHP_EOL . 'CLEANING UP ';
            
            $fileIo = new Varien_Io_File();
            $fileIo->cd($fileLocationDir);
            $result1 = $fileIo->rm($tempNoHeader);
            $result2 = $fileIo->rm($sortedNoHeader);
            
            echo PHP_EOL . $tempNoHeader . ': ' . $result1;
            echo PHP_EOL . $sortedNoHeader . ': ' . $result2 . PHP_EOL;
        }
        
        return $finalSortedSuffixName;
    }

    /**
     * Wrapper to get a, set up VarienFileIo
     *
     * @param string $fileLocationIn
     * @param string $mode See Fopen
     * @return Varien_Io_File
     */
    protected function _getFileIo($fileLocationIn, $mode = 'w+')
    {
        $file = new Varien_Io_File();
        $file->cd(dirname($fileLocationIn));
        $file->streamOpen($fileLocationIn, $mode);
        return $file;
    }

    protected function _SMloadDescriptionMap($dMapLocation)
    {
        $file = $this->_getFileIo($dMapLocation, 'r');
        
        $map = array();
        $header = $file->streamReadCsv();
        while ($row = $file->streamReadCsv()) {
            $map[$row[0]] = $row[1];
        }
        
        return $map;
    }

    public function SMmanipAfterChop($fileDirLocation, $work = null, $append = '-mod')
    {
        echo PHP_EOL . 'STARTING MANIP AFTER CHOP' . PHP_EOL;
        $dMapLocation = @'J:\www\shoemart\dev\WMS\files\descriptions-commas.csv';
        $dMap = $this->_SMloadDescriptionMap($dMapLocation);
        foreach (glob($fileDirLocation) as $fileLocationIn) {
            echo PHP_EOL . "STARTING $fileLocationIn" . PHP_EOL;
            $fileLocationOut = $this->appendPreSuffix($fileLocationIn, $append, '.csv');
            $file = $this->_getFileIo($fileLocationIn, 'r');
            $fileOut = $this->_getFileIo($fileLocationOut, 'w+');
            
            $header = $file->streamReadCsv();
            $fileOut->streamWriteCsv($header);
            
            $indexSku = array_search('sku', $header);
            $indexType = array_search('_type', $header);
            $indexVisibility = array_search('visibility', $header);
            $indexWMSQty = array_search('orderflow_wms_stock', $header);
            $indexDropshipQty = array_search('orderflow_dropship_stock', $header);
            $indexQty = array_search('qty', $header);
            $indexIsInStock = array_search('is_in_stock', $header);
            $indexDescription = array_search('description', $header);
            $indexCat = array_search('_category', $header);
            $indexRootCat = array_search('_root_category', $header);
            
            while ($row = $file->streamReadCsv()) {
                // Map Qty
                if (empty($row[$indexType])) {
                    $row[$indexQty] = '';
                    $row[$indexIsInStock] = '';
                } elseif ($row[$indexType] == 'simple') {
                    $row[$indexQty] = $row[$indexDropshipQty] + $row[$indexWMSQty];
                    $row[$indexIsInStock] = $row[$indexQty] > 0 ? 1 : 0;
                } elseif ($row[$indexType] == 'configurable') {
                    $row[$indexQty] = '';
                    $row[$indexIsInStock] = 1;
                }
                
                // Map Visibility
                if (empty($row[$indexType])) {
                    $row[$indexVisibility] = '';
                } elseif ($row[$indexType] == 'simple') {
                    $row[$indexVisibility] = 1;
                } elseif ($row[$indexType] == 'configurable') {
                    $row[$indexVisibility] = 4;
                }
                
                // Map Description
                if (empty($row[$indexType])) {
                    $row[$indexDescription] = '';
                } elseif ($row[$indexType] == 'simple') {
                    $row[$indexDescription] = 'S Product Desc';
                } elseif ($row[$indexType] == 'configurable') {
                    $row[$indexDescription] = (empty($dMap[$row[$indexSku]])) ? 'Sample Product Description' : $dMap[$row[$indexSku]];
                }
                
                // Map Categories -> Men => Men's || Woman => Women's (Note plural change)
                $catTree = explode('/', $row[$indexCat]);
                switch ($catTree[0]) {
                    case 'Woman':
                        $catTree[0] = "Women's";
                        break;
                    case 'Men':
                        $catTree[0] = "Men's";
                        break;
                }
                $row[$indexCat] = implode('/', $catTree);
                
                $fileOut->streamWriteCsv($row);
            }
            $fileOut->streamClose();
            echo "ENDING $fileLocationIn => $fileLocationOut" . PHP_EOL;
        }
        
        echo PHP_EOL . 'ENDING MANIP AFTER CHOP' . PHP_EOL;
        return true;
    }

    public function manipFieldsAndHeader($fileLocationIn, $headerInfo, $columnsInfo, $deleteInfo, $addInfo, $append = '-map')
    {
        echo PHP_EOL . 'MAPPING' . PHP_EOL;
        
        $fileLocationOut = $this->appendPreSuffix($fileLocationIn, $append, '.csv');
        $file = $this->_getFileIo($fileLocationIn, 'r');
        $fileOut = $this->_getFileIo($fileLocationOut, 'w+');
        $header = $file->streamReadCsv();
        
        foreach ($deleteInfo as $columnName) {
            $unsetIndexes[] = array_search($columnName, $header);
        }
        
        foreach ($columnsInfo as $names => $info) {
            $columnIndexs[array_search($names, $header)] = $info;
        }
        
        // HEADER
        // Map Header
        foreach ($header as $index => $value) {
            if (isset($headerInfo[$value])) {
                $header[$index] = $headerInfo[$value];
            }
        }
        
        // unset Indexed in Header
        foreach ($unsetIndexes as $index) {
            unset($header[$index]);
        }
        
        //add to header
        $header = array_merge($header, array_keys($addInfo));
        foreach ($addInfo as $name => $value) {
            $addIndexs[array_search($name, $header)] = $value;
        }
        $fileOut->streamWriteCsv($header);
        
        // Do rest
        $count = 0;
        while ($row = $file->streamReadCsv()) {
            $count ++;
            if ($count % 10000 == 0) {
                echo 'Row: ' . $count . PHP_EOL;
            }
            
            // Map // TODO move this
            foreach ($columnIndexs as $columnIndex => $map) {
                if (isset($map[$row[$columnIndex]]) || (isset($map[0]) && is_array($map[0]))) {
                    if (isset($map[0]) && is_array($map[0])) {
                        list ($key, $value) = each($map[0]);
                        switch ($key) {
                            case 'gt0':
                                $dval = doubleval($row[$columnIndex]);
                                $row[$columnIndex] = empty($dval) ? $value[0] : $value[1];
                                break;
                            default:
                                break;
                        }
                    } else {
                        $row[$columnIndex] = $map[$row[$columnIndex]];
                    }
                }
            }
            
            // Unset
            foreach ($unsetIndexes as $index) {
                unset($row[$index]);
            }
            
            // ADD 
            $row = array_values($row);
            $row = $row + $addIndexs;
            
            // Write
            $fileOut->streamWriteCsv($row);
        }
        
        echo 'MAPPING DONE Count: ' . $count . PHP_EOL;
        return $fileLocationOut;
    }

    public function addColumns($fileLocationIn, $columnsFixed, $columnMap, $append = '-add')
    {
        $file = new Varien_Io_File();
        $file->cd(dirname($fileLocationIn));
        $file->streamOpen($fileLocationIn, 'r');
        
        $fileOutLocation = $this->appendPreSuffix($fileLocationIn, $column, '.csv');
        $fileOut = new Varien_Io_File();
        $fileOut->cd(dirname($fileOutLocation));
        $fileOut->streamOpen($fileOutLocation);
        $header = $file->streamReadCsv();
        
        foreach ($columns as $column) {
            foreach ($column as $append) {
                ;
            }
        
        }
    }

    public function mapField($fileLocationIn, $column, $map, $append = null)
    {
        $file = new Varien_Io_File();
        $file->cd(dirname($fileLocationIn));
        $file->streamOpen($fileLocationIn, 'r');
        
        $fileOutLocation = $this->appendPreSuffix($fileLocationIn, $column, '.csv');
        $fileOut = new Varien_Io_File();
        $fileOut->cd(dirname($fileOutLocation));
        $fileOut->streamOpen($fileOutLocation);
        $header = $file->streamReadCsv();
        
        $columnIndex = array_search($column, $header);
        
        while ($row = $file->streamReadCsv()) {
            if (isset($map[$row[$columnIndex]])) {
                $row[$columnIndex] = $map[$row[$columnIndex]];
            }
            $fileOut->streamWriteCsv($row);
        }
        
        return $fileOutLocation;
    }

    public function mapHeader($fileLocationIn, $map, $append = '-hmap')
    {
        $fileLocationOut = $this->appendPreSuffix($fileLocationIn, $append, '.csv');
        echo PHP_EOL . "-{$fileLocationOut}-" . PHP_EOL;
        $fileInHandle = fopen($fileLocationIn, 'r');
        $fileOutHandle = fopen($fileLocationOut, 'w+');
        
        $header = fgetcsv($fileInHandle);
        foreach ($header as $index => $value) {
            if (isset($map[$value])) {
                $header[$index] = $map[$value];
            }
        }
        
        fputcsv($fileOutHandle, $header);
        $this->moveFileContents($fileInHandle, $fileOutHandle);
        fclose($fileInHandle);
        fclose($fileOutHandle);
        
        return $fileLocationOut;
    }

    public function deleteColumns($fileLocation, $columns, $append = '-dCol')
    {
        if (empty($columns)) {
            return;
        }
        
        $file = new Varien_Io_File();
        $file->cd(dirname($fileLocation));
        $file->streamOpen($fileLocation, 'r');
        
        $fileOutLocation = $this->appendPreSuffix($fileLocation, $append, '.csv');
        $fileOut = new Varien_Io_File();
        $fileOut->cd(dirname($fileOutLocation));
        $fileOut->streamOpen($fileOutLocation);
        $header = $file->streamReadCsv();
        
        if (! is_array($columns)) {
            $columns = array(
                $columns
            );
        }
        
        // Get Indexes
        foreach ($columns as $column) {
            $unsetIndexes[] = array_search($column, $header);
        }
        
        // unset Indexed in Header
        foreach ($unsetIndexes as $index) {
            unset($header[$index]);
        }
        $fileOut->streamWriteCsv($header);
        
        // Unset and write
        while ($row = $file->streamReadCsv()) {
            foreach ($unsetIndexes as $index) {
                unset($row[$index]);
            }
            $fileOut->streamWriteCsv($row);
        }
        
        return $fileOutLocation;
    }

    public function findEmptyColumns($fileLocation)
    {
        echo PHP_EOL . 'FINDING Empty COLUMNS' . PHP_EOL;
        $results = array();
        $endResult = array();
        
        $file = new Varien_Io_File();
        $file->cd(dirname($fileLocation));
        $file->streamOpen($fileLocation, 'r');
        
        $header = $file->streamReadCsv();
        foreach ($header as $index => $value) {
            $results[] = true;
        }
        
        $rowsToCheck = $results;
        
        while ($row = $file->streamReadCsv()) {
            foreach ($rowsToCheck as $index => $column) {
                if (! empty($row[$index])) { //|| $row[$index] == 0) {
                    $results[$index] = false;
                    unset($rowsToCheck[$index]);
                }
            }
        }
        
        foreach ($results as $index => $allEmpty) {
            if ($allEmpty) {
                $endResult[] = $header[$index];
                echo 'Found: ' . $header[$index] . PHP_EOL;
            }
        }
        
        return $endResult;
    }

    public function findUnchangingValues($fileLocation, $updateField, $fieldsToCheck)
    {
        echo PHP_EOL . 'FINDING Changing COLUMNS';
        foreach($fieldsToCheck as $names){
        	echo ' - ' . $names;
        }
        echo '. In ' . basename($fileLocation, '.csv') . PHP_EOL;
        
        $results = array();
        $endResult = array();
        
        $file = new Varien_Io_File();
        $file->cd(dirname($fileLocation));
        $file->streamOpen($fileLocation, 'r');
        $header = $file->streamReadCsv();
        
        // Get Indexes
        $updateIndex = array_search($updateField, $header);
        foreach ($fieldsToCheck as $column) {
            $checkIndexes[] = array_search($column, $header);
        }
        
        $updateField = '';
        $checkArray = array();
        while ($row = $file->streamReadCsv()) {
            // Set starting value
            if ($updateField != $row[$updateIndex]) {
            	$updateField = $row[$updateIndex];
                foreach ($checkIndexes as $count => $column) {
                    $checkArray[$count] = $row[$column];
                }
            
            }
            
            //Check Against Starting Value
            foreach ($checkIndexes as $count => $column) {
                if ($checkArray[$count] != $row[$column]) {
                	$results[$column][$updateField][$row[$column]] = true;
                	$results[$column][$updateField][$checkArray[$count]] = true;
                	$checkArray[$count] = $row[$column];
                }
            }
        }
        
        foreach ($results as $index => $updateField) {
            foreach ($updateField as $name => $indexes) {
            	foreach($indexes as $value => $true)
                    echo 'Found: ' . $header[$index] . ' - ' . $name . ' - ' . $value . PHP_EOL;
            }
        }
        
        return $endResult;
    }

    protected function appendPreSuffix($fileLocation, $append, $suffix)
    {
        return dirname($fileLocation) . DIRECTORY_SEPARATOR . basename($fileLocation, $suffix) . $append . $suffix;
    }

    protected function moveFileContents($fileHandleStart, $fileHandleEnd, $halfMegChunks = 2)
    {
        $chuckSize = $halfMegChunks * 524288; // Half meg Chuck
        while ($data = fread($fileHandleStart, $chuckSize)) {
            fwrite($fileHandleEnd, $data);
        }
    }

    public function splitOnParentSku($fileName, $parentColumn)
    {

    }

    protected function _getOpenAndMove($fileLocationIn, $fileLocationOut, $isGetHeader, $inHeader = null)
    {
        $fileInHandle = fopen($fileLocationIn, 'r');
        $fileOutHandle = fopen($fileLocationOut, 'w+');
        
        if ($isGetHeader) {
            $header = fgetcsv($fileInHandle);
        } else {
            $header = null;
        }
        
        if (isset($inHeader)) {
            fputcsv($fileOutHandle, $inHeader);
        }
        
        $this->moveFileContents($fileInHandle, $fileOutHandle);
        fclose($fileInHandle);
        fclose($fileOutHandle);
        
        return $header;
    }
}