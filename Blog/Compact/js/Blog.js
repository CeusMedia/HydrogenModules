var Blog = {
	initIndex: function(){
		$("#blog input[name=states]").bind("change",function(){
			$(this).parent().children("span").addClass("loading");
			$.ajax({
				url: "./blog/setFilter",
				data: {
					name: "states",
					mode: $(this).is(":checked") ? "add" : "remove",
					value: $(this).attr("value")
				},
				type: "post",
				success: function(){
					document.location.href = "./blog";
				}
			});
		});
	},
	initEditor: function(){
	}
};
