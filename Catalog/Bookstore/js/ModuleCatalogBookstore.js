ModuleCatalogBookstore = {
	setupCategoryIndex: function(selector){
		var container = typeof selector == "undefined" ? $("body") : $(selector);
		container.find("span.hitarea:not(.empty)").click(function(){
			if($(this).hasClass("closed")){
				$(this).removeClass("closed").addClass("open");
				$(this).parent().children("ul").eq(0).show();
			}else{
				$(this).removeClass("open").addClass("closed");
				$(this).parent().children("ul").eq(0).hide();
			}
		});
	}
};
