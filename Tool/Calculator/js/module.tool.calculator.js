
function openCalculator(){
	jQuery('#modalCalculator').modal();
}

function prepareCalculatorLink(){
	$("ul.nav li>a[href=\"./#Calculator\"]").each(function(nr){
		$(this).bind("click", function(e){
			openCalculator();
			e.preventDefault();
		});
		let calc = new Calculator("#calc-modal");
	});
}

class Calculator{

	constructor(selector){
		this.instance = jQuery(selector);
		this.display = this.instance.find(".calculator-display");
		this.pad = this.instance.find(".calculator-pad");
		this.messenger = this.instance.find(".calculator-messenger");
		this.scroll	= this.instance.find(".calculator-scroll")
		this.display.bind("keydown input", {instance: this}, this.onInput);
		this.pad.find("button.input").bind("click", {instance: this}, function(event){
			var context = event.data.instance;
			context.display.val(context.display.val()+$(this).val());
			context.display.trigger("input").focus();
			this.blur();
		});
		this.pad.find("button.evaluate").bind("click", {instance: this}, function(event){
			var e = jQuery.Event("keydown");
			e.keyCode = 13;
			event.data.instance.display.trigger(e);
		});
		this.pad.find("button.clear").bind("click", {instance: this}, function(event){
			event.data.instance.display.val("").trigger("input");
		});
		this.display.focus();
	}

	onInput(event){
		var context = event.data.instance;
		if(event.type === "keydown"){
			if(event.keyCode == 13){
				if(context.display.val().length > 1){
					jQuery.ajax({
						url: "./tool/calculator",
						method: "POST",
						data: {formula: context.display.val()},
						dataType: "json",
						context: context,
						success: function(response){
							this.handleResponse(response);
						}
					})
				}
			}
		}
		else if(event.type === "input"){
			context.display.removeClass("success").removeClass("error").focus();
			context.messenger.html('');
		}
	}

	handleResponse(response){
		switch(response.status){
			case "success":
				response.data = Math.round(response.data * 1000000) / 1000000;
				response.data = new String(response.data).replace(/\./, ",");
				var line = this.display.val()+" = "+response.data;
				this.scroll.prepend(jQuery("<div></div>").html(line))
				this.display.val(response.data).addClass("success").remove("error");
				break;
			case "error":
				this.display.addClass("error");
				this.messenger.html(response.data);
				break;
		}
		this.display.focus();
	};
}
