var FormNoteFilter = {
	form: null,
	__init: function(){
		this.form = $("#form_note_filter");
		if(!this.form.size())
			return false;
		this.form.find("#input_filter_query").bind("change",function(){
			this.form.submit();
		});
		if(this.form.find("#input_filter_query").val().length)
			this.form.find("#reset-button-container").show();
		this.form.find("#reset-button-trigger").bind("click",this.clearQuery);

		$("li.note .note-title a").each(function(){
			$(this).html(FormNoteFilter.highlightQuery($(this).html(),query));
		})

		return true;
	},

	addToQuery: function(text){
		console.log(text);
		if(!text.length)
			return;
		var input = $("#input_filter_query");
		if(input.val().length)
			text = input.val()+' '+text;
		this.form.submit();
	},

	clearQuery: function(){
		if(!FormMissionFilter.form.size())
			return false;
		FormMissionFilter.form.find("#filter_query").val("");
		FormMissionFilter.form.submit();
		return true;
	},

	highlight: function(data,search,prefix,suffix){
		search = typeof search === 'string' ? search : '';
		prefix = typeof prefix !== 'undefined' ? prefix : '<b>';
		suffix = typeof suffix !== 'undefined' ? suffix : '</b>';
		if(!search.length)
			return data;
		return data.replace( new RegExp( search.pregQuote(), 'gi' ), prefix + search + suffix );
	},

	highlightQuery: function(string,query){
		parts	= query.split(' ');
		for(i in parts){
			if(parts[i].length >= 1)
				string =  FormNoteFilter.highlight(string, parts[i],'<span class="high term-'+i+'">','</span>');
		}
		return string;
	}
};
