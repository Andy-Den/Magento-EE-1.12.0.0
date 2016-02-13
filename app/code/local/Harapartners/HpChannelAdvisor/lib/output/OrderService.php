<?php

if (!class_exists("SetOrdersExportStatus", false)) {
/**
 * SetOrdersExportStatus
 */
class SetOrdersExportStatus {
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
	/**
	 * @access public
	 * @var sboolean
	 */
	public $markAsExported;
}}

if (!class_exists("SetOrdersExportStatusResponse", false)) {
/**
 * SetOrdersExportStatusResponse
 */
class SetOrdersExportStatusResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfSetExportStatusResponse
	 */
	public $SetOrdersExportStatusResult;
}}

if (!class_exists("APIResultOfArrayOfSetExportStatusResponse", false)) {
/**
 * APIResultOfArrayOfSetExportStatusResponse
 */
class APIResultOfArrayOfSetExportStatusResponse {
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
	 * @var ArrayOfSetExportStatusResponse
	 */
	public $ResultData;
}}

if (!class_exists("ResultStatus", false)) {
/**
 * ResultStatus
 */
class ResultStatus {
}}

if (!class_exists("SetExportStatusResponse", false)) {
/**
 * SetExportStatusResponse
 */
class SetExportStatusResponse {
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
	 * @var sboolean
	 */
	public $Success;
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

if (!class_exists("SubmitOrderRefund", false)) {
/**
 * SubmitOrderRefund
 */
class SubmitOrderRefund {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var RefundOrderRequest
	 */
	public $request;
}}

if (!class_exists("SubmitOrderRefundResponse", false)) {
/**
 * SubmitOrderRefundResponse
 */
class SubmitOrderRefundResponse {
	/**
	 * @access public
	 * @var APIResultOfRefundOrderResponse
	 */
	public $SubmitOrderRefundResult;
}}

if (!class_exists("APIResultOfRefundOrderResponse", false)) {
/**
 * APIResultOfRefundOrderResponse
 */
class APIResultOfRefundOrderResponse {
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
	 * @var RefundOrderResponse
	 */
	public $ResultData;
}}

if (!class_exists("SetSellerOrderID", false)) {
/**
 * SetSellerOrderID
 */
class SetSellerOrderID {
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
	public $sellerOrderIDList;
}}

if (!class_exists("SetSellerOrderIDResponse", false)) {
/**
 * SetSellerOrderIDResponse
 */
class SetSellerOrderIDResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfInt32
	 */
	public $SetSellerOrderIDResult;
}}

if (!class_exists("APIResultOfArrayOfInt32", false)) {
/**
 * APIResultOfArrayOfInt32
 */
class APIResultOfArrayOfInt32 {
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
	 * @var ArrayOfInt
	 */
	public $ResultData;
}}

if (!class_exists("SetSellerOrderItemIDList", false)) {
/**
 * SetSellerOrderItemIDList
 */
class SetSellerOrderItemIDList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sint
	 */
	public $orderID;
	/**
	 * @access public
	 * @var ArrayOfInt
	 */
	public $lineItemIDList;
	/**
	 * @access public
	 * @var ArrayOfString
	 */
	public $sellerOrderItemIDList;
}}

if (!class_exists("SetSellerOrderItemIDListResponse", false)) {
/**
 * SetSellerOrderItemIDListResponse
 */
class SetSellerOrderItemIDListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfBoolean
	 */
	public $SetSellerOrderItemIDListResult;
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

if (!class_exists("GetOrderRefundHistory", false)) {
/**
 * GetOrderRefundHistory
 */
class GetOrderRefundHistory {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sint
	 */
	public $orderID;
}}

if (!class_exists("GetOrderRefundHistoryResponse", false)) {
/**
 * GetOrderRefundHistoryResponse
 */
class GetOrderRefundHistoryResponse {
	/**
	 * @access public
	 * @var APIResultOfOrderRefundHistoryResponse
	 */
	public $GetOrderRefundHistoryResult;
}}

if (!class_exists("APIResultOfOrderRefundHistoryResponse", false)) {
/**
 * APIResultOfOrderRefundHistoryResponse
 */
class APIResultOfOrderRefundHistoryResponse {
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
	 * @var OrderRefundHistoryResponse
	 */
	public $ResultData;
}}

if (!class_exists("UpdateOrderList", false)) {
/**
 * UpdateOrderList
 */
class UpdateOrderList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var ArrayOfOrderUpdateSubmit
	 */
	public $updateOrderSubmitList;
}}

if (!class_exists("OrderUpdateSubmit", false)) {
/**
 * OrderUpdateSubmit
 */
class OrderUpdateSubmit {
	/**
	 * @access public
	 * @var sint
	 */
	public $OrderID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $NewClientOrderIdentifier;
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
	 * @var TransactionNoteSubmit
	 */
	public $TransactionNotes;
	/**
	 * @access public
	 * @var OrderStatusUpdateSubmit
	 */
	public $OrderStatusUpdate;
	/**
	 * @access public
	 * @var BillingInfoUpdateSubmit
	 */
	public $BillingInfo;
	/**
	 * @access public
	 * @var ShippingInfoUpdateSubmit
	 */
	public $ShippingInfo;
	/**
	 * @access public
	 * @var PaymentInfoUpdateSubmit
	 */
	public $PaymentInfo;
	/**
	 * @access public
	 * @var ShippingMethodInfoUpdateSubmit
	 */
	public $RequestedShippingMethodInfo;
}}

if (!class_exists("TransactionNoteSubmit", false)) {
/**
 * TransactionNoteSubmit
 */
class TransactionNoteSubmit {
	/**
	 * @access public
	 * @var sstring
	 */
	public $Note;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $ShouldOverwrite;
}}

if (!class_exists("OrderStatusUpdateSubmit", false)) {
/**
 * OrderStatusUpdateSubmit
 */
class OrderStatusUpdateSubmit {
	/**
	 * @access public
	 * @var sstring
	 */
	public $CheckoutPaymentStatus;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ShippingStatus;
}}

if (!class_exists("ShippingMethodInfoUpdateSubmit", false)) {
/**
 * ShippingMethodInfoUpdateSubmit
 */
class ShippingMethodInfoUpdateSubmit {
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
}}

if (!class_exists("UpdateOrderListResponse", false)) {
/**
 * UpdateOrderListResponse
 */
class UpdateOrderListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfOrderUpdateResponse
	 */
	public $UpdateOrderListResult;
}}

if (!class_exists("APIResultOfArrayOfOrderUpdateResponse", false)) {
/**
 * APIResultOfArrayOfOrderUpdateResponse
 */
class APIResultOfArrayOfOrderUpdateResponse {
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
	 * @var ArrayOfOrderUpdateResponse
	 */
	public $ResultData;
}}

if (!class_exists("OrderUpdateResponse", false)) {
/**
 * OrderUpdateResponse
 */
class OrderUpdateResponse {
	/**
	 * @access public
	 * @var sboolean
	 */
	public $FlagAndNotesSuccess;
	/**
	 * @access public
	 * @var sstring
	 */
	public $FlagAndNotesMessage;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $OrderStatusSuccess;
	/**
	 * @access public
	 * @var sstring
	 */
	public $OrderStatusMessage;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $ShippingAndCOIDSuccess;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ShippingAndCOIDMessage;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $BillingAndPaymentSuccess;
	/**
	 * @access public
	 * @var sstring
	 */
	public $BillingAndPaymentMessage;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $RequestedShippingMethodSuccess;
	/**
	 * @access public
	 * @var sstring
	 */
	public $RequestedShippingMethodMessage;
}}

if (!class_exists("OrderMerge", false)) {
/**
 * OrderMerge
 */
class OrderMerge {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sint
	 */
	public $primaryOrderID;
	/**
	 * @access public
	 * @var ArrayOfInt
	 */
	public $orderIDMergeList;
}}

if (!class_exists("OrderMergeResponse", false)) {
/**
 * OrderMergeResponse
 */
class OrderMergeResponse {
	/**
	 * @access public
	 * @var APIResultOfBoolean
	 */
	public $OrderMergeResult;
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

if (!class_exists("OrderSplit", false)) {
/**
 * OrderSplit
 */
class OrderSplit {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var sint
	 */
	public $orderID;
	/**
	 * @access public
	 * @var ArrayOfInt
	 */
	public $lineItemIDList;
}}

if (!class_exists("OrderSplitResponse", false)) {
/**
 * OrderSplitResponse
 */
class OrderSplitResponse {
	/**
	 * @access public
	 * @var APIResultOfBoolean
	 */
	public $OrderSplitResult;
}}

if (!class_exists("SubmitOrder", false)) {
/**
 * SubmitOrder
 */
class SubmitOrder {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var OrderSubmit
	 */
	public $order;
}}

if (!class_exists("SubmitOrderResponse", false)) {
/**
 * SubmitOrderResponse
 */
class SubmitOrderResponse {
	/**
	 * @access public
	 * @var APIResultOfInt32
	 */
	public $SubmitOrderResult;
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

if (!class_exists("GetOrderList", false)) {
/**
 * GetOrderList
 */
class GetOrderList {
	/**
	 * @access public
	 * @var sstring
	 */
	public $accountID;
	/**
	 * @access public
	 * @var OrderCriteria
	 */
	public $orderCriteria;
}}

if (!class_exists("GetOrderListResponse", false)) {
/**
 * GetOrderListResponse
 */
class GetOrderListResponse {
	/**
	 * @access public
	 * @var APIResultOfArrayOfOrderResponseItem
	 */
	public $GetOrderListResult;
}}

if (!class_exists("APIResultOfArrayOfOrderResponseItem", false)) {
/**
 * APIResultOfArrayOfOrderResponseItem
 */
class APIResultOfArrayOfOrderResponseItem {
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
	 * @var ArrayOfOrderResponseItem
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

if (!class_exists("RefundOrderRequest", false)) {
/**
 * RefundOrderRequest
 */
class RefundOrderRequest {
	/**
	 * @access public
	 * @var sstring
	 */
	public $ClientOrderIdentifier;
	/**
	 * @access public
	 * @var sint
	 */
	public $OrderID;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $Amount;
	/**
	 * @access public
	 * @var sstring
	 */
	public $AdjustmentReason;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SellerRefundID;
	/**
	 * @access public
	 * @var ArrayOfRefundItem
	 */
	public $RefundItems;
}}

if (!class_exists("RefundItem", false)) {
/**
 * RefundItem
 */
class RefundItem {
	/**
	 * @access public
	 * @var sstring
	 */
	public $SKU;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $Amount;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ShippingAmount;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ShippingTaxAmount;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $TaxAmount;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $GiftWrapAmount;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $GiftWrapTaxAmount;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $RecyclingFee;
	/**
	 * @access public
	 * @var sint
	 */
	public $Quantity;
	/**
	 * @access public
	 * @var sint
	 */
	public $RefundRequestID;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $RefundRequested;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $RestockQuantity;
	/**
	 * @access public
	 * @var sstring
	 */
	public $AdjustmentReason;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SellerRefundID;
}}

if (!class_exists("OrderLineItemRefundHistoryResponse", false)) {
/**
 * OrderLineItemRefundHistoryResponse
 */
class OrderLineItemRefundHistoryResponse extends RefundItem {
	/**
	 * @access public
	 * @var sint
	 */
	public $InvoiceItemID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ItemSaleSource;
	/**
	 * @access public
	 * @var sstring
	 */
	public $RefundRequestStatus;
	/**
	 * @access public
	 * @var PaymentInfo
	 */
	public $RefundPaymentInfo;
	/**
	 * @access public
	 * @var sstring
	 */
	public $RestockStatus;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $RefundCreateDateGMT;
}}

if (!class_exists("PaymentInfo", false)) {
/**
 * PaymentInfo
 */
class PaymentInfo {
	/**
	 * @access public
	 * @var sstring
	 */
	public $PaymentType;
	/**
	 * @access public
	 * @var sstring
	 */
	public $CreditCardLast4;
	/**
	 * @access public
	 * @var sstring
	 */
	public $PayPalID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $MerchantReferenceNumber;
	/**
	 * @access public
	 * @var sstring
	 */
	public $PaymentTransactionID;
}}

if (!class_exists("RefundOrderResponse", false)) {
/**
 * RefundOrderResponse
 */
class RefundOrderResponse {
	/**
	 * @access public
	 * @var sstring
	 */
	public $ClientOrderIdentifier;
	/**
	 * @access public
	 * @var sint
	 */
	public $OrderID;
	/**
	 * @access public
	 * @var ArrayOfRefundItem
	 */
	public $RefundItems;
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

if (!class_exists("OrderRefundHistoryResponse", false)) {
/**
 * OrderRefundHistoryResponse
 */
class OrderRefundHistoryResponse {
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
	public $RefundStatus;
	/**
	 * @access public
	 * @var ArrayOfOrderLineItemRefundHistoryResponse
	 */
	public $LineItemRefunds;
}}

if (!class_exists("AddressInfo", false)) {
/**
 * AddressInfo
 */
class AddressInfo {
	/**
	 * @access public
	 * @var sstring
	 */
	public $AddressLine1;
	/**
	 * @access public
	 * @var sstring
	 */
	public $AddressLine2;
	/**
	 * @access public
	 * @var sstring
	 */
	public $City;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Region;
	/**
	 * @access public
	 * @var sstring
	 */
	public $RegionDescription;
	/**
	 * @access public
	 * @var sstring
	 */
	public $PostalCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $CountryCode;
}}

if (!class_exists("PaymentInfoUpdateSubmit", false)) {
/**
 * PaymentInfoUpdateSubmit
 */
class PaymentInfoUpdateSubmit extends PaymentInfo {
}}

if (!class_exists("Order", false)) {
/**
 * Order
 */
class Order {
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $OrderTimeGMT;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ClientOrderIdentifier;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SellerOrderID;
	/**
	 * @access public
	 * @var OrderStatus
	 */
	public $OrderStatus;
	/**
	 * @access public
	 * @var sstring
	 */
	public $BuyerEmailAddress;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $EmailOptIn;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ResellerID;
	/**
	 * @access public
	 * @var BillingInfo
	 */
	public $BillingInfo;
	/**
	 * @access public
	 * @var PaymentInfo
	 */
	public $PaymentInfo;
	/**
	 * @access public
	 * @var OrderCart
	 */
	public $ShoppingCart;
	/**
	 * @access public
	 * @var ArrayOfCustomValue
	 */
	public $CustomValueList;
}}

if (!class_exists("OrderStatus", false)) {
/**
 * OrderStatus
 */
class OrderStatus {
	/**
	 * @access public
	 * @var sstring
	 */
	public $CheckoutStatus;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $CheckoutDateGMT;
	/**
	 * @access public
	 * @var sstring
	 */
	public $PaymentStatus;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $PaymentDateGMT;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ShippingStatus;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $ShippingDateGMT;
	/**
	 * @access public
	 * @var sstring
	 */
	public $OrderRefundStatus;
}}

if (!class_exists("OrderCart", false)) {
/**
 * OrderCart
 */
class OrderCart {
	/**
	 * @access public
	 * @var sint
	 */
	public $CartID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $CheckoutSource;
	/**
	 * @access public
	 * @var sstring
	 */
	public $VATTaxCalculationOption;
	/**
	 * @access public
	 * @var sstring
	 */
	public $VATShippingOption;
	/**
	 * @access public
	 * @var sstring
	 */
	public $VATGiftWrapOption;
	/**
	 * @access public
	 * @var ArrayOfOrderLineItemItem
	 */
	public $LineItemSKUList;
	/**
	 * @access public
	 * @var ArrayOfOrderLineItemInvoice
	 */
	public $LineItemInvoiceList;
	/**
	 * @access public
	 * @var ArrayOfOrderLineItemPromo
	 */
	public $LineItemPromoList;
}}

if (!class_exists("OrderLineItemBase", false)) {
/**
 * OrderLineItemBase
 */
class OrderLineItemBase {
	/**
	 * @access public
	 * @var sstring
	 */
	public $LineItemType;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $UnitPrice;
}}

if (!class_exists("OrderLineItemPromo", false)) {
/**
 * OrderLineItemPromo
 */
class OrderLineItemPromo extends OrderLineItemBase {
	/**
	 * @access public
	 * @var sstring
	 */
	public $PromoCode;
}}

if (!class_exists("OrderLineItemItemPromo", false)) {
/**
 * OrderLineItemItemPromo
 */
class OrderLineItemItemPromo extends OrderLineItemPromo {
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ShippingPrice;
}}

if (!class_exists("OrderLineItemInvoice", false)) {
/**
 * OrderLineItemInvoice
 */
class OrderLineItemInvoice extends OrderLineItemBase {
}}

if (!class_exists("ItemWeight", false)) {
/**
 * ItemWeight
 */
class ItemWeight {
}}

if (!class_exists("CustomValue", false)) {
/**
 * CustomValue
 */
class CustomValue {
	/**
	 * @access public
	 * @var sint
	 */
	public $ID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Value;
}}

if (!class_exists("Shipment", false)) {
/**
 * Shipment
 */
class Shipment {
	/**
	 * @access public
	 * @var sstring
	 */
	public $ShippingCarrier;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ShippingClass;
	/**
	 * @access public
	 * @var sstring
	 */
	public $TrackingNumber;
}}

if (!class_exists("OrderCriteria", false)) {
/**
 * OrderCriteria
 */
class OrderCriteria {
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $OrderCreationFilterBeginTimeGMT;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $OrderCreationFilterEndTimeGMT;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $StatusUpdateFilterBeginTimeGMT;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $StatusUpdateFilterEndTimeGMT;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $JoinDateFiltersWithOr;
	/**
	 * @access public
	 * @var sstring
	 */
	public $DetailLevel;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ExportState;
	/**
	 * @access public
	 * @var ArrayOfInt
	 */
	public $OrderIDList;
	/**
	 * @access public
	 * @var ArrayOfString
	 */
	public $ClientOrderIdentifierList;
	/**
	 * @access public
	 * @var sstring
	 */
	public $OrderStateFilter;
	/**
	 * @access public
	 * @var sstring
	 */
	public $PaymentStatusFilter;
	/**
	 * @access public
	 * @var sstring
	 */
	public $CheckoutStatusFilter;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ShippingStatusFilter;
	/**
	 * @access public
	 * @var sstring
	 */
	public $RefundStatusFilter;
	/**
	 * @access public
	 * @var sstring
	 */
	public $DistributionCenterCode;
	/**
	 * @access public
	 * @var sstring
	 */
	public $FulfillmentTypeFilter;
	/**
	 * @access public
	 * @var sint
	 */
	public $PageNumberFilter;
	/**
	 * @access public
	 * @var sint
	 */
	public $PageSize;
}}

if (!class_exists("OrderResponseItem", false)) {
/**
 * OrderResponseItem
 */
class OrderResponseItem {
	/**
	 * @access public
	 * @var sint
	 */
	public $NumberOfMatches;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $OrderTimeGMT;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $LastUpdateDate;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $TotalOrderAmount;
	/**
	 * @access public
	 * @var sstring
	 */
	public $OrderState;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $DateCancelledGMT;
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
	public $SellerOrderID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $FlagStyle;
}}

if (!class_exists("OrderResponseDetailLow", false)) {
/**
 * OrderResponseDetailLow
 */
class OrderResponseDetailLow extends OrderResponseItem {
	/**
	 * @access public
	 * @var OrderStatus
	 */
	public $OrderStatus;
}}

if (!class_exists("OrderResponseDetailMedium", false)) {
/**
 * OrderResponseDetailMedium
 */
class OrderResponseDetailMedium extends OrderResponseDetailLow {
	/**
	 * @access public
	 * @var sstring
	 */
	public $ResellerID;
	/**
	 * @access public
	 * @var sstring
	 */
	public $BuyerEmailAddress;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $EmailOptIn;
	/**
	 * @access public
	 * @var PaymentInfoResponse
	 */
	public $PaymentInfo;
	/**
	 * @access public
	 * @var ShippingInfoResponse
	 */
	public $ShippingInfo;
	/**
	 * @access public
	 * @var BillingInfo
	 */
	public $BillingInfo;
	/**
	 * @access public
	 * @var sstring
	 */
	public $FlagDescription;
}}

if (!class_exists("PaymentInfoResponse", false)) {
/**
 * PaymentInfoResponse
 */
class PaymentInfoResponse extends PaymentInfo {
}}

if (!class_exists("OrderResponseDetailHigh", false)) {
/**
 * OrderResponseDetailHigh
 */
class OrderResponseDetailHigh extends OrderResponseDetailMedium {
	/**
	 * @access public
	 * @var OrderCart
	 */
	public $ShoppingCart;
}}

if (!class_exists("OrderResponseDetailComplete", false)) {
/**
 * OrderResponseDetailComplete
 */
class OrderResponseDetailComplete extends OrderResponseDetailHigh {
	/**
	 * @access public
	 * @var ArrayOfCustomValue
	 */
	public $CustomValueList;
	/**
	 * @access public
	 * @var sstring
	 */
	public $BuyerIpAddress;
	/**
	 * @access public
	 * @var sstring
	 */
	public $TransactionNotes;
}}

if (!class_exists("ContactComplete", false)) {
/**
 * ContactComplete
 */
class ContactComplete extends AddressInfo {
	/**
	 * @access public
	 * @var sstring
	 */
	public $CompanyName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $JobTitle;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Title;
	/**
	 * @access public
	 * @var sstring
	 */
	public $FirstName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $LastName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Suffix;
	/**
	 * @access public
	 * @var sstring
	 */
	public $PhoneNumberDay;
	/**
	 * @access public
	 * @var sstring
	 */
	public $PhoneNumberEvening;
}}

if (!class_exists("ShippingInfoUpdateSubmit", false)) {
/**
 * ShippingInfoUpdateSubmit
 */
class ShippingInfoUpdateSubmit extends ContactComplete {
	/**
	 * @access public
	 * @var sstring
	 */
	public $EmailAddress;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ShippingInstructions;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $EstimatedShipDate;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $DeliveryDate;
}}

if (!class_exists("OrderSubmit", false)) {
/**
 * OrderSubmit
 */
class OrderSubmit extends Order {
	/**
	 * @access public
	 * @var ShippingInfoSubmit
	 */
	public $ShippingInfo;
}}

if (!class_exists("OrderLineItemItem", false)) {
/**
 * OrderLineItemItem
 */
class OrderLineItemItem extends OrderLineItemBase {
	/**
	 * @access public
	 * @var sint
	 */
	public $LineItemID;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $AllowNegativeQuantity;
	/**
	 * @access public
	 * @var sint
	 */
	public $Quantity;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ItemSaleSource;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SKU;
	/**
	 * @access public
	 * @var sstring
	 */
	public $Title;
	/**
	 * @access public
	 * @var sstring
	 */
	public $BuyerUserID;
	/**
	 * @access public
	 * @var sint
	 */
	public $BuyerFeedbackRating;
	/**
	 * @access public
	 * @var sstring
	 */
	public $SalesSourceID;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $VATRate;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $TaxCost;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ShippingCost;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $ShippingTaxCost;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $GiftWrapCost;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $GiftWrapTaxCost;
	/**
	 * @access public
	 * @var sstring
	 */
	public $GiftMessage;
	/**
	 * @access public
	 * @var sstring
	 */
	public $GiftWrapLevel;
	/**
	 * @access public
	 * @var ArrayOfOrderLineItemItemPromo
	 */
	public $ItemPromoList;
	/**
	 * @access public
	 * @var sdecimal
	 */
	public $RecyclingFee;
}}

if (!class_exists("OrderLineItemItemResponse", false)) {
/**
 * OrderLineItemItemResponse
 */
class OrderLineItemItemResponse extends OrderLineItemItem {
	/**
	 * @access public
	 * @var ItemWeight
	 */
	public $UnitWeight;
	/**
	 * @access public
	 * @var sstring
	 */
	public $WarehouseLocation;
	/**
	 * @access public
	 * @var sstring
	 */
	public $UserName;
	/**
	 * @access public
	 * @var sstring
	 */
	public $DistributionCenterCode;
	/**
	 * @access public
	 * @var sboolean
	 */
	public $IsExternallyFulfilled;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ItemSaleSourceTransactionID;
}}

if (!class_exists("ShippingInfo", false)) {
/**
 * ShippingInfo
 */
class ShippingInfo extends ContactComplete {
	/**
	 * @access public
	 * @var ArrayOfShipment
	 */
	public $ShipmentList;
	/**
	 * @access public
	 * @var sstring
	 */
	public $ShippingInstructions;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $EstimatedShipDate;
	/**
	 * @access public
	 * @var sdateTime
	 */
	public $DeliveryDate;
}}

if (!class_exists("ShippingInfoResponse", false)) {
/**
 * ShippingInfoResponse
 */
class ShippingInfoResponse extends ShippingInfo {
}}

if (!class_exists("BillingInfo", false)) {
/**
 * BillingInfo
 */
class BillingInfo extends ContactComplete {
}}

if (!class_exists("ShippingInfoSubmit", false)) {
/**
 * ShippingInfoSubmit
 */
class ShippingInfoSubmit extends ShippingInfo {
}}

if (!class_exists("BillingInfoUpdateSubmit", false)) {
/**
 * BillingInfoUpdateSubmit
 */
class BillingInfoUpdateSubmit extends BillingInfo {
	/**
	 * @access public
	 * @var sstring
	 */
	public $EmailAddress;
}}

if (!class_exists("OrderService", false)) {
/**
 * OrderService
 * @author WSDLInterpreter
 */
class OrderService extends SoapClient {
	/**
	 * Default class map for wsdl=>php
	 * @access private
	 * @var array
	 */
	private static $classmap = array(
		"SetOrdersExportStatus" => "SetOrdersExportStatus",
		"SetOrdersExportStatusResponse" => "SetOrdersExportStatusResponse",
		"APIResultOfArrayOfSetExportStatusResponse" => "APIResultOfArrayOfSetExportStatusResponse",
		"ResultStatus" => "ResultStatus",
		"SetExportStatusResponse" => "SetExportStatusResponse",
		"APICredentials" => "APICredentials",
		"SubmitOrderRefund" => "SubmitOrderRefund",
		"SubmitOrderRefundResponse" => "SubmitOrderRefundResponse",
		"APIResultOfRefundOrderResponse" => "APIResultOfRefundOrderResponse",
		"SetSellerOrderID" => "SetSellerOrderID",
		"SetSellerOrderIDResponse" => "SetSellerOrderIDResponse",
		"APIResultOfArrayOfInt32" => "APIResultOfArrayOfInt32",
		"SetSellerOrderItemIDList" => "SetSellerOrderItemIDList",
		"SetSellerOrderItemIDListResponse" => "SetSellerOrderItemIDListResponse",
		"APIResultOfArrayOfBoolean" => "APIResultOfArrayOfBoolean",
		"GetOrderRefundHistory" => "GetOrderRefundHistory",
		"GetOrderRefundHistoryResponse" => "GetOrderRefundHistoryResponse",
		"APIResultOfOrderRefundHistoryResponse" => "APIResultOfOrderRefundHistoryResponse",
		"UpdateOrderList" => "UpdateOrderList",
		"OrderUpdateSubmit" => "OrderUpdateSubmit",
		"TransactionNoteSubmit" => "TransactionNoteSubmit",
		"OrderStatusUpdateSubmit" => "OrderStatusUpdateSubmit",
		"ShippingMethodInfoUpdateSubmit" => "ShippingMethodInfoUpdateSubmit",
		"UpdateOrderListResponse" => "UpdateOrderListResponse",
		"APIResultOfArrayOfOrderUpdateResponse" => "APIResultOfArrayOfOrderUpdateResponse",
		"OrderUpdateResponse" => "OrderUpdateResponse",
		"OrderMerge" => "OrderMerge",
		"OrderMergeResponse" => "OrderMergeResponse",
		"APIResultOfBoolean" => "APIResultOfBoolean",
		"OrderSplit" => "OrderSplit",
		"OrderSplitResponse" => "OrderSplitResponse",
		"SubmitOrder" => "SubmitOrder",
		"SubmitOrderResponse" => "SubmitOrderResponse",
		"APIResultOfInt32" => "APIResultOfInt32",
		"GetOrderList" => "GetOrderList",
		"GetOrderListResponse" => "GetOrderListResponse",
		"APIResultOfArrayOfOrderResponseItem" => "APIResultOfArrayOfOrderResponseItem",
		"Ping" => "Ping",
		"PingResponse" => "PingResponse",
		"APIResultOfString" => "APIResultOfString",
		"RefundOrderRequest" => "RefundOrderRequest",
		"RefundItem" => "RefundItem",
		"OrderLineItemRefundHistoryResponse" => "OrderLineItemRefundHistoryResponse",
		"PaymentInfo" => "PaymentInfo",
		"RefundOrderResponse" => "RefundOrderResponse",
		"OrderRefundHistoryResponse" => "OrderRefundHistoryResponse",
		"BillingInfoUpdateSubmit" => "BillingInfoUpdateSubmit",
		"BillingInfo" => "BillingInfo",
		"ContactComplete" => "ContactComplete",
		"AddressInfo" => "AddressInfo",
		"ShippingInfoUpdateSubmit" => "ShippingInfoUpdateSubmit",
		"PaymentInfoUpdateSubmit" => "PaymentInfoUpdateSubmit",
		"OrderSubmit" => "OrderSubmit",
		"Order" => "Order",
		"OrderStatus" => "OrderStatus",
		"OrderCart" => "OrderCart",
		"OrderLineItemItem" => "OrderLineItemItem",
		"OrderLineItemBase" => "OrderLineItemBase",
		"OrderLineItemPromo" => "OrderLineItemPromo",
		"OrderLineItemItemPromo" => "OrderLineItemItemPromo",
		"OrderLineItemInvoice" => "OrderLineItemInvoice",
		"OrderLineItemItemResponse" => "OrderLineItemItemResponse",
		"ItemWeight" => "ItemWeight",
		"CustomValue" => "CustomValue",
		"ShippingInfoSubmit" => "ShippingInfoSubmit",
		"ShippingInfo" => "ShippingInfo",
		"Shipment" => "Shipment",
		"ShippingInfoResponse" => "ShippingInfoResponse",
		"OrderCriteria" => "OrderCriteria",
		"OrderResponseItem" => "OrderResponseItem",
		"OrderResponseDetailLow" => "OrderResponseDetailLow",
		"OrderResponseDetailMedium" => "OrderResponseDetailMedium",
		"PaymentInfoResponse" => "PaymentInfoResponse",
		"OrderResponseDetailHigh" => "OrderResponseDetailHigh",
		"OrderResponseDetailComplete" => "OrderResponseDetailComplete",
	);

	/**
	 * Constructor using wsdl location and options array
	 * @param string $wsdl WSDL location for this service
	 * @param array $options Options for the SoapClient
	 */
	public function __construct($wsdl="https://api.channeladvisor.com/ChannelAdvisorAPI/v7/OrderService.asmx?WSDL", $options=array()) {
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
	 * Service Call: SetOrdersExportStatus
	 * Parameter options:
	 * (SetOrdersExportStatus) parameters
	 * (SetOrdersExportStatus) parameters
	 * @param mixed,... See function description for parameter options
	 * @return SetOrdersExportStatusResponse
	 * @throws Exception invalid function signature message
	 */
	public function SetOrdersExportStatus($mixed = null) {
		$validParameters = array(
			"(SetOrdersExportStatus)",
			"(SetOrdersExportStatus)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("SetOrdersExportStatus", $args);
	}


	/**
	 * Service Call: SubmitOrderRefund
	 * Parameter options:
	 * (SubmitOrderRefund) parameters
	 * (SubmitOrderRefund) parameters
	 * @param mixed,... See function description for parameter options
	 * @return SubmitOrderRefundResponse
	 * @throws Exception invalid function signature message
	 */
	public function SubmitOrderRefund($mixed = null) {
		$validParameters = array(
			"(SubmitOrderRefund)",
			"(SubmitOrderRefund)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("SubmitOrderRefund", $args);
	}


	/**
	 * Service Call: SetSellerOrderID
	 * Parameter options:
	 * (SetSellerOrderID) parameters
	 * (SetSellerOrderID) parameters
	 * @param mixed,... See function description for parameter options
	 * @return SetSellerOrderIDResponse
	 * @throws Exception invalid function signature message
	 */
	public function SetSellerOrderID($mixed = null) {
		$validParameters = array(
			"(SetSellerOrderID)",
			"(SetSellerOrderID)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("SetSellerOrderID", $args);
	}


	/**
	 * Service Call: SetSellerOrderItemIDList
	 * Parameter options:
	 * (SetSellerOrderItemIDList) parameters
	 * (SetSellerOrderItemIDList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return SetSellerOrderItemIDListResponse
	 * @throws Exception invalid function signature message
	 */
	public function SetSellerOrderItemIDList($mixed = null) {
		$validParameters = array(
			"(SetSellerOrderItemIDList)",
			"(SetSellerOrderItemIDList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("SetSellerOrderItemIDList", $args);
	}


	/**
	 * Service Call: GetOrderRefundHistory
	 * Parameter options:
	 * (GetOrderRefundHistory) parameters
	 * (GetOrderRefundHistory) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetOrderRefundHistoryResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetOrderRefundHistory($mixed = null) {
		$validParameters = array(
			"(GetOrderRefundHistory)",
			"(GetOrderRefundHistory)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetOrderRefundHistory", $args);
	}


	/**
	 * Service Call: UpdateOrderList
	 * Parameter options:
	 * (UpdateOrderList) parameters
	 * (UpdateOrderList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return UpdateOrderListResponse
	 * @throws Exception invalid function signature message
	 */
	public function UpdateOrderList($mixed = null) {
		$validParameters = array(
			"(UpdateOrderList)",
			"(UpdateOrderList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("UpdateOrderList", $args);
	}


	/**
	 * Service Call: OrderMerge
	 * Parameter options:
	 * (OrderMerge) parameters
	 * (OrderMerge) parameters
	 * @param mixed,... See function description for parameter options
	 * @return OrderMergeResponse
	 * @throws Exception invalid function signature message
	 */
	public function OrderMerge($mixed = null) {
		$validParameters = array(
			"(OrderMerge)",
			"(OrderMerge)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("OrderMerge", $args);
	}


	/**
	 * Service Call: OrderSplit
	 * Parameter options:
	 * (OrderSplit) parameters
	 * (OrderSplit) parameters
	 * @param mixed,... See function description for parameter options
	 * @return OrderSplitResponse
	 * @throws Exception invalid function signature message
	 */
	public function OrderSplit($mixed = null) {
		$validParameters = array(
			"(OrderSplit)",
			"(OrderSplit)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("OrderSplit", $args);
	}


	/**
	 * Service Call: SubmitOrder
	 * Parameter options:
	 * (SubmitOrder) parameters
	 * (SubmitOrder) parameters
	 * @param mixed,... See function description for parameter options
	 * @return SubmitOrderResponse
	 * @throws Exception invalid function signature message
	 */
	public function SubmitOrder($mixed = null) {
		$validParameters = array(
			"(SubmitOrder)",
			"(SubmitOrder)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("SubmitOrder", $args);
	}


	/**
	 * Service Call: GetOrderList
	 * Parameter options:
	 * (GetOrderList) parameters
	 * (GetOrderList) parameters
	 * @param mixed,... See function description for parameter options
	 * @return GetOrderListResponse
	 * @throws Exception invalid function signature message
	 */
	public function GetOrderList($mixed = null) {
		$validParameters = array(
			"(GetOrderList)",
			"(GetOrderList)",
		);
		$args = func_get_args();
		$this->_checkArguments($args, $validParameters);
		return $this->__soapCall("GetOrderList", $args);
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