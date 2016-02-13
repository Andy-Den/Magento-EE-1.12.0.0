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
class Harapartners_Service_Block_Rewrite_Review_Helper extends Mage_Review_Block_Helper
{
   protected $_availableTemplates = array(
        'default' => 'review/helper/summary.phtml',
        'short'   => 'review/helper/summary_short.phtml',
   		'hp_grid' => 'review/helper/summary_hp_grid.phtml',
   		'hp_list' => 'review/helper/summary_hp_list.phtml'
    );
    
    public function getReviewFormHtml(){
    	$reviewForm = $this->getLayout()->createBlock('review/form'); //Set template is automatic in the constructor
    	return $reviewForm->toHtml();
    }
    
    public function getReviewViewListHtml(){
    	$reviewViewList = $this->getLayout()->createBlock('review/product_view_list')->setTemplate('review/product/view/list.phtml');
    	return $reviewViewList->toHtml();
    }
   
}
