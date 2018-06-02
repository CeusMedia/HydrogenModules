var FormNoteFilter = {
	form: null,
	__init: function(){
		this.form = $("#form_note_filter");
		if(!this.form.length)
			return false;
		if(this.form.find("#input_filter_query").val().length)
			this.form.find("#reset-button-container").show();
		this.form.find("#reset-button-trigger").on("click",this.clearQuery);

		$("li.note .note-title a").each(function(){
			$(this).html(FormNoteFilter.highlightQuery($(this).html(),query));
		})
		$("li.note a.list-item-tag-link").each(function(){
			$(this).html(FormNoteFilter.highlightQuery($(this).html(),query));
		})
		$("li.note span.list-item-tag").each(function(){
			$(this).html(FormNoteFilter.highlightQuery($(this).html(),query));
		})

		$("ul.tags-list-inline button.tag-add").on("click",function(){
			document.location.href = "./work/note/addSearchTag/"+$(this).data("tag-id");
		});
		$("ul.tags-list-inline button.tag-remove").on("click",function(){
			document.location.href = "./work/note/forgetTag/"+$(this).data("tag-id");
		});

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
		if(!FormNoteFilter.form.length)
			return false;
		FormNoteFilter.form.find("#input_filter_query").val("");
		FormNoteFilter.form.submit();
		return true;
	},

	highlight: function(data,search,prefix,suffix){
		search = typeof search === 'string' ? search : '';
		prefix = typeof prefix !== 'undefined' ? prefix : '<b>';
		suffix = typeof suffix !== 'undefined' ? suffix : '</b>';
		if(!search.length)
			return data;
		return data.replace( new RegExp( search.pregQuote(), 'gi' ), function(term){return prefix + term + suffix});
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
