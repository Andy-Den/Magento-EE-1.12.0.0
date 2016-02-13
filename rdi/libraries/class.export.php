<?php


class rdi_export extends field_mapping
{
	public $export_type;
	public $hook;
	
	//tables definitions
	public $tables = array();
	
	public function __construct($db, $export_type = false)
	{
		//set load type
		if($export_type)
		{
			$this->export_type = $export_type;
		}
		
		$this->set_constants();
		
		//call parent
		parent::__construct($db);
	}
	
	public function pre_export()
	{
		global $hook_handler;
		
		$hook_handler->call_hook($this->hook . __FUNCTION__ );		
		return $this;
	}
	
	public function post_export()
	{
		global $hook_handler;
		
		$hook_handler->call_hook($this->hook . __FUNCTION__ );		
		return $this;
	}
	
	public function export()
	{
		$this->hook = str_replace("rdi_","",get_class($this));
		
		if(!isset($this->export_type))
		{
			//assume the load type is removing all "export"s and underscores;
			$this->export_type = str_replace("export","",str_replace("_","",$this->hook));
		}		
		
		//Call order.
		// Call pre_export functions, could be rewritten in the pos class. and further in the cart.
		// call main export, including insert and update functions.
		// call load in the cart, which is any cart specific loading
		// post load hooks or checks on data validity.
		// Call post_export
		if($this->test_setting(__FUNCTION__))
		{
			$this->pre_export()->cart_export()->main_export()->pos_export()->post_export();
		}
		else
		{
			$this->_echo(__FUNCTION__ . "setting off");
		}
	}
	
	// called with $this->test_setting(__FUNCTION__)
	// 1 is ON and anything else is off. generally 0 off.
	// this should probably return the value if greater than 0. but in the future.
	public function test_setting($setting)
	{
		$setting_name = "{$setting}_{$this->export_type}";
		
		$this->_echo("Setting:{$setting_name}[{$GLOBALS[$setting_name]}]");
		
		return isset($GLOBALS[$setting_name]) && $GLOBALS[$setting_name] == '1';
	}
	
	// functions for completeness
	public function pos_export(){return $this;}
	
	public function main_export()
	{
		$this->select_data()->insert_data();
		//$this->_print_r($this->returns);
		return $this;
	}
	
	//copy this to the cart class
	public function select_data()
	{			
		return $this;
	}
	
	//copy this to the pos class
	public function insert_data()
	{	$this->_echo(__FUNCTION__);
		return $this;
	}	
	
	public function cart_export(){return $this;}
	
	
	//static class for initializing a load class structure.
	// can't use an auto load as this would interfere with Zend in Magento and other carts.
	public static function include_libs($db, $library)
	{
		global $cart_type, $pos_type;
		
		if(!isset($cart_type))
		{
			die("Specify a cart_type in the init.php");
		}
		
		if(!isset($pos_type))
		{
			die("Specify a pos_type in the init.php");
		}
		
		if(!file_exists("libraries/class.field_mapping.php"))
		{
			die("Missing library file libraries/class.field_mapping.php");
		}

		require_once "libraries/class.field_mapping.php";
		
		if(!class_exists("field_mapping"))
		{
			die("Missing library class in field_mapping");
		}
		
		//include the main class if there is one. Not all libraries will have a shared core class.
		if(file_exists("libraries/class.rdi_{$library}_export.php"))
		{
			require_once "libraries/class.rdi_{$library}_export.php";
		}
		
		if(!file_exists("libraries/cart_libs/{$cart_type}/{$cart_type}_rdi_cart_{$library}_export.php"))
		{
			die("Missing library file libraries/cart_libs/{$cart_type}/{$cart_type}_rdi_cart_{$library}_export.php");
		}
		
		if(!file_exists("libraries/pos_libs/{$pos_type}/{$pos_type}_rdi_pos_{$library}_export.php"))
		{
			die("Missing library file libraries/pos_libs/{$pos_type}/{$pos_type}_rdi_pos_{$library}_export.php");
		}
		
		require_once "libraries/cart_libs/{$cart_type}/{$cart_type}_rdi_cart_{$library}_export.php";
		
		require_once "libraries/pos_libs/{$pos_type}/{$pos_type}_rdi_pos_{$library}_export.php";
		
		if(!class_exists("rdi_pos_{$library}_export"))
		{
			die("Missing library class in libraries/cart_libs/{$cart_type}/{$cart_type}_rdi_cart_{$library}_export.php");
		}
				
		if(!class_exists("rdi_pos_{$library}_export"))
		{
			die("Missing library class in libraries/cart_libs/{$cart_type}/{$cart_type}_rdi_cart_{$library}_export.php");
		}
		
		//pos is the final child class.
		$class_name = "rdi_pos_{$library}_export";
			
		return new $class_name($db,$library);
	}
	
	public function mark_exported($id){echo "Marking orders not implimented {$id}!"; }
	
	//copy the method from CP to insert into a table here.
}
?>