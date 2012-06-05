ModuleSourceAdmin = {
	checkSource: function (){
		$.ajax({
			url: "./admin/module/source/ajaxReadSource/"+sourceId,
			dataType: "json",
			cache: false,
			data: {path: $(this).val(), type: $("#input_type").val()},
			type: "post",
			context: $(this),
			success: function(data){
				var form = $(this.get(0).form);
				if(data.code <= 0){
					$("#panelModules").fadeOut();
					this.addClass("invalid").removeClass("valid");
					form.find("button.save").prop("disabled","disabled");
				}
				else{
					$("#count-modules").html(data.modules.length);
					if(data.modules){
						var list = $("<ul></ul>");
						for(i in data.modules){
							var module = data.modules[i];
							var desc = module.description.split("/\n/").shift();
							var label = module.title;
							if(desc)
								label = $("<acronym></acronym>").attr("title",desc).html(label);
							list.append($("<li></li>").html(label));
						}
						$("#panelModules-content").html(list);

					}
					$("#panelModules").fadeIn();
					this.addClass("valid").removeClass("invalid");
					form.find("button.save").prop("disabled",null);
				}
			}
		});
	},
	checkSources: function(){
		$("table tr.source").each(function(){
			console.log("1");
			$.ajax({
				url: "./admin/module/source/ajaxReadSource/"+$(this).data("id"),
				dataType: "json",
				type: "post",
				context: $(this),
				success: function(data){
					var cell = this.find(".counter-modules");
					if(data.code > 0){
						cell.append("("+data.modules.length+")");
						if(data.readable)
							this.addClass("access-readable");
						if(data.writable)
							this.addClass("access-writable");
						if(data.executable)
							this.addClass("access-executable");
					}
					else{
						console.log(data);
						var label = "(-)";
						if(data.error)
							label = $("<acronym></acronym>").attr("title",data.error).html(label);
						cell.append(label);
						this.addClass("access-none");
					}
				}
			});
		});
	}
};


$(document).ready(function(){
	if($("body.moduleAdminModuleSource").size()){
		if($("body.site-admin-module-source-index").size())
			ModuleSourceAdmin.checkSources();
		$("#input_path").bind("keyup change",ModuleSourceAdmin.checkSource).trigger("keyup");
	}
});
