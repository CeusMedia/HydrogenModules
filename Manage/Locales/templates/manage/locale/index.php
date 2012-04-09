<?php

$panelFilter	= $this->loadTemplate( 'manage/locale', 'filter' );
$panelList		= $this->loadTemplate( 'manage/locale', 'list' );

$optPath	= array_merge( array( '' ), $paths );
$optPath	= array_combine( $optPath, $optPath );
$optPath	= UI_HTML_Elements::Options( $optPath );

$w	= (object) $words['addFolder'];
$panelFolder	= '
<form action="./manage/locale/addFolder" method="post">
	<fieldset>
		<legend class="add">'.$w->legend.'</legend>
		<label for="input_folder_name">'.$w->labelName.'</label><br/>
		<input type="text" name="folder_name" id="input_folder_name" class="max"/>
		<label for="input_folder_path">'.$w->labelPath.'</label><br/>
		<select name="folder_path" id="input_folder_path" class="max">'.$optPath.'</select>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'add', $w->buttonAdd, 'button add' ).'
		</div>
	</fieldset>
</form>
';

$w	= (object) $words['add'];
$panelFile	= '
<form action="./manage/locale/add" method="post">
	<fieldset>
		<legend class="add">'.$w->legend.'</legend>
		<label for="input_file_name">'.$w->labelName.'</label><br/>
		<input type="text" name="file_name" id="input_file_name" class="max"/>
		<label for="input_file_path">'.$w->labelPath.'</label><br/>
		<select name="file_path" id="input_file_path" class="max">'.$optPath.'</select>
		<div class="buttonbar">
			'.UI_HTML_Elements::Button( 'add', $w->buttonAdd, 'button add' ).'
		</div>
	</fieldset>
</form>
';

return '
<div class="column-left-20">
	'.$panelFilter.'
	'.$panelList.'
</div>
<div class="column-left-80"">
	<div class="column-left-50">
		'.$panelFile.'
	</div>
	<div class="column-right-50">
		'.$panelFolder.'
	</div>
	<div class="column-clear"></div>
</div>
';
?>