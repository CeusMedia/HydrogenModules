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
			<li>
				<label for="input_path" class="mandatory">Pfad</label><br/>
				<code>'.$root.'</code><input type="text" name="path" id="input_path" value="'.$instance->path.'" class="l"/>
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
	'.$panelDatabase.'
</div>
<div class="column-left-40">
	'.$panelInfo.'
</div>
<div class="column-clear"></div>';
?>