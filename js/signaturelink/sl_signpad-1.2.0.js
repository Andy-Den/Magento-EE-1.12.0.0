/*----------------------------------------------------------------
- Project: SignPad support 
- Note: Assumes jQuery is loaded 
- Note: Assumes slss is loaded 
- Note: Assumes gs is loaded 
----------------------------------------------------------------*/


//  main entry point of validation
function cbValidateSignPad(e, c)
{
	// slHelper.logToConsole('cbValidateSignPad');
	signpad.cbValidateSignPad(e, c);
}

var SLCapture = {

	_htmlDataId: "",
	_orderSubmitSetupFunc: null,
	_orderSubmitResetFunc: null,
	_extValidateFunc: null,

	init: function (form, htmlDataId, orderSubmitSetupFunc, orderSubmitResetFunc, externalValidate) {
		var that = this;

		this._htmlDataId = htmlDataId;
		this._orderSubmitSetupFunc = orderSubmitSetupFunc;
		this._orderSubmitResetFunc = orderSubmitResetFunc;
		this._extValidateFunc = externalValidate;

		var theTargetForm = $(form);
		theTargetForm.submit(function (evt) {
			if (that._orderSubmitSetupFunc) {
				that._orderSubmitSetupFunc();
			}
			var valid = false;
			if (that._extValidateFunc) {
				var v = that._extValidateFunc();
				if (typeof v === 'boolean') {
					valid = v;
				} else {
					valid = true; // bypass external validation if it doesn't return 'true' or 'false'
				}
			} else {
				valid = true; // so far
			}
			if (valid) {
				that.scrape();
			}

			if (that._orderSubmitResetFunc) {
				that._orderSubmitResetFunc();
			}

			if (valid) {
				return true;
			} else {
				evt.preventDefault();
				return false;
			}
		});
	},

	scrape: function () {
		var ssData = document.getElementById(this._htmlDataId);
		if (ssData) {
			ssData.value = slScrape.htmlEncode(this.getScrape());
		}
		return true;
	},

	getScrape: function () {
	    var htmlData = null;
		try {
			var replaceHtml5 = false, removeProfileTags = true;
			var spID = cnps._spIdPrefex;
			var flashDivID = spID + slHelper._suffixFlash, html5DivID = spID + slHelper._suffixHtml5;
			var profileDivID = null, removeScripts = true;
			var maskFields = cnps._captureOptions.signPadOptions.maskFields;

			if (!cnps._techFailed && cnps._captureOptions.signPadOptions.use) {
			    // display the Flash sign pad and hide the HTML5 version
			    replaceHtml5 = true;
			}
			htmlData = slScrape._getHTML(replaceHtml5, flashDivID, html5DivID, removeProfileTags, profileDivID, removeScripts, encodeBase64, maskFields);
		}
		catch (e) {

		}
		return htmlData;
	}
}


var signpadUtils = {

	_hasSufficientFlash: false,
	_canvasSupported: false,
	_padType: null,
	_spObj: null,
	_spObjId: null,
	_initalized: false,

	init: function (preferredType, signPadId, minFlashVersion) {

		this._hasSufficientFlash = slHelper.isFlashOK(minFlashVersion);
		this._canvasSupported = slHelper.isCanvasSupported(signPadId);
		this._padType = slHelper._typeNone;

		// Set padType based on preference and then compatibility
		if (this._hasSufficientFlash && this._canvasSupported) { 
			// both are supported, use preference to set
			this._padType = preferredType.toLowerCase() == slHelper._typeFlash ? slHelper._typeFlash : slHelper._typeHtml5;
		}
		else if (this._canvasSupported) {
			this._padType = slHelper._typeHtml5;
		}
		else if (this._hasSufficientFlash) {
			this._padType = slHelper._typeFlash;
		}
		else {
			// do nothing; keep _padType set to the default value
		}

		// set the sign pad object ID based on pad type we are using
		if (this._padType == slHelper._typeFlash)
			this._spObjId = signPadId + slHelper._suffixFlash;
		else if (this._padType == slHelper._typeHtml5)
			this._spObjId = signPadId + slHelper._suffixHtml5;

		this._initalized = true;
		this._spObj = null;
		this._spObj = this.getSignPad(); // document.getElementById(this._spObjId);

	},

	// function to get the flash object
	getSignPad: function () {

		var spObj;

		if (this._spObj) {
			spObj = this._spObj;
		}
		else if (this._initalized && this._padType != slHelper._typeNone) {
			if (this._padType == slHelper._typeFlash)
				spObj = this.getFlashObj();
			else if (this._padType == slHelper._typeHtml5)
				spObj = this.getHtml5Obj();
		}

		return spObj;
	},

	// function to get the flash object
	getFlashObj: function () {
		var fo = swfobject.getObjectById(this._spObjId);
		if (!fo) {
			if (window.document[this._spObjId]) {
				fo = window.document[this._spObjId];
			}
			if (navigator.appName.indexOf("Microsoft Internet") == -1) {
				if (document.embeds && document.embeds[this._spObjId])
					fo = document.embeds[this._spObjId];
			}
			else {
				fo = document.getElementById(this._spObjId);
			}
		}
		return fo;
	},

	// function to get the flash object
	getHtml5Obj: function () {
		var fo = document.getElementById(this._spObjId);
		return fo;
	}
}

var signpad = {

	_type: "flash",
	_signPadId: "",
	_spSLId: "",
	_htmlDataId: "",
	_orderSubmitSetupFunc: null,
	_orderSubmitResetFunc: null,
	_orderSubmitContFunc: null,
	_extValidateFunc: null,
	_timeout: 6000,
	_intervalIDs: [],
	_flashintervalID: null,
	_minDataPoints: 30,
	_coordSep: ',',
	_useSecure: true,
	_msgPleaseSign: 'Please sign your name with your mouse on the signature form',
	_hasCalledBack: false,
	_validResults: {
		_code: '',
		_err: '',
		_excpt: '',

		setCode: function (c) {
			if (c != "") {
				this._code = c;
			}
		},
		setError: function (e) {
			if (e != "") {
				this._err = e;
			}
		},
		setVals: function (error, code, exception) {
			this._code = code;
			this._err = error;
			this._excpt = exception;
		},
		clear: function () {
			this._code = "";
			this._err = "";
		},
		hasError: function () {
			return (this._err ? true : false);
		},
		hasCode: function () {
			return (this._code ? true : false);
		},
		hasException: function () {
			return (this._excpt ? true : false);
		},
		isEmpty: function () {
			return (this._code || this._err || this._excpt ? false : true);
		}
	},

	//  main entry point of validation
	cbValidateSignPad: function (e, c) {
		var that = signpad;
		var code = '';
		var err = '';

		that._hasCalledBack = true;
		that.stopInterval();

		if (signpadUtils._padType == slHelper._typeFlash) {
			code = c;
			err = e;
		}
		else if (signpadUtils._padType == slHelper._typeHtml5) {
			var resp;
			// slHelper.logToConsole('try to parse response');
			try {
				if (e != 'error') {
					if (c)
						resp = (c.code || c.errMsg ? c : eval('(' + c + ')'));
					else if (arguments.length > 2)
						resp = eval('(' + arguments[2].responseText + ')');

					slHelper.logToConsole('resp.code = ' + resp.code);
					code = resp.code;
					err = (resp.errMsg ? resp.errMsg : '');
				}
				else {
					code = '';
					err = e + ' - ' + arguments[2].status;
				}
			}
			catch (ex) {
				try {
					if (arguments.length > 2) {
						resp = eval('(' + arguments[2].responseText + ')');
						code = resp.code;
						err = (resp.errMsg ? resp.errMsg : '');
					}
				}
				catch (exc) {
					code = '';
					err = 'exception - ' + e + ' - ' + ex;
				}
			}
		}
		that.setSignResultsAndContinue(err, code);
	},

	//  main entry point of validation
	validateSignPad: function (spo) {
		var code = '', s = false;

		if (!this._validResults.hasError() && this._validResults.hasCode()) {
			// slHelper.logToConsole('this._validResults._code = ' + this._validResults._code);
			code = this._validResults._code;
		}
		else {
			if (signpadUtils._padType == slHelper._typeFlash) {
				try {
					code = spo.GetValidationCode('cbValidateSignPad');
					s = true;
					// set interval to check the FLASH pad for a result from the WS call
					// this._flashintervalID = setInterval(this.clearTimeout, this._timeout);
				}
				catch (ex) {
					slHelper.logToConsole(ex);
				}
			}
			else if (signpadUtils._padType == slHelper._typeHtml5) {
				// Do validation
				var strCoords = canvfuncs._oCoords.value;
				strCoords = strCoords.trim().replace(/[ ,]+/g, ",");

				// remove first "," if it is the first char of the string
				if (strCoords.indexOf(",") == 0) {
					strCoords = strCoords.substr(1);
					canvfuncs._oCoords.value = strCoords;
				}

				var coords = strCoords.trim().split(",");

				if (coords.length < this._minDataPoints * 2) {
					this._validResults.setVals(this._msgPleaseSign, '');
					alert(this._msgPleaseSign);
					return false;
				}

				SlSignSrv.setCoords(
					cnps._captureOptions.signPadOptions.companyID,
					cnps._captureOptions.signPadOptions.storeID,
					strCoords, //.replace(/\s+/g, this._coordSep), 
					this._useSecure,
					this.cbValidateSignPad,
					this.cbValidateSignPad
				);
				s = true;
			}

			if (s) {
				// set interval to cancel service call in the event they are running too long
				this._intervalIDs.push(setInterval(this.clearTimeout, this._timeout));
			}
			else {
				this.setSignResultsAndContinue('tech-failed', '');
			}
		}
		return code;
	},

	// function to validate fields
	validateFields: function () {
		var spo = signpadUtils.getSignPad(this._signPadId);
		var code = ""; // jQuery('#' + cnps._captureOptions.signPadOptions.spSLId).val();

		if (spo && !code) {
			if (spo.GetValidationCode) {
				code = this.validateSignPad(spo);
			} else { // when the External Interface for Flash is not allowed - permissions issue with the broswer
				this.procField();
				return true;
			}
		}
		else if (cnps._techFailed) {
			this.setSignResultsAndContinue('tech-failed', '');
		}
		if (code != "") {
			this.setSLId(code);
			this.procField();
			return true;
		}
		return false;
	},

	checkFlashSignPadForCode: function () {
		// slHelper.logToConsole('clearTimeout');

		// check if the flash pad has generated a SLID
		var code = '';
		if (signpadUtils._padType == slHelper._typeFlash) {
			try {
				var spo = signpadUtils.getSignPad(this._signPadId);
				code = spo.GetSLCode();
			}
			catch (ex) {
				slHelper.logToConsole(ex);
			}
			if (code != '') {
				// got it, call results function
				signpad.setSignResultsAndContinue('', code);
			}
		}
	},

	stopInterval: function () {
		// stop the interval
		if (this._intervalIDs && this._intervalIDs.length > 0) {
			var intervalID = this._intervalIDs[0];
			clearInterval(intervalID);
			this._intervalIDs.shift();
		}
		if (this._flashintervalID)
			clearInterval(this._flashintervalID);
	},

	clearTimeout: function () {
		//slHelper.logToConsole('clearTimeout');
		//alert('clearTimeout');

		signpad.stopInterval();
		if (!signpad._hasCalledBack) {
			// stop the jQuery AJAX call for the HTML5 version
			SlSignSrv.abort();

			var v = jQuery('#' + this._spSLId).val();
			if (!v || v.length > 12)
				return false;

			// set validResult with failure and run the client callback function
			var e = 'timeout', c = '';

			// one last try to see if the flash pad has generated a SLID
			var code = '';
			if (signpadUtils._padType == slHelper._typeHtml5) {
				code = this._validResults._code;
			}
			else if (signpadUtils._padType == slHelper._typeFlash) {
				try {
					var spo = signpadUtils.getSignPad(this._signPadId);
					code = spo.GetSLCode();
				}
				catch (ex) {
					slHelper.logToConsole(ex);
				}
			}
			if (code != '') {
				e = ''; c = code;
			}

			if (!signpad._hasCalledBack) {
				signpad._hasCalledBack = true;
				signpad.setSignResultsAndContinue(e, c);
			}
		}
	},

	setSignResultsAndContinue: function (e, c) {
		//alert('setSignResultsAndContinue = ' + c);

		this.stopInterval();
		this._validResults.setVals(e, c);
		this.setSLId(c);

		var funcName = (this._orderSubmitContFunc ? this._orderSubmitContFunc : cnps._captureOptions.signPadOptions.orderSubmitContFunc);
		var resetFunc = (this._orderSubmitResetFunc ? this._orderSubmitResetFunc : cnps._captureOptions.signPadOptions.orderSubmitResetFunc);

		resetFunc();
		try{
			window[funcName](e, c);
		}
		catch (ex) {
			funcName(e, c);
		}
	},

	setSLId: function (code) {
		jQuery('#' + this._spSLId).val(code);
	},

	procField: function () {
		var ssData = document.getElementById(this._htmlDataId);
		if (ssData) {
			var replaceHtml5 = false, removeProfileTags = true;
			var spID = cnps._spIdPrefex;
			var flashDivID = spID + slHelper._suffixFlash, html5DivID = spID + slHelper._suffixHtml5;

			if (!cnps._techFailed && cnps._captureOptions.signPadOptions.use) {
				// display the Flash sign pad and hide the HTML5 version
				replaceHtml5 = true;
			}
			ssData.value = slScrape.htmlEncode(slScrape._getHTML(replaceHtml5, flashDivID, html5DivID, removeProfileTags, null));
		}
		return true;
	},

	// functions in screenScraper package:

	//
	registerSignpad: function (type, form, signpadFileLoc, signpadInsertId, controlInputId, signPadId, companyID, storeID, customerID, spSLId, htmlDataId, orderSubmitSetupFunc, orderSubmitResetFunc, orderSubmitContFunc, externalValidate, useFlash, coordsID, readonly) {
		var that = this;

		this._type = type;
		this._signPadId = signPadId;
		this._spSLId = spSLId;
		this._htmlDataId = htmlDataId;
		this._orderSubmitSetupFunc = orderSubmitSetupFunc;
		this._orderSubmitResetFunc = orderSubmitResetFunc;
		this._orderSubmitContFunc = orderSubmitContFunc;
		this._extValidateFunc = externalValidate;
		this._form = form;

		this._formCaptureAndSetup = function (s) {
			// submit override
			var theTargetForm = jQuery('#' + s._form);
			theTargetForm.submit(function (evt) { // Used by Storefront and other carts
				// jQuery('#' + s._form).live('submit', function (evt) { // specfic for the Magento cart
				if (!signpad._validResults.hasError() && signpad._validResults.hasCode()) {
					// slHelper.logToConsole('this._validResults._code = ' + this._validResults._code);
					return true;
				} else {
					evt.preventDefault();
					return false;
				}

				/*
				if (s._orderSubmitSetupFunc) {
					s._orderSubmitSetupFunc(this);
				}
				var valid = false;
				if (s._extValidateFunc) {
					var v = s._extValidateFunc();
					if (typeof v === 'boolean') {
						valid = v;
					} else {
						valid = true; // bypass external validation if it doesn't return 'true' or 'false'
					}
				} else {
					valid = true; // so far
				}
				if (valid) {
					valid = s.validateFields();
				}

				if (s._orderSubmitResetFunc) {
					s._orderSubmitResetFunc(); //this);
				}

				if (valid) {
					return true;
				} else {
					evt.preventDefault();
					return false;
				}
				*/
			});
		};

		var inserted = false;

		if (signpadUtils._padType == slHelper._typeFlash) {
			that.fn = function (e) {
				// e.success (boolean), e.id, e.ref
				inserted = e.success;
				/*
				if (inserted) {
				that._formCaptureAndSetup(that);
				} else {
				// not inserted
				}
				*/
			};
			swfobject.registerObject(signPadId, "9", signpadFileLoc, that.fn);
			inserted = true;
			jQuery('#' + signPadId).css('visibility', 'visible');
		}
		else if (signpadUtils._padType == slHelper._typeHtml5) {
			slInitHTML5(signPadId, coordsID, readonly)
			inserted = true;
		}
		else {
			// not high enough flash version only grab the HTML
			var theTargetForm = jQuery(form);
			theTargetForm.submit(function (evt) {
				var valid = false;
				valid = that.procField();

				if (valid) {
					return true;
				} else {
					evt.preventDefault();
					return false;
				}
			});
		}
		if (inserted) {
			that._formCaptureAndSetup(that);
		}

		return inserted;
	}
}


var cnps = {

	_captureOptions: {
		signPadOptions: {
			use: true,
			type: 'flash',
			form: 'slForm',
			signpadFileLoc: '/flash/playerProductInstall.swf',
			controlInputId: 'sl_verify',
			signPadId: 'cnpsSignPad',
			signPadDivId: 'slpadDiv',
			companyID: 'tst001',
			storeID: '1',
			customerID: null,
			spSLId: 'spreturn',
			coordsID: 'slCmds',
			htmlDataId: '',
			orderSubmitSetupFunc: null,
			orderSubmitResetFunc: null,
			orderSubmitContFunc: 'submitCallback',
			externalValidate: null,
			minFlashVersion: '9.0.280',
			readonly: false,
			maskFields: null
		}
	},
	_termsConditionsOptions: { use: false, languageCodeControlID: '', languageCode: ''},
	_techFailed: false,
	_signPadUtils: null,
	_usingFlash: false,
	_usingHTML5: false,
	_jqSignPad: null,
	_jqSignPadDiv: null,
	_spIdPrefex: null,

	init: function (captureOptions, termsAndConditionsOptions) {
		if (captureOptions) {
			if (captureOptions.signPadOptions) {
				var pObj = captureOptions.signPadOptions;
				for (var propertyName in pObj) {
					slHelper.setProperty(this._captureOptions.signPadOptions, propertyName, pObj[propertyName], false);
				}
			}
		}
		if (termsAndConditionsOptions) {
			var pObj = termsAndConditionsOptions;
			for (var propertyName in pObj) {
				slHelper.setProperty(this._termsConditionsOptions, propertyName, pObj[propertyName], false);
			}
		}

		var signPadId = this._captureOptions.signPadOptions.signPadId;
		var signPadDivId = this._captureOptions.signPadOptions.signPadDivId;
		var preferredType = this._captureOptions.signPadOptions.type;
		var minFlashVer = this._captureOptions.signPadOptions.minFlashVersion;

		this._spIdPrefex = signPadId;

		if( this._captureOptions.signPadOptions.use ) 
		{
			signpadUtils.init(preferredType, signPadId, minFlashVer);
			this._captureOptions.signPadOptions.signPadId = signpadUtils._spObjId;
			this._techFailed = signpadUtils._spObj ? false : true;
			this._usingFlash = signpadUtils._padType == slHelper._typeFlash ? true : false;
			this._usingHTML5 = signpadUtils._padType == slHelper._typeHtml5 ? true : false;
			this._signPadUtils = signpadUtils;

			if( !this._techFailed ) 
			{
				this._jqSignPad = jQuery(signpadUtils._spObj);
				this._jqSignPadDiv = jQuery(this._jqSignPad).parents('div')[0];
				if (!this._jqSignPadDiv)
					this._jqSignPadDiv = jQuery('#' + signPadDivId + (this._usingFlash ? slHelper._suffixFlash : slHelper._suffixHtml5));
			}
		}
	},

	setTCOk: function (data) {
		if (data && data.Status == 1) {
			if (data.TermsAndConditionsStatus && data.TermsAndConditionsStatus == 'ENABLED') {
				var tc = data.TermsAndConditions;
				var rId = cnps._termsConditionsOptions.replacementID;

				if (data.TermsAndConditionsUploadDT) {
					tc = tc + " <br /> Last Modified: " + data.TermsAndConditionsUploadDT;
				}
				if (data.TermsAndConditionsCSSClassName) {
					jQuery('#' + rId).addClass(data.TermsAndConditionsCSSClassName);
				}
				if (data.TermsAndConditionsCSSAttr) {
					jQuery('#' + rId).attr('style', data.TermsAndConditionsCSSAttr);
				}
				// append T&Cs + change the id to prevent render append
				jQuery('#' + rId).append(tc).attr("id", rId + "_m");
				
				var tcLanguageCode = data.LanguageCode;
				jQuery('#' + cnps._termsConditionsOptions.languageCodeControlID).val(tcLanguageCode);				
			}
		}
	},

	setTCErr: function (data) {

	},

	registerSignpad: function () {

		if (this._captureOptions) {
			var spOpts = this._captureOptions.signPadOptions, opts = this._captureOptions.options;

			if (spOpts.use && !this._techFailed) {
				jQuery(this._jqSignPadDiv).css('display', 'block');
			}

			if (this._techFailed && spOpts.use) {
				SLCapture.init(spOpts.form, spOpts.htmlDataId, spOpts.orderSubmitSetupFunc, spOpts.orderSubmitResetFunc, spOpts.externalValidate);
			}
			else if (spOpts && spOpts.use) {
				var spOpts = spOpts;
				signpad.registerSignpad(
						spOpts.type,
						spOpts.form,
						spOpts.signpadFileLoc,
						spOpts.signpadInsertId,
						spOpts.controlInputId,
						spOpts.signPadId,
						spOpts.companyID,
						spOpts.storeID,
						spOpts.customerID,
						spOpts.spSLId,
						spOpts.htmlDataId,
						spOpts.orderSubmitSetupFunc,
						spOpts.orderSubmitResetFunc,
						spOpts.orderSubmitContFunc,
						spOpts.externalValidate,
						this._usingFlash,
						spOpts.coordsID,
						spOpts.readonly
					);
			}
			else if (opts && opts.capture) {
				SLCapture.init(opts.form, opts.htmlDataId, opts.orderSubmitSetupFunc, opts.orderSubmitResetFunc, opts.externalValidate);
			}
		}
	},

	loadAll: function () {
		var spo = (this._captureOptions && this._captureOptions.signPadOptions) ? true : false;
		var del = false;

		if (spo) {
			if (this._captureOptions.signPadOptions.loc) {
				if (this._captureOptions.signPadOptions.use) {
					if (this._techFailed) {
						del = true;
					}
				}
				else {
					del = true;
				}
			}
		}
		if (del == true)
			jQuery('#' + this._captureOptions.signPadOptions.loc).remove();

		if (this._termsConditionsOptions && this._termsConditionsOptions.use) {
			if (this._termsConditionsOptions.settingID && this._termsConditionsOptions.clientID && this._termsConditionsOptions.storeID) {
				var useSecure = (this._termsConditionsOptions.useSecure) ? this._termsConditionsOptions.useSecure : true;
				RetSettings.getSettings(this._termsConditionsOptions.settingID, this._termsConditionsOptions.clientID, this._termsConditionsOptions.storeID, useSecure, cnps.setTCOk, this._termsConditionsOptions.err,this._termsConditionsOptions.languageCode);
			}
		}
	}
}

var SlSignSrv = {
	_aVariablePlaceholder: "",
	_setCoordsBreak: false,
	_getCoordsBreak: false,
	_xhr: null,

	init: function () {
		var that = this;
	},

	abort: function (){
		if( this._xhr ) this._xhr.abort();
		return true; 
	},

	getCoords: function (clientID, storeID, slid, useSecure, f, err) {
		useSecure = false;
		var url = "slr.signaturelink.com/signservicealt.svc/r/gsc/" + clientID + "/" + storeID + "?slc=" + slid;
		url = (useSecure ? "https://" : "http://") + url;

		this._xhr = jQuery.ajax({
			type: 'GET',
			url: url,
			dataType: 'jsonp',
			error: function (e) {
				if (err) {
					err("error");
				}
			},
			success: function (data) {
				if (f) {
					if (data) {
						f(data);
					} else if (err) {
						err("no data");
					}
				}
			}
		});
	},

	setCoords: function (clientID, storeID, cords, useSecure, f, err) {
		useSecure = false;
		var that = this;
		var url = "slr.signaturelink.com/SignServiceAlt.svc/p/vsp";
		url = (useSecure ? "https://" : "http://") + url;
		this._setCoordsBreak = false;
		
		var agentString = slHelper.getUserAgent();
		var fV = swfobject.getFlashPlayerVersion();
		var flashVersion = fV.major + "." + fV.minor + "." + fV.release;

		var input =
		{
			clientid: clientID,
			storeID: storeID,
			signvalues: cords,
			userAgent: agentString,
			flashVersion: flashVersion
		};

		this._xhr = jQuery.ajax({
			// crossDomain: true,
			type: 'POST',
			url: url,
			contentType: 'text/json',
			data: JSON.stringify(input),
			success: function (data, status, jqXHR) {
				slHelper.logToConsole('2.success - response: ' + data);
				if (f) {
					if (data) {
						f(status, data, jqXHR);
					} else if (err) {
						err("no data", null, jqXHR, status);
					}
				}
			},
			error: function (jqXHR, status, error) {
				slHelper.logToConsole("2.error - Status\n-----\n" + jqXHR.status + '\n' + jqXHR.responseText);
				if (err) {
					err("error", null, jqXHR, status);
				}
			}
		});
	},

	drawSign: function (data) {
		//global search in string
		alert(data);
		data = data.replace(/\,/g, " ");
		canvfuncs.drawSignature(data);
	}
}
