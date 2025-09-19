let WorkMissionsEditor = {
	contentFormat: null,
	markdown: null,
	mirror: null,
	mission: null,
	missionWorkerId: 0,
	missionId: null,
	textarea: null,
	userId: null,
	urlEnv: null,
	urlFrontend: null,

	init: function(missionId){
		"use strict";
		this._initForm();
		if(!this.contentFormat){
			this.contentFormat = settings.Work_Missions.format;
			if(this.mission){
				this.contentFormat = this.mission.format;
			}
		}
		if(this.contentFormat.toUpperCase() == "HTML"){
			this._setupTinyMCE(missionId);
		}
		else if(this.contentFormat.toUpperCase() == "MARKDOWN"){
			this._setupMarkdownEditor(missionId);
		}
		else{
			alert('No editor available for format: ' + this.contentFormat);
		}
	},

	_bindWorkerSelectUpdateOnProjectInputChange: function(idSelectWorker, idInputProject){
		let selectProject = $("#"+idInputProject);
		if(selectProject.length){
			if( !this.mission)
				$("#"+idSelectWorker).on("change", function(){
					WorkMissionsEditor.missionWorkerId = $(this).val();
				});
			selectProject.on("change", function(){
				let workerId;
				let projectId = selectProject.val();
				if(WorkMissionsEditor.mission)
					workerId = WorkMissionsEditor.mission.workerId;
				else
					workerId = WorkMissionsEditor.missionWorkerId;
				WorkMissionsEditor._updateProjectWorkers(projectId, workerId, {idSelectWorker: idSelectWorker});
			}).trigger("change");
		}
	},

	_initForm: function(){
		$("#input_title").focus();
		let dateInputs = $("#input_dayWork, #input_dayDue, #input_dayStart, #input_dayEnd");
		let timeInputs = $("#input_timeStart, #input_timeEnd");

/*		dateInputs.datepicker({
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
		});*/

		//  @link   http://trentrichardson.com/examples/timepicker/
//		timeInputs.timepicker({});
//		$("#input_type").trigger("change");
		dateInputs.add(timeInputs).on("change", WorkMissionsEditor._sanitizeDateAndTime);
		this._bindWorkerSelectUpdateOnProjectInputChange("input_workerId", "input_projectId");
	},

	_sanitizeDateAndTime: function(event){
		let typeValue = parseInt($("#input_type").val(), 10);
		let dayStart  = $(typeValue === 0 ? "#input_dayWork" : "#input_dayStart");
		let dayEnd    = $(typeValue === 0 ? "#input_dayDue" : "#input_dayEnd");
		let timeStart = $("#input_timeStart");
		let timeEnd   = $("#input_timeEnd");
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
					let timeStartValue = parseInt(timeStart.val().replace(/:/, ""), 10);
					let timeEndValue = parseInt(timeEnd.val().replace(/:/, ""), 10);
					if(timeStartValue >= timeEndValue){
						timeEnd.val("").trigger("change-update");
						UI.Messenger.noteNotice("Endzeit war ungültig und wurde geleert.");
					}
				}
			}
		}
	},

	_renderContentOfMarkdownEditor: function(){
		let content	= WorkMissionsEditor.textarea.hide().val();											//  get content of editor
		WorkMissionsEditor.markdown.css({opacity: 0.5});
		$.ajax({
			url: "./ajax/helper/markdown/render",
			data: {content: content},
			method: "POST",
			success: function(json){
				$("#work-missions-loader").remove();
				WorkMissionsEditor.markdown.html(json.data).css({opacity: 1});
				WorkMissionsEditor._resizeMarkdownEditor();
			}
		});
	},

	_resizeMarkdownEditor: function(){
		let height = WorkMissionsEditor.markdown.height();
		let length = WorkMissionsEditor.markdown.html().length;
		if(!height && length){
			let clone = WorkMissionsEditor.markdown.clone().css("visibility", "hidden");
			$('body').append(clone);
			height = clone.outerHeight();
			clone.remove();
		}
		let maxHeight = Math.ceil($(window).height()/2);
		height = Math.min(maxHeight, height);
		height = Math.max(height - 19, 160);
		WorkMissionsEditor.markdown.css("max-height", maxHeight);
		WorkMissionsEditor.mirror.setSize("99.5%", height);
	},

	_setupMarkdownEditor: function(missionId){
		"use strict";
		WorkMissionsEditor.missionId = missionId;
		WorkMissionsEditor.userId = Auth.userId;
		WorkMissionsEditor.markdown = $("#mission-content-html");
		let editor = "Ace";
//		var editor = "CodeMirror";
		switch(editor){
			case "Ace":
				this._setupMarkdownEditorWithAce(missionId);
				break;
			case "CodeMirror":
				this._setupMarkdownEditorWithCodeMirror(missionId);
				break;
		}
	},
	_setupMarkdownEditorWithAce: function(missionId){
		let editor 	= ModuleAce.applyTo("#input_content");
		let saveUrl	= "ajax/work/mission/saveContent/"+WorkMissionsEditor.missionId;
		let onUpdate	= function(chance, editor){
			jQuery.post("./ajax/helper/markdown/render", {content: editor.getValue()})
			.done(function(json){
				jQuery("#work-missions-loader").remove();
				WorkMissionsEditor.markdown.html(json.data).css({opacity: 1});
			});
		};
		ModuleAceAutoSave.applyToEditor(editor, saveUrl, {callbacks: {update: onUpdate}});
		onUpdate(null, editor);
	},
	_setupMarkdownEditorWithCodeMirror: function(missionId){
                "use strict";
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
					url: "./ajax/work/mission/saveContent/"+WorkMissionsEditor.missionId,
					data: {content: WorkMissionsEditor.textarea.val()},
					method: "POST",
					dataType: "json",
					success: function(json){
						if(missionId)
							$(".CodeMirror").removeClass("changed");
						WorkMissionsEditor.markdown.html(json.data).css({opacity: 1});
					}
				});
			}
			else{
				WorkMissionsEditor._renderContentOfMarkdownEditor();
				if(missionId)
					$(".CodeMirror").removeClass("changed");
			}
		}, 500);
		WorkMissionsEditor.mirror.on("change", function(instance, update){
			instance.save();																			//  apply changes of markdown editor to input element
			WorkMissionsEditor._resizeMarkdownEditor();
			if(missionId)
				$(".CodeMirror").addClass("changed").trigger("keyup");									//  trigger key up event for automatic save
			$(instance.getTextArea()).trigger("keyup");												//  trigger key up event for automatic rendering
		});
		WorkMissionsEditor._renderContentOfMarkdownEditor();
	},

	_setupTinyMCE: function(missionId){
		"use strict";
		let options = $.extend(tinymce.Config.get(),{
			selector: ".TinyMCE-minimal",
//			height: settings.Work_Missions.editor_height,					//  @todo not working right now
			menubar: settings.Work_Missions.editor_TinyMCE_menubar,
			envUri: this.urlEnv,
			frontendUri: this.urlFrontend,
			toolbar: settings.JS_TinyMCE['auto_toolbar_'+settings.Work_Missions.editor_TinyMCE_toolbar]
		});
		$("#work-missions-loader").remove();
		tinymce.init(options);
	},

	_updateProjectWorkers: function(projectId, currentWorkerId, options){
		currentWorkerId = parseInt(currentWorkerId, 10);
		let _options = $.extend({
			idSelectWorker: "input_workerId",
			urlGetProjectUsers: "./ajax/work/mission/getProjectUsers/"+projectId,
			allowEmpty: true,
		}, options);
		$.ajax({
			url: _options.urlGetProjectUsers,
			dataType: "json",
			success: function(json){
				let selectWorker = $("#"+_options.idSelectWorker).html("");
				if(_options.allowEmpty){
					let option = $("<option></option>").val("").html("-");
					selectWorker.append(option);
				}
				$(json.data).each(function(nr){
					let option = $("<option></option>").val(this.userId).html(this.username);
					this.userId = parseInt(this.userId, 10);
					if(currentWorkerId === this.userId || json.data.length === 1 || ( !currentWorkerId && this.userId === Auth.userId ) )
						option.prop("selected", "selected");
					selectWorker.append(option);
				});
				selectWorker.trigger("change");
			}
		});
	}
};
