<?php

$installer = $this;

$installer->startSetup();

$installer->run("
INSERT INTO {$this->getTable('sales_order_status')}
(status, label)
VALUES
('sl_authentication_failed', 'Authentication Failed')
");


$installer->endSetup();