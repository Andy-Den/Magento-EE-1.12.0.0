<?php

if (!class_exists("GetAuthorizationList", false)) {
/**
 * GetAuthorizationList
 */
class GetAuthorizationList {
	/**
	 * @access public
	 * @var sinteger
	 */
	public $localID;
}}

if (!class_exists("GetAuthorizationListResponse", false)) {
/**
 * GetAuthorizationListResponse
 */
class GetAuthorizationListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfAuthorizationResponse
	 */
	public $GetAuthorizationListResult;
}}

if (!class_exists("APIResultOfArrayOfAuthorizationResponse", false)) {
/**
 * APIResultOfArrayOfAuthorizationResponse
 */
class APIResultOfArrayOfAuthorizationResponse {
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
	 * @var ArrayOfAuthorizationResponse
	 */
	public $ResultData;
}}

if (!class_exists("ResultStatus", false)) {
/**
 * ResultStatus
 */
class ResultStatus {
}}

if (!class_exists("AuthorizationResponse", false)) {
/**
 * AuthorizationResponse
 */
class AuthorizationResponse {
	/**
	 * @access public
	 * @var sstring
	 */
	public $AccountID;
	/**
	 * @access public
	 * @var sint
	 */
	public $LocalID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $AccountName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $AccountType;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ResourceName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Status;
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

if (!class_exists("RequestAccess", false)) {
/**
 * RequestAccess
 */
class RequestAccess {
	/**
	 * @access public
	 * @var sint
	 */
	public $localID;
}}

if (!class_exists("RequestAccessResponse", false)) {
/**
 * RequestAccessResponse
 */
class RequestAccessResponse {
	/**
	 * @access public
	 * @var APIResultOfBoolean
	 */
	public $RequestAccessResult;
}}

if (!class_exists("APIResultOfBoolean", false)) {
/**
 * APIResultOfBoolean
 */
class APIResultOfBoolean {
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
	 * @var sboolean
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

if (!class_exists("AdminService")) {
/**
 * AdminService
 * @author WSDLInterpreter
 */
class AdminService extends SoapClient {
	/**
	 * Default class map for wsdl=>php
	 * @access private
	 * @var array
	 */
	private static $classmap = array(
		"GetAuthorizationList" => "GetAuthorizationList",
		"GetAuthorizationListResponse" => "GetAuthorizationListResponse",
		"APIResultOfArrayOfAuthorizationResponse" => "APIResultOfArrayOfAuthorizationResponse",
		"ResultStatus" => "ResultStatus",
		"AuthorizationResponse" => "AuthorizationResponse",
		"APICredentials" => "APICredentials",
		"RequestAccess" => "RequestAccess",
		"RequestAccessResponse" => "RequestAccessResponse",
		"APIResultOfBoolean" => "APIResultOfBoolean",
		"Ping" => "Ping",
		"PingResponse" => "PingResponse",
		"APIResultOfString" => "APIResultOfString",
	);

	/**
	 * Constructor using wsdl location and options array
	 * @param string $wsdl WSDL location for this service
	 * @param array $options Options for the SoapClient
	 */
	public function __construct($wsdl="https://api.channeladvisor.com/ChannelAdvisorAPI/v7/AdminService.asmx?WSDL", $options=array()) {
		foreach(self::$classmap as $wsdlClassName => $phpClassName) {
		    if(!isset($options['classmap'][$wsdlClassName])) {
		        $options['classmap'][$wsdlClassName] = $phpClassName;
		    }
		}
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
	 * Service Call: GetAuthorizationList
	 * Parameter options:
	 * (GetAuthorizationList) parameters
	 * (GetAuthorizationList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetAuthorizationListResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetAuthorizationList($mixed = null) {
		$validParameters = array(
			"(GetAuthorizationList)",
			"(GetAuthorizationList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetAuthorizationList", $args);
	}


	/**
	 * Service Call: RequestAccess
	 * Parameter options:
	 * (RequestAccess) parameters
	 * (RequestAccess) parameters
	 * @param mixed,... See function description for parameter options
	 * @return RequestAccessResponse
	 * @throws Exception invalid function signature message
	 */
	public function RequestAccess($mixed = null) {
		$validParameters = array(
			"(RequestAccess)",
			"(RequestAccess)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("RequestAccess", $args);
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