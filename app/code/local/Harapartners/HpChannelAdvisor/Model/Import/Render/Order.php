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
 * @package     Harapartners_HpChannelAdvisor_Model_Render_Order
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/HpChannelAdvisor/Model/Render/Order.php
class Harapartners_HpChannelAdvisor_Model_Import_Render_Order extends Mage_Core_Model_Abstract
{

    public function renderOrderResponseDetailHigh(OrderResponseDetailHigh $orderResponce)
    {
        $rendered = array();
        
        $orderLvl = array();
        $orderLvl['emailAddress'] = $orderResponce->BuyerEmailAddress;
        $orderLvl['orderDate'] = $orderResponce->OrderTimeGMT;
        $orderLvl['serviceId'] = $orderResponce->ClientOrderIdentifier;
        $orderLvl['extraInfo']['OrderID'] = $orderResponce->OrderID;
        
        $paymentResponce = $orderResponce->PaymentInfo;
        $paymentLvl = array();
        $paymentLvl['type'] = $paymentResponce->PaymentType; //The most commonly defined payment types include AX, CC, DI, GG, MC, MO, PC, PO, PP, VI, WT.  These correspond to American Express, Certified Check, Discover, Google Checkout, MasterCard, Money Order, Personal Check, Purchase Order, PayPal, Visa, and Wire Transfer.
        $paymentLvl['transactionId'] = $paymentResponce->PaymentTransactionID;
        $paymentLvl['payPalId'] = $paymentResponce->PayPalID;
        $paymentLvl['ccLast4'] = $paymentResponce->CreditCardLast4;
        $paymentLvl['referenceNumber'] = $paymentResponce->MerchantReferenceNumber;
        
        $shippingResponce = $orderResponce->ShippingInfo;
        $shippingLvl = array();
        $shippingLvl['instructions'] = $shippingResponce->ShippingInstructions;
        if (is_array($shippingResponce->ShipmentList)) {
            $shippingLvl['methodTracking'] = array(
                'carrier' => $shippingResponce->ShipmentList[0]->ShippingCarrier , 
                'class' => $shippingResponce->ShipmentList[0]->ShippingClass , 
                'tracking' => $shippingResponce->ShipmentList[0]->TrackingNumber
            );
        } elseif (isset($shippingResponce->ShipmentList->Shipment) && ! empty($shippingResponce->ShipmentList->Shipment->ShippingClass)) {
            $shippingLvl['methodTracking'] = array(
                'carrier' => $shippingResponce->Shipment->ShippingCarrier , 
                'class' => $shippingResponce->Shipment->ShippingClass , 
                'tracking' => $shippingResponce->Shipment->TrackingNumber
            );
        } else 

        {
            $shippingLvl['methodTracking'] = null;
        }
        
        $shippingLvl['contact'] = array(
            'company' => $shippingResponce->CompanyName , 
            'title' => $shippingResponce->Title , 
            'firstName' => $shippingResponce->FirstName , 
            'lastName' => $shippingResponce->LastName , 
            'suffix' => $shippingResponce->Suffix , 
            'phoneDay' => $shippingResponce->PhoneNumberDay , 
            'phoneNight' => $shippingResponce->PhoneNumberEvening , 
            'addressLine1' => $shippingResponce->AddressLine1 , 
            'addressLine2' => $shippingResponce->AddressLine2 , 
            'city' => $shippingResponce->City , 
            'regionCode' => $shippingResponce->Region , 
            'regionDescription' => $shippingResponce->RegionDescription , 
            'postalCode' => $shippingResponce->PostalCode , 
            'countryCode' => $shippingResponce->CountryCode
        );
        
        $billingResponce = $orderResponce->BillingInfo;
        $billingLvl = array();
        $billingLvl['contact'] = array(
            'company' => $billingResponce->CompanyName , 
            'title' => $billingResponce->Title , 
            'firstName' => $billingResponce->FirstName , 
            'lastName' => $billingResponce->LastName , 
            'suffix' => $billingResponce->Suffix , 
            'phoneDay' => $billingResponce->PhoneNumberDay , 
            'phoneNight' => $billingResponce->PhoneNumberEvening , 
            'addressLine1' => $billingResponce->AddressLine1 , 
            'addressLine2' => $billingResponce->AddressLine2 , 
            'city' => $billingResponce->City , 
            'regionCode' => $billingResponce->Region , 
            'regionDescription' => $billingResponce->RegionDescription , 
            'postalCode' => $billingResponce->PostalCode , 
            'countryCode' => $billingResponce->CountryCode
        );
        
        $cartResponce = $orderResponce->ShoppingCart;
        $cartLvl = array();
        
        $cartLvl['source'] = $cartResponce->CheckoutSource;
        $cartLvl['lineItems'] = array();
        foreach ($cartResponce->LineItemSKUList as $skuList) {
            /* @var $skuList OrderLineItemItem */
            $cartLvl['lineItems'][] = array(
                'sku' => $skuList->SKU , 
                'price' => $skuList->UnitPrice , 
                'qty' => $skuList->Quantity , 
                'title' => $skuList->Title , 
                'listingId' => $skuList->SalesSourceID , 
                'tax' => $skuList->TaxCost , 
                'shipping' => $skuList->ShippingCost , 
                'shippingTax' => $skuList->ShippingTaxCost , 
                'lineItemId' => $skuList->LineItemID
            );
        }
        
        foreach ($cartResponce->LineItemInvoiceList->OrderLineItemInvoice as $invoicesCosts) {
            /* @var $invoicesCosts OrderLineItemInvoice */
            $cartLvl['costs'][$invoicesCosts->LineItemType] = $invoicesCosts->UnitPrice;
        }
        
        $rendered['payment'] = $paymentLvl;
        $rendered['order'] = $orderLvl;
        $rendered['shipping'] = $shippingLvl;
        $rendered['billing'] = $billingLvl;
        $rendered['cart'] = $cartLvl;
        return $rendered;
    }

}
