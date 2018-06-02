function setupInputFileUpload(){
	$("input.bs-input-file").on("change", function(){
		var name = $(this).val().replace(/^(.+fakepath).(.+)$/, "$2");
		var container = $(this).parent();
		container.children("input.bs-input-file-info").val(name).blur();
	});
	$("input.bs-input-file-info").on("click", function(){
		var container = $(this).parent();
		container.find("input.bs-input-file").trigger("click");
	});
	$("a.bs-input-file-toggle").on("click", function(){
		var container = $(this).parent();
		container.find("input.bs-input-file").trigger("click");
	});
}
