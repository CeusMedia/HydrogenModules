
var UI; if(!UI) UI = {};
UI.DialogLink = function(container, options){
	container.each(function(){
		var element = $(this);
		var options = $.extend({
			width: 600,
			closeOnEscape: true,
			minWidth: 500,
			maxWidth: 900,
			height: 500,
			minHeight: 400,
			maxHeight: 650,
			modal: true,
			autoOpen: false,
			title: element.attr("title"),
			open: function(){
				div.load(element.attr("href"));
			}
		},options);
		var div = $("<div></div>").appendTo($("body")).dialog(options);
		element.click(function(event){
			div.dialog("open");
			event.preventDefault();
		});
	})
};
