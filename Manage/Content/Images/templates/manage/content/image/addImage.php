<?php

$optFolder	= array();
foreach( $folders as $folder )
	$optFolder[$folder]	= $folder;
$optFolder	= UI_HTML_Elements::Options( $optFolder, $path );

$listFolders	= $view->listFolders( $path );

return '
<div class="row-fluid">
	<div class="span3">
		<h4>Ordner</h4>
		'.$listFolders.'
		<a href="./manage/content/image/addFolder?path='.$path.'" class="btn btn-info btn-small"><i class="icon-plus icon-white"></i> neuer Ordner</a>
	</div>
	<div class="span9">
		<h4>Neues Bild</h4> 
		<div class="row-fluid">
			<div class="span9">
				<div class="row-fluid">
					<div class="span7">
						<form action="./manage/content/image/addImage?path='.$path.'" method="post" enctype="multipart/form-data">
							<label for="input_file">lokale Datei</label>
							<input type="file" name="file" id="input_file"/>
							<label for="input_folder">Ordner</label>
							<select class="span11" name="folder" id="input_folder">'.$optFolder.'</select>
							<div class="buttonbar">
								<a class="btn btn-small" href="./manage/content/image?path='.$path.'"><i class="icon-arrow-left"></i> zurück</a>
								<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
							</div>
						</form>
					</div>
				</div>
			</div>
<!--			<div class="span3">
				<h4>Anleitung</h4>
				<p>
				</p>
				<div class="alert alert-error"><b>Achtung</b> falls Bild bereits verlinkt wurde!</div>
				<p>
					Wenn die Bilddatei umbenannt oder verschoben wird, kann das zu Fehler bei der Darstellung der Webseite führen.<br/>
						Bitte stelle sicher, dass alle Stellen, wo die Bilddatei verlinkt wurde, korrigiert werden.
						</p>
					</div>
				</div>
			</div>-->
		</div>
	</div>
</div>
';
?>
