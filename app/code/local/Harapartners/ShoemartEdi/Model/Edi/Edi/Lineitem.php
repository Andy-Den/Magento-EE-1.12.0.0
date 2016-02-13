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
class Harapartners_ShoemartEdi_Model_Edi_Edi_Lineitem {
	const FIELD_NAME_RECORD_ID = 'LIN';
	
	protected $_totalItems = 0;
	
	/**
	 * Creates a Shoemart EDI for GSX
	 * From Mage_Sales_Model_Order_Item
	 *
	 * @todo FIX pricing in a AAAA.XX Format ??
	 * @param Mage_Sales_Model_Order_Item $orderItem
	 * @return array
	 */
	public function createLineItem(array $orderItemInfo) {
		// EDI is just Fields so item order is important.
		$lineArray [] = self::FIELD_NAME_RECORD_ID;
		$lineArray [] = $orderItemInfo['upc'];
		$lineArray [] = $orderItemInfo['qty'];
		$lineArray [] = $orderItemInfo['cost'];
		$lineArray [] = $orderItemInfo['price'];
		$lineArray [] = $orderItemInfo['stockNumber'];
		$lineArray [] = $orderItemInfo['itemNumber'];
		$lineArray [] = $orderItemInfo['stockName'];
		$lineArray [] = $orderItemInfo['color'];
		$lineArray [] = $orderItemInfo['size'];
		$lineArray [] = $orderItemInfo['width'];

		$this->_totalItems += $orderItemInfo['qty'];
		return $lineArray;
	}
	
	public function getTotalItemCount() {
		return $this->_totalItems;
	}
	
	public function resetTotalItemCount() {
		$this->_totalItems = 0;
		return $this;
	}
}
