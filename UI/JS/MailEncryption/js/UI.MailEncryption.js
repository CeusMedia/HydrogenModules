if(typeof UI === "undefined")                                                   //  no UI object defined yet
	var UI = {};                                                                //  define empty UI object

UI.MailEncryption = {};
UI.MailEncryption.decrypt = function(){
	$("body span.encrypted-mail").each(function(){								//  find all spans holding an email address
		var addr = $(this).data('name') + "@" + $(this).data("host");			//  assemble email address
		var label = addr;														//  link label is address for now
		var link = $("<a></a>").attr("href", "mailto:"+addr);					//  create link element
		if($(this).data("subject"))												//  subject has been defined
			addr += '?subject=' + encodeURI($(this).data("subject"));			//  append subject to mail address
		link.html($(this).html().length ? $(this).html() : label);				//  set link content
		link.addClass($(this).data("class") ? $(this).data("class") : "mail");	//  set link class
		$(this).replaceWith(link);												//  replace span by link
	});
};
