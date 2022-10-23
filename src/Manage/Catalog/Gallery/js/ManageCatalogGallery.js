var ManageCatalogGallery = {
	updatePath: function(elem){
		var value = jQuery(elem).val();
		var path = value.toLowerCase();
		path = path.replace(/[^a-z0-9]/g, "_");
		if(value !== path)
			jQuery("#input_path").val(path);
	}
};

