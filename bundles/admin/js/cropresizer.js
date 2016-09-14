/***************************************************************
 *  FC-CropResizer
 *
 *  Copyright (C) 2008 by Alexander Burtsev - http://fastcoder.org/
 *
 *  This program is free software; you can redistribute it and/or
 *  modify it under the terms of the BSD Licenses
***************************************************************/

var cropresizer = { // NAMESPACE
	archive : {},
	getObject : function(id) {
		if (typeof this.archive[id] == "undefined") {
			this.archive[id] = new this.fc(id);
		}
		return this.archive[id];
	}
};

cropresizer.fc = function(id) { // Constructor
	// Vars
	this.id = id;
		// Image data
	this.iWidth = 0;
	this.iHeight = 0;
	this.iMaxWidth = 0;
	this.iMaxHeight = 0;
	this.iMinWidth = 0;
	this.iMinHeight = 0;
	this.iTop = 0;
	this.iLeft = 0;
	this.iRate = 0;
		// Crop block data
	this.cropWidth = 100;
	this.cropHeight = 100;
	this.cropTop = 0;
	this.cropLeft = 0;
	this.cropBackground = false;
		// Move
	this.X0 = 0;
	this.Y0 = 0;
		// Resize div data
	this.resizeWidth = 16;
	this.resizeHeight = 16;
	this.resizeTop = 0;
	this.resizeLeft = 0;
		// Resize move
	this.iBuferWidth = 0;
	this.iBuferHeight = 0;
	// Flags
	this.cropMoveState = false;
	this.resizeMoveState = false;
	this.saveProportions = true;
	this.withContainer = true;
	this.stopSelection = true;
	this.showCropSize = true;
	this.debugMode = true;
	// Handlers
	this.onUpdate = null;
	// Nodes
	this.image = null;
	this.crop = null;
	this.resize = null;
}

cropresizer.fc.prototype = {
// Const
	ERRORS : {
		1 : "Object initialisation error",
		2 : "Image was not found",
		3 : "Element in not image",
		4 : "Callback-function [onUpdate] was not defined",
		5 : "HTML-element BODY was not defined"
	},
// Methods
	// Default
	gebi : function(id) {
		return document.getElementById(id);
	},
	addHandler : function(object, event, handler, useCapture) {
		if (object.addEventListener) {
			object.addEventListener(event, handler, useCapture ? useCapture : false);
		} else if (object.attachEvent) {
			object.attachEvent('on' + event, handler);
		} else alert(this.errorArray[9]);
	},
	defPosition : function(event) { 
		var x = y = 0; 
		if (document.attachEvent != null) {
			x = window.event.clientX + document.documentElement.scrollLeft + document.body.scrollLeft; 
			y = window.event.clientY + document.documentElement.scrollTop + document.body.scrollTop; 
		} 
		if (!document.attachEvent && document.addEventListener) { // Gecko 
			x = event.clientX + window.scrollX; 
			y = event.clientY + window.scrollY; 
		} 
		return {x:x, y:y}; 
	},
	absPosition : function(obj) { 
		var x = y = 0; 
		while(obj) { 
			x += obj.offsetLeft; 
			y += obj.offsetTop; 
			obj = obj.offsetParent; 
		} 
		return {x:x, y:y}; 
	},
	domReady : function(i) { /* Copyright http://ajaxian.com/ */
		var u =navigator.userAgent;
		var e=/*@cc_on!@*/false;
		var st = setTimeout;
		if (/webkit/i.test(u)) {
			st(
				function() {
					var dr=document.readyState;
					if(dr=="loaded"||dr=="complete") i();
					else st(arguments.callee,10);
				},
				10
			);
		} else if ((/mozilla/i.test(u)&&!/(compati)/.test(u)) || (/opera/i.test(u))) {
			document.addEventListener("DOMContentLoaded", i, false);
		} else if (e) {(
			function(){
				var t=document.createElement('doc:rdy');
				try {
					t.doScroll('left');	i(); t=null;
				} catch(e) {st(arguments.callee,0);}
			})();
		} else window.onload=i;
	},
	// Common
	debug : function(keys) {
		if (!this.debugMode) return;
		var mes = "";
		for (var i = 0; i < keys.length; i++) mes += this.ERRORS[keys[i]] + " : ";
		mes = mes.substring(0, mes.length - 3);
		alert(mes);
	},
	init : function(hash, node) {
		if (typeof node == "undefined") {
			var _this = this;
			this.domReady(
				function() {_this.init(hash, 1)}
			);
			return;
		}
		this.image = this.gebi(this.id);
		if (this.image == null) {
			this.debug([1,2]);
				return;
		}
		if (this.image.nodeName.toLowerCase() != "img") {
			this.debug([1,3]);
				return;
		}
		try {
			for (var i in hash) this[i] = hash[i];
			if (this.onUpdate == null) {
				this.debug([1,4]);
					return;
			}
			if (!document.body) {
				this.debug([1,5]);
					return;
			}
			this.image.ondragstart = function() {return false;} // IE fix
			// Set default
			this.redefine();
			this.iMaxWidth = this.iWidth;
			this.iMaxHeight = this.iHeight;
			this.iMinWidth = this.cropWidth;
			this.iMinHeight = this.cropHeight;;
			this.iRate = this.iWidth / this.iHeight;
			if (this.withContainer) this.addContainer();
			if (this.stopSelection) this.setBlocker();
			this.drawCropBlock();
			this.drawResizeBlock();
			// Add handers
			var _this = this;
			this.addHandler(document, "mousemove", function (evt) {
				_this.cropMoveHandler(evt);
				_this.resizeMoveHandler(evt);
			});
			this.addHandler(document, "mouseup", function () {
				_this.cropMoveState = false;
				_this.resizeMoveState = false;
				_this.redefine();
			});
			this.addHandler(_this.crop, "mousedown", function (evt) {
				_this.cropMoveState = true;
				evt = evt || window.event;
				_this.X0 = _this.defPosition(evt).x;
				_this.Y0 = _this.defPosition(evt).y;
			});
			this.addHandler(_this.resize, "mousedown", function (evt) {
				_this.resizeMoveState = true;
				evt = evt || window.event;
				_this.X0 = _this.defPosition(evt).x;
				_this.Y0 = _this.defPosition(evt).y;
				_this.iBuferWidth = _this.iWidth;
				_this.iBuferHeight = _this.iHeight;
			});
		} catch(e) {this.debug([1]);}
	},
	setBlocker : function() {
		if (!this.gebi("blockerDivId_" + this.id)) {
			this.blocker = document.createElement("div");
			this.blocker.id = "blockerDivId_" + this.id;
			this.blocker.className = "blocker";
			this.blocker.style.width = document.body.scrollWidth + "px";
			this.blocker.style.height = document.body.scrollHeight + "px";
			this.blocker.style.display = "none";
			document.body.appendChild(this.blocker);
		}
		this.blocker = this.gebi("blockerDivId_" + this.id);
		this.blocker.style.display = "";
	},
	addContainer : function() {
		var div = document.createElement("div");
		div.style.width = this.iMaxWidth + "px";
		div.style.height = this.iMaxHeight + "px";
		div.appendChild(this.image.cloneNode(true));
		this.image.parentNode.replaceChild(div, this.image);
		this.image = div.firstChild;
	},
	redefine : function() {
		this.iTop = this.absPosition(this.image).y;
		this.iLeft = this.absPosition(this.image).x;
		this.iWidth = this.image.style.width ? parseInt(this.image.style.width) : this.image.offsetWidth;
		this.iHeight = this.image.style.height ? parseInt(this.image.style.height) : this.image.offsetHeight;
		this.resizeTop = this.iTop + this.iHeight;
		this.resizeLeft = this.iLeft + this.iWidth;
		if (this.crop) {
			this.cropLeft = parseInt(this.crop.style.left);
			this.cropTop = parseInt(this.crop.style.top);
		}
		this.onUpdate();
	},
	// Crop
	drawCropBlock : function() {
		if (!this.gebi("cropDivId_" + this.id)) {
			this.crop = document.createElement("div");
			this.crop.id = "cropDivId_" + this.id;
			this.crop.className = "cropDiv";
			this.crop.style.width = this.cropWidth + "px";
			this.crop.style.height = this.cropHeight + "px";
			this.crop.style.display = "none";
			document.body.appendChild(this.crop);
		}
		this.crop = this.gebi("cropDivId_" + this.id);
		this.cropTop = this.iTop + (this.iHeight - this.cropHeight) / 2;
		this.cropLeft = this.iLeft + (this.iWidth - this.cropWidth) / 2;
		this.crop.style.top = this.cropTop + "px";
		this.crop.style.left = this.cropLeft + "px";
		if (this.cropBackground) this.crop.style.background = this.cropBackground;
		this.crop.innerHTML = "<div>" + (this.showCropSize ? "<span>" + this.cropWidth + "x" + this.cropHeight + "</span>" : "") + "<div></div></div>";
		this.crop.style.display = "";
	},
	cropMoveHandler : function(evt) {
		if (!this.cropMoveState) return;
		evt = evt || window.event;
		var newX = this.defPosition(evt).x - this.X0 + this.cropLeft;
		var newY = this.defPosition(evt).y - this.Y0 + this.cropTop;
		if (newX < this.iLeft) newX = this.iLeft;
		if (newX + this.cropWidth > this.iLeft + this.iWidth) newX = this.iLeft + this.iWidth - this.cropWidth;
		if (newY < this.iTop) newY = this.iTop;
		if (newY + this.cropHeight > this.iTop + this.iHeight) newY = this.iTop + this.iHeight - this.cropHeight;
		this.crop.style.top = newY + "px";
		this.crop.style.left = newX + "px";
	},
	// Resize
	drawResizeBlock : function() {
		if (!this.gebi("resizeDivId_" + this.id)) {
			this.resize = document.createElement("div");
			this.resize.id = "resizeDivId_" + this.id;
			this.resize.className = "resizeDiv";
			this.resize.innerHTML = "<img src=\"i/0.gif\" width=\"1\" height=\"1\" alt=\"\">";
			this.resize.style.width = this.resizeWidth + "px";
			this.resize.style.height = this.resizeHeight + "px";
			this.resize.style.display = "none";
			document.body.appendChild(this.resize);
		}
		this.resize = this.gebi("resizeDivId_" + this.id);
		this.setResizeVars();
		this.resize.style.display = "";
	},
	setResizeVars : function() {
		this.redefine();
		this.resize.style.top = (this.resizeTop - 10) + "px";
		this.resize.style.left = (this.resizeLeft - 10) + "px";
	},
	resizeMoveHandler : function(evt) {
		if (!this.resizeMoveState) return;
		evt = evt || window.event;
		var nW, nH;
		hW = this.iBuferWidth + this.defPosition(evt).x - this.X0;
		hH = this.iBuferHeight + this.defPosition(evt).y - this.Y0;
		if (this.iRate < 1) {
			hW = this.iBuferWidth + this.defPosition(evt).x - this.X0;
			if (this.saveProportions) hH = hW / this.iRate;
			else hH = this.iBuferHeight + this.defPosition(evt).y - this.Y0;
		} else {
			hH = this.iBuferHeight + this.defPosition(evt).y - this.Y0;
			if (this.saveProportions) hW = this.iRate * hH;
			else hW = this.iBuferWidth + this.defPosition(evt).x - this.X0;
		}
		if (hW <= this.iMinWidth) hW = this.iMinWidth;
		if (hW > this.iMaxWidth) hW = this.iMaxWidth;
		if (hH <= this.iMinHeight) hH = this.iMinHeight;
		if (hH > this.iMaxHeight) hH = this.iMaxHeight;
		
		if (this.iRate < 1) {
			this.image.style.width = hW + "px";
			this.image.style.height = (this.saveProportions ? hW / this.iRate : hH) + "px";
		} else {
			this.image.style.height = hH + "px";
			this.image.style.width = (this.saveProportions ? this.iRate * hH : hW) + "px";
		}
		
		var crop_dY = this.resizeTop - this.cropTop - this.cropHeight;
		var crop_dX = this.resizeLeft - this.cropLeft - this.cropWidth;
		if (crop_dY < 0) this.crop.style.top = (this.cropTop + crop_dY) + "px";
		if (crop_dX < 0) this.crop.style.left = (this.cropLeft + crop_dX) + "px";
		this.setResizeVars();
	}
}