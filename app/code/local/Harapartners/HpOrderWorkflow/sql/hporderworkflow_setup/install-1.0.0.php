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
 // TODO use a table prefix...
 
$installer = $this;
$installer->startSetup();

$installer->run("
    INSERT INTO  `{$this->getTable('sales/order_status')}` (`status`, `label`) 
        VALUES ('cs_needed',  'Customer Support Needed');
    INSERT INTO  `{$this->getTable('sales/order_status_state')}` (`status`, `state`, `is_default`)
        VALUES ('cs_needed',  'processing',  '0');
");


$catalogEavSetup = new Mage_Catalog_Model_Resource_Eav_Mysql4_Setup();
$attrGroupName = 'OrderFlow Stock Values';
$defaultSortOrder = 9999; //Put at the end
$subscriptionEnabledData = array(
        'sort_order' => 100,
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'WMS Stock Count',
        'note' => '',
        'input' => 'text',
        'class' => '',
        'source' => '',
    	'default'=> 0, //Disabled by default
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
        'used_for_promo_rules' => false
);
$subscriptionFrequencyData =  array(
        'sort_order' => 110,
        'type' => 'int',
        'backend' => '',
        'frontend' => '',
        'label' => 'DropShip Stock Count',
        'note' => '',
        'input' => 'text',
        'class' => '',
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
        'used_for_promo_rules' => false
);
$subscriptionFrequencyDate =  array(
        'sort_order' => 120,
        'type' => 'datetime',
        'backend' => '',
        'frontend' => '',
        'label' => 'Dropship Valid Untill',
        'note' => '',
        'input' => 'date',
        'class' => '',
        'source' => '',
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
        'used_for_promo_rules' => false
);
$orderStockUpdateDate = array(
        'sort_order' => 130,
        'type' => 'datetime',
        'backend' => '',
        'frontend' => '',
        'label' => 'OrderFlow Stock Last Update',
        'note' => '',
        'input' => 'date',
        'class' => '',
        'source' => '',
    	'default'=> 0, //Disabled by default
        'global' => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
        'visible' => true,
        'required' => false,
        'user_defined' => false,
        'visible_on_front' => false,
        'unique' => false,
        'is_configurable' => false,
        'used_for_promo_rules' => false
);

$catalogEavSetup->addAttribute(
		Mage_Catalog_Model_Product::ENTITY, 
		//Harapartners_HpSubscription_Helper_Data::SUBSCRIPTION_ATTRIBUTE_PREFIX . 'is_allowed',
		'orderflow_wms_stock',
		$subscriptionEnabledData
);
$catalogEavSetup->addAttribute(
		Mage_Catalog_Model_Product::ENTITY, 
		//Harapartners_HpSubscription_Helper_Data::SUBSCRIPTION_ATTRIBUTE_PREFIX . 'allowed_freqs', //Max attr code length is 30
		'orderflow_dropship_stock',
		$subscriptionFrequencyData
);
$catalogEavSetup->addAttribute(
		Mage_Catalog_Model_Product::ENTITY, 
		//Harapartners_HpSubscription_Helper_Data::SUBSCRIPTION_ATTRIBUTE_PREFIX . 'allowed_freqs', //Max attr code length is 30
		'orderflow_dropship_stock_date',
		$subscriptionFrequencyDate
);
$catalogEavSetup->addAttribute(
		Mage_Catalog_Model_Product::ENTITY, 
		//Harapartners_HpSubscription_Helper_Data::SUBSCRIPTION_ATTRIBUTE_PREFIX . 'allowed_freqs', //Max attr code length is 30
		'orderflow_updated_at',
		$orderStockUpdateDate
);

//Add group and attributes to all attribute sets
$productAllAttributeSetIds = $catalogEavSetup->getAllAttributeSetIds(Mage_Catalog_Model_Product::ENTITY);
foreach($productAllAttributeSetIds as $productAttributeSetId){
	$catalogEavSetup->addAttributeGroup(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $attrGroupName, $defaultSortOrder);
	$productSubscriptionGroupId = $catalogEavSetup->getAttributeGroupId(Mage_Catalog_Model_Product::ENTITY, $productAttributeSetId, $attrGroupName);
	
	// ===== WMS STOCK ===== //
	$catalogEavSetup->addAttributeToSet(
			Mage_Catalog_Model_Product::ENTITY, 
			$productAttributeSetId, 
			$productSubscriptionGroupId,
			//Harapartners_HpSubscription_Helper_Data::SUBSCRIPTION_ATTRIBUTE_PREFIX . 'is_allowed'
			'orderflow_wms_stock'
	);
	// ===== DROPSHIP STOCK ===== //
	$catalogEavSetup->addAttributeToSet(
			Mage_Catalog_Model_Product::ENTITY, 
			$productAttributeSetId, 
			$productSubscriptionGroupId, 
			//Harapartners_HpSubscription_Helper_Data::SUBSCRIPTION_ATTRIBUTE_PREFIX . 'allowed_freqs'
			'orderflow_dropship_stock'
	);
	// ===== DROPSHIP STOCK DATE ===== //
	$catalogEavSetup->addAttributeToSet(
			Mage_Catalog_Model_Product::ENTITY, 
			$productAttributeSetId, 
			$productSubscriptionGroupId, 
			//Harapartners_HpSubscription_Helper_Data::SUBSCRIPTION_ATTRIBUTE_PREFIX . 'allowed_freqs'
			'orderflow_dropship_stock_date'
	);
	// ===== ORDERFLOW STOCK UPDATE DATE ===== //
	$catalogEavSetup->addAttributeToSet(
			Mage_Catalog_Model_Product::ENTITY, 
			$productAttributeSetId, 
			$productSubscriptionGroupId, 
			//Harapartners_HpSubscription_Helper_Data::SUBSCRIPTION_ATTRIBUTE_PREFIX . 'allowed_freqs'
			'orderflow_updated_at'
	);
}
$installer->endSetup();
