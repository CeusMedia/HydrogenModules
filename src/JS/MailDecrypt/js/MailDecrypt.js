var MailDecrypt = function(){
	$("body span.encrypted-mail").each(function(){								//  find all spans holding an email address
		var addr = $(this).data('name') + "@" + $(this).data("host");			//  assemble email address
		var link = $("<a></a>").attr("href", "mailto:"+addr).html(addr);		//  create link element
		link.addClass($(this).data("class") ? $(this).data("class") : "mail");	//  set link class
		link.html($(this).html().length ? $(this).html() : addr);				//  set link content
		$(this).replaceWith(link);												//  replace span by link
	});
};

