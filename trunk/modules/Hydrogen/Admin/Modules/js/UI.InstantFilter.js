var InstantFilter = function(selectorInput,options){
	var defaultOptions = {
		caseSense: true,
		skipKeys: [32],
		selectorItems: '.searchable',
		selectorReset: null,
		durationFadeIn: 'fast',
		durationFadeOut: 'fast',
		autoFocus: false,
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
		if(!(this.selectorInput && $(this.selectorInput).size()))
			return;
		if(!(this.options.selectorItems && $(this.options.selectorItems).size()))
			return;
		var all = $(this.options.selectorItems).stop(true,true);
		if(query.length){
			var expr = this.options.caseSense ? ':contains' : ':icontains';
			var got = $(this.options.selectorItems+expr+"('"+query+"')");
			all.not(got).filter(":visible").addClass('hidden').fadeOut(this.options.durationFadeOut);
			got.not(":visible").removeClass('hidden').fadeIn(this.options.durationFadeIn);
			if(this.options.selectorReset && $(this.options.selectorReset))
				$(this.options.selectorReset).fadeIn(this.options.durationFadeIn);
			this.options.onSearch(this);
		}
		else{
			all.filter(".hidden").fadeIn(this.options.durationFadeIn).removeClass("hidden");
			if(this.options.selectorReset && $(this.options.selectorReset))
				$(this.options.selectorReset).fadeOut(this.options.durationFadeOut);
			this.options.onReset(this);
		}
	}
};
