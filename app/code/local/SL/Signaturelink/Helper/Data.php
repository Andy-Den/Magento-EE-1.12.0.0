<?php 
class SL_Signaturelink_Helper_Data extends Mage_Core_Helper_Abstract
{
	const XML_CONFIG_PATH = 'signaturelink/';
	const SL_SOAP_ENDPOINT = 'https://sls.signaturelink.com/generatepdf.svc?wsdl';

    public function isEnabled()
    {
		return $this->getConfig('sl_config', 'enabled', true);
    }

    /**
     * Checks if the customer (guest or registered) in the checkout flow has been added to the whitelist
     * @return bool
     */
    public function isWhitelisted($whitelistEmail = null)
    {
        //If an email isn't passed in, then we try to pull it out of the session
        if(is_null($whitelistEmail))
        {
            /** @var $session Mage_Checkout_Model_Session */
            $session = Mage::getSingleton('checkout/session');

            /** @var $quote Mage_Sales_Model_Quote */
            $quote = $session->getQuote();

            //We are getting the email address from the billing address - this is automatically set to the account email
            //address for a logged in customer
            $whitelistEmail = $quote->getBillingAddress()->getEmail();
        }

        $whitelisted = false;

        try{

            $whitelist = Mage::getModel('signaturelink/whitelist')
                            ->getCollection()
                            ->addFieldToFilter('whitelist_email', array('eq' => trim($whitelistEmail)))
                            ->addFieldToFilter('active', array('eq' => 1));

            $count = count($whitelist);

            if($count > 0)
            {
                $whitelisted = true;
            }

        }catch(Exception $e)
        {
            $whitelisted = false;
        }

        return $whitelisted;
    }

	public function generateSLSession()
	{  
		$session = Mage::getSingleton('checkout/session');
		return md5($session->getEncryptedSessionId() . ':' . $session->getQuoteId());
	}

	public function getConfig($section, $key, $flag = false) {
		$path = self::XML_CONFIG_PATH . $section . '/' . $key;

		if ($flag) {
			return Mage::getStoreConfigFlag($path);
		} else {
			return Mage::getStoreConfig($path);
		}
	}

	public function getFlashParams() {
		$clientId	= $this->getConfig('sl_config', 'clientid');
		$storeId	= $this->getConfig('sl_config', 'storeid');

		return (object) Array (
			'clientId'	=> $clientId,
			'storeId'	=> $storeId,
			'flashFile'	=> Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS) . 'signaturelink/Signremote.swf',
			'vars'		=> "CompanyID={$clientId}&amp;CustomerID={$storeId}&amp;fn=slForm&amp;ffuid=sid&amp;drawonload=y"
		);
	}

	public function isTCEnabled() {
		return $this->getConfig('sl_config', 'opt_tcbox', true);
	}

	public function isTMEnabled() {
		return $this->getConfig('tm_config', 'enabled', true);
	}

	public function getDisabledMessage() {
		return $this->getConfig('tm_config', 'tm_disabled_message');
	}

	public function threatPassed($sessionId)
    {
		$allowed = true;

        //TODO: check to see if the whitelist is enabled, and if given customer is whitelisted
		if ( $this->isTMEnabled() && ! $this->isWhitelisted() )
        {

			$enum = Array(
				1 => 'high',
				2 => 'medium',
				3 => 'neutral',
				4 => 'low'
			);

            Mage::getSingleton('checkout/session')->setQuarantine(false);

            //Let's check to see if we want to quarantine the order, because if so we want to
            //Add the threadId to the session so we can properly handle it later on in the process
            if($this->getConfig('tm_config', 'tm_quarantine_fraud_threshold') != 'none'){
                $profile = $this->getProfileData($sessionId);

                $threatId = array_search($profile->RiskRating, $enum);
                $configLevel = $this->getConfig('tm_config', 'tm_quarantine_fraud_threshold');
                $configLevel = array_search($configLevel, $enum);

                //Now we check to see if the thread level that came back was equal to or greater then the
                //value set to quarantine at
                if(intval($threatId) <= intval($configLevel))
                {
                    //If so, we are setting a session variable telling us to quanrantine the order
                    Mage::getSingleton('checkout/session')->setQuarantine(true);

                }
            }


		} else {
            Mage::getSingleton('checkout/session')->setQuarantine(false);
		}

        return $allowed;
	}

	public function getProfileData($sessionId) {
		$options = array(
			'exceptions'	=> true,
		);
		$soap = new SoapClient(self::SL_SOAP_ENDPOINT, $options);

		try {
			$msg = new StdClass();
			$msg->clientName	= $this->getConfig('sl_config', 'te_user');
			$msg->storeID		= $this->getConfig('sl_config', 'storeid');
			$msg->password		= $this->getConfig('sl_config', 'te_pass');
			$msg->additionalData= new StdClass();

			$msg->additionalData->ProfileSessionID = $sessionId;

			$msg = new SoapParam($msg, 'parameters');

			$ProfileQuery = $soap->ProfileQuery($msg)->ProfileQueryResult;

			return $ProfileQuery;
		} catch (Exception $e) {
			Mage::log($e->getMessage());
		}
	}

	public function generatePDF($order, $sid, $profileId, $html, $hdnReturnedLanguageCode) {
		$options = array(
			'exceptions'	=> true,
		);

		$soap = new SoapClient(self::SL_SOAP_ENDPOINT, $options);

		$payment = $order->getPayment();
		$transId = $payment->getCcTransId();
		$authCode = $payment->getApproval();

		if (!$transId) {
			$orderData = $order->getPayment()->getData();
			$additional = $orderData['additional_information']['authorize_cards'];
			$keys = array_keys($additional);
			$transId = $additional[$keys[0]]['last_trans_id'];
		}
		
		if (!$transId) {
			$transId = 'N/A';
		}

		try {
			$dateClass = new StdClass();
			$dateClass->DateTime = new SoapVar('2012-03-27T14:04:37', XSD_DATETIME);
			$dateClass->OffsetMinutes = new SoapVar(-300, XSD_SHORT);
			$date = $dateClass; //new SoapParam($dateClass, 'transactiondate');

			$msg = new StdClass();
			$msg->clientName	= $this->getConfig('sl_config', 'te_user');
			$msg->storeID		= $this->getConfig('sl_config', 'storeid');
			$msg->password		= $this->getConfig('sl_config', 'te_pass');
			$msg->html			= $html;
			$msg->additionalData= (object) Array (
				'OrderNumber'		=> $order->getIncrementId(),
				'OrderAmount'		=> $order->getGrandTotal(),
				'URL'				=> Mage::getBaseUrl(),
				'TransactionNumber'	=> $transId,
				'Authorization'		=> $authCode,
				'SigCode'			=> $sid,
				'CaptureIP'			=> isset($_SERVER['X_FORWARDED_FOR']) ? $_SERVER['X_FORWARDED_FOR'] : $_SERVER['REMOTE_ADDR'],
				'CaptureUserAgent'	=> $_SERVER['HTTP_USER_AGENT'],
				'ProfileSessionID'	=> $profileId,
				'TermsAndConditionsLanguageCode' => $hdnReturnedLanguageCode
			);

			$msg = new SoapParam($msg, 'parameters');
	
			$soap->GeneratePDFPlusData($msg);

		} catch (Exception $e) {
			Mage::log($e->getMessage());
		}
	}
	
	
	/*
	Function Name: MPIAuthentication
	@Description: This function is being use to return value to display/hide the threedsecure page. This function has been called on threedsecure_frame.php
	@param   $_GET data array
	@return  Object
	*/
	public function Show3DS($getArr) {
		$postdata = base64_decode($getArr['postdata']);
		$arrpostdata = explode("#", $postdata);
		$baseurl = Mage::getBaseUrl();
		$baseurlarr = explode("threedsecure_frame.php", $baseurl);	
		
			
		$tm_config = Mage::getStoreConfig('signaturelink/tm_config/enabled');
	
		$securbuy_based_risk_score = Mage::getStoreConfig('signaturelink/tm_config/securbuy_based_risk_score');
		
		$securebuy_min_mpi_amountval = Mage::getStoreConfig('signaturelink/tm_config/securebuy_min_mpi_amountbox');
	
		$signpad_allow_specific_country = Mage::getStoreConfig('signaturelink/tm_config/signpad_allow_specific_country');	
		
		
		if($signpad_allow_specific_country == '1'){
			$signpad_country_mode = Mage::getStoreConfig('signaturelink/tm_config/signpad_country_mode');			
		}
		
		
		$tm_quarantine_fraud_threshold = Mage::getStoreConfig('signaturelink/tm_config/tm_quarantine_fraud_threshold');
		$baseurlarr = explode("threedsecure_frame.php", $baseurl);
	
		$CreditCardNumber = $arrpostdata[0];
		$ExpMonth = $arrpostdata[1];
		
		if($ExpMonth < 10) {
			$ExpMonth = '0'.$arrpostdata[1];
		} else {
			$ExpMonth = $arrpostdata[1];
		}
		
		$ExpYear = substr($arrpostdata[2],-2);		
		$TransDisplayAmount = Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();			
		
		$enable_3d_secure = 0;
		$risk_score_flag = 0;
		$min_api_amountval_flag = 0;
		$country_allow_flag = 0;
		$customerShipData = Mage::getSingleton('checkout/session')->getQuote()
								  ->getShippingAddress()
								  ->getData();

		$customerBillData = Mage::getSingleton('checkout/session')->getQuote()
								  ->getBillingAddress()
								  ->getData();

					  
		$customerName = Mage::helper('customer')->getCustomerName();	
		$customerEmail = Mage::getSingleton('customer/session')->getCustomer()->getEmail();
		$currencyCode = Mage::app()->getStore($storeID)->getCurrentCurrencyCode();
		
		
		$unserInfoArr = array('TransactionID' => '', 'ProfileSessionID' => $slSession, 'account_login' => $customerEmail, 'password_hash' => '', 'account_number' => '', 'account_name' =>$customerName, 'account_email' => $customerEmail, 'account_telephone' => $customerBillData['telephone'], 'cc_number_hash' => '', 'cc_bin_number' =>'', 'account_address_street1' => $customerBillData['street'], 'account_address_street2' => '', 'account_address_city' => $customerBillData['city'], 'account_address_state' =>'', 'account_address_country' => $customerBillData['country_id'], 'account_address_zip' =>$customerBillData['postcode'], 'ship_address_street1' => $customerShipData['street'], 'ship_address_street2' => '', 'ship_address_city' => $customerShipData['city'], 'ship_address_state' => '', 'ship_address_country' => $customerShipData['country_id'], 'ship_address_zip' => $customerShipData['postcode'], 'ach_routing_number' =>'', 'ach_account_hash' => '', 'ssn_hash'=> '', 'drivers_licence_number_hash' => '', 'input_ip_address' => $_SERVER['REMOTE_ADDR'], 'transaction_amount' => $TransDisplayAmount, 'transaction_currency' => $currencyCode);	
		
		if($this->isSessionExist($slSession,$unserInfoArr) == false){			
			$threadMatrixArr =  $this->getThreadMatrix($unserInfoArr);
			$_SESSION['profileSessionData']['TMResponse'] = serialize( $threadMatrixArr );		
		}else{		
			$threadMatrixArr = unserialize( $_SESSION['profileSessionData']['TMResponse'] );
		}
		
		
		switch ($tm_config) {
			case 0:
				$enable_3d_secure = 0;
				break;		
			case 1:
				$enable_3d_secure = 1;
				break;
			case 2:
				if(!empty($securbuy_based_risk_score)) {
					if($threadMatrixArr->PolicyScore >= $securbuy_based_risk_score) {
						$risk_score_flag = 1;					
					}
				} else {
					$risk_score_flag = 1;
				}

				if(!empty($securebuy_min_mpi_amountval)) {	
					if($TransDisplayAmount >= $securebuy_min_mpi_amountval) {
						$min_api_amountval_flag = 1;
					} 
				} else {
					$min_api_amountval_flag = 1;
				}
			
				if($signpad_allow_specific_country == 1){
					$user_select_country = $customerBillData['country_id'];
					if(empty($user_select_country)){
						$user_select_country = $customerShipData['country_id'];
					}
					$signpad_country_mode = Mage::getStoreConfig('signaturelink/tm_config/signpad_country_mode');
					$signpad_country_mode_arr = explode(',',$signpad_country_mode);
					if(in_array($user_select_country, $signpad_country_mode_arr)){
						$country_allow_flag = 1;
					}
				} else {
					$country_allow_flag = 1;
				}

				if(($risk_score_flag == 1) && ($min_api_amountval_flag == 1) && ($country_allow_flag == 1)) {	
					$enable_3d_secure = 1;
				}
				break;	
			default:
				$enable_3d_secure = 1;		
		}
		Mage::getSingleton('customer/session')->setThreeDSecure($enable_3d_secure);
		return $enable_3d_secure;
	}

	 /*
	 Function Name: MPIAuthentication
	 @Description: This function is being use to return MPI response. This function has been called on threedsecure_frame.php
	 @param   $_GET data array
	 @return  Object
	 */
	public function MPIAuthentication($getArr) { 
		$postdata = base64_decode($getArr['postdata']);
		$arrpostdata = explode("#", $postdata);
	
		$CreditCardNumber = $arrpostdata[0];
		$ExpMonth = $arrpostdata[1];
		
		if($ExpMonth < 10) {
			$ExpMonth = '0'.$arrpostdata[1];
		} else {
			$ExpMonth = $arrpostdata[1];
		}
		$ExpYear = substr($arrpostdata[2],-2);		
		$TransDisplayAmount = Mage::getModel('checkout/cart')->getQuote()->getGrandTotal();			

		$options = array(
				'exceptions'	=> false,
			);
			$client = new SoapClient("https://sls.signaturelink.com/MPIAuthentication.svc?wsdl", $options);
			try {
				$result = $client->DoMPIAuthentication(array('CreditCardNumber' => $CreditCardNumber,'ExpMonth' =>$ExpMonth,'ExpYear' => $ExpYear,'TransDisplayAmount' => $TransDisplayAmount));

				$strMD = $result->DoMPIAuthenticationResult->MD;
				$getACSURL = $result->DoMPIAuthenticationResult->ACSUrl;
				$strPaReq = $result->DoMPIAuthenticationResult->PaReq;
				
				if(($strMD == "") && ($getACSURL == "") && ($strPaReq == "")){
					echo "<span style='color:#EB340A;'>".$result->DoMPIAuthenticationResult->ReturnMessage."</span>";
					die;
				}

				$this->Handle3DSecureAuthentication($strMD, $getACSURL, $strPaReq);		
			} catch (Exception $e) {

				echo "Log Message: ".$e;
			}
	}

	/*
	Function Name: Handle3DSecureAuthentication
	@Description: This function is being use to authentication validation with MPI response.
	@param   strMD, $getACSURL, $strPaReq, $termUrl(All parameters Return from MPI response)
	@return  Redirect to RedirectToACS function
	*/
	
	public function Handle3DSecureAuthentication($strMD, $getACSURL, $strPaReq){
		if ($_SERVER['HTTPS'] == "ON") {
			$termUrl = "https://";
		} else {
			$termUrl = "http://";
		}
		$termUrl = $termUrl . $_SERVER["HTTP_HOST"];
		$termUrl = $termUrl . $_SERVER["PHP_SELF"] . "?final=true";
		
		$this->RedirectToACS($getACSURL, $strPaReq, $termUrl, $strMD);

	}

	/*
	Function Name: RedirectToACS
	@Description: This function is being use to redirect to MPI website with term URL  .
	@param   $strASCUrl, $strPaReq, $strTermUrl, $strMD(All parameters return from MPI response)
	@return  Redirect to MPI
	*/

	public function RedirectToACS($strASCUrl, $strPaReq, $strTermUrl, $strMD){

	echo '<script type="text/javascript">
	   
	   function OnLoadEvent() { document.downloadForm.submit(); }
	  
	   </script>
	   <body OnLoad="OnLoadEvent();">
		  <form name="downloadForm" action="'.$strASCUrl.'" method="POST" >
			 <INPUT type="hidden" name="PaReq"   value="'.$strPaReq.'"    >
			 <input type="hidden" name="TermUrl" value="'.$strTermUrl.'"  >
			 <input type="hidden" name="MD"      value="'.$strMD.'"       >
		  </form>
	   </body>';

	}  

	/*
	Function Name: HandleACSResponse
	@Description: This function is being use to return MPI response .
	@param   MPI Authentication Array
	@return  Object
	*/

	public function HandleACSResponse() {

		$MD = $_REQUEST["MD"];
		$PaRes = $_REQUEST["PaRes"];
		$authenticationStatus = "";
		$pass3DS    = "0";
		$baseurl = Mage::getBaseUrl();
		$baseurlarr = explode("threedsecure_frame.php", $baseurl);	
		$soap = new SoapClient('https://sls.signaturelink.com/MPIAuthentication.svc?wsdl');
	   
	   try {
			
			$rs = $soap->GetMPIAuthentication(array('MD' => $MD, 'PaRes' => $PaRes, 'ClientID' => NULL));
			$authenticationCAVV = $rs->GetMPIAuthenticationResult->AuthenticationCAVV;
			$authenticationCAVVAlgorithm = $rs->GetMPIAuthenticationResult->AuthenticationCAVVAlgorithm;
			$authenticationECI = $rs->GetMPIAuthenticationResult->AuthenticationECI;
			$authenticationStatus = $rs->GetMPIAuthenticationResult->AuthenticationStatus;
			$transactionId = $rs->GetMPIAuthenticationResult->TransactionId;

		 if($authenticationStatus == "Y" || $authenticationStatus == "A")
		 {
			$pass3DS = "1";
	 
			Mage::getSingleton('customer/session')->setMyCustomData($authenticationCAVV);
			Mage::getSingleton('customer/session')->setMyCustomData1($authenticationECI);
			Mage::getSingleton('customer/session')->setMyCustomData2($transactionId);	
			
		    echo "<script language='javascript'>
			      parent.closeOpenedIframe('success', '".$baseurlarr[0]."');
		          </script>";
		
		 }
		 else
		 {
		   echo "<script language='javascript'>
			     parent.closeOpenedIframe('fail', '".$baseurlarr[0]."');
			     </script>";
		 }
	   }
	   catch(Exception $e)
	   {
		 $authStatus =  "Log Message: ".$e;
		 echo "<script language='javascript'>
			   parent.closeOpenedIframe('fail', '".$baseurlarr[0]."');
			   </script>"; 
	   }
	}    //end  HandleACSResponse
	
	/*
	Function Name: getThreadMatrix
	@Description: This function is being use to return ProfileQuery.
	@param   Cuntomer Info Array
	@return  Object
	*/
	public function getThreadMatrix($unserInfoArr){
		$options = array(
			'exceptions'	=> true,
		);
		$client = new SoapClient("https://sls.signaturelink.com/generatepdf.svc?wsdl", $options);
		try {
			$msg = new StdClass();
			
			$clientName = Mage::getStoreConfig('signaturelink/sl_config/te_user');
			$storeID = Mage::getStoreConfig('signaturelink/sl_config/storeid');
			$password = Mage::getStoreConfig('signaturelink/sl_config/te_pass');
			
			$msg->clientName =	"$clientName";
			$msg->storeID = 1;
			$msg->password = "$password";
			
			$msg->additionalData= (object) $unserInfoArr;
			$msg = new SoapParam($msg, 'parameters');
			$result = $client->ProfileQuery($msg)->ProfileQueryResult;
			return $result;
		} catch (Exception $e) {
			Mage::log($e->getMessage());
		}
	}

	/*
	Function Name: isSessionExist
	@Description: This function is being use to check velocity.
	@param   Profile sessionId,Customer info Array
	@return  SessionId
	*/
	public function isSessionExist( $sessionId , $additionalData ) {
		$isSession = true;
		if($_SESSION['userSessionId'] != "") {
			if($sessionId != $_SESSION['userSessionId']) {
				$isSession = false;
			}else{
				foreach($additionalData as $key => $val) {
					if(strtolower($_SESSION['profileSessionData'][$key]) != strtolower($val)) {
						$isSession = false;
						Mage::log('profile session data mismatch for sessionId- '.$sessionId . 'is- ' . $_SESSION['profileSessionData'][$key] . ' = ' . $val);
						break;
					}
				}
			}
		}if($isSession == false || $_SESSION['userSessionId'] == ""){	
			$isSession = false;	
			$_SESSION['userSessionId'] = $sessionId;
			foreach( $additionalData as $key => $val ) {
				$_SESSION['profileSessionData'][$key] = $val;
			}				
		}		
		return $isSession;
	}

	/**
	 Function Name: GetHOPClientID
	 @Description: This function is being use to get hop client id. This function has been called on threedsecure_frame.php to validate the values of client id entered by admin.
	 @param   hop_username, hop_pass, store_id.
	 @return  hop cleint id
	*/ 
	public function GetHOPClientID($hop_username, $hop_pass, $store_id){
		$clientName = $hop_username;
		$storeID = $store_id;
		$password = $hop_pass;

		$options = array(
		'exceptions'	=> true,
		);
		$client = new SoapClient("https://sls.signaturelink.com/getsettings.svc?wsdl", $options);

		try {
			$msg = new StdClass();
			
			//GetHOPClientID
			$msg->clientName =	$clientName;
			$msg->storeID = $storeID;
			$msg->password = $password;
			$msg = new SoapParam($msg, 'parameters');
			$result = $client->GetHOPClientID($msg);
			$hopClientId = $result->GetHOPClientIDResult;
			return $hopClientId;
		} catch (Exception $e) {
			return $e;
		}
	}
	
}
