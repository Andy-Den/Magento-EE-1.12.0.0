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
class Harapartners_HpIntWms_Model_Adapter_Request_Order
{

    protected function _getDummyIdArray()
    {
        $time = time();
        return array(
            'TransactionID' => $time
        );
    }

    public function getPostOrderHeader($header)
    {
        $table_header = 'PickingOrder'; // po_header
        

        $insert = array(
            'Site' => $header['Site'] , 
            'OrderNumber' => $header['OrderNumber'] , 
            'OrderDate' => $header['OrderDate'] , 
            'DueDate' => $header['DueDate'] , 
            'CustomerBillTo' => $header['CustomerBillTo'] , 
            'CustomerShipTo' => $header['CustomerShipTo'] , 
            'Carrier' => $header['Carrier'] , 
            'Method' => $header['Method']
        );
        
        return array(
            'table' => $table_header , 
            'value' => $insert
        );
        
    //        $sqlStringHeader = "INSERT INTO `{$table_header}` ";
    //        $sqlStringHeader .= ' ( Site, OrderNumber, OrderDate, DueDate, CustomerBillTo, CustomerShipTo, ShipMethod) ';
    //        $sqlStringHeader .= 'VALUES ( ';
    //        $sqlStringHeader .= "'" . $header['Site'] . "'" . ' , ';
    //        $sqlStringHeader .= "'" . $header['OrderNumber'] . "'" . ' , ';
    //        $sqlStringHeader .= "'" . $header['OrderDate'] . "'" . ' , ';
    //        $sqlStringHeader .= "'" . $header['DueDate'] . "'" . ' , ';
    //        $sqlStringHeader .= "'" . $header['CustomerBillTo'] . "'" . ' , ';
    //        $sqlStringHeader .= "'" . $header['CustomerShipTo'] . "'" . ' , ';
    //        $sqlStringHeader .= "'" . $header['ShipMethod'] . "'";
    //        $sqlStringHeader .= ') ';
    //        return $sqlStringHeader;
    

    }

    public function getPostRecievingOrderHeader($header)
    {
        $table_header = 'ReceivingOrder';
        
        $insert = array(
            'Site' => $header['Site'] , 
            'OrderNumber' => $header['OrderNumber'] , 
            'OrderDate' => $header['OrderDate'] , 
            'DueDate' => $header['DueDate'] , 
            'VendorId' => $header['VendorId'] , 
            'UDF04' => 'PO' // PO || RMA
        );
        
        return array(
            'table' => $table_header , 
            'value' => $insert
        );
        
    //        $sqlStringHeader = "INSERT INTO `{$table_header}` ";
    //        $sqlStringHeader .= ' ( Site, OrderNumber, OrderDate, DueDate, CustomerBillTo, CustomerShipTo, ShipMethod) ';
    //        $sqlStringHeader .= 'VALUES ( ';
    //        $sqlStringHeader .= "'" . $header['Site'] . "'" . ' , ';
    //        $sqlStringHeader .= "'" . $header['OrderNumber'] . "'" . ' , ';
    //        $sqlStringHeader .= "'" . $header['OrderDate'] . "'" . ' , ';
    //        $sqlStringHeader .= "'" . $header['DueDate'] . "'" . ' , ';
    //        $sqlStringHeader .= "'" . $header['CustomerBillTo'] . "'" . ' , ';
    //        $sqlStringHeader .= "'" . $header['CustomerShipTo'] . "'" . ' , ';
    //        $sqlStringHeader .= "'" . $header['ShipMethod'] . "'";
    //        $sqlStringHeader .= ') ';
    //        return $sqlStringHeader;
    

    }

    public function getPostOrderItem($item)
    {
        $table_item = 'PickingDetail'; // po_item
        $insert = array(
            'Site' => $item['Site'] , 
            'OrderNumber' => $item['OrderNumber'] , 
            'LineNumber' => $item['LineNumber'] , 
            'ItemNumber' => $item['ItemNumber'] , 
            'UnitOfMeasure' => $item['UnitOfMeasure'] , 
            'OrderedQuantity' => $item['OrderedQuantity']
        );
        
        return array(
            'table' => $table_item , 
            'value' => $insert
        );
    }

    public function getPostOrderItems($items)
    {
        $table_item = 'PickingDetail'; // po_item
        foreach ($items as $item) {
            $insert[] = array(
                'Site' => $item['Site'] , 
                'OrderNumber' => $item['OrderNumber'] , 
                'LineNumber' => $item['LineNumber'] , 
                'ItemNumber' => $item['ItemNumber'] , 
                'UnitOfMeasure' => $item['UnitOfMeasure'] , 
                'OrderedQuantity' => $item['OrderedQuantity']
            );
        }
        
        return array(
            'table' => $table_item , 
            'value' => $insert
        );
        
    //        $sqlStringItems = '';
    //        foreach ($items as $item) {
    //            $sqlStringItems .= "INSERT INTO `{$table_item}` ";
    //            $sqlStringItems .= ' ( Site, OrderNumber, LineNumber, ItemNumber, UnitOfMeasure, OrderedQuantity) ';
    //            $sqlStringItems .= 'VALUES ( ';
    //            $sqlStringItems .= "'" . $item['Site'] . "'" . ' , ';
    //            $sqlStringItems .= "'" . $item['OrderNumber'] . "'" . ' , ';
    //            $sqlStringItems .= "'" . $item['LineNumber'] . "'" . ' , ';
    //            $sqlStringItems .= "'" . $item['ItemNumber'] . "'" . ' , ';
    //            $sqlStringItems .= "'" . $item['UnitOfMeasure'] . "'" . ' , ';
    //            $sqlStringItems .= "'" . $item['OrderedQuantity'] . "'";
    //            $sqlStringItems .= '); ';
    //        }
    //        return $sqlStringItems;
    }

    public function getPostRecivingOrderItems($items)
    {
        $orderItems = $this->getPostOrderItem($item);
        $orderItems['table'] = 'ReceivingDetail';
        return $orderItem;
    }

    public function getPostRecivingOrderItem($item)
    {
        $orderItem = $this->getPostOrderItem($item);
        $orderItem['table'] = 'ReceivingDetail';
        return $orderItem;
    }

    public function getPostOrderShipping($shipping)
    {
        $table_shipping = 'CustomerShipTo'; // po_shipping
        $insert = array(
            'CustomerBillTo' => $shipping['CustomerBillTo'] , 
            'CustomerShipTo' => $shipping['CustomerShipTo'] , 
            'CompanyName' => $shipping['CompanyName'] , 
            'Contact' => $shipping['CustomerName'] , 
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
        
    //        $sqlStringShipping = '';
    //        $sqlStringShipping = "INSERT INTO `{$table_shipping}` ";
    //        $sqlStringShipping .= ' ( CustomerBillTo, CustomerShipTo, CompanyName, Address1, Address2, City, State, ZipCode) ';
    //        $sqlStringShipping .= 'VALUES ( ';
    //        $sqlStringShipping .= "'" . $shipping['CustomerBillTo'] . "'" . ' , ';
    //        $sqlStringShipping .= "'" . $shipping['CustomerShipTo'] . "'" . ' , ';
    //        $sqlStringShipping .= "'" . $shipping['CompanyName'] . "'" . ' , ';
    //        $sqlStringShipping .= "'" . $shipping['Address1'] . "'" . ' , ';
    //        $sqlStringShipping .= "'" . $shipping['Address2'] . "'" . ' , ';
    //        $sqlStringShipping .= "'" . $shipping['City'] . "'" . ' , ';
    //        $sqlStringShipping .= "'" . $shipping['State'] . "'" . ' , ';
    //        $sqlStringShipping .= "'" . $shipping['ZipCode'] . "'";
    //        $sqlStringShipping .= ') ';
    //        return $sqlStringShipping;
    }

    public function getPostOrderBilling($billing)
    {
        $table_billing = 'CustomerBillTo'; // po_billing
        $insert = array(
            'CustomerBillTo' => $billing['CustomerBillTo'] , 
            'CompanyName' => $billing['CompanyName'] , 
            'Contact' => $billing['CustomerName'] , 
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
        
    //        $sqlStringBilling = '';
    //        $sqlStringBilling .= "INSERT INTO `{$table_billing}` ";
    //        $sqlStringBilling .= ' ( CustomerBillTo, CompanyName, Address1, Address2, City, State, ZipCode) ';
    //        $sqlStringBilling .= 'VALUES ( ';
    //        $sqlStringBilling .= "'" . $billing['CustomerBillTo'] . "'" . ' , ';
    //        $sqlStringBilling .= "'" . $billing['CompanyName'] . "'" . ' , ';
    //        $sqlStringBilling .= "'" . $billing['Address1'] . "'" . ' , ';
    //        $sqlStringBilling .= "'" . $billing['Address2'] . "'" . ' , ';
    //        $sqlStringBilling .= "'" . $billing['City'] . "'" . ' , ';
    //        $sqlStringBilling .= "'" . $billing['State'] . "'" . ' , ';
    //        $sqlStringBilling .= "'" . $billing['ZipCode'] . "'";
    //        $sqlStringBilling .= ') ';
    //        return $sqlStringBilling;
    }
}

