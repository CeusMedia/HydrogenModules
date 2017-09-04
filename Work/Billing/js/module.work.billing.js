var WorkBilling = {
	round: function(number, precision){
		precision = Math.abs(parseInt(precision)) || 0;
		var coefficient = Math.pow(10, precision);
		var rounded = Math.round(number * coefficient) / coefficient;
		return rounded;
		return rounded.toPrecision(precision);
	},
	init: function(){
		jQuery("form input.input-number").each(function(){
			return;
			var input = jQuery(this);
			input.bind("keydown", function(event){
				if(event.keyCode == 188){
					input.val(input.val() + '.');
					if(!isNaN(input.val()))
						input.data('oldValue', input.val());
					event.preventDefault();
					return false;
				}
				if(!isNaN(input.val()))
					input.data('oldValue', input.val());
			});
			input.bind("keyup", function(event){
				if(isNaN(input.val()))
					input.val(input.data('oldValue')).trigger("keyup");

				if(typeof input.data('maxValue') !== "undefined"){
					if(input.val() > input.data('maxValue'))
						input.val(input.data('oldValue')).trigger("keyup");
				}
				if(typeof input.data('minValue') !== "undefined"){
					if(input.val() < input.data('minValue'))
						input.val(input.data('oldValue')).trigger("keyup");
				}
				if(typeof input.data('maxPrecision') !== "undefined"){
					var valueCur = input.val();
					var valueNew = WorkBilling.round(input.val(), input.data('maxPrecision'));
					if(valueCur != valueNew)
						input.val(valueNew).trigger("keyup");
				}
			});
		})
	}
};
WorkBilling.Bill = {
	updateAmounts: function(elem){
		var id = jQuery(elem).attr("id");
		var taxRate = jQuery("#input_taxRate").val();
		if(isNaN(taxRate) || taxRate.length == 0)
			return;
		if(id == "input_amountNetto"){
			var value = jQuery(elem).val() * ( 1 + taxRate / 100);
			value = Math.round(value * 100, 2) / 100;
			jQuery("#input_amountTaxed").val(value);
			var tax = value - jQuery(elem).val();
			jQuery("#output_tax").val(Math.round(tax * 100, 2) / 100);
		}
		if(id == "input_amountTaxed"){
			var value = jQuery(elem).val() / ( 1 + taxRate / 100);
			value = Math.round(value * 100, 2) / 100;
			jQuery("#input_amountNetto").val(value);
			var tax = jQuery(elem).val() - value;
			jQuery("#output_tax").val(Math.round(tax * 100, 2) / 100);
		}
		if(id == "input_taxRate"){
			if(!isNaN(jQuery("#input_amountNetto").val())){
				if(jQuery("#input_amountNetto").val().length > 0){
					var value = jQuery("#input_amountNetto").val() * ( 1 + taxRate / 100);
					value = Math.round(value * 100, 2) / 100;
					jQuery("#input_amountTaxed").val(value);
					var tax = value - jQuery("#input_amountNetto").val();
					jQuery("#output_tax").val(Math.round(tax * 100, 2) / 100);
				}
			}
		}
	},
};
WorkBilling.Reserve = {
	updatePersonalize: function(elem){
		var selector = jQuery(elem);
		console.log(selector);
		console.log(selector.val());
		if(selector.val() > 0){
			jQuery("#input_personalize").removeAttr("readonly");
			jQuery("#input_personalize").val(jQuery("#input_personalize").data("oldValue"));
		}
		else{
			jQuery("#input_personalize").data("oldValue", jQuery("#input_personalize").val())
			jQuery("#input_personalize").attr("readonly", "readonly");
			jQuery("#input_personalize").val("1");
		}
	}
};
