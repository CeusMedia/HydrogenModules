if(typeof ModuleUiNavigation == "undefined")
	var ModuleUiNavigation = {};
ModuleUiNavigation.Sidebar = {
	status: 0,
	verbose: false,
	widthDefault: 240,
	durations: {
		hide: 350,
		show: 250
	},
	isPhone: false,

	init: function(verbose){
		ModuleUiNavigation.Sidebar.widthDefault = jQuery("#layout-nav").outerWidth();
		if(typeof verbose !== "undefined")
			ModuleUiNavigation.Sidebar.verbose = verbose;
		if(ModuleUiNavigation.Sidebar.verbose)
			console.log("ModuleUiNavigationSidebar: init");
		jQuery(window).on("resize", ModuleUiNavigation.Sidebar.onWindowResize);
		jQuery("#nav-sidebar-toggle").on("click", ModuleUiNavigation.Sidebar.toggle);
		jQuery("#layout-field").on("click", ModuleUiNavigation.Sidebar.hide);
		ModuleUiNavigation.Sidebar.onWindowResize();
	},
	toggle: function(){
		if(!ModuleUiNavigation.Sidebar.isPhone)
			return;
		if(ModuleUiNavigation.Sidebar.verbose)
			console.log("ModuleUiNavigationSidebar: toggle");
		if(ModuleUiNavigation.Sidebar.isOpen())
			ModuleUiNavigation.Sidebar.hide();
		else
			ModuleUiNavigation.Sidebar.show();
	},
	show: function(){
		if(!ModuleUiNavigation.Sidebar.isPhone)
			return;
		var widthDefault	= ModuleUiNavigation.Sidebar.widthDefault;
		var widthMax		= jQuery(window).width() * 0.7;
		ModuleUiNavigation.Sidebar.width	= Math.min(widthDefault, widthMax);
		jQuery("#layout-nav").width(ModuleUiNavigation.Sidebar.width);
		if(ModuleUiNavigation.Sidebar.isOpen())
			return;
		if(ModuleUiNavigation.Sidebar.verbose)
			console.log("ModuleUiNavigationSidebar: show");
		ModuleUiNavigation.Sidebar.status = 1;
		jQuery("#layout-nav").stop(true).animate({
			left: "0px"
		}, ModuleUiNavigation.Sidebar.durations.show);
	},
	hide: function(){
		if(!ModuleUiNavigation.Sidebar.isPhone)
			return;
		if(!ModuleUiNavigation.Sidebar.isOpen())
			return;
		if(ModuleUiNavigation.Sidebar.verbose)
			console.log("ModuleUiNavigationSidebar: hide");
		ModuleUiNavigation.Sidebar.status = 0;
		jQuery("#layout-nav").stop(true).animate({
			left: "-" + (ModuleUiNavigation.Sidebar.width * 1.2) + "px"
		}, ModuleUiNavigation.Sidebar.durations.hide);
	},
	isOpen: function(){
		return ModuleUiNavigation.Sidebar.status > 0;
	},
	onWindowResize: function(event){
//		var width = Math.round(window.outerWidth / window.devicePixelRatio);
		var width = jQuery(window).width();
		if(ModuleUiNavigation.Sidebar.verbose)
			console.log("ModuleUiNavigationSidebar: onWindowResize: " + width);
		ModuleUiNavigation.Sidebar.isPhone = width < 768;
		if(event){
			var left = 0;
			if(ModuleUiNavigation.Sidebar.isPhone)
				if(!ModuleUiNavigation.Sidebar.isOpen())
					left	= "-" + (ModuleUiNavigation.Sidebar.width * 1.2) + "px"
			jQuery("#layout-nav").css({left: left});
		}
	}
}
