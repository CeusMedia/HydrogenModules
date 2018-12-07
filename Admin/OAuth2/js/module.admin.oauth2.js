var ModuleAdminOAuth2 = {
	mapKeys: {
		"title":	"title",
		"icon":		"icon",
		"class":	"className",
		"package":	"composerPackage",
		"options":	"options",
		"scopes":	"scopes",
	},
	providers: null,
	init: function(){
		jQuery("#input_providerKey").on("input", this._onSelectProvider);
		jQuery("#input_options").on("keyup input", this._onValidateOption);
	},
	setProviders: function(providers){
		this.providers = providers;
	},
	_onSelectProvider: function(event){
		if(typeof ModuleAdminOAuth2.providers !== 'object')
			throw "No providers set";
		var providerKey = jQuery(this).val();
		var modelKey, inputKey, value, json;
		for(modelKey in ModuleAdminOAuth2.mapKeys){
			inputKey	= ModuleAdminOAuth2.mapKeys[modelKey];
			value		= "";
			if(providerKey){
				value = ModuleAdminOAuth2.providers[providerKey][modelKey];
				if(inputKey === "title")
					value = providerKey;
				if(inputKey === "scopes")
					value = value.join(",");
				if(inputKey === "options" ){
					json = JSON.stringify(value);
					value = (json.length > 2) ? json : "";
				}
			}
			jQuery("#input_"+inputKey).val(value);
		}
	},
	_onValidateOption: function(event){
		var $input	= $(this);
		var $submit = $input.closest("form").find(":submit");
		if(!$input.val().length){
			$input.removeClass("valid").removeClass("invalid");
			$submit.prop("disabled", null);
			return;
		}
		try{
			var data = JSON.parse($input.val());
			if(typeof data === 'object'){
				$input.removeClass("invalid").addClass("valid");
				$submit.prop("disabled", null);
				return;
			}
		}
		catch( exception ){
			$input.prop("title", exception);
		}
		$input.removeClass("valid").addClass("invalid");
		$submit.prop("disabled", "disabled");
	}
};
