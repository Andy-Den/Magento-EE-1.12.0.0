<?php
// copyright notice
$RxQgNcbf = $this; $RxQgNcbf->startSetup();       $RxQgNcbf->run("

DROP TABLE IF EXISTS {$this->getTable('speedtax_log/error')};
CREATE TABLE {$this->getTable('speedtax_log/error')} (
  `log_id` int(10) unsigned NOT NULL auto_increment,
  `event` varchar(255) NOT NULL default '',
  `message` text NOT NULL default '',
  `result_type` varchar(255) NOT NULL default '',
  `address_shipping_from` text NOT NULL default '',
  `address_shipping_to` text NOT NULL default '',
  `customer_name` varchar(255) NOT NULL default '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Speedtax Error Log';
    "); $RxQgNcbf->run("
    DROP TABLE IF EXISTS {$this->getTable('speedtax_log/call')};
CREATE TABLE {$this->getTable('speedtax_log/call')} (
  `log_id` int(10) unsigned NOT NULL auto_increment,
  `event` varchar(255) NOT NULL default '',
  `result_type` varchar(255) NOT NULL default '',
  `invoice_num` varchar(255) NOT NULL default '',
  `gross` varchar(255) NOT NULL default '',
  `exempt` varchar(255) NOT NULL default '',
  `tax` varchar(255) NOT NULL default '',
  `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY  (`log_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Speedtax Call Log';
    "); $RxQgNcbf->endSetup(); 