<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$w	= (object) $words['edit'];

$optType	= HtmlElements::Options( $words['types'], $source->type );

$panelEdit	= '
<form action="./admin/module/source/edit/'.$sourceId.'" method="post">
	<fieldset>
		<legend class="edit">'.$w->legend.'</legend>
		<ul class="input">
			<li class="column-left-75">
				<label for="input_title" class="mandatory">'.$w->labelTitle.'</label><br/>
				<input type="text" name="title" id="input_title" value="'.htmlentities( $source->title ).'" class="max"/>
			</li>
			<li class="column-left-25">
				<label for="input_id" class="mandatory">'.$w->labelId.'</label><br/>
				<input type="text" name="id" id="input_id" value="'.htmlentities( $sourceId ).'" class="max"/>
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
					'.HtmlTag::create( 'input', NULL, [
						'type'		=> "checkbox",
						'name'		=> "active",
						'id'		=> "input_active",
						'checked'	=> $source->active == "yes" ? 'checked' : NULL,
					] ).'&nbsp;'.$w->labelActive.'
				</label>
			</li>
		</ul>
		<div class="buttonbar">
			'.HtmlElements::LinkButton( './admin/module/source', $w->buttonCancel, 'button cancel' ).'
			'.HtmlElements::Button( 'edit', $w->buttonSave, 'button save' ).'
			'.HtmlElements::LinkButton( './admin/module/source/refresh/'.$sourceId.'/0', $w->buttonRefresh, 'button icon refresh' ).'
			'.HtmlElements::LinkButton( './admin/module/source/remove/'.$sourceId, $w->buttonRemove, 'button remove', $w->buttonRemoveConfirm ).'
		</div>
	</fieldset>
</form>';

$panelInfo	= $view->loadContentFile( 'html/admin/module/source/edit.info.html' );

return '
<script>
var sourceId = "'.$sourceId.'";
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