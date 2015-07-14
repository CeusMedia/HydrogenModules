
var ModuleAdminInstances = {
    labelsReachabilities: {
        unchecked: "unchecked",
        online: "online",
        offline: "offline"
    },
    checkInstanceReachability: function(url, context, onYes, onNo){
		$.ajax({
			url: url,
			context: context,
			type: "HEAD",
            success: onYes,
            error: onNo
		});
	},
    suggestIdForTitle: function(title){
        title = title.replace(/[^a-z0-9 ]/gi, " ");
        var list = [];
        var parts = id.split(" ");
        for(var i in parts){
            if(parts[i].trim().length){
                parts[i][0] = parts[i][0].toUpperCase();
                list.push(parts[i]);
            }
        }
        return list.join("");
    }
};

var ModuleAdminInstancesAdd = {
    init: function(){
        $("#input_title").bind("keyup init", function(){
            var id = $("#input_title").val();
            $("#input_id").val(ModuleAdminInstances.suggestIdForTitle(id));
        });
    }
};

var ModuleAdminInstancesIndex = {
    checkReachabilities: function(){
        $("tr").each(function(){
            if(!$(this).data("url"))
                return;
            $(this).removeClass("error success").addClass("notice");
			ModuleAdminInstances.checkInstanceReachability($(this).data("url"), $(this), function(){
					var box = $(this).find("td.status-http div.status-http");
                    var title = ModuleAdminInstances.labelsReachabilities["online"];
				    box.addClass("status-box-yes").attr("title", title);
				    $(this).removeClass("notice").addClass("success");
				},
				function(a, b){
                    console.log(a);
                    console.log(b);
					var box = $(this).find("td.status-http div.status-http");
                    var title = ModuleAdminInstances.labelsReachabilities["offline"];
                    if(a.statusText)
                        title += ": " + a.statusText;
					box.addClass("status-box-no").attr("title", title);
					$(this).removeClass("notice").addClass("error");
				}
			);
        });
    },
    init: function(){},
    loadTodos: function(){
        $("tr").each(function(){
            if(!$(this).data("url-todos") || $(this).data("check") == "no")
                return;
            $.ajax({
                url: $(this).data("url-todos") + "?format=json",
                dataType: "json",
                context: this,
                success: function(response){
                    if(response !== null && typeof(response) == "object"){			//  
                        var link = $("<a></a>").attr("href",$(this).data("url"));	//  
                        $(this).find("td").eq(2).html(link.html(response.todos))	//  
                    }
                }
            });
        })
    }
};


// console.log("%c Status: %s ", "color: #753; background:#F7F7F7; font-size: 9pt; font-style: italic", status);
