<?php

/**
 * Description of magento_rdi_cart_refund
 * This will create a creditmemo is magento for an order.
 *
 * @author PMBliss <pmbliss@retaildimensions.com>
 * @copyright (c) 2005-2014 Retail Dimensions Inc.
 * @package Core\Refund\Magento\Product
 * @todo clean up and comment out this class.
 */
class magento_rdi_cart_refund extends rdi_general {

    public $_attributes;
    public $creditmemoData;
    public $refunds;
    public $refund;
    public $items;

    public function magento_rdi_cart_refund($db = '')
    {
        $this->_echo(__FUNCTION__);
        if ($db)
            $this->set_db($db);
    }

    /**
     * Pre Load Function for magento_rdi_cart_refund
     * @global rdi_hook $hook_handler
     * @hook magento_rdi_cart_refund_pre_load
     */
    public function pre_load()
    {
        $this->_echo(__FUNCTION__);
        global $hook_handler;

        //check if magento has been loaded, if not load it.
        $this->load_magento();

        //_comment might not be in rdi_general
        $this->_comment("This calls and creates creditmemos.");

        $hook_handler->call_hook('magento_rdi_cart_refund_pre_load');

        return $this;
    }

    /**
     * Post Load Function for magento_rdi_cart_refund
     * @global rdi_hook $hook_handler
     * @hook magento_rdi_cart_refundpost_load
     */
    public function post_load()
    {
        $this->_echo(__FUNCTION__);
        global $hook_handler;

        $hook_handler->call_hook('magento_rdi_cart_refund_post_load');

        return $this;
    }

    /**
     * Main Load Function for magento_rdi_cart_refund
     *  @return \magento_rdi_cart_refund
     */
    public function load()
    {
        $this->_echo(__FUNCTION__);
        global $db_lib;

        $this->prefix = $this->db_connection->get_db_prefix();

        if ($this->db_connection->count('rpro_in_refund') > 0)
        {
            $this->pre_load()->load_magento_rdi_cart_refund()->post_load();
        }

        return $this;
    }

    public function load_magento()
    {
        $this->_echo(__FUNCTION__);
        if (!class_exists('Mage'))
        {
            require_once("../app/Mage.php");    // External script - Load magento framework
            Mage::app();
        }
    }

    /**
     * Working Function for magento_rdi_cart_refund
     * @global stagging_db_lib $db_lib
     * @return \magento_rdi_cart_refund
     */
    public function load_magento_rdi_cart_refund()
    {
        $this->_echo(__FUNCTION__);

        /**
         * get the attribute_ids that we will use
         */
        $this->set_attributes('');

        /**
         * Enter the customer value for the initial sql
         */
        if ($this->get_refunds())
        {
            foreach ($this->refunds as $refund)
            {
                $this->refund = $refund;

                $this->_print_r($this->refund);
                //Get items to be refunded
                $this->get_items()->set_creditmemoData()->issue_refund($this->refund['so_number'], $this->creditmemoData, $this->refund['comment']);
                $this->creditmemoData = null;
                $this->items = false;
            }
        }
        else
        {
            $this->_echo("No refunds to process.");
        }

        return $this;
    }

    /**
     * Sets the attribute we will need to update.
     * 
     * @param type $attribute_codes
     * @return \magento_rdi_cart_refund
     */
    public function set_attributes($attribute_codes = '')
    {
        $this->_echo(__FUNCTION__);
        $attribute_names = "'related_id','related_parent_id'" . ( $attribute_codes == '' ? '' : "," . $attribute_codes );

        $this->entity_type_code = "catalog_product";

        $this->_attributes = $this->db_connection->cells("SELECT 
                                                         attribute_code,attribute_id FROM {$this->prefix}eav_attribute
                                                        INNER JOIN {$this->prefix}eav_entity_type on {$this->prefix}eav_entity_type.entity_type_id = {$this->prefix}eav_attribute.entity_type_id
                                                        WHERE attribute_code in('related_id','related_parent_id',{$attribute_names}) 
                                                        AND {$this->prefix}eav_entity_type.entity_type_code = '{$this->entity_type_code}'", "attribute_id", "attribute_code");
        return $this;
    }

    public function get_refunds()
    {
        $this->_echo(__FUNCTION__);
        $this->refunds = $this->db_connection->rows("SELECT DISTINCT
                                                        refund.invc_no,
                                                        refund.so_doc_no,
                                                        refund.so_number,
                                                        refund.status AS pos_status,
                                                        refund.refund_date,
                                                        refund.sid,
                                                        refund.subtotal AS pos_subtotal,
                                                        refund.shipping,
                                                        refund.subtotal_refund,
                                                        refund.comment,
                                                        refund.item_qty_refunded,
                                                        o.status AS cart_status,
                                                        o.subtotal_invoiced,
                                                        o.subtotal_canceled,
                                                        o.subtotal_refunded,
                                                        o.shipping_amount,
                                                        o.shipping_canceled,
                                                        o.shipping_invoiced,
                                                        o.shipping_refunded,
                                                        o.tax_amount,
                                                        o.tax_canceled,
                                                        o.tax_invoiced,
                                                        o.tax_refunded,
                                                        o.total_invoiced,
                                                        o.total_canceled,
                                                        o.total_refunded,
                                                        o.total_paid,
                                                        o.total_offline_refunded,
                                                        o.total_online_refunded
                                                         FROM rpro_in_refund refund
                                                        JOIN sales_flat_order o
                                                        ON o.increment_id = refund.so_number
                                                        left join sales_flat_creditmemo c
                                                        on c.order_id = o.entity_id
                                                        and c.increment_id = refund.so_doc_no
                                                        WHERE refund.record_type = 'Refund'
                                                        and c.increment_id is null
");

        return !empty($this->refunds);
    }

    /**
     * Parms are used here and not the vars to be more concise to the actual intensions of the function.
     * @param type $order_id
     * @param type $data
     * @param type $comment
     * @param type $include_comment
     * @param type $refund_store_credit
     */
    public function issue_refund($order_id, $data, $comment, $refund_store_credit = false)
    {
        $this->_echo(__FUNCTION__);
        //$creditmemo = new Mage_Sales_Model_Order_Creditmemo_Api();
       // $invoice = new Mage_Sales_Model_Order_Invoice_Api();

       
       // $this->_print_r($data); //exit;
        try
        {
            //$invoice->create();

            //create($orderIncrementId, $creditmemoData = null, $comment = null, $notifyCustomer = false, $includeComment = false, $refundToStoreCreditAmount = null)            
            $creditmemo_increment_id = $this->create($order_id, $data, $comment, strlen($comment) > 0, $refund_store_credit);
        } catch (Mage_Api_Exception $e)
        {
            //todo: push this error to the order status.
            $this->_echo($e->getMessage());
            //$this->_print_r($e->getTrace());
        }
        /*
         * This can probable be removed later.
$this->_echo(__FUNCTION__ . __LINE__);
        if (isset($creditmemo_increment_id) && $creditmemo_increment_id !== '')//if we did make a creditmemo.
        {$this->_echo(__FUNCTION__ . __LINE__);
            $new_increment_id = $this->set_creditmemo_increment_id($creditmemo_increment_id);
            $this->_echo(__FUNCTION__ . __LINE__);
            $creditmemo_obj = Mage::getModel('sales/order_creditmemo')->load($new_increment_id,'increment_id');
            $this->_echo(__FUNCTION__ . __LINE__);
            try
            {
                $creditmemo_obj->refund();
            } 
            catch (Mage_Api_Exception $e) 
            {$this->_echo(__FUNCTION__ . __LINE__);
                $this->_echo($e->getMessage());
                $this->_print_r($e->getTrace());
            }
            $this->_echo(__FUNCTION__ . __LINE__);
        }
$this->_echo(__FUNCTION__ . __LINE__);
         * 
         */
        unset($creditmemo_increment_id);
    }

    public function get_items()
    {
        $this->_echo(__FUNCTION__);
        //If on the header we have  -1 we will do all items. If we have a 0 we will not do anything the rest will mean go into shipping items.
        if ($this->refund['item_qty_refunded'] === -1)
        {
            $this->items = false;
        }
        else if ($this->refund['item_qty_refunded'] === 0)
        {
            $this->items = array();
        }
        // we are going to get items and keep going
        else
        {
            $this->items = $this->db_connection->rows("SELECT IFNULL(oi.parent_item_id, oi.item_id) AS order_item_id, item.item_qty_refunded AS qty FROM rpro_in_refund item
                                                        JOIN sales_flat_order sfo
                                                        ON sfo.increment_id = item.so_number
                                                        JOIN sales_flat_order_item oi
                                                        ON oi.order_id = sfo.entity_id
                                                        JOIN catalog_product_entity_varchar v
                                                        ON v.entity_id = oi.product_id
                                                        AND v.attribute_id = {$this->_attributes['related_id']}
                                                        AND v.value = item.item_sid
                                                        WHERE item.record_type = 'item' and item.so_number = '{$this->refund['so_number']}'");
        }

        return $this;
    }

    public function set_convertor()
    {
        $this->_echo(__FUNCTION__);
        if (!isset($this->convertor))
        {
            $this->convertor = Mage::getModel('sales/convert_order');
        }
    }

    public function set_creditmemoData()
    {
        $this->_echo(__FUNCTION__);
        //todo look at partial shipping amounts.

        if (isset($this->refund['subtotal_refund']) && $this->refund['subtotal_refund'] > 0.0000)
        {
            $this->refund['adjustment_positive'] = $this->refund['subtotal_refund'];
            $this->refund['adjustment_negative'] = 0.0000;
        }
        else if (isset($this->refund['subtotal_refund']) && $this->refund['subtotal_refund'] < 0.0000)
        {
            $this->refund['adjustment_positive'] = 0.0000;
            $this->refund['adjustment_negative'] = abs($this->refund['subtotal_refund']);
        }
        else
        {
            $this->refund['adjustment_positive'] = 0.0000;
            $this->refund['adjustment_negative'] = 0.0000;
        }

        if (!$this->items)
        {
            $this->set_convertor();
            $this->creditmemoData = $this->convertor->toCreditmemo($this->refund['so_number'])->getData();

            $this->creditmemoData['shipping_amount'] = $this->refund['shipping_amount'];
        }
        else
        {
            $this->creditmemoData = array(
                'qtys' => $this->items,
                'adjustment_positive' => $this->refund['adjustment_positive'], //amount to refund the customer if there is above the original cost
                'adjustment_negative' => $this->refund['adjustment_negative']);
        }

        $this->set_shipping_amount();

        return $this;
    }

    public function set_shipping_amount()
    {
        $this->_echo(__FUNCTION__);
        //if we have already refunded the shipping we dont want to refund more.
        if ($this->refund['shipping_refunded'] == 0.0000)
        {
            $this->creditmemoData['shipping_amount'] = $this->refund['shipping'];
        }
        else
        {
            $this->creditmemoData['shipping_amount'] = $this->refund['shipping_amount'] - $this->refund['shipping_refunded'] < $this->refund['shipping'] ? $this->refund['shipping_amount'] - $this->refund['shipping_refunded'] : $this->refund['shipping'];
        }
    }

    public function set_creditmemo_increment_id($increment_id)
    {
        $this->db_connection->exec("UPDATE sales_flat_creditmemo set increment_id = '{$this->refund['so_doc_no']}' WHERE increment_id = '{$increment_id}'");

        $this->db_connection->exec("UPDATE sales_flat_creditmemo_grid set increment_id = '{$this->refund['so_doc_no']}' WHERE increment_id = '{$increment_id}'");
        
        return $this->refund['so_doc_no'];
    }

   public function create($orderIncrementId, $creditmemoData = null, $comment = null, $notifyCustomer = false,
        $includeComment = false, $refundToStoreCreditAmount = null)
    {
       $api_this = new Mage_Sales_Model_Order_Creditmemo_Api();
       $this->_echo(__FUNCTION__);
       $this->_echo($orderIncrementId);
        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->load($orderIncrementId, 'increment_id');
        if (!$order->getId()) {
            $api_this->_fault('order_not_exists');
        }
        if (!$order->canCreditmemo()) {
            $api_this->_fault('cannot_create_creditmemo');
        }
        $creditmemoData = $this->_prepareCreateData($creditmemoData);
        //$creditmemo->setRefundRequested(true);
        
        /** @var $service Mage_Sales_Model_Service_Order */
        $service = Mage::getModel('sales/service_order', $order);
        /** @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        

        foreach ($order->getInvoiceCollection() as $invoice) {
            if ($invoice->canRefund()) {
                $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $creditmemoData);
                
                break;
            }
        }
        
        
        // refund to Store Credit
        if ($refundToStoreCreditAmount) {
            // check if refund to Store Credit is available
            if ($order->getCustomerIsGuest()) {
                $api_this->_fault('cannot_refund_to_storecredit');
            }
            $refundToStoreCreditAmount = max(
                0,
                min($creditmemo->getBaseCustomerBalanceReturnMax(), $refundToStoreCreditAmount)
            );
            if ($refundToStoreCreditAmount) {
                $refundToStoreCreditAmount = $creditmemo->getStore()->roundPrice($refundToStoreCreditAmount);
                $creditmemo->setBaseCustomerBalanceTotalRefunded($refundToStoreCreditAmount);
                $refundToStoreCreditAmount = $creditmemo->getStore()->roundPrice(
                    $refundToStoreCreditAmount*$order->getStoreToOrderRate()
                );
                // this field can be used by customer balance observer
                $creditmemo->setBsCustomerBalTotalRefunded($refundToStoreCreditAmount);
                // setting flag to make actual refund to customer balance after credit memo save
                $creditmemo->setCustomerBalanceRefundFlag(true);
            }
        }
        
        //$this->_var_dump($creditmemo->getData());
        //$this->_print_r(get_class_methods($creditmemo));
        
        $creditmemo->setRequestedCaptureCase('online');
        $creditmemo->setPaymentRefundDisallowed(false)->register();
        //$this->_var_dump($creditmemo->getData());
        // add comment to creditmemo
        if (!empty($comment)) {
            $creditmemo->addComment($comment, $notifyCustomer);
        }
        try {
            Mage::getModel('core/resource_transaction')
                ->addObject($creditmemo)
                ->addObject($order)
                ->save();
            // send email notification
            $creditmemo->sendEmail($notifyCustomer, ($includeComment ? $comment : ''));
        } catch (Mage_Core_Exception $e) {
            $api_this->_fault('data_invalid', $e->getMessage());
        }
        return $creditmemo->getIncrementId();
    }
    
    /**
     * Hook method, could be replaced in derived classes
     *
     * @param  array $data
     * @return array
     */
    protected function _prepareCreateData($data)
    {
        $data = isset($data) ? $data : array();

        if (isset($data['qtys']) && count($data['qtys'])) {
            $qtysArray = array();
            foreach ($data['qtys'] as $qKey => $qVal) {
                // Save backward compatibility
                if (is_array($qVal)) {
                    if (isset($qVal['order_item_id']) && isset($qVal['qty'])) {
                        $qtysArray[$qVal['order_item_id']] = $qVal['qty'];
                    }
                } else {
                    $qtysArray[$qKey] = $qVal;
                }
            }
            $data['qtys'] = $qtysArray;
        }
        return $data;
    }

}
