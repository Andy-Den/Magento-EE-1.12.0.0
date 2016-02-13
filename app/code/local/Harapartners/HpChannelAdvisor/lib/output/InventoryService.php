<?php

if (!class_exists("DoesSkuExist", false)) {
/**
 * DoesSkuExist
 */
class DoesSkuExist {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $sku;
}}

if (!class_exists("DoesSkuExistResponse", false)) {
/**
 * DoesSkuExistResponse
 */
class DoesSkuExistResponse {
	/**
	 * @access public
	 * @var sstring
	 */
	public $Sku;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $Result;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ErrorMessage;
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

if (!class_exists("ResultStatus", false)) {
/**
 * ResultStatus
 */
class ResultStatus {
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

if (!class_exists("DoesSkuExistList", false)) {
/**
 * DoesSkuExistList
 */
class DoesSkuExistList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var ArrayOfString
	 */
	public $skuList;
}}

if (!class_exists("DoesSkuExistListResponse", false)) {
/**
 * DoesSkuExistListResponse
 */
class DoesSkuExistListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfDoesSkuExistResponse
	 */
	public $DoesSkuExistListResult;
}}

if (!class_exists("APIResultOfArrayOfDoesSkuExistResponse", false)) {
/**
 * APIResultOfArrayOfDoesSkuExistResponse
 */
class APIResultOfArrayOfDoesSkuExistResponse {
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
	 * @var ArrayOfDoesSkuExistResponse
	 */
	public $ResultData;
}}

if (!class_exists("GetInventoryItemList", false)) {
/**
 * GetInventoryItemList
 */
class GetInventoryItemList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var ArrayOfString
	 */
	public $skuList;
}}

if (!class_exists("GetInventoryItemListResponse", false)) {
/**
 * GetInventoryItemListResponse
 */
class GetInventoryItemListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfInventoryItemResponse
	 */
	public $GetInventoryItemListResult;
}}

if (!class_exists("APIResultOfArrayOfInventoryItemResponse", false)) {
/**
 * APIResultOfArrayOfInventoryItemResponse
 */
class APIResultOfArrayOfInventoryItemResponse {
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
	 * @var ArrayOfInventoryItemResponse
	 */
	public $ResultData;
}}

if (!class_exists("InventoryItemResponse", false)) {
/**
 * InventoryItemResponse
 */
class InventoryItemResponse {
	/**
	 * @access public
	 * @var sstring
	 */
	public $Sku;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Title;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Subtitle;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ShortDescription;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Description;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $Weight;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SupplierCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $WarehouseLocation;
	/**
	 * @access public
	 * @var sstring
	 */
	public $TaxProductCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $FlagStyle;
	/**
	 * @access public
	 * @var sstring
	 */
	public $FlagDescription;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $IsBlocked;
	/**
	 * @access public
	 * @var sstring
	 */
	public $BlockComment;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ASIN;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ISBN;
	/**
	 * @access public
	 * @var sstring
	 */
	public $UPC;
	/**
	 * @access public
	 * @var sstring
	 */
	public $MPN;
	/**
	 * @access public
	 * @var sstring
	 */
	public $EAN;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Manufacturer;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Brand;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Condition;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Warranty;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ProductMargin;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SupplierPO;
	/**
	 * @access public
	 * @var sstring
	 */
	public $HarmonizedCode;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $Height;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $Length;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $Width;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Classification;
	/**
	 * @access public
	 * @var ArrayOfDistributionCenterInfoResponse
	 */
	public $DistributionCenterList;
	/**
	 * @access public
	 * @var QuantityInfoResponse
	 */
	public $Quantity;
	/**
	 * @access public
	 * @var PriceInfo
	 */
	public $PriceInfo;
	/**
	 * @access public
	 * @var ArrayOfAttributeInfo
	 */
	public $AttributeList;
	/**
	 * @access public
	 * @var VariationInfo
	 */
	public $VariationInfo;
	/**
	 * @access public
	 * @var StoreInfo
	 */
	public $StoreInfo;
	/**
	 * @access public
	 * @var ArrayOfImageInfoResponse
	 */
	public $ImageList;
	/**
	 * @access public
	 * @var sstring
	 */
	public $MetaDescription;
}}

if (!class_exists("DistributionCenterInfoResponse", false)) {
/**
 * DistributionCenterInfoResponse
 */
class DistributionCenterInfoResponse {
	/**
	 * @access public
	 * @var sstring
	 */
	public $DistributionCenterCode;
	/**
	 * @access public
	 * @var sint
	 */
	public $AvailableQuantity;
	/**
	 * @access public
	 * @var sint
	 */
	public $OpenAllocatedQuantity;
	/**
	 * @access public
	 * @var sint
	 */
	public $OpenAllocatedPooledQuantity;
	/**
	 * @access public
	 * @var sstring
	 */
	public $WarehouseLocation;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $ReceivedInInventory;
	/**
	 * @access public
	 * @var ArrayOfShippingRateInfo
	 */
	public $ShippingRateList;
}}

if (!class_exists("ShippingRateInfo", false)) {
/**
 * ShippingRateInfo
 */
class ShippingRateInfo {
	/**
	 * @access public
	 * @var sstring
	 */
	public $DestinationZoneName;
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
	 * @var sdecimal
	 */
	public $FirstItemRate;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $AdditionalItemRate;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $FirstItemHandlingRate;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $AdditionalItemHandlingRate;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $FreeShippingIfBuyItNow;
	/**
	 * @access public
	 * @var sstring
	 */
	public $FirstItemRateAttribute;
	/**
	 * @access public
	 * @var sstring
	 */
	public $FirstItemHandlingRateAttribute;
	/**
	 * @access public
	 * @var sstring
	 */
	public $AdditionalItemRateAttribute;
	/**
	 * @access public
	 * @var sstring
	 */
	public $AdditionalItemHandlingRateAttribute;
}}

if (!class_exists("QuantityInfoResponse", false)) {
/**
 * QuantityInfoResponse
 */
class QuantityInfoResponse {
	/**
	 * @access public
	 * @var sint
	 */
	public $Available;
	/**
	 * @access public
	 * @var sint
	 */
	public $OpenAllocated;
	/**
	 * @access public
	 * @var sint
	 */
	public $OpenUnallocated;
	/**
	 * @access public
	 * @var sint
	 */
	public $PendingCheckout;
	/**
	 * @access public
	 * @var sint
	 */
	public $PendingPayment;
	/**
	 * @access public
	 * @var sint
	 */
	public $PendingShipment;
	/**
	 * @access public
	 * @var sint
	 */
	public $Total;
	/**
	 * @access public
	 * @var sint
	 */
	public $OpenAllocatedPooled;
	/**
	 * @access public
	 * @var sint
	 */
	public $OpenUnallocatedPooled;
	/**
	 * @access public
	 * @var sint
	 */
	public $PendingCheckoutPooled;
	/**
	 * @access public
	 * @var sint
	 */
	public $PendingPaymentPooled;
	/**
	 * @access public
	 * @var sint
	 */
	public $PendingShipmentPooled;
	/**
	 * @access public
	 * @var sint
	 */
	public $TotalPooled;
}}

if (!class_exists("PriceInfo", false)) {
/**
 * PriceInfo
 */
class PriceInfo {
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $Cost;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $RetailPrice;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $StartingPrice;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ReservePrice;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $TakeItPrice;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $SecondChanceOfferPrice;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $StorePrice;
}}

if (!class_exists("AttributeInfo", false)) {
/**
 * AttributeInfo
 */
class AttributeInfo {
	/**
	 * @access public
	 * @var sstring
	 */
	public $Name;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Value;
}}

if (!class_exists("VariationInfo", false)) {
/**
 * VariationInfo
 */
class VariationInfo {
	/**
	 * @access public
	 * @var sboolean
	 */
	public $IsInRelationship;
	/**
	 * @access public
	 * @var sstring
	 */
	public $RelationshipName;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $IsParent;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ParentSku;
}}

if (!class_exists("StoreInfo", false)) {
/**
 * StoreInfo
 */
class StoreInfo {
	/**
	 * @access public
	 * @var sboolean
	 */
	public $DisplayInStore;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Title;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Description;
	/**
	 * @access public
	 * @var sint
	 */
	public $CategoryID;
}}

if (!class_exists("ImageInfoResponse", false)) {
/**
 * ImageInfoResponse
 */
class ImageInfoResponse {
	/**
	 * @access public
	 * @var sstring
	 */
	public $PlacementName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $FolderName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Url;
	/**
	 * @access public
	 * @var ArrayOfImageThumbInfo
	 */
	public $ImageThumbList;
}}

if (!class_exists("ImageThumbInfo", false)) {
/**
 * ImageThumbInfo
 */
class ImageThumbInfo {
	/**
	 * @access public
	 * @var sstring
	 */
	public $TypeName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Url;
}}

if (!class_exists("GetInventoryItemListWithFullDetail", false)) {
/**
 * GetInventoryItemListWithFullDetail
 */
class GetInventoryItemListWithFullDetail {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var ArrayOfString
	 */
	public $skuList;
}}

if (!class_exists("GetInventoryItemListWithFullDetailResponse", false)) {
/**
 * GetInventoryItemListWithFullDetailResponse
 */
class GetInventoryItemListWithFullDetailResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfInventoryItemResponse
	 */
	public $GetInventoryItemListWithFullDetailResult;
}}

if (!class_exists("GetFilteredInventoryItemList", false)) {
/**
 * GetFilteredInventoryItemList
 */
class GetFilteredInventoryItemList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var InventoryItemCriteria
	 */
	public $itemCriteria;
	/**
	 * @access public
	 * @var InventoryItemDetailLevel
	 */
	public $detailLevel;
	/**
	 * @access public
	 * @var sstring
	 */
	public $sortField;
	/**
	 * @access public
	 * @var sstring
	 */
	public $sortDirection;
}}

if (!class_exists("InventoryItemCriteria", false)) {
/**
 * InventoryItemCriteria
 */
class InventoryItemCriteria {
	/**
	 * @access public
	 * @var sstring
	 */
	public $DateRangeField;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $DateRangeStartGMT;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $DateRangeEndGMT;
	/**
	 * @access public
	 * @var sstring
	 */
	public $PartialSku;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SkuStartsWith;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SkuEndsWith;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ClassificationName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $LabelName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $QuantityCheckField;
	/**
	 * @access public
	 * @var sstring
	 */
	public $QuantityCheckType;
	/**
	 * @access public
	 * @var sint
	 */
	public $QuantityCheckValue;
	/**
	 * @access public
	 * @var sint
	 */
	public $PageNumber;
	/**
	 * @access public
	 * @var sint
	 */
	public $PageSize;
}}

if (!class_exists("InventoryItemDetailLevel", false)) {
/**
 * InventoryItemDetailLevel
 */
class InventoryItemDetailLevel {
	/**
	 * @access public
	 * @var sboolean
	 */
	public $IncludeQuantityInfo;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $IncludePriceInfo;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $IncludeClassificationInfo;
}}

if (!class_exists("GetFilteredInventoryItemListResponse", false)) {
/**
 * GetFilteredInventoryItemListResponse
 */
class GetFilteredInventoryItemListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfInventoryItemResponse
	 */
	public $GetFilteredInventoryItemListResult;
}}

if (!class_exists("GetFilteredSkuList", false)) {
/**
 * GetFilteredSkuList
 */
class GetFilteredSkuList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var InventoryItemCriteria
	 */
	public $itemCriteria;
	/**
	 * @access public
	 * @var sstring
	 */
	public $sortField;
	/**
	 * @access public
	 * @var sstring
	 */
	public $sortDirection;
}}

if (!class_exists("GetFilteredSkuListResponse", false)) {
/**
 * GetFilteredSkuListResponse
 */
class GetFilteredSkuListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfString
	 */
	public $GetFilteredSkuListResult;
}}

if (!class_exists("APIResultOfArrayOfString", false)) {
/**
 * APIResultOfArrayOfString
 */
class APIResultOfArrayOfString {
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
	 * @var ArrayOfString
	 */
	public $ResultData;
}}

if (!class_exists("GetInventoryItemShippingInfo", false)) {
/**
 * GetInventoryItemShippingInfo
 */
class GetInventoryItemShippingInfo {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $sku;
}}

if (!class_exists("GetInventoryItemShippingInfoResponse", false)) {
/**
 * GetInventoryItemShippingInfoResponse
 */
class GetInventoryItemShippingInfoResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfDistributionCenterInfoResponse
	 */
	public $GetInventoryItemShippingInfoResult;
}}

if (!class_exists("APIResultOfArrayOfDistributionCenterInfoResponse", false)) {
/**
 * APIResultOfArrayOfDistributionCenterInfoResponse
 */
class APIResultOfArrayOfDistributionCenterInfoResponse {
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
	 * @var ArrayOfDistributionCenterInfoResponse
	 */
	public $ResultData;
}}

if (!class_exists("GetInventoryItemQuantityInfo", false)) {
/**
 * GetInventoryItemQuantityInfo
 */
class GetInventoryItemQuantityInfo {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $sku;
}}

if (!class_exists("GetInventoryItemQuantityInfoResponse", false)) {
/**
 * GetInventoryItemQuantityInfoResponse
 */
class GetInventoryItemQuantityInfoResponse {
	/**
	 * @access public
	 * @var APIResultOfQuantityInfoResponse
	 */
	public $GetInventoryItemQuantityInfoResult;
}}

if (!class_exists("APIResultOfQuantityInfoResponse", false)) {
/**
 * APIResultOfQuantityInfoResponse
 */
class APIResultOfQuantityInfoResponse {
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
	 * @var QuantityInfoResponse
	 */
	public $ResultData;
}}

if (!class_exists("GetClassificationConfigurationInformation", false)) {
/**
 * GetClassificationConfigurationInformation
 */
class GetClassificationConfigurationInformation {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
}}

if (!class_exists("GetClassificationConfigurationInformationResponse", false)) {
/**
 * GetClassificationConfigurationInformationResponse
 */
class GetClassificationConfigurationInformationResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfClassificationConfigurationInformation
	 */
	public $GetClassificationConfigurationInformationResult;
}}

if (!class_exists("APIResultOfArrayOfClassificationConfigurationInformation", false)) {
/**
 * APIResultOfArrayOfClassificationConfigurationInformation
 */
class APIResultOfArrayOfClassificationConfigurationInformation {
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
	 * @var ArrayOfClassificationConfigurationInformation
	 */
	public $ResultData;
}}

if (!class_exists("ClassificationConfigurationInformation", false)) {
/**
 * ClassificationConfigurationInformation
 */
class ClassificationConfigurationInformation {
	/**
	 * @access public
	 * @var sstring
	 */
	public $Name;
	/**
	 * @access public
	 * @var ArrayOfClassificationConfigurationInformationAttribute
	 */
	public $ClassificationConfigurationInformationAttributeArray;
}}

if (!class_exists("ClassificationConfigurationInformationAttribute", false)) {
/**
 * ClassificationConfigurationInformationAttribute
 */
class ClassificationConfigurationInformationAttribute {
	/**
	 * @access public
	 * @var sstring
	 */
	public $Name;
	/**
	 * @access public
	 * @var sstring
	 */
	public $DefaultValue;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ListOfChoices;
}}

if (!class_exists("GetInventoryItemAttributeList", false)) {
/**
 * GetInventoryItemAttributeList
 */
class GetInventoryItemAttributeList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $sku;
}}

if (!class_exists("GetInventoryItemAttributeListResponse", false)) {
/**
 * GetInventoryItemAttributeListResponse
 */
class GetInventoryItemAttributeListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfAttributeInfo
	 */
	public $GetInventoryItemAttributeListResult;
}}

if (!class_exists("APIResultOfArrayOfAttributeInfo", false)) {
/**
 * APIResultOfArrayOfAttributeInfo
 */
class APIResultOfArrayOfAttributeInfo {
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
	 * @var ArrayOfAttributeInfo
	 */
	public $ResultData;
}}

if (!class_exists("GetInventoryItemVariationInfo", false)) {
/**
 * GetInventoryItemVariationInfo
 */
class GetInventoryItemVariationInfo {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $sku;
}}

if (!class_exists("GetInventoryItemVariationInfoResponse", false)) {
/**
 * GetInventoryItemVariationInfoResponse
 */
class GetInventoryItemVariationInfoResponse {
	/**
	 * @access public
	 * @var APIResultOfVariationInfo
	 */
	public $GetInventoryItemVariationInfoResult;
}}

if (!class_exists("APIResultOfVariationInfo", false)) {
/**
 * APIResultOfVariationInfo
 */
class APIResultOfVariationInfo {
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
	 * @var VariationInfo
	 */
	public $ResultData;
}}

if (!class_exists("GetInventoryItemStoreInfo", false)) {
/**
 * GetInventoryItemStoreInfo
 */
class GetInventoryItemStoreInfo {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $sku;
}}

if (!class_exists("GetInventoryItemStoreInfoResponse", false)) {
/**
 * GetInventoryItemStoreInfoResponse
 */
class GetInventoryItemStoreInfoResponse {
	/**
	 * @access public
	 * @var APIResultOfStoreInfo
	 */
	public $GetInventoryItemStoreInfoResult;
}}

if (!class_exists("APIResultOfStoreInfo", false)) {
/**
 * APIResultOfStoreInfo
 */
class APIResultOfStoreInfo {
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
	 * @var StoreInfo
	 */
	public $ResultData;
}}

if (!class_exists("GetInventoryItemImageList", false)) {
/**
 * GetInventoryItemImageList
 */
class GetInventoryItemImageList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $sku;
}}

if (!class_exists("GetInventoryItemImageListResponse", false)) {
/**
 * GetInventoryItemImageListResponse
 */
class GetInventoryItemImageListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfImageInfoResponse
	 */
	public $GetInventoryItemImageListResult;
}}

if (!class_exists("APIResultOfArrayOfImageInfoResponse", false)) {
/**
 * APIResultOfArrayOfImageInfoResponse
 */
class APIResultOfArrayOfImageInfoResponse {
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
	 * @var ArrayOfImageInfoResponse
	 */
	public $ResultData;
}}

if (!class_exists("GetInventoryQuantity", false)) {
/**
 * GetInventoryQuantity
 */
class GetInventoryQuantity {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $sku;
}}

if (!class_exists("GetInventoryQuantityResponse", false)) {
/**
 * GetInventoryQuantityResponse
 */
class GetInventoryQuantityResponse {
	/**
	 * @access public
	 * @var APIResultOfInt32
	 */
	public $GetInventoryQuantityResult;
}}

if (!class_exists("APIResultOfInt32", false)) {
/**
 * APIResultOfInt32
 */
class APIResultOfInt32 {
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
	 * @var sint
	 */
	public $ResultData;
}}

if (!class_exists("GetInventoryQuantityList", false)) {
/**
 * GetInventoryQuantityList
 */
class GetInventoryQuantityList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var ArrayOfString
	 */
	public $skuList;
}}

if (!class_exists("GetInventoryQuantityListResponse", false)) {
/**
 * GetInventoryQuantityListResponse
 */
class GetInventoryQuantityListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfInventoryQuantityResponse
	 */
	public $GetInventoryQuantityListResult;
}}

if (!class_exists("APIResultOfArrayOfInventoryQuantityResponse", false)) {
/**
 * APIResultOfArrayOfInventoryQuantityResponse
 */
class APIResultOfArrayOfInventoryQuantityResponse {
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
	 * @var ArrayOfInventoryQuantityResponse
	 */
	public $ResultData;
}}

if (!class_exists("InventoryQuantityResponse", false)) {
/**
 * InventoryQuantityResponse
 */
class InventoryQuantityResponse {
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
}}

if (!class_exists("DeleteInventoryItem", false)) {
/**
 * DeleteInventoryItem
 */
class DeleteInventoryItem {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $sku;
}}

if (!class_exists("DeleteInventoryItemResponse", false)) {
/**
 * DeleteInventoryItemResponse
 */
class DeleteInventoryItemResponse {
	/**
	 * @access public
	 * @var APIResultOfBoolean
	 */
	public $DeleteInventoryItemResult;
}}

if (!class_exists("SynchInventoryItem", false)) {
/**
 * SynchInventoryItem
 */
class SynchInventoryItem {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var InventoryItemSubmit
	 */
	public $item;
}}

if (!class_exists("InventoryItemSubmit", false)) {
/**
 * InventoryItemSubmit
 */
class InventoryItemSubmit {
	/**
	 * @access public
	 * @var sstring
	 */
	public $Sku;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Title;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Subtitle;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ShortDescription;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Description;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $Weight;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SupplierCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $WarehouseLocation;
	/**
	 * @access public
	 * @var sstring
	 */
	public $TaxProductCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $FlagStyle;
	/**
	 * @access public
	 * @var sstring
	 */
	public $FlagDescription;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $IsBlocked;
	/**
	 * @access public
	 * @var sstring
	 */
	public $BlockComment;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ASIN;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ISBN;
	/**
	 * @access public
	 * @var sstring
	 */
	public $UPC;
	/**
	 * @access public
	 * @var sstring
	 */
	public $MPN;
	/**
	 * @access public
	 * @var sstring
	 */
	public $EAN;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Manufacturer;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Brand;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Condition;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Warranty;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ProductMargin;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SupplierPO;
	/**
	 * @access public
	 * @var sstring
	 */
	public $HarmonizedCode;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $Height;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $Length;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $Width;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Classification;
	/**
	 * @access public
	 * @var ArrayOfDistributionCenterInfoSubmit
	 */
	public $DistributionCenterList;
	/**
	 * @access public
	 * @var PriceInfo
	 */
	public $PriceInfo;
	/**
	 * @access public
	 * @var ArrayOfAttributeInfo
	 */
	public $AttributeList;
	/**
	 * @access public
	 * @var VariationInfo
	 */
	public $VariationInfo;
	/**
	 * @access public
	 * @var StoreInfo
	 */
	public $StoreInfo;
	/**
	 * @access public
	 * @var ArrayOfImageInfoSubmit
	 */
	public $ImageList;
	/**
	 * @access public
	 * @var ArrayOfString
	 */
	public $LabelList;
	/**
	 * @access public
	 * @var sstring
	 */
	public $MetaDescription;
}}

if (!class_exists("DistributionCenterInfoSubmit", false)) {
/**
 * DistributionCenterInfoSubmit
 */
class DistributionCenterInfoSubmit {
	/**
	 * @access public
	 * @var sstring
	 */
	public $DistributionCenterCode;
	/**
	 * @access public
	 * @var sint
	 */
	public $Quantity;
	/**
	 * @access public
	 * @var sstring
	 */
	public $QuantityUpdateType;
	/**
	 * @access public
	 * @var sstring
	 */
	public $WarehouseLocation;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $ReceivedInInventory;
	/**
	 * @access public
	 * @var ArrayOfShippingRateInfo
	 */
	public $ShippingRateList;
}}

if (!class_exists("ImageInfoSubmit", false)) {
/**
 * ImageInfoSubmit
 */
class ImageInfoSubmit {
	/**
	 * @access public
	 * @var sstring
	 */
	public $PlacementName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $FolderName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $FilenameOrUrl;
}}

if (!class_exists("SynchInventoryItemResponse", false)) {
/**
 * SynchInventoryItemResponse
 */
class SynchInventoryItemResponse {
	/**
	 * @access public
	 * @var sstring
	 */
	public $Sku;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $Result;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ErrorMessage;
}}

if (!class_exists("SynchInventoryItemList", false)) {
/**
 * SynchInventoryItemList
 */
class SynchInventoryItemList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var ArrayOfInventoryItemSubmit
	 */
	public $itemList;
}}

if (!class_exists("SynchInventoryItemListResponse", false)) {
/**
 * SynchInventoryItemListResponse
 */
class SynchInventoryItemListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfSynchInventoryItemResponse
	 */
	public $SynchInventoryItemListResult;
}}

if (!class_exists("APIResultOfArrayOfSynchInventoryItemResponse", false)) {
/**
 * APIResultOfArrayOfSynchInventoryItemResponse
 */
class APIResultOfArrayOfSynchInventoryItemResponse {
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
	 * @var ArrayOfSynchInventoryItemResponse
	 */
	public $ResultData;
}}

if (!class_exists("UpdateInventoryItemQuantityAndPrice", false)) {
/**
 * UpdateInventoryItemQuantityAndPrice
 */
class UpdateInventoryItemQuantityAndPrice {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var InventoryItemQuantityAndPrice
	 */
	public $itemQuantityAndPrice;
}}

if (!class_exists("InventoryItemQuantityAndPrice", false)) {
/**
 * InventoryItemQuantityAndPrice
 */
class InventoryItemQuantityAndPrice {
	/**
	 * @access public
	 * @var sstring
	 */
	public $Sku;
	/**
	 * @access public
	 * @var sstring
	 */
	public $DistributionCenterCode;
	/**
	 * @access public
	 * @var sint
	 */
	public $Quantity;
	/**
	 * @access public
	 * @var sstring
	 */
	public $UpdateType;
	/**
	 * @access public
	 * @var PriceInfo
	 */
	public $PriceInfo;
}}

if (!class_exists("UpdateInventoryItemQuantityAndPriceResponse", false)) {
/**
 * UpdateInventoryItemQuantityAndPriceResponse
 */
class UpdateInventoryItemQuantityAndPriceResponse {
	/**
	 * @access public
	 * @var APIResultOfBoolean
	 */
	public $UpdateInventoryItemQuantityAndPriceResult;
}}

if (!class_exists("UpdateInventoryItemQuantityAndPriceList", false)) {
/**
 * UpdateInventoryItemQuantityAndPriceList
 */
class UpdateInventoryItemQuantityAndPriceList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var ArrayOfInventoryItemQuantityAndPrice
	 */
	public $itemQuantityAndPriceList;
}}

if (!class_exists("UpdateInventoryItemQuantityAndPriceListResponse", false)) {
/**
 * UpdateInventoryItemQuantityAndPriceListResponse
 */
class UpdateInventoryItemQuantityAndPriceListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfUpdateInventoryItemResponse
	 */
	public $UpdateInventoryItemQuantityAndPriceListResult;
}}

if (!class_exists("APIResultOfArrayOfUpdateInventoryItemResponse", false)) {
/**
 * APIResultOfArrayOfUpdateInventoryItemResponse
 */
class APIResultOfArrayOfUpdateInventoryItemResponse {
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
	 * @var ArrayOfUpdateInventoryItemResponse
	 */
	public $ResultData;
}}

if (!class_exists("UpdateInventoryItemResponse", false)) {
/**
 * UpdateInventoryItemResponse
 */
class UpdateInventoryItemResponse {
	/**
	 * @access public
	 * @var sstring
	 */
	public $Sku;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $Result;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ErrorMessage;
}}

if (!class_exists("AssignLabelListToInventoryItemList", false)) {
/**
 * AssignLabelListToInventoryItemList
 */
class AssignLabelListToInventoryItemList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var ArrayOfString
	 */
	public $labelList;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $createLabelIfNotExist;
	/**
	 * @access public
	 * @var ArrayOfString
	 */
	public $skuList;
	/**
	 * @access public
	 * @var sstring
	 */
	public $assignReasonDesc;
}}

if (!class_exists("AssignLabelListToInventoryItemListResponse", false)) {
/**
 * AssignLabelListToInventoryItemListResponse
 */
class AssignLabelListToInventoryItemListResponse {
	/**
	 * @access public
	 * @var APIResultOfBoolean
	 */
	public $AssignLabelListToInventoryItemListResult;
}}

if (!class_exists("RemoveLabelListFromInventoryItemList", false)) {
/**
 * RemoveLabelListFromInventoryItemList
 */
class RemoveLabelListFromInventoryItemList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var ArrayOfString
	 */
	public $labelList;
	/**
	 * @access public
	 * @var ArrayOfString
	 */
	public $skuList;
	/**
	 * @access public
	 * @var sstring
	 */
	public $removeReasonDesc;
}}

if (!class_exists("RemoveLabelListFromInventoryItemListResponse", false)) {
/**
 * RemoveLabelListFromInventoryItemListResponse
 */
class RemoveLabelListFromInventoryItemListResponse {
	/**
	 * @access public
	 * @var APIResultOfBoolean
	 */
	public $RemoveLabelListFromInventoryItemListResult;
}}

if (!class_exists("AddUpsellRelationship", false)) {
/**
 * AddUpsellRelationship
 */
class AddUpsellRelationship {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var ArrayOfInventoryUpsellInfoSubmit
	 */
	public $upsellInfoList;
}}

if (!class_exists("InventoryUpsellInfoSubmit", false)) {
/**
 * InventoryUpsellInfoSubmit
 */
class InventoryUpsellInfoSubmit {
	/**
	 * @access public
	 * @var sstring
	 */
	public $ParentSKU;
	/**
	 * @access public
	 * @var ArrayOfInventoryUpsellChildInfo
	 */
	public $ChildItemList;
}}

if (!class_exists("InventoryUpsellChildInfo", false)) {
/**
 * InventoryUpsellChildInfo
 */
class InventoryUpsellChildInfo {
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
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $SalePrice;
}}

if (!class_exists("AddUpsellRelationshipResponse", false)) {
/**
 * AddUpsellRelationshipResponse
 */
class AddUpsellRelationshipResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfArrayOfBoolean
	 */
	public $AddUpsellRelationshipResult;
}}

if (!class_exists("APIResultOfArrayOfArrayOfBoolean", false)) {
/**
 * APIResultOfArrayOfArrayOfBoolean
 */
class APIResultOfArrayOfArrayOfBoolean {
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
	 * @var ArrayOfArrayOfBoolean
	 */
	public $ResultData;
}}

if (!class_exists("GetUpsellRelationship", false)) {
/**
 * GetUpsellRelationship
 */
class GetUpsellRelationship {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var ArrayOfString
	 */
	public $parentSKUList;
}}

if (!class_exists("GetUpsellRelationshipResponse", false)) {
/**
 * GetUpsellRelationshipResponse
 */
class GetUpsellRelationshipResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfInventoryUpsellInfoResponse
	 */
	public $GetUpsellRelationshipResult;
}}

if (!class_exists("APIResultOfArrayOfInventoryUpsellInfoResponse", false)) {
/**
 * APIResultOfArrayOfInventoryUpsellInfoResponse
 */
class APIResultOfArrayOfInventoryUpsellInfoResponse {
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
	 * @var ArrayOfInventoryUpsellInfoResponse
	 */
	public $ResultData;
}}

if (!class_exists("InventoryUpsellInfoResponse", false)) {
/**
 * InventoryUpsellInfoResponse
 */
class InventoryUpsellInfoResponse {
	/**
	 * @access public
	 * @var sstring
	 */
	public $ParentSKU;
	/**
	 * @access public
	 * @var ArrayOfInventoryUpsellChildInfo
	 */
	public $ChildItemList;
}}

if (!class_exists("DeleteUpsellRelationship", false)) {
/**
 * DeleteUpsellRelationship
 */
class DeleteUpsellRelationship {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $parentSKU;
	/**
	 * @access public
	 * @var ArrayOfString
	 */
	public $childSKUList;
}}

if (!class_exists("DeleteUpsellRelationshipResponse", false)) {
/**
 * DeleteUpsellRelationshipResponse
 */
class DeleteUpsellRelationshipResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfBoolean
	 */
	public $DeleteUpsellRelationshipResult;
}}

if (!class_exists("APIResultOfArrayOfBoolean", false)) {
/**
 * APIResultOfArrayOfBoolean
 */
class APIResultOfArrayOfBoolean {
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
	 * @var ArrayOfBoolean
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

if (!class_exists("InventoryService", false)) {
/**
 * InventoryService
 * @author WSDLInterpreter
 */
class InventoryService extends SoapClient {
	/**
	 * Default class map for wsdl=>php
	 * @access private
	 * @var array
	 */
	private static $classmap = array(
		"DoesSkuExist" => "DoesSkuExist",
		"DoesSkuExistResponse" => "DoesSkuExistResponse",
		"APIResultOfBoolean" => "APIResultOfBoolean",
		"ResultStatus" => "ResultStatus",
		"APICredentials" => "APICredentials",
		"DoesSkuExistList" => "DoesSkuExistList",
		"DoesSkuExistListResponse" => "DoesSkuExistListResponse",
		"APIResultOfArrayOfDoesSkuExistResponse" => "APIResultOfArrayOfDoesSkuExistResponse",
		"GetInventoryItemList" => "GetInventoryItemList",
		"GetInventoryItemListResponse" => "GetInventoryItemListResponse",
		"APIResultOfArrayOfInventoryItemResponse" => "APIResultOfArrayOfInventoryItemResponse",
		"InventoryItemResponse" => "InventoryItemResponse",
		"DistributionCenterInfoResponse" => "DistributionCenterInfoResponse",
		"ShippingRateInfo" => "ShippingRateInfo",
		"QuantityInfoResponse" => "QuantityInfoResponse",
		"PriceInfo" => "PriceInfo",
		"AttributeInfo" => "AttributeInfo",
		"VariationInfo" => "VariationInfo",
		"StoreInfo" => "StoreInfo",
		"ImageInfoResponse" => "ImageInfoResponse",
		"ImageThumbInfo" => "ImageThumbInfo",
		"GetInventoryItemListWithFullDetail" => "GetInventoryItemListWithFullDetail",
		"GetInventoryItemListWithFullDetailResponse" => "GetInventoryItemListWithFullDetailResponse",
		"GetFilteredInventoryItemList" => "GetFilteredInventoryItemList",
		"InventoryItemCriteria" => "InventoryItemCriteria",
		"InventoryItemDetailLevel" => "InventoryItemDetailLevel",
		"GetFilteredInventoryItemListResponse" => "GetFilteredInventoryItemListResponse",
		"GetFilteredSkuList" => "GetFilteredSkuList",
		"GetFilteredSkuListResponse" => "GetFilteredSkuListResponse",
		"APIResultOfArrayOfString" => "APIResultOfArrayOfString",
		"GetInventoryItemShippingInfo" => "GetInventoryItemShippingInfo",
		"GetInventoryItemShippingInfoResponse" => "GetInventoryItemShippingInfoResponse",
		"APIResultOfArrayOfDistributionCenterInfoResponse" => "APIResultOfArrayOfDistributionCenterInfoResponse",
		"GetInventoryItemQuantityInfo" => "GetInventoryItemQuantityInfo",
		"GetInventoryItemQuantityInfoResponse" => "GetInventoryItemQuantityInfoResponse",
		"APIResultOfQuantityInfoResponse" => "APIResultOfQuantityInfoResponse",
		"GetClassificationConfigurationInformation" => "GetClassificationConfigurationInformation",
		"GetClassificationConfigurationInformationResponse" => "GetClassificationConfigurationInformationResponse",
		"APIResultOfArrayOfClassificationConfigurationInformation" => "APIResultOfArrayOfClassificationConfigurationInformation",
		"ClassificationConfigurationInformation" => "ClassificationConfigurationInformation",
		"ClassificationConfigurationInformationAttribute" => "ClassificationConfigurationInformationAttribute",
		"GetInventoryItemAttributeList" => "GetInventoryItemAttributeList",
		"GetInventoryItemAttributeListResponse" => "GetInventoryItemAttributeListResponse",
		"APIResultOfArrayOfAttributeInfo" => "APIResultOfArrayOfAttributeInfo",
		"GetInventoryItemVariationInfo" => "GetInventoryItemVariationInfo",
		"GetInventoryItemVariationInfoResponse" => "GetInventoryItemVariationInfoResponse",
		"APIResultOfVariationInfo" => "APIResultOfVariationInfo",
		"GetInventoryItemStoreInfo" => "GetInventoryItemStoreInfo",
		"GetInventoryItemStoreInfoResponse" => "GetInventoryItemStoreInfoResponse",
		"APIResultOfStoreInfo" => "APIResultOfStoreInfo",
		"GetInventoryItemImageList" => "GetInventoryItemImageList",
		"GetInventoryItemImageListResponse" => "GetInventoryItemImageListResponse",
		"APIResultOfArrayOfImageInfoResponse" => "APIResultOfArrayOfImageInfoResponse",
		"GetInventoryQuantity" => "GetInventoryQuantity",
		"GetInventoryQuantityResponse" => "GetInventoryQuantityResponse",
		"APIResultOfInt32" => "APIResultOfInt32",
		"GetInventoryQuantityList" => "GetInventoryQuantityList",
		"GetInventoryQuantityListResponse" => "GetInventoryQuantityListResponse",
		"APIResultOfArrayOfInventoryQuantityResponse" => "APIResultOfArrayOfInventoryQuantityResponse",
		"InventoryQuantityResponse" => "InventoryQuantityResponse",
		"DeleteInventoryItem" => "DeleteInventoryItem",
		"DeleteInventoryItemResponse" => "DeleteInventoryItemResponse",
		"SynchInventoryItem" => "SynchInventoryItem",
		"InventoryItemSubmit" => "InventoryItemSubmit",
		"DistributionCenterInfoSubmit" => "DistributionCenterInfoSubmit",
		"ImageInfoSubmit" => "ImageInfoSubmit",
		"SynchInventoryItemResponse" => "SynchInventoryItemResponse",
		"SynchInventoryItemList" => "SynchInventoryItemList",
		"SynchInventoryItemListResponse" => "SynchInventoryItemListResponse",
		"APIResultOfArrayOfSynchInventoryItemResponse" => "APIResultOfArrayOfSynchInventoryItemResponse",
		"UpdateInventoryItemQuantityAndPrice" => "UpdateInventoryItemQuantityAndPrice",
		"InventoryItemQuantityAndPrice" => "InventoryItemQuantityAndPrice",
		"UpdateInventoryItemQuantityAndPriceResponse" => "UpdateInventoryItemQuantityAndPriceResponse",
		"UpdateInventoryItemQuantityAndPriceList" => "UpdateInventoryItemQuantityAndPriceList",
		"UpdateInventoryItemQuantityAndPriceListResponse" => "UpdateInventoryItemQuantityAndPriceListResponse",
		"APIResultOfArrayOfUpdateInventoryItemResponse" => "APIResultOfArrayOfUpdateInventoryItemResponse",
		"UpdateInventoryItemResponse" => "UpdateInventoryItemResponse",
		"AssignLabelListToInventoryItemList" => "AssignLabelListToInventoryItemList",
		"AssignLabelListToInventoryItemListResponse" => "AssignLabelListToInventoryItemListResponse",
		"RemoveLabelListFromInventoryItemList" => "RemoveLabelListFromInventoryItemList",
		"RemoveLabelListFromInventoryItemListResponse" => "RemoveLabelListFromInventoryItemListResponse",
		"AddUpsellRelationship" => "AddUpsellRelationship",
		"InventoryUpsellInfoSubmit" => "InventoryUpsellInfoSubmit",
		"InventoryUpsellChildInfo" => "InventoryUpsellChildInfo",
		"AddUpsellRelationshipResponse" => "AddUpsellRelationshipResponse",
		"APIResultOfArrayOfArrayOfBoolean" => "APIResultOfArrayOfArrayOfBoolean",
		"GetUpsellRelationship" => "GetUpsellRelationship",
		"GetUpsellRelationshipResponse" => "GetUpsellRelationshipResponse",
		"APIResultOfArrayOfInventoryUpsellInfoResponse" => "APIResultOfArrayOfInventoryUpsellInfoResponse",
		"InventoryUpsellInfoResponse" => "InventoryUpsellInfoResponse",
		"DeleteUpsellRelationship" => "DeleteUpsellRelationship",
		"DeleteUpsellRelationshipResponse" => "DeleteUpsellRelationshipResponse",
		"APIResultOfArrayOfBoolean" => "APIResultOfArrayOfBoolean",
		"Ping" => "Ping",
		"PingResponse" => "PingResponse",
		"APIResultOfString" => "APIResultOfString",
	);

	/**
	 * Constructor using wsdl location and options array
	 * @param string $wsdl WSDL location for this service
	 * @param array $options Options for the SoapClient
	 */
	public function __construct($wsdl="https://api.channeladvisor.com/ChannelAdvisorAPI/v7/InventoryService.asmx?WSDL", $options=array()) {
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
	 * Service Call: DoesSkuExist
	 * Parameter options:
	 * (DoesSkuExist) parameters
	 * (DoesSkuExist) parameters
	 * @param mixed,... See function description for parameter options
	 * @return DoesSkuExistResponse
	 * @throws Exception invalid function signature message
	 */
	public function DoesSkuExist($mixed = null) {
		$validParameters = array(
			"(DoesSkuExist)",
			"(DoesSkuExist)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("DoesSkuExist", $args);
	}


	/**
	 * Service Call: DoesSkuExistList
	 * Parameter options:
	 * (DoesSkuExistList) parameters
	 * (DoesSkuExistList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return DoesSkuExistListResponse
	 * @throws Exception invalid function signature message
	 */
	public function DoesSkuExistList($mixed = null) {
		$validParameters = array(
			"(DoesSkuExistList)",
			"(DoesSkuExistList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("DoesSkuExistList", $args);
	}


	/**
	 * Service Call: GetInventoryItemList
	 * Parameter options:
	 * (GetInventoryItemList) parameters
	 * (GetInventoryItemList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetInventoryItemListResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetInventoryItemList($mixed = null) {
		$validParameters = array(
			"(GetInventoryItemList)",
			"(GetInventoryItemList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetInventoryItemList", $args);
	}


	/**
	 * Service Call: GetInventoryItemListWithFullDetail
	 * Parameter options:
	 * (GetInventoryItemListWithFullDetail) parameters
	 * (GetInventoryItemListWithFullDetail) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetInventoryItemListWithFullDetailResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetInventoryItemListWithFullDetail($mixed = null) {
		$validParameters = array(
			"(GetInventoryItemListWithFullDetail)",
			"(GetInventoryItemListWithFullDetail)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetInventoryItemListWithFullDetail", $args);
	}


	/**
	 * Service Call: GetFilteredInventoryItemList
	 * Parameter options:
	 * (GetFilteredInventoryItemList) parameters
	 * (GetFilteredInventoryItemList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetFilteredInventoryItemListResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetFilteredInventoryItemList($mixed = null) {
		$validParameters = array(
			"(GetFilteredInventoryItemList)",
			"(GetFilteredInventoryItemList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetFilteredInventoryItemList", $args);
	}


	/**
	 * Service Call: GetFilteredSkuList
	 * Parameter options:
	 * (GetFilteredSkuList) parameters
	 * (GetFilteredSkuList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetFilteredSkuListResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetFilteredSkuList($mixed = null) {
		$validParameters = array(
			"(GetFilteredSkuList)",
			"(GetFilteredSkuList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetFilteredSkuList", $args);
	}


	/**
	 * Service Call: GetInventoryItemShippingInfo
	 * Parameter options:
	 * (GetInventoryItemShippingInfo) parameters
	 * (GetInventoryItemShippingInfo) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetInventoryItemShippingInfoResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetInventoryItemShippingInfo($mixed = null) {
		$validParameters = array(
			"(GetInventoryItemShippingInfo)",
			"(GetInventoryItemShippingInfo)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetInventoryItemShippingInfo", $args);
	}


	/**
	 * Service Call: GetInventoryItemQuantityInfo
	 * Parameter options:
	 * (GetInventoryItemQuantityInfo) parameters
	 * (GetInventoryItemQuantityInfo) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetInventoryItemQuantityInfoResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetInventoryItemQuantityInfo($mixed = null) {
		$validParameters = array(
			"(GetInventoryItemQuantityInfo)",
			"(GetInventoryItemQuantityInfo)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetInventoryItemQuantityInfo", $args);
	}


	/**
	 * Service Call: GetClassificationConfigurationInformation
	 * Parameter options:
	 * (GetClassificationConfigurationInformation) parameters
	 * (GetClassificationConfigurationInformation) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetClassificationConfigurationInformationResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetClassificationConfigurationInformation($mixed = null) {
		$validParameters = array(
			"(GetClassificationConfigurationInformation)",
			"(GetClassificationConfigurationInformation)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetClassificationConfigurationInformation", $args);
	}


	/**
	 * Service Call: GetInventoryItemAttributeList
	 * Parameter options:
	 * (GetInventoryItemAttributeList) parameters
	 * (GetInventoryItemAttributeList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetInventoryItemAttributeListResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetInventoryItemAttributeList($mixed = null) {
		$validParameters = array(
			"(GetInventoryItemAttributeList)",
			"(GetInventoryItemAttributeList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetInventoryItemAttributeList", $args);
	}


	/**
	 * Service Call: GetInventoryItemVariationInfo
	 * Parameter options:
	 * (GetInventoryItemVariationInfo) parameters
	 * (GetInventoryItemVariationInfo) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetInventoryItemVariationInfoResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetInventoryItemVariationInfo($mixed = null) {
		$validParameters = array(
			"(GetInventoryItemVariationInfo)",
			"(GetInventoryItemVariationInfo)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetInventoryItemVariationInfo", $args);
	}


	/**
	 * Service Call: GetInventoryItemStoreInfo
	 * Parameter options:
	 * (GetInventoryItemStoreInfo) parameters
	 * (GetInventoryItemStoreInfo) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetInventoryItemStoreInfoResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetInventoryItemStoreInfo($mixed = null) {
		$validParameters = array(
			"(GetInventoryItemStoreInfo)",
			"(GetInventoryItemStoreInfo)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetInventoryItemStoreInfo", $args);
	}


	/**
	 * Service Call: GetInventoryItemImageList
	 * Parameter options:
	 * (GetInventoryItemImageList) parameters
	 * (GetInventoryItemImageList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetInventoryItemImageListResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetInventoryItemImageList($mixed = null) {
		$validParameters = array(
			"(GetInventoryItemImageList)",
			"(GetInventoryItemImageList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetInventoryItemImageList", $args);
	}


	/**
	 * Service Call: GetInventoryQuantity
	 * Parameter options:
	 * (GetInventoryQuantity) parameters
	 * (GetInventoryQuantity) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetInventoryQuantityResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetInventoryQuantity($mixed = null) {
		$validParameters = array(
			"(GetInventoryQuantity)",
			"(GetInventoryQuantity)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetInventoryQuantity", $args);
	}


	/**
	 * Service Call: GetInventoryQuantityList
	 * Parameter options:
	 * (GetInventoryQuantityList) parameters
	 * (GetInventoryQuantityList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetInventoryQuantityListResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetInventoryQuantityList($mixed = null) {
		$validParameters = array(
			"(GetInventoryQuantityList)",
			"(GetInventoryQuantityList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetInventoryQuantityList", $args);
	}


	/**
	 * Service Call: DeleteInventoryItem
	 * Parameter options:
	 * (DeleteInventoryItem) parameters
	 * (DeleteInventoryItem) parameters
	 * @param mixed,... See function description for parameter options
	 * @return DeleteInventoryItemResponse
	 * @throws Exception invalid function signature message
	 */
	public function DeleteInventoryItem($mixed = null) {
		$validParameters = array(
			"(DeleteInventoryItem)",
			"(DeleteInventoryItem)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("DeleteInventoryItem", $args);
	}


	/**
	 * Service Call: SynchInventoryItem
	 * Parameter options:
	 * (SynchInventoryItem) parameters
	 * (SynchInventoryItem) parameters
	 * @param mixed,... See function description for parameter options
	 * @return SynchInventoryItemResponse
	 * @throws Exception invalid function signature message
	 */
	public function SynchInventoryItem($mixed = null) {
		$validParameters = array(
			"(SynchInventoryItem)",
			"(SynchInventoryItem)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("SynchInventoryItem", $args);
	}


	/**
	 * Service Call: SynchInventoryItemList
	 * Parameter options:
	 * (SynchInventoryItemList) parameters
	 * (SynchInventoryItemList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return SynchInventoryItemListResponse
	 * @throws Exception invalid function signature message
	 */
	public function SynchInventoryItemList($mixed = null) {
		$validParameters = array(
			"(SynchInventoryItemList)",
			"(SynchInventoryItemList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("SynchInventoryItemList", $args);
	}


	/**
	 * Service Call: UpdateInventoryItemQuantityAndPrice
	 * Parameter options:
	 * (UpdateInventoryItemQuantityAndPrice) parameters
	 * (UpdateInventoryItemQuantityAndPrice) parameters
	 * @param mixed,... See function description for parameter options
	 * @return UpdateInventoryItemQuantityAndPriceResponse
	 * @throws Exception invalid function signature message
	 */
	public function UpdateInventoryItemQuantityAndPrice($mixed = null) {
		$validParameters = array(
			"(UpdateInventoryItemQuantityAndPrice)",
			"(UpdateInventoryItemQuantityAndPrice)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("UpdateInventoryItemQuantityAndPrice", $args);
	}


	/**
	 * Service Call: UpdateInventoryItemQuantityAndPriceList
	 * Parameter options:
	 * (UpdateInventoryItemQuantityAndPriceList) parameters
	 * (UpdateInventoryItemQuantityAndPriceList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return UpdateInventoryItemQuantityAndPriceListResponse
	 * @throws Exception invalid function signature message
	 */
	public function UpdateInventoryItemQuantityAndPriceList($mixed = null) {
		$validParameters = array(
			"(UpdateInventoryItemQuantityAndPriceList)",
			"(UpdateInventoryItemQuantityAndPriceList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("UpdateInventoryItemQuantityAndPriceList", $args);
	}


	/**
	 * Service Call: AssignLabelListToInventoryItemList
	 * Parameter options:
	 * (AssignLabelListToInventoryItemList) parameters
	 * (AssignLabelListToInventoryItemList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return AssignLabelListToInventoryItemListResponse
	 * @throws Exception invalid function signature message
	 */
	public function AssignLabelListToInventoryItemList($mixed = null) {
		$validParameters = array(
			"(AssignLabelListToInventoryItemList)",
			"(AssignLabelListToInventoryItemList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("AssignLabelListToInventoryItemList", $args);
	}


	/**
	 * Service Call: RemoveLabelListFromInventoryItemList
	 * Parameter options:
	 * (RemoveLabelListFromInventoryItemList) parameters
	 * (RemoveLabelListFromInventoryItemList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return RemoveLabelListFromInventoryItemListResponse
	 * @throws Exception invalid function signature message
	 */
	public function RemoveLabelListFromInventoryItemList($mixed = null) {
		$validParameters = array(
			"(RemoveLabelListFromInventoryItemList)",
			"(RemoveLabelListFromInventoryItemList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("RemoveLabelListFromInventoryItemList", $args);
	}


	/**
	 * Service Call: AddUpsellRelationship
	 * Parameter options:
	 * (AddUpsellRelationship) parameters
	 * (AddUpsellRelationship) parameters
	 * @param mixed,... See function description for parameter options
	 * @return AddUpsellRelationshipResponse
	 * @throws Exception invalid function signature message
	 */
	public function AddUpsellRelationship($mixed = null) {
		$validParameters = array(
			"(AddUpsellRelationship)",
			"(AddUpsellRelationship)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("AddUpsellRelationship", $args);
	}


	/**
	 * Service Call: GetUpsellRelationship
	 * Parameter options:
	 * (GetUpsellRelationship) parameters
	 * (GetUpsellRelationship) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetUpsellRelationshipResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetUpsellRelationship($mixed = null) {
		$validParameters = array(
			"(GetUpsellRelationship)",
			"(GetUpsellRelationship)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetUpsellRelationship", $args);
	}


	/**
	 * Service Call: DeleteUpsellRelationship
	 * Parameter options:
	 * (DeleteUpsellRelationship) parameters
	 * (DeleteUpsellRelationship) parameters
	 * @param mixed,... See function description for parameter options
	 * @return DeleteUpsellRelationshipResponse
	 * @throws Exception invalid function signature message
	 */
	public function DeleteUpsellRelationship($mixed = null) {
		$validParameters = array(
			"(DeleteUpsellRelationship)",
			"(DeleteUpsellRelationship)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("DeleteUpsellRelationship", $args);
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