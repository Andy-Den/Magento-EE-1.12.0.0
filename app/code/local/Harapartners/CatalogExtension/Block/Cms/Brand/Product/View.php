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
 */

class Harapartners_CatalogExtension_Block_Cms_Brand_Product_View extends Mage_Catalog_Block_Product_Abstract {
	
	public function __construct() {
		parent::__construct();
		$this->setTemplate('catalogextension/cms/brand/product/view.phtml');
	}
	
}