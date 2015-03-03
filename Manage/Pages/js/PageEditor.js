var PageEditor = {

	editor: "none",
	format: "HTML",
	imageList: [],
	linkList: [],

	init: function(format){
		$("#tabs-page-editor .optional-trigger").trigger("change");
		$("#tabs-page-editor>ul>li>a").each(function(){
			if($(this).parent().hasClass("active"))
				$(this).parent().parent().parent().find($(this).attr("href")).addClass("active");
		});
		$("#tabs-page-editor>ul>li>a").bind("click", function(){
			var key = $(this).attr("href").replace(/#tab/, "");
			if(key == 3 && PageEditor.editor.toLowerCase() == "codemirror")
				window.setTimeout(PageEditor.setupCodeMirror, 20);
			if(key == 4)
				PageEditor.loadPagePreview();
			$.ajax({
				url: "./manage/page/ajaxSetTab/"+key,
				type: "post"
			});
		});
		if(this.format.toUpperCase() === "MD" && this.editor.toLowerCase() === "tinymce")
			this.editor = 'codemirror';
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
		PageEditor.loadPagePreview();
		$("#page-preview").mouseenter(function(){
			$("#page-preview-mask").hide();
		}).mouseleave(function(){
			$("#page-preview-mask").show();
		});
		PageEditor.initDefaultMetaCopy();
		$("#input_editor").bind("change", PageEditor.setEditor);
	},

	initDefaultMetaCopy: function(){
		$("#meta-defaults dt").each(function(nr, term){
			if($(term).hasClass("meta-default")){
				var link = $("<a></a>").attr("href", "#").html("&larr;&nbsp;kopieren");
				link.bind("click", {term: term, key: $(term).data("key")}, function(event){
					var input = $("#input_"+event.data.key);
					input.val($(event.data.term).next().html());
					event.stopPropagation();
					return false;
				});
				link.wrap("<small></small>");
				$(term).append("<br/>").append(link);
			}
		});
	},

	loadPagePreview: function(){
		var iframe = $("<iframe></iframe>");
		iframe.addClass("preview");
		iframe.attr("src", $("#page-preview").data("url"));
		$("#page-preview-iframe-container").html(iframe);
	},

	setEditor: function(event){
		event.stopPropagation();
		var value = $("#input_editor").val();
		$.ajax({
			url: "./manage/page/ajaxSetEditor/"+value,
			success: function(){
				$("#input_editor").data("original-value", value).trigger("keyup.FormChanges");
				document.location.reload();
			}
		});
	},

	setupCodeMirror: function(){
		var mode = PageEditor.format.toUpperCase() === "HTML" ? "htmlmixed" : "markdown";
		var options = {
			gutter: true,
			fixedGutter: true,
			lineNumbers: true,
			lineWrapping: false,
			indentUnit: 4,
			tabSize: 4,
			indentWithTabs: true,
			theme: "default",
			mode: mode,
			extraKeys: {
				"F11": function(cm) {
					CodeMirror.setFullScreen(cm, !CodeMirror.isFullScreen(cm));
				},
				"Esc": function(cm) {
					if (CodeMirror.isFullScreen(cm)) CodeMirror.setFullScreen(cm, false);
				}
			}
		};
		if(mode === "markdown")
			options.extraKeys['Enter']	= "newlineAndIndentContinueMarkdownList";
		var textarea = $("textarea#input_content");
		if(!textarea.is(":visible"))
			return;
		var mirror = CodeMirror.fromTextArea(textarea.get(0), options);
		mirror.on("change", function(instance, update){
			textarea.next("div.CodeMirror").addClass("changed");
		});
		textarea.data('codemirror',mirror);
		mirror.setSize("auto",textarea.height());	//  set same size as textarea
		$("#hint").html("Press <b>F11</b> for fullscreen editing.");
	},

	setupTinyMCE: function(){
		tinymce.init({
			selector: "textarea#input_content",
			plugins: "textcolor advlist autolink link image lists charmap print autosave code hr paste searchreplace visualblocks wordcount visualchars table contextmenu emoticons",
			document_base_url: $("base").attr("href")+"../",
			content_css: "http://cdn.int1a.net/css/bootstrap.css",
			image_list: PageEditor.imageList,
			link_list: PageEditor.linkList,
//			external_image_list_url: "verwaltung/manage/page/getJsImageList",
			toolbars: "emoticons",
			height: 360,
			language: settings.JS_TinyMCE.auto_language
	/*		template_external_list_url : "js/template_list.js",
			external_link_list_url : "js/link_list.js",
			media_external_list_url : "js/media_list.js",
	*/	});
	}	
};

