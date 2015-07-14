var cmFormChange = {

	applyTo: function(selector, options){
		$(selector).each(function(){
			cmFormChange.applyToForm($(this), options);
		});
	},

	applyToForm: function(elem, options){
		var form = $(elem).addClass("cmFormChange");
		var inputs = form.find("input").not("[type=checkbox]").add(form.find("select")).add(form.find("textarea"));
		inputs.each(function(){
			var elem = $(this);
			elem.data('original-value', elem.val());
			elem.bind("keyup change change-update", {elem: elem}, function(event){
				var elem = event.data.elem;
				var changed = elem.val() !== elem.data('original-value');
				changed ? elem.addClass('changed') : elem.removeClass('changed');
			});
		});
	}
};
