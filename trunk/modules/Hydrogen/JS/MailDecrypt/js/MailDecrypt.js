var MailDecrypt = function(){
	$("body span.encrypted-mail").each(function(){								//  find all spans holding an email address
		var addr = $(this).data('name') + "@" + $(this).data("host");			//  assemble email address
		var link = $("<a></a>").attr("href", "mailto:"+addr).html(addr);		//  create link element
		link.addClass($(this).data("class") ? $(this).data("class") : "mail");	//  set link class
		link.html($(this).html().length ? $(this).html() : addr);				//  set link content
		$(this).replaceWith(link);												//  replace span by link
	});
	MailDecryptDeprecated("body", "/\[mail:([\S]+)#([^@]+)(@.+)?\]/g");
}

var MailDecryptDeprecated = function(selector,pattern){
//	if(typeof pattern == "undefined")											//  no special regex pattern defined
//		pattern = /\[mail:([\S]+)#([^@]+)(@.+)?\]/g;							//  use default regex pattern
	var attr, i, matches, string, user, host, repl;								//  declare variables
	var element = $(selector);
	if(!$(selector).size())
		return;
	string = $(selector).html();												//  get content of selected node
	if(string.match(pattern)){													//  content matches regex pattern
		matches = pattern.exec(string);											//  get all matches
		if(matches.length > 2){													//  at least user and host found
			if(typeof matches[3] != "undefined"){								//  also found attribute map
				attr = matches[3].substr(1).split("@");							//  
				for(i=0; i<attr.length; i++)									//  
					attr[i] = ' '+attr[i].replace(/:/,'="')+'"';				//  
				attr = attr.join("");											//  
			}
			user = matches[1].replace(/<.+?>/g,'').replace(/&.+?;/g,'');		//  get clean user
			host = matches[2].replace(/<.+?>/g,'').replace(/&.+?;/g,'');		//  get clean host
			repl = '<a href="mailto:'+user+'@'+host+'"'+attr+'>$1@$2</a>';		//  
			string = string.replace(pattern, repl);								//  replace node conten by mail link
			$(selector).html(string);											//  realize new linked content in node
		}
	}
};
