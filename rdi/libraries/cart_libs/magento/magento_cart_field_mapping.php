<?php

if(!class_exists("cart_field_mapping"))
{
    class cart_field_mapping extends rdi_general
    {
        public $attribute_ids;
        public $attributes;

            public function get_attribute($attribute_name, $entity_type_code = "catalog_product", $extended = false)
        {
                    if(!isset($this->attributes[$entity_type_code]))
                    {
                            $this->attributes[$entity_type_code] = $this->db_connection->rows("SELECT et.entity_type_code, ea.*
                                                        FROM {$this->prefix}eav_attribute ea
                                                        INNER JOIN {$this->prefix}eav_entity_type et
                                                        ON et.entity_type_id = ea.entity_type_id
                                                        AND et.entity_type_code = '{$entity_type_code}'","attribute_code");
                    }

                    if(isset($this->attributes[$entity_type_code][$attribute_name]))
                    {
                            return $extended?$this->attributes[$entity_type_code][$attribute_name]:$this->attributes[$entity_type_code][$attribute_name]['attribute_id'];
                    }

                    return '0';

        }
    }
}