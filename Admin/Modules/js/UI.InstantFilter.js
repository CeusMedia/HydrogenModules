var InstantFilter = function(selectorInput,options){
	var defaultOptions = {
		caseSense: true,
		skipKeys: [32],
		selectorItems: '.searchable',
		selectorReset: null,
		durationFadeIn: 'fast',
		durationFadeOut: 'fast',
		autoFocus: false,
		onItemHide: function(instance, item){},
		onItemShow: function(instance, item){},
		onSearch: function(instance){},
		onReset: function(instance){}
	};
	
	this.options = $.extend(defaultOptions,options);
	this.selectorInput = selectorInput;
	if(this.options.selectorReset){
		$(this.options.selectorReset).bind("click",{instance: this},function(event){
			event.data.instance.reset(event.data.instance);
		});
	}

	if(!this.options.caseSense){
		if(typeof($.expr[':'].icontains) == 'undefined'){
			$.expr[':'].icontains = function(obj, index, meta, stack){
				var text = (obj.textContent || obj.innerText || jQuery(obj).text() || '');
				return text.toLowerCase().indexOf(meta[3].toLowerCase()) >= 0;
			}
		}
	}
	$(this.selectorInput).bind("keyup",{instance: this},function(event){
		event.data.instance.search($(this).val());
	}).bind("keydown",{instance: this},function(event){
		var skip = event.data.instance.options.skipKeys;
		if($.inArray(event.keyCode,skip) >= 0)
			return false;
	});
	if(this.options.autoFocus)
		$(this.selectorInput).focus(); 

	this.reset = function(){
		if(!(this.selectorInput && $(this.selectorInput).size()))
			return;
		if(!(this.options.selectorItems && $(this.options.selectorItems).size()))
			return;
		$(this.selectorInput).val("");
		this.search("");
	}
	
	this.search = function(query){
		var instance = this;
		var options = this.options;
		if(!(this.selectorInput && $(this.selectorInput).size()))
			return;
		if(!(options.selectorItems && $(options.selectorItems).size()))
			return;
		var all = $(options.selectorItems).data('found', 0).stop(true,true);
		if(query.length){
			var items = $(options.selectorItems);
			var parts = query.trim().split(/ /);
			var nrTerms = parts.length;
			for(var i=0; i<parts.length; i++){
				var expr = options.caseSense ? ':contains' : ':icontains';
				var found = items.filter(expr+"('"+parts[i]+"')");
				found.each(function(){
					$(this).data('found', $(this).data('found') + 1);
				});
			}
			items.each(function(){
				var item = $(this);
				if(item.data('found') === nrTerms){
					item.removeClass('hidden').fadeIn(options.durationFadeIn, function(){
						$(this).addClass("found");
						options.onItemShow(instance, item);
					});
				}
				else{
					item.removeClass("found").fadeOut(options.durationFadeOut, function(){
						$(this).addClass('hidden');
						options.onItemHide(instance, item);
					});
				}
			});
			this.options.onSearch(this);
			if(this.options.selectorReset && $(this.options.selectorReset))
				$(options.selectorReset).fadeIn(options.durationFadeIn);
		}
		else{
			all.filter(".hidden").fadeIn(this.options.durationFadeIn).removeClass("hidden");
			if(this.options.selectorReset && $(this.options.selectorReset))
				$(this.options.selectorReset).fadeOut(this.options.durationFadeOut);
			this.options.onReset(this);
		}
	}
};
