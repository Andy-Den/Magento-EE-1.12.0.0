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
class Harapartners_ShoemartEdi_Model_Edi_Tracking
{
    const FIELD_NAME_S_RECORD_ID = 'SUM';
    
    const SECTION_HEADER = 'header';
    const SECTION_SHIPMENT = 'detail-shipment';
    const SECTION_ORDER = 'detail-order';
    const SECTION_PACK = 'detail-pack';
    const SECTION_ITEMS = 'items';
    
    const FIELD_ORDER_ID = 'PO-NUM';
    const FIELD_SHIPMENT_CARRIER = 'ROUTING'; // TODO THIS CAN BE MORE THEN ONE !!??
    const FIELD_ROUTING_NUM = 'BILL-OF-LADING';
    const FIELD_ROUTING_NUM_PACK = 'TRACKING-ID-PACKAGING-DETAILS';
    const FIELD_UPC = 'PART-NUM';
    const FIELD_QTY_SHIPPED = 'TOTAL-SHIP-UNITS';
    const FIELD_SHIP_DATE = 'TRANS-DATE';
    const FIELD_SHIP_TIME = 'TRANS-TIME';
    const FIELD_WEIGHT = 'WEIGHT'; // TODO this can be for the whole package (multi-ship)
    const FIELD_NUM_SHIP = 'LADING-QTY';
    
    /**
     * Names of header columns
     *
     * @var array
     */
    protected $headerMap = array(
        'RECORD-ID' , 
        'TRADING-PARTNER-ID' , 
        'INTERNAL-ID' , 
        'DOCUMENT-ID' , 
        'TRANS-CODE' , 
        'SHIP-CNTRL-NUM' , 
        'TRANS-DATE' , 
        'TRANS-TIME' , 
        'HL-CODE'
    );
    
    /**
     * Names of Detail Shippment columns
     *
     * @var array
     */
    protected $detailShippmentMap = array(
        'RECORD-ID' , 
        'PACK-CODE' , 
        'LADING-QTY' , 
        'WEIGHT-CODE' , 
        'WEIGHT' , 
        'MEASUREMENT-CODE' , 
        'ROUTING-SEQ-CODE' , 
        'ROUTING-ID-QUAL' , 
        'ROUTING-CODE' , 
        'ROUTING' , 
        'ROUTING-STATUS-CODE' , 
        'EQB-DESCRIPTION-CODE' , 
        'EQB-NUMBER' , 
        'REF-ID-QUAL' , 
        'BILL-OF-LADING' , 
        'SHIP-DATE-QUAL' , 
        'SHIP-DATE' , 
        'SHIP-TIME' , 
        'FOB-METHOD-PAYMENT' , 
        'FOB-LOCATION-QUAL' , 
        'FOB-TRANS-TERM-QUAL-CODE' , 
        'FOB-TRANS-TERM-CODE' , 
        'SUPPLIER-LOC-CODE-ST' , 
        'SUPPLIER-NAME-ST' , 
        'SUPPLIER-ID-QUAL-ST' , 
        'SUPPLIER-ID-CODE-ST' , 
        'SHIP-TO-COMPANY-NAME-ST' , 
        'SHIP-TO-ADDRESS1-ST' , 
        'SHIP-TO-ADDRESS2-ST' , 
        'SHIP-TO-CITY-ST' , 
        'SHIP-TO-STATE-ST' , 
        'SHIP-TO-POSTAL-CODE-ST' , 
        'SHIP-TO-COUNTRY-CODE-ST'
    );
    
    /**
     * Names of Detail Order columns
     *
     * @var array
     */
    protected $detailOrder = array(
        'RECORD-ID' , 
        'PO-NUM' , 
        'PO-DATE' , 
        'BILL-OF-LADING' , 
        'SUPPLIER-LOC-CODE-BY' , 
        'SUPPLIER-NAME-BY' , 
        'SUPPLIER-ID-QUAL-BY' , 
        'SUPPLIER-ID-CODE-BY'
    );
    
    /**
     * Names of Detail Pack columns
     *
     * @var array
     */
    protected $detailPack = array(
        'RECORD-ID' , 
        'MARKS-NUM-QUAL' , 
        'TRACKING-ID-PACKAGING-DETAILS'
    );
    
    /**
     * Names of Detail Item columns
     *
     * @var array
     */
    protected $detailItem = array(
        'RECORD-ID' , 
        'PROD-ID-QUAL' , 
        'PART-NUM' , 
        'PROD-ID-QUAL-2' , 
        'ITEM-NUM2' , 
        'PROD-ID-QUAL-3' , 
        'ITEM-NUM3' , 
        'PROD-ID-QUAL-4' , 
        'ITEM-NUM4' , 
        'SN1-ASSIGNED-ID' , 
        'TOTAL-SHIP-UNITS' , 
        'SHIP-UNIT-CODE' , 
        'SN1-SHIP-TO-DATE' , 
        'TOTAL-QUANTITY'
    );
    
    /**
     * Is the last file handle at the end
     *
     * @var bool
     */
    protected $_isLastHandleEmpty = false;

    /**
     * @return bool
     */
    public function getIsDone()
    {
        return $this->_isLastHandleEmpty;
    }

    /**
     * Gets the next shipment from the edi doc
     * @todo seperate the file logic from the mapping logic
     *
     * @param filehandle $documentHandle
     * @return array parsed info
     */
    public function getNextParseEdiTrackingDocument($documentHandle)
    {
        $outputArray = array();
        $numPackages = 0;
        
        // === Get Header === //
        $line = fgetcsv($documentHandle, 0, '|');
        if ($line == null) {
            $this->_isLastHandleEmpty = true;
            fclose($documentHandle);
            return null;
        }
        array_pop($line);
        $tempLine = array_combine($this->headerMap, $line);
        $outputArray[self::SECTION_HEADER] = $tempLine;
        
        // === Get Detail-Shippment === //
        $line = fgetcsv($documentHandle, 0, '|');
        array_pop($line);
        $tempLine = array_combine($this->detailShippmentMap, $line);
        $outputArray[self::SECTION_SHIPMENT] = $tempLine;
        $numPackages = $tempLine['LADING-QTY'];
        
        // === Get Detail-Order === //
        $line = fgetcsv($documentHandle, 0, '|');
        array_pop($line);
        $tempLine = array_combine($this->detailOrder, $line);
        $outputArray[self::SECTION_ORDER] = $tempLine;
        
        // === Get Detail-Pack (Package LvL Repeats $numPackage times) === //
        for ($i = 0; $i < $numPackages; $i ++) {
            $line = fgetcsv($documentHandle, 0, '|');
            array_pop($line);
            $tempLine = array_combine($this->detailPack, $line);
            $outputArray[self::SECTION_PACK][$i] = $tempLine;
            
            // Get the Items for this package
            $line = fgetcsv($documentHandle, 0, '|');
            while ($line != false && $line[0] == 'ITM') {
                array_pop($line);
                $tempLine = array_combine($this->detailItem, $line);
                $outputArray[self::SECTION_PACK][$i][self::SECTION_ITEMS][] = $tempLine;
                $oldFPos = ftell($documentHandle);
                $line = fgetcsv($documentHandle, 0, '|');
            }
            
            if ($line !== false) {
                // We have passed so reset it.
                fseek($documentHandle, $oldFPos, 'SEEK_SET');
            }
        }
        
        return $outputArray;
    }

    /**
     * Create Shippment from the parsed Tracking
     *
     * @param array $parsedTracking
     * @return bool
     */
    public function createShipment(array $parsedTracking)
    {
        $orderConverter = Mage::getModel('sales/convert_order');
        $orderId = $parsedTracking[self::SECTION_ORDER][self::FIELD_ORDER_ID];
        $carrier = $parsedTracking[self::SECTION_SHIPMENT][self::FIELD_SHIPMENT_CARRIER];
        $shipDate = $parsedTracking[self::SECTION_HEADER][self::FIELD_SHIP_DATE];
        $shipTime = $parsedTracking[self::SECTION_HEADER][self::FIELD_SHIP_TIME];
        $shipTotalWeight = $parsedTracking[self::SECTION_SHIPMENT][self::FIELD_WEIGHT];
        $numPackages = $parsedTracking[self::SECTION_SHIPMENT][self::FIELD_NUM_SHIP];
        
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        if (! $order || ! $order->getId()) { // TODO READD || $order->getStatus () != Harapartners_ShoemartEdi_Helper_Data::ORDER_STATUS_SENT) {
            // Invalid order, So log it and return
            Mage::log('Invalid Order ID, For a Tracking:' . $orderIncrementId . PHP_EOL, null, 'edi_transaction_sync.log', true);
            return false;
        }
        
        $allOrderItems = $order->getAllItems();
        $productArray = array();
        foreach ($allOrderItems as $count => $orderItem) {
            $productArray[$orderItem->getData('product_id')] = array(
                'item_id' => $orderItem->getData('item_id') , 
                'index' => $count
            );
        }
        
        $shipments = array();
        $shipmentItem = null;
        $count = 0;
        $totalQty = 0;
        foreach ($parsedTracking[self::SECTION_PACK] as $packageCount => $package) {
            $shipment = $orderConverter->toShipment($order);
            foreach ($package[self::SECTION_ITEMS] as $itemCount => $item) {
                // TODO This can be done using colections and one DB hit
                $mageProduct = Mage::getModel('catalog/product')->loadByAttribute('upc', $item[self::FIELD_UPC]);
                if (! $mageProduct || ! $mageProduct->getId()) {
                    // Product Not Found
                    // TODO Handle this case ?? Skip For now
                    continue;
                }
                
                $shipQty = $item[self::FIELD_QTY_SHIPPED];
                $shipmentItem = $orderConverter->itemToShipmentItem($allOrderItems[$productArray[$mageProduct->getId()]['index']]);
                $shipmentItem->setQty($shipQty);
                $shipment->addItem($shipmentItem);
                //$totalQty += $shipQty;
            }
            
            $trackingInfoArray = $this->_getTrackingArray($carrier, $package[self::FIELD_ROUTING_NUM_PACK]);
            $track = Mage::getModel('sales/order_shipment_track')->addData($trackingInfoArray);
            $shipment->setCreatedAt($this->_createTimeStamp($shipDate, $shipTime));
            $shipment->setTotalWeight((int) $shipTotalWeight / $numPackages);
            //$shipment->setTotalQty ( $totalQty );
            $shipment->addTrack($track);
            $shipment->register();
            $shipments[] = $shipment;
        }
        $saveTransaction = Mage::getModel('core/resource_transaction');
        foreach ($shipments as $shipment) {
            // Save Each Shipment !! 
            $saveTransaction->addObject($shipment);
        }
        $order->setUpdatedAt(now()); // TODO This will not be needed eventually
        $saveTransaction->addObject($order);
        try {
            $saveTransaction->save();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Uses the self::FIELD_ROUTING_NUM & self::FIELD_SHIPMENT_CARRIER
     * The will be filled with unknown if method is unknown.
     *
     * @param string $carrier
     * @param string $trackingNumber
     * @return array
     */
    protected function _getTrackingArray($carrier, $trackingNumber)
    {
        switch ($carrier) {
            case 'FDX':
                $carrierCode = 'fedex';
                $title = 'Fedex';
                break;
            case 'UPS':
                $carrierCode = 'ups';
                $title = 'UPS';
                break;
            case 'USPS':
                $carrierCode = 'usps';
                $title = 'USPS';
                break;
            default:
                $carrierCode = 'unknown';
                $title = 'Unknown';
                break;
        }
        
        return array(
            'carrier_code' => $carrierCode , 
            'title' => $title , 
            'number' => $trackingNumber
        );
    }

    /**
     * Converts a date and a time to a Mage timestamp
     *
     * @param string $date
     * @param string $time
     * @return string (Y-m-d H:i:s)
     */
    protected function _createTimeStamp($date, $time)
    {
        $unixDateTime = strtotime($date . ' ' . $time);
        return date('Y-m-d H:i:s', $unixDateTime);
    }

    /**
     * Moves to edi/in/complete
     *
     * @param string $fileLocation
     * @return bool
     */
    public function moveCompletedDocument($fileLocation)
    {
        $this->_moveToDirectory = Mage::getBaseDir('base') . DS . Mage::getStoreConfig('shoemart_edi/sync_setting/base_dir') . DS . 'in' . DS . 'complete' . DS;
        $flocal = new Varien_Io_File();
        if ($flocal->checkAndCreateFolder($this->_moveToDirectory)) {
            return $flocal->mv($fileLocation, $this->_moveToDirectory . basename($fileLocation));
        }
        return false;
    }
}
