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
			<li>
				<label for="input_path" class="mandatory">Pfad</label><br/>
				<code>'.$root.'</code><input type="text" name="path" id="input_path" value="'.$path.'" class="l"/>
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