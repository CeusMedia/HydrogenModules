<?php

$optStatus	= array();
foreach( array_reverse( $words['states'], TRUE ) as $key => $value ){
	$attributes		= array(
		'value'		=> $key,
		'class'		=> 'project status'.$key,
		'selected'	=> ( $key == 0 ? 'selected' : NULL )
	);
	$optStatus[]	= UI_HTML_Tag::create( 'option', $value, $attributes );
}
$optStatus		= join( '', $optStatus );

$panelAdd		= '
<form name="" action="./manage/project/add" method="post">
	<fieldset>
		<legend class="icon add">'.$words['add']['legend'].'</legend>
		<ul class="input">
			<li>
				<label for="input_title" class="mandatory">'.$words['add']['labelTitle'].'</label><br/>
				<input type="text" name="title" id="input_title" class="max mandatory"/>
			</li>
			<li>
				<label for="input_description">'.$words['add']['labelDescription'].'</label><br/>
				<textarea name="description" id="input_description" class="max"></textarea>
			</li>
			<li class="column-left-20">
				<label for="input_status" class="mandatory">'.$words['add']['labelStatus'].'</label><br/>
				<select name="status" id="input_status" class="max">'.$optStatus.'</select>
			</li>
			<li class="column-left-80">
				<label for="input_url">'.$words['add']['labelUrl'].'</label><br/>
				<input type="text" name="url" id="input_url" class="max"/>
			</li>
		</ul>
		<div class="buttonbar">
			'.UI_HTML_Elements::LinkButton( './manage/project', $words['add']['buttonCancel'], 'button cancel' ).'
			'.UI_HTML_Elements::Button( 'save', $words['add']['buttonSave'], 'button add' ).'
		</div>
	</fieldset>
</form>
';

return '
<div class="column-left-75">
	'.$panelAdd.'
</div>
<div class="column-left-25">

</div>
<div class="column-clear"></div>
';
?>
