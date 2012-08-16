MailDecrypt = function(selector,pattern){
	if(typeof pattern == "undefined")											//  no special regex pattern defined
		pattern = /\[mail:([\S]+)#([^@]+)(@.+)?\]/g;							//  use default regex pattern
	var attr, i, matches, string, user, host, repl;								//  declare variables
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
			user = matches[1].replace(/<.+?>/g,'');								//  get clean user
			host = matches[2].replace(/<.+?>/g,'');								//  get clean host
			repl = '<a href="mailto:'+user+'@'+host+'"'+attr+'>$1@$2</a>';		//  
			string = string.replace(pattern, repl);								//  replace node conten by mail link
			$(selector).html(string);											//  realize new linked content in node
		}
	}
}
