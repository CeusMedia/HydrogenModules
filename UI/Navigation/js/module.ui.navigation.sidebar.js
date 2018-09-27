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
	},
	phone: {
		detected: false,
		deviceWidthLimit: 768,
		factorWidth: 1.2,
		factorWidthMax: 0.7,
	},

	//  --  PROTECTED  --  //
	widthDefault: 240,
	intervalScroll: null,
	storage: null,
	containers: {},
	status: 0,																	//  0:uninitialized, 1:hidden, 2:shown

	//  --  PUBLIC  --  //
	init: function(verbose){
		this.containers.field		= jQuery(this.selectors.field);
		this.containers.nav			= jQuery(this.selectors.nav);
		this.containers.navMenu		= jQuery(this.selectors.navMenu);
		this.containers.navToggle	= jQuery(this.selectors.navToggle);
		this.containers.window		= jQuery(window);
		this.widthDefault		= this.containers.nav.outerWidth();
		if(typeof verbose !== "undefined")
			this.verbose = verbose;
		if(this.verbose)
			console.log("ModuleUiNavigation.Sidebar: init");
		this.containers.window.on("resize resize-sidebar", this.onWindowResize);
		this.containers.navToggle.on("click", this.onToggle);
		this.containers.field.on("click", this.onBlur);
		this.containers.nav.on("click", function(e){e.stopPropagation()});
		if(typeof Storages !== "undefined"){
			this.storage = Storages.cookieStorage;
			this.storage.setDomain(settings.Env.domain);
			this.storage.setPath(settings.Env.path);
			this.containers.navMenu.on('scroll', this.onScroll);
		}
		this.status = 1;
		this.containers.window.trigger("resize-sidebar");
	},
	show: function(){
		if(!this.status)
			throw "ModuleUiNavigation.Sidebar not initialized";
		if(!this.phone.detected)
			return;
		var widthDefault	= this.widthDefault;
		var widthMax		= this.containers.window.width() * this.phone.factorWidthMax;
		this.width	= Math.min(widthDefault, widthMax);
		this.containers.nav.width(this.width);
		if(this.isOpen())
			return;
		if(this.verbose)
			console.log("ModuleUiNavigation.Sidebar: show");
		this.status = 2;
		this.containers.nav.stop(true).animate({
			left: "0px"
		}, this.durations.show);
	},
	hide: function(){
		if(!this.status)
			throw "ModuleUiNavigation.Sidebar not initialized";
		if(!this.phone.detected)
			return;
		if(!this.isOpen())
			return;
		if(this.verbose)
			console.log("ModuleUiNavigation.Sidebar: hide");
		this.status = 1;
		this.containers.nav.stop(true).animate({
			left: "-" + (this.width * this.phone.factorWidth) + "px"
		}, this.durations.hide);
	},
	isOpen: function(){
		return this.status > 1;
	},
	onBlur: function(event){
		if(typeof event === "undefined")
			throw "ModuleUiNavigation.Sidebar.onBlur: No event given";
		if(this.verbose)
			console.log("ModuleUiNavigation.Sidebar: onblur");
		ModuleUiNavigation.Sidebar.hide();
	},

	//  --  PROTECTED  --  //
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
		if(!sidebar.phone.detected)
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
		var sidebar	= ModuleUiNavigation.Sidebar;
		var width	= sidebar.containers.window.width();
		if(sidebar.verbose)
			console.log("ModuleUiNavigation.Sidebar: onWindowResize: " + width);
		sidebar.phone.detected = width < sidebar.phone.deviceWidthLimit;
		var left = 0;
		if(sidebar.phone.detected)
			if(!sidebar.isOpen())
				left	= "-" + (sidebar.width * sidebar.phone.factorWidth) + "px"
		sidebar.containers.nav.css({left: left});
		var offsetPage	= sidebar.containers.nav.offset().top;
		var offsetNav	= sidebar.containers.navMenu.offset().top;
		var height = window.innerHeight - offsetNav + offsetPage;
		sidebar.containers.navMenu.height(height);
		sidebar.containers.navMenu.trigger('scroll');
	}
}
