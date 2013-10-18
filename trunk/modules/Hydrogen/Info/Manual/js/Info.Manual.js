$(document).ready(function(){
	var markdown = $(".markdown");
	var converter = new Markdown.Converter();
	markdown.html(converter.makeHtml(markdown.html())).show();
});
