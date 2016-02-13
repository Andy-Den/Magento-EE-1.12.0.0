/**
 * File Name: sl_aim.js
 * Description:
 * This file is used to describe the foloowing functionality:
   1. Show and hide 3D Secure page in iframe using closeOpenedIframe() function.
   2. Hide 3D Secure page iframe using show3DSIframe() function.
   3. Encode the credit card number, expiry date and amount using base64Encode_aim() function.
   4. If 3D Secure config setting is enable from admin then call the paymentSubmit() function and validate all the form details. If validation return true then call show3DSIframe() function to dispaly iframe.
 *
 * Author         :Q3 Technologies (http://www.q3tech.com)
 * Last modified  :23 Apr 2013
 */

/*
Function Name: show3DSIframe
Description: This function is being use to show 3D Secure page link in iframe and encode the credit card number, expiry date and amount using base64Encode_aim() function 
*/

function show3DSIframe(url, grandTotal) {
	jQuery('#sb3dFrame').attr('src', '');
	//Setting function on focus on credit card field  
	jQuery('input[type=text][name="payment[cc_number]"]').focus(function() {
		
		jQuery('#sb3dFrame').attr('src', '');
		jQuery("#sb3dDiv").hide();
		jQuery("#sl_verify").css('display','block');
	 
	 });
	
	//Setting function on change of credit card type  
	jQuery('select').change(function() {
		
		jQuery('#sb3dFrame').attr('src', '');
		jQuery("#sb3dDiv").hide();
		jQuery("#sl_verify").css('display','block');
	 
	 });

	//Setting function on change of payment method  
	 jQuery('input[name="payment[method]"]').change(function() {
		
		jQuery('#sb3dFrame').attr('src', '');
		jQuery("#sb3dDiv").hide();
		jQuery("#sl_verify").css('display','block');
		
	 
	 });

	  //Setting function on click of shipping method
	  jQuery('.validation-passed').click(function() {
		
		jQuery("#sb3dDiv").hide();
		jQuery("#sl_verify").css('display','block');
		
	 
	 });

	var ccNum = jQuery('#aim_cc_number').val();
	var ccExpMonth = jQuery('#aim_expiration').val();
	var ccExpYear = jQuery('#aim_expiration_yr').val();
	var transAmount = grandTotal;
	var transDisplayAmount = grandTotal;	
	var mcString = ccNum+"#"+ccExpMonth+"#"+ccExpYear+"#"+transAmount+"#"+transDisplayAmount;
	var htmlData = base64Encode_aim(mcString);

	threedIframe = getIframeWindow(document.getElementById("sb3dFrame"));

	if(threedIframe)
	{
		
		jQuery('#sb3dFrame').attr('src', url+'threedsecure_frame.php?postdata='+htmlData);		
		jQuery('#sb3dDiv').show();
		jQuery('#sl_verify').css('display','none');
		
	}
	window.scroll(0,window.innerHeight);
	
}

/*
Function Name: emptyFrame
Description: worked on back button to empty IFRame src
*/
function emptyFrame(){	
	jQuery('#sb3dFrame').attr('src', '');
}

/*
Function Name: getIframeWindow
Description: 
*/

function getIframeWindow( iframe_object ) {
	var doc;

	if (iframe_object.contentWindow)
		return iframe_object.contentWindow;

	if (iframe_object.window)
		return iframe_object.window;

	if (!doc && iframe_object.contentDocument)
		doc = iframe_object.contentDocument;

	if (!doc && iframe_object.document)
		doc = iframe_object.document;

	if (doc && doc.defaultView)
		return doc.defaultView;

	if (doc && doc.parentWindow)
		return doc.parentWindow;

	return null;
}

/*
Function Name: closeOpenedIframe
Description: This function is being use to close iframe after submitting 3D Secure form from iframe. If returned value is true then close iframe and proceed further. If returned value is flase then display the error message and hide "Continue" button.
*/

function closeOpenedIframe(status, url) {
	//Q3 Changes
	jQuery('#sb3dDiv iframe').hide();
	if(status=='success') { 
		jQuery('#sl_verify').css('display','');
		payment.save();
		//jQuery('#sl_verify').click();
	} else {
		jQuery('#3d-failure-msg').html("3D Secure Authentication Failed.");
		jQuery('#sl_verify').css('display','none');
	}
	//Q3 Changes	
}

/*
Function Name: getIframeWindow
Description: 
*/

function open3dpage(url) { 
	var aim_cc_number = jQuery('#aim_cc_number').val();
	var aim_expiration = jQuery('#aim_expiration').val();
	var aim_expiration_yr = jQuery('#aim_expiration_yr').val();
	var mcString = aim_cc_number+"#"+aim_expiration+"#"+aim_expiration_yr;
	var htmlData = base64Encode(mcString);
	w1 = window.open(url+'threedsecure.php?postdata='+htmlData+'&3dopen=yes', '_blank');
}

/*
Function Name: getIframeWindow
Description: 
*/

function closeOpenedWindow(status) {
	w1.close();
	if(status=='success') {
		jQuery('#3d-failure-msg').html('<img src="loading.png/>');
		review.save();
	} else {
		jQuery('#3d-failure-msg').html("3D Secure Authentication Failed.");
	}	
}

/*
Function Name: getIframeWindow
Description: This function is being use to encode the data with base64 algorithm.
*/

function base64Encode_aim(str) {
	var CHARS = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/";
	var out = "", i = 0, len = str.length, c1, c2, c3;
	while (i < len) {
		c1 = str.charCodeAt(i++) & 0xff;
		if (i == len) {
			out += CHARS.charAt(c1 >> 2);
			out += CHARS.charAt((c1 & 0x3) << 4);
			out += "==";
			break;
		}
		c2 = str.charCodeAt(i++);
		if (i == len) {
			out += CHARS.charAt(c1 >> 2);
			out += CHARS.charAt(((c1 & 0x3)<< 4) | ((c2 & 0xF0) >> 4));
			out += CHARS.charAt((c2 & 0xF) << 2);
			out += "=";
			break;
		}
		c3 = str.charCodeAt(i++);
		out += CHARS.charAt(c1 >> 2);
		out += CHARS.charAt(((c1 & 0x3) << 4) | ((c2 & 0xF0) >> 4));
		out += CHARS.charAt(((c2 & 0xF) << 2) | ((c3 & 0xC0) >> 6));
		out += CHARS.charAt(c3 & 0x3F);
	}
	return out;
}

/*
Function Name: getIframeWindow
Description: This function is being use to validate the form fields of payment section. If returned value is true the  display the iframe and proceed further. If returned value is flase then display the error message for the corresponding fields.
This function will get called when 3D Secure config setting is enabled from admin section.  
*/

function paymentSubmit(){
	
	function validateMe(){
		this.form = document.getElementById('co-payment-form');
		var validator = new Validation(this.form); 
		if (this.validate() && validator.validate()) { 
			return true;
		} else{
			return false;
		}
	}
	var payment = new Payment();
	if(validateMe.call(payment)){ 
		var secureCardValidate = jQuery('#secure-card-validation').val();
		var checkenable        = jQuery('#checkenabel').val();
		/* Payment gateway disable related to 3D-secure */
		if(checkenable == 1) {
			if((document.getElementById('p_method_aim').checked) && secureCardValidate=='1'){
				var baseURL = jQuery('#baseURL').val();
				var grandTotal = jQuery('#grandTotal').val();
				show3DSIframe(baseURL,grandTotal);
				var paymentSubmit = jQuery('#payment-submit-3d').val();
				if(paymentSubmit == '') { 
					jQuery('#payment-submit-3d').val('moneris_pay');
					return false;
				} 
			}
		} else {
				payment.saveUrl = opcSavePaymentUrl;
				payment.save();
		}

	} 
}


function submitSLwithout3d(form) 
{
	var spo, err, code, slid;

	try
	{
		spo = cnps._captureOptions.signPadOptions;
		// checkout.setLoadWaiting('payment');

		slid = jQuery('#' + spo.ffuid).val();
		// set validResult with failure and run the client callback function
		err = ( spo.use && cnps._techFailed ? 'tech-failed' : 'not-used' );
		code = ( slid ? slid : '' );

		if( spo.use && !cnps._techFailed )
		{
			var valid = false;
			if( spo.orderSubmitSetupFunc ) {
				spo.orderSubmitSetupFunc();
			}
			if( spo.extValidateFunc ) {
				var v = spo.extValidateFunc();
				if (typeof v === 'boolean') {
					valid = v;
				} else {
					valid = true; // bypass external validation if it doesn't return 'true' or 'false'
				}
			} else {
				valid = true; // so far
			}
			if( valid ) {
				valid = signpad.validateFields();
			}

			if( signpad._validResults.hasCode() || signpad._validResults.hasError() ) {
				if( spo.orderSubmitResetFunc ) {
					spo.orderSubmitResetFunc();
				}
				if( signpad._validResults._err != signpad._msgPleaseSign ) {
					submitOrderContinue();
				}
			}
		}
	}
	catch ( ex )
	{
		err = ( spo.use && cnps._techFailed ? 'tech-failed' : 'not-used' );
		signpad.setSignResultsAndContinue(err, code);
	}
	
}