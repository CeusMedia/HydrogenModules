var Instance = {
	isReachable: function(url, context, events){
		var options = $.extend({
			url: url,
			context: context,
			type: "HEAD"
		}, events);
		$.ajax(options);
	}
}

var ModuleAdminInstances = {
    checkReachabilities: function(labels){
        $("tr.notice").each(function(){
            if(!$(this).data("url"))
                return;
			Instance.isReachable($(this).data("url"), $(this), {
				success: function(){
					var box = $(this).find("td.status-http div.status-http");
				    box.addClass("status-box-yes").attr("title", labels["online"]);
				    $(this).removeClass("notice").addClass("success");
				},
				error: function(){
					var box = $(this).find("td.status-http div.status-http");
					box.addClass("status-box-no").attr("title", labels["offline"]);
					$(this).removeClass("notice").addClass("error");
				}
			});
        });
    },
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
