<?php

$moduleConfig	= $config->getAll( 'module.admin_instances.', TRUE );
$protocolLocked	= FALSE;
$hostLocked		= NULL;
$pathLocked		= NULL;

if( $moduleConfig->get( 'lock' ) ){
	$moduleConfig->get( 'lock.host' );
	if( ( $protocolLocked = strlen( trim( $moduleConfig->get( 'lock.protocol' ) ) ) ) )
		$instance->protocol	= $moduleConfig->get( 'lock.protocol' );
	if( ( $hostLocked = strlen( trim( $moduleConfig->get( 'lock.host' ) ) ) ) )
		$instance->host	= $moduleConfig->get( 'lock.host' );
	if( ( $pathLocked = strlen( trim( $moduleConfig->get( 'lock.path' ) ) ) ) ){
		$lockPath	= trim( $moduleConfig->get( 'lock.path' ) );
		if( !$instance->path || substr( $instance->path, 0, strlen( $lockPath ) ) != $lockPath )
			$instance->path	= $lockPath.$instance->path;
	}
	if( ( $uriLocked = strlen( trim( $moduleConfig->get( 'lock.uri' ) ) ) ) ){
		$lockUri	= trim( $moduleConfig->get( 'lock.uri' ) );
		if( !$instance->uri || substr( $instance->uri, 0, strlen( $lockUri ) ) != $lockUri )
			$instance->uri	= $lockUri.$instance->uri;
	}
}

$optProtocol	= array( '' => '' );
foreach( $words['protocols'] as $key => $value )
	$optProtocol[$key.'://']	= $value;
$optProtocol	= UI_HTML_Elements::Options( $optProtocol, $instance->protocol );

$panelEdit	= '
<form action="./admin/instance/edit/'.$instance->id.'" method="post">
	<fieldset>
		<legend class="edit">Instanz bearbeiten</legend>
		<ul class="input">
			<li class="column-left-75">
				<label for="input_title" class="mandatory">Titel</label><br/>
				<input type="text" name="title" id="input_title" value="'.htmlentities( $instance->title, ENT_QUOTES, 'UTF-8' ).'" class="max"/>
			</li>
			<li class="column-left-25">
				<label for="input_id" class="mandatory">Instanz-ID</label><br/>
				<input type="text" name="id" id="input_id" value="'.htmlentities( $instance->id, ENT_QUOTES, 'UTF-8' ).'" class="max"/>
			</li>
			<li class="column-left-20">
				<label for="input_protocol" class="">Protokol</label><br/>
				<select name="protocol" id="input_protocol" class="max" '.( $protocolLocked ? 'disabled="disabled"' : "" ).'>'.$optProtocol.'</select>
			</li>
			<li class="column-left-30">
				<label for="input_host" class="mandatory">Server-Host / Domäne</label><br/>
				<input type="text" name="host" id="input_host" value="'.htmlentities( $instance->host, ENT_QUOTES, 'UTF-8' ).'" class="max mandatory" '.( $hostLocked ? 'disabled="disabled"' : "" ).'/>
			</li>
			<li class="column-left-50">
				<label for="input_path" class="">Pfad</label><br/>
				<input type="text" name="path" id="input_path" value="'.htmlentities( $instance->path, ENT_QUOTES, 'UTF-8' ).'" class="max"/>
			</li>
			<li class="column-clear">
				<label for="input_uri" class="mandatory">Absoluter Pfad <small>auf dem Server</small></label><br/>
				<input type="text" name="uri" id="input_uri" value="'.htmlentities( $instance->uri, ENT_QUOTES, 'UTF-8' ).'" class="max"/>
			</li>
			<li class="column-left-50">
				<label for="input_configPath" class="">Konfigurationspfad <small>(Standard: <code>config/</code>)</small></label><br/>
				<input type="text" name="configPath" id="input_configPath" value="'.htmlentities( $instance->configPath, ENT_QUOTES, 'UTF-8' ).'" class="max"/>
			</li>
			<li class="column-right-50">
				<label for="input_configFile" class="">Konfigurationsdatei <small>(Standard: <code>config.ini</code>)</small></label><br/>
				<input type="text" name="configFile" id="input_configFile" value="'.htmlentities( $instance->configFile, ENT_QUOTES, 'UTF-8' ).'" class="max"/>
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
$panelConfig	= $this->loadTemplateFile( 'admin/instance/edit.config.php' );

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
	'.$panelConfig.'
	'./*$panelDatabase.*/'
</div>
<div class="column-left-40">
	'.$panelInfo.'
</div>
<div class="column-clear"></div>';
?>
