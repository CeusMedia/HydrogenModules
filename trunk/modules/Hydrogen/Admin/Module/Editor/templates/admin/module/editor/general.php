<?php

$w	= (object) $words['view'];


$attributes			= array( 'type' => 'button', 'class' => 'button cancel auto-back', 'readonly' => 'readonly', 'disabled' => 'disabled' );
$buttonBack			= UI_HTML_Tag::create( 'button', '<span>'.$w->buttonBack.'</span>', $attributes );
$buttonList			= UI_HTML_Elements::LinkButton( './admin/module', $w->buttonList, 'button cancel' );
$buttonCancel		= UI_HTML_Elements::LinkButton( './admin/module/viewer/index/'.$module->id, $w->buttonCancel, 'button cancel' );
$buttonSave			= UI_HTML_Elements::Button( 'save', $w->buttonSave, 'button save' );
$buttonRemove		= UI_HTML_Elements::LinkButton( './admin/module/editor/remove/'.$module->id, $w->buttonRemove, 'button remove', $w->buttonRemoveConfirm );
$buttonExport		= UI_HTML_Elements::LinkButton( './admin/module/editor/export/'.$module->id, 'exportieren', 'button' );
$buttons			= '<div class="buttonbar">
	'.$buttonBack.'
<!--	'.$buttonList.'
	'.$buttonCancel.'
-->	'.$buttonSave.'
	'.$buttonRemove.'
	'.$buttonExport.'
</div>';

$panelEdit	= '
<fieldset>
	<legend>Modulbeschreibung</legend>
	<form action="./admin/module/editor/edit/'.$moduleId.'" method="post">
		<ul class="input">
			<li class="column-left-75">
				<label for="input_edit_title" class="mandatory">'.$w->labelTitle.'</label><br/>
				<input type="text" name="edit_title" id="input_edit_title" class="max mandatory" value="'.$module->title.'"/>
			</li>
			<li class="column-left-25">
				<label for="input_edit_version" class="mandatory">'.$w->labelVersion.'</label><br/>
				<input type="text" name="edit_version" id="input_edit_version" class="max mandatory" value="'.$module->version.'"/>
			</li>
			<li class="column-left-50">
				<label for="input_edit_id">'.$w->labelModuleId.'</label><br/>
				<input type="text" name="edit_id" id="input_edit_id" class="max" readonly="readonly" disabled="disabled" style="background-color: #EEE; border-color: #BBB;" value="'.$module->id.'"/>
			</li>
			<li class="column-left-50">
				<label for="input_edit_path">'.$w->labelModulePath.'</label><br/>
				<input type="text" name="edit_path" id="input_edit_path" class="max" readonly="readonly" disabled="disabled" style="background-color: #EEE; border-color: #BBB;" value="'.str_replace( '_', '/', $module->id ).'"/>
			</li>
			<li class="column-clear not-column-right-50">
				<label for="input_edit_description">'.$w->labelDescription.'</label><br/>
				<textarea name="edit_description" id="input_edit_description" class="max" rows="10">'.$module->description.'</textarea>
			</li>
		</ul>
		'.$buttons.'
	</form>
</fieldset>
';

$panelCompanies	= $this->loadTemplateFile( 'admin/module/editor/general.companies.php' );
$panelAuthors	= $this->loadTemplateFile( 'admin/module/editor/general.authors.php' );
$panelLicenses	= $this->loadTemplateFile( 'admin/module/editor/general.licenses.php' );

/*$panelData	= '
<dl>
	<dt>'.$w->versionAvailable.'</dt>
	<dd>'.( $module->versionAvailable ? $module->versionAvailable : '-' ).'</dd>
	<dt>'.$w->type.'</dt>
	<dd><span class="module-type type-'.$module->type.'">'.$words['types'][$module->type].'</span></dd>
</dl>
<div class="clearfix"></div>';
*/

$optSource	= array( '' => '- wählen -' );
foreach( $sources as $source ){
	if( $source->active )
		if( strtolower( $source->type ) == "folder" )
			$optSource[$source->id]	= /*$source->id.': '.*/$source->title;
}
$optSource	= UI_HTML_Elements::Options( $optSource, $request->get( 'source' ) );

$panelCommit	= '
<form action="./admin/module/editor/commit/'.$moduleId.'" method="post">
	<fieldset>
		<legend>Module zu Quelle einreichen</legend>
		<ul class="input">
			<li class="column-left-50">
				<label for="input_source" class="mandatory">Quelle</label><br/>
				<select id="input_source" name="source" class="max mandatory">'.$optSource.'</select>
<!--				<input type="text" id="input_source" name="source">-->
			</li>
			<li class="column-left-50">
				<label for="input_writable">Schreibrecht</label><br/>
				<input type="text" name="writable" id="input_writable" class="s" readonly="readonly" disabled="disabled" style="background-color: #EEE; border-color: #BBB;" value=""/>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'commit', 'einreichen', 'button add import' ).'
		</div>
	</fieldset>
</form>
<script>
var sourceAccessWords	= ["","nein","ja"];
function getSourceInfo(elem){
	var sourceId = $(elem).val();
	var form = $(elem.form);
	var button = form.find("button[type=submit]");
	var label = form.find("#input_writable");
	label.val(sourceAccessWords[0]);
	label.css("background-color","#EEEEEE");
	button.prop("disabled", true);
	if(sourceId){
		$.ajax({
			url: "./admin/source/ajaxReadSource/"+sourceId,
			dataType: "json",
			cache: false,
			type: "post",
			success: function(data){
	//			console.log(data);
	//			data.access	= 4;
	//			console.log(data.access & 4 && true || false);
	//			console.log(data.access & 2 && true || false);
	//			console.log(data.access & 1 && true || false);
				label.val(sourceAccessWords[1]);
				label.css("background-color","#FFDDDD");
				if(data.access & 2 && true || false){
					button.prop("disabled",null);
					label.val(sourceAccessWords[2]);
					label.css("background-color","#DDFFDD");
				}
			}
		});
	}
}

$(document).ready(function(){
	$("#input_source").bind("change",function(){
		getSourceInfo(this);
	}).trigger("change");
});
</script>
';

$buttonOpen	= '<button type="button" class="button iconed tiny edit form-trigger"><span></span></button>';
$image		= '<span class="hint"><small><em>Kein Bild vorhanden</em></small></span>';
if( $module->icon )
	$image	=  '<div style="text-align: center"><img src="'.$module->icon.'"/></div>';
$urlRemove	= './admin/module/editor/removeIcon/'.$moduleId;
$panelImage	= '
<form action="./admin/module/editor/uploadIcon/'.$moduleId.'" method="post" enctype="multipart/form-data">
	<fieldset>
		<legend class="icon image">Bild</legend>
		'.$buttonOpen.'
		'.$image.'
		<ul class="input" style="display: none">
			<li>
				<label for="input_image">Bild&nbsp;<small><em>(nur PNG, max. 128 x 128 Pixel)</em></small></label><br/>
				<input type="file" name="image" id="input_image"/>
			</li>
		</ul>
		<div class="buttonbar" style="display: none">
			'.UI_HTML_Elements::Button( 'upload', 'hochladen', 'button add' ).'
<!--			'.UI_HTML_Elements::LinkButton( $urlRemove, 'entfernen', 'button remove', 'Wirklich?' ).'-->
		</div>
	</fieldset>
</form>
';

return '
<div class="column-left-70">
	'./*$panelData.*/'
	'.$panelEdit.'
	'.$panelCommit.'
</div>
<div class="column-right-30">
	'.$panelImage.'
	'.$panelCompanies.'
	'.$panelAuthors.'
	'.$panelLicenses.'
</div>
<div class="column-clear"></div>';
?>