<?php
class DoesSkuExist {
  public $accountID; // string
  public $sku; // string
}

class DoesSkuExistResponse {
  public $DoesSkuExistResult; // APIResultOfBoolean
}

class APIResultOfBoolean {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // boolean
}

class ResultStatus {
  const Success = 'Success';
  const Failure = 'Failure';
}

class APICredentials {
  public $DeveloperKey; // string
  public $Password; // string
}

class DoesSkuExistList {
  public $accountID; // string
  public $skuList; // ArrayOfString
}

class DoesSkuExistListResponse {
  public $DoesSkuExistListResult; // APIResultOfArrayOfDoesSkuExistResponse
}

class APIResultOfArrayOfDoesSkuExistResponse {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // ArrayOfDoesSkuExistResponse
}

class DoesSkuExistResponse {
  public $Sku; // string
  public $Result; // boolean
  public $ErrorMessage; // string
}

class GetInventoryItemList {
  public $accountID; // string
  public $skuList; // ArrayOfString
}

class GetInventoryItemListResponse {
  public $GetInventoryItemListResult; // APIResultOfArrayOfInventoryItemResponse
}

class APIResultOfArrayOfInventoryItemResponse {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // ArrayOfInventoryItemResponse
}

class InventoryItemResponse {
  public $Sku; // string
  public $Title; // string
  public $Subtitle; // string
  public $ShortDescription; // string
  public $Description; // string
  public $Weight; // decimal
  public $SupplierCode; // string
  public $WarehouseLocation; // string
  public $TaxProductCode; // string
  public $FlagStyle; // string
  public $FlagDescription; // string
  public $IsBlocked; // boolean
  public $BlockComment; // string
  public $ASIN; // string
  public $ISBN; // string
  public $UPC; // string
  public $MPN; // string
  public $EAN; // string
  public $Manufacturer; // string
  public $Brand; // string
  public $Condition; // string
  public $Warranty; // string
  public $ProductMargin; // decimal
  public $SupplierPO; // string
  public $HarmonizedCode; // string
  public $Height; // decimal
  public $Length; // decimal
  public $Width; // decimal
  public $Classification; // string
  public $DistributionCenterList; // ArrayOfDistributionCenterInfoResponse
  public $Quantity; // QuantityInfoResponse
  public $PriceInfo; // PriceInfo
  public $AttributeList; // ArrayOfAttributeInfo
  public $VariationInfo; // VariationInfo
  public $StoreInfo; // StoreInfo
  public $ImageList; // ArrayOfImageInfoResponse
  public $MetaDescription; // string
}

class DistributionCenterInfoResponse {
  public $DistributionCenterCode; // string
  public $AvailableQuantity; // int
  public $OpenAllocatedQuantity; // int
  public $OpenAllocatedPooledQuantity; // int
  public $WarehouseLocation; // string
  public $ReceivedInInventory; // dateTime
  public $ShippingRateList; // ArrayOfShippingRateInfo
}

class ShippingRateInfo {
  public $DestinationZoneName; // string
  public $CarrierCode; // string
  public $ClassCode; // string
  public $FirstItemRate; // decimal
  public $AdditionalItemRate; // decimal
  public $FirstItemHandlingRate; // decimal
  public $AdditionalItemHandlingRate; // decimal
  public $FreeShippingIfBuyItNow; // boolean
  public $FirstItemRateAttribute; // string
  public $FirstItemHandlingRateAttribute; // string
  public $AdditionalItemRateAttribute; // string
  public $AdditionalItemHandlingRateAttribute; // string
}

class QuantityInfoResponse {
  public $Available; // int
  public $OpenAllocated; // int
  public $OpenUnallocated; // int
  public $PendingCheckout; // int
  public $PendingPayment; // int
  public $PendingShipment; // int
  public $Total; // int
  public $OpenAllocatedPooled; // int
  public $OpenUnallocatedPooled; // int
  public $PendingCheckoutPooled; // int
  public $PendingPaymentPooled; // int
  public $PendingShipmentPooled; // int
  public $TotalPooled; // int
}

class PriceInfo {
  public $Cost; // decimal
  public $RetailPrice; // decimal
  public $StartingPrice; // decimal
  public $ReservePrice; // decimal
  public $TakeItPrice; // decimal
  public $SecondChanceOfferPrice; // decimal
  public $StorePrice; // decimal
}

class AttributeInfo {
  public $Name; // string
  public $Value; // string
}

class VariationInfo {
  public $IsInRelationship; // boolean
  public $RelationshipName; // string
  public $IsParent; // boolean
  public $ParentSku; // string
}

class StoreInfo {
  public $DisplayInStore; // boolean
  public $Title; // string
  public $Description; // string
  public $CategoryID; // int
}

class ImageInfoResponse {
  public $PlacementName; // string
  public $FolderName; // string
  public $Url; // string
  public $ImageThumbList; // ArrayOfImageThumbInfo
}

class ImageThumbInfo {
  public $TypeName; // string
  public $Url; // string
}

class GetInventoryItemListWithFullDetail {
  public $accountID; // string
  public $skuList; // ArrayOfString
}

class GetInventoryItemListWithFullDetailResponse {
  public $GetInventoryItemListWithFullDetailResult; // APIResultOfArrayOfInventoryItemResponse
}

class GetFilteredInventoryItemList {
  public $accountID; // string
  public $itemCriteria; // InventoryItemCriteria
  public $detailLevel; // InventoryItemDetailLevel
  public $sortField; // string
  public $sortDirection; // string
}

class InventoryItemCriteria {
  public $DateRangeField; // string
  public $DateRangeStartGMT; // dateTime
  public $DateRangeEndGMT; // dateTime
  public $PartialSku; // string
  public $SkuStartsWith; // string
  public $SkuEndsWith; // string
  public $ClassificationName; // string
  public $LabelName; // string
  public $QuantityCheckField; // string
  public $QuantityCheckType; // string
  public $QuantityCheckValue; // int
  public $PageNumber; // int
  public $PageSize; // int
}

class InventoryItemDetailLevel {
  public $IncludeQuantityInfo; // boolean
  public $IncludePriceInfo; // boolean
  public $IncludeClassificationInfo; // boolean
}

class GetFilteredInventoryItemListResponse {
  public $GetFilteredInventoryItemListResult; // APIResultOfArrayOfInventoryItemResponse
}

class GetFilteredSkuList {
  public $accountID; // string
  public $itemCriteria; // InventoryItemCriteria
  public $sortField; // string
  public $sortDirection; // string
}

class GetFilteredSkuListResponse {
  public $GetFilteredSkuListResult; // APIResultOfArrayOfString
}

class APIResultOfArrayOfString {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // ArrayOfString
}

class GetInventoryItemShippingInfo {
  public $accountID; // string
  public $sku; // string
}

class GetInventoryItemShippingInfoResponse {
  public $GetInventoryItemShippingInfoResult; // APIResultOfArrayOfDistributionCenterInfoResponse
}

class APIResultOfArrayOfDistributionCenterInfoResponse {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // ArrayOfDistributionCenterInfoResponse
}

class GetInventoryItemQuantityInfo {
  public $accountID; // string
  public $sku; // string
}

class GetInventoryItemQuantityInfoResponse {
  public $GetInventoryItemQuantityInfoResult; // APIResultOfQuantityInfoResponse
}

class APIResultOfQuantityInfoResponse {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // QuantityInfoResponse
}

class GetClassificationConfigurationInformation {
  public $accountID; // string
}

class GetClassificationConfigurationInformationResponse {
  public $GetClassificationConfigurationInformationResult; // APIResultOfArrayOfClassificationConfigurationInformation
}

class APIResultOfArrayOfClassificationConfigurationInformation {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // ArrayOfClassificationConfigurationInformation
}

class ClassificationConfigurationInformation {
  public $Name; // string
  public $ClassificationConfigurationInformationAttributeArray; // ArrayOfClassificationConfigurationInformationAttribute
}

class ClassificationConfigurationInformationAttribute {
  public $Name; // string
  public $DefaultValue; // string
  public $ListOfChoices; // string
}

class GetInventoryItemAttributeList {
  public $accountID; // string
  public $sku; // string
}

class GetInventoryItemAttributeListResponse {
  public $GetInventoryItemAttributeListResult; // APIResultOfArrayOfAttributeInfo
}

class APIResultOfArrayOfAttributeInfo {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // ArrayOfAttributeInfo
}

class GetInventoryItemVariationInfo {
  public $accountID; // string
  public $sku; // string
}

class GetInventoryItemVariationInfoResponse {
  public $GetInventoryItemVariationInfoResult; // APIResultOfVariationInfo
}

class APIResultOfVariationInfo {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // VariationInfo
}

class GetInventoryItemStoreInfo {
  public $accountID; // string
  public $sku; // string
}

class GetInventoryItemStoreInfoResponse {
  public $GetInventoryItemStoreInfoResult; // APIResultOfStoreInfo
}

class APIResultOfStoreInfo {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // StoreInfo
}

class GetInventoryItemImageList {
  public $accountID; // string
  public $sku; // string
}

class GetInventoryItemImageListResponse {
  public $GetInventoryItemImageListResult; // APIResultOfArrayOfImageInfoResponse
}

class APIResultOfArrayOfImageInfoResponse {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // ArrayOfImageInfoResponse
}

class GetInventoryQuantity {
  public $accountID; // string
  public $sku; // string
}

class GetInventoryQuantityResponse {
  public $GetInventoryQuantityResult; // APIResultOfInt32
}

class APIResultOfInt32 {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // int
}

class GetInventoryQuantityList {
  public $accountID; // string
  public $skuList; // ArrayOfString
}

class GetInventoryQuantityListResponse {
  public $GetInventoryQuantityListResult; // APIResultOfArrayOfInventoryQuantityResponse
}

class APIResultOfArrayOfInventoryQuantityResponse {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // ArrayOfInventoryQuantityResponse
}

class InventoryQuantityResponse {
  public $SKU; // string
  public $Quantity; // int
  public $MessageCode; // int
  public $Message; // string
}

class DeleteInventoryItem {
  public $accountID; // string
  public $sku; // string
}

class DeleteInventoryItemResponse {
  public $DeleteInventoryItemResult; // APIResultOfBoolean
}

class SynchInventoryItem {
  public $accountID; // string
  public $item; // InventoryItemSubmit
}

class InventoryItemSubmit {
  public $Sku; // string
  public $Title; // string
  public $Subtitle; // string
  public $ShortDescription; // string
  public $Description; // string
  public $Weight; // decimal
  public $SupplierCode; // string
  public $WarehouseLocation; // string
  public $TaxProductCode; // string
  public $FlagStyle; // string
  public $FlagDescription; // string
  public $IsBlocked; // boolean
  public $BlockComment; // string
  public $ASIN; // string
  public $ISBN; // string
  public $UPC; // string
  public $MPN; // string
  public $EAN; // string
  public $Manufacturer; // string
  public $Brand; // string
  public $Condition; // string
  public $Warranty; // string
  public $ProductMargin; // decimal
  public $SupplierPO; // string
  public $HarmonizedCode; // string
  public $Height; // decimal
  public $Length; // decimal
  public $Width; // decimal
  public $Classification; // string
  public $DistributionCenterList; // ArrayOfDistributionCenterInfoSubmit
  public $PriceInfo; // PriceInfo
  public $AttributeList; // ArrayOfAttributeInfo
  public $VariationInfo; // VariationInfo
  public $StoreInfo; // StoreInfo
  public $ImageList; // ArrayOfImageInfoSubmit
  public $LabelList; // ArrayOfString
  public $MetaDescription; // string
}

class DistributionCenterInfoSubmit {
  public $DistributionCenterCode; // string
  public $Quantity; // int
  public $QuantityUpdateType; // string
  public $WarehouseLocation; // string
  public $ReceivedInInventory; // dateTime
  public $ShippingRateList; // ArrayOfShippingRateInfo
}

class ImageInfoSubmit {
  public $PlacementName; // string
  public $FolderName; // string
  public $FilenameOrUrl; // string
}

class SynchInventoryItemResponse {
  public $SynchInventoryItemResult; // APIResultOfBoolean
}

class SynchInventoryItemList {
  public $accountID; // string
  public $itemList; // ArrayOfInventoryItemSubmit
}

class SynchInventoryItemListResponse {
  public $SynchInventoryItemListResult; // APIResultOfArrayOfSynchInventoryItemResponse
}

class APIResultOfArrayOfSynchInventoryItemResponse {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // ArrayOfSynchInventoryItemResponse
}

class SynchInventoryItemResponse {
  public $Sku; // string
  public $Result; // boolean
  public $ErrorMessage; // string
}

class UpdateInventoryItemQuantityAndPrice {
  public $accountID; // string
  public $itemQuantityAndPrice; // InventoryItemQuantityAndPrice
}

class InventoryItemQuantityAndPrice {
  public $Sku; // string
  public $DistributionCenterCode; // string
  public $Quantity; // int
  public $UpdateType; // string
  public $PriceInfo; // PriceInfo
}

class UpdateInventoryItemQuantityAndPriceResponse {
  public $UpdateInventoryItemQuantityAndPriceResult; // APIResultOfBoolean
}

class UpdateInventoryItemQuantityAndPriceList {
  public $accountID; // string
  public $itemQuantityAndPriceList; // ArrayOfInventoryItemQuantityAndPrice
}

class UpdateInventoryItemQuantityAndPriceListResponse {
  public $UpdateInventoryItemQuantityAndPriceListResult; // APIResultOfArrayOfUpdateInventoryItemResponse
}

class APIResultOfArrayOfUpdateInventoryItemResponse {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // ArrayOfUpdateInventoryItemResponse
}

class UpdateInventoryItemResponse {
  public $Sku; // string
  public $Result; // boolean
  public $ErrorMessage; // string
}

class AssignLabelListToInventoryItemList {
  public $accountID; // string
  public $labelList; // ArrayOfString
  public $createLabelIfNotExist; // boolean
  public $skuList; // ArrayOfString
  public $assignReasonDesc; // string
}

class AssignLabelListToInventoryItemListResponse {
  public $AssignLabelListToInventoryItemListResult; // APIResultOfBoolean
}

class RemoveLabelListFromInventoryItemList {
  public $accountID; // string
  public $labelList; // ArrayOfString
  public $skuList; // ArrayOfString
  public $removeReasonDesc; // string
}

class RemoveLabelListFromInventoryItemListResponse {
  public $RemoveLabelListFromInventoryItemListResult; // APIResultOfBoolean
}

class AddUpsellRelationship {
  public $accountID; // string
  public $upsellInfoList; // ArrayOfInventoryUpsellInfoSubmit
}

class InventoryUpsellInfoSubmit {
  public $ParentSKU; // string
  public $ChildItemList; // ArrayOfInventoryUpsellChildInfo
}

class InventoryUpsellChildInfo {
  public $SKU; // string
  public $Quantity; // int
  public $SalePrice; // decimal
}

class AddUpsellRelationshipResponse {
  public $AddUpsellRelationshipResult; // APIResultOfArrayOfArrayOfBoolean
}

class APIResultOfArrayOfArrayOfBoolean {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // ArrayOfArrayOfBoolean
}

class GetUpsellRelationship {
  public $accountID; // string
  public $parentSKUList; // ArrayOfString
}

class GetUpsellRelationshipResponse {
  public $GetUpsellRelationshipResult; // APIResultOfArrayOfInventoryUpsellInfoResponse
}

class APIResultOfArrayOfInventoryUpsellInfoResponse {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // ArrayOfInventoryUpsellInfoResponse
}

class InventoryUpsellInfoResponse {
  public $ParentSKU; // string
  public $ChildItemList; // ArrayOfInventoryUpsellChildInfo
}

class DeleteUpsellRelationship {
  public $accountID; // string
  public $parentSKU; // string
  public $childSKUList; // ArrayOfString
}

class DeleteUpsellRelationshipResponse {
  public $DeleteUpsellRelationshipResult; // APIResultOfArrayOfBoolean
}

class APIResultOfArrayOfBoolean {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // ArrayOfBoolean
}

class Ping {
}

class PingResponse {
  public $PingResult; // APIResultOfString
}

class APIResultOfString {
  public $Status; // ResultStatus
  public $MessageCode; // int
  public $Message; // string
  public $Data; // string
  public $ResultData; // string
}


/**
 * InventoryService class
 * 
 *  
 * 
 * @author    {author}
 * @copyright {copyright}
 * @package   {package}
 */
class InventoryService extends SoapClient {

  private static $classmap = array(
                                    'DoesSkuExist' => 'DoesSkuExist',
                                    'DoesSkuExistResponse' => 'DoesSkuExistResponse',
                                    'APIResultOfBoolean' => 'APIResultOfBoolean',
                                    'ResultStatus' => 'ResultStatus',
                                    'APICredentials' => 'APICredentials',
                                    'DoesSkuExistList' => 'DoesSkuExistList',
                                    'DoesSkuExistListResponse' => 'DoesSkuExistListResponse',
                                    'APIResultOfArrayOfDoesSkuExistResponse' => 'APIResultOfArrayOfDoesSkuExistResponse',
                                    'DoesSkuExistResponse' => 'DoesSkuExistResponse',
                                    'GetInventoryItemList' => 'GetInventoryItemList',
                                    'GetInventoryItemListResponse' => 'GetInventoryItemListResponse',
                                    'APIResultOfArrayOfInventoryItemResponse' => 'APIResultOfArrayOfInventoryItemResponse',
                                    'InventoryItemResponse' => 'InventoryItemResponse',
                                    'DistributionCenterInfoResponse' => 'DistributionCenterInfoResponse',
                                    'ShippingRateInfo' => 'ShippingRateInfo',
                                    'QuantityInfoResponse' => 'QuantityInfoResponse',
                                    'PriceInfo' => 'PriceInfo',
                                    'AttributeInfo' => 'AttributeInfo',
                                    'VariationInfo' => 'VariationInfo',
                                    'StoreInfo' => 'StoreInfo',
                                    'ImageInfoResponse' => 'ImageInfoResponse',
                                    'ImageThumbInfo' => 'ImageThumbInfo',
                                    'GetInventoryItemListWithFullDetail' => 'GetInventoryItemListWithFullDetail',
                                    'GetInventoryItemListWithFullDetailResponse' => 'GetInventoryItemListWithFullDetailResponse',
                                    'GetFilteredInventoryItemList' => 'GetFilteredInventoryItemList',
                                    'InventoryItemCriteria' => 'InventoryItemCriteria',
                                    'InventoryItemDetailLevel' => 'InventoryItemDetailLevel',
                                    'GetFilteredInventoryItemListResponse' => 'GetFilteredInventoryItemListResponse',
                                    'GetFilteredSkuList' => 'GetFilteredSkuList',
                                    'GetFilteredSkuListResponse' => 'GetFilteredSkuListResponse',
                                    'APIResultOfArrayOfString' => 'APIResultOfArrayOfString',
                                    'GetInventoryItemShippingInfo' => 'GetInventoryItemShippingInfo',
                                    'GetInventoryItemShippingInfoResponse' => 'GetInventoryItemShippingInfoResponse',
                                    'APIResultOfArrayOfDistributionCenterInfoResponse' => 'APIResultOfArrayOfDistributionCenterInfoResponse',
                                    'GetInventoryItemQuantityInfo' => 'GetInventoryItemQuantityInfo',
                                    'GetInventoryItemQuantityInfoResponse' => 'GetInventoryItemQuantityInfoResponse',
                                    'APIResultOfQuantityInfoResponse' => 'APIResultOfQuantityInfoResponse',
                                    'GetClassificationConfigurationInformation' => 'GetClassificationConfigurationInformation',
                                    'GetClassificationConfigurationInformationResponse' => 'GetClassificationConfigurationInformationResponse',
                                    'APIResultOfArrayOfClassificationConfigurationInformation' => 'APIResultOfArrayOfClassificationConfigurationInformation',
                                    'ClassificationConfigurationInformation' => 'ClassificationConfigurationInformation',
                                    'ClassificationConfigurationInformationAttribute' => 'ClassificationConfigurationInformationAttribute',
                                    'GetInventoryItemAttributeList' => 'GetInventoryItemAttributeList',
                                    'GetInventoryItemAttributeListResponse' => 'GetInventoryItemAttributeListResponse',
                                    'APIResultOfArrayOfAttributeInfo' => 'APIResultOfArrayOfAttributeInfo',
                                    'GetInventoryItemVariationInfo' => 'GetInventoryItemVariationInfo',
                                    'GetInventoryItemVariationInfoResponse' => 'GetInventoryItemVariationInfoResponse',
                                    'APIResultOfVariationInfo' => 'APIResultOfVariationInfo',
                                    'GetInventoryItemStoreInfo' => 'GetInventoryItemStoreInfo',
                                    'GetInventoryItemStoreInfoResponse' => 'GetInventoryItemStoreInfoResponse',
                                    'APIResultOfStoreInfo' => 'APIResultOfStoreInfo',
                                    'GetInventoryItemImageList' => 'GetInventoryItemImageList',
                                    'GetInventoryItemImageListResponse' => 'GetInventoryItemImageListResponse',
                                    'APIResultOfArrayOfImageInfoResponse' => 'APIResultOfArrayOfImageInfoResponse',
                                    'GetInventoryQuantity' => 'GetInventoryQuantity',
                                    'GetInventoryQuantityResponse' => 'GetInventoryQuantityResponse',
                                    'APIResultOfInt32' => 'APIResultOfInt32',
                                    'GetInventoryQuantityList' => 'GetInventoryQuantityList',
                                    'GetInventoryQuantityListResponse' => 'GetInventoryQuantityListResponse',
                                    'APIResultOfArrayOfInventoryQuantityResponse' => 'APIResultOfArrayOfInventoryQuantityResponse',
                                    'InventoryQuantityResponse' => 'InventoryQuantityResponse',
                                    'DeleteInventoryItem' => 'DeleteInventoryItem',
                                    'DeleteInventoryItemResponse' => 'DeleteInventoryItemResponse',
                                    'SynchInventoryItem' => 'SynchInventoryItem',
                                    'InventoryItemSubmit' => 'InventoryItemSubmit',
                                    'DistributionCenterInfoSubmit' => 'DistributionCenterInfoSubmit',
                                    'ImageInfoSubmit' => 'ImageInfoSubmit',
                                    'SynchInventoryItemResponse' => 'SynchInventoryItemResponse',
                                    'SynchInventoryItemList' => 'SynchInventoryItemList',
                                    'SynchInventoryItemListResponse' => 'SynchInventoryItemListResponse',
                                    'APIResultOfArrayOfSynchInventoryItemResponse' => 'APIResultOfArrayOfSynchInventoryItemResponse',
                                    'SynchInventoryItemResponse' => 'SynchInventoryItemResponse',
                                    'UpdateInventoryItemQuantityAndPrice' => 'UpdateInventoryItemQuantityAndPrice',
                                    'InventoryItemQuantityAndPrice' => 'InventoryItemQuantityAndPrice',
                                    'UpdateInventoryItemQuantityAndPriceResponse' => 'UpdateInventoryItemQuantityAndPriceResponse',
                                    'UpdateInventoryItemQuantityAndPriceList' => 'UpdateInventoryItemQuantityAndPriceList',
                                    'UpdateInventoryItemQuantityAndPriceListResponse' => 'UpdateInventoryItemQuantityAndPriceListResponse',
                                    'APIResultOfArrayOfUpdateInventoryItemResponse' => 'APIResultOfArrayOfUpdateInventoryItemResponse',
                                    'UpdateInventoryItemResponse' => 'UpdateInventoryItemResponse',
                                    'AssignLabelListToInventoryItemList' => 'AssignLabelListToInventoryItemList',
                                    'AssignLabelListToInventoryItemListResponse' => 'AssignLabelListToInventoryItemListResponse',
                                    'RemoveLabelListFromInventoryItemList' => 'RemoveLabelListFromInventoryItemList',
                                    'RemoveLabelListFromInventoryItemListResponse' => 'RemoveLabelListFromInventoryItemListResponse',
                                    'AddUpsellRelationship' => 'AddUpsellRelationship',
                                    'InventoryUpsellInfoSubmit' => 'InventoryUpsellInfoSubmit',
                                    'InventoryUpsellChildInfo' => 'InventoryUpsellChildInfo',
                                    'AddUpsellRelationshipResponse' => 'AddUpsellRelationshipResponse',
                                    'APIResultOfArrayOfArrayOfBoolean' => 'APIResultOfArrayOfArrayOfBoolean',
                                    'GetUpsellRelationship' => 'GetUpsellRelationship',
                                    'GetUpsellRelationshipResponse' => 'GetUpsellRelationshipResponse',
                                    'APIResultOfArrayOfInventoryUpsellInfoResponse' => 'APIResultOfArrayOfInventoryUpsellInfoResponse',
                                    'InventoryUpsellInfoResponse' => 'InventoryUpsellInfoResponse',
                                    'DeleteUpsellRelationship' => 'DeleteUpsellRelationship',
                                    'DeleteUpsellRelationshipResponse' => 'DeleteUpsellRelationshipResponse',
                                    'APIResultOfArrayOfBoolean' => 'APIResultOfArrayOfBoolean',
                                    'Ping' => 'Ping',
                                    'PingResponse' => 'PingResponse',
                                    'APIResultOfString' => 'APIResultOfString',
                                   );

  public function InventoryService($wsdl = "https://api.channeladvisor.com/ChannelAdvisorAPI/v7/InventoryService.asmx?WSDL", $options = array()) {
    foreach(self::$classmap as $key => $value) {
      if(!isset($options['classmap'][$key])) {
        $options['classmap'][$key] = $value;
      }
    }
    parent::__construct($wsdl, $options);
  }

  /**
   *  
   *
   * @param DoesSkuExist $parameters
   * @return DoesSkuExistResponse
   */
  public function DoesSkuExist(DoesSkuExist $parameters) {
    return $this->__soapCall('DoesSkuExist', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param DoesSkuExistList $parameters
   * @return DoesSkuExistListResponse
   */
  public function DoesSkuExistList(DoesSkuExistList $parameters) {
    return $this->__soapCall('DoesSkuExistList', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetInventoryItemList $parameters
   * @return GetInventoryItemListResponse
   */
  public function GetInventoryItemList(GetInventoryItemList $parameters) {
    return $this->__soapCall('GetInventoryItemList', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetInventoryItemListWithFullDetail $parameters
   * @return GetInventoryItemListWithFullDetailResponse
   */
  public function GetInventoryItemListWithFullDetail(GetInventoryItemListWithFullDetail $parameters) {
    return $this->__soapCall('GetInventoryItemListWithFullDetail', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetFilteredInventoryItemList $parameters
   * @return GetFilteredInventoryItemListResponse
   */
  public function GetFilteredInventoryItemList(GetFilteredInventoryItemList $parameters) {
    return $this->__soapCall('GetFilteredInventoryItemList', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetFilteredSkuList $parameters
   * @return GetFilteredSkuListResponse
   */
  public function GetFilteredSkuList(GetFilteredSkuList $parameters) {
    return $this->__soapCall('GetFilteredSkuList', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetInventoryItemShippingInfo $parameters
   * @return GetInventoryItemShippingInfoResponse
   */
  public function GetInventoryItemShippingInfo(GetInventoryItemShippingInfo $parameters) {
    return $this->__soapCall('GetInventoryItemShippingInfo', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetInventoryItemQuantityInfo $parameters
   * @return GetInventoryItemQuantityInfoResponse
   */
  public function GetInventoryItemQuantityInfo(GetInventoryItemQuantityInfo $parameters) {
    return $this->__soapCall('GetInventoryItemQuantityInfo', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetClassificationConfigurationInformation $parameters
   * @return GetClassificationConfigurationInformationResponse
   */
  public function GetClassificationConfigurationInformation(GetClassificationConfigurationInformation $parameters) {
    return $this->__soapCall('GetClassificationConfigurationInformation', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetInventoryItemAttributeList $parameters
   * @return GetInventoryItemAttributeListResponse
   */
  public function GetInventoryItemAttributeList(GetInventoryItemAttributeList $parameters) {
    return $this->__soapCall('GetInventoryItemAttributeList', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetInventoryItemVariationInfo $parameters
   * @return GetInventoryItemVariationInfoResponse
   */
  public function GetInventoryItemVariationInfo(GetInventoryItemVariationInfo $parameters) {
    return $this->__soapCall('GetInventoryItemVariationInfo', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetInventoryItemStoreInfo $parameters
   * @return GetInventoryItemStoreInfoResponse
   */
  public function GetInventoryItemStoreInfo(GetInventoryItemStoreInfo $parameters) {
    return $this->__soapCall('GetInventoryItemStoreInfo', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetInventoryItemImageList $parameters
   * @return GetInventoryItemImageListResponse
   */
  public function GetInventoryItemImageList(GetInventoryItemImageList $parameters) {
    return $this->__soapCall('GetInventoryItemImageList', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetInventoryQuantity $parameters
   * @return GetInventoryQuantityResponse
   */
  public function GetInventoryQuantity(GetInventoryQuantity $parameters) {
    return $this->__soapCall('GetInventoryQuantity', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetInventoryQuantityList $parameters
   * @return GetInventoryQuantityListResponse
   */
  public function GetInventoryQuantityList(GetInventoryQuantityList $parameters) {
    return $this->__soapCall('GetInventoryQuantityList', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param DeleteInventoryItem $parameters
   * @return DeleteInventoryItemResponse
   */
  public function DeleteInventoryItem(DeleteInventoryItem $parameters) {
    return $this->__soapCall('DeleteInventoryItem', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param SynchInventoryItem $parameters
   * @return SynchInventoryItemResponse
   */
  public function SynchInventoryItem(SynchInventoryItem $parameters) {
    return $this->__soapCall('SynchInventoryItem', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param SynchInventoryItemList $parameters
   * @return SynchInventoryItemListResponse
   */
  public function SynchInventoryItemList(SynchInventoryItemList $parameters) {
    return $this->__soapCall('SynchInventoryItemList', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param UpdateInventoryItemQuantityAndPrice $parameters
   * @return UpdateInventoryItemQuantityAndPriceResponse
   */
  public function UpdateInventoryItemQuantityAndPrice(UpdateInventoryItemQuantityAndPrice $parameters) {
    return $this->__soapCall('UpdateInventoryItemQuantityAndPrice', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param UpdateInventoryItemQuantityAndPriceList $parameters
   * @return UpdateInventoryItemQuantityAndPriceListResponse
   */
  public function UpdateInventoryItemQuantityAndPriceList(UpdateInventoryItemQuantityAndPriceList $parameters) {
    return $this->__soapCall('UpdateInventoryItemQuantityAndPriceList', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param AssignLabelListToInventoryItemList $parameters
   * @return AssignLabelListToInventoryItemListResponse
   */
  public function AssignLabelListToInventoryItemList(AssignLabelListToInventoryItemList $parameters) {
    return $this->__soapCall('AssignLabelListToInventoryItemList', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param RemoveLabelListFromInventoryItemList $parameters
   * @return RemoveLabelListFromInventoryItemListResponse
   */
  public function RemoveLabelListFromInventoryItemList(RemoveLabelListFromInventoryItemList $parameters) {
    return $this->__soapCall('RemoveLabelListFromInventoryItemList', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param AddUpsellRelationship $parameters
   * @return AddUpsellRelationshipResponse
   */
  public function AddUpsellRelationship(AddUpsellRelationship $parameters) {
    return $this->__soapCall('AddUpsellRelationship', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param GetUpsellRelationship $parameters
   * @return GetUpsellRelationshipResponse
   */
  public function GetUpsellRelationship(GetUpsellRelationship $parameters) {
    return $this->__soapCall('GetUpsellRelationship', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param DeleteUpsellRelationship $parameters
   * @return DeleteUpsellRelationshipResponse
   */
  public function DeleteUpsellRelationship(DeleteUpsellRelationship $parameters) {
    return $this->__soapCall('DeleteUpsellRelationship', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

  /**
   *  
   *
   * @param Ping $parameters
   * @return PingResponse
   */
  public function Ping(Ping $parameters) {
    return $this->__soapCall('Ping', array($parameters),       array(
            'uri' => 'http://api.channeladvisor.com/webservices/',
            'soapaction' => ''
           )
      );
  }

}

?>
