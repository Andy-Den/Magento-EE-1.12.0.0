<?php
$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE {$this->getTable('signaturelink_whitelist')} (
	`entity_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`whitelist_email` varchar(255) NOT NULL,
	`admin_responsible` varchar(50) NULL,
	`related_order` int(11) NULL,
	`active` tinyint(2) DEFAULT 0 NULL,
	`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`entity_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
