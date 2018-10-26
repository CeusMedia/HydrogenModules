if(typeof ModuleUiNavigation == "undefined")
	var ModuleUiNavigation = {};
ModuleUiNavigation.Sidebar = {

	//  --  PUBLIC  --  //
	verbose: false,
	durations: {
		hide: 350,
		show: 250,
		scrollSave: 200,
	},
	selectors: {
		field: "#layout-container",
		nav: '#layout-nav',
		navMenu: '#nav-sidebar-list',
		navToggle: '#nav-sidebar-toggle',
		navMask: '#nav-sidebar-field-mask',
	},
	phone: {
		detected: false,
		deviceWidthLimit: 768,
		factorWidth: 1.2,
		factorWidthMax: 0.7,
	},
	viewport: {
		width: 0,
		height: 0,
		scale: 1,
	},

	//  --  PROTECTED  --  //
	widthDefault: 240,
	intervalScroll: null,
	storage: null,
	containers: {},
	status: 0,																	//  0:uninitialized, 1:hidden, 2:shown

	//  --  PUBLIC  --  //
	init: function(verbose){
		if(this.status > 0)
			return;
		if(typeof verbose !== "undefined")
			this.verbose = verbose;
		if(this.verbose)
			console.log("ModuleUiNavigation.Sidebar: init");
		this.initContainers();
		this.initFieldMask();
		this.initContainerEvents();
//		this.containers.window.trigger("resize-sidebar");
		this.detectViewport();
		this.detectPhone();
		this.status = 1;
		this.widthDefault			= this.containers.nav.outerWidth();
		if(typeof Storages !== "undefined"){
			this.storage = Storages.cookieStorage;
			if(typeof settings.Env !== "undefined"){
				if(typeof settings.Env.domain !== "undefined")
					this.storage.setDomain(settings.Env.domain);
				if(typeof settings.Env.domain !== "undefined")
					this.storage.setPath(settings.Env.path);
			}
			this.containers.navMenu.on('scroll', this.onScroll);
		}
	},
	show: function(){
		if(!this.status)
			throw "ModuleUiNavigation.Sidebar not initialized";
		if(!this.isPhone() || this.isOpen())
			return;
		var widthMax	= this.viewport.width * this.phone.factorWidthMax;
		this.width		= Math.min(this.widthDefault, widthMax);
		this.containers.nav.width(this.width);
		this.containers.navMask.css({left: 0});
		if(this.verbose)
			console.log("ModuleUiNavigation.Sidebar: show");
		this.status = 2;
		this.containers.nav.stop(true).animate({
			left: "0px"
		}, this.durations.show);
		this.containers.body.addClass('nav-sidebar-open');
	},
	hide: function(){
		if(!this.status)
			throw "ModuleUiNavigation.Sidebar not initialized";
		if(!this.isPhone() || !this.isOpen())
			return;
		if(this.verbose)
			console.log("ModuleUiNavigation.Sidebar: hide");
		this.status = 1;
		this.containers.nav.stop(true).animate({
			left: "-" + (this.width * this.phone.factorWidth) + "px"
		}, this.durations.hide, function(){
			ModuleUiNavigation.Sidebar.containers.navMask.css({left: '-100vw'});
		});
		this.containers.body.removeClass('nav-sidebar-open');
	},
	isOpen: function(){
		return this.status > 1;
	},
	isPhone: function(detect){
		if(typeof detect !== "undefined" && detect === true)
			return this.detectPhone();
		return this.phone.detected;
	},
	onBlur: function(event){
		if(typeof event === "undefined")
			throw "ModuleUiNavigation.Sidebar.onBlur: No event given";
		if(this.verbose)
			console.log("ModuleUiNavigation.Sidebar: onblur");
		ModuleUiNavigation.Sidebar.hide();
	},

	//  --  PROTECTED  --  //
	detectPhone: function(){
		var viewportWidth = this.viewport.width;
		this.phone.detected = viewportWidth < this.phone.deviceWidthLimit;
		if(this.verbose){
			var value = this.phone.detected ? "yes" : "no";
			console.log("ModuleUiNavigation.Sidebar: detectPhone: " + value);
		}
		return this.phone;
	},
	detectViewport: function(){
		this.viewport.scale = Math.round(window.devicePixelRatio * 1000) / 1000;
		this.viewport.width = this.containers.window.outerWidth();
		this.viewport.height = this.containers.window.outerHeight();
		if(this.verbose){
			console.log("ModuleUiNavigation.Sidebar: detectViewport:");
			console.log(this.viewport);
		}
		return this.viewport;
	},
	initContainers: function(){
		this.containers.field		= jQuery(this.selectors.field);
		this.containers.nav			= jQuery(this.selectors.nav);
		this.containers.navMenu		= jQuery(this.selectors.navMenu);
		this.containers.navToggle	= jQuery(this.selectors.navToggle);
		this.containers.navMask		= jQuery(this.selectors.navMask);
		this.containers.window		= jQuery(window);
		this.containers.body		= jQuery("body");
	},
	initContainerEvents: function(){
		this.containers.window.on("resize resize-sidebar", this.onWindowResize);
		this.containers.navToggle.on("click", this.onToggle);
		this.containers.field.on("click", this.onBlur);
		this.containers.nav.on("click", function(e){e.stopPropagation()});
	},
	initFieldMask: function(){
		if(!jQuery(this.selectors.navMask).size()){
			var mask = jQuery("<div></div>");
			mask.attr({id: this.selectors.navMask.replace(/^#/, '')});
			mask.insertAfter(this.containers.navToggle);
			this.containers.navMask = jQuery(this.selectors.navMask);
		}
		this.containers.navMask.css({left: '-100vw'});
	},
	onScroll: function(event){
		if(typeof event === "undefined")
			throw "ModuleUiNavigation.Sidebar.onScroll: No event given";
		var offset = jQuery(event.target).scrollTop();
		var sidebar = ModuleUiNavigation.Sidebar;
		if(sidebar.timeoutScroll)
			window.clearTimeout(sidebar.timeoutScroll);
		sidebar.timeoutScroll = window.setTimeout(function(){
			sidebar.storage.set('sidebarOffset', offset);
			if(sidebar.verbose)
				console.log('offset set');
		}, sidebar.durations.scrollSave);
	},
	onToggle: function(event){
		if(typeof event === "undefined")
			throw "ModuleUiNavigation.Sidebar.onToggle: No event given";
		var sidebar = ModuleUiNavigation.Sidebar;
		if(!sidebar.isPhone())
			return;
		if(sidebar.verbose)
			console.log("ModuleUiNavigation.Sidebar: toggle");
		if(sidebar.isOpen())
			sidebar.hide();
		else
			sidebar.show();
		event.stopPropagation();
	},
	onWindowResize: function(event){
		if(typeof event === "undefined")
			throw "ModuleUiNavigation.Sidebar.onWindowResize: No event given";
//		var width = Math.round(window.outerWidth / window.devicePixelRatio);
		var sidebar		= ModuleUiNavigation.Sidebar;
		if(sidebar.verbose)
			console.log("ModuleUiNavigation.Sidebar: onWindowResize");
		var was	= {
			phone: sidebar.isPhone(),
			viewport: sidebar.viewport,
		};
		sidebar.detectViewport();
		sidebar.detectPhone();
		var is = {
			phone: sidebar.isPhone(),
			viewport: sidebar.viewport,
		};
		if(is.phone && !was.phone){
			if(!sidebar.isOpen()){
				var left = sidebar.width * sidebar.phone.factorWidth;
				sidebar.containers.nav.css({left: "-" + left + "px"});
			}
		}
		else if(!is.phone && was.phone){
			sidebar.containers.nav.css({left: 0});
		}
		var offsetPage	= sidebar.containers.nav.offset().top;
		var offsetNav	= sidebar.containers.navMenu.offset().top;
		var height		= window.innerHeight - offsetNav + offsetPage;
		sidebar.containers.navMenu.height(height);
		sidebar.containers.navMenu.trigger('scroll');
	}
}
