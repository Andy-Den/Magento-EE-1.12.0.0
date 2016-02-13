<?php

/**
 * Description of magento_rdi_cart_return
 * This will create a creditmemo is magento for an order.
 *
 * @author PMBliss <pmbliss@retaildimensions.com>
 * @copyright (c) 2005-2014 Retail Dimensions Inc.
 * @package Core\Refund\Magento\Product
 * @todo clean up and comment out this class.
 */
class rdi_cart_return extends rdi_general {

    public $_attributes;
    public $creditmemoData;
    public $returns;
    public $return;
    public $return_mapping = array();
    public $items;

    public function rdi_cart_return($db = '')
    {
        $this->_echo(__FUNCTION__);
        if ($db)
            $this->set_db($db);
    }

    /**
     * Pre Load Function for magento_rdi_cart_return
     * @global rdi_hook $hook_handler
     * @hook magento_rdi_cart_return_pre_load
     */
    public function pre_load()
    {
        $this->_echo(__FUNCTION__);
        global $hook_handler;

        //check if magento has been loaded, if not load it.
        $this->load_magento();

        //_comment might not be in rdi_general
        $this->_comment("This calls and creates creditmemos.");

        $hook_handler->call_hook('magento_rdi_cart_return_pre_load');

        return $this;
    }

    /**
     * Post Load Function for magento_rdi_cart_return
     * @global rdi_hook $hook_handler
     * @hook magento_rdi_cart_return_post_load
     */
    public function post_load()
    {
        $this->_echo(__FUNCTION__);
        global $hook_handler;

        $hook_handler->call_hook('magento_rdi_cart_return_post_load');

        return $this;
    }

    /**
     * Main Load Function for magento_rdi_cart_return
     *  @return \magento_rdi_cart_return
     */
    public function load()
    {
        $this->_echo(__FUNCTION__);
        global $db_lib;

        $this->prefix = $this->db_connection->get_db_prefix();

        if ($this->db_connection->count('rpro_in_return') > 0)
        {
            $this->pre_load()->load_magento_rdi_cart_return()->post_load();
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
     * Working Function for magento_rdi_cart_return
     * @global stagging_db_lib $db_lib
     * @return \magento_rdi_cart_return
     */
    public function load_magento_rdi_cart_return()
    {
        global $error_handler;
        $this->_echo(__FUNCTION__);

        /**
         * get the attribute_ids that we will use
         */
        $this->set_attributes('');

        /**
         * Enter the customer value for the initial sql
         */
        if ($this->get_returns())
        {
            foreach ($this->returns as $return)
            {
                $this->return = $return;

                $this->_print_r($this->return);
                //Get items to be returned
                try
                {
                    $this->get_items()->set_creditmemoData()->issue_return($this->return['increment_id'], $this->creditmemoData, $this->return['comment1']);
                } catch (Exception $e)
                {
                    $this->creditmemoData = null;
                    $this->items = false;

                    $this->_print_r($e->getMessage());
                    $error_handler->error_handler(1, 'General Error Returning Items', basename(__FILE__), __FUNCTION__ . ":" . __LINE__, print_r($e->getMessage(), true));
                }
            }
        }
        else
        {
            $this->_echo("No returns to process.");
        }

        return $this;
    }

    /**
     * Sets the attribute we will need to update.
     * 
     * @param type $attribute_codes
     * @return \magento_rdi_cart_return
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
    
    public function return_mapping_to_fields($return_mapping)
    {
        $_fields = array();
        
        foreach($return_mapping as $field => $mapping)
        {
            if(!in_array($field, array("sales_increment_id","creditmemo_increment_id",'item_sid')))
            {
                $_fields[] = !empty($mapping['pos_field'])?"{$mapping['pos_field']} AS `{$mapping['cart_field']}`":"'{$mapping['default']}' AS `{$mapping['cart_field']}`";
            }
        }
        
        
        return implode(", ",$_fields).",";
    }

    public function get_returns()
    {
        global $order_prefix, $field_mapping;
        
        $this->return_mapping['header'] = $field_mapping->get_field_list("return_in");
        $this->return_mapping['items'] = $field_mapping->get_field_list("return_in_items");
                
        $fields = $this->return_mapping_to_fields($this->return_mapping['header']);
        
        $this->_echo(__FUNCTION__);
        $this->returns = $this->db_connection->rows("SELECT DISTINCT 
                                                            {$fields}
							    o.increment_id,
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
                                                          FROM
                                                            rpro_in_return `return` 
                                                            JOIN {$this->prefix}sales_flat_order o 
                                                              ON CONCAT('{$order_prefix}',o.increment_id) = {$this->return_mapping['header']['sales_increment_id']['pos_field']}
                                                            LEFT JOIN {$this->prefix}sales_flat_creditmemo c 
                                                              ON c.order_id = o.entity_id 
                                                              AND c.increment_id = {$this->return_mapping['header']['creditmemo_increment_id']['pos_field']} 
                                                          WHERE return.record_type = 'Return' 
                                                            AND c.increment_id IS NULL 
                                                          ");
                                                              
                                                              $this->_print_r($this->returns);

        return !empty($this->returns);
    }

    /**
     * Parms are used here and not the vars to be more concise to the actual intensions of the function.
     * @param type $order_id
     * @param type $data
     * @param type $comment
     * @param type $include_comment
     * @param type $return_store_credit
     */
    public function issue_return($order_id, $data, $comment, $return_store_credit = false)
    {
        $this->_echo(__FUNCTION__);
        //$creditmemo = new Mage_Sales_Model_Order_Creditmemo_Api();
        // $invoice = new Mage_Sales_Model_Order_Invoice_Api();
        // $this->_print_r($data); //exit;
        try
        {
            //$invoice->create();
            //create($orderIncrementId, $creditmemoData = null, $comment = null, $notifyCustomer = false, $includeComment = false, $refundToStoreCreditAmount = null)            
            $creditmemo_increment_id = $this->create($order_id, $data, $comment, true, $refund_store_credit);
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
        global $order_prefix, $field_mapping;
        
        $fields = $this->return_mapping_to_fields($this->return_mapping['items']);
        
        //$this->_var_dump($this->return_mapping['items']);
        
        $this->_echo(__FUNCTION__);
        //If on the header we have  -1 we will do all items. If we have a 0 we will not do anything the rest will mean go into shipping items.
        //we can't do this anymore. So have to look at the items and set this.
        /* if ($this->return['item_qty_refunded'] === $this->return['item_qty'] )
          {
          $this->items = false;
          }
          else if ($this->return['item_qty_refunded'] === 0)
          {
          $this->items = array();
          }
          // we are going to get items and keep going
          else
          { */
        $this->items = $this->db_connection->rows("SELECT {$fields} IFNULL(oi.parent_item_id, oi.item_id) AS order_item_id FROM rpro_in_return item
													JOIN {$this->prefix}sales_flat_order sfo
													ON CONCAT('{$order_prefix}',sfo.increment_id) = {$this->return_mapping['items']['sales_increment_id']['pos_field']}
													JOIN {$this->prefix}sales_flat_order_item oi
													ON oi.order_id = sfo.entity_id
													and oi.related_id = {$this->return_mapping['items']['item_sid']['pos_field']}
                                                                                                        and item.so_number = '{$this->return['so_number']}'");

        if (empty($this->items))
        {
            throw new Exception("The attempted return[{$this->return['so_number']}] did not return any items. A manual creditmemo/return will be needed. ");
        }

        //}

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
        //not setting this anymore all fees charge are adjustment negative.
        /* if (isset($this->return['subtotal_refund']) && $this->return['subtotal_refund'] > 0.0000)
          {
          $this->return['adjustment_positive'] = $this->return['subtotal_refund'];
          $this->return['adjustment_negative'] = 0.0000;
          }
          else if (isset($this->return['subtotal_refund']) && $this->return['subtotal_refund'] < 0.0000)
          {
          $this->return['adjustment_positive'] = 0.0000;
          $this->return['adjustment_negative'] = abs($this->return['subtotal_refund']);
          }
          else
          {
          $this->return['adjustment_positive'] = 0.0000;
          $this->return['adjustment_negative'] = 0.0000;
          } */

        $this->_print_r($this->items);
        $this->_print_r($this->return);
        if (empty($this->items))
        {
            $this->set_convertor();

            $this->creditmemoData = $this->convertor->toCreditmemo(Mage::getModel('sales/order')->load($this->return['increment_id'], 'increment_id'))->getData();

            $this->creditmemoData['shipping_amount'] = $this->return['shipping_amount'];
        }
        else
        {
            $this->creditmemoData = array(
                'qtys' => $this->items,
                'adjustment_positive' => $this->return['adjustment_positive'], //amount to return the customer if there is above the original cost
                'adjustment_negative' => $this->return['adjustment_negative']);
        }

        $this->set_shipping_amount();

        return $this;
    }

    public function set_shipping_amount()
    {
        $this->_echo(__FUNCTION__);
        //if we have already returned the shipping we dont want to return more.
        if ($this->return['shipping_refunded'] == 0.0000)
        {
            $this->creditmemoData['shipping_amount'] = $this->return['shipping'];
        }
        else
        {
            $this->creditmemoData['shipping_amount'] = $this->return['shipping_amount'] - $this->return['shipping_refunded'] < $this->return['shipping'] ? $this->return['shipping_amount'] - $this->return['shipping_refunded'] : $this->return['shipping'];
        }
    }

    public function set_creditmemo_increment_id($increment_id)
    {
        $this->db_connection->exec("UPDATE sales_flat_creditmemo set increment_id = '{$this->return['so_doc_no']}' WHERE increment_id = '{$increment_id}'");

        $this->db_connection->exec("UPDATE sales_flat_creditmemo_grid set increment_id = '{$this->return['so_doc_no']}' WHERE increment_id = '{$increment_id}'");

        return $this->return['so_doc_no'];
    }

    public function create($orderIncrementId, $creditmemoData = null, $comment = null, $notifyCustomer = false, $includeComment = false, $refundToStoreCreditAmount = null)
    {
        global $error_handler;
        $api_this = new Mage_Sales_Model_Order_Creditmemo_Api();
        $this->_echo(__FUNCTION__);
        $this->_echo($orderIncrementId);
        /** @var $order Mage_Sales_Model_Order */
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        //$order->addStatusHistoryComment("start return");
        if (!$order->getId())
        {
            //$api_this->_fault('order_not_exists');
            $error_handler->error_handler(1, 'order_not_exists', basename(__FILE__), __FUNCTION__ . ":" . __LINE__, $orderIncrementId);
            return null;
        }
        if (!$order->canCreditmemo())
        {
            //$api_this->_fault('cannot_create_creditmemo');
            $error_handler->error_handler(1, 'cannot_create_creditmemo', basename(__FILE__), __FUNCTION__ . ":" . __LINE__, $orderIncrementId);
            return null;
        }
        $creditmemoData = $this->_prepareCreateData($creditmemoData);
        //$creditmemo->setRefundRequested(true);

        $this->_echo(__LINE__);
        /** @var $service Mage_Sales_Model_Service_Order */
        $service = Mage::getModel('sales/service_order', $order);
        /** @var $creditmemo Mage_Sales_Model_Order_Creditmemo */
        $this->_echo(__LINE__);
        foreach ($order->getInvoiceCollection() as $invoice)
        {

            if ($invoice->canRefund())
            {
                $creditmemo = $service->prepareInvoiceCreditmemo($invoice, $creditmemoData);

                break;
            }
        }


        $this->_echo(__LINE__);
        // refund to Store Credit
        if ($refundToStoreCreditAmount)
        {
            $this->_echo(__LINE__);
            // check if refund to Store Credit is available
            if ($order->getCustomerIsGuest())
            {
                $this->_echo(__LINE__);
                $error_handler->error_handler(1, 'cannot_refund_to_storecredit', basename(__FILE__), __FUNCTION__ . ":" . __LINE__, $orderIncrementId);
            }
            $refundToStoreCreditAmount = max(
                    0, min($creditmemo->getBaseCustomerBalanceReturnMax(), $refundToStoreCreditAmount)
            );
            if ($refundToStoreCreditAmount)
            {
                $this->_echo(__LINE__);
                $refundToStoreCreditAmount = $creditmemo->getStore()->roundPrice($refundToStoreCreditAmount);
                $creditmemo->setBaseCustomerBalanceTotalRefunded($refundToStoreCreditAmount);
                $refundToStoreCreditAmount = $creditmemo->getStore()->roundPrice(
                        $refundToStoreCreditAmount * $order->getStoreToOrderRate()
                );
                // this field can be used by customer balance observer
                $creditmemo->setBsCustomerBalTotalRefunded($refundToStoreCreditAmount);
                // setting flag to make actual refund to customer balance after credit memo save
                $creditmemo->setCustomerBalanceRefundFlag(true);
            }
        }

        $creditmemo->setData('increment_id', $this->return['invc_no']);
        $this->_var_dump($creditmemo->getData('increment_id'));
        //$this->_print_r(get_class_methods($creditmemo));
        $this->_echo(__LINE__);
        //$creditmemo->setRequestedCaptureCase('online');
        $creditmemo->setRequestedCaptureCase('offline');
        try
        {
            $creditmemo->setPaymentRefundDisallowed(false)->register();
        } catch (Mage_Core_Exception $e)
        {
            $error_handler->error_handler(1, 'could_not_register_credit_memo', basename(__FILE__), __FUNCTION__ . ":" . __LINE__, print_r($e->getMessage(), true));

            $order->addStatusHistoryComment(print_r($e->getMessage(), true))->save();
            return false;
        }
        //$this->_var_dump($creditmemo->getData());
        // add comment to creditmemo
        if (!empty($comment))
        {
            $this->_echo(__LINE__);
            $creditmemo->addComment($comment, $notifyCustomer);
        }
        try
        {
            $this->_echo(__LINE__);
            Mage::getModel('core/resource_transaction')
                    ->addObject($creditmemo)
                    ->addObject($order)
                    ->save();
            // send email notification
            $creditmemo->sendEmail($notifyCustomer, ($includeComment ? $comment : ''));
        } catch (Mage_Core_Exception $e)
        {
            //$api_this->_fault('data_invalid', $e->getMessage());
            $error_handler->error_handler(1, 'data_invalid', basename(__FILE__), __FUNCTION__ . ":" . __LINE__, print_r($e->getMessage(), true));
            $order->addStatusHistoryComment(print_r($e->getMessage(), true))->save();
            return false;
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

        if (isset($data['qtys']) && count($data['qtys']))
        {
            $qtysArray = array();
            foreach ($data['qtys'] as $qKey => $qVal)
            {
                // Save backward compatibility
                if (is_array($qVal))
                {
                    if (isset($qVal['order_item_id']) && isset($qVal['qty']))
                    {
                        $qtysArray[$qVal['order_item_id']] = $qVal['qty'];
                    }
                }
                else
                {
                    $qtysArray[$qKey] = $qVal;
                }
            }
            $data['qtys'] = $qtysArray;
        }
        return $data;
    }

}
