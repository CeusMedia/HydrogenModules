$(document).ready(function(){
	$("button.edit").bind("click",function(){
		var row = $(this).parent().parent();
		row.find("input").prop("disabled",true);
		$(this).prop("disabled",true);
		$.ajax({
			url: "./admin/cache/ajaxEdit",
			data: {key: row.data("key"), value: row.find("input").val()},
			type: "post",
			context: row,
			dataType: "json",
			success: function(response){
				$(this).find("button.edit").prop("disabled",false);
				var field = $(this).find("input");
				field.data("original",field.val());
				field.trigger("keyup");
				field.prop("disabled",false);
			}
		});
	});

	$("button.remove").bind("click",function(){
		var row = $(this).parent().parent();
		row.find("input").prop("disabled",true);
		$(this).prop("disabled",true);
		$.ajax({
			url: "./admin/cache/ajaxRemove",
			data: {key: row.data("key")},
			type: "post",
			context: row,
			dataType: "json",
			success: function(response){
				$(this).remove();
			}
		});
	});

	$("input").each(function(){
		$(this).data("original",$(this).val()).bind("keyup",function(){
			var color = $(this).data("original") == $(this).val() ? "" : "rgb(255,255,240)";
			$(this).css("backgroundColor",color);
		});
	});
});
