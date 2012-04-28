var UI = {

	addToQuery: function(text){
		console.log(text);
		if(!text.length)
			return;
		var input = $("#input-query");
		if(input.val().length)
			text = input.val()+' '+text;
		input.val(text).get(0).form.submit();
	},

	highlight: function(data,search,prefix,suffix){
		search = typeof search === 'string' ? search : '';
		prefix = typeof prefix !== 'undefined' ? prefix : '<b>';
		suffix = typeof suffix !== 'undefined' ? suffix : '</b>';
		if(!search.length)
			return data;
		return data.replace( new RegExp( search.preg_quote(), 'gi' ), prefix + search + suffix );
	},

	highlightQuery: function(string,query){
		parts	= query.split(' ');
		for(i in parts){
			if(parts[i].length >= 1)
				string =  UI.highlight(string, parts[i],'<span class="high term-'+i+'">','</span>');
		}
		return string;
	}
};
