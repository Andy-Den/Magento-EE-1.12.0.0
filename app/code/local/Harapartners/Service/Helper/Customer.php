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
 */
class Harapartners_Service_Helper_Customer extends Mage_Core_Helper_Data
{

    public function getLoginByOrderInfoPostUrl()
    {
        return Mage::getUrl('service/customer/loginByOrderInfoPost');
    }

    /**
     * Gets Customer Id based on the service's order ID.  
     *
     * @param int $orderTransactionId The Order Id from the channel
     * @param string $billingZipcode Validation purposes
     * @return int|null Id or null on not found
     */
    public function getCustomerIdByOrderInfo($orderTransactionId, $billingZipcode)
    {
        $customerId = null;
        $isNotFound = false;
        try {
            $order = Mage::getModel('sales/order')->load($orderTransactionId, 'service_transactionid');
            if (! ($order && $order->getId())) {
                $isNotFound = true;
            } else {
                $orderBillingAddress = $order->getBillingAddress();
                if (! ($orderBillingAddress && $orderBillingAddress->getId())) {
                    $isNotFound = true;
                } else {
                    if ($orderBillingAddress->getPostcode() != $billingZipcode) {
                        $isNotFound = true;
                    } else {
                        $isNotFound = false;
                    }
                }
            }
        } catch (Exception $e) {
            Mage::logException($e);
            $isNotFound = true;
        }
        
        return $isNotFound ? null : $order->getCustomerId();
    }

}