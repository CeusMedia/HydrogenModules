var InfoForum = {
	lastPostText: null,
	lastPostHtml: null,
	lastPostCell: null,
	lastPostId: null,
	postEditors: [],
	baseUrl: "./info/forum",
	preparePostEditor: function(postId){
		if(InfoForum.lastPostId && InfoForum.lastPostCell.find("textarea").size()){					//  post editor in current row is still open
			console.log(InfoForum.lastPostId);
			InfoForum.lastPostCell.find("textarea").trigger("editor-change");						//  close editor by triggering change event
			if(InfoForum.lastPostId === postId)														//  same edit button has been clicked
				return;																				//  stop here since changes will be stored
		}
		InfoForum.lastPostRow = $("tr#post-" + postId);												//  note current row
		InfoForum.lastPostCell = InfoForum.lastPostRow.find("td.content");							//  note current content cell
		InfoForum.lastPostId = postId;																//  note current post ID
		InfoForum.lastPostHtml = InfoForum.lastPostCell.html();										//  note current post HTML
		var input = $("<textarea></textarea>").attr("disabled", "disabled");						//  create text area, disable at start
		input.addClass("span12").attr("rows", 10).attr("required", "required");						//  configure text area styling and state
		InfoForum.lastPostCell.html(input);															//  replace post HTML by text area
		$.ajax({																					//  request via AJAX
			url: InfoForum.baseUrl + "/ajaxGetPost/" + postId,										//  ... get post data
			dataType: "json",																		//  ... as JSON
			async: false,																			//  ... wait for it
			success: function(json){																//  ... and on response
				InfoForum.lastPostText = json.content;												//  note current post text content
				var input = InfoForum.lastPostCell.find("textarea");								//  find text area of current row
				input.val(json.content).removeAttr("disabled").focus();								//  insert post text content, enable and focus
				input.on("editor-change", function(){												//  bind custom event 
					if(InfoForum.lastPostText === $(this).val()){									//  post text content did not change
						console.log("no change");
						InfoForum.lastPostCell.html(InfoForum.lastPostHtml);						//  replace text area by old post HTML
						InfoForum.lastPostId = null;												//  reset noted post ID
					}
					else{																			//  post text has been changed
						$(this).attr("disabled", "disabled");										//  disable text area to indicate action
						$.ajax({																	//  request via AJAX
							url: InfoForum.baseUrl + "/ajaxEditPost",								//  ... to edit post
							data: {postId: InfoForum.lastPostId, content: $(this).val()},			//  ... by content
							type: "post",															//  ... via POST request
							success: function(){													//  ... and on success
								document.location.reload();											//  ... reload page
							}
						});
					}
				});
			}
		});
	},
	changeTopicName: function(topicId, currentName){
		var value = prompt("Neuer Name:", currentName);
		if(value.length){
			$.ajax({
				url: InfoForum.baseUrl + "/ajaxRenameTopic/",
				data: {
					topicId: topicId,
					name: value
				},
				success: function(){
					document.location.href = InfoForum.baseUrl + "/";
				}
			});
		}
	},
	changeThreadName: function(threadId, topicId, currentName){
		var value = prompt("Neuer Name:", currentName);
		if(value.length){
			$.ajax({
				url: InfoForum.baseUrl + "/ajaxRenameThread/",
				data: {
					threadId: threadId,
					name: value
				},
				success: function(){
					document.location.href = InfoForum.baseUrl + "/topic/" + topicId;
				}
			});
		}
	},
	changeThreadType: function(threadId, topicId){
		$.ajax({
			url: InfoForum.baseUrl + "/ajaxStarThread/" + threadId,
			success: function(){
				document.location.href = InfoForum.baseUrl + "/topic/" + topicId;
			}
		});
	}
};
