var ModulePaymentMangopayBankPayin = {
	currencyFirst: false,
	numberSeparator: false,
	wallets: [],
	init: function(){
		this.onCurrencyChange();
	},
	renderMoney: function(money, currencyFirst, separator){
		if(typeof currencyFirst === "undefined")
	 		currencyFirst = true;
		if(typeof separator === "undefined")
			separator = ".";

		amount = (money.Amount / 100).toFixed(2);
		amount = (separator !== ".") ? amount.replace(".", separator) : amount;
		label = currencyFirst ? money.Currency+" "+amount : amount+" "+money.Currency;
		return label;
	},
	renderWalletOption: function(wallet){
		var self = ModulePaymentMangopayBankPayin;
		var money = self.renderMoney(
			wallet.Balance,
			self.currencyFirst,
			self.numberSeparator
		);
		var label = wallet.Description+" ("+money+")";
		return jQuery("<option></option>").html(label).val(wallet.Id);
	},
	onCurrencyChange: function(){
		var self = ModulePaymentMangopayBankPayin;
		var currency = jQuery("#input_currency").val();
		var select = jQuery("#input_walletId").html("");
		jQuery(self.wallets).each(function(){
			if(this.Currency !== currency)
				return;
			select.append(self.renderWalletOption(this));
		});
		if(select.find("option").length == 1)
			select.attr("readonly", "readonly");
		else
			select.removeAttr("readonly");
	}
};
