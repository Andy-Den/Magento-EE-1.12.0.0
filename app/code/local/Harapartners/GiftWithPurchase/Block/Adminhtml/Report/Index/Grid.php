<?php 

/* NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 */ 
 
class Harapartners_GiftWithPurchase_Block_Adminhtml_Report_Index_Grid extends Mage_Adminhtml_Block_Widget_Grid {
	
	public function __construct(){
        parent::__construct();
        $this->setId('GiftWithPurchaseGrid');
    }
    
	protected function _prepareCollection(){
        $orderItemCollection = Mage::getModel('sales/order_item')->getCollection();
        $orderItemCollection->getSelect()->where('`parent_item_id` IS NULL AND `is_gift_with_purchase_product` = 1');
		$this->setCollection($orderItemCollection);
        parent::_prepareCollection();
        return $this;
    }
    
	protected function _getStore(){
        $storeId = (int) $this->getRequest()->getParam('store', 0);
        return Mage::app()->getStore($storeId);
    }
    
	public function getRowUrl($row){
        return false;
    }
    
    protected function _prepareColumns(){        
        $this->addColumn('order_id', array(
            'header'        => Mage::helper('giftwithpurchase')->__('Order ID'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'order_id'
        ));
        
        $this->addColumn('product_id', array(
            'header'        => Mage::helper('giftwithpurchase')->__('Product ID'),
            'align'         => 'right',
            'width'         => '5px',
            'index'         => 'product_id'
        ));
        
        $this->addColumn('sku', array(
            'header'        => Mage::helper('giftwithpurchase')->__('Product SKU'),
            'align'         => 'right',
            'width'         => '80px',
            'index'         => 'sku'
        ));
        
        $this->addColumn('name', array(
            'header'        => Mage::helper('giftwithpurchase')->__('Product Name'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'name'
        ));
        
        $this->addColumn('qty_ordered', array(
            'header'        => Mage::helper('giftwithpurchase')->__('Qty Ordered'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'qty_ordered'
        ));
        
        $this->addColumn('row_total', array(
            'header'        => Mage::helper('giftwithpurchase')->__('Row Total'),
            'align'         => 'right',
            'width'         => '10px',
            'index'         => 'row_total'
        ));
        
        $this->addColumn('created_at', array(
            'header'        => Mage::helper('giftwithpurchase')->__('Ordered Date'),
            'align'         => 'right',
            'width'         => '10px',
        	'type' 			=> 'datetime',
            'index'         => 'created_at'
        ));

    }
    
}