<?php

$w	= (object) $words['add'];

$optType	= UI_HTML_Elements::Options( $words['types'] );

$panelAdd	= '
<form action="./admin/module/source/add" method="post">
	<fieldset>
		<legend class="add">'.$w->legend.'</legend>
		<ul class="input">
			<li class="column-left-75">
				<label for="input_title" class="mandatory">'.$w->labelTitle.'</label><br/>
				<input type="text" name="title" id="input_title" value="'.$title.'" class="max"/>
			</li>
			<li class="column-left-25">
				<label for="input_id" class="mandatory">'.$w->labelId.'</label><br/>
				<input type="text" name="id" id="input_id" value="'.$id.'" class="max"/>
			</li>
			<li class="column-left-25">
				<label for="input_type" class="mandatory">'.$w->labelType.'</label><br/>
				<select name="type" id="input_type" class="max">'.$optType.'</select>
			</li>
			<li class="column-left-75">
				<label for="input_path" class="mandatory">'.$w->labelPath.'</label><br/>
				<input type="text" name="path" id="input_path" value="'.$path.'" class="max"/>
			</li>
			<li class="column-clear">
				<label for="input_active">
					'.UI_HTML_Tag::create( 'input', NULL, array(
						'type'		=> "checkbox",
						'name'		=> "active",
						'id'		=> "input_active",
						'checked'	=> $active ? 'checked' : NULL,
					) ).'&nbsp;'.$w->labelActive.'
				</label>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './admin/module/source', $w->buttonCancel, 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'add', $w->buttonAdd, 'button add' ).'
		</div>
	</fieldset>
</form>
';

$panelInfo	= $view->loadContentFile( 'html/admin/module/source/add.info.html' );

return '
<script>
var sourceId = "";
</script>
<div class="column-left-60">
	'.$panelAdd.'
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