var InfoManual = {
	init: function(selectorContainer, selectorIndex){
		InfoManual.renderMarkdown(selectorContainer);
		InfoManual.renderIndex(selectorContainer, selectorIndex, [1,2,3,4,5], 'level-');
	},
	renderMarkdown: function(selectorContainer){
		var markdown = $(selectorContainer);
		if(markdown.size()){
			var converter = new Markdown.Converter();
			markdown.html(converter.makeHtml(markdown.html())).show();
		}
	},
	renderIndex: function(selectorSource, selectorTarget, levels, itemClassPrefix){
		if(!selectorSource)
			throw "No source selector given."
		if(!jQuery(selectorSource).size())
			throw "No source element found by source selector."
		if(!selectorTarget)
			throw "No target selector given."
		if(!jQuery(selectorTarget).size())
			return;
		if(!levels.length)
			throw "No heading levels given."
		levels	= "h"+levels.join(",h");
		itemClassPrefix = typeof itemClassPrefix === "undefined" ? "" : itemClassPrefix;
		var list = jQuery("<ul></ul>").addClass("index-list");
		jQuery(selectorSource).find(levels).each(function(nr){
			var label, id, anchorLink, className, listItem;
			label = jQuery(this).html();
			if(!label.length)
				return;
			id = label.replace(/[^a-z0-9- ]/ig, "").replace(/ +/g, "_");
			jQuery(this).attr("id", id);
			anchorLink	= jQuery("<a></a>").html(label).attr("href","#"+id);
			className	= itemClassPrefix + this.nodeName.substr(1);
			listItem	= jQuery("<li></li>").append(anchorLink).addClass(className);
			list.append(listItem);
		});
		if(list.children().size() >= 3){
			var pathname = window.location.href.split('#')[0];
			list.find('a[href^="#"]').each(function() {
				$(this).attr('href', pathname + $(this).attr('href'));
			});
			jQuery(selectorTarget).append(list).show();
		}
	}
};
