<?php
$installer = $this;

$installer->startSetup();

$installer->run("
CREATE TABLE {$this->getTable('signaturelink')} (
	`signature_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
	`order_id` int(11) NOT NULL,
	`sl_sid` char(52) NOT NULL,
	`tm_score` tinyint(4) DEFAULT NULL,
	`tm_risk` varchar(10) DEFAULT NULL,
	`created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY (`signature_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
");

$installer->endSetup();
