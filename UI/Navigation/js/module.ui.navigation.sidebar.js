if(typeof ModuleUiNavigation == "undefined")
	var ModuleUiNavigation = {};
ModuleUiNavigation.Sidebar = {
	status: 0,
	verbose: false,
	width: 240,
	isPhone: false,

	init: function(verbose){
		ModuleUiNavigation.Sidebar.width = jQuery("#layout-nav").outerWidth();
		ModuleUiNavigation.Sidebar.verbose = verbose;
		if(ModuleUiNavigation.Sidebar.verbose)
			console.log("ModuleUiNavigationSidebar: init");
		jQuery(window).bind("resize", ModuleUiNavigation.Sidebar.onWindowResize);
		jQuery("#nav-sidebar-toggle").bind("click", ModuleUiNavigation.Sidebar.toggle);
		jQuery("#layout-field").bind("click", ModuleUiNavigation.Sidebar.hide);
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
		var widthDefault	= ModuleUiNavigation.Sidebar.width;
		var widthMax		= jQuery(window).width() * 0.8;
		jQuery("#layout-nav").width(Math.min(widthDefault, widthMax));
		if(ModuleUiNavigation.Sidebar.isOpen())
			return;
		if(ModuleUiNavigation.Sidebar.verbose)
			console.log("ModuleUiNavigationSidebar: show");
		ModuleUiNavigation.Sidebar.status = 1;
		jQuery("#layout-nav").stop(true).animate({
			left: "0px"
		});
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
			left: "-"+ModuleUiNavigation.Sidebar.width+"px"
		});
	},
	isOpen: function(){
		return ModuleUiNavigation.Sidebar.status > 0;
	},
	onWindowResize: function(event){
		if(ModuleUiNavigation.Sidebar.verbose)
			console.log("ModuleUiNavigationSidebar: onWindowResize");
		var width = Math.round(window.outerWidth / window.devicePixelRatio);
		ModuleUiNavigation.Sidebar.isPhone = width < 768;
		if(event){
			var left = 0;
			if(ModuleUiNavigation.Sidebar.isPhone)
				if(!ModuleUiNavigation.Sidebar.isOpen())
					left	= "-"+ModuleUiNavigation.Sidebar.width+"px"
			jQuery("#layout-nav").css({left: left});
		}
	}
}
