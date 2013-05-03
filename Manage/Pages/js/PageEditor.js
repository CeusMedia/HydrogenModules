var PageEditor = {

	editor: "none",

	setEditor: function(elem){
		$.ajax({
			url: "./manage/page/ajaxSetEditor/"+elem.value,
			success: function(){
				document.location.reload();
			}
		});
	},

	init: function(){
		$("input[type='text'], select").on("keyup change", function(){
			var input = $(this);
			if(typeof input.data("original") == "undefined")
				input.data("original", input.val());
			if(input.val() !== input.data("original"))
				input.addClass("changed");
			else
				input.removeClass("changed");
		}).trigger("keyup");
		$("#tabs-page-editor .optional-trigger").trigger("change");
		$("#tabs-page-editor>ul>li>a").each(function(){
			if($(this).parent().hasClass("active"))
				$(this).parent().parent().parent().find($(this).attr("href")).addClass("active");
		});
		$("#tabs-page-editor>ul>li>a").bind("click", function(){
			var key = $(this).attr("href").replace(/#tab/, "");
			if(key == 2 && PageEditor.editor.toLowerCase() == "codemirror")
				window.setTimeout(PageEditor.setupCodeMirror, 20);
			$.ajax({
				url: "./manage/page/ajaxSetTab/"+key,
				type: "post"
			});
		})
		switch(this.editor.toLowerCase()){
			case 'tinymce':
				PageEditor.setupTinyMCE();
				break;
			case 'codemirror':
				PageEditor.setupCodeMirror();
				break;
			default:
				break;
		}
	},

	setupCodeMirror: function(){
		var options = {
			gutter: true,
			fixedGutter: true,
			lineNumbers: true,
			lineWrapping: false,
			indentUnit: 4,
			tabSize: 4,
			indentWithTabs: true,
	//		theme: "elegant",
			mode: "htmlmixed"
		};
		var textarea = $("textarea#input_content");
		if(!textarea.is(":visible"))
			return;
		var mirror = CodeMirror.fromTextArea(textarea.get(0), options);
		mirror.on("change", function(instance, update){
			textarea.next("div.CodeMirror").addClass("changed");
		});
		textarea.data('codemirror',mirror);
		mirror.setSize("auto",textarea.height());	//  set same size as textarea
	},

	setupTinyMCE: function(){
		tinymce.init({
			selector: "textarea#input_content",
			plugins : "textcolor advlist autolink link image lists charmap print autosave code hr paste searchreplace visualblocks wordcount visualchars table contextmenu emoticons",
			document_base_url : $("base").attr("href")+"../",
			content_css : "http://cdn.int1a.net/css/bootstrap.css",
			external_image_list_url : "verwaltung/manage/page/getJsImageList",
			toolbars: "emoticons",
			height: 360,
			language: config.module_js_tinymce_auto_language
	/*		template_external_list_url : "js/template_list.js",
			external_link_list_url : "js/link_list.js",
			media_external_list_url : "js/media_list.js",
	*/	});
	}	
};

