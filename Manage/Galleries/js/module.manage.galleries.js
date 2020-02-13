
$(document).ready(function(){
	if($("#tabs-gallery-editor").length){
		$("#tabs-gallery-editor>ul>li>a").each(function(){
			if($(this).parent().hasClass("active"))
				$(this).parent().parent().parent().find($(this).attr("href")).addClass("active");
		});

		tinymce.init(tinymce.Config.apply({selector: '#tabs-gallery-editor .mceEditor'}, 'minimal'));

		$("#tabs-gallery-editor>ul>li>a").on("click", function(){
			$.ajax({
				url: "./manage/gallery/ajaxSetTab",
				type: "post",
				data: {tab: $(this).attr("href").replace(/#tab/, "")},
			});
		})
	}

	jQuery("#input_title").on("keyup change", function(){
		jQuery("#input_path").val(getPathForTitle(jQuery(this).val()));
	});

});

function getPathForTitle(title){
	title = title.toLowerCase();
	title = title.replace(/ +/g, "_");
	title = title.replace(/\.+/g, "_");
	title = title.replace(/\//g, "-");
	title = title.replace(/\\+/g, "-");
	title = title.replace(/-+/g, "-");
	title = title.replace(/_+/g, "_");
	return title;
}
