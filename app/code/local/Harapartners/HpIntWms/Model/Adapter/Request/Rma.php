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
class Harapartners_HpIntWms_Model_Adapter_Request_Rma
{
    const TRACKING_SYNCED_COLUMN = 'ExportedQuantity';
    const TRACKING_SHIPPED_COLUMN = 'ActualQuantity';

    public function getPostOrderHeader($header)
    {
        $table_header = 'ReceivingOrder'; //rma_header
        

        $insert = array(
            'Site' => $header['Site'] , 
            'OrderNumber' => $header['OrderNumber'] , 
            //// 'PickingOrderNumber' => $header['PickingOrderNumber'] , // DOES Not Exist
            'OrderDate' => $header['OrderDate'] , 
            'DueDate' => $header['DueDate'] , 
            'UDF03' => $header['PickingOrderNumber'] , 
            'UDF04' => 'RMA' // PO || RMA
        ); ////'CustomerBillTo' => $header['CustomerBillTo'] , // DOES Not Exist
        ////'CustomerShipTo' => $header['CustomerShipTo'] // DOES Not Exist
        // 'ShipMethod' => $header['ShipMethod']
        

        return array(
            'table' => $table_header , 
            'value' => $insert
        );
    }

    public function getPostOrderItem($item)
    {
        $table_item = 'ReceivingDetail';
        $insert = array(
            'Site' => $item['Site'] , 
            'OrderNumber' => $item['OrderNumber'] , 
            'LineNumber' => $item['LineNumber'] , 
            'ItemNumber' => $item['ItemNumber'] , 
            'UnitOfMeasure' => $item['UnitOfMeasure'] , 
            'OrderedQuantity' => $item['ReturnQuantity']
        );
        
        return array(
            'table' => $table_item , 
            'value' => $insert
        );
    }

    public function getPostOrderItems($items)
    {
        $table_item = 'ReceivingDetail';
        foreach ($items as $item) {
            $insert[] = array(
                'Site' => $item['Site'] , 
                'OrderNumber' => $item['OrderNumber'] , 
                'LineNumber' => $item['LineNumber'] , 
                'ItemNumber' => $item['ItemNumber'] , 
                'UnitOfMeasure' => $item['UnitOfMeasure'] , 
                'OrderedQuantity' => $item['ReturnQuantity']
            );
        }
        return array(
            'table' => $table_item , 
            'value' => $insert
        );
    }

    /*
    public function getPostOrderShipping($shipping)
    {
        $table_shipping = 'rma_shipping';
        $insert = array(
            'CustomerBillTo' => $shipping['CustomerBillTo'] , 
            'CustomerShipTo' => $shipping['CustomerShipTo'] , 
            'CompanyName' => $shipping['CompanyName'] , 
            'Address1' => $shipping['Address1'] , 
            'Address2' => $shipping['Address2'] , 
            'City' => $shipping['City'] , 
            'State' => $shipping['State'] , 
            'ZipCode' => $shipping['ZipCode']
        );
        return array(
            'table' => $table_shipping , 
            'value' => $insert
        );
    }

    public function getPostOrderBilling($billing)
    {
        $table_billing = 'rma_billing';
        $insert = array(
            'CustomerBillTo' => $billing['CustomerBillTo'] , 
            'CompanyName' => $billing['CompanyName'] , 
            'Address1' => $billing['Address1'] , 
            'Address2' => $billing['Address2'] , 
            'City' => $billing['City'] , 
            'State' => $billing['State'] , 
            'ZipCode' => $billing['ZipCode']
        );
        return array(
            'table' => $table_billing , 
            'value' => $insert
        );
    }
    */
    
    public function getCheckRmabyId($rmaId)
    {
        $table_header = 'ReceivingOrder';
        $from = array(
            $table_header , 
            array(
                '*'
            )
        );
        
        $where = array(
            'type' => 'where' , 
            'value' => "OrderNumber = '{$rmaId}'"
        );
        
        return array(
            'from' => $from , 
            'where' => $where
        );
    }

    // TODO this needs to join useing te view insteaed
    public function getRecievedRmaItems($date)
    {
        $table_item = 'rma_item'; // ReceivingDetail
        $from = array(
            $table_item , 
            array(
                '*'
            )
        );
        
        // TODO Date where and actuall QTY can be wrong
        $where = array(
            array(
                'type' => 'where' , 
                'value' => "ActualQuantity > 0"
            )
        );
        
        return array(
            'from' => $from , 
            'where' => $where
        );
    }

    public function getRecievedRmaItemsByView($date)
    {
        $table_item = 'vwRMAs';
        $from = array(
            $table_item , 
            array(
                '*'
            )
        );
        
        return array(
            'from' => $from , 
            'where' => $where
        );
    }

    public function syncBackRmaItemsFull($rmaIds)
    {
        $table_header = 'ReceivingDetail';
        $columnSynced = self::TRACKING_SYNCED_COLUMN;
        $columnShipped = self::TRACKING_SHIPPED_COLUMN;
        $columnOrderNumebr = 'OrderNumber';
        $data = array(
            $columnSynced => new Zend_Db_Expr($columnShipped)
        );
        
        $RmaNumbersCompletedString = implode("','", $rmaIds);
        $where = array(
            "{$columnOrderNumebr} IN ('{$RmaNumbersCompletedString}')"
        );
        
        return array(
            'table' => $table_header , 
            'value' => $data , 
            'where' => $where
        );
    }
}

