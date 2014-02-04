
function addArticleTag(articleId){
	var tag = $("#input_tag").val();
	if(tag.length){
		document.location.href = "./manage/catalog/article/addTag/"+articleId+"/"+tag;
		$("#input_tag").val("");
	}
}

function scrollToActiveListItem(list){
	if(list.find("li.active").size()){
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
}

function bindListResize(list){
	$(window).resize(function(){
		list.css({overflow: "auto", "overflow-y": "scroll"}).show();
		var height = $(window).height();
		height -= list.offset().top;
		height -= parseInt($("#layout-content").css("margin-bottom"), 10);
		height -= parseInt(list.css("padding-top"), 10);
		height -= parseInt(list.css("padding-bottom"), 10);
		height -= parseInt(list.css("border-top-width"), 10);
		height -= parseInt(list.css("border-bottom-width"), 10);
		if(list.prev("input").size())
			height -= parseInt(list.prev("input").show().height(), 10);
		else
			height -= 10;
		if($("#layout-footer").size())
			height -= $("#layout-footer").outerHeight();
		list.height(height);
	}).trigger("resize");
}

function setArticleTab(tabKey){
	$.ajax("./manage/catalog/article/ajaxSetTab/"+tabKey);
}

function onSearchChangeFilterList(event){
	"use strict";
	var input = $(this);
	var query = input.val();
	var list  = input.next("ul");
	if(list.size()){
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
}

$(document).ready(function(){
	if($("body.moduleManageCatalogCategory").size()){
		var list = $("body.moduleManageCatalogCategory ul.nav-pills.main").eq(0);
		bindListResize(list);
		scrollToActiveListItem(list);
		$("#input_search").bind("keyup", onSearchChangeFilterList).focus();
		if($("body.action-add").size())
			$("#input_firstname").focus();
	}
	if($("body.moduleManageCatalogAuthor").size()){
		var list = $("body.moduleManageCatalogAuthor ul.nav-pills").eq(0);
		bindListResize(list);
		scrollToActiveListItem(list);
		$("#input_search").bind("keyup", onSearchChangeFilterList).focus();
		if($("body.action-add").size())
			$("#input_firstname").focus();
	}
	if($("body.moduleManageCatalogArticle").size()){
		var list = $("body.moduleManageCatalogArticle ul.nav-pills").eq(0);
		bindListResize(list);
		scrollToActiveListItem(list);
	}
});
