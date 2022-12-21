$(document).ready(function(){
	var max=0;
	var container = $("#layout-index");
	container.find("li").each(function(){
		height = $(this).height();
		if(height>max)
			max=height;
		var url = $("a",$(this)).attr("href");
		$(this).css("cursor", "pointer").click(function(){location.href=url});
	});
	container.find("li").height(max);
	container.find("#tool-index-filter #query").focus().keyup(function(){
		var query = $(this).val();
		if(query)
			container.find("#tool-index-filter #clearer").show();
		else
			container.find("#tool-index-filter #clearer").hide();
		
		container.find("li").show();
		if(query.length){
			parts	= query.split(" ");
			$(parts).each(function(){
				if(this.length)
					container.find("li").not(":contains(\""+this+"\")").hide();
			});
		}
	});
	container.find("span.tag").click(function(){
		container.find("#tool-index-filter #query").val($(this).html()).trigger('keyup');
		return false;
	});
	container.find("#tool-index-filter #clearer").click(function(){
		container.find("#tool-index-filter #query").val("").trigger('keyup');
	});
});
