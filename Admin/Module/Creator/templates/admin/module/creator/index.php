<?php
$w	= (object) $words['index'];
$panelAdd	= '
<fieldset>
	<legend class="module-add">Neues lokales Module</legend>
	<form action="./manage/module/creator" method="post">
		<ul class="input">
			<li class="column-right-50">
				<label for="input_add_description">'.$w->labelDescription.'</label><br/>
				<textarea name="add_description" id="input_add_description" rows="10">'.$request->get( 'add_description' ).'</textarea>
			</li>
			<li class="column-left-50">
				<label for="input_add_title">'.$w->labelTitle.'</label><br/>
				<input type="text" name="add_title" id="input_add_title" value="'.$request->get( 'add_title' ).'"/>
			</li>
			<li class="column-left-20">
				<label for="input_add_id">'.$w->labelModuleId.'</label><br/>
				<input type="text" name="add_id" id="input_add_id" readonly="readonly" style="background-color: #EEE; border-color: #BBB;"/>
			</li>
			<li class="column-left-20">
				<label for="input_add_path">'.$w->labelModulePath.'</label><br/>
				<input type="text" name="add_path" id="input_add_path" readonly="readonly" style="background-color: #EEE; border-color: #BBB;"/>
			</li>
			<li class="column-left-10">
				<label for="input_add_filepath">'.$w->labelFilePath.'</label><br/>
				<input type="text" name="add_filepath" id="input_add_filepath" readonly="readonly" style="background-color: #EEE; border-color: #BBB;"/>
			</li>
			<li class="column-left-50">
				<label for="input_add_route">'.$w->labelRoute.'</label><br/>
				<input type="text" name="add_route" id="input_add_route" value="'.$request->get( 'add_route' ).'"/>
			</li>
			<li class="column-left-20">
				<label for="input_add_version">'.$w->labelVersion.'</label><br/>
				<input type="text" name="add_version" id="input_add_version" value="'.$request->get( 'add_version' ).'"/>
			</li>
			<li class="column-left-30">
				<label for="input_add_scafold">
					<input type="checkbox" name="add_scafold" id="input_add_scafold"/>
					'.$w->labelScafold.'
				</label><br/>
				<strike><label for="input_add_import">
					<input type="checkbox" name="add_import" id="input_add_import" disabled="disabled"/>
					'.$w->labelImport.'
				</label></strike><br/>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './manage/module', $w->buttonCancel, 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'create', $w->buttonCreate, 'button add' ).'
		</div>
	</form>
</fieldset>
	<script>
function getModuleIdFromString(string){
	string	= $.trim(string);
	if(!string.match(/^[a-z].+[a-z0-9]$/i))
		string	= "";
	string = string.replace(/[^a-z0-9]+/ig,"_");
	return string.replace(/_+/,"_");
}

$(document).ready(function(){
	

	$("#input_add_title").bind("keydown keyup",function(){
		var id = getModuleIdFromString($(this).val());
		$("#input_add_id").val(id);
		var path = id.replace(/_/g,"/");
		$("#input_add_path").val(path);
		$("#input_add_filepath").val(path.toLowerCase());
		$("#input_add_route").val(path.toLowerCase());
	}).trigger("keyup");
});
	</script>
';
return '
<div>
	'.$panelAdd.'
</div>';
?>