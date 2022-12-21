//var Newsletter = {
var ModuleWorkNewsletter = {
	imageList: [],
	linkList: [],
	cssSources: [
		'https://cdn.ceusmedia.de/css/bootstrap.css'
//        'css/style.css'
	],
	cmDefaultOptions: {
		theme: "default",
		lineNumbers: true,
		enterMode: "keep",
		indentUnit: 4,
		indentWithTabs: true,
		tabMode: "shift",
		tabSize: 4,
		body_class: 'mail mail-newsletter mail-editor'
	},
	modes: {
		0: 'newsletter',
		1: 'template'
	},
	init: function(baseUrlBackend, templateId, mode){
		var mode = typeof mode === "undefined" ? 0 : parseInt(mode);
		if($("textarea#input_html").length){
			switch(settings.Work_Newsletter['editor_' + ModuleWorkNewsletter.modes[mode]]){
				case 'CodeMirror':
					CodeMirror.fromTextArea($("textarea#input_html").get(0), $.extend(this.cmDefaultOptions, {
						highlightSelectionMatches: true,
						matchBrackets: true,
						mode: "htmlmixed"
					}));
					break;
				case 'TinyMCE':
					/* @todo rethink baseUrl use for several cases */
					var baseUrl = baseUrlBackend;
					if(typeof settings.JS_TinyMCE.auto_baseUrl !== "undefined"){
						if(settings.JS_TinyMCE.auto_baseUrl.match(/^http/))
							baseUrl = settings.JS_TinyMCE.auto_baseUrl;
						else
							baseUrl = baseUrlBackend + settings.JS_TinyMCE.auto_baseUrl;
					}
					var styleUrl = baseUrlBackend + 'work/newsletter/template/ajaxGetStyle/' + templateId + '/1';
					this.cssSources.unshift(styleUrl + '?' + new Date().getTime());
					var options = tinymce.Config.apply({
						selector: "textarea#input_html",
						content_css: ModuleWorkNewsletter.cssSources.join(','),
//						plugins: "textcolor advlist autolink link image lists charmap preview autosave code charmap hr paste searchreplace visualblocks wordcount visualchars table",
//						tools: "inserttable",
//						image_list: ModuleWorkNewsletter.imageList,
//						link_list: ModuleWorkNewsletter.linkList,
//						document_base_url: baseUrl,
						body_class: 'mail mail-newsletter mail-editor',
						init_instance_callback: function(editor) {
							editor.on("Change", function(e, l) {
//								console.debug("Editor contents was modified.");
							});
						}
					});
					tinymce.init(options);
					break;
			}
		}
		if($("textarea#input_style").length)
			CodeMirror.fromTextArea($("textarea#input_style").get(0), $.extend(this.cmDefaultOptions, {
				mode: "css"
			}));
		if($("textarea#input_script").length)
			CodeMirror.fromTextArea($("textarea#input_script").get(0), $.extend(this.cmDefaultOptions, {
				mode: "javascript"
			}));
		if($("textarea#input_plain").length)
			CodeMirror.fromTextArea($("textarea#input_plain").get(0), $.extend(this.cmDefaultOptions, {
			}));
		$("#preview-refresh").on("click", function(){
			$("#modal-preview iframe").get(0).src = $("#modal-preview iframe").attr("src");
		})
	},
	showPreview: function(url){
		$("#modal-preview iframe").get(0).src = url;
	}
};
