/*----------------------------------------------------------------
- Project: SignPad support 
- Note: Assumes jQuery is loaded 
- Note: Assumes slss is loaded 
- Note: Assumes gs is loaded 
----------------------------------------------------------------*/


// external var for events (mouse, touch) to use
var canvfuncs;

function wait(msecs)
{
	var start = new Date().getTime();
	var cur = start
	while(cur - start < msecs)
	{
		cur = new Date().getTime();
	}
}


//  main entry point of validation
function cbValidateSignPad(e, c)
{
	slHelper.logToConsole('cbValidateSignPad');
	signpad.cbValidateSignPad(e, c);
}

function TipLayer(tipTaperFactor, fillColor) {
	this.init();
}
TipLayer.prototype =
{
	_ready: false,
	_tipTaperFactor: 0.8,
	_fillColor: "#000000",
	_lineThickness: null,
	_mouseX: null, 
	_mouseY: null,
	_smoothedMouseX: null,
	_smoothedMouseY: null,
	_cos1: null,
	_sin1: null,

	init: function(tipTaperFactor, fillColor)
	{
		if( tipTaperFactor )
			this._tipTaperFactor = tipTaperFactor;
		if( fillColor )
			this._fillColor = fillColor;
	},

	set: function(lineThickness, mouseX, mouseY, smoothedMouseX, smoothedMouseY, cos1, sin1, fillColor)
	{
		this._lineThickness = lineThickness;
		this._mouseX = mouseX;
		this._mouseY = mouseY;
		this._smoothedMouseX = smoothedMouseX;
		this._smoothedMouseY = smoothedMouseY;
		this._cos1 = cos1;
		this._sin1 = sin1;
		if( fillColor )
			this._fillColor = fillColor;
		this._ready = true;
	},

	drawEllipse: function(ctx, x, y, w, h) 
	{
		var x1 = x + w/2, y1 = y - h/2;
		var x2 = x - w/2, y2 = y + h/2;
  
		ctx.beginPath();
		ctx.moveTo(x, y1);
		ctx.bezierCurveTo(x1, y1, x1, y2, x, y2);
		ctx.bezierCurveTo(x2, y2, x2, y1, x, y1);
		ctx.fillStyle = this._fillColor;
		ctx.fill();
		ctx.closePath();	
	},

	drawOval: function(ctx, x, y, rw, rh)
	{
		ctx.save();
		ctx.scale(1,  rh/rw);
		ctx.beginPath();
		ctx.arc(x, y, r, 0, 2 * Math.PI);
        ctx.fillStyle = this._fillColor;
        ctx.fill();
		ctx.restore();
		ctx.lineWidth=1;
		ctx.stroke();  
	},

	draw: function(ctx, tipTaperFactor)
	{
		var taperThickness = tipTaperFactor * this._lineThickness;
		var startX = this._smoothedMouseX + this._cos1;
		var startY = this._smoothedMouseY + this._sin1;
		
		ctx.lineWidth = 1;
		
		//round tip:
		this.drawEllipse( ctx, this._mouseX-taperThickness+2, this._mouseY-taperThickness+2, 2*taperThickness, 2*taperThickness );
		
		
		//quad segment
		ctx.beginPath();
		ctx.moveTo(startX, startY);
		ctx.lineTo(this._mouseX + tipTaperFactor * this._cos1, this._mouseY + tipTaperFactor * this._sin1);
		ctx.lineTo(this._mouseX - tipTaperFactor * this._cos1, this._mouseY - tipTaperFactor * this._sin1);
		ctx.lineTo(this._smoothedMouseX - this._cos1, this._smoothedMouseY - this._sin1);
		ctx.lineTo(startX, startY);
        ctx.closePath();
        ctx.fillStyle = this._fillColor;
        ctx.fill();
		ctx.stroke();
	}
}



function SpDataPoints(pointsObj, points) {
	this.init(pointsObj, points);
}
SpDataPoints.prototype =
{
	data: null,
	lastPoint: null,
	_stroke: null,

	startStroke: function(point){
		//if(point && typeof(point.x) == "number" && typeof(point.y) == "number"){
			this._stroke = {'x':[point.relX], 'y':[point.relY]};
			this.data.push(this._stroke);
			this.lastPoint = point;
			// this.inStroke = true
			return point;
		// } else {
		//	return null
		//}
	},

	addToStroke: function(point){
		// if (this.inStroke && typeof(point.x) === "number" && typeof(point.y) === "number" ){
			this._stroke.x.push(point.relX);
			this._stroke.y.push(point.relY);
			this.lastPoint = point;
			return point;
		// } else {
		//	return null
		// }
	},

	endStroke: function(point){
		if( point ){
			this._stroke.x.push(point.relX);
			this._stroke.y.push(point.relY);
		}
		this.lastPoint = point;
		return true;
	},

	clearAll: function(){
		this.data = [];
	},

	removeStroke: function(){
		if (this.data.length) {
			this.data.pop();
		} 
	},

	denormalize: function(){
		var numofstrokes = this.data.length, stroke, numofpoints, denorm = '', x, y;

		for( var i = 0; i < numofstrokes; i++ )
		{
			stroke = this.data[i];
			x = stroke.x, y = stroke.y;
			numofpoints = x.length;

			if( numofpoints > 0 )
			{
				if( i != 0 )
					denorm += ',' + x[0] + ',' + y[0] + ',' + x[0] + ',' + y[0];
				else
					denorm = x[0] + ',' + y[0] + ',' + x[0] + ',' + y[0];

				for( var j = 1; j < numofpoints; j++ ){
					denorm += ',' + x[j-1] + ',' + y[j-1] + ',' + x[j] + ',' + y[j];
				}
			}
		}
		return denorm;
	},

	normalize: function(points, xCorrection, yCorrection)
	{
		if( points )
		{
			// split the commands
			var a = points.trim().split(",");
			var i = 0;

			// loop through and draw each command right away
			while (i + 4 < a.length) {
				var x1 = parseInt(a[i]) + xCorrection;
				var y1 = parseInt(a[i + 1]) + yCorrection;
				var x2 = parseInt(a[i + 2]) + xCorrection;
				var y2 = parseInt(a[i + 3]) + yCorrection;
				var pt  = {'relX':x2, 'relY':y2};

				if( x1==x2 && y1==y2 )
					this.startStroke( pt );
				else
					this.addToStroke( pt );

				i += 4;
			}
		}
		return this.data;
	},

	init: function(pointsObj, points)
	{
		if( pointsObj )
			this.data = pointsObj;
		else if( points )
			this.normalize( points, 0, 0 );
		else
			this.clearAll();
	},
}

/* SPCanvasFunctions: wrap all important functionality in this object*/

function SPCanvasFunctions(canvas, iSrc, button, coordsID, readonly) {
	this.Init(canvas, iSrc, button, coordsID, readonly);
}
SPCanvasFunctions.prototype =
{
	_WIDTH: 400,
	_HEIGHT: 198,

	prevMouseX: 0,
	prevMouseY: 0,

	cp1x: 0,
	cp1y: 0,
	cp2x: 0,
	cp2y: 0,

	canv: null,
	ctx: null,
	tip: null,
	dataPts: null,
	readonly: false,

	img: null,

	vertCorrection: -15,
	horiCorrection: 0,

	coordCnt: 0,
	oCoords: null,

	_button: '<input type="button" onclick="canvfuncs.closeHelp();" value="Close" />',
	_mouseOut: false,

	// ---------------------------------------------
	// Smooth drawing variables
	// ---------------------------------------------
	_lineColor: "#000000",
	_dotRadius: 3,

	_minThickness: 0.20,
	_thicknessFactor: 0.17,
	_smoothingFactor: 0.3, //Should be set to something between 0 and 1.  Higher numbers mean less smoothing.
	_thicknessSmoothingFactor: 0.3,
	_tipTaperFactor: 0.8,

	_mouseMoved: null, 
	_targetLineThickness: null,

	_startX: null,
	_startY: null,
	_smoothedMouseX: null,
	_smoothedMouseY: null,

	_lastSmoothedMouseX: null,
	_lastSmoothedMouseY: null,	
	_lastMouseX: null,
	_lastMouseY: null,
	_lastMouseChangeVectorX: null,
	_lastMouseChangeVectorY: null,
	
	_lastThickness: null,
	_lastRotation: null,
	_lineThickness: null,
	_lineRotation: null,

	drawEllipse: function (x, y, w, h, fillColor) {
		var x1 = x + w / 2, y1 = y - h / 2;
		var x2 = x - w / 2, y2 = y + h / 2;

		this.ctx.beginPath();
		this.ctx.moveTo(x, y1);
		this.ctx.bezierCurveTo(x1, y1, x1, y2, x, y2);
		this.ctx.bezierCurveTo(x2, y2, x2, y1, x, y1);
		this.ctx.fillStyle = fillColor;
		this.ctx.fill();
		this.ctx.closePath();
	},

	/* drawing related functions */
	drawStart: function (mouseX, mouseY) // return void 
	{
		this._startX = this._lastMouseX = this._smoothedMouseX = this._lastSmoothedMouseX = mouseX;
		this._startY = this._lastMouseY = this._smoothedMouseY = this._lastSmoothedMouseY = mouseY;
		this._lastThickness = 0;
		this._lastRotation = Math.PI / 2;
		this._lastMouseChangeVectorX = 0;
		this._lastMouseChangeVectorY = 0;

		// We will keep track of whether the mouse moves in between a mouse down and a mouse up.
		this._mouseMoved = false;
	},
	drawStop: function (mouseX, mouseY) // return void 
	{
		// If the mouse didn't move, we will draw just a dot.  Its size will be randomized.
		if (!this._mouseMoved) {
			var randRadius = -50;
			var dotColor = '#000000';
			this.drawEllipse(this._startX, this._startY, 2 * this._dotRadius, 2 * this._dotRadius, dotColor);
		}

		//We add the tipLayer to complete the line all the way to the current mouse position:
		this.tip.draw(this.ctx, this._tipTaperFactor);

		//record undo stack
		/*
		var undoBuffer:BitmapData = new BitmapData(boardWidth, boardHeight, false);
		undoBuffer.copyPixels(boardBitmapData,undoBuffer.rect,new Point(0,0));
		undoStack.push(undoBuffer);
		if (undoStack.length > numUndoLevels + 1) {
		undoStack.splice(0,1);
		}
		*/
	},
	lineDraw: function (mouseX, mouseY) // return void 
	{
		var ctx = this.ctx;

		this._mouseMoved = true;

		var mouseChangeVectorX = mouseX - this._lastMouseX;
		var mouseChangeVectorY = mouseY - this._lastMouseY;


		// Cusp detection - if the mouse movement is more than 90 degrees from the last motion, we will draw all the way out to the last mouse
		// position before proceeding.  We handle this by drawing the previous tipLayer, and resetting the last smoothed mouse position to the 
		// last actual mouse position. We use a dot product to determine whether the mouse movement is more than 90 degrees from the last motion.
		if (mouseChangeVectorX * this._lastMouseChangeVectorX + mouseChangeVectorY * this._lastMouseChangeVectorY < 0) {
			this.tip.draw(ctx, this._tipTaperFactor);
			this._smoothedMouseX = this._lastSmoothedMouseX = this._lastMouseX;
			this._smoothedMouseY = this._lastSmoothedMouseY = this._lastMouseY;
			this._lastRotation += Math.PI;
			this._lastThickness = this._tipTaperFactor * this._lastThickness;
		}

		// We smooth out the mouse position.  The drawn line will not extend to the current mouse position; instead it will be drawn only a portion 
		// of the way towards the current mouse position.  This creates a nice smoothing effect.
		this._smoothedMouseX = this._smoothedMouseX + this._smoothingFactor * (mouseX - this._smoothedMouseX);
		this._smoothedMouseY = this._smoothedMouseY + this._smoothingFactor * (mouseY - this._smoothedMouseY);

		// We determine how far the mouse moved since the last position.  We use this distance to change the thickness and brightness of the "line".
		// The more it has moved the thicker the "line"
		var dx = this._smoothedMouseX - this._lastSmoothedMouseX;
		var dy = this._smoothedMouseY - this._lastSmoothedMouseY;
		var dist = Math.sqrt(dx * dx + dy * dy);

		if (dist != 0) {
			this._lineRotation = Math.PI / 2 + Math.atan2(dy, dx);
		}
		else {
			this._lineRotation = 0;
		}

		//		
		// We use a similar smoothing technique to change the thickness of the line, so that it doesn't change too abruptly.
		//
		this._targetLineThickness = this._minThickness + this._thicknessFactor * dist;
		this._lineThickness = this._lastThickness + this._thicknessSmoothingFactor * (this._targetLineThickness - this._lastThickness);

		//
		// The "line" being drawn is actually composed of filled in shapes.  This is what allows us to create a varying thickness of the line.
		//
		var sin0   = Math.sin(this._lastRotation);
		var cos0   = Math.cos(this._lastRotation);
		var sin1   = Math.sin(this._lineRotation);
		var cos1   = Math.cos(this._lineRotation);
		var L0Sin0 = this._lastThickness * sin0;
		var L0Cos0 = this._lastThickness * cos0;
		var L1Sin1 = this._lineThickness * sin1;
		var L1Cos1 = this._lineThickness * cos1;

		var controlVecX = 0.33 * dist * sin0;
		var controlVecY = -0.33 * dist * cos0;

		var controlX1 = this._lastSmoothedMouseX + L0Cos0 + controlVecX;
		var controlY1 = this._lastSmoothedMouseY + L0Sin0 + controlVecY;
		var controlX2 = this._lastSmoothedMouseX - L0Cos0 + controlVecX;
		var controlY2 = this._lastSmoothedMouseY - L0Sin0 + controlVecY;

		// draw the "line" (actually a filled in shape)
		ctx.beginPath();
		ctx.moveTo(this._lastSmoothedMouseX + L0Cos0, this._lastSmoothedMouseY + L0Sin0);
		ctx.quadraticCurveTo(controlX1, controlY1, this._smoothedMouseX + L1Cos1, this._smoothedMouseY + L1Sin1);
		ctx.lineTo(this._smoothedMouseX - L1Cos1, this._smoothedMouseY - L1Sin1);
		ctx.quadraticCurveTo(controlX2, controlY2, this._lastSmoothedMouseX - L0Cos0, this._lastSmoothedMouseY - L0Sin0);
		ctx.lineTo(this._lastSmoothedMouseX + L0Cos0, this._lastSmoothedMouseY + L0Sin0);
		ctx.closePath();
		ctx.lineWidth = 1;
		// ctx.fillStyle = this._lineColor;
		ctx.fill();
		ctx.stroke();


		//We draw the tip, which completes the line from the smoothed mouse position to the actual mouse position.
		//We won't actually add this to the canvas until a mouse up completes the drawing of the current line.
		this.tip.set(this._lineThickness, mouseX, mouseY, this._smoothedMouseX, this._smoothedMouseY, L1Cos1, L1Sin1, this._lineColor);

		this._lastSmoothedMouseX = this._smoothedMouseX;
		this._lastSmoothedMouseY = this._smoothedMouseY;
		this._lastRotation = this._lineRotation;
		this._lastThickness = this._lineThickness;
		this._lastMouseChangeVectorX = mouseChangeVectorX;
		this._lastMouseChangeVectorY = mouseChangeVectorY;
		this._lastMouseX = mouseX;
		this._lastMouseY = mouseY;
	},

	show_message: function (msg, oID) {
		var m = jQuery(cnps._jqSignPadDiv).children('#' + oID)[0];

		if (!m) {
			var a = {};
			a['id'] = oID;
			m = jQuery('<div/>', a).appendTo(cnps._jqSignPadDiv);
		}
		else if (jQuery(m).html()) { // if DIV exist and has content then use its current content
			msg = jQuery(m).html();
		}

		jQuery('#sl_msg_box').html(msg);
		jQuery('#sl_msg_box').css({ top: 35, left: 20 });
		jQuery('#sl_msg_box').show('slow');
	},

	close_message: function () {
		jQuery('#sl_msg_box').hide('fast');
		jQuery('#darkbg').hide();
	},

	showHelp: function () {
		if( !this.readonly )
		{
			var msg = '<a id="closeMsg" href="javascript:canvfuncs.closeHelp();">Close</a><br />' +
				'<h3>Instructions for &quot;Mouse&quot; Input Devices:</h3>' +
				'<p>Point mouse inside signature pad area with the gray border and...</p>' +
				'<h3>Windows Users:</h3>' +
				'<p>Click and hold left mouse button, and sign name.</p>' +
				'<h3>Mac Users:</h3>' +
				'<p>Click and hold button, and sign name.</p>';
			this.show_message(msg, 'sl_msg_box');
		}
	},

	closeHelp: function () {
		this.close_message();
	},

	// set the field with the sl id
	setslid: function (slid) {
		this._oCoords.value = slid;
	},


	// clears the canvas itself
	clearcanvas: function () {
		/* note: bug in some browsers with this clearing technique: */
		/* canv.width = canv.width; */

		/* so use this instead */
		this.ctx.clearRect(0, 0, this._WIDTH, this._HEIGHT);
		this.ctx = this.canv.getContext("2d");
		// this.ctx.drawImage(this.img, 0, 0);
	},

	// draw the image(s) on the background
	drawBackground: function (reloadExisting) {
		if (reloadExisting)
			this.drawSignatureFromExistingData();
	},

	drawSignature: function (coords) {
		this.setslid(coords);
		this.clearcanvas();
		this.drawBackground(false);
		this.dataPts.normalize(coords, 0, 0); //this.vertCorrection, this.horiCorrection);
		this.replaycommands(null, 0, 0); //this.vertCorrection, this.horiCorrection);
	},

	drawSignatureFromDataPts: function () {
		this.setslid(JSON.stringify(this.dataPts.data));
		this.clearcanvas();
		this.drawBackground(false);
		this.replaycommands(null, this.vertCorrection, this.horiCorrection);
	},

	drawSignatureFromExistingData: function () {
		// var commands = document.getElementById('slCmds');
		this.drawSignature(this._oCoords.value);
	},

	// remove the previously drawn line (revert to the last save point)
	undo: function () {
		if( !this.readonly )
		{
			// blank the surface
			this.clearcanvas();

			// clear commands
			this.clearcommands();

			// redraw the background
			this.drawBackground(false);

			this.dataPts.removeStroke();
			this.replaycommands(null, 0, 0);
		}
	},

	// clear the canvas (and commands) and redraw the background
	clear: function () {
		if( !this.readonly )
		{
			// blank the surface
			this.clearcanvas();

			// clear commands
			this.clearcommands();

			// redraw the background
			this.drawBackground(false);

			this.dataPts.clearAll();
		}
	},


	//
	// DATA
	//

	// clear commands
	clearcommands: function () 
	{
		this._oCoords.value = "";
        signpad._validResults._code="";
        signpad._validResults._err="";
	},

	recordcommand: function (cmd, oldX, oldY, newX, newY) {
		// _dataPts.push([newX, newY, (oldX == newX && newY == oldY ? 0 : 1)]);
		var pts = oldX + "," + oldY + "," + newX + "," + newY;
		this._oCoords.value += "," + pts.valueOf();
	},

	// replay commands (without recording them)
	replaycommands: function (pts, verticalCorrection, horCorrection) {

		if( pts )
		{
			// save the old stroke style & set a new stroke style
			var oldStyle = this.ctx.strokeStyle;
			this.ctx.strokeStyle = "rgba(250, 005, 20, 0.9);";

			// split the commands
			var a = pts.trim().split(",");
			var i = 0;

			// loop through and draw each command right away
			while (i + 4 < a.length) {
				var x1 = parseInt(a[i]) + verticalCorrection;
				var y1 = parseInt(a[i + 1]) + horCorrection;
				var x2 = parseInt(a[i + 2]) + verticalCorrection;
				var y2 = parseInt(a[i + 3]) + horCorrection;

				this.ctx.moveTo(x1, y1);
				this.ctx.lineTo(x2, y2);
				i += 4;
			}

			// single stoke at the end & restore the old stroke style
			this.ctx.stroke();
			this.ctx.strokeStyle = oldStyle;
		}
		else if( this.dataPts && this.dataPts.data )
		{
			var data = this.dataPts.data;
			var numofstrokes = data.length, stroke, numofpoints, x, y;

			for( var i=0; i<numofstrokes; i++ )
			{
				stroke = data[i];
				x = stroke.x, y = stroke.y;
				numofpoints = x.length;

				this.drawStart( x[0], y[0] );

				for( var n=1; n<numofpoints; n++ )
					this.lineDraw( x[n], y[n] );

				this.drawStop(0, 0);
			}
			this.setslid( this.dataPts.denormalize() );
		}
	},

	// Draw a line on the canvas, don't record this in the commands. Change the color 
	// so we can see the new graphic, and restore the old color when we're done
	rawdrawLine: function (startX, startY, endX, endY) {
		var oldStyle = this.ctx.strokeStyle;
		this.ctx.strokeStyle = "rgba(250, 005, 20, 0.9);";
		this.ctx.moveTo(startX, startY);
		this.ctx.lineTo(endX, endY);
		this.ctx.strokeStyle = oldStyle;
	},

	// call calculation and return an array object
	getCanvasCoords: function (absX, absY) {
		var relative = slHelper.ObjectPosition(this.canv);
		return ({ relX: (absX - relative[0]), relY: (absY - relative[1]) });
	},


	//
	// ADD OR REMOVE MOUSE AND TOUCH EVENTS
	//
	addPointerEvents: function (evtType) {
		// add events
		if (evtType == 'mouse') {
			this.canv.addEventListener("mouseout", this.mouseout, false);
			this.canv.addEventListener("mousemove", this.mousemove, false);
			this.canv.addEventListener("mouseup", this.mouseup, false);
		}
		else {
			this.canv.addEventListener("touchmove", this.touchmove, false);
			this.canv.addEventListener("touchend", this.touchend, false);
		}
	},
	removePointerEvents: function (evtType) {
		// remove events
		if (evtType == 'mouse') {
			this.canv.removeEventListener("mouseout", this.mouseout, false);
			this.canv.removeEventListener("mousemove", this.mousemove, false);
			this.canv.removeEventListener("mouseup", this.mouseup, false);
		}
		else {
			this.canv.removeEventListener("touchmove", this.touchmove, false);
			this.canv.removeEventListener("touchend", this.touchend, false);
		}
	},


	//
	// UNIVERSAL FUNCTIONS FOR MOUSE AND TOUCH EVENTS
	//
	pointerMove: function (evt, x1, y1) {
		this.coordCnt++;
		//slHelper.logToConsole('count='+this.coordCnt);

		// get position relaive to the canvas
		var p = this.getCanvasCoords(x1, y1);

		// DRAW
		this.lineDraw(p.relX, p.relY);
		this.recordcommand("lt", this.prevMouseX, this.prevMouseY, p.relX, p.relY);
		this.dataPts.addToStroke(p);
		//slHelper.logToConsole('x='+p.relX);
		
		this.rawdrawLine(this.prevMouseX, this.prevMouseY, p.relX, p.relY);

		// record the relative locations
		this.prevMouseX = p.relX;
		this.prevMouseY = p.relY;
	},
	pointerDown: function (evt, x1, y1, evtType) {
		this.coordCnt = 1;

		// get position relaive to the canvas
		var p = this.getCanvasCoords(x1, y1);

		// save old mouse position
		this.prevMouseX = p.relX;
		this.prevMouseY = p.relY;

		// record command
		this.recordcommand("bp", p.relX, p.relY, p.relX, p.relY);
		this.dataPts.startStroke(p);

		// DRAW
		this.drawStart(p.relX, p.relY);

		// add events
		this.addPointerEvents(evtType);
	},
	pointerUp: function (evt, x1, y1, evtType) {
		// remove events
		this.removePointerEvents(evtType);

		// get position relaive to the canvas and stop drawing
		var p = this.getCanvasCoords(x1, y1);
		this.drawStop(p.relX, p.relY);
		// this.dataPts.addToStroke(p);
		this.dataPts.endStroke(null);
	},
	pointerOut: function (evt, x1, y1, evtType) {
		this.pointerUp(evt, x1, y1, evtType);
	},

	//
	// MOUSE event handlers
	//

	// returns a function that closures 'this' (SPCanvasFunctions) into the function
	getmousedown: function () {
		var funcs = this;

		return function (e) {
			e.preventDefault();
			funcs.pointerDown(e, e.clientX, e.clientY, 'mouse');
		};
	},

	// handle mouseout - 'this' is a canvas element
	mouseout: function (e) {
		return canvfuncs.pointerOut(e, e.clientX, e.clientY, 'mouse');
	},

	// handle mousemove - 'this' is a canvas element
	mousemove: function (e) {
		canvfuncs.pointerMove(e, e.clientX, e.clientY);
	},

	// handle mouseup - 'this' is a canvas element
	mouseup: function (e) {
		canvfuncs.pointerUp(e, e.clientX, e.clientY, 'mouse');
	},

	//
	// TOUCH event handlers
	//

	// handle touchstart event - 'this' is a canvas element
	touchstart: function (e) {
		// prevent dragging of the surface
		e.preventDefault();

		// get the single touch event
		var touch = slHelper.getTouchEvent(e);

		canvfuncs.pointerDown(e, touch.pageX, touch.pageY, 'touch');

		return FALSE;
	},

	// handle touchmove event
	touchmove: function (e) {
		// prevent dragging of the surface
		e.preventDefault();

		// get the single touch event
		var touch = slHelper.getTouchEvent(e);

		canvfuncs.pointerMove(e, touch.pageX, touch.pageY);
	},

	// handle touchend event
	touchend: function (e) {
		// get the single touch event
		var touch = slHelper.getTouchEvent(e);

		canvfuncs.pointerUp(e, touch.pageX, touch.pageY, 'touch');
	},

	//
	// initialization
	//

	// init values and functions and events, etc
	Init: function (canvas, iSrc, button, coordsID, readonly) {

		_button = button;
		this._oCoords = document.getElementById(coordsID);

		// primary canvas and context variables
		this.canv = canvas;
		this._WIDTH = 350;
		this._HEIGHT = 200;
		this.readonly = readonly;

		if (canvas.getContext) {
			var ctx = canvas.getContext("2d");
			// this.ctx.strokeStyle = "rgba(250, 005, 20, 0.9);";
			ctx.shadowColor = "rgba(0,0,0,.1)";
			ctx.shadowBlur = 0.5;
			ctx.lineCap = 'round';
			ctx.lineJoin = 'round';
			ctx.lineWidth = 1;
			ctx.miterLimit = 5;

			this.ctx = ctx;

			var img = new Image();
			img.onload = function () {
				// ctx.drawImage(img, 0, 0);
			};
			img.src = iSrc;
			this.img = img;

			// set initial event
			if (!readonly) {
				this.canv.addEventListener("touchstart", this.touchstart, false);
				this.canv.addEventListener("mousedown", this.getmousedown(), false);
			}
			else {
				jQuery('#slh5Reset').click(function (e) {
					$(e).attr("href", "#");
					e.preventDefault();
					//return false;
				});
			}

			//
			// Smooth Drwaing Initilization
			// 
			boardWidth = 302; // this.width;
			boardHeight = 177; // this.height;

			// this._minThickness = 0.2;
			// this._thicknessFactor = 0.25;
			// this._smoothingFactor = 0.3;  //Should be set to something between 0 and 1.  Higher numbers mean less smoothing.
			// this._thicknessSmoothingFactor = 0.3;
			// this._tipTaperFactor = 0.8;
			// this._dotRadius = 2; //radius for drawn dot if there is no mouse movement between mouse down and mouse up.

			this.dataPts = new SpDataPoints();


			/*
			The tipLayer holds the tip portion of the line.
			Because of the smoothing technique we are using, while the user is drawing the drawn line will not
			extend all the way from the last position to the current mouse position.  We use a small 'tip' to 
			complete this line all the way to the current mouse position.
			*/
			this.tip = new TipLayer();

			// this.drawSignatureFromExistingData();
		}
	}
};

// init HTML5 signpad
function slInitHTML5(signPadID, coordsID, readonly) 
{
	var b = '<input type="button" onclick="canvfuncs.closeHelp();" value="Close" />';
	var c = document.getElementById(signPadID);
	var i = '/js/signaturelink/baseskin_v1.jpg';

	canvfuncs = new SPCanvasFunctions(c, i, b, coordsID, readonly);
	canvfuncs.drawBackground(true);

	c.GetValidationCode = function () { alert("This is a CANVAS"); }

	// When scrolling the document, using a timeout to create a slight delay seems to be necessary.
	// NOTE: For the iPhone, the window has a native method, scrollTo().
	setTimeout(function () { window.scrollTo(0, 0); }, 50);
}
