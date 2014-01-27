<?php

$w	= (object) $words['index'];

$optDriver	= array( '' => '- keiner -' );
foreach( $drivers as $driver )
	$optDriver[$driver]	= $words['database-drivers'][$driver];
$optDriver	= UI_HTML_Elements::Options( $optDriver, $data->driver );

$panelDatabase	= '
<form id="form_database_connection" action="./admin/database/connection/configure" method="post">
	<fieldset>
		<legend class="database">'.$w->legend.'</legend>
		<ul class="input">
			<li class="column-left-30">
				<label for="input_access_driver" class="mandatory">'.$w->labelDriver.'</label><br/>
				<select name="access_driver" id="input_access_driver" class="max mandatory">'.$optDriver.'</select>
			</li>
			<li class="column-left-60 optional access_driver access_driver-mysql access_driver-pgsql access_driver-mssql">
				<label for="input_access_host" class="mandatory">'.$w->labelHost.'</label><br/>
				<input type="text" name="access_host" id="input_access_host" class="max mandatory" value="'.htmlentities( $data->host ).'"/>
			</li>
			<li class="column-left-10 optional access_driver access_driver-mysql access_driver-pgsql access_driver-mssql">
				<label for="input_access_port" class="optional">'.$w->labelPort.'</label><br/>
				<input type="text" name="access_port" id="input_access_port" class="max numeric" value="'.htmlentities( $data->port ).'"/>
			</li>
			<li class="column-clear column-left-40 optional access_driver access_driver-mysql access_driver-pgsql access_driver-mssql access_driver-sqlite">
				<label for="input_access_name" class="mandatory">'.$w->labelName.'</label><br/>
				<input type="text" name="access_name" id="input_access_name" class="max mandatory" value="'.htmlentities( $data->name ).'"/>
			</li>
			<li class="column-left-20 optional access_driver access_driver-mysql access_driver-pgsql access_driver-mssql access_driver-sqlite">
				<label for="input_access_prefix" class="optional">'.$w->labelPrefix.'</label><br/>
				<input type="text" name="access_prefix" id="input_access_prefixs" class="max" value="'.htmlentities( $data->prefix ).'"/>
			</li>
			<li class="column-left-20 optional access_driver access_driver-mysql access_driver-pgsql access_driver-mssql">
				<label for="input_access_username" class="mandatory">'.$w->labelUsername.'</label><br/>
				<input type="text" name="access_username" id="input_access_username" class="max mandatory" value="'.htmlentities( $data->username ).'"/>
			</li>
			<li class="column-left-20 optional access_driver access_driver-mysql access_driver-pgsql access_driver-mssql">
				<label for="input_access_password" class="mandatory">'.$w->labelPassword.'</label><br/>
				<input type="password" name="access_password" id="input_access_password" class="max mandatory" value="'.htmlentities( $data->password ).'"/>
			</li>
<!--			<li class="column-clear">
				<label for="input_access_log">'.$w->labelLog.'</label><br/>
				<input type="text" name="access_log" id="input_access_log" class="" value="'.htmlentities( $data->log ).'"/>
			</li>-->
		</ul>
		<div id="status-info" class="column-clear"></div>
		<div class="buttonbar">
			<button type="button" id="button_check" class="button check connect"><span>'.$w->buttonCheck.'</span></button>
			'.UI_HTML_Elements::Button( 'save', $w->buttonSave, 'button edit save', NULL, TRUE ).'
		</div>
	</fieldset>
</form>';

$panelInfo	= '';

return '
<script>
function showOptionals(elem){
	var form = $(elem.form);
	var name = $(elem).attr("name");
	var type = name+"-"+$(elem).val();
	form.find(".optional."+name).not("."+type).hide();
	form.find(".optional."+type).show();
}
$(document).ready(function(){
	$("#form_database_connection #input_access_driver").bind("change",function(){
		showOptionals(this);
		$("#form_database_connection #status-info").fadeOut();
		if($(this).val().length){
			$("#form_database_connection #button_check").removeAttr("disabled");
			$("#form_database_connection button.save").attr("disabled",true);
		}
		else{
			$("#form_database_connection #button_check").attr("disabled",true);
			$("#form_database_connection button.save").removeAttr("disabled");
		}
	}).trigger("change");
	$("#form_database_connection input").bind("keyup change",function(){
		$("#form_database_connection #button_check").removeAttr("disabled");
		$("#form_database_connection button.save").attr("disabled",true);
		$("#form_database_connection #status-info").fadeOut();
	});
	$("#form_database_connection #button_check").bind("click",function(){
		var form = $("#form_database_connection");
		$.ajax({
			url: "./admin/database/connection/ajaxCheck",
			data: {
				driver: form.find("#input_access_driver").val(),
				host: form.find("#input_access_host").val(),
				port: form.find("#input_access_port").val(),
				name: form.find("#input_access_name").val(),
				username: form.find("#input_access_username").val(),
				password: form.find("#input_access_password").val()
			},
			dataType: "json",
			type: "post",
			success: function(data){
				var status = $("#form_database_connection #status-info");
				status.removeClass("database-status-0").removeClass("database-status-1");
				status.addClass("database-status-"+data.status);
				hint = data.error ? data.error.replace("\n","<br/>") : "OK";
				status.html(hint).fadeIn();
				if(data.status){
					$("#form_database_connection #button_check").attr("disabled",true);
					$("#form_database_connection button.save").removeAttr("disabled");
				}
			}
		});
	});
});
</script>
	
<div class="column-left-60">
	'.$panelDatabase.'
</div>
<div class="column-left-40">
	'.$panelInfo.'
</div>
<div class="column-clear"></div>';
?>