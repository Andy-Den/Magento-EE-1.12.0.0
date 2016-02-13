<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Test Connection
 *
 * This simple script is run at the start of polling to make sure the
 * scripts are accessible and they can connect to the DB
 *
 * PHP version 5
 *
 * @author     Tom Martin <tmartin@retaildimensions.com>
 * @author     Ken Cobun <kcobun@retaildimensions.com>
 * @copyright  2006-2015 Retail Dimensions Inc.
 * 
 * 
 * @package Core\Import
 */

require 'init.php';
$benchmarker->set_start(basename(__FILE__), "load");
$sql = 'SELECT
          COUNT(*)
        FROM rpro_in_styles';

$cart->get_db()->exec($sql);

echo 'success';
$benchmarker->set_end(basename(__FILE__), "load");
?>