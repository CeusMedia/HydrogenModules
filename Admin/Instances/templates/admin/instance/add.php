<?php

$moduleConfig	= $config->getAll( 'module.admin_instances.', TRUE );

if( $moduleConfig->get( 'lock' ) ){
	$moduleConfig->get( 'lock.host' );
	if( ( $protocolLocked = strlen( trim( $moduleConfig->get( 'lock.protocol' ) ) ) ) )
		$protocol	= $moduleConfig->get( 'lock.protocol' );
	if( ( $hostLocked = strlen( trim( $moduleConfig->get( 'lock.host' ) ) ) ) )
		$host	= $moduleConfig->get( 'lock.host' );
	if( ( $pathLocked = strlen( trim( $moduleConfig->get( 'lock.path' ) ) ) ) ){
		$lockPath	= trim( $moduleConfig->get( 'lock.path' ) );
		if( !$path || substr( $path, 0, strlen( $lockPath ) ) != $lockPath )
			$path	= $lockPath;
	}
	if( ( $uriLocked = strlen( trim( $moduleConfig->get( 'lock.uri' ) ) ) ) ){
		$lockUri	= trim( $moduleConfig->get( 'lock.uri' ) );
		if( !$uri || substr( $uri, 0, strlen( $lockUri ) ) != $lockUri )
			$uri	= $lockUri;
	}
}

$optProtocol	= array( '' => '' );
foreach( $words['protocols'] as $key => $value )
	$optProtocol[$key.'://']	= $value;
$optProtocol	= UI_HTML_Elements::Options( $optProtocol, $protocol );

$panelAdd	= '
<form action="./admin/instance/add" method="post">
	<fieldset>
		<legend class="add">neue Instanz</legend>
		<ul class="input">
			<li class="column-left-75">
				<label for="input_title" class="mandatory">Titel</label><br/>
				<input type="text" name="title" id="input_title" value="'.htmlentities( $title, ENT_QUOTES, 'UTF-8' ).'" class="max"/>
			</li>
			<li class="column-left-25">
				<label for="input_id" class="mandatory">Instanz-ID</label><br/>
				<input type="text" name="id" id="input_id" value="'.htmlentities( $id, ENT_QUOTES, 'UTF-8' ).'" class="max"/>
			</li>
			<li class="column-left-20">
				<label for="input_protocol" class="">Protokol</label><br/>
				<select name="protocol" id="input_protocol" class="max" '.( $protocolLocked ? 'disabled="disabled"' : "" ).'>'.$optProtocol.'</select>
			</li>
			<li class="column-left-30">
				<label for="input_host" class="mandatory">Server-Host / Domäne</label><br/>
				<input type="text" name="host" id="input_host" value="'.htmlentities( $host, ENT_QUOTES, 'UTF-8' ).'" class="max mandatory" '.( $hostLocked ? 'disabled="disabled"' : "" ).'/>
			</li>
			<li class="column-left-50">
				<label for="input_path" class="">Pfad</label><br/>
				<input type="text" name="path" id="input_path" value="'.htmlentities( $path, ENT_QUOTES, 'UTF-8' ).'" class="max" data-default="/"/>
			</li>
             <li class="column-clear">
				<label for="input_uri" class="mandatory">Absoluter Pfad <small>auf dem Server</small></label><br/>
				<input type="text" name="uri" id="input_uri" value="'.htmlentities( $uri, ENT_QUOTES, 'UTF-8' ).'" class="max"/>
			</li>
			<li class="column-left-50">
				<label for="input_configPath" class="">Konfigurationspfad</label><br/>
				<input type="text" name="configPath" id="input_configPath" value="'.htmlentities( $configPath, ENT_QUOTES, 'UTF-8' ).'" class="max"/>
			</li>
			<li class="column-right-50">
				<label for="input_configFile" class="">Konfigurationsdatei</label><br/>
				<input type="text" name="configFile" id="input_configFile" value="'.htmlentities( $configFile, ENT_QUOTES, 'UTF-8' ).'" class="max"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './admin/instance', 'zur Liste', 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'add', 'hinzufügen', 'button add' ).'
		</div>
	</fieldset>
</form>
<style>
input.default {
	color: gray;
	}
</style>
<script>
function showDefaultInputValues(selector){
	$(selector).find("input").each(function(nr){
		if($(this).data("default")){
			$(this).bind("focus blue keyup init",function(){
				var i = $(this);
				if(i.val() == i.data("default"))
					i.addClass("default");
				else
					i.removeClass("default");
			}).trigger("init");
		}
	});
}
$(document).ready(function(){
	showDefaultInputValues("form");
});
</script>
';

$panelInfo		= $this->loadContentFile( 'html/admin/instance/add.info.html' );

return '
<div class="column-left-60">
	'.$panelAdd.'
</div>
<div class="column-left-40">
	'.$panelInfo.'
</div>
<div class="column-clear"></div>';
?>
