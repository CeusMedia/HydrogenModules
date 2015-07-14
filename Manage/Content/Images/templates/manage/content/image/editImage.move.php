<?php

$optFolder	= array( '.' => "" );
foreach( $folders as $folder )
	$optFolder[$folder]	= $folder;
$optFolder	= UI_HTML_Elements::Options( $optFolder, dirname( $path ) );

$panelMove	= '
<div class="content-panel">
	<h4>Bild umbenennen oder verschieben</h4>
	<div class="content-panel-inner">
		<form action="./manage/content/image/editImage?path='.$imagePath.'" method="post">
			<div class="row-fluid">
				<div class="span12">
					<label for="input_filename">Dateiname</label>
					<input class="span11" type="text" name="filename" id="input_filename" value="'.htmlentities( $imageName, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					<label for="input_folderpath">Ordner</label>
					<select class="span11" name="folderpath" id="input_folderpath">'.$optFolder.'</select>
				</div>
			</div>
			<div class="buttonbar">
				<a class="btn btn-small" href="./manage/content/image?path='.dirname( $imagePath ).'"><i class="icon-arrow-left"></i> zurück</a>
				<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
				<button type="button" class="btn btn-small btn-danger" onclick="if(confirm(\'Wirklich?\'))document.location.href=\'./manage/content/image/removeImage?path='.$imagePath.'\';"><i class="icon-remove icon-white"></i> entfernen</a>
			</div>
		</form>
	</div>
</div>';

return $panelMove;
?>
