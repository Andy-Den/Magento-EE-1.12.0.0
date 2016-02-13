function displayTCs(data) { 
	sltc = data;
	if (data.TermsAndConditionsStatus == 'ENABLED') {
		jQuery('#sl_tc').val(jQuery.trim(data.TermsAndConditions));
		jQuery('#sl_tc_header').text(jQuery.trim(data.TermsAndConditionsUploadDT));
	}
}


function sl_processDom(swfData, sessionId) {
//<![CDATA[
	// swfobject.removeSWF('SignremoteF9');  // NOT SURE IF THIS IS NEEDED - THIS OBJ LIVES IN A HIDDEN DIV and THE LOGIC BELOW TO REMOVES HIDDEN DIVs

	jQuery('button.btn-checkout').each(function() {
		var onclick_captured = jQuery(this).data('onclick_captured');
		if( !onclick_captured ) {
			// need to capture the click event 
			jQuery(this).data('onclick', this.onclick);
			jQuery(this).data('onclick_captured', true);
			this.onclick = function(e) { return false; }
		}

		jQuery(this).click(function(evt) {
			var sl_spID  = cnps._spIdPrefex;
			var sl_spFID = sl_spID + slHelper._suffixFlash;
			var sl_spHID = sl_spID + slHelper._suffixHtml5;
			var htmlData = '';

			var $base = jQuery('<base href="' + slScrape.getBaseURL() + '">');
			var $meta = jQuery('<meta http-equiv="x-ua-compatible" content="IE=8">');

			if (jQuery.browser.msie) 
			{
				var bd = document.createElement('body');
				bd.innerHTML = document.getElementsByTagName('body')[0].outerHTML;
				bd.className = document.getElementsByTagName('body')[0].className;

				try {
					if( cnps._techFailed || !cnps._captureOptions.signPadOptions.use ) {
						// hide both the Flash and HTML5 sign pads
						jQuery(bd).find('#' + sl_spFID).parent().css('display', 'none');
						jQuery(bd).find('#' + sl_spHID).parent().css('display', 'none');
					}
					else if (cnps._captureOptions.signPadOptions.use) { 
						// display the Flash sign pad and hide the HTML5 version
						jQuery(bd).find('#' + sl_spFID).parent().css('display', 'block');
						jQuery(bd).find('#' + sl_spHID).parent().css('display', 'none');
					}
					jQuery(bd).find('div.zopim').each(function() { jQuery(this).remove(); });
				}
				catch( ex ) {}

				Prototype.Selector.select('#sl_div', bd).each(function(e) { $(e).remove() });  // not sure if this element(s) even exist ???
				Prototype.Selector.select('script', bd).each(function(e) { if (!e.src.match(/jquery/)) { $(e).remove() } });
				Prototype.Selector.select('div', bd).each(function(e) { 
					if (e && e.style) {
						if (e.style.display == 'none') {
							$(e).remove();
						}
					}
				});
				Prototype.Selector.select('#' + sl_spFID, bd)[0].replace('<object id="SignremoteConfirm" classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" width="400" height="200"><PARAM NAME="Movie" VALUE="' + swfData + '"><PARAM NAME="AllowScriptAccess" VALUE="always"></object>');

				var hd = document.createElement('head');

				if( $base ) {
					hd.appendChild( $base.get(0) );
				}
				if( $meta ) {
					hd.appendChild( $meta.get(0) );
				}

				Prototype.Selector.select('*', document.getElementsByTagName('head')[0]).each(function (e) { // clone all elements except non SL scripts
					var c = true;

					if( $(e).is('script') ) {
						var s = $(e).attr('src');
						if( s ) {
							if( !s.beginsWith('sl') ) {
								c = false;
							}
						}
						else
							c = false;
					}
					if( c == true )
						hd.appendChild(e.cloneNode());
				});

				var pS = document.documentElement.previousSibling;
				var docType = '<!DOCTYPE html PUBLIC "' + pS.publicId + '" "' + pS.systemId + '">'
				htmlData = docType + '<html>' + hd.outerHTML + bd.outerHTML + '</html>';
			} 
			else 
			{
				elem = document.createElement('html');
				elem.innerHTML = document.getElementsByTagName('html')[0].innerHTML;

				$elem = jQuery(elem);

				$elem.find('head').each(function (){
					if( $meta ) 
						jQuery(this).prepend($meta);
					if( $base ) 
						jQuery(this).prepend($base); 
				});


				try {
					if( cnps._techFailed || !cnps._captureOptions.signPadOptions.use ) {
						// hide both the Flash and HTML5 sign pads
						$elem.find('#' + sl_spFID).parent().css('display', 'none');
						$elem.find('#' + sl_spHID).parent().css('display', 'none');
					}
					else if (cnps._captureOptions.signPadOptions.use) { 
						// display the Flash sign pad and hide the HTML5 version
						$elem.find('#' + sl_spFID).parent().css('display', 'block');
						$elem.find('#' + sl_spHID).parent().css('display', 'none');
					}
					$elem.find('div.zopim').each(function() { jQuery(this).remove(); });
				}
				catch( ex ) {}

				$elem.find('div, textarea').each(function () { 
					if (jQuery(this).css('display') == 'none') {
						jQuery(this).remove();
					}
					else if (this.type === 'textarea') {
						jQuery(this).text(jQuery('#' + this.id).val());
					}
				});

				$elem.find('script').each(function() { jQuery(this).remove(); });

				htmlData = '<html>' + $elem.html() + '</html>'
			}

			//htmlData = SLCapture.getScrape();
			//var hObj = jQuery(htmlData);
			//jQuery(hObj).find('div.zopim').each(function() { jQuery(this).remove(); });
			// form#search_mini_form  input[name='q'] margin-left: -225px;
			// div.page = width: 1000px;
			// #checkoutSteps').find('li.section').
			// htmlData = jQuery(hObj).html();

			jQuery('#slCapture').removeAttr('disabled').val(base64Encode(htmlData));
			jQuery(this).data('onclick').call(this, evt);
		});
	});
	jQuery('#slSid').removeAttr('disabled');
	jQuery('#sid').val( jQuery('#slSid').val() );
 
	//jQuery('#slProfile').removeAttr('disabled').val(sessionId);
	jQuery('#slProfile').removeAttr('disabled').val(sessionId+","+jQuery('#hdnReturnedLanguageCode').val());
//]]>
}

function sl_fetchTC(clientId, storeId) {
	RetSettings.getSettings('tc', clientId, storeId, true, displayTCs);
}
