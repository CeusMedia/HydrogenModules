var HelperInputResource = {
	modePaths: {
		'image': ['contents/images/', 'images/', 'contents/themes/', 'themes/'],
		'style': ['contents/themes/', 'themes/'],
		'document': ['contents/'],
		'download': ['contents/']
	},
	modeExtensions: {
		'image': [],
		'style': [],
		'document': [],
		'download': []
	},
	open: function(elem){
		var inputId = jQuery(elem).data('inputId');
		var modalId = jQuery(elem).data('modalId');
		if(!modalId || !inputId)
			return;
		var mode = jQuery(elem).data('mode');
		var paths = this.modePaths[mode];
		var forcedPaths	= jQuery(elem).data('paths');
		if(forcedPaths.length)
			paths = forcedPaths.split(/,/);
		jQuery("#"+modalId+"-content").hide();
		jQuery("#"+modalId+"-loader").show();
		jQuery("#"+modalId).modal();
		jQuery.ajax({
			url: "./helper/input/resource/ajaxRender",
			data: {
				inputId: inputId,
				modalId: modalId,
				mode: mode,
				paths: paths,
			},
			method: "post",
			dataType: "html",
			success: function(html){
				jQuery("#"+modalId+"-content").html(html).show();
				jQuery("#"+modalId+"-loader").hide();
			}
		})
	},
	setSourceItem: function(elem){
		var sourcePath = jQuery(elem).data("sourcePath");
		var modalId = jQuery(elem).data("modalId");
		var inputId = jQuery(elem).data("inputId");
		if(!modalId || !inputId)
			return;
		jQuery("#"+inputId).val(sourcePath);
		jQuery("#"+modalId).modal("hide");
	}
};
