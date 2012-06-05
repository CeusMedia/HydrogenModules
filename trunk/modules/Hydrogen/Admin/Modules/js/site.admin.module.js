
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
