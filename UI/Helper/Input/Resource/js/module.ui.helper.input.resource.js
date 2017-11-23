var HelperInputResource = {
	openImageList: function(elem){
		var inputId = jQuery(elem).data('inputId');
		var modalId = jQuery(elem).data('modalId');
		if(!modalId || !inputId)
			return;
		jQuery("#"+modalId+"-content").hide();
		jQuery("#"+modalId+"-loader").show();
		jQuery("#"+modalId).modal();
		jQuery.ajax({
			url: "./helper/input/resource/ajaxRender",
			data: {
				inputId: inputId,
				modalId: modalId,
				paths: ["contents/images/", "images/", "themes/"],
				extensions: ["png", "gif", "jpg", "jpeg", "jpe", "svg"],
				mimeTypes: ["*"]
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
