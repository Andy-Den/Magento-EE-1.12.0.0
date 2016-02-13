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
class Harapartners_ShoemartEdi_Model_Edi_Edi_Header
{
    const FIELD_NAME_RECORD_ID = 'HDR';
    const FIELD_NAME_DOCUEMENT_ID = '850';
    const FIELD_NAME_SHOEMART_ID_REG = 'SHOEMARTPROD';
    const FIELD_NAME_SHOEMART_ID_SPEC = 'SHOEMARTSA';
    const FIELD_NAME_PO_TYPE_CODE_DROPSHIP = 'DS';
    const FIELD_NAME_PO_TYPE_CODE_SPECIAL = 'SA';
    const SUFFIX_PONUM_SPECIAL = 'SP';
    const FIELDS_NAME_TERMS = '30';
    
    protected $_isSpecialOrder = false;

    /**
     * Creates a EDI header Row
     * @todo TP_ID && shipping
     *
     * @param array $orderInfo
     * @param string $vendorCode
     * @return array Header line in Array Format
     */
    public function createHeader($orderInfo, $vendorCode, $isSpecialOrder = false)
    {
        $this->_isSpecialOrder = $isSpecialOrder;
        
        // EDI is just Fields so item order is important.
        $infoFields = $this->_getInfoFields($orderInfo, $vendorCode);
        
        // === SOLD-TO => this should be billing Address === //
        $soldToFields = $this->_getSoldToFields($orderInfo['billingAddress']);
        
        // === BILL => This is Always Shoemart Info === //
        $billToFields = $this->_getBillToFields();
        
        // === SHIP-TO => this should be shipping Address === //
        $shipToFields = $this->_getShipToFields($orderInfo['shippingAddress']);
        
        // === MERGE First 4 === //
        $lineArray = array_merge($infoFields, $soldToFields, $billToFields, $shipToFields);
        
        // === Extra info === //
        $lineArray[] = $isSpecialOrder ? self::FIELDS_NAME_TERMS : null;
        $lineArray[] = $isSpecialOrder ? self::FIELD_NAME_PO_TYPE_CODE_SPECIAL : self::FIELD_NAME_PO_TYPE_CODE_DROPSHIP;
        return $lineArray;
    }

    protected function _getInfoFields($orderInfo, $vendorCode)
    {
        $orderId = $orderInfo['orderId'];
        $orderDateFormat = date('Ymd', strtotime($orderInfo['orderDate'])); // yyyymmdd
        $cancelDate = date('Ymd', strtotime('+5 day', strtotime($orderInfo['orderDate']))); // TODO verify this - weekends???
        

        if ($this->_isSpecialOrder) {
            $dropShipType = self::FIELD_NAME_PO_TYPE_CODE_SPECIAL;
            $dropShipTypeId = self::FIELD_NAME_SHOEMART_ID_SPEC;
            $poNumber = $vendorCode . $orderId . self::SUFFIX_PONUM_SPECIAL;
        } else {
            $dropShipType = self::FIELD_NAME_PO_TYPE_CODE_DROPSHIP;
            $dropShipTypeId = self::FIELD_NAME_SHOEMART_ID_REG;
            $poNumber = $vendorCode . $orderId;
        }
        
        $lineArray = array();
        $lineArray[] = self::FIELD_NAME_RECORD_ID;
        $lineArray[] = self::FIELD_NAME_DOCUEMENT_ID . $dropShipType;
        $lineArray[] = $vendorCode;
        $lineArray[] = $dropShipTypeId;
        $lineArray[] = $orderDateFormat;
        $lineArray[] = $poNumber;
        $lineArray[] = $orderDateFormat; // RequestDate same as OrderDate
        $lineArray[] = $cancelDate; // Cancel Date
        $lineArray[] = $orderInfo['shipCarrier'];
        $lineArray[] = $orderInfo['shipMethod'];
        return $lineArray;
    }

    protected function _getSoldToFields($billingAddress)
    {
        if ($this->_isSpecialOrder) {
            return $this->_getSpecialOrderSoldToFields();
        } else {
            return $this->_getAddressFields($billingAddress);
        }
    }

    protected function _getShipToFields($shippingAddress)
    {
        if ($this->_isSpecialOrder) {
            $shippingFields = $this->_getSpecialOrderShippingFields();
        } else {
            $shippingFields = $this->_getAddressFields($shippingAddress);
        }
        
        // Unset email
        unset($shippingFields[8]);
        return $shippingFields;
    }

    protected function _getAddressFields($addressInfo)
    {
        $splitStreet = explode("\n", $addressInfo['street']);
        
        $lineArray = array();
        $lineArray[] = $addressInfo['firstname'] . ' ' . $addressInfo['lastname'];
        $lineArray[] = $addressInfo['company'];
        $lineArray[] = $splitStreet[0]; // sold-to-addr1
        $lineArray[] = isset($splitStreet[1]) ? $splitStreet[1] : null; // sold-to-addr2
        $lineArray[] = $addressInfo['city'];
        $lineArray[] = $addressInfo['region'];
        $lineArray[] = $addressInfo['postcode'];
        $lineArray[] = $addressInfo['telephone'];
        $lineArray[] = $addressInfo['email'];
        return $lineArray;
    }

    protected function _getBillToFields()
    {
        $lineArray = array();
        if ($this->_isSpecialOrder) {
            $lineArray[] = 'DAN ZAPATKA';
            $lineArray[] = 'SHOEMART';
            $lineArray[] = '3 BERKELEY STREET';
            $lineArray[] = '';
            $lineArray[] = 'NORWALK';
            $lineArray[] = 'CT';
            $lineArray[] = '06850';
            $lineArray[] = '2038536543 x804';
            $lineArray[] = '2038521760'; // This is extra vs shipping FAX
            $lineArray[] = 'DANZ@THESHOEMART.COM';
        } else {
            $lineArray[] = 'MARK WATERS';
            $lineArray[] = 'SHOEMART';
            $lineArray[] = '3 BERKELEY STREET';
            $lineArray[] = '';
            $lineArray[] = 'NORWALK';
            $lineArray[] = 'CT';
            $lineArray[] = '06850';
            $lineArray[] = '2038536543 x804';
            $lineArray[] = '2038521760'; // This is extra vs shipping FAX
            $lineArray[] = 'EDI@THESHOEMART.COM';
        }
        return $lineArray;
    }

    protected function _getSpecialOrderShippingFields()
    {
        $lineArray = array();
        $lineArray[] = 'DAN ZAPATKA';
        $lineArray[] = 'SHOEMART';
        $lineArray[] = '950 BRIDGEPORT AVE';
        $lineArray[] = '';
        $lineArray[] = 'MILFORD';
        $lineArray[] = 'CT';
        $lineArray[] = '06460';
        $lineArray[] = '2038536543 x804';
        $lineArray[] = 'DANZ@THESHOEMART.COM';
        return $lineArray;
    }

    protected function _getSpecialOrderSoldToFields()
    {
        $lineArray = array();
        $lineArray[] = 'DAN ZAPATKA';
        $lineArray[] = 'SHOEMART';
        $lineArray[] = '3 BERKELEY STREET';
        $lineArray[] = '';
        $lineArray[] = 'MILFORD';
        $lineArray[] = 'CT';
        $lineArray[] = '06850';
        $lineArray[] = '2038536543 x804';
        $lineArray[] = 'DANZ@THESHOEMART.COM';
        return $lineArray;
    }

}
