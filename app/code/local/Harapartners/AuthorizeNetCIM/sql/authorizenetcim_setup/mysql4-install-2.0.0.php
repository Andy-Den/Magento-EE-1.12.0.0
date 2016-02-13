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

$eavSetup = new Mage_Eav_Model_Entity_Setup();
$eavSetup->addAttribute('customer', 'cim_customer_profile_id', array(
    'type'      	=> 'varchar',
    'label'     	=> 'CIM Customer Profile_ID',
    'input'     	=> 'text',
    'required'	=> 0
));

$installer = $this;
$installer->startSetup();

$installer->run("

-- DROP TABLE IF EXISTS {$this->getTable('authorizenetcim/profilemanager')};
CREATE TABLE {$this->getTable('authorizenetcim/profilemanager')}(
  `entity_id` int(10) unsigned NOT NULL auto_increment,
  `customer_payment_profile_id` int(10) NOT NULL,
  `customer_id` int(10) NOT NULL,
  `customer_profile_id` int(10) NOT NULL,
  `first_name` varchar(50) default '',
  `last_name` varchar(50) default '',
  `company` varchar(50) default '',
  `address` text default '',
  `city` varchar(50) default '',
  `region` varchar(50) default '',
  `region_id` int(10) default NULL,
  `zipcode` int(10) default NULL,
  `country` varchar(50) default '',
  `phone_number` varchar(50) default '',
  `last4digits` varchar(4) default '',
  `created_at` datetime default NULL,
  `updated_at` datetime default NULL,
  `status` int(3) default NULL,
  `expire_date` varchar(10) default NULL,
  PRIMARY KEY  (`entity_id`),
  UNIQUE `CUSTOMER_PAYMENT_PROFILE_ID` (customer_payment_profile_id),
  CONSTRAINT `FK_AUTHORIZENETCIM_PROFILEMANAGER_CUSTOMER` FOREIGN KEY (`customer_id`) REFERENCES {$installer->getTable('customer/entity')} (`entity_id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Authorize.Net CIM Profile';
");

$installer->endSetup();