<?php
/*
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User Software Agreement (EULA).
 * It is also available through the world-wide-web at this URL:
 * http://www.harapartners.com/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to eula@harapartners.com so we can send you a copy immediately.
 * 
 */

/**
 * RMA model Extended to have an event prefix
 *
 * @category   Enterprise
 * @package    Enterprise_Rma
 * @author     s.hoffman <s.hoffman@harapartners.com>
 */
class Harapartners_HpIntWms_Model_Rewrite_Enterprise_Rma_Rma extends Enterprise_Rma_Model_Rma
{
    protected $_eventPrefix = 'enterprise_rma';
    protected $_eventObject = 'rma';
}
