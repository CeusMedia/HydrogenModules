let Module_Info_Newsletter_Form  = {

	init: function(){
		let selector = "#input_accept, #input_firstname, #input_surname, #input_email";
		let callback = Module_Info_Newsletter_Form.updateFormButton;
		jQuery(selector).on("change", callback);
		callback();
	},
	updateFormButton: function(){
		let button = $("#button_save");
		button.prop("disabled", "disabled");
		if(!$("#input_accept").is(":checked"))
			return;
		if(!$("#input_firstname").val().length)
			return;
		if(!$("#input_surname").val().length)
			return;
		if(!$("#input_email").val().length)
			return;
		button.prop("disabled", null);
	}
};

let Module_Info_Newsletter_Latest = {
	writeHtmlToIframe: function(iframeEl, htmlString) {
		// Bei sandbox="allow-same-origin" ist contentWindow.document verfügbar.
		const doc = iframeEl.contentWindow && iframeEl.contentWindow.document;
		if (!doc) {
			console.error('Kein Zugriff auf iframe.document — evtl. sandbox/Browser-Einschränkung.');
			return;
		}
		doc.open();
		doc.write(htmlString);
		doc.close();
	},
	// Base64 -> Uint8Array -> UTF-8-String
	base64ToUtf8: function(base64) {
		const binary = atob(base64); // decodiert Base64 -> binary string (latin1)
		const bytes = Uint8Array.from(binary, c => c.charCodeAt(0));
		// TextDecoder wandelt UTF-8-Bytes in String
		return new TextDecoder().decode(bytes);
	}
};

