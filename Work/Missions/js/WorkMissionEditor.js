var WorkMissionEditor = {
	mirror: null,
	missionId: null,
	markdown: null,
	converter: null,
	textarea: null,
	fallbackFormShowOptionals(elem){
		var form = $(elem.form);
		var name = $(elem).attr("name");
		var type = $(elem).attr("type");
//		console.log("name: "+name);
//		console.log("type: "+type);
		var value = name+"-"+$(elem).val();
		if(type == "checkbox"){
			value = name+"-"+$(elem).prop("checked");
		}
		var toHide = form.find(".optional."+name).not("."+value);
		var toShow = form.find(".optional."+value);
		if(!$(elem).data("status")){
			toHide.hide();
			toShow.show();
			$(elem).data("status",1)
			return;
		}
		switch($(elem).data('animation')){
			case 'fade':
				toHide.fadeOut();
				toShow.fadeIn();
				break;
			case 'slide':
				toHide.slideUp($(elem).data('speed-hide'));
				toShow.slideDown($(elem).data('speed-show'));
				break;
			default:
				toHide.hide();
				toShow.show();
		}
	},
	init: function(missionId){
		"use strict";
		WorkMissionEditor.missionId = missionId;
		WorkMissionEditor.markdown = $("#descriptionAsMarkdown");
		WorkMissionEditor.converter = new Markdown.Converter();
		WorkMissionEditor.textarea = $("#input_content");
		WorkMissionEditor.mirror = CodeMirror.fromTextArea(WorkMissionEditor.textarea.get(0), {
			lineNumbers: true,
//			theme: "neat",
			theme: "elegant",
			mode: "markdown",
//			viewportMarin: "Infinity",
			fixedGutter: true,
		});
		if(WorkMissionEditor.missionId){
			WorkMissionEditor.textarea.bindWithDelay("keyup", function(){
				$.ajax({
					url: "./work/mission/ajaxSaveContent/"+WorkMissionEditor.missionId,
					data: {content: WorkMissionEditor.textarea.val()},
					type: "post",
					success: function(){
						$(".CodeMirror").removeClass("changed");
					}
				});
			}, 1000);
		}
		WorkMissionEditor.mirror.on("change", function(instance, update){
			instance.save();
			var content	= WorkMissionEditor.textarea.hide().val();											//  get content of editor
			WorkMissionEditor.markdown.html(WorkMissionEditor.converter.makeHtml(content));					//  display content after markdown rendering
			if(WorkMissionEditor.missionId){																//  edit mode
				$(".CodeMirror").addClass("changed").trigger("keyup");										//  trigger key up event for automatic save
				$(instance.getTextArea()).trigger("keyup");													//  trigger key up event for automatic rendering
			}
			WorkMissionEditor.resize();																		//  trigger automatic input element resize
		});

		WorkMissionEditor.markdown.html(WorkMissionEditor.converter.makeHtml(WorkMissionEditor.textarea.hide().val()));
		WorkMissionEditor.resize();

/* --  DEPRECATED CODE --  */
/*		$(window).bind("resize", function(){
			$("#mirror-container").width($(".column-left-75").eq(0).width()-12);
			$("#mirror-container").width("100%");
		}).trigger("resize");
		$(".tabbable .nav-tabs li a").bind("shown", function(event){
			mirror.refresh();
		});
		$("input, select, textarea").each(function(){
			$(this).data("value-original", $(this).val());
		}).bind("change keyup", function(){
		var input = $(this);
		input.removeClass("changed");
		if(input.val() != input.data("value-original"))
			input.addClass("changed");
		});
*/
	},
	resize: function(){
		var height =  Math.max(WorkMissionEditor.markdown.height()-30, 160);
		WorkMissionEditor.mirror.setSize("100%", height);
	}
};
