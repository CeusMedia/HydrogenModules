let ModuleInfoBlog = {
	init: function(){
		let form = $("#form-info-blog-comment-add");
		form.find(":input[required]").on("keyup", ModuleInfoBlog.updateSaveButton);
		this.updateSaveButton();
	},
	updateSaveButton: function(){
		let form = $("#form-info-blog-comment-add");
		let required = form.find(":input[required]");
		let got = 0;
		required.each(function(){
			if($(this).val().length)
				got ++;
		});
		if(got === required.length)
			form.find("button").removeProp("disabled");
		else
			form.find("button").prop("disabled", "disabled");
	},
	comment: function(){
		let form = $("#form-info-blog-comment-add");
		$.ajax({
			url: "./info/blog/ajax/comment/",
			data: {
				postId: form.find("#input_postId").val(),
				username: form.find("#input_username").val(),
				email: form.find("#input_username").val(),
				content: form.find("#input_content").val(),
			},
			method: "post",
			dataType: "json",
			success: function(json){
				let container = $("<div></div>").addClass("comment-new").hide();
				container.html(json.data.html);
				$(".list-comments").append(container);
				container.fadeIn(1000);
			},
			error: function(json){
				console.log(json);
				if(typeof json.responseJSON != "undefined")
					alert(json.responseJSON.data);
				else
					alert(json);
			}
		});
	}
};
$(document).ready(function(){
	ModuleInfoBlog.init();
});
