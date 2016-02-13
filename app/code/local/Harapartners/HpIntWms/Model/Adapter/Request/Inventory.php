<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */
class Harapartners_HpIntWms_Model_Adapter_Request_Inventory
{
	/**
	 * Generates the CreateItem SQL
	 * @todo Fix this up.
	 * 
	 * @param array $info ItemInfo
	 * @return array ('table','value') 
	 */
    public function getCreateItemInfo($info)
    {
        $table_header = 'Item'; // po_header
        
		// $info ['sex'] = $product->getSku ();
        $insert = array(
            'ItemNumber' => $info['id'] , 
            'Description' => substr($info['name'],0,50) , 
            'AlternateNumber' => $info['upc'] ,
        	// $info ['type'] = $info['type'],
            'UDF01' => $info['vendorCode'] ,
            'UDF02' => $info['vendorName'] , 
            'UDF04' => $info['cat'] , 
            'UDF05' => $info['stockNumber'] , 
            'UDF06' => $info['stockName'],
        	'UDF07' => $info['color'] , 
            'UDF08' => $info['size'] , 
        	'UDF09' => $info['width'] , 
        	'UDF14' => $info['sku'],
        );
        
        return array(
            'table' => $table_header , 
            'value' => $insert
        );
    }
    
    
    /**
     * Gets all inventory. If date is set, uses only newer.
     * @todo fix
     *
     * @param int|string $date
     * @return array ('from', 'where')
     */
    public function getInventory($date = null) {
		$table_name = 'Inventory';
		$date_table = 'TransactionDate';
		$from = array ($table_name, array ('*') );
		
		$where = array();
		if ($date) {
			$dateFormated = date ( "Y-m-d\TH:i:s", $date );
			$where = array (
				'type'=> 'where',
				'value' => "{$date_table} > '{$dateFormated}'",
			);
		}
		
		return array ('from' => $from, 'where' => $where );
	}
}

