<?php

$panelEdit	= '
<form action="./admin/instance/edit/'.$instance->id.'" method="post">
	<fieldset>
		<legend class="edit">Instanz bearbeiten</legend>
		<ul class="input">
			<li class="column-left-75">
				<label for="input_title" class="mandatory">Titel</label><br/>
				<input type="text" name="title" id="input_title" value="'.$instance->title.'" class="max"/>
			</li>
			<li class="column-left-25">
				<label for="input_id" class="mandatory">Instanz-ID</label><br/>
				<input type="text" name="id" id="input_id" value="'.$instance->id.'" class="max"/>
			</li>
			<li class="column-left-20">
				<label for="input_protocol" class="">Protokol</label><br/>
				<select name="protocol" id="input_protocol" class="max"><option>http://</option></select>
			</li>
			<li class="column-left-30">
				<label for="input_host" class="mandatory">Server-Host / Domäne</label><br/>
				<input type="text" name="host" id="input_host" value="'.$instance->host.'" class="max mandatory"/>
			</li>
			<li class="column-left-50">
				<label for="input_path" class="">Pfad</label><br/>
				<input type="text" name="path" id="input_path" value="'.$instance->path.'" class="max"/>
			</li>
			<li class="column-clear">
				<label for="input_uri" class="mandatory">Absoluter Pfad <small>auf dem Server</small></label><br/>
				<input type="text" name="uri" id="input_uri" value="'.$instance->uri.'" class="max"/>
			</li>
			<li class="column-left-50">
				<label for="input_configPath" class="">Konfigurationspfad</label><br/>
				<input type="text" name="configPath" id="input_configPath" value="'.$instance->configPath.'" class="max"/>
			</li>
			<li class="column-right-50">
				<label for="input_configFile" class="">Konfigurationsdatei</label><br/>
				<input type="text" name="configFile" id="input_configFile" value="'.$instance->configFile.'" class="max"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './admin/instance', 'zur Liste', 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'edit', 'speichern', 'button save' ).'
		</div>
	</fieldset>
</form>
';

$panelInfo		= $this->loadContentFile( 'html/admin/instance/edit.info.html' );

$panelCheck		= $this->loadTemplateFile( 'admin/instance/edit.check.php' );
$panelDatabase	= $this->loadTemplateFile( 'admin/instance/edit.database.php' );

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
	$("#input_database_driver").trigger("change");
});
</script>
	
<div class="column-left-60">
	'.$panelEdit.'
	'.$panelCheck.'
	'./*$panelDatabase.*/'
</div>
<div class="column-left-40">
	'.$panelInfo.'
</div>
<div class="column-clear"></div>';
?>
