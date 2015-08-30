
var AdminModuleInstaller = {
	appName: '<em>unknown application<em>',
	labels: {
		en: {
			msgDone: '<b>Done.</b><br/>Loading <cite>#AppName#</cite> now ...',
			msgError: '<b>Error:</b> Installation failed. See response below!',
			msgErrorConfig: '<b>Error:</b> File <cite>config/config.ini</cite> could not be created.',
			msgStarted: '<b>Installation in progress.</b><br/>Please wait ...'
		},
		de: {
			msgDone: '<b>Fertig!</b><br/><cite>#AppName#</cite> wird nun gestartet ...',
			msgError: '<b>Fehler:</b> Installation fehlgeschlagen.',
			msgErrorConfig: '<b>Fehler:</b> Datei <cite>config/config.ini</cite> konnte nicht erzeugt werden.',
			msgStarted: '<b>Die Installation läuft.</b><br/>Bitte warten ...'
		}
	},
	init: function(locale,appName){
		if(typeof this.labels[locale] === "undefined")
			locale = 'en';
		this.labels	= this.labels[locale];
		for(var key in this.labels)
			if(typeof this.labels[key] == "string")
				this.labels[key] = this.labels[key].replace(/#AppName#/, appName);
		this.appName = appName;

//$(document).ready(function(){
		$("#input_update_files_show_unchanged").bind("change", AdminModuleInstaller.toggleUpdateFilesWithoutChanges );
		$("#input_update_files_show_unchanged").trigger("change");
//});
		return this;
	},
	start: function(){
		$("#greeting").fadeIn();
		$("#status").removeClass("failed").addClass("installing").html(AdminModuleInstaller.labels.msgStarted);
		$.ajax({
			url: './',
			success: function(response){
				$("#status").html(" ").removeClass("installing");
				if(response.match(/#greeting/)){
					$("#status").addClass("failed").html(AdminModuleInstaller.labels.msgErrorConfig);
				}
				else if(response.match(/.\/admin\/instance\/select/)){
					$("#status").addClass("done").html(AdminModuleInstaller.labels.msgDone);
					document.location.reload();
				}
				else{
					$("#status").addClass("failed").html(AdminModuleInstaller.labels.msgError);

					//  @see http://stackoverflow.com/questions/7965111/prevent-jquery-ajax-to-execute-javascript-from-script-or-html-response
					var regExp = new RegExp( "<script.*?>([\w\W\d\D\s\S\0\n\f\r\t\v\b\B]*?)<\/script>", "gi");
					response = response.replace(regExp, '');

					preview.document.open("text/html","replace").write(response);
					$("#response").show();
				}
			}
		});
	},

	toggleUpdateFilesWithoutChanges: function(event){
		var isChecked = $(this).prop("checked");
		var table = $("#panel-module-update-files table");
		var rows = table.find("tbody tr");
		var rowsLinked = rows.filter(".status-linked,.status-refered");
		isChecked ? rowsLinked.addClass("hidden") : rowsLinked.removeClass("hidden");
		var rowsHidden = rows.filter(".hidden");
		var rowsVisible = rows.not(rowsHidden);
		rowsVisible.size() ? table.show() : table.hide();
	},

	toggleSubmitButton: function(){
		var button = $("button[type=submit]");
		if(button.prop("disabled"))
			button.prop("disabled",null);
		else
			button.prop("disabled",true);
	}
};

var AdminModuleUpdater = {
    init: function(){
        $("button.button.copy").each(function(){
            $(this).parent().css({position: "relative"});
            $(this).css({position: "absolute", left: "-19px", top: "5px"});
            $(this).css({padding: "2px 2px 0px 2px", height: "22px"});
            $(this).on("click", function(){
                var inputs = $(this).parent().children(":input").not("button");
                inputs.eq(1).val(inputs.eq(0).val());
                inputs.eq(1).trigger("change");
            });
        });
        $("button.button.reset").each(function(){
            $(this).css({position: "absolute", left: "-19px", top: "30px", opacity: 0.5});
            $(this).css({padding: "2px 2px 0px 2px", height: "22px"});
            var inputs = $(this).parent().children(":input").not("button");
            $(this).on("click", {inputs: inputs}, function(event){
                event.data.inputs.eq(1).val(event.data.inputs.eq(1).data("init"));
                $(this).css({opacity: 0.5});
            });
            inputs.eq(1).on("change", {input: inputs.eq(1)}, function(event){
                var button = $(this).parent().children("button.button.reset");
                button.css({opacity: 1});
                if( event.data.input.val() == event.data.input.data("init") )
                    button.css({opacity: 0.5});
            })
        });
//$(document).ready(function(){
		$("#input_update_files_show_unchanged").bind("change", AdminModuleInstaller.toggleUpdateFilesWithoutChanges );
		$("#input_update_files_show_unchanged").trigger("change");
//});
    },
    switchAllFiles: function(){
        var status = parseInt($("#btn_switch_files").data("state"), 10);
        if(status === 0 ){
            $("#file-rows input.file-check").prop("checked", "checked");
            $("#btn_switch_files").data("state", 1);
        }
        else{
            $("#file-rows input.file-check").removeProp("checked");
            $("#btn_switch_files").data("state", 0);
        }
    }
};
