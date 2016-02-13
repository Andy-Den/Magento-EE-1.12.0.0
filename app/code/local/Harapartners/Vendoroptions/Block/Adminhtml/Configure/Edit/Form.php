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
 * @package     Harapartners\Webservice\Block
 * @author      Steven Hoffman <s.hoffman@harapartners.com>
 * @copyright   Copyright (c) 2013 Harapartners Inc.
 */

// app/code/local/Harapartners/Vendoroptions/Block/Adminhtml/Configure/Edit/Form.php
class Harapartners_Vendoroptions_Block_Adminhtml_Configure_Edit_Form extends Mage_Adminhtml_Block_Widget_Form {
	
	protected function _prepareForm() {
		//$categroryArray = Mage::getModel('promotionfactory/catalog_category')->getCategoryInfo();
		$yesno = Mage::getModel ( 'adminhtml/system_config_source_yesno' );
		
		$form = new Varien_Data_Form ( array ('id' => 'edit_form', 'action' => $this->getData ( 'action' ), 'method' => 'post' ) );
		
		//		$fieldset = $form->addFieldset('header', array('legend'=>Mage::helper('promotionfactory')->__('Instruction')));
		//		$fieldset->addField('instruction', 'label', array(
		//				'value' => Mage::helper('promotionfactory')->__('Here you can set the rule for the promotion, such as buy 2(x) product from category A get 30% discount on the next 1(y) product from category B'),
		//		));
		

		$fieldsetIds = $form->addFieldset ( 'rule', array ('legend' => Mage::helper ( 'vendoroptions' )->__ ( 'Rule Setting' ) ) );
		
		$fieldsetIds->addField ( 'entity_id', 'label', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Entity ID' ),
		'name' => 'entity_id',
		'required' => true,
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor number' ),
		));
		
		$fieldsetIds->addField ( 'code', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor Code' ),
		'name' => 'code',
		));
		
		$fieldsetIds->addField ( 'name', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor Name' ),
		'name' => 'name',
		));
		
		$fieldsetIds->addField ( 'account_number', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor Account Number' ),
		'name' => 'account_number',
		));
		
		$fieldsetIds->addField ( 'phone', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor Phone Number' ),
		'name' => 'phone',
		));
		
		$fieldsetIds->addField ( 'url_path', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor OLD Url Path' ),
		'name' => 'url_path',
		));
		
		$fieldsetIds->addField ( 'size_type', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor Default Size Type' ),
		'name' => 'size_type',
		));
		
		$fieldsetIds->addField ( 'size_chart_cms_block', 'text', array (
				'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor Size Chart CMS Block' ),
				'name' => 'size_chart_cms_block',
				'note' => 'If empty, the default size chart will be used',
		));
		
		$fieldsetDropship = $form->addFieldset ( 'dropship_group', array ('legend' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor Dropship Settings' ) ) );
		
		$fieldsetDropship->addField ( 'dropship', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Does Vendor support Dropship?' ),
		'name' => 'dropship',
		////'note' => Mage::helper ( 'vendoroptions' )->__ ( 'use | (bar/pipe) to separate tokens' ),
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetDropship->addField ( 'dropship_account', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Dropship Account Number' ),
		'name' => 'dropship_account',
		));
		
		$fieldsetDropship->addField ( 'dropship_method', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'What method does the vendor require' ),
		'name' => 'dropship_method',
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'This will be choices later' ),
		));
		
		$fieldsetDropship->addField ( 'dropship_email', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Dropship Email Address' ),
		'name' => 'dropship_email',
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If method is Email' ),
		));
		
		$fieldsetDropship->addField ( 'dropship_fax', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Dropship Fax Number' ),
		'name' => 'dropship_fax',
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If method is Fax' ),
		));
		
		$fieldsetDropship->addField ( 'dropship_lead_time_info', 'text', array (
				'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Dropship Lead Time Info' ),
				'name' => 'dropship_lead_time_info',
				'note' => 'Used on product detail page',
		));
		
		$fieldsetDropship->addField ( 'edi', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Does Vendor support EDI?' ),
		'name' => 'edi',
		////'note' => Mage::helper ( 'vendoroptions' )->__ ( 'use | (bar/pipe) to separate tokens' ),
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetDropship->addField ( 'edi_doc_id', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Edi Document ID' ),
		'name' => 'edi_doc_id',
		////'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If method is Fax' ),
		));
		
		$fieldsetDropship->addField ( 'edi_catalog_id', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Edi catalog ID' ),
		'name' => 'dropship_catalog_id',
		////'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If method is Fax' ),
		));
		
		$fieldsetDropship->addField ( 'edi_inventory_feed', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Does Vendor provide inventory EDI feed?' ),
		'name' => 'edi_inventory_feed',
		////'note' => Mage::helper ( 'vendoroptions' )->__ ( 'use | (bar/pipe) to separate tokens' ),
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetDropship->addField ( 'edi_inventory_control', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Default Vendor Inventory Control?' ),
		'name' => 'edi_inventory_control',
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'What exactly is this?' ),
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetDropship->addField ( 'edi_date_offset', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor Edi Date Offset' ),
		'name' => 'edi_date_offset',
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'Use "+X days"' ),
		));
		
		$fieldsetShipping = $form->addFieldset ( 'dropship_shipping', array ('legend' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor Dropship Settings' ) ) );
		
		$fieldsetShipping->addField ( 'edi_drop_ship', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Does Vendor support EDI dropship?' ),
		'name' => 'edi_drop_ship',
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'Is this duplicate' ),
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetShipping->addField ( 'edi_special_order', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Does Vendor support EDI special orders?' ),
		'name' => 'edi_special_order',
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'Why is only one different' ),
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetShipping->addField ( 'ship_usps', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Does Vendor ship USPS?' ),
		'name' => 'ship_usps',
		////'note' => Mage::helper ( 'vendoroptions' )->__ ( 'use | (bar/pipe) to separate tokens' ),
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetShipping->addField ( 'ship_fedex', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Does Vendor ship FedEx?' ),
		'name' => 'ship_fedex',
		////'note' => Mage::helper ( 'vendoroptions' )->__ ( 'use | (bar/pipe) to separate tokens' ),
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetShipping->addField ( 'ship_intl', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Does Vendor allow International shipping?' ),
		'name' => 'ship_intl',
		////'note' => Mage::helper ( 'vendoroptions' )->__ ( 'use | (bar/pipe) to separate tokens' ),
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetShipping->addField ( 'fedex_codes', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'FedEx Codes: G|3|2|1' ),
		'name' => 'fedex_codes',
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'use | (bar/pipe) to separate tokens' ),
		));
		
		$fieldsetShipping->addField ( 'ups_codes', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'UPS Codes:   G|3|2|1' ),
		'name' => 'ups_codes',
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'use | (bar/pipe) to separate tokens' ),
		));
		
		$fieldsetShipping->addField ( 'usps_codes', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'USPS Codes: G|3|2|1' ),
		'name' => 'usps_codes',
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'use | (bar/pipe) to separate tokens' ),
		));
		
		$fieldsetChannel = $form->addFieldset ( 'channel_group', array ('legend' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor Channel Settings' ) ) );
		
		$fieldsetChannel->addField ( 'sell_ebay', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Does vendor Allow Ebay?' ),
		'name' => 'sell_ebay',
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetChannel->addField ( 'ebay_schedule', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Ebay Schedule Codes' ),
		'name' => 'ebay_schedule',
		////'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If method is Fax' ),
		));
		
		$fieldsetChannel->addField ( 'sell_amz', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Does vendor Allow Amazon?' ),
		'name' => 'sell_amz',
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetChannel->addField ( 'amz_label', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Amazon Label codes' ),
		'name' => 'amz_label',
		////'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If method is Fax' ),
		));
		
		$fieldsetChannel->addField ( 'channel_qty_wms_only', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Use Only WMS Quantity for Sync' ),
		'name' => 'channel_qty_wms_only',
		'values' => $yesno->toOptionArray(),
		));

		$fieldsetDate = $form->addFieldset ( 'vendor_dates', array ('legend' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor Date Info' ) ) );
		
		$fieldsetDate->addField ( 'updated_at', 'label', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Updated At' ),
		'name' => 'updated_at',
		////'note' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor number' ),
		));
		
		$fieldsetDate->addField ( 'created_at', 'label', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Created At' ),
		'name' => 'created_at',
		////'note' => Mage::helper ( 'vendoroptions' )->__ ( 'Vendor number' ),
		));
		
		/*
		$fieldsetShippingtype = $form->addFieldset ( 'shippingtype', array ('legend' => Mage::helper ( 'vendoroptions' )->__ ( 'Shipping Setting' ) ) );
		
		$fieldsetShippingtype->addField ( 'shipping_type', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Shipping Type Token' ),
		'name' => 'shipping_type',
		));
		
		$fieldsetShippingtype->addField ( 'rate_table_dom', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Domestic Rate Table' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If you are not Useing this leave it blank' ),
		'name' => 'rate_table_dom',
		));
		
		$fieldsetShippingtype->addField ( 'rate_table_int', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'International Rate Table' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If you are not Useing this leave it blank' ),
		'name' => 'rate_table_int',
		));
		
		$fieldsetPromotional = $form->addFieldset ( 'promotional', array ('legend' => Mage::helper ( 'vendoroptions' )->__ ( 'Promotional Setting' ) ) );

		$fieldsetPromotional->addField ( 'promo_ship_discount', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Use the Domestic Shipping promotion' ),
		'name' => 'promo_ship_discount',
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetPromotional->addField ( 'ship_discount_prof_id', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Domestic Shipping Discount Id/Name' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If you are not Useing this leave it blank' ),
		'name' => 'ship_discount_prof_id',
		));
		
		$fieldsetPromotional->addField ( 'promo_ship_discount_int', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Use the International Shipping promotion' ),
		'name' => 'promo_ship_discount_int',
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetPromotional->addField ( 'ship_discount_prof_id_int', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'International Shipping Discount Id/Name' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If you are not Useing this leave it blank' ),
		'name' => 'ship_discount_prof_id_int',
		));
		
		$fieldsetServicemods = $form->addFieldset ( 'servicemods', array ('legend' => Mage::helper ( 'vendoroptions' )->__ ( 'Service Modifiers' ) ) );
		
		$fieldsetServicemods->addField ( 'dispatch_time_max', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Dispatch Time Max Token' ),
		'name' => 'dispatch_time_max',
		));
		
		$fieldsetServicemods->addField ( 'get_it_fast', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Get it Fast' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'This requires other Settings also' ),
		'name' => 'get_it_fast',
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetServicemods->addField ( 'free_dom_shipping', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Offer free domestic shipping' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'This applies to the First Domestic method' ),
		'name' => 'free_dom_shipping',
		'values' => $yesno->toOptionArray(),
		));
		
		$fieldsetServicemods->addField ( 'global_shipping', 'select', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Use Global Shipping Program (Ebay Signup required)' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'This replaces the first International Shipping Service' ),
		'name' => 'global_shipping',
		'values' => $yesno->toOptionArray(),
		));
		
		
		$fieldsetServices = $form->addFieldset ( 'services', array ('legend' => Mage::helper ( 'vendoroptions' )->__ ( 'Services' ) ) );
		
		$fieldsetServices->addField ( 'dom1', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Domestic Shipping Service 1' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If you are not Useing this leave it blank' ),
		'name' => 'dom1',
		));
		
		$fieldsetServices->addField ( 'dom2', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Domestic Shipping Service 2' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If you are not Useing this leave it blank' ),
		'name' => 'dom2',
		));
		
		$fieldsetServices->addField ( 'dom3', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Domestic Shipping Service 3' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If you are not Useing this leave it blank' ),
		'name' => 'dom3',
		));
		
		$fieldsetServices->addField ( 'dom4', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'Domestic Shipping Service 4' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If you are not Useing this leave it blank' ),
		'name' => 'dom4',
		));
		
		$fieldsetServices->addField ( 'int1', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'International Shipping Service 1' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If you are not Useing this leave it blank' ),
		'name' => 'int1',
		));
		
		$fieldsetServices->addField ( 'int2', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'International Shipping Service 2' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If you are not Useing this leave it blank' ),
		'name' => 'int2',
		));
		
		$fieldsetServices->addField ( 'int3', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'International Shipping Service 3' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If you are not Useing this leave it blank' ),
		'name' => 'int3',
		));
		
		$fieldsetServices->addField ( 'int4', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'International Shipping Service 4' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If you are not Useing this leave it blank' ),
		'name' => 'int4',
		));
		
		$fieldsetServices->addField ( 'int5', 'text', array (
		'label' => Mage::helper ( 'vendoroptions' )->__ ( 'International Shipping Service 5' ),
		'note' => Mage::helper ( 'vendoroptions' )->__ ( 'If you are not Useing this leave it blank' ),
		'name' => 'int5',
		));
		*/
		
		
		if (Mage::registry ( 'vendoroptions_configure_data' )) {
			$form->setValues ( Mage::registry ( 'vendoroptions_configure_data' )->getData () );
		} elseif ($shippingData = Mage::getSingleton ( 'adminhtml/session' )->getData ( 'vendoroptions_configure_data' )) {
			$form->setValues ( $shippingData );
			Mage::getSingleton ( 'adminhtml/session' )->setData ( 'vendoroptions_configure_data', null );
		}
		
		$form->setUseContainer ( true );
		$this->setForm ( $form );
		return parent::_prepareForm ();
	}
}