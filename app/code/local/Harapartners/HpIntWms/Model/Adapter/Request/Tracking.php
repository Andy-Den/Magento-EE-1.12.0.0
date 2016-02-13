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
class Harapartners_HpIntWms_Model_Adapter_Request_Tracking
{
	const TRACKING_SYNCED_COLUMN = 'ExportedQuantity';
	const TRACKING_SHIPPED_COLUMN = 'ActualQuantity';
	
    public function getOpenTrackings()
    {
        $table_name = 'PickingDetail'; //po_tracking
        $columnSynced = self::TRACKING_SYNCED_COLUMN;
        $from = array(
            $table_name , 
            array(
                'Site',
                'OrderNumber',
                'LineNumber',
                'ItemNumber',
                'ActualQuantity',
                'ActualDate',
                'TrackingNumber',
                'Charges',
                'Carrier',
                'Method',
            )
        );
        
        $where[] = array(
            'type' => 'where',
            'value' => "{$columnSynced} = 0"
        );
        
        $where[] = array(
            'type' => 'where',
            'value' => "TrackingNumber IS NOT NULL"
        );
        
        return array(
            'from' => $from , 
            'where' => $where
        );
    }

    public function syncBackTrackings($orderNumbersCompleted)
    {
        $table_name = 'PickingDetail'; //po_tracking
        $columnSynced = self::TRACKING_SYNCED_COLUMN;
        $columnOrderNumebr = 'OrderNumber';
        $data = array(
            $columnSynced => 1
        );
        $orderNumbersCompletedString = implode(',', $orderNumbersCompleted);
        $where = array(
            "{$columnOrderNumebr} IN({$orderNumbersCompletedString})" 
        );
        
        return array(
            'table' => $table_name , 
            'value' => $data , 
            'where' => $where
        );
    }
    
    public function syncBackTrackingsQtyEach($orderNumbersCompleted){
    	$table_name = 'PickingDetail'; //po_tracking
        $columnSynced = self::TRACKING_SYNCED_COLUMN;
        $columnShipped = self::TRACKING_SHIPPED_COLUMN;
        $columnOrderNumebr = 'OrderNumber';
        $data = array(
            $columnSynced => new Zend_Db_Expr($columnShipped)
        );
        $orderNumbersCompletedString = implode("','", $orderNumbersCompleted);
        $where = array(
            "{$columnOrderNumebr} IN('{$orderNumbersCompletedString}')" 
        );
        
        return array(
            'table' => $table_name , 
            'value' => $data , 
            'where' => $where
        );
    }
}

