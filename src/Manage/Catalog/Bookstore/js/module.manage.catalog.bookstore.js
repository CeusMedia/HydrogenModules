
var ModuleManageCatalogBookstore = {
	bindListResize: function(list){
		"use strict";
		$(window).resize(function(){
			list.css({overflow: "auto", "overflow-y": "scroll"}).show();
			var height = $(window).height();
			height -= list.offset().top;
			height -= parseInt($("#layout-content").css("margin-bottom"), 10);
			height -= parseInt(list.css("padding-top"), 10);
			height -= parseInt(list.css("padding-bottom"), 10);
			height -= parseInt(list.css("border-top-width"), 10);
			height -= parseInt(list.css("border-bottom-width"), 10);
			if(list.prev("input").length)
				height -= parseInt(list.prev("input").show().height(), 10);
			else
				height -= 10;
			if($("#layout-footer").length)
				height -= $("#layout-footer").outerHeight();
			list.height(height);
		}).trigger("resize");
	},
	init: function(){
		"use strict";
		if($("body.moduleManageCatalogBookstoreArticle").length){
			var list = $("body.moduleManageCatalogBookstoreArticle ul.nav-pills").eq(0);
			ModuleManageCatalogBookstore.bindListResize(list);
			ModuleManageCatalogBookstore.scrollToActiveListItem(list);
		}
		if($("body.moduleManageCatalogBookstoreAuthor").length){
			var list = $("body.moduleManageCatalogBookstoreAuthor ul.nav-pills").eq(0);
			ModuleManageCatalogBookstore.bindListResize(list);
			ModuleManageCatalogBookstore.scrollToActiveListItem(list);
			$("#input_search").on("keyup", ModuleManageCatalogBookstore.onSearchChangeFilterList).focus();
			if($("body.action-add").length)
				$("#input_firstname").focus();
		}
		if($("body.moduleManageCatalogBookstoreCategory").length){
			var list = $("body.moduleManageCatalogBookstoreCategory ul.nav-pills.main").eq(0);
			ModuleManageCatalogBookstore.bindListResize(list);
			ModuleManageCatalogBookstore.scrollToActiveListItem(list);
			$("#input_search").on("keyup", ModuleManageCatalogBookstore.onSearchChangeFilterList).focus();
			if($("body.action-add").length)
				$("#input_firstname").focus();
		}
	},
	onSearchChangeFilterList: function(event){
		"use strict";
		var input = $(this);
		var query = input.val();
		var list  = input.next("ul");
		if(list.length){
			if(query !== input.data("latestQuery")){
				if(query.length){
					list.find("li").each(function(){
						var item = $(this);
						var anchor = item.children("a");
						var selector = ":containsIgnoreCase(" + query + ")";
						anchor.is(selector) ? item.show() : item.hide();
					});
				}else{
					list.find("li").show();
				}
				input.data("latestQuery", query);
			}
			else if(event.keyCode === 13){
				list.find("li:visible:eq(0) a").each(function(){
					document.location.href = $(this).attr("href");
				});
			}
		}
	},
	scrollToActiveListItem: function(list){
		"use strict";
		if(list.find("li.active").length){
			var pos = list.find("li.active").offset().top;
			pos -= list.offset().top;
			if(pos > list.height() / 2){
				pos -= list.height() / 2;
				pos	+= list.find("li.active").outerHeight() / 2;
	//			console.log(pos);
				list.animate({scrollTop: pos}, 0);
			}
		}
		list.css("margin", 0);
	},
	setArticleTab: function(tabKey){
		"use strict";
		$.ajax("./manage/catalog/bookstore/article/ajaxSetTab/"+tabKey);
	},
	setAuthorTab: function(tabKey){
		"use strict";
		$.ajax("./manage/catalog/bookstore/author/ajaxSetTab/"+tabKey);
	},
	setCategoryTab: function(tabKey){
		"use strict";
		$.ajax("./manage/catalog/bookstore/category/ajaxSetTab/"+tabKey);
	}
};

$(document).ready(function(){
	ModuleManageCatalogBookstore.init();
});
