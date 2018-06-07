var ShopPaymentStripe = {
	api: null,
	style: {
		base: {		// Add your base input styles here. For example:
			fontSize: "16px"
		}
	},
	init: function(){
		ShopPaymentStripe.api = Stripe(settings.Resource_Payment_Stripe.api_key_public);
	},
	apply: function(selectorInput, idForm, idErrors, idButton){
		if(!ShopPaymentStripe.api)
			ShopPaymentStripe.init();
		var elements = ShopPaymentStripe.api.elements();
		var card = elements.create("card", {style: ShopPaymentStripe.style});	// Create an instance of the card Element
		card.mount(selectorInput);												// Add an instance of the card Element into the `card-element` <div>
		card.addEventListener("change", function(event) {
			var displayError = document.getElementById(idErrors);
			console.log(event);
			if (event.complete)
				jQuery("#"+idButton).prop("disabled", false);
			else
				jQuery("#"+idButton).prop("disabled", "disabled");

			if (event.error) {
				displayError.textContent = event.error.message;
				jQuery("#"+idErrors).addClass("has-error");
			} else {
				displayError.textContent = "";
				jQuery("#"+idErrors).removeClass("has-error");
			}
		});

		var form = document.getElementById(idForm);								// Create a token or display an error when the form is submitted.
		form.addEventListener("submit", function(event) {
			event.preventDefault();
			ShopPaymentStripe.api.createToken(card).then(function(result) {
			if (result.error) {
				var errorElement = document.getElementById(idErrors);			// Inform the customer that there was an error
				errorElement.textContent = result.error.message;
				jQuery("#"+idErrors).addClass("has-error");
			} else {
				ShopPaymentStripe.handleToken(result.token, idForm);			// Send the token to your server
			}
			});
		});
	},
	handleToken: function(token, idForm) {
		var form = document.getElementById(idForm);								// Insert the token ID into the form so it gets submitted to the server
		var hiddenInput = document.createElement("input");
		hiddenInput.setAttribute("type", "hidden");
		hiddenInput.setAttribute("name", "stripeToken");
		hiddenInput.setAttribute("value", token.id);
		form.appendChild(hiddenInput);
		form.submit();															// Submit the form
	}
};
