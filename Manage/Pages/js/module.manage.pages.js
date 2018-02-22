var ModuleManagePages = {};
ModuleManagePages.PageEditor = {

	pageId: null,
	pageIdentiffier: null,
	parentId: null,
	editor: "none",
	editors: [],
	format: "HTML",
	imageList: [],
	linkList: [],
	frontendUri: null,

	init: function(format){
//		console.log('init');
		if($("#tabs-page-editor").size()){
			$("#tabs-page-editor .optional-trigger").trigger("change");
			$("#tabs-page-editor>ul>li>a").each(function(){
				if($(this).parent().hasClass("active"))
					$(this).parent().parent().parent().find($(this).attr("href")).addClass("active");
			});
			$("#tabs-page-editor>ul>li>a").bind("click", function(){
				var key = $(this).attr("href").replace(/#tab/, "");
				if(key == 3){
					if( ModuleManagePages.PageEditor.editor.toLowerCase() == "codemirror")
						window.setTimeout(ModuleManagePages.PageEditor.setupCodeMirror, 20);
					if( ModuleManagePages.PageEditor.editor.toLowerCase() == "ace")
						window.setTimeout(ModuleManagePages.PageEditor.setupAce, 20);
				}
				if(key == 4)
					ModuleManagePages.PageEditor.loadPagePreview();
				$.ajax({
					url: "./manage/page/ajaxSetTab/"+key,
					type: "post"
				});
			});
			if(this.format.toUpperCase() === "MD" && this.editor.toLowerCase() === "tinymce")
				this.editor = 'codemirror';
			switch(this.editor.toLowerCase()){
				case 'tinymce':
					ModuleManagePages.PageEditor.setupTinyMCE();
					break;
				case 'codemirror':
					ModuleManagePages.PageEditor.setupCodeMirror();
					break;
				case 'ace':
					ModuleManagePages.PageEditor.setupAce();
					break;
				default:
					break;
			}
			ModuleManagePages.PageEditor.loadPagePreview();
			$("#page-preview").mouseenter(function(){
				$("#page-preview-mask").hide();
			}).mouseleave(function(){
				$("#page-preview-mask").show();
			});
			ModuleManagePages.PageEditor.initDefaultMetaCopy();
			$("#input_page_editor").bind("change", ModuleManagePages.PageEditor.setEditor);
		}
		ModuleManagePages.PageEditor.initSortable();
	},

	initSortable: function(){
		$("#manage-page-tree ul").sortable({
			stop: function(event, ui) {
				var pageIds = [];
				ui.item.parent().children("li").each(function(){
					pageIds.push($(this).data("pageId"));
				});
				$.ajax({
					url: "./manage/page/ajaxOrderPages",
					data: {pageIds: pageIds},
					method: "POST",
					success: function(){}
				});
			}
		});
	},

	initDefaultMetaCopy: function(){
//		console.log('initDefaultMetaCopy');
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
		var value = $("#input_page_editor").val();
		$.ajax({
			url: "./manage/page/ajaxSetEditor/"+value,
			success: function(){
				$("#input_page_editor").data("original-value", value).trigger("keyup.FormChanges");
				document.location.reload();
			}
		});
	},

	setupAce: function(){
		if(jQuery("textarea#input_page_content").size())
			ModuleAce.applyTo("textarea#input_page_content");
	},

	setupCodeMirror: function(){
		var mode = ModuleManagePages.PageEditor.format.toUpperCase() === "HTML" ? "htmlmixed" : "markdown";
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
				},
				"Ctrl-S": function(cm) {
					$.ajax({
						url: "./manage/page/ajaxSaveContent/",
						data: {content: cm.getValue(), pageId: ModuleManagePages.PageEditor.pageId},
						dataType: "json",
						method: "post",
						success: function(json){
							if(json){
								textarea.next("div.CodeMirror").removeClass("changed");
								$("#page-preview-iframe-container iframe").get(0).contentWindow.location.reload();
							}
							else
								alert("Error on saving changes.");
						}
					});
				}
			}
		};
		if(mode === "markdown")
			options.extraKeys['Enter']	= "newlineAndIndentContinueMarkdownList";
		var textarea = $("textarea#input_page_content");
		if(!textarea.is(":visible"))
			return;
		var mirror = CodeMirror.fromTextArea(textarea.get(0), options);
		mirror.on("change", function(instance, update){
			textarea.next("div.CodeMirror").addClass("changed");
		});
		textarea.data({codemirror: mirror, pageId: ModuleManagePages.PageEditor.pageId});
		mirror.setSize("auto",textarea.height());	//  set same size as textarea
		$("#hint").html("Press <b>F11</b> for fullscreen editing.");
	},

	setupTinyMCE: function(){
		var options = {
			selector: "textarea#input_page_content",
//			plugins: "textcolor advlist autolink link image lists charmap print autosave code hr paste searchreplace visualblocks wordcount visualchars table contextmenu emoticons",
//			document_base_url: this.frontendUri,
//			content_css: "http://cdn.int1a.net/css/bootstrap.css",
//			image_list: ModuleManagePages.PageEditor.imageList,
//			link_list: ModuleManagePages.PageEditor.linkList,
			height: 360,
//			language: settings.JS_TinyMCE.auto_language,
		};
		if(typeof tinymce.Config !== "undefined")
			options = tinymce.Config.apply(options, null);
		tinymce.init(options);
	},

	blacklistSuggestedWords: function(pageId, id){
		var words = prompt( "Wörter ausschließen, getrennt mit Leerzeichen" );
		if(!words)
			return;
		jQuery.ajax({
			url: "./manage/page/ajaxBlacklistSuggestedKeywords",
			data: {pageId: pageId, words: words},
			method: "post",
			dataType: "JSON",
			success: function(response){
				if(response.status === "data")
					ModuleManagePages.PageEditor.suggestKeyWords(pageId, id);
			}
		});
	},

	suggestKeyWords: function(pageId, id){
		jQuery.ajax({
			url: "./manage/page/ajaxSuggestKeywords",
			data: {pageId: pageId},
			method: "post",
			dataType: "JSON",
			success: function(response){
				if(response.status === "data")
					jQuery(id).val(response.data.join(", "));
			}
		});
	}
};
