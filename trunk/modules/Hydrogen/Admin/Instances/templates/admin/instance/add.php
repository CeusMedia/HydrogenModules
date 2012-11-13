<?php

$panelAdd	= '
<form action="./admin/instance/add" method="post">
	<fieldset>
		<legend class="add">neue Instanz</legend>
		<ul class="input">
			<li class="column-left-75">
				<label for="input_title" class="mandatory">Titel</label><br/>
				<input type="text" name="title" id="input_title" value="'.$title.'" class="max"/>
			</li>
			<li class="column-left-25">
				<label for="input_id" class="mandatory">Instanz-ID</label><br/>
				<input type="text" name="id" id="input_id" value="'.$id.'" class="max"/>
			</li>
			<li class="column-left-20">
				<label for="input_protocol" class="">Protokol</label><br/>
				<select name="protocol" id="input_protocol" class="max"><option>http://</option></select>
			</li>
			<li class="column-left-30">
				<label for="input_host" class="mandatory">Server-Host / Domäne</label><br/>
				<input type="text" name="host" id="input_host" value="'.$host.'" class="max mandatory"/>
			</li>
			<li class="column-left-50">
				<label for="input_path" class="">Pfad</label><br/>
				<input type="text" name="path" id="input_path" value="'.$path.'" class="max" data-default="/"/>
			</li>
             <li class="column-clear">
				<label for="input_uri" class="mandatory">Absoluter Pfad <small>auf dem Server</small></label><br/>
				<input type="text" name="uri" id="input_uri" value="'.$uri.'" class="max"/>
			</li>
			<li class="column-left-50">
				<label for="input_configPath" class="">Konfigurationspfad</label><br/>
				<input type="text" name="configPath" id="input_configPath" value="'.$configPath.'" class="max"/>
			</li>
			<li class="column-right-50">
				<label for="input_configFile" class="">Konfigurationsdatei</label><br/>
				<input type="text" name="configFile" id="input_configFile" value="'.$configFile.'" class="max"/>
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
