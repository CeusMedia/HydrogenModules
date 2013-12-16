$(document).ready(function(){	
	var markdown = $(".markdown");
	if(markdown.size()){
		var converter = new Markdown.Converter();
		markdown.html(converter.makeHtml(markdown.html())).show();
	}
});
