var InfoManual = {};

InfoManual.UI = {};

InfoManual.UI.Filter = {
	selectorCategoryId: '#select_category',
	init: function(){
		jQuery(InfoManual.UI.Filter.selectorCategoryId).bind('change', this.onFilterCategoryChange);
	},
	onFilterCategoryChange: function(){
//		var categoryId = jQuery(InfoManual.UI.Filter.selectorCategoryId).val();
		var categoryId = jQuery(this).val();
		document.location.href = './info/manual/category/' + categoryId;
	}
};

InfoManual.UI.Tree = {
	init: function(selectorTree){
		jQuery(selectorTree+" li.list-group-item").bind("click", this.onFolderToggle);
	},
	onFolderToggle: function(event){
		var folder = jQuery(this);
		var link = folder.children("a");
		var pageId = parseInt(link.prop('href').split(/\//).pop(), 10);
		jQuery.ajax({
			url: './ajax/info/manual/setBranchStatus',
			method: 'POST',
			data: {
				nodeId: folder.data('nodeid'),
				pageId: pageId,
				pageUrl: link.prop('href'),
			}
		});
	}
};

InfoManual.UI.Page = {
	renderer: "client",

	init: function(selectorContainer, selectorIndex){
		InfoManual.UI.Page.renderMarkdown(selectorContainer);
		InfoManual.UI.Page.renderIndex(selectorContainer, selectorIndex, [1,2,3,4,5], 'level-');
	},


	renderMarkdown: function(selectorContainer){
		var markdown = $(selectorContainer);
		if(markdown.length){
			if(this.renderer === "client"){
				var converter = new Markdown.Converter();
				var content = markdown.html().replace(/&gt;/, ">").replace(/&lt;/, "<");
				markdown.html(converter.makeHtml(content)).show();
			}
			else if(this.renderer === "server-ajax"){
				if(typeof AJAJ !== "undefined"){
					AJAJ.post("./helper/markdown/ajaxRender", {content: markdown.html()}, function(data){
						markdown.html(data).show();
					}, function(json){});
				}else{
					var content = markdown.html()/*.replace(/&gt;/, ">").replace(/&lt;/, "<")*/;
					$.ajax({
						url: "./helper/markdown/ajaxRender",
						dataType: "html",
						data: {content: content},
						method: "POST",
						success: function(html){markdown.html(html).show();}
					});
				}
			}
		}
	},

	renderIndex: function(selectorSource, selectorTarget, levels, itemClassPrefix){
		if(!selectorSource)
			throw "No source selector given."
		if(!jQuery(selectorSource).length)
			throw "No source element found by source selector."
		if(!selectorTarget)
			throw "No target selector given."
		if(!jQuery(selectorTarget).length)
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
			anchorLink	= jQuery("<a></a>").html(label).data('pageId', id).attr("href","#"+id);
			className	= itemClassPrefix + this.nodeName.substr(1);
			listItem	= jQuery("<li></li>").append(anchorLink).addClass(className);
			list.append(listItem);
		});
		if(list.children().length >= 3){
			var pathname = window.location.href.split('#')[0];
			list.find('a[href^="#"]').each(function() {
				$(this).attr('href', pathname + $(this).attr('href'));
			});
			jQuery(selectorTarget).append(list).show();
		}
	}
};
