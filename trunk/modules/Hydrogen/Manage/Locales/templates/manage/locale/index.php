<?php

$panelFilter	= $this->loadTemplate( 'manage/locale', 'filter' );
$panelList		= $this->loadTemplate( 'manage/locale', 'list' );

$optPath	= array_merge( array( '' ), $paths );
$optPath	= array_combine( $optPath, $optPath );
$optPath	= UI_HTML_Elements::Options( $optPath );

$w	= (object) $words['addFolder'];
$panelFolder	= '
<form action="./manage/locale/addFolder" method="post">
	<h3>'.$w->legend.'</h3>
	<div class="row-fluid">
		<label for="input_folder_name">'.$w->labelName.'</label>
		<input type="text" name="folder_name" id="input_folder_name" class="max span11"/>
	</div>
	<div class="row-fluid">
		<label for="input_folder_path">'.$w->labelPath.'</label>
		<select name="folder_path" id="input_folder_path" class="max span11">'.$optPath.'</select>
	</div>
	<div class="buttonbar">
		'.UI_HTML_Elements::Button( 'add', '<i class="icon-plus icon-white"></i> '.$w->buttonAdd, 'button add btn btn-success' ).'
	</div>
</form>
';

$w	= (object) $words['add'];
$panelFile	= '
<form action="./manage/locale/add" method="post">
	<h3>'.$w->legend.'</h3>
	<div class="row-fluid">
		<label for="input_file_name">'.$w->labelName.'</label>
		<input type="text" name="file_name" id="input_file_name" class="max span11"/>
	</div>
	<div class="row-fluid">
		<label for="input_file_path">'.$w->labelPath.'</label>
		<select name="file_path" id="input_file_path" class="max span11">'.$optPath.'</select>
	</div>
	<div class="buttonbar">
		'.UI_HTML_Elements::Button( 'add', '<i class="icon-plus icon-white"></i> '.$w->buttonAdd, 'button add btn btn-success' ).'
	</div>
</form>
';

return '
<div class="row-fluid">
	<div class="span4">
		'.$panelFilter.'
		'.$panelList.'
	</div>
	<div class="span4">
		'.$panelFile.'
	</div>
	<div class="span4">
		'.$panelFolder.'
	</div>
</div>
';
?>
