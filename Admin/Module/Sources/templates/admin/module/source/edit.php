<?php

$w	= (object) $words['edit'];

$optType	= UI_HTML_Elements::Options( $words['types'], $source->type );

$panelEdit	= '
<form action="./admin/module/source/edit/'.$source->id.'" method="post">
	<fieldset>
		<legend class="edit">'.$w->legend.'</legend>
		<ul class="input">
			<li class="column-left-75">
				<label for="input_title" class="mandatory">'.$w->labelTitle.'</label><br/>
				<input type="text" name="title" id="input_title" value="'.htmlentities( $source->title ).'" class="max"/>
			</li>
			<li class="column-left-25">
				<label for="input_id" class="mandatory">'.$w->labelId.'</label><br/>
				<input type="text" name="id" id="input_id" value="'.htmlentities( $source->id ).'" class="max"/>
			</li>
			<li class="column-left-25">
				<label for="input_type" class="mandatory">'.$w->labelType.'</label><br/>
				<select name="type" id="input_type" class="max">'.$optType.'</select>
			</li>
			<li class="column-left-75">
				<label for="input_path" class="mandatory">'.$w->labelPath.'</label><br/>
				<input type="text" name="path" id="input_path" value="'.htmlentities( $source->path ).'" class="max"/>
			</li>
			<li class="column-clear">
				<label for="input_active">
					'.UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> "checkbox",
						'name'		=> "active",
						'id'		=> "input_active",
						'checked'	=> $source->active == "yes" ? 'checked' : NULL,
					) ).'&nbsp;'.$w->labelActive.'
				</label>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './admin/module/source', $w->buttonCancel, 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'edit', $w->buttonSave, 'button save' ).'
			'.UI_HTML_Elements::LinkButton( './admin/module/source/refresh/'.$source->id.'/0', $w->buttonRefresh, 'button icon refresh' ).'
			'.UI_HTML_Elements::LinkButton( './admin/module/source/remove/'.$source->id, $w->buttonRemove, 'button remove', $w->buttonRemoveConfirm ).'
		</div>
	</fieldset>
</form>
';

$panelInfo	= $view->loadContentFile( 'html/admin/module/source/edit.info.html' );

return '
<script>
var sourceId = "'.$source->id.'";
</script>
<div class="column-left-60">
	'.$panelEdit.'
</div>
<div class="column-left-40">
	'.$panelInfo.'
	<fieldset id="panelModules" style="display: none;">
		<legend class="module">'.$words['listFoundModule']['legend'].'&nbsp;<small>(<span id="count-modules"></span>)</small></legend>
		<div id="panelModules-content">
		</div>
	</fieldset>
</div>
<div class="column-clear"></div>';
?>