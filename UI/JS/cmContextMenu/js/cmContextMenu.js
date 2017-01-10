var cmContextMenu = {
	status: 0,
	labels: {},
	container: null,
	containment: "body",
	onShow: function(){},
	onChange: function(){},
	init: function(){
		this.container = $("<div></div>").attr("id","contextMenu").hide();
		this.container.attr("oncontextmenu","cmContextMenu.hide(); return false;").appendTo("body");
		this.container.bind("click", function(event){event.stopPropagation();});
		$(this.containment).bind("click contextmenu", function(event){cmContextMenu.hide(event, false);});
	},
	assignRenderer: function(selector, callback){
		$(selector).attr("oncontextmenu","return false;").mouseup(function(event){
			if(event.which == 3){
				event.preventDefault();
				event.stopPropagation();
				cmContextMenu.show(event, this, callback);
			}
			return true;
		});
	},
	addItem: function(label){
		this.container.find("ul").append($("<li></li>").html(label).addClass("item-label"));
	},
	addLinkItem: function(url, label, icon){
		var icon = $("<i></i>").attr("class", icon);
		var button = $("<a></a>").attr({href: url, class: 'btn btn-small btn-success link-icon'}).bind('click', function(event){
			cmContextMenu.hide(event, true);
		}).append(icon).append('&nbsp;').append(label);
		var item = $("<li></li>").append(button);
		this.container.find("ul").append(item);
	},
	render: function(elem){},
	show: function(event, elem, renderer){
		event.preventDefault();
		event.stopPropagation();
		if(this.status !== 0)
			return false;
		this.status = 1;
		this.container.html("<ul></ul>");
		renderer(this, $(elem));
		var posX = event.pageX - 1;
		var outX = event.pageX + this.container.width() > $(window).width();
		var outY = event.pageY + this.container.height() > $(window).height();
		var posX = event.pageX + (outX ? - this.container.width() + 1 : - 1);
		var posY = event.pageY + (outY ? - this.container.height() + 1 : - 1);
		this.container.css({left: posX, top: posY});
		$("#contextMenu").stop(true, true).fadeIn(250);
		cmContextMenu.onShow($("#contextMenu"), elem);
	},
	hide: function(event, changed){
		if(this.status !== 1)
			return false;
		this.status = 0;
		if(changed)
			cmContextMenu.onChange();
		$("#contextMenu").stop(true, true).fadeOut(150);
		return true;
/*	},
	toggle: function(event){
		if(!this.show(event))
			this.hide(event);*/
	}
};
