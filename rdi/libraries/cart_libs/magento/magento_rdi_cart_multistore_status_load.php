<?php

/**
 * does not do anything yet. 01172013
 * @package Core\Multistore\Magento
 */


class rdi_multistore_status_load extends rdi_general {
    
    public $pos;
    
    public function rdi_multistore_status_load($db = ''){
        
        $this->check_order_lib_version(); 
        
        if ($db){
            $this->set_db($db); 
        }
    }
    
    public function pre_load()
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("cart_multistore_pre_load");
        
        return $this;
    }
    
    public function post_load()    
    {
        global $hook_handler;       
        
        $hook_handler->call_hook("cart_multistore_post_load");
        
        return $this;
    }
    
    public function load()
    {
        $this->pre_load()->load_multistore()->post_load();
        
        return $this;
    }
    
    public function load_multistore()
    {
        global $pos;
        
        $this->pos = $pos->get_processor("rdi_multistore_status");
        
        $this->insert()->update()->extra();
    }
    
    public function insert()
    {
        $this->db_connection->rows("SELECT {$fields} FROM {$this->pos::STORE_TABLE} {$this->pos->STORE_TABLE_ALIAS}");
        return $this;
    }
    
    public function update()
    {
        
        return $this;
    }
    
    public function extra()
    {
        
        return $this;
    }
    
    
}


?>
