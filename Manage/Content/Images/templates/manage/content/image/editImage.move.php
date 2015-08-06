<?php

$optFolder	= array( '.' => "" );
foreach( $folders as $folder )
	$optFolder[$folder]	= preg_replace( "/\.\/?/", "", $folder );
$optFolder	= UI_HTML_Elements::Options( $optFolder, $path );

$w	= (object) $words['editImage.move'];

$panelMove	= '
<div class="content-panel">
	<h4>'.$w->heading.'</h4>
	<div class="content-panel-inner">
		<form action="./manage/content/image/editImage/'.base64_encode( $imagePath ).'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_filename">'.$w->labelFile.'</label>
					<input class="span11" type="text" name="filename" id="input_filename" value="'.htmlentities( $imageName, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_folderpath">'.$w->labelFolder.'</label>
					<select class="span11" name="folderpath" id="input_folderpath">'.$optFolder.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<a class="btn btn-small" href="./manage/content/image"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
				<button type="submit" name="save" class="btn not-btn-small btn-primary"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
				<button type="button" class="btn btn-small btn-danger" onclick="if(confirm(\''.$w->buttonRemove_confirm.'\'))document.location.href=\'./manage/content/image/removeImage/'.base64_encode( $imagePath ).'\';"><i class="icon-remove icon-white"></i> '.$w->buttonRemove.'</a>
			</div>
		</form>
	</div>
</div>';

return $panelMove;
?>
