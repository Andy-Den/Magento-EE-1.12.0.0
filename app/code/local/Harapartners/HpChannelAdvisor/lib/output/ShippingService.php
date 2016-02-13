<?php

if (!class_exists("GetShippingRateList", false)) {
/**
 * GetShippingRateList
 */
class GetShippingRateList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sint
	 */
	public $cartID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $street;
	/**
	 * @access public
	 * @var sstring
	 */
	public $city;
	/**
	 * @access public
	 * @var sstring
	 */
	public $state;
	/**
	 * @access public
	 * @var sstring
	 */
	public $postalCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $country;
}}

if (!class_exists("GetShippingRateListResponse", false)) {
/**
 * GetShippingRateListResponse
 */
class GetShippingRateListResponse {
	/**
	 * @access public
	 * @var APIResultOfShippingRateResult
	 */
	public $GetShippingRateListResult;
}}

if (!class_exists("APIResultOfShippingRateResult", false)) {
/**
 * APIResultOfShippingRateResult
 */
class APIResultOfShippingRateResult {
	/**
	 * @access public
	 * @var tnsResultStatus
	 */
	public $Status;
	/**
	 * @access public
	 * @var sint
	 */
	public $MessageCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Message;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Data;
	/**
	 * @access public
	 * @var ShippingRateResult
	 */
	public $ResultData;
}}

if (!class_exists("ResultStatus", false)) {
/**
 * ResultStatus
 */
class ResultStatus {
}}

if (!class_exists("ShippingRateResult", false)) {
/**
 * ShippingRateResult
 */
class ShippingRateResult {
	/**
	 * @access public
	 * @var ArrayOfShippingItemBase
	 */
	public $ShippingDetailList;
}}

if (!class_exists("ShippingItemBase", false)) {
/**
 * ShippingItemBase
 */
class ShippingItemBase {
	/**
	 * @access public
	 * @var sstring
	 */
	public $CarrierName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ClassName;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ShippingCost;
}}

if (!class_exists("APICredentials", false)) {
/**
 * APICredentials
 */
class APICredentials {
	/**
	 * @access public
	 * @var sstring
	 */
	public $DeveloperKey;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Password;
}}

if (!class_exists("GetShippingCarrierList", false)) {
/**
 * GetShippingCarrierList
 */
class GetShippingCarrierList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
}}

if (!class_exists("GetShippingCarrierListResponse", false)) {
/**
 * GetShippingCarrierListResponse
 */
class GetShippingCarrierListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfShippingCarrier
	 */
	public $GetShippingCarrierListResult;
}}

if (!class_exists("APIResultOfArrayOfShippingCarrier", false)) {
/**
 * APIResultOfArrayOfShippingCarrier
 */
class APIResultOfArrayOfShippingCarrier {
	/**
	 * @access public
	 * @var tnsResultStatus
	 */
	public $Status;
	/**
	 * @access public
	 * @var sint
	 */
	public $MessageCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Message;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Data;
	/**
	 * @access public
	 * @var ArrayOfShippingCarrier
	 */
	public $ResultData;
}}

if (!class_exists("ShippingCarrier", false)) {
/**
 * ShippingCarrier
 */
class ShippingCarrier {
	/**
	 * @access public
	 * @var sint
	 */
	public $CarrierID;
	/**
	 * @access public
	 * @var sint
	 */
	public $ClassID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $CarrierName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $CarrierCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ClassCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ClassName;
}}

if (!class_exists("GetOrderShipmentHistoryList", false)) {
/**
 * GetOrderShipmentHistoryList
 */
class GetOrderShipmentHistoryList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var ArrayOfInt
	 */
	public $orderIDList;
	/**
	 * @access public
	 * @var ArrayOfString
	 */
	public $clientOrderIdentifierList;
}}

if (!class_exists("GetOrderShipmentHistoryListResponse", false)) {
/**
 * GetOrderShipmentHistoryListResponse
 */
class GetOrderShipmentHistoryListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfOrderShipmentHistoryResponse
	 */
	public $GetOrderShipmentHistoryListResult;
}}

if (!class_exists("APIResultOfArrayOfOrderShipmentHistoryResponse", false)) {
/**
 * APIResultOfArrayOfOrderShipmentHistoryResponse
 */
class APIResultOfArrayOfOrderShipmentHistoryResponse {
	/**
	 * @access public
	 * @var tnsResultStatus
	 */
	public $Status;
	/**
	 * @access public
	 * @var sint
	 */
	public $MessageCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Message;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Data;
	/**
	 * @access public
	 * @var ArrayOfOrderShipmentHistoryResponse
	 */
	public $ResultData;
}}

if (!class_exists("SubmitOrderShipmentList", false)) {
/**
 * SubmitOrderShipmentList
 */
class SubmitOrderShipmentList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var OrderShipment[]
	 */
	public $shipmentList;
}}

if (!class_exists("OrderShipment", false)) {
/**
 * OrderShipment
 */
class OrderShipment {
	/**
	 * @access public
	 * @var sint
	 */
	public $OrderID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ClientOrderIdentifier;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ShipmentType;
	/**
	 * @access public
	 * @var PartialShipmentContents
	 */
	public $PartialShipment;
	/**
	 * @access public
	 * @var FullShipmentContents
	 */
	public $FullShipment;
}}

if (!class_exists("PartialShipmentContents", false)) {
/**
 * PartialShipmentContents
 */
class PartialShipmentContents {
	/**
	 * @access public
	 * @var LineItem[]
	 */
	public $LineItemList;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $DateShippedGMT;
	/**
	 * @access public
	 * @var sstring
	 */
	public $CarrierCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ClassCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $TrackingNumber;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SellerFulfillmentID;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ShipmentCost;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ShipmentTaxCost;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $InsuranceCost;
}}

if (!class_exists("LineItem", false)) {
/**
 * LineItem
 */
class LineItem {
	/**
	 * @access public
	 * @var sstring
	 */
	public $SKU;
	/**
	 * @access public
	 * @var sint
	 */
	public $Quantity;
}}

if (!class_exists("FullShipmentContents", false)) {
/**
 * FullShipmentContents
 */
class FullShipmentContents {
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $DateShippedGMT;
	/**
	 * @access public
	 * @var sstring
	 */
	public $CarrierCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ClassCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $TrackingNumber;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SellerFulfillmentID;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ShipmentCost;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ShipmentTaxCost;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $InsuranceCost;
}}

if (!class_exists("SubmitOrderShipmentListResponse", false)) {
/**
 * SubmitOrderShipmentListResponse
 */
class SubmitOrderShipmentListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfShipmentResponse
	 */
	public $SubmitOrderShipmentListResult;
}}

if (!class_exists("APIResultOfArrayOfShipmentResponse", false)) {
/**
 * APIResultOfArrayOfShipmentResponse
 */
class APIResultOfArrayOfShipmentResponse {
	/**
	 * @access public
	 * @var tnsResultStatus
	 */
	public $Status;
	/**
	 * @access public
	 * @var sint
	 */
	public $MessageCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Message;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Data;
	/**
	 * @access public
	 * @var ArrayOfShipmentResponse
	 */
	public $ResultData;
}}

if (!class_exists("Ping", false)) {
/**
 * Ping
 */
class Ping {
}}

if (!class_exists("PingResponse", false)) {
/**
 * PingResponse
 */
class PingResponse {
	/**
	 * @access public
	 * @var APIResultOfString
	 */
	public $PingResult;
}}

if (!class_exists("APIResultOfString", false)) {
/**
 * APIResultOfString
 */
class APIResultOfString {
	/**
	 * @access public
	 * @var tnsResultStatus
	 */
	public $Status;
	/**
	 * @access public
	 * @var sint
	 */
	public $MessageCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Message;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Data;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ResultData;
}}

if (!class_exists("OrderShipmentHistoryResponse", false)) {
/**
 * OrderShipmentHistoryResponse
 */
class OrderShipmentHistoryResponse {
	/**
	 * @access public
	 * @var sint
	 */
	public $OrderID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ClientOrderIdentifier;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ShippingStatus;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $ShippingStatusUpdateDateGMT;
	/**
	 * @access public
	 * @var ArrayOfOrderShipmentResponse
	 */
	public $OrderShipments;
}}

if (!class_exists("OrderShipmentResponse", false)) {
/**
 * OrderShipmentResponse
 */
class OrderShipmentResponse {
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $ShipmentDateGMT;
	/**
	 * @access public
	 * @var sstring
	 */
	public $CarrierCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ClassCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $TrackingNumber;
	/**
	 * @access public
	 * @var sstring
	 */
	public $DistributionCenterCode;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ShipmentCost;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ShipmentTaxCost;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ShipmentInsuranceCost;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SellerFulfillmentID;
	/**
	 * @access public
	 * @var ArrayOfShipmentLineItemResponse
	 */
	public $ShipmentLineItems;
}}

if (!class_exists("ShipmentLineItemResponse", false)) {
/**
 * ShipmentLineItemResponse
 */
class ShipmentLineItemResponse {
	/**
	 * @access public
	 * @var sint
	 */
	public $LineItemID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SKU;
	/**
	 * @access public
	 * @var sint
	 */
	public $Quantity;
}}

if (!class_exists("ShipmentResponse", false)) {
/**
 * ShipmentResponse
 */
class ShipmentResponse {
	/**
	 * @access public
	 * @var sboolean
	 */
	public $Success;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Message;
}}

if (!class_exists("ShippingService", false)) {
/**
 * ShippingService
 * @author WSDLInterpreter
 */
class ShippingService extends SoapClient {
	/**
	 * Default class map for wsdl=>php
	 * @access private
	 * @var array
	 */
	private static $classmap = array(
		"GetShippingRateList" => "GetShippingRateList",
		"GetShippingRateListResponse" => "GetShippingRateListResponse",
		"APIResultOfShippingRateResult" => "APIResultOfShippingRateResult",
		"ResultStatus" => "ResultStatus",
		"ShippingRateResult" => "ShippingRateResult",
		"ShippingItemBase" => "ShippingItemBase",
		"APICredentials" => "APICredentials",
		"GetShippingCarrierList" => "GetShippingCarrierList",
		"GetShippingCarrierListResponse" => "GetShippingCarrierListResponse",
		"APIResultOfArrayOfShippingCarrier" => "APIResultOfArrayOfShippingCarrier",
		"ShippingCarrier" => "ShippingCarrier",
		"GetOrderShipmentHistoryList" => "GetOrderShipmentHistoryList",
		"GetOrderShipmentHistoryListResponse" => "GetOrderShipmentHistoryListResponse",
		"APIResultOfArrayOfOrderShipmentHistoryResponse" => "APIResultOfArrayOfOrderShipmentHistoryResponse",
		"SubmitOrderShipmentList" => "SubmitOrderShipmentList",
		"OrderShipment" => "OrderShipment",
		"PartialShipmentContents" => "PartialShipmentContents",
		"LineItem" => "LineItem",
		"FullShipmentContents" => "FullShipmentContents",
		"SubmitOrderShipmentListResponse" => "SubmitOrderShipmentListResponse",
		"APIResultOfArrayOfShipmentResponse" => "APIResultOfArrayOfShipmentResponse",
		"Ping" => "Ping",
		"PingResponse" => "PingResponse",
		"APIResultOfString" => "APIResultOfString",
		"OrderShipmentHistoryResponse" => "OrderShipmentHistoryResponse",
		"OrderShipmentResponse" => "OrderShipmentResponse",
		"ShipmentLineItemResponse" => "ShipmentLineItemResponse",
		"ShipmentResponse" => "ShipmentResponse",
	);

	/**
	 * Constructor using wsdl location and options array
	 * @param string $wsdl WSDL location for this service
	 * @param array $options Options for the SoapClient
	 */
	public function __construct($wsdl="https://api.channeladvisor.com/ChannelAdvisorAPI/v7/ShippingService.asmx?WSDL", $options=array()) {
		foreach(self::$classmap as $wsdlClassName => $phpClassName) {
		    if(!isset($options['classmap'][$wsdlClassName])) {
		        $options['classmap'][$wsdlClassName] = $phpClassName;
		    }
		}
		$options['trace'] = isset($options['trace']) ? $options['trace'] : true;
		parent::__construct($wsdl, $options);
	}

	/**
	 * Checks if an argument list matches against a valid argument type list
	 * @param array $arguments The argument list to check
	 * @param array $validParameters A list of valid argument types
	 * @return boolean true if arguments match against validParameters
	 * @throws Exception invalid function signature message
	 */
	public function _checkArguments($arguments, $validParameters) {
		$variables = "";
		foreach ($arguments as $arg) {
		    $type = gettype($arg);
		    if ($type == "object") {
		        $type = get_class($arg);
		    }
		    $variables .= "(".$type.")";
		}
		if (!in_array($variables, $validParameters)) {
		    throw new Exception("Invalid parameter types: ".str_replace(")(", ", ", $variables));
		}
		return true;
	}

	/**
	 * Service Call: GetShippingRateList
	 * Parameter options:
	 * (GetShippingRateList) parameters
	 * (GetShippingRateList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetShippingRateListResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetShippingRateList($mixed = null) {
		$validParameters = array(
			"(GetShippingRateList)",
			"(GetShippingRateList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetShippingRateList", $args);
	}


	/**
	 * Service Call: GetShippingCarrierList
	 * Parameter options:
	 * (GetShippingCarrierList) parameters
	 * (GetShippingCarrierList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetShippingCarrierListResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetShippingCarrierList($mixed = null) {
		$validParameters = array(
			"(GetShippingCarrierList)",
			"(GetShippingCarrierList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetShippingCarrierList", $args);
	}


	/**
	 * Service Call: GetOrderShipmentHistoryList
	 * Parameter options:
	 * (GetOrderShipmentHistoryList) parameters
	 * (GetOrderShipmentHistoryList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetOrderShipmentHistoryListResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetOrderShipmentHistoryList($mixed = null) {
		$validParameters = array(
			"(GetOrderShipmentHistoryList)",
			"(GetOrderShipmentHistoryList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetOrderShipmentHistoryList", $args);
	}


	/**
	 * Service Call: SubmitOrderShipmentList
	 * Parameter options:
	 * (SubmitOrderShipmentList) parameters
	 * (SubmitOrderShipmentList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return SubmitOrderShipmentListResponse
	 * @throws Exception invalid function signature message
	 */
	public function SubmitOrderShipmentList($mixed = null) {
		$validParameters = array(
			"(SubmitOrderShipmentList)",
			"(SubmitOrderShipmentList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("SubmitOrderShipmentList", $args);
	}


	/**
	 * Service Call: Ping
	 * Parameter options:
	 * (Ping) parameters
	 * (Ping) parameters
	 * @param mixed,... See function description for parameter options
	 * @return PingResponse
	 * @throws Exception invalid function signature message
	 */
	public function Ping($mixed = null) {
		$validParameters = array(
			"(Ping)",
			"(Ping)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("Ping", $args);
	}


}}

?>