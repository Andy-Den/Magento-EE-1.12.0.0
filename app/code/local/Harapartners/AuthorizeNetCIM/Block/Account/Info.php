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
 
class Harapartners_AuthorizeNetCIM_Block_Account_Info extends Mage_Directory_Block_Data{
	
	protected $_paymentProfile;
	
	protected $_months;
	
	protected function _getPaymentProfile() {
		if( ! $this->_paymentProfile ) {
			$this->_paymentProfile = Mage::registry('payment_profile_object');
		}
		return $this->_paymentProfile;
	}
	
	public function getPostUrl(){
		$paymentProfile = $this->_getPaymentProfile();
		if( $paymentProfile && $paymentProfile->getId() ) {
			return $this->getUrl("authorizenetcim/manage/editPost", array('_secure'=>true));
		} else {
			return $this->getUrl("authorizenetcim/manage/newPost", array('_secure'=>true));
		}
	}
	
	public function getBackUrl(){
		return $this->getUrl("authorizenetcim/manage/");
	}
	
    public function getPaymentProfileId(){
   		$paymentProfile = $this->_getPaymentProfile();
   		if( $paymentProfile ) {
   			return $paymentProfile->getId();
   		} else {
   			return null;
   		}
    }
    
    public function getDisplayCardNumber() {
    	//Authnet CIM require format of XXXX#### not xxxx-####
    	$paymentProfile = $this->_getPaymentProfile();
   		if( $paymentProfile ) {
   			return "xxxx-" . $paymentProfile->getData( "last4digits" );
   		} else {
   			return null;
   		}
    }
    
    public function getCardNumber(){
    	//Authnet CIM require format of XXXX#### not xxxx-####
    	$paymentProfile = $this->_getPaymentProfile();
   		if( $paymentProfile ) {
   			return "XXXX" . $paymentProfile->getData( "last4digits" );
   		} else {
   			return null;
   		}
    }
    
//	public function getExpireDate(){
//		$paymentProfile = $this->_getPaymentProfile();
//   		if( $paymentProfile ) {
//   			return "XXXX";
//   		} else {
//   			return null;
//   		}
//    }
    
	public function getFirstName(){
		$paymentProfile = $this->_getPaymentProfile();
   		if( $paymentProfile ) {
   			return $paymentProfile->getData( "first_name" );
   		} else {
   			return null;
   		}
    }
	
	public function getLastName(){
		$paymentProfile = $this->_getPaymentProfile();
   		if( $paymentProfile ) {
   			return $paymentProfile->getData( "last_name" );
   		} else {
   			return null;
   		}
    }
    
	public function getCompany(){
		$paymentProfile = $this->_getPaymentProfile();
   		if( $paymentProfile ) {
   			return $paymentProfile->getData( "company" );
   		} else {
   			return null;
   		}
    }
    
	public function getAddress(){
		$paymentProfile = $this->_getPaymentProfile();
   		if( $paymentProfile ) {
   			return $paymentProfile->getData( "address" );
   		} else {
   			return null;
   		}
    }
    
	public function getCity(){
		$paymentProfile = $this->_getPaymentProfile();
   		if( $paymentProfile ) {
   			return $paymentProfile->getData( "city" );
   		} else {
   			return null;
   		}
    }
    
	public function getRegion(){
		$paymentProfile = $this->_getPaymentProfile();
   		if( $paymentProfile ) {
   			return $paymentProfile->getData( "region" );
   		} else {
   			return null;
   		}
    }
    
    public function getSavedRegionId(){
    	$paymentProfile = $this->_getPaymentProfile();
    	if( $paymentProfile ) {
   			return $paymentProfile->getData( "region_id" );
   		} else {
   			return null;
   		}
    }
    
	public function getZipcode(){
		$paymentProfile = $this->_getPaymentProfile();
   		if( $paymentProfile ) {
   			return $paymentProfile->getData( "zipcode" );
   		} else {
   			return null;
   		}
    }
    
	public function getCountry(){
		$paymentProfile = $this->_getPaymentProfile();
   		if( $paymentProfile ) {
   			return $paymentProfile->getData( "country" );
   		} else {
   			return null;
   		}
    }
    
	public function getPhoneNumber(){
		$paymentProfile = $this->_getPaymentProfile();
   		if( $paymentProfile ) {
   			return $paymentProfile->getData( "phone_number" );
   		} else {
   			return null;
   		}
    }
    
    public function getButtonText() {
    	$paymentProfile = $this->_getPaymentProfile();
   		if( $paymentProfile && $paymentProfile->getId() ) {
   			return Mage::helper( "authorizenetcim" )->__( "Update Credit Card" );
   		} else {
   			return Mage::helper( "authorizenetcim" )->__( "Save Credit Card" );
   		}
    }
    
	public function getTitle(){
		$paymentProfile = $this->_getPaymentProfile();
        if ($paymentProfile && $paymentProfile->getId()) {
            $title = Mage::helper( "authorizenetcim" )->__('Edit Credit Card');
        }
        else {
            $title = Mage::helper( "authorizenetcim" )->__('Add New Credit Card');
        }
        return $title;
    }
    
    public function getMonthSelectHtml(){
    	$paymentProfile = $this->_getPaymentProfile();
    	$monthMap = array(
    		"Jan"	=>		'01',
    		"Feb"	=>		'02',
    		"Mar"	=>		'03',
    		"Apr"	=>		'04',
    		"May"	=>		'05',
    		"Jun"	=>		'06',
    		"Jul"	=>		'07',
    		"Aug"	=>		'08',
    		"Sep"	=>		'09',
    		"Oct"	=>		'10',
    		"Nov"	=>		'11',
    		"Dec"	=> 		'12'
    	);
    	
    	$expireDateInfo = array();
    	if(!!$paymentProfile){
    		$expireDateInfo = explode('-', $paymentProfile->getExpireDate());
    	}
    	
		$monthSelectHtml = '<select name="cc_exp_month" id="cc_exp_month" class="validate-select" style="width:150px" ><option value="">' . $this->__('Month') . '</option>';
		
		foreach($monthMap as $key => $value){
			if(isset($expireDateInfo[1]) && $expireDateInfo[1] == (int)$value){
				$monthSelectHtml .= '<option selected="selected" value="' . $value . '">' . $key . '</option>';
			}else{
				$monthSelectHtml .= '<option value="' . $value . '">' . $key . '</option>';
			}
		}
		$monthSelectHtml .= '</select>';
        return $monthSelectHtml;
    }
    
    public function getYearSelectHtml(){
    	$paymentProfile = $this->_getPaymentProfile();
    	$yearMap = array();
    	$currentYear = date(Y);
    	//Allow current year
    	for($i = 0; $i < 10; $i++){
    		$yearMap[] = $currentYear;
    		$currentYear ++;
    	}
    	
    	$expireDateInfo = array();
    	if(!!$paymentProfile){
    		$expireDateInfo = explode('-', $paymentProfile->getExpireDate());
    	}
    	
		$yearSelectHtml = '<select name="cc_exp_year" id="cc_exp_year" class="validate-select" style="width:150px" ><option value="">' . $this->__('Year') . '</option>';
		
		foreach($yearMap as $value){
			if(isset($expireDateInfo[0]) && $expireDateInfo[0] == (int)$value){
				$yearSelectHtml .= '<option selected="selected" value="' . $value . '">' . $value . '</option>';
			}else{
				$yearSelectHtml .= '<option value="' . $value . '">' . $value . '</option>';
			}
		}
		$yearSelectHtml .= '</select>';
        return $yearSelectHtml;
    }
    
}