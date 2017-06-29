ContextHelp = {
	data: [],
	lastTimeout: [],
	visible: false,
	classActiveToggle: 'btn-success',
	classActiveNavLinkToggle: 'active',
	add: function(key, selector, content){
		this.data[key] = {
			selector: selector,
			content: content
		};
	},
	loadHelp: function(data){
		for(var i=0; i<data.length; i++)
			ContextHelp.add(data[i][0], data[i][1], data[i][2]);
	},
	prepare: function(visible){
		ContextHelp.visible = true && visible;
		ContextHelp.prepareHelpContents();
		ContextHelp.prepareHelpMasks();
		ContextHelp.prepareNavLinkToggle();
		ContextHelp.updateToggle();
		ContextHelp.updateNavLinkToggle();
	},
	prepareHelpMasks: function(){
		$(".has-context-help").each(function(nr){
			var elem = $(this);
			var key = elem.data('help-key');
			var mask = $('<div></div>').attr('id', 'context-help-mask-' + nr).addClass('context-help-mask');
			ContextHelp.visible ? mask.show() : mask.hide();
			var help = $('<div></div>').attr('id', 'context-help-layer-' + nr).addClass('context-help-layer');
			mask.bind('click', function(event){
//				event.preventDefault();
			});
			mask.css('width', elem.outerWidth() + 1);
			mask.css('height', elem.outerHeight() + 1);
			mask.bind('mouseenter', {nr: nr}, function(event){
				var mask = $(this);
				var help = mask.next();
				help.stop(true, true);
				var pos = mask.parent().position();
				var outsideX = $("body").outerWidth() < pos.left + 250;
				var outsideY = $("body").outerHeight() < pos.top + help.outerHeight() + 50;
				if(outsideX)
					help.addClass('from-right');
				if(outsideY)
					help.addClass('from-bottom');
				mask.addClass('focused');
				if(ContextHelp.lastTimeout[key])
					clearTimeout(ContextHelp.lastTimeout);
				help.fadeIn(300);
			});
			mask.bind('mouseleave', {nr: nr}, function(event){
				$(this).removeClass('focused');
				var help = $(this).next();
				ContextHelp.lastTimeout[key] = setTimeout(function(){
					if(!help.hasClass('focused'))
						help.stop(true, true).fadeOut(150,function(){
							$(this).removeClass('from-right from-bottom');
						});
				},80);
			});
			help.bind('click', function(event){
				event.preventDefault();
				event.stopPropagation();
			});
			help.bind('mouseenter', function(event){
				if(ContextHelp.lastTimeout[key])
					clearTimeout(ContextHelp.lastTimeout[key]);
				$(this).addClass('focused');
			});
			help.bind('mouseleave', function(event){
				$(this).removeClass('focused');
				var help = $(this);
				var mask = help.prev();
				if(ContextHelp.lastTimeout[key])
					clearTimeout(ContextHelp.lastTimeout[key]);
				ContextHelp.lastTimeout[key] = setTimeout(function(){
					if(!mask.hasClass('focused'))
						mask.trigger('mouseleave');

				}, 160);
			});
			help.html(elem.data('help-content'));
			elem.append(mask).append(help);
		});
	},
	prepareHelpContents: function(){
		for(var i in ContextHelp.data){
			$(ContextHelp.data[i].selector).each(function(nr){
				$(this).addClass('has-context-help').addClass('context-help-' + i);
				if($.inArray($(this).css('position'), ['absolute', 'fixed', 'relative']) < 0)
					$(this).css('position', 'relative');
				var help = $(this).children('div.context-help-layer');
				$(this).data('help-content', ContextHelp.data[i].content);
				$(this).data('help-key', i);
			});
		}
	},
	prepareNavLinkToggle: function(){
		$("ul.nav li>a[href=\"./#ContextHelp\"]").each(function(nr){
			$(this).bind("click", function(e){
				var item = $(this).parent();
				var visible = ContextHelp.toggleMasks();
				visible ? item.addClass("active") : item.removeClass("active");
				e.preventDefault();
			});
		});
		ContextHelp.updateNavLinkToggle();
	},
	updateNavLinkToggle: function(){
		$("ul.nav li>a[href=\"./#ContextHelp\"]").each(function(nr){
			$(this).parent().removeClass(ContextHelp.classActiveNavLinkToggle);
			if(ContextHelp.visible)
				$(this).parent().addClass(ContextHelp.classActiveNavLinkToggle);
		});
	},
	updateToggle: function(){
		$("#help-switch-toggle").each(function(nr){
			$(this).removeClass(ContextHelp.classActiveToggle);
			if(ContextHelp.visible)
				$(this).addClass(ContextHelp.classActiveToggle);
		});
	},
	toggleMasks: function(){
		ContextHelp.visible ? ContextHelp.hideMasks() : ContextHelp.showMasks();
		return ContextHelp.visible;
	},
	hideMasks: function(){
		if(!ContextHelp.visible)
			return;
		$(".has-context-help div.context-help-mask").fadeOut(100);
		ContextHelp.visible = false;
		ContextHelp.updateToggle();
		ContextHelp.updateNavLinkToggle();
	},
	showMasks: function(){
		if(ContextHelp.visible)
			return;
		$(".has-context-help div.context-help-mask").fadeIn(100);
		ContextHelp.visible = true;
		ContextHelp.updateToggle();
		ContextHelp.updateNavLinkToggle();
	}
};
