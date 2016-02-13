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
class Harapartners_HpIntWms_Model_Import_Tracking
{
    protected $_numCreated;
    protected $_numOrderNotFound;
    protected $_numErrors;
    protected $_ordersCompleted = array();
    
    /**
     * array (
     *     order_number_1 => array(
     *         tracking_number_1 => array(
     *             order_item_1 => qty_shipped_1,
     *             ...
     *             order_item_n => qty_shipped_n
     *         ),
     *         ...
     *     ),
     *   ...
     * )
     *
     * @var array
     */
    protected $_processedOrders = array();
    
    /**
     * Reference array of tracking_number => ship_date
     *
     * tracking_number_1 => ship_date_1,
     * tracking_number_2 => ship_date_2,
     * ...,
     * tracking_number_n => ship_date_n
     *
     * @var array
     */
    protected $_shipDates = array();
    
    /**
     * Reference array of tracking_number => carrier Array
     *
     * array (
     *     tracking_number_1 => array(
     *         carrier => carrier,
     *         method  => method
     *     ),
     *   ...
     * )
     *
     * @var array
     */
    protected $_carierInfo = array();

    /**
     * Creates Shipments and associated info
     * Sends Emails
     * 
     * @todo Create the shipment email at the order LVL
     *
     * @param unknown_type $trackings
     * @return int -1 if errors ( +numOrderNotFound) , numberCreated if no errors 
     */
    public function processTrackings($trackings)
    {
        try {
            // Render Stuff
            $this->_processTrackingInfoIntoArray($trackings);
            $shipments = $this->_createShipemtsByOrder();
            
            // Save the Shipments/Orders
            foreach ($shipments as $orderNumber => $shipmentArray) {
                $saveTransaction = Mage::getModel('core/resource_transaction');
                foreach ($shipmentArray as $shipment) {
                    $saveTransaction->addObject($shipment);
                }
                try {
                    $saveTransaction->addObject($shipment->getOrder())->save();
                    $this->_numCreated ++;
                    $this->_ordersCompleted[] = $orderNumber;
                    
                    // SEND EMAIL
                    $emailSentStatus = $shipment->getData('email_sent');
                    $customerEmail = $shipment->getOrder()->getData('customer_email');
                    $customerEmailComments = '';
                    
                    // TODO this needs to be sent on the order lvl
                    if (! is_null($customerEmail) && ! $emailSentStatus) {
                        $shipment->sendEmail($customerEmail, $customerEmailComments); //TODO do we want to impliment this?? F:Steven
                        $shipment->setEmailSent(true);
                        $shipment->save();
                    }
                } catch (Exception $e) {
                    Mage::logException($e);
                    $this->_numErrors ++;
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }
        return ($this->_numErrors + $this->_numOrderNotFound > 0) ? - 1 : $this->_numCreated;
    }

    protected function _processTrackingInfoIntoArray($trackings)
    {
        foreach ($trackings as $tracking) {
            $this->_processedOrders[$tracking['OrderNumber']][$tracking['TrackingNumber']][$tracking['LineNumber']] = $tracking['ActualQuantity'];
            $this->_shipDates[$tracking['TrackingNumber']] = $tracking['ActualDate'];
            $this->_carierInfo[$tracking['TrackingNumber']] = array( 'carrier' => $tracking['Carrier'], 'method' => $tracking['Method']);
        }
    }

    protected function _createShipemtsByOrder()
    {
        foreach ($this->_processedOrders as $orderNumber => $trackingNumbers) {
            $order = Mage::getModel('sales/order')->loadByIncrementId($orderNumber);
            if (! ! $order && ! ! $order->getId()) {
                if (! $order->canShip()) {
                    Mage::throwException('This order cannot be shipped.');
                    // TODO This may not work out well
                }
            } else {
                // Unknown Order ID.
                $this->_numOrderNotFound ++;
                continue;
            }
            $qtys = array();
            foreach ($trackingNumbers as $trackingNumber => $items) {
                // Create Shipments on that order
                foreach ($items as $itemNumber => $shippedQty) {
                    $qtys[$itemNumber] = $shippedQty;
                }
            }
            
            $shipments[$orderNumber][] = $this->_convertToShipmentTracking($order, $qtys, $trackingNumber); // TODO do we need to pass the order back?
        }
        return $shipments;
    }

    /**
     * Creates a shipment based on Order with QTYS and a tracking number
     *
     * @param Mage_Sales_Model_Order $order
     * @param array $qtys
     * @param string $tracking
     * @return Mage_Sales_Model_Order_Shipment
     */
    protected function _convertToShipmentTracking(Mage_Sales_Model_Order $order, array $qtys, $tracking)
    {
        if (empty($tracking)) {
            // SANITY CHECK FOR NOW -> Want to Validate before this function
            Mage::throwException('Tracking number cannot be empty.');
        }
        
        $shipment = $order->prepareShipment($qtys);
        $trackingInfoArray = $this->_getTrackingArray($tracking);
        $track = Mage::getModel('sales/order_shipment_track')->addData($trackingInfoArray);
        $shipment->addTrack($track);
        $shipment->setCreatedAtTime($this->_shipDates[$tracking]);
        $shipment->register();
        
        return $shipment;
    }

    protected function _getTrackingArray($tracking)
    {
    	$carrierInfo = $this->_carierInfo[$tracking];
        return array(
            'carrier_code' => $carrierInfo['carrier'] , 
            'title' => $carrierInfo['method'] , 
            'number' => $tracking
        );
        
		//        return array(
		//            'carrier_code' => 'fedex' , 
		//            'title' => 'Federal Express' , 
		//            'number' => $tracking
		//        );
    }

    /**
     * @return int
     */
    public function get_numberCreated()
    {
        return $this->_numCreated;
    }

    /**
     * @return int
     */
    public function get_numberError()
    {
        return $this->_numErrors;
    }

    /**
     * @return int
     */
    public function get_numberOrderNotFound()
    {
        return $this->_numOrderNotFound;
    }

    /**
     * @return unknown
     */
    public function get_ordersCompletedArray()
    {
        return $this->_ordersCompleted;
    }

    public function _isAvailible($item)
    {
        // Possible Check for later
        return true;
    }
}
