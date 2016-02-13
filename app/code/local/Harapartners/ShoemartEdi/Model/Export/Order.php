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
class Harapartners_ShoemartEdi_Model_Export_Order
{
    const FIELD_NAME_S_RECORD_ID = 'SUM';
    const CANCEL_DAYS_OFFSET = '+5 day';
    
    protected $_order = null;
    protected $_ediOrderModel = null;
    protected $_dropshipInventoryModel = null;

    public function processOrder($order, $qtys, $isWorkflow = false, $items = null)
    {
        $this->_order = $order;
        
        /* @var $orderProcess Harapartners_ShoemartEdi_Model_Edi_Order */
        $orderProcess = $this->_getEdiOrderModel();
        $parsedArray = $orderProcess->parseOrder($order, $qtys);
        $dividedArray = $orderProcess->splitByType($parsedArray);
        $renderedArray = $orderProcess->renderOrderByType($dividedArray);
        
        // Actually process
        $this->_processOutAll($renderedArray);
        
        // For now assume all success. -> No feedback is given yet anyway
        if ($isWorkflow) {
            $this->_processStockChanges($qtys, $items);
        }
        
        // TODO Set order And Line Items Status
        return $qtys;
    }

    protected function _processOutAll($results)
    {
        if (isset($results['edi'])) {
            $this->_proccessOutEdi($results['edi']);
        }
        
        if (isset($results['email'])) {
            $this->_proccessOutEmail($results['email']);
        }
        
        if (isset($results['fax'])) {
            $this->_proccessOutFax($results['fax']);
        }
        
        if (isset($results['other'])) {
            $this->_proccessOutOther($results['other']);
        }
    }


    /**
     * Creates the Actual EDI documents
     * @todo config this
     *
     * @param array $result The Processed EDI; info => EdiString
     */
    protected function _proccessOutEdi($result)
    {
        foreach ($result as $info => $ediString) {
            $infoArray = explode('_', $info);
            $filePath = Mage::getBaseDir() . DS . 'var' . DS . 'edi' . DS . 'out' . DS . $infoArray[0] . $infoArray[1] . '-edi_order-' . time() . '.txt';
            if (! is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), null, trues);
            }
            
            $handle = fopen($filePath, 'w');
            fputs($handle, $ediString);
            fclose($handle);
        
        }
    }

    protected function _proccessOutEmail($result)
    {
        foreach ($result as $info => $ediString) {
            $infoArray = explode('_', $info);
            $filePath = Mage::getBaseDir() . DS . 'var' . DS . 'edi' . DS . 'out' . DS . $infoArray[0] . $infoArray[1] . '-email_order-' . time() . '.txt';
            if (! is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), null, trues);
            }
            
            $handle = fopen($filePath, 'w');
            fputs($handle, $ediString);
            fclose($handle);
        
        }
    }

    protected function _proccessOutFax($result)
    {
        foreach ($result as $info => $ediString) {
            $infoArray = explode('_', $info);
            $filePath = Mage::getBaseDir() . DS . 'var' . DS . 'edi' . DS . 'out' . DS . $infoArray[0] . $infoArray[2] . '-fax_order-' . time() . '.txt';
            if (! is_dir(dirname($filePath))) {
                mkdir(dirname($filePath), null, trues);
            }
            
            $handle = fopen($filePath, 'w');
            fputs($handle, $ediString);
            fclose($handle);
        
        }
    }

    protected function _proccessOutOther($result)
    {
        $order = $this->_order;
        $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, 'cs_needed', $result);
        $order->save();
    }

    protected function _renderOrderOther($parsedOrderArray)
    {
        $count = 0;
        $string = 'Cutomer Service Needs To Order:    ' . PHP_EOL;
        foreach ($parsedOrderArray['lineItems'] as $lineItem) {
            $count ++;
            $string .= "{$count}) Pid: {$lineItem['productId']}. QTY: {$lineItem['qty']}. Vendor_StockNumber: {$lineItem['vendorCode']}_{$lineItem['stockNumber']}     " . PHP_EOL;
        }
        
        return $string;
    }

    protected function _processStockChanges($qtys, $items)
    {
        if (! empty($qtys)) {
            foreach ($qtys as $itemNumber => $qty) {
                $productId = $items[$itemNumber]->getProductId();
                $product = Mage::getModel('catalog/product')->setId($productId);
                $this->_getDropshipInventory()->subtractDropshipStock($product, $qty);
            }
        }
    }

    protected function _getDropshipInventory()
    {
        if (! $this->_dropshipInventoryModel) {
            $this->_dropshipInventoryModel = Mage::getModel('hporderworkflow/inventory');
        }
        return $this->_dropshipInventoryModel;
    }

    protected function _getEdiOrderModel()
    {
        if (! $this->_ediOrderModel) {
            $this->_ediOrderModel = Mage::getModel('shoemartedi/edi_order');
        }
        return $this->_ediOrderModel;
    }
}
