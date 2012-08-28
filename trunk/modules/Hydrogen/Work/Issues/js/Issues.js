Issues = {
	renderIssues: function(table, items){
		var row, item, link, title, status, age;
		table.html("");
		for(var i=0; i<items.length; i++){
			item = items[i];
			link = $("<a></a>").html(item.title);
			link.attr("href","./work/issue/edit/"+item.issueId);
			title = $("<td></td>").html(link);
			status = $("<td></td>").addClass("status").html(item.priority);
			age = Math.round(new Date().getTime() / 1000) - item.modifiedAt;
			age = Math.ceil(age / 24 / 60 / 60);
			age = $("<td></td>").html(age);
			row = $("<tr></tr>").attr("id","issue-"+item.issueId);
			row.data("issue-id",item.issueId);
			row.addClass("status-"+item.status).addClass("priority-"+item.priority);
			row.append(status).append(title).append(age);
			table.append(row);
		}
	},
	loadLatest: function(selector){
		$.ajax({
			url: "./work/issue/export",
			data: {filters: {type: 0, status: [0,1,2,3]}, orders: {modifiedAt: "DESC", createdAt: "DESC"}},
			method: "post",
			dataType: "json",
			success: function(response){
				var table = $(selector+" tbody");
				Issues.renderIssues(table, response);
			},
			error: function(response,message,error){
				UI.Messenger.noteFailure("AJAX request failed: "+message);
				console.log("response:");
				console.log(response);
				console.log("error:");
				console.log(error);
			}
		});
	},
	loadLatestDone: function(selector){
		$.ajax({
			url: "./work/issue/export",
			data: {filters: {type: 0, status: [4,5]}, orders: {modifiedAt: "DESC"}},
			method: "post",
			dataType: "json",
			success: function(response){
				var table = $(selector+" tbody").html("");
				Issues.renderIssues(table, response);
			},
			error: function(response,message,error){
				UI.Messenger.noteFailure("AJAX request failed: "+message);
				console.log("response:");
				console.log(response);
				console.log("error:");
				console.log(error);
			}
		});
	}
};