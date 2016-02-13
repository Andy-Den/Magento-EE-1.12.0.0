<?php

class rdi_upsell_item_load extends rdi_load 
{	
	public $_product_link_type = array();
	
	public function has_required_settings()
	{
		global $product_link_type;
		
		//require settings
		if(!(isset($product_link_type) && strlen($product_link_type) > 0))
		{
			return false;
		}
		
		return true;
	}
	
	public function set_product_link_types()
	{
		global $product_link_type;
		
		if(strstr($product_link_type,","))
		{
			$this->_product_link_type = explode(",",$product_link_type);
		}
		else
		{
			$this->_product_link_type[] = $product_link_type;
		}
	}
	
	public function main_load()
	{
		if(!$this->has_required_settings())
		{
			return $this;
		}
		
		$this->insert()->update()->delete();
		
		return $this;
	}
}

?>