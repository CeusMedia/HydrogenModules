var WorkMissionsEditor = {
	markdown: null,
	mirror: null,
	mission: null,
	missionWorkerId: 0,
	missionId: null,
	textarea: null,
	userId: null,

	bindWorkerSelectUpdateOnProjectInputChange: function(idSelectWorker, idInputProject){
		var selectProject = $("#"+idInputProject);
		if(selectProject.size()){
			if( !this.mission)
				$("#"+idSelectWorker).bind("change", function(){
					WorkMissionsEditor.missionWorkerId = $(this).val();
				});
			selectProject.bind("change", function(){
				var workerId;
				var projectId = selectProject.val();
				if(WorkMissionsEditor.mission)
					workerId = WorkMissionsEditor.mission.workerId;
				else
					workerId = WorkMissionsEditor.missionWorkerId;
				WorkMissionsEditor.formUpdateWorkers(projectId, workerId, {idSelectWorker: idSelectWorker});
			}).trigger("change");
		}
	},

	formUpdateWorkers: function(projectId, currentWorkerId, options){
		currentWorkerId = parseInt(currentWorkerId, 10);
		var options = $.extend({
			idSelectWorker: "input_workerId",
			urlGetProjectUsers: "./work/mission/ajaxGetProjectUsers/"+projectId,
			allowEmpty: true,
		}, options);
		$.ajax({
			url: options.urlGetProjectUsers,
			dataType: "json",
			success: function(json){
				var selectWorker = $("#"+options.idSelectWorker).html("");
				if(options.allowEmpty){
					var option = $("<option></option>").val("").html("-");
					selectWorker.append(option);
				}
				$(json).each(function(nr){
					var option = $("<option></option>").val(this.userId).html(this.username);
					this.userId = parseInt(this.userId, 10);
					if(currentWorkerId === this.userId || json.length === 1 || ( !currentWorkerId && this.userId === Auth.userId ) )
						option.prop("selected", "selected");
					selectWorker.append(option);
				});
				selectWorker.trigger("change");
			}
		});
	},

	initForms: function(){
		$("#input_title").focus();
		var dateInputs = $("#input_dayWork, #input_dayDue, #input_dayStart, #input_dayEnd");
		var timeInputs = $("#input_timeStart, #input_timeEnd");

		dateInputs.datepicker({
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

		//  @link   http://trentrichardson.com/examples/timepicker/
		timeInputs.timepicker({});
//		$("#input_type").trigger("change");

		dateInputs.add(timeInputs).bind("change", WorkMissionsEditor.sanitizeDateAndTime);

		this.bindWorkerSelectUpdateOnProjectInputChange("input_workerId", "input_projectId");
	},

	init: function(missionId){
		"use strict";
		this.initForms();
		WorkMissionsEditor.missionId = missionId;
		WorkMissionsEditor.userId = Auth.userId;
		WorkMissionsEditor.markdown = $("#mission-content-html");
		WorkMissionsEditor.textarea = $("#input_content");
		WorkMissionsEditor.mirror = CodeMirror.fromTextArea(WorkMissionsEditor.textarea.get(0), {
			lineNumbers: true,
//			theme: "neat",
			theme: "elegant",
			mode: "markdown",
			fixedGutter: true,
		});
//		WorkMissionsEditor.mirror.setSize("100%",600);

		WorkMissionsEditor.textarea.bindWithDelay("keyup", function(){
			if(WorkMissionsEditor.missionId){
				WorkMissionsEditor.markdown.css({opacity: 0.5});
				$.ajax({
					url: "./work/mission/ajaxSaveContent/"+WorkMissionsEditor.missionId,
					data: {content: WorkMissionsEditor.textarea.val()},
					method: "POST",
					dataType: "html",
					success: function(html){
						$(".CodeMirror").removeClass("changed");
						WorkMissionsEditor.markdown.html(html).css({opacity: 1});
					}
				});
			}
			else{
				WorkMissionsEditor.renderContent();
				$(".CodeMirror").removeClass("changed");
			}
		}, 500);
		WorkMissionsEditor.mirror.on("change", function(instance, update){
			instance.save();																			//  apply changes of markdown editor to input element
			WorkMissionsEditor.resizeInput();
			$(".CodeMirror").addClass("changed").trigger("keyup");									//  trigger key up event for automatic save
			$(instance.getTextArea()).trigger("keyup");												//  trigger key up event for automatic rendering
		});
		WorkMissionsEditor.renderContent();
	},
	realHeight: function(obj){
		var clone = obj.clone().css("visibility", "hidden");
		$('body').append(clone);
		var height = clone.outerHeight();
		clone.remove();
		return height;
	},
	renderContent: function(){
		var content	= WorkMissionsEditor.textarea.hide().val();											//  get content of editor
		WorkMissionsEditor.markdown.css({opacity: 0.5});
		$.ajax({
			url: "./helper/markdown/ajaxRender",
			data: {content: content},
			method: "POST",
			success: function(html){
				WorkMissionsEditor.markdown.html(html).css({opacity: 1});
				WorkMissionsEditor.resizeInput();
			}
		});
	},
	resizeInput: function(){
		var height = WorkMissionsEditor.markdown.height();
		var length = WorkMissionsEditor.markdown.html().length;
		if(!height && length){
			var clone = WorkMissionsEditor.markdown.clone().css("visibility", "hidden");
			$('body').append(clone);
			var height = clone.outerHeight();
			clone.remove();
		}
		var maxHeight = Math.ceil($(window).height()/2);
		var height =  Math.min(maxHeight, height);
		height = Math.max(height-30, 160);
		WorkMissionsEditor.markdown.css("max-height", maxHeight);
		WorkMissionsEditor.mirror.setSize("99.5%", height);
	},
	sanitizeDateAndTime: function(event){
		var typeValue = parseInt($("#input_type").val(), 10);
		var dayStart  = $(typeValue === 0 ? "#input_dayWork" : "#input_dayStart");
		var dayEnd    = $(typeValue === 0 ? "#input_dayDue" : "#input_dayEnd");
		var timeStart = $("#input_timeStart");
		var timeEnd   = $("#input_timeEnd");
		if(dayStart.val() && dayEnd.val()){
			if(dayStart.val() > dayEnd.val()){
				dayEnd.datepicker("setDate", dayStart.val()).trigger("change-update");
				if(typeValue === 0)
					UI.Messenger.noteNotice("Fälligkeit korrigiert auf: "+dayStart.val());
				else
					UI.Messenger.noteNotice("Endtag korrigiert auf: "+dayStart.val());
			}
			if(dayStart.val() === dayEnd.val()){
				if(timeStart.val() && timeEnd.val()){
					var timeStartValue = parseInt(timeStart.val().replace(/:/, ""), 10);
					var timeEndValue = parseInt(timeEnd.val().replace(/:/, ""), 10);
					if(timeStartValue >= timeEndValue){
						timeEnd.val("").trigger("change-update");
						UI.Messenger.noteNotice("Endzeit war ungültig und wurde geleert.");
					}
				}
			}
		}
	}
};
