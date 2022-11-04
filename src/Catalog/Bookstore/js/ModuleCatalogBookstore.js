var ModuleCatalogBookstoreCategoryIndex = {
	container: null,
	find: function(selector){
		return ModuleCatalogBookstoreCategoryIndex.container.find(selector);
	},
	closeAllBranches: function(){
		ModuleCatalogBookstoreCategoryIndex.find("ul.branches > li.branch").each(function(){
			var hitarea = jQuery(this).children(".hitarea")
			if(hitarea.hasClass("open"))
				hitarea.trigger("click");
		});
	},
	openAllBranches: function(){
		ModuleCatalogBookstoreCategoryIndex.find("ul.branches > li.branch").each(function(){
			var hitarea = jQuery(this).children(".hitarea")
			if(hitarea.hasClass("closed"))
				hitarea.trigger("click");
		});
	},
	init: function(selector){
		ModuleCatalogBookstoreCategoryIndex.container = typeof selector == "undefined" ? $("body") : $(selector);
		ModuleCatalogBookstoreCategoryIndex.setupCollapsable();
		ModuleCatalogBookstoreCategoryIndex.setupFilterable();
	},
	setupCollapsable: function(){
		ModuleCatalogBookstoreCategoryIndex.find("span.hitarea:not(.empty)").click(function(){
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
	},
	setupFilterable: function(){
		ModuleCatalogBookstoreCategoryIndex.find("#input_search").on("input", function(){
			var terms = jQuery(this).val().toLowerCase().trim().split(/\s+/);
			if(terms.length == 1 && terms[0] == ""){
				ModuleCatalogBookstoreCategoryIndex.find("ul.branches li").removeClass("search-miss").removeClass("search-hit");
				ModuleCatalogBookstoreCategoryIndex.find("ul.branches span.hitarea").each(function(){
					if(jQuery(this).hasClass("open"))
						jQuery(this).trigger("click");
				});
				return;
			}
			ModuleCatalogBookstoreCategoryIndex.find("ul.branches > li.branch > ul.topics > li.topic").each(function(){
				var item = jQuery(this);
				var link = item.children("a");
				for(i=0; i<terms.length; i++){
					if(link.html().toLowerCase().indexOf(terms[i]) == -1){
						item.removeClass("search-hit");
						item.addClass("search-miss");
						return;
					}
				}
				item.addClass("search-hit");
			});
			ModuleCatalogBookstoreCategoryIndex.find("ul.branches > li.branch").each(function(){
				var item = jQuery(this);
				var hitarea = item.children(".hitarea");
				var match = true;
				for(i=0; i<terms.length; i++){
					if(item.children("a").html().toLowerCase().indexOf(terms[i]) == -1){
						match = false;
						break;
					}
				}
				if(item.find("ul > li.search-hit").length || match){
					item.removeClass("search-miss").addClass("search-hit");
					if(hitarea.hasClass("closed"))
						hitarea.trigger("click");
				}
				else{
					item.removeClass("search-hit").addClass("search-miss");
					if(hitarea.hasClass("open"))
						hitarea.trigger("click");
				}
			});
		});
	}
};

var ModuleCatalogBookstoreRelatedArticlesSlider = {
	pos: 0,
	animating: false,
	init: function(width){
		var number = $(".related-articles-list-item").length;
		ModuleCatalogBookstoreRelatedArticlesSlider.number = number;
		ModuleCatalogBookstoreRelatedArticlesSlider.width = width;
		$(".related-articles-list").width(number * width);
		$(".related-articles-container").scroll(ModuleCatalogBookstoreRelatedArticlesSlider.onScroll);
		ModuleCatalogBookstoreRelatedArticlesSlider.updateArrows();
	},
	onScroll: function(container){
		if(ModuleCatalogBookstoreRelatedArticlesSlider.animating)
			return;
		var pos = $(this).scrollLeft();
		ModuleCatalogBookstoreRelatedArticlesSlider.pos = Math.round(pos / ModuleCatalogBookstoreRelatedArticlesSlider.width);
		ModuleCatalogBookstoreRelatedArticlesSlider.updateArrows();
	},
	updateArrows: function(){
		if(ModuleCatalogBookstoreRelatedArticlesSlider.pos === 0)
			$(".related-articles-arrow-left").stop(true).animate({opacity: 0.25});
		else
			$(".related-articles-arrow-left").stop(true).animate({opacity: 1});

		if(ModuleCatalogBookstoreRelatedArticlesSlider.pos + 3 === ModuleCatalogBookstoreRelatedArticlesSlider.number)
			$(".related-articles-arrow-right").stop(true).animate({opacity: 0.25});
		else
			$(".related-articles-arrow-right").stop(true).animate({opacity: 1});
	},
	slideToCurrentPosition: function(options){
		var options = $.extend({
			callback: function(){}
		}, options);
		var pos = ModuleCatalogBookstoreRelatedArticlesSlider.pos * ModuleCatalogBookstoreRelatedArticlesSlider.width;
		ModuleCatalogBookstoreRelatedArticlesSlider.animating = true;
		ModuleCatalogBookstoreRelatedArticlesSlider.updateArrows();
		$(".related-articles-container").stop(true).animate({scrollLeft: pos}, {complete: function(){
			ModuleCatalogBookstoreRelatedArticlesSlider.animating = false;
			options.callback();
		}});
	},
	slideLeft: function(){
		if(ModuleCatalogBookstoreRelatedArticlesSlider.pos > 0){
			ModuleCatalogBookstoreRelatedArticlesSlider.pos--;
			ModuleCatalogBookstoreRelatedArticlesSlider.slideToCurrentPosition();
		}
	},
	slideRight: function(){
		if(ModuleCatalogBookstoreRelatedArticlesSlider.pos + 3 < ModuleCatalogBookstoreRelatedArticlesSlider.number){
			ModuleCatalogBookstoreRelatedArticlesSlider.pos++;
			ModuleCatalogBookstoreRelatedArticlesSlider.slideToCurrentPosition();
		}
	}
};
