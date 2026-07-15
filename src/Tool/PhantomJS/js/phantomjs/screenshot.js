"use strict";
var RenderToFile, args, system;

system = require("system");

RenderToFile = function(url, filename, width, height, zoom, username, password) {
	width = typeof width === "undefined" ? 800 : parseInt(width);
	height = typeof height === "undefined" ? 600 : parseInt(height);
	zoom = typeof zoom === "undefined" ? 1 : parseFloat(zoom);
	var page = require('webpage').create();

	if(typeof username !== "undefined")
		page.settings.userName = username;
	if(typeof password !== "undefined")
		page.settings.password = password;

	page.viewportSize = {
		width: width,
		height: height
	};
	page.clipRect = {
		width: width,
		height: height
	};
	page.zoomFactor = zoom;
	page.open(url, function() {
		page.render(filename);
		phantom.exit();
	});
}

if (system.args.length > 2) {
	args = Array.prototype.slice.call(system.args, 1);
	RenderToFile.apply(this, args);
} else {
	console.log("Usage: phantomjs page.js URL FILENAME [WIDTH] [HEIGHT] [USERNAME] [PASSWORD]");
	phantom.exit();
}
