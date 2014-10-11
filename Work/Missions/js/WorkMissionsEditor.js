var WorkMissionsEditor = {
	mirror: null,
	missionId: null,
	markdown: null,
	converter: null,
	textarea: null,
	fallbackFormShowOptionals: function(elem){
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
	initForms: function(){
		$("#input_title").focus();
		$("#input_dayWork, #input_dayDue, #input_dayStart, #input_dayEnd").datepicker({
			dateFormat: "yy-mm-dd",
		//	appendText: "(yyyy-mm-dd)",
		//	buttonImage: "/images/datepicker.gif",
		//	changeMonth: true,
		//	changeYear: true,
		//	gotoCurrent: true,
		//	autoSize: true,
			firstDay: 1,
			nextText: "nächster Monat",
			prevText: "vorheriger Monat",
			yearRange: "c:c+2",
			monthNames: monthNames
		});
		$("#input_dayWork").bind("change", function(event){
			var fieldEnd = $("#input_dayDue");
			if(fieldEnd.val() && $(this).val() > fieldEnd.val())
				fieldEnd.datepicker("setDate", $(this).val());
		});
		$("#input_dayStart").bind("change", function(event){
			var fieldEnd = $("#input_dayEnd");
			if(fieldEnd.val() && $(this).val() > fieldEnd.val())
				fieldEnd.datepicker("setDate", $(this).val());
		});

		//  @link   http://trentrichardson.com/examples/timepicker/
		$("#input_timeStart").timepicker({});
		$("#input_timeEnd").timepicker({});
		$("#input_type").trigger("change");

		/*  mark changed inputs and selects  */
		$("input").not("[type=checkbox]").add($("select")).each(function(){
			var elem = $(this);
			elem.data('original-value', elem.val());
			elem.bind("keyup change", {elem: elem}, function(event){
				var elem = event.data.elem;
				var changed = elem.val() !== elem.data('original-value');
				changed ? elem.addClass('changed') : elem.removeClass('changed');
			});
		});
	},

	init: function(missionId){
		"use strict";
		this.initForms();
	console.log("WorkMissionsEditor.init");
		WorkMissionsEditor.missionId = missionId;
		WorkMissionsEditor.markdown = $("#descriptionAsMarkdown");
		WorkMissionsEditor.converter = new Markdown.Converter();
		WorkMissionsEditor.textarea = $("#input_content");
		WorkMissionsEditor.mirror = CodeMirror.fromTextArea(WorkMissionsEditor.textarea.get(0), {
			lineNumbers: true,
//			theme: "neat",
			theme: "elegant",
			mode: "markdown",
//			viewportMarin: "Infinity",
			fixedGutter: true,
		});
		if(WorkMissionsEditor.missionId){
			WorkMissionsEditor.textarea.bindWithDelay("keyup", function(){
				$.ajax({
					url: "./work/mission/ajaxSaveContent/"+WorkMissionsEditor.missionId,
					data: {content: WorkMissionsEditor.textarea.val()},
					type: "post",
					success: function(){
						$(".CodeMirror").removeClass("changed");
					}
				});
			}, 1000);
		}
		WorkMissionsEditor.mirror.on("change", function(instance, update){
			instance.save();																			//  apply changes of markdown editor to input element
			WorkMissionsEditor.renderContent();															//  render input element content to HTML using markdown
			if(WorkMissionsEditor.missionId){															//  edit mode
				$(".CodeMirror").addClass("changed").trigger("keyup");									//  trigger key up event for automatic save
				$(instance.getTextArea()).trigger("keyup");												//  trigger key up event for automatic rendering
			}
		});
		WorkMissionsEditor.renderContent();

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
	renderContent: function(){
		var content	= WorkMissionsEditor.textarea.hide().val();											//  get content of editor
		WorkMissionsEditor.markdown.html(WorkMissionsEditor.converter.makeHtml(content));				//  display content after markdown rendering
		WorkMissionsEditor.resizeInput();
	},
	resizeInput: function(){
		var height =  Math.max(WorkMissionsEditor.markdown.height()-30, 160);
		WorkMissionsEditor.mirror.setSize("100%", height);
	}
};
