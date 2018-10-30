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

	blacklistSuggestedWords: function(pageId, id, question){
		var words = prompt(question);
		if(!(words))
			return;
		jQuery.ajax({
			url: "./manage/page/ajaxBlacklistSuggestedKeywords",
			data: {pageId: pageId, words: words},
			method: "post",
			dataType: "JSON",
			success: function(response){
				if(response.status === "data"){
					var inputKeywords	= jQuery("#input_page_keywords");
					var inputBlacklist	= jQuery("#input_page_keywords_blacklist");
					inputKeywords.val(response.data.keywords.join(", "));
					inputBlacklist.val(response.data.blacklist.join(", "));
//					ModuleManagePages.PageEditor.suggestKeyWords(pageId, id);
				}
			}
		});
	},

	init: function(format){
//		console.log('init: ModuleManagePages.PageEditor');
		ModuleManagePages.PageEditor._initCreator(format);
		ModuleManagePages.PageEditor._initEditor(format);
		ModuleManagePages.PageEditor._initDefaultMetaCopy();
		ModuleManagePages.PageEditor._initSortable();
	},

	loadPagePreview: function(){
		var iframe = jQuery("<iframe></iframe>");
		iframe.addClass("preview");
		iframe.attr("src", jQuery("#page-preview").data("url"));
		jQuery("#page-preview-iframe-container").html(iframe);
	},

	reducePath: function(path, keepCase){
		if(!(typeof keepCase !== "undefined" && keepCase))
			path = path.toLowerCase();
		path = path.replace(/ /g, '_');
		path = path.replace(/[^a-z0-9_/-]/g, '');
		return path;
	},

	setEditor: function(event){
		event.stopPropagation();
		var value = jQuery("#input_page_editor").val();
		jQuery.ajax({
			url: "./manage/page/ajaxSetEditor/"+value,
			success: function(){
				jQuery("#input_page_editor").data("original-value", value).trigger("keyup.FormChanges");
				document.location.reload();
			}
		});
	},

	setupAce: function(){
		if(jQuery("textarea#input_page_content").length)
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
					jQuery.ajax({
						url: "./manage/page/ajaxSaveContent/",
						data: {content: cm.getValue(), pageId: ModuleManagePages.PageEditor.pageId},
						dataType: "json",
						method: "post",
						success: function(json){
							if(json){
								textarea.next("div.CodeMirror").removeClass("changed");
								jQuery("#page-preview-iframe-container iframe").get(0).contentWindow.location.reload();
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
		var textarea = jQuery("textarea#input_page_content");
		if(!textarea.is(":visible"))
			return;
		var mirror = CodeMirror.fromTextArea(textarea.get(0), options);
		mirror.on("change", function(instance, update){
			textarea.next("div.CodeMirror").addClass("changed");
		});
		textarea.data({codemirror: mirror, pageId: ModuleManagePages.PageEditor.pageId});
		mirror.setSize("auto",textarea.height());	//  set same size as textarea
		jQuery("#hint").html("Press <b>F11</b> for fullscreen editing.");
	},

	setupTinyMCE: function(){
		var options = {
			selector: "textarea#input_page_content",
//			plugins: "textcolor advlist autolink link image lists charmap print autosave code hr paste searchreplace visualblocks wordcount visualchars table contextmenu emoticons",
//			document_base_url: this.frontendUri,
//			content_css: "https://cdn.ceusmedia.de/css/bootstrap.css",
//			image_list: ModuleManagePages.PageEditor.imageList,
//			link_list: ModuleManagePages.PageEditor.linkList,
			height: 360,
//			language: settings.JS_TinyMCE.auto_language,
		};
		if(typeof tinymce.Config !== "undefined")
			options = tinymce.Config.apply(options, null);
		tinymce.init(options);
	},

	suggestKeyWords: function(pageId, id){
		jQuery.ajax({
			url: "./manage/page/ajaxSuggestKeywords",
			data: {pageId: pageId},
			method: "post",
			dataType: "JSON",
			success: function(response){
				if(response.status === "data"){
					var input = jQuery(id);
					input.val(response.data.join(", "));
					input.trigger("input");
					jQuery("#btn-meta-blacklist").prop({disabled: "disabled"});
				}
			}
		});
	},

	toggleSortable: function(){
		var container = jQuery("#manage-page-tree ul");
		if(container.sortable("option", "disabled")){
			container.sortable("option", "disabled", false);
			container.addClass("sortable");
			container.find("a").on("click", function(){
				return false;
			});
		}
		else{
			container.sortable("option", "disabled", true)
			container.removeClass("sortable");
			container.find("a").off("click");
		}
		jQuery("#toggle-sortable").blur();
	},

	/*  private  */
	_initCreator: function(format){
		var container	= jQuery("#panel-page-add");
		if(!container.size())
			return;
		var inputTitle	= container.find("#input_page_title");
		var inputSlug	= container.find("#input_page_identifier");
		if(inputTitle.length){
			inputTitle.on("input", function(){
				var value = jQuery(this).val();
				inputSlug.val(ModuleManagePages.PageEditor.reducePath(value));
			});
		}
	},

	_initEditor: function(format){
		var containerEditor	= jQuery("#tabs-page-editor");
		if(!containerEditor.length)
			return;
		containerEditor.find(".optional-trigger").trigger("change");
		containerEditor.find(">ul>li>a").each(function(){
			if(jQuery(this).parent().hasClass("active"))
				jQuery(this).parent().parent().parent().find(jQuery(this).attr("href")).addClass("active");
		});
		containerEditor.find(">ul>li>a").on("click", function(){
			var key = jQuery(this).attr("href").replace(/#tab/, "");
			if(key == 3){
				if( ModuleManagePages.PageEditor.editor.toLowerCase() == "codemirror")
					window.setTimeout(ModuleManagePages.PageEditor.setupCodeMirror, 20);
				if( ModuleManagePages.PageEditor.editor.toLowerCase() == "ace")
					window.setTimeout(ModuleManagePages.PageEditor.setupAce, 20);
			}
			if(key == 4)
				ModuleManagePages.PageEditor.loadPagePreview();
			jQuery.ajax({
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
		jQuery("#page-preview").mouseenter(function(){
			jQuery("#page-preview-mask").hide();
		}).mouseleave(function(){
			jQuery("#page-preview-mask").show();
		});
		jQuery("#input_page_editor").on("change", ModuleManagePages.PageEditor.setEditor);

		jQuery("#btn-meta-suggest").on("click", function(){
			var me = jQuery(this);
			var pageId = me.data("pageId");
			var target = me.data("target");
			ModuleManagePages.PageEditor.suggestKeyWords(pageId, target);
		});

		jQuery("#btn-meta-blacklist").on("click", function(){
			var me = jQuery(this);
			var pageId = me.data("pageId");
			var target = me.data("target");
			var question = me.data("question");
			ModuleManagePages.PageEditor.blacklistSuggestedWords(pageId, target, question);
		});
	},

	_initSortable: function(){
		jQuery("#manage-page-tree ul").sortable({
			disabled: true,
			stop: function(event, ui) {
				var pageIds = [];
				ui.item.parent().children("li").each(function(){
					pageIds.push(jQuery(this).data("pageId"));
				});
				jQuery.ajax({
					url: "./manage/page/ajaxOrderPages",
					data: {pageIds: pageIds},
					method: "POST",
					success: function(){}
				});
			}
		});
	},

	_initDefaultMetaCopy: function(){
//		console.log('initDefaultMetaCopy');
		jQuery("#btn-copy-description").on("click", function(event){
			var source = jQuery("#input_page_default_description");
			var target = jQuery("#input_page_description");
			target.val(source.val());
			jQuery(this).blur();
		});
		jQuery("#btn-copy-keywords").on("click", function(event){
			var source = jQuery("#input_page_default_keywords");
			var target = jQuery("#input_page_keywords");
			target.val(source.val());
			jQuery(this).blur();
		});
	}
};
