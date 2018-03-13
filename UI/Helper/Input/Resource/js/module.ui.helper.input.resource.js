var HelperInputResource = {
	open: function(elem){
		var inputId = jQuery(elem).data('inputId');
		var modalId = jQuery(elem).data('modalId');
		var mode = jQuery(elem).data('mode');
		if(!modalId || !inputId)
			return;
		jQuery("#"+modalId+"-content").hide();
		jQuery("#"+modalId+"-loader").show();
		jQuery("#"+modalId).modal();

		var data = {
			inputId: inputId,
			modalId: modalId,
			mode: mode,
		};
		switch(mode){
			case 'image':
				data = jQuery.extend(data, {
					paths: ["contents/images/", "images/", "themes/"],
				});
				break;
			case 'style':
				data = jQuery.extend(data, {
					paths: ["themes/", "contents/themes/"],
				});
				break;
		}
		jQuery.ajax({
			url: "./helper/input/resource/ajaxRender",
			data: data,
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
