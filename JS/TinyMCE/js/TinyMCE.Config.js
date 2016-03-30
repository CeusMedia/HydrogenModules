if(typeof tinymce !== "undefined"){

	tinymce.addI18n('de',{
		"test": "Test"
	});

	tinymce.Config = {
		envUri: "./",
		frontendUri: "../",
		language: "en",
		languages: "English=en",
		listImages: [],
		listLinks: [],

		apply: function(custom, mode, verbose){
			if(typeof custom === "undefined")
				return this.options;
			if(typeof mode === "undefined" || !mode)
				mode = settings.JS_TinyMCE.auto_mode;
			var options = $.extend({}, this.options);

			options.selector = settings.JS_TinyMCE.auto_selector;
			options.plugins = settings.JS_TinyMCE.auto_plugins;
//			options.height = settings.JS_TinyMCE.auto_height;			//  @todo not working right now
			options.language = tinymce.Config.language;
			options.document_base_url = this.frontendUri;
			options.image_list = this.listImages;
			options.link_list = this.listLinks;
			options.content_css += "," + this.envUri + "themes/custom/css/tinymce.content.css";
//			options.spellchecker_languages = this.languages;
//			options.spellchecker_rpc_url = this.frontendUri + 'spellchecker/spellchecker.php';

			if(!$.inArray(mode, ["default", "extended", "minimal"]))
				mode = "default";
			var toolbars = settings.JS_TinyMCE['auto_toolbar_'+mode].split(/#/);
			for(var i=1; i<=toolbars.length; i++)
				options["toolbar"+i] = toolbars[i-1];

			custom = $.extend(options, custom);

			if(typeof verbose !== "undefined" && verbose)
				console.log(custom);
			return custom;
		},
		get: function(){
			return tinymce.Config.options;
		},
		options: {
//			theme_advanced_resizing: true,
//			theme_advanced_resizing_use_cookie: false,
			content_css: [
					"http://cdn.int1a.net/css/bootstrap.css"
				].join(","),
//			toolbar: "undo redo | styleselect | fontselect | fontsizeselect | bold italic | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image | print preview media fullpage | forecolor backcolor emoticons",
//			toolbar: "styleselect | fontselect | fontsizeselect | bold italic forecolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image",
			insertdatetime_formats: ["%d.%m.%Y", "%H:%M"],
			plugins: "advlist anchor autolink autoresize autosave charmap code colorpicker contextmenu emoticons fullscreen hr image insertdatetime link lists media nonbreaking noneditable pagebreak paste preview print save searchreplace spellchecker tabfocus table template textcolor textpattern visualblocks visualchars wordcount",
			noneditable_leave_contenteditable: true,
			fontsize_formats: "80% 90% 100% 110% 120% 150% 200% 300%",
			browser_spellcheck: true,
			style_formats_merge: true,
			style_formats: [{
				title: 'Bildformatierung',
				items: [{
					title: 'Ausrichtung',
					items: [{
						title: 'links',
						selector: 'img',
						styles: {
							'float': 'left',
							'margin': '0 20px 10px 0px'
						}
					},{
						title: 'rechts',
						selector: 'img',
						styles: {
							'float': 'right',
							'margin': '0 0 10px 20px'
						}
					}]
				},{
					title: 'Dekoration',
					items: [{
						title: 'abgerundet',
						selector: 'img',
						classes: 'img-rounded'
					},{
						title: 'kreisrund',
						selector: 'img',
						classes: 'img-circle'
					},{
						title: 'Polaroid',
						selector: 'img',
						classes: 'img-polaroid'
					}]
				}]
			},{
				title: 'In Fenster öffnen',
				selector: 'a',
				classes: ["fancybox-auto"]
			}]
		}
	};
}
