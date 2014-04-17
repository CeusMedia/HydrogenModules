var ModuleAdminModule = {
	init: function(selector, options){
	//	console.log( "calling InstantFilter" );
		var options	= $.extend({
			caseSense: false,
			skipKeys: [],
			durationFadeIn: 600,
			durationFadeOut: 200,
			selectorGroups: 'fieldset',
			selectorItems: 'div.module',
			selectorReset: '#search #search-reset',
			autoFocus: true,
			onSearch: function(instance){
				var o = instance.options;
				$(o.selectorItems).parent(o.selectorGroups).each(function(){
					var got = $(this).find(o.selectorItems).filter(".found").stop(true,true);
					var dur = got.size() ? o.durationFadeIn : o.durationFadeOut;
					got.size() ? $(this).fadeIn(dur) : $(this).fadeOut(dur);
				});
			},
			onReset: function(instance){
				var o = instance.options;
				var fieldSets = $(o.selectorItems).parent(o.selectorGroups);
				fieldSets.not(":visible").fadeIn(o.durationFadeIn);
			}
		}, options);
		new InstantFilter(selector,options);	
	}
};

$(document).ready(function(){
	var selectorModuleFilter = 'div#search input#input_query';
	if($(selectorModuleFilter).size())
		ModuleAdminModule.init(selectorModuleFilter);

	$("button.auto-back").each(function(){
		$(this).removeAttr("disabled").removeAttr("readonly");
		$(this).bind("click",function(){
			history.back();
		});
	});

	$("button.form-trigger").bind("click",function(){
		$(this).parent().children().show();
		$(this).parent().find(".hint").hide();
		$(this).hide();
	});

	$("button.legend-form-trigger").bind("click",function(){				//  @todo	kriss: is this used or deprecated ?
		$(this).hide();														//  @todo	kriss: can be combined with next line
		$(this).parent().parent().children().show();
	});
});
