// string helper; add the trim function
String.prototype.trim = function() {
	return this.replace(/^\s+|\s+$/g,"");
};
String.prototype.ltrim = function() {
	return this.replace(/^\s+/,"");
};
String.prototype.rtrim = function() {
	return this.replace(/\s+$/,"");
};
String.prototype.beginsWith = function (val) {
    return(this.indexOf(val) === 0);
};


var slHelper = {

	// ----------------------------------------------------
	// constants - DO NOT SET OR CHANGE 
	// ----------------------------------------------------
	_typeFlash: 'flash',
	_typeHtml5: 'html5',
	_typeNone: 'none',
	_suffixFlash: 'F9',
	_suffixHtml5: 'H5',

	logToConsole: function (msg) {
		try {
			console.log( msg );
		}
		catch (ex)
		{ }
	},

	getUserAgent: function () {
		return window.navigator.userAgent;
	},

	getProperty: function (o, prop, throwEx) {
		if (o[prop] !== undefined)
			return o[prop];
		else if (throwEx == true)
			throw new ReferenceError('The property ' + prop + ' is not defined on this object');
	},

	setProperty: function (o, prop, val, throwEx) {
		if (o[prop] !== undefined || throwEx == false)
			o[prop] = val;
		else if (throwEx == true)
			throw new ReferenceError('The property ' + prop + ' is not defined on this object');
	},

	isFlashOK: function (minFlashVersion) {
		var hasSufficientFlash = false;
		try {
			hasSufficientFlash = swfobject.hasFlashPlayerVersion(minFlashVersion);
		}
		catch (ex) {
			// do nothing
			hasSufficientFlash = false;
		}
		return hasSufficientFlash;
	},

	isCanvasSupported: function (signPadID) {
		var supported = false;
		try {
			var canvasSupported = !!window.HTMLCanvasElement;
			var canvas2DSupported = !!window.CanvasRenderingContext2D;

			supported = (canvasSupported && canvas2DSupported && this.isCorsSupported());
		}
		catch (ex) {
			// do nothing
			supported = false;
		}
		return supported;
	},

	isCorsSupported: function () {
		return jQuery.support.cors;
	},


	// determine if the page is on iphone or the like
	IsIPhoneOrLike: function () {
		var isiPhone = navigator.userAgent.match(/iPhone/i) !== null;
		var isiPad = navigator.userAgent.match(/iPad/i) !== null;
		var isiPod = navigator.userAgent.match(/iPod/i) !== null;
		var is_touch_device = 'ontouchstart' in document.documentElement;
		return is_touch_device || isiPad || isiPod || isiPhone;
	},

	// get a single touch event
	getTouchEvent: function (event) {
		return window.event.targetTouches[0];
	},

	// get the object's position relative to the window, and then correct for scrolling,
	ObjectPosition: function (obj) {
		var curleft = 0;
		var curtop = 0;

		if (obj.offsetParent) {
			do {
				curleft += obj.offsetLeft;
				curtop += obj.offsetTop;
			} while (obj = obj.offsetParent);
		}

		var scrollTop = 0;
		if (!slHelper.IsIPhoneOrLike()) {
			scrollTop = document.body.scrollTop;
			if (scrollTop == 0) {
				if (window.pageYOffset) {
					scrollTop = window.pageYOffset;
				}
				else {
					//scrollTop = (document.body.parentElement) ? document.body.parentElement.scrollTop : 0;
				}
			}
		}

		var scrollLeft = 0;
		if (!slHelper.IsIPhoneOrLike()) {
			scrollLeft = document.body.scrollLeft;
			if (scrollLeft == 0) {
				if (window.pageXOffset) {
					scrollLeft = window.pageXOffset;
				}
				else {
					//scrollLeft = (document.body.parentElement) ? document.body.parentElement.scrollLeft : 0;
				}
			}
		}
		return [curleft - scrollLeft, curtop - scrollTop];
	},

	wait: function (msecs) {
		var start = new Date().getTime();
		var cur = start
		while (cur - start < msecs) {
			cur = new Date().getTime();
		}
	}
};