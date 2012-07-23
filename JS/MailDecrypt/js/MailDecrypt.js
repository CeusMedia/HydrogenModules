MailDecrypt = function(selector,pattern){
	if(typeof pattern == "undefined")
		pattern = /\[mail:([\S]+)#([^@]+)(@.+)?\]/g;
	var attr,i,matches,string;
	string = $(selector).html();
	if(string.match(pattern)){
		matches = pattern.exec(string);
		if(matches.length > 2){
			if(typeof matches[3] != "undefined"){
				attr = matches[3].substr(1).split("@");
				for(i=0; i<attr.length; i++)
					attr[i] = ' '+attr[i].replace(/:/,'="')+'"';
				attr = attr.join("");
			}
			string = string.replace(pattern,'<a href="mailto:$1@$2"'+attr+'>$1@$2</a>');
			$(selector).html(string);
		}
	}
}
