
AdminModuleInstaller = {
	toggleSubmitButton: function(){
		var button = $("button[type=submit]");
		if(button.prop("disabled"))
			button.prop("disabled",null);
		else
			button.prop("disabled",true);
	}
};

$(document).ready(function(){

	new InstantFilter('div#search input#input_query',{
		caseSense: false,
		skipKeys: [32],
		durationFadeIn: 600,
		durationFadeOut: 200,
		selectorItems: 'div.module',
		selectorReset: '#search #search-reset',
		onSearch: function(instance){
			var o = instance.options;
			$(o.selectorItems).parent().each(function(){
				var got = $(this).find(o.selectorItems).not(".hidden").stop(true,true);
				var dur = got.size() ? o.durationFadeIn : o.durationFadeOut;
				got.size() ? $(this).fadeIn(dur) : $(this).fadeOut(dur);
			});
		},
		onReset: function(instance){
			var o = instance.options;
			var fieldSets = $(o.selectorItems).parent();
			fieldSets.not(":visible").fadeIn(o.durationFadeIn);
		}
	});

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

	$("button.legend-form-trigger").bind("click",function(){
		$(this).hide();
		$(this).parent().parent().children().show();
	});
});
