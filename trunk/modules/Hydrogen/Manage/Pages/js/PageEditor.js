var PageEditor = {

	init: function(){
		PageEditor.setupTinyMCE();

		$("input[type='text'], select").on("keyup change", function(){
			var input = $(this);
			if(typeof input.data("original") == "undefined")
				input.data("original", input.val());
			if(input.val() !== input.data("original"))
				input.addClass("changed");
			else
				input.removeClass("changed");
		}).trigger("keyup");
		$('#tabs-page-editor ul.nav a').click(function (e) {
			e.preventDefault();
			$(this).tab('show');
			var tab = $($(this).attr("href"));
			if(tab.find("textarea.CodeMirror").size()){
				if(!tab.find("div.CodeMirror").size())
					PageEditor.setupCodeMirror(tab.find("textarea.CodeMirror"));
				$("textarea.mceEditor").attr("disabled", "disabled");
				$("textarea.CodeMirror").removeAttr("disabled");
			}
			if(tab.find("textarea.mceEditor").size()){
				if(!tab.find("span.mceEditor").size())
					PageEditor.setupTinyMCE();
				$("textarea.CodeMirror").attr("disabled", "disabled");
				$("textarea.mceEditor").removeAttr("disabled");
			}
		})
	//	if(typeof pageType !== "undefined" && pageType == 0)
	//		$("#tabs-page-editor ul.nav li a:last").trigger("click")
		$("#tabs-page-editor .optional-trigger").trigger("change");
	},

	setupCodeMirror: function(elem){
		var options = {
			lineNumbers: true,
			indentUnit: 4,
			tabSize: 4,
			indentWithTabs: true,
	//		theme: "elegant",
			mode: "htmlmixed",
		};
		CodeMirror.fromTextArea(elem.get(0), options).on("change", function(instance, update){
			$(instance.getTextArea()).next("div.CodeMirror").addClass("changed");
		});
	},

	setupTinyMCE: function(){
		tinymce.init({
			selector: "textarea.mceEditor",
			plugins : "textcolor advlist autolink link image lists charmap print autosave code hr paste searchreplace visualblocks wordcount visualchars table contextmenu emoticons",
			document_base_url : $("base").attr("href")+"../",
			content_css : "http://cdn.int1a.net/css/bootstrap.css",
			external_image_list_url : "verwaltung/manage/page/getJsImageList",
			toolbars: "emoticons",
			height: 360,
			language: config.module_js_tinymce_auto_language,
	/*		template_external_list_url : "js/template_list.js",
			external_link_list_url : "js/link_list.js",
			media_external_list_url : "js/media_list.js",
	*/	});
	}	
};

$(document).ready(function(){
	PageEditor.init();
});

