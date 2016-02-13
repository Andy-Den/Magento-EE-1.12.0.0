<?php
/**
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 * @package     Harapartners_Webservice_Model_Import
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

class Harapartners_Shoemart_Model_Import_Orders extends Mage_Core_Model_Abstract {
	
	// File Handle one is the one that can contain multiple lines per one in File handle 2
	protected $_fileName = null;
	protected $_fileName2 = null;
	protected $_fileHandle = null;
	protected $_fileHandle2 = null;
	
	protected $_rows = null;
	protected $_headers = null;
	protected $_currentRow = null;
	protected $_nextRow = null;
	protected $_currentRow2 = null;
	protected $_oldLine = null;
	
	protected $_headers2 = null;
	
	protected $_itemBuffer = null;
	protected $_subTotal = 0;
	protected $_subTotalTax = 0;
	protected $_saleIDBuffer = null;
	
	const LINE_ITEM_ID_ROW = 0;
	
	const PROCESSING_FLAG = 'Processing';
	const SHIPPED_FLAG = 'Shipped';
	const CANCELED_FLAG = 'Canceled';
	const RETRUNED_FLAG = 'Returned';
	
	public function importProcess($action, $fileName, $fileName2 = null, $data = null) {
		$action = '_' . $action;
		$this->_fileName = $fileName;
		$this->_fileName2 = $fileName2;
		$this->_fileHandle = fopen ( $fileName, 'r' );
		$this->_fileHandle2 = $fileName2 ? fopen ( $fileName2, 'r' ) : null;
		//$this->_importCVS ( $fileName );
		

		$this->_headers = $this->_getRow ( $this->_fileHandle );
		$this->_headers2 = $this->_getRow ( $this->_fileHandle2 );
		
		$count = 0;
		while ( ($firstDataRow = $this->_getRow ( $this->_fileHandle )) != null && ! empty ( $firstDataRow [0] ) ) {
			$this->_setCurrentLine ( $firstDataRow );
			echo '-- ' . $firstDataRow [0] . PHP_EOL;
			if ($firstDataRow [0] == 100332) {
				echo print_r ( $firstDataRow );
			}
			$this->_ImportOrderComplete ( $this->_fileHandle2, $data );
			$count ++;
			if ($count % 50 == 0) {
				echo 'Imported Count: ' . $count . PHP_EOL;
			}
		}
		
	//		// EXTRA?
	//		if ($this->_fileHandle2) {
	//			$this->_setCurrentLine2 ( $this->_getRow ( $this->_fileHandle2 ) );
	//		}
	//		
	//		// Rows start from 1
	//		$rowCount = count ( $this->_rows );
	//		if ($rowCount == 1) {
	//			$this->_setCurrentLine ( $this->_rows [1] );
	//			$this->_nextRow = $this->_currentRow;
	//			$this->_nextRow ['Order ID'] = null;
	//			call_user_func ( array ($this, $action ) );
	//		} else {
	//			$this->_setNextLine ( $this->_rows [1] );
	//			for($i = 2; $i < $rowCount + 1; $i ++) {
	//				$this->_currentRow = $this->_nextRow;
	//				$this->_setNextLine ( $this->_rows [$i] );
	//				call_user_func ( array ($this, $action ) );
	//			}
	//			
	//			// Process Last Row
	//			$this->_currentRow = $this->_nextRow;
	//			$this->_nextRow ['Order ID'] = null;
	//			call_user_func ( array ($this, $action ) );
	//		}
	}
	
	//===== Handle the actual logic === //
	private function _ImportOrderComplete($fileHandle2, $data = null) {
		if (is_array ( $data )) {
			//check it
			if (isset ( $data ['store_id'] )) {
				$storeId = $data ['store_id'];
			} else {
				$storeId = 1;
			}
			
			if (isset ( $data ['website_id'] )) {
				$websiteId = $data ['website_id'];
			} else {
				$websiteId = 1;
			}
			
			if (isset ( $data ['customer_group'] )) {
				$customerGroup = $data ['customer_group'];
			} else {
				$customerGroup = 1;
			}
		} else {
			$storeId = 1;
			$websiteId = 1;
			$customerGroup = 1;
		}
		
		$orderRow = $this->_currentRow;
		$isCreateShipments = false;
		$isCreateInvoice = false;
		$isCreateCreditMemo = false;
		$isCancel = false;
		$orderConverter = Mage::getModel ( 'sales/convert_order' );
		
		// GET all order lines
		// Get old line or new one
		$currenLineItem = ($this->_oldLine) ? ($this->_oldLine) : $this->_getRow ( $fileHandle2 );
		if ($currenLineItem [self::LINE_ITEM_ID_ROW] > $orderRow ['order_id']) {
			echo 'ERROR: NO Line ITEMS!!' . PHP_EOL;
			echo (print_r ( $currenLineItem ) . PHP_EOL . print_r ( $orderRow ));
			return;
		} elseif ($currenLineItem [self::LINE_ITEM_ID_ROW] < $orderRow ['order_id']) {
			echo 'ERROR: EXTRA (Orphan) Line!!' . PHP_EOL;
			echo (print_r ( $currenLineItem ) . PHP_EOL . print_r ( $orderRow ));
			echo 'READING FWRD -> ->' . PHP_EOL;
			while ( $currenLineItem && $currenLineItem [self::LINE_ITEM_ID_ROW] < $orderRow ['order_id'] ) {
				$currenLineItem = $this->_getRow ( $fileHandle2 );
			}
			if ($currenLineItem [self::LINE_ITEM_ID_ROW] > $orderRow ['order_id']) {
				// PASSED So SAVE & Retrun
				if ($currenLineItem !== null) {
					// Need to save the last line for next item
					$this->_oldLine = $currenLineItem;
				} else {
					$this->_oldLine = null;
				}
				return;
			}
		} else {
			echo 'Line: ' . $currenLineItem [self::LINE_ITEM_ID_ROW] . '-' . $orderRow ['order_id'] . PHP_EOL;
		}
		
		// Find Ahead?? Unsure?? read ahead till you find the next orderID OR End
		while ( $currenLineItem && $currenLineItem [self::LINE_ITEM_ID_ROW] == $orderRow ['order_id'] ) {
			$currenLineItem = $this->_mapHeaders ( $currenLineItem, $this->_headers2 );
			$lineItems [] = $currenLineItem;
			$currenLineItem = $this->_getRow ( $fileHandle2 );
		}
		
		// Buffer Line for next run
		if ($currenLineItem !== null) {
			// Need to save the last line for next item
			$this->_oldLine = $currenLineItem;
		} else {
			$this->_oldLine = null;
		}
		
		// Check if exists already TODO Re-add
		//$order = Mage::getModel ( 'sales/order' )->getCollection ()->addFieldToFilter ( 'service_type', array ('like' => 'blackthorne%' ) )->addFilter ( 'service_transactionid', $orderRow['Sale ID'] )->setPageSize ( 1 )->getFirstItem ();
		

		// Code structure from HP:Owen
		// TODO Why use !! ?
		if (isset ( $order ) && $order->getId ()) {
			throw new Exception ( 'Sale ID already exists! ' . $orderRow ['order_id'] );
		}
		
		// Get Customer info // TODO unhardcode cust group & store_id
		/** @var Mage_Sales_Model_Order $order  */
		$order = Mage::getModel ( 'sales/order' )->setStoreId ( $storeId )->setCustomerEmail ( $orderRow ['email'] )->setData ( 'customer_group_id', $customerGroup )->setData ( 'customer_lastname', $orderRow ['bill_last_name'] )->setData ( 'customer_firstname', $orderRow ['bill_first_name'] );
		$customer = Mage::getModel ( 'customer/customer' )->setWebsiteId ( $websiteId )->loadByEmail ( trim ( $orderRow ['user_id'] ) );
		if (! $customer || ! $customer->getId ()) {
			//        throw new Exception('Order ' . $orderId . ': Invalid customer: "' . " Custoomer doesn't exist" );
		//$order = Mage::getModel ( 'sales/order' )->setStoreId ( 3 )->setData ( 'customer_group_id', 0 );
		//$order = Mage::getModel ( 'sales/order' )->setStoreId ( 3 )->setCustomerEmail ( $orderRow ['email'] )->setData ( 'customer_group_id', 5 )->setData ( 'customer_lastname', $orderRow ['bill_last_name'] )->setData ( 'customer_firstname', $orderRow ['bill_first_name'] );
		} else {
			// TODO Use the customer or the cust id?
			//$order = Mage::getModel ( 'sales/order' )->setStoreId ( 3 )->setCustomerId ( $customer->getId () )->setCustomerEmail ( $customer->getEmail () )->setData ( 'customer_group_id', 5 )->setData ( 'customer_lastname', $customer->getLastname () )->setData ( 'customer_middlename', $customer->getMiddlename () )->setData ( 'customer_firstname', $customer->getFirstname () );
			$order->setCustomerId ( $customer->getId () );
		}
		
		// SET BASIC order Information
		// Do shipping Twice for B and S
		// Billing First
		$strpos = strpos ( $orderRow ['bill_zip'], '-' );
		if ($strpos !== FALSE) {
			$zip5 = substr ( $orderRow ['bill_zip'], 0, 5 );
		} else {
			$zip5 = $orderRow ['bill_zip'];
		}
		
		$street = trim ( $orderRow ['bill_street1'] . " " . $orderRow ['bill_street2'] );
		$address = Mage::getModel ( 'sales/order_address' );
		$address->setData ( 'firstname', $orderRow ['bill_first_name'] );
		$address->setData ( 'lastname', $orderRow ['bill_last_name'] );
		$address->setData ( 'street', $street );
		$address->setData ( 'city', $orderRow ['bill_city'] );
		$address->setData ( 'postcode', $zip5 );
		$address->setData ( 'region', $orderRow ['bill_state'] );
		$address->setData ( 'country_id', $orderRow ['bill_country'] );
		$address->setData ( 'region_id', Mage::getModel ( 'directory/region' )->loadByCode ( $orderRow ['bill_state'], $orderRow ['bill_country'] )->getRegionId () ); // TODO Verify this?
		$address->setData ( 'telephone', $orderRow ['phone'] );
		$address->setData ( 'address_type', 'billing' );
		$order->addAddress ( $address );
		
		// Shipping Second
		$strpos = strpos ( $orderRow ['ship_zip'], '-' );
		if ($strpos !== FALSE) {
			$zip5 = substr ( $orderRow ['ship_zip'], 0, 5 );
		} else {
			$zip5 = $orderRow ['ship_zip'];
		}
		
		// Street
		$street = trim ( $orderRow ['ship_street1'] . " " . $orderRow ['ship_street2'] );
		$address = Mage::getModel ( 'sales/order_address' );
		$address->setData ( 'firstname', $orderRow ['ship_first_name'] );
		$address->setData ( 'lastname', $orderRow ['ship_last_name'] );
		$address->setData ( 'street', $street );
		$address->setData ( 'city', $orderRow ['ship_city'] );
		$address->setData ( 'postcode', $zip5 );
		$address->setData ( 'region', $orderRow ['ship_state'] );
		$address->setData ( 'country_id', $orderRow ['bill_country'] ); // TODO No ship country???? only US?
		$address->setData ( 'region_id', Mage::getModel ( 'directory/region' )->loadByCode ( $orderRow ['ship_state'], $orderRow ['bill_country'] )->getRegionId () ); // TODO Verify this
		$address->setData ( 'telephone', $orderRow ['phone'] ); // TODO No ship country???? only billing?
		$address->setData ( 'address_type', 'shipping' );
		$order->addAddress ( $address );
		
		// UnPack Item LVL data
		$total_tax = $orderRow ['tax'];
		$count = - 1;
		foreach ( $lineItems as $lineItem ) {
			$lineStatus = $lineItem ['STATUS'];
			if ($lineStatus != self::PROCESSING_FLAG) {
				$qty = $lineItem ['quantity'];
				if ($qty > 0) {
					$sku = $lineItem ['sku'];
					$product = Mage::getModel ( 'catalog/product' )->loadByAttribute ( 'sku', $sku );
					if (! $product || ! $product->getId ()) {
						// There is no Matching SKU -> This should not happen in full import
						// Todo make this a comment??
						//echo PHP_EOL . '<br> ShoeMart ' . $orderRow ['order_id'] . ' imported with warning: Invalid product SKU "' . $sku . '"' . ' may/does not exist anymore';
						$lineItemName = $sku;
					} else {
						$lineItemName = $product->getData ( 'name' );
					}
					
					$base_cost = $lineItem ['cost'];
					$salePrice = $lineItem ['price'];
					$lineDiscount = $lineItem ['discount'];
					//$lineTax =
					$lineTax = 0; // TODO figure out how the tax works AND fix. 
					////$taxForLine = $total_tax / $rowSubTotal;
					$discountPrecent = ($lineDiscount != 0) ? $salePrice / $lineDiscount : 0.0;
					$rowSubTotal = ($salePrice * $qty);
					$rowSubTotalAfterTax = $rowSubTotal + $lineTax - $lineDiscount;
					
					$Item = Mage::getModel ( 'sales/order_item' );
					$Item->setData ( 'base_cost', $base_cost );
					$Item->setData ( 'price', $salePrice );
					$Item->setData ( 'base_price', $salePrice ); // TODO this may change???
					$Item->setData ( 'original_price', $salePrice );
					$Item->setData ( 'original_base_price', $salePrice );
					$Item->setData ( 'tax_amount', $lineTax );
					$Item->setData ( 'base_tax_amount', $lineTax );
					$Item->setData ( 'price_incl_tax', $lineTax );
					$Item->setData ( 'base_price_incl_tax', $lineTax );
					$Item->setData ( 'row_total', $rowSubTotal );
					$Item->setData ( 'base_row_total', $rowSubTotal );
					$Item->setData ( 'discount_amount', $lineDiscount );
					$Item->setData ( 'base_discount_amount', $lineDiscount );
					$Item->setData ( 'discount_percent', $discountPrecent );
					$Item->setData ( 'row_total_incl_tax', $rowSubTotalAfterTax );
					$Item->setData ( 'base_row_total_incl_tax', $rowSubTotalAfterTax );
					
					$Item->setData ( 'qty_ordered', $qty );
					$Item->setData ( 'sku', $sku );
					$Item->setData ( 'name', $lineItemName );
					$Item->setData ( 'weight', 0 ); // TODO do THey need weight??
					

					// TODO remove this -> It is Not needed?
					$count ++;
					switch ($lineStatus) {
						case self::SHIPPED_FLAG :
							// Cache the Ship Info
							// Get the carrier code (fedex,ups ...)
							if (strlen ( $lineItem ['tracking'] ) != 0) {
								// Skip line Items with no Tracking Number Listed
								$carrier_code = strtolower ( strtok ( trim ( $lineItem ['ship_method'] ), " " ) );
								$lineStatuses [$count] = array (self::SHIPPED_FLAG => $sku, 'qty' => $qty, 'info' => array ('tracking' => array ('carrier_code' => $carrier_code, 'title' => $lineItem ['ship_method'], 'number' => strtolower ( $lineItem ['tracking'] ) ) ) );
								$trackings [$carrier_code . '_' . $lineItem ['tracking']] [$count] = true;
								$isCreateShipments = true;
								break;
							}
						// Dont break use Canceled logic for missing tracking
						case self::CANCELED_FLAG :
							// Cache the Cancel the Line Item
							$lineStatuses [$count] = array (self::CANCELED_FLAG => $Item );
							$isCancel = true;
							break;
						case self::RETRUNED_FLAG :
							// Cache the returned
							$lineStatuses [$count] = array (self::RETRUNED_FLAG => $sku, 'qty' => $qty );
							$isCancel = true;
							break;
					}
					
					$order->addItem ( $Item );
					
					// Buffer some things for Sanity Checks
					// TODO add Sanity Checks
					$this->_subTotal += $rowSubTotal;
					//$this->_subTotalTax += $taxAmount;
				}
			} else {
				// This is a new order-> Skip it
				return;
			}
		}
		
		//set other order info
		// TODO Sanity Checks -> compare the toatals witht eh data in the Order row
		$orderSubTotal = $orderRow ['subtotal'];
		$orderSubTotalTax = $orderRow ['tax'];
		$shippingAmount = $orderRow ['shipping_cost'];
		$orderTotalDiscount = $orderRow ['coupon_discount'];
		$orderTotalCost = $orderSubTotal + $orderSubTotalTax + $shippingAmount - $orderTotalDiscount;
		$order->setData ( 'subtotal', $orderSubTotal );
		$order->setData ( 'base_subtotal', $orderSubTotal );
		$order->setData ( 'discount_amount', $orderTotalDiscount );
		$order->setData ( 'base_discount_amount', $orderTotalDiscount );
		$order->setData ( 'tax_amount', $orderSubTotalTax );
		$order->setData ( 'base_tax_amount', $orderSubTotalTax );
		$order->setData ( 'shipping_amount', $shippingAmount );
		$order->setData ( 'base_shipping_amount', $shippingAmount );
		$order->setData ( 'grand_total', $orderTotalCost );
		$order->setData ( 'base_grand_total', $orderTotalCost );
		
		// SET Created Date & sipping method
		$createTime = date ( 'Y-m-d H:i:s', strtotime ( $orderRow ['date_placed'] ) );
		$order->setData ( 'created_at', $createTime );
		$order->setData ( 'store_name', 'SAVE AUTH TOKEN' ); // TODO unhardcode
		$order->setData ( 'currency_id', 'USD' ); // TODO unhardcode Maybe?
		$order->setData ( 'shipping_method', 'ups_gnd' ); //$orderRow ['shipping_method'] ); //usps_Priority Mail International
		$order->setData ( 'shipping_description', 'Description: ' . $orderRow ['shipping_method'] );
		
		// Add payment INFO // TODO it needs to handle moreish
		$payment = Mage::getModel ( 'sales/order_payment' )->setMethod ( 'checkmo' );
		$payment->setOrder ( $order );
		////$payment->setTransactionId ( $paypalTransId );
		////$payment->setLastTransId ( $paypalTransId );
		$payment->setPreparedMessage ( $orderRow ['on_hold_notes'] . PHP_EOL . $orderRow ['taken_by'] );
		$order->addPayment ( $payment );
		$order->save ();
		$allOrderItems = $order->getAllItems ();
		
		// Shipping
		if ($isCreateShipments) {
			$shipments = array ();
			$shipmentItem = null;
			$count = 0;
			$totalQty = 0;
			$trackingNum = '';
			$carrier_code = '';
			foreach ( $trackings as $tracking ) {
				$totalQty = 0;
				$shipment = $orderConverter->toShipment ( $order );
				for($i = 0; $i < count ( $lineStatuses ); $i ++) {
					if (isset ( $tracking [$i] )) {
						$shipmentItem = $orderConverter->itemToShipmentItem ( $allOrderItems [$i] );
						$shipmentItem->setQty ( $lineStatuses [$i] ['qty'] );
						$shipment->addItem ( $shipmentItem );
						$trackingInfoArray = $lineStatuses [$i] ['info'] ['tracking'];
						$totalQty += $lineStatuses [$i] ['qty'];
					}
				}
				//				/// OLD ////
				//				for($i = 0; $i < count ( $lineStatuses ); $i ++) {
				//					$itemId = $Item->load ( $lineStatuses [$i] [self::SHIPPED_FLAG], 'sku' )->getId ();
				//					isset ( $tracking [$i] ) ? $shipQtys [$itemId] = $lineStatuses [$i] ['qty'] : $shipQtys [$i] = 1; // Need 1 to be able to add the item if no match
				//					$trackingInfoArray = $lineStatuses [$i] ['info'] ['tracking'];
				//				}
				//				$shipment = $order->prepareShipment ( $shipQtys );
				$track = Mage::getModel ( 'sales/order_shipment_track' )->addData ( $trackingInfoArray );
				$shipment->setTotalQty ( $totalQty );
				$shipment->addTrack ( $track );
				$shipment->register ();
				$shipments [] = $shipment; // TODO need to save multiple? DOING??
			}
		}
		
		// Invoice (If shipped it is invoiced)
		if ($isCreateShipments) {
			$isCreateInvoice = true;
			$invoice = $orderConverter->toInvoice ( $order );
			$invoiceItem = null;
			$totalQty = 0;
			for($i = 0; $i < count ( $lineStatuses ); $i ++) {
				if (isset ( $lineStatuses [$i] [self::SHIPPED_FLAG] )) {
					$invoiceItem = $orderConverter->itemToInvoiceItem ( $allOrderItems [$i] );
					$invoiceItem->setQty ( $lineStatuses [$i] ['qty'] );
					$invoice->addItem ( $invoiceItem );
					$totalQty += $lineStatuses [$i] ['qty'];
				}
			}
			$invoice->collectTotals ();
			$order->getInvoiceCollection ()->addItem ( $invoice );
			$invoice->setTotalQty ( $totalQty );
			$invoice->setRequestedCaptureCase ( Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE );
			$invoice->register ();
			$payment->pay ( $invoice );
			
		//			foreach ( $lineStatuses as $lineStatus ) {
		//				isset ( $lineStatus [self::SHIPPED_FLAG] ) ? $invoiceQtys [$Item->load ( $lineStatus [self::SHIPPED_FLAG], 'sku' )->getId ()] = $lineStatus ['qty'] : $invoiceQtys [] = 0;
		//			}
		//			$invoice = $order->prepareInvoice ( $invoiceQtys );
		//			$invoice->setRequestedCaptureCase ( Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE );
		//			$invoice->register ();
		//			$payment->pay ( $invoice );
		}
		
		// Refunded // TODO this does not work... ALSO need to invoice the returned ones.... (ship)?
		if ($isCreateCreditMemo) {
			foreach ( $lineStatuses as $lineStatus ) {
				isset ( $lineStatus [self::RETRUNED_FLAG] ) ? $returnQtys [$Item->load ( $lineStatus [self::RETRUNED_FLAG], 'sku' )->getId ()] = $lineStatus ['qty'] : $returnQtys [] = 0;
			}
			$creditMemo = Mage::getModel ( 'sales/service_order', $order )->prepareCreditMemo ( array ('qtys' => $shipQtys ) );
			$creditMemo->register ();
		}
		
		// Cancel
		if ($isCancel) {
			foreach ( $lineStatuses as $key => $lineStatus ) {
				isset ( $lineStatus [self::CANCELED_FLAG] ) ? $lineStatus [self::CANCELED_FLAG]->setQtyCanceled ( $this->getQtyToCancel () ) : $null = 0;
			}
		}
		
		// SET Status/Hold
		//		if ($orderRow ['on_hold']) {
		//			// This is on hold
		//			$isOnHold = true;
		//			$order->setState ( 'holded', 'holded', $orderRow ['on_hold_notes'] . PHP_EOL . $orderRow ['taken_by'] );
		//			//// $order->setStatus ( 'holded' );
		//		} else {
		//$order->setState ( 'processing', 'processing', $orderRow ['on_hold_notes'] . PHP_EOL . $orderRow ['taken_by'] );
		$order->setState ( Mage_Sales_Model_Order::STATE_CANCELED, Mage_Sales_Model_Order::STATE_CANCELED, $orderRow ['on_hold_notes'] . PHP_EOL . $orderRow ['taken_by'] . PHP_EOL . 'Order Imported and CANCELED for compatibility' );
		////$order->setStatus ( Mage_Sales_Model_Order::STATE_CANCELED );
		////$order->addStatusToHistory ( $order->getStatus (), 'Order Imported and CANCELED for compatibility', false );
		//		}
		

		// ReSave the Order + (Invoice)
		$transactionSave = Mage::getModel ( 'core/resource_transaction' )->addObject ( $order );
		if ($isCreateInvoice && $invoice) {
			$transactionSave->addObject ( $invoice );
		}
		if ($isCreateShipments && count ( $shipments ) > 0) {
			foreach ( $shipments as $shipment ) {
				$transactionSave->addObject ( $shipment );
			}
		}
		if ($isCreateCreditMemo && $creditMemo) {
			$transactionSave->addObject ( $creditMemo );
		}
		$transactionSave->save ();
		
	//		if ($orderStatus === 'Shipped') {
	//			// This is a shipped order
	//			// Shipped orders have a capture(payment/invoice + a shipppment)
	//			$payment = Mage::getModel ( 'sales/order_payment' )->setMethod ( 'credit_card' ); // TODO what is valid here??
	//			$payment->setOrder ( $order );
	//			//$payment->setTransactionId ( $paypalTransId );
	//			//$payment->setLastTransId ( $paypalTransId );
	//			
	//
	//			$payment->setPreparedMessage ( $orderRow ['on_hold_notes'] . PHP_EOL . $orderRow ['taken_by'] );
	//			$order->addPayment ( $payment );
	//			$invoice = $order->prepareInvoice ();
	//			$invoice->setRequestedCaptureCase ( Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE );
	//			$invoice->register ();
	//			$payment->pay ( $invoice );
	//			
	//			// Shipment
	//			$shipQtys = array ();
	//			foreach ( $lineItems as $lineItem ) { // TODO hmm this can be tricky....
	//				$lineStatus = $lineItem ['STATUS'];
	//				if ($lineStatus == 'Shipped') {
	//					$shipQtys[];
	//				}
	//			
	//			}
	//			$shipment = Mage::getModel ( 'sales/service_order', $order )->prepareShipment ( $shipQtys ); // TODO Fix this...
	//			$trackingInfoArray = array (/*'carrier_code' => $CarrierInfo->getCarrierId (), 'title' => $CarrierInfo->getServiceLevel (),*/ 'number' => ( string ) $cartonAttrib->TrackingId );
	//			
	//			if (empty ( $trackingInfoArray ['number'] )) {
	//				Mage::throwException ( 'Tracking number cannot be empty.' );
	//			}
	//			$track = Mage::getModel ( 'sales/order_shipment_track' )->addData ( $trackingInfoArray );
	//			$shipment->addTrack ( $track );
	//			//Harapartners, Jun: shipment preparation, END
	//			
	//
	//			$shipment->register (); //Harapartners, Jun: shipment registration is important
	//		}
	//		$transactionSave = Mage::getModel ( 'core/resource_transaction' )->addObject ( $invoice->getOrder () )->addObject ( $invoice );
	//		$transactionSave->save ();
	//		
	//		// Reset
	//		$this->_itemBuffer = null;
	//		$this->_saleIDBuffer = null;
	//		$this->_subTotal = 0;
	//		$this->_subTotalTax = 0;
	

	}
	
	//============= END Handle Logic ================== //
	

	//===== Handle the actual logic === //
	private function _ImportCompleteOrders() {
		$row = $this->_currentRow;
		// Check if exists already
		$order = Mage::getModel ( 'sales/order' )->getCollection ()->addFieldToFilter ( 'service_type', array ('like' => 'blackthorne%' ) )->addFilter ( 'service_transactionid', $row ['Sale ID'] )->setPageSize ( 1 )->getFirstItem ();
		
		// Code structure from HP:Owen
		// TODO Why use !! ?
		if (isset ( $order ) && $order->getId ()) {
			throw new Exception ( 'Sale ID already exists! ' . $row ['Sale ID'] );
		}
		
		// They Can send who knows what. So trust them...
		//		if ($row ['Sale Status'] != 14 && $row ['Sale Status'] != 16) {
		//			// If it is not Complete Skip it // THIS is a sanity check here.
		//			return;
		//		}
		

		// Add items
		// TODO logic to handle sku OR PART NUMBER AND the mapping -> Dont forget to replace all
		$qty = $row ['Quantity Sold'];
		if ($qty > 0) {
			$product = Mage::getModel ( 'catalog/product' )->loadByAttribute ( 'sku', $row ['Part Number'] );
			if (! $product || ! $product->getId ()) {
				// Todo make this a comment??
				echo '<br> Ebay ' . $row ['Sale ID'] . ' imported with warning: Invalid product SKU "' . $row ['Part Number'] . '"' . ' may does not exist anymore';
			}
			
			$salePrice = $row ['Sale Price'];
			$taxAmount = $row ['Sales Tax'];
			$rowSubTotal = $salePrice * $qty;
			$rowSubTotalAfterTax = $rowSubTotal + $taxAmount;
			
			$Item = Mage::getModel ( 'sales/order_item' );
			$Item->setData ( 'price', $salePrice );
			$Item->setData ( 'base_price', $salePrice );
			$Item->setData ( 'original_price', $salePrice );
			$Item->setData ( 'original_base_price', $salePrice );
			$Item->setData ( 'tax_amount', $taxAmount );
			$Item->setData ( 'base_tax_amount', $taxAmount );
			$Item->setData ( 'price_incl_tax', $taxAmount );
			$Item->setData ( 'base_price_incl_tax', $taxAmount );
			$Item->setData ( 'row_total', $rowSubTotal );
			$Item->setData ( 'base_row_total', $rowSubTotal );
			$Item->setData ( 'row_total_incl_tax', $rowSubTotalAfterTax );
			$Item->setData ( 'base_row_total_incl_tax', $rowSubTotalAfterTax );
			
			$Item->setData ( 'qty_ordered', $qty );
			$Item->setData ( 'sku', $row ['Part Number'] );
			$Item->setData ( 'name', $row ['Title'] );
			
			// Buffer the Item Info
			$this->_subTotal += $rowSubTotal;
			$this->_subTotalTax += $taxAmount;
			$this->_itemBuffer [] = $Item;
			
			$this->_saleIDBuffer [] = $row ['Sale ID'];
		}
		
		if ($this->_nextRow ['Order ID'] !== $this->_currentRow ['Order ID']) {
			// Un buffer item Info
			$orderSubTotal = $this->_subTotal;
			$orderSubTotalTax = $this->_subTotalTax;
			$orderLineItems = $this->_itemBuffer;
			
			// Create and save the Order
			// These are Out of scope orders -> no cust id likely
			/*$customer = Mage::getModel ( 'customer/customer' )->setWebsiteId ( 4 )->load ( trim ( $orderData ['order_info'] ['magento_customer_id'] ) );
			if (! $customer || ! $customer->getId ()) {
				//        throw new Exception('Order ' . $orderId . ': Invalid customer: "' . " Custoomer doesn't exist" );
				$order = Mage::getModel ( 'sales/order' )->setStoreId ( 5 )->setData ( 'customer_group_id', 0 );
			} else {
				$order = Mage::getModel ( 'sales/order' )->setStoreId ( 5 )->setCustomerId ( $customer->getId () )->setCustomerEmail ( $customer->getEmail () )->setData ( 'customer_group_id', 1 )->setData ( 'customer_lastname', $customer->getLastname () )->setData ( 'customer_middlename', $customer->getMiddlename () )->setData ( 'customer_firstname', $customer->getFirstname () );
			}*/
			// TODO ->setCustomerId ( $customer->getId () ) ->setData ( 'customer_middlename', $customer->getMiddlename () ) TODO unhardcode cust group & store_id
			$order = Mage::getModel ( 'sales/order' )->setStoreId ( 2 )->setCustomerEmail ( $row ['Buyers.Email Address'] )->setData ( 'customer_group_id', 4 )->setData ( 'customer_lastname', $row ['Buyers.Last Name'] )->setData ( 'customer_firstname', $row ['Buyers.First Name'] );
			$order->setData ( 'service_type', Harapartners_Blackmagento_Helper_Data::BLACKTHORNE_IMPORTED )->setData ( 'service_transactionid', $row ['Sale ID'] );
			foreach ( $orderLineItems as $lineItem ) {
				$order->addItem ( $lineItem );
			}
			
			if (count ( $this->_saleIDBuffer ) > 1) {
				// If >1; then it is combined Order
				// Note: new order so no need to worry about old add info
				$order ['service_data'] = json_encode ( array ('additionalInfo' => array ('saleIds' => $this->_saleIDBuffer ) ) );
			}
			
			// TODO Handle BuyerStaticAlias -> Wasnt retruned bec sandbox???
			// Do shipping Twice for B and S
			// Billing First
			$strpos = strpos ( $row ['BillingAddress.Zip'], '-' );
			if ($strpos !== FALSE) {
				$zip5 = substr ( $row ['BillingAddress.Zip'], 0, 5 );
			} else {
				$zip5 = $row ['BillingAddress.Zip'];
			}
			
			$street = $row ['BillingAddress.Address Line 1'] . " " . $row ['BillingAddress.Address Line 2'];
			$address = Mage::getModel ( 'sales/order_address' );
			$address->setData ( 'firstname', $row ['BillingAddress.First Name'] );
			$address->setData ( 'lastname', $row ['BillingAddress.Last Name'] );
			$address->setData ( 'street', $street );
			$address->setData ( 'city', $row ['BillingAddress.City'] );
			$address->setData ( 'postcode', $zip5 );
			$address->setData ( 'region', $row ['BillingAddress.State'] );
			$address->setData ( 'country_id', $row ['BillingAddress.Country'] );
			$address->setData ( 'region_id', Mage::getModel ( 'directory/region' )->loadByCode ( $row ['BillingAddress.State'], $row ['BillingAddress.Country'] )->getRegionId () ); // TODO Verify this
			$address->setData ( 'telephone', $row ['BillingAddress.Phone'] );
			$address->setData ( 'address_type', 'billing' );
			$order->addAddress ( $address );
			
			// Shipping Second
			$strpos = strpos ( $row ['ShippingAddress.Zip'], '-' );
			if ($strpos !== FALSE) {
				$zip5 = substr ( $row ['ShippingAddress.Zip'], 0, 5 );
			} else {
				$zip5 = $row ['ShippingAddress.Zip'];
			}
			
			// Street
			$street = $row ['ShippingAddress.Address Line 1'] . " " . $row ['ShippingAddress.Address Line 2'];
			if (isset ( $row ['GSP Refrence Number'] )) {
				$street += PHP_EOL . $row ['GSP Refrence Number'];
			}
			
			$address = Mage::getModel ( 'sales/order_address' );
			$address->setData ( 'firstname', $row ['ShippingAddress.First Name'] );
			$address->setData ( 'lastname', $row ['ShippingAddress.Last Name'] );
			$address->setData ( 'street', $street );
			$address->setData ( 'city', $row ['ShippingAddress.City'] );
			$address->setData ( 'postcode', $zip5 );
			$address->setData ( 'region', $row ['ShippingAddress.State'] );
			$address->setData ( 'country_id', $row ['ShippingAddress.Country'] );
			$address->setData ( 'region_id', Mage::getModel ( 'directory/region' )->loadByCode ( $row ['ShippingAddress.State'], $row ['ShippingAddress.Country'] )->getRegionId () ); // TODO Verify this
			$address->setData ( 'telephone', $row ['ShippingAddress.Phone'] );
			$address->setData ( 'address_type', 'shipping' );
			$order->addAddress ( $address );
			
			//set other order info
			$shippingAmount = $row ['Shipping'];
			$orderTotalCost = $orderSubTotal + $orderSubTotalTax + $shippingAmount;
			$order->setData ( 'subtotal', $orderSubTotal );
			$order->setData ( 'base_subtotal', $orderSubTotal );
			//// $order->setData ( 'discount_amount', '0' ); // TODO dont think this applies -> Check this stuff
			//// $order->setData ( 'base_discount_amount', '0' );
			$order->setData ( 'tax_amount', $orderSubTotalTax );
			$order->setData ( 'base_tax_amount', $orderSubTotalTax );
			$order->setData ( 'shipping_amount', $shippingAmount );
			$order->setData ( 'base_shipping_amount', $shippingAmount );
			$order->setData ( 'grand_total', $orderTotalCost );
			$order->setData ( 'base_grand_total', $orderTotalCost );
			$createTime = date ( 'Y-m-d H:i:s', strtotime ( $row ['Date Sold'] ) );
			$order->setData ( 'created_at', $createTime );
			$order->setData ( 'store_name', 'SAVE AUTH TOKEN' ); // TODO unhardcode
			$order->setData ( 'currency_id', 'USD' );
			$order->setData ( 'shipping_method', 'usps_Priority Mail International' ); //$row ['Shipping Company'] );
			$order->setData ( 'shipping_description', 'Description: ' . $row ['Shipping Company'] );
			
			$order->setState ( 'processing' ); // Need to set the state also
			$order->setStatus ( 'ebay_complete' ); // TODO unhardcode
			

			$paypalTransId = substr ( $row ['Paypal Transaction Information'], 0, strpos ( $row ['Paypal Transaction Information'], ' ' ) );
			$payment = Mage::getModel ( 'sales/order_payment' )->setMethod ( 'paypal_standard' );
			$payment->setOrder ( $order );
			$payment->setTransactionId ( $paypalTransId );
			$payment->setLastTransId ( $paypalTransId );
			
			$payment->setPreparedMessage ( 'PayPal payment. Captured through Ebay' );
			$order->addPayment ( $payment );
			$invoice = $order->prepareInvoice ();
			$invoice->setRequestedCaptureCase ( Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE );
			$invoice->register ();
			$payment->pay ( $invoice );
			
			$transactionSave = Mage::getModel ( 'core/resource_transaction' )->addObject ( $invoice->getOrder () )->addObject ( $invoice );
			$transactionSave->save ();
			
			// Reset
			$this->_itemBuffer = null;
			$this->_saleIDBuffer = null;
			$this->_subTotal = 0;
			$this->_subTotalTax = 0;
		}
	}
	
	//============= END Handle Logic ================== //
	

	protected function _setNextLine($line) {
		$headers = $this->_headers;
		
		$count = count ( $headers );
		for($i = 0; $i < $count; $i ++) {
			$this->_nextRow [$headers [$i]] = $line [$i];
		}
	}
	
	protected function _setCurrentLine($line) {
		$headers = $this->_headers;
		
		$count = count ( $headers );
		for($i = 0; $i < $count; $i ++) {
			$this->_currentRow [$headers [$i]] = $line [$i];
		}
	}
	
	protected function _setCurrentLine2($line) {
		$headers = $this->_headers;
		
		$count = count ( $headers );
		for($i = 0; $i < $count; $i ++) {
			$this->_currentRow2 [$headers [$i]] = $line [$i];
		}
	}
	
	protected function _mapHeaders($line, $headers) {
		//$headers = $headers;
		

		$count = count ( $headers );
		for($i = 0; $i < $count; $i ++) {
			$temp [$headers [$i]] = $line [$i];
		}
		
		return $temp;
	}
	
	/**
	 * This gets the next row
	 *
	 * @param unknown_type $fileHandle
	 * @return unknown
	 */
	protected function _getRow($fileHandle) {
		if ($fileHandle == null) {
			return null;
		} else {
			if (($data = fgetcsv ( $fileHandle )) !== FALSE) {
				return $data;
			} else {
				return null;
			}
		}
	}
	
	protected function _importCVS() {
		$fileName = $this->_fileName;
		$dataStore = array ();
		$fieldNames = array ();
		
		$row = 1;
		if (($handle = fopen ( $fileName, "rb" )) !== FALSE) {
			if (($data = fgetcsv ( $handle )) !== FALSE) {
				$fieldNames = $data;
			}
			
			while ( ($data = fgetcsv ( $handle )) !== FALSE ) {
				$dataStore [$row] = $data;
				$row ++;
			}
			
			fclose ( $handle );
		}
		
		// TODO clean a bit -> count twice and so on. maybe move?
		if ($fieldNames [count ( $fieldNames ) - 1] == '') {
			unset ( $fieldNames [count ( $fieldNames ) - 1] );
		}
		
		$this->_headers = $fieldNames;
		$this->_rows = $dataStore;
		//return array ('Names' => $fieldNames, 'Rows' => $dataStore )
		;
	}
}
