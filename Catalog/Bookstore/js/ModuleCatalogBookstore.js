var ModuleCatalogBookstore = {
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

var ModuleCatalogBookstoreRelatedArticlesSlider = {
	pos: 0,
	animating: false,
	init: function(width){
		var number = $(".related-articles-list-item").size();
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
