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

class Harapartners_AuthorizeNetCIM_Helper_EmailSender extends Mage_Core_Helper_Abstract{
	
	public function sendEmail($customerEmail){
		
		$templateId = Mage::getStoreConfig('payment/authorizenetcim/template');
		$name = Mage::getStoreConfig('payment/authorizenetcim/emailsender_name');
		$email = Mage::getStoreConfig('payment/authorizenetcim/email_address');
		$mailSubject = Mage::getStoreConfig('payment/authorizenetcim/email_subject');
		
		if(!$templateId || !$name || !$email || !$mailSubject){
			return false;
		}
	 
		$sender = Array('name'  => $name, 'email' => $email);	 
		$vars = Array('customer_email' => $customerEmail,
				'store_name' =>	Mage::app()->getStore()->getName()
		);

		$storeId = Mage::app()->getStore()->getId();
		
		try{
			$translate  = Mage::getSingleton('core/translate');
			Mage::getModel('core/email_template')
					->setTemplateSubject($mailSubject)
					->sendTransactional($templateId, $sender, $email, $name, $vars, $storeId);
			return true;
		}catch(Exception $e){
			return false;
		}
	}
}
	