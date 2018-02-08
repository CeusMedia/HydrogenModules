ModuleCatalogBookstore = {
	setupCategoryIndex: function(selector){
		var container = typeof selector == "undefined" ? $("body") : $(selector);
		container.find("span.hitarea:not(.empty)").click(function(){
			var list = jQuery(this).parent().children("ul.topics");
			var area = jQuery(this);
			if(area.hasClass("closed")){
				area.removeClass("closed").addClass("open");
				list.slideDown(250);
			}else{
				area.removeClass("open").addClass("closed");
				list.slideUp(250);
			}
		});
	}
};

