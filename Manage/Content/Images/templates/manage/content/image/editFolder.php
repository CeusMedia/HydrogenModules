<?php

$optFolder	= array( '.' => '' );
foreach( $folders as $folder )
	$optFolder[$folder]	= $folder;
$optFolder	= UI_HTML_Elements::Options( $optFolder, $folderPath );

$listFolders	= $view->listFolders( $path );

return '
<div class="row-fluid">
	<div class="span3">
		<h4>Ordner</h4>
		'.$listFolders.'
		<a href="./manage/content/image/addFolder?path='.$path.'" class="btn btn-info btn-small"><i class="icon-plus icon-white"></i> neuer Ordner</a>
	</div>
	<div class="span6">
		<h4><span class="muted">Ordner: </span>'.$path.'</h4>
		<div class="row-fluid">
			<div class="span11">
				<form action="./manage/content/image/editFolder?path='.$path.'" method="post">
					<div class="row-fluid">
						<div class="span4">
							<label for="input_name">Name</label>
							<input class="span12" type="text" name="name" id="input_name" value="'.$folderName.'"/>
						</div>
						<div class="span8">
							<label for="input_folder">in Ordner</label>
							<select class="span12" name="folder" id="folder">'.$optFolder.'</select>
						</div>
						<div class="buttonbar">
							<a href="./manage/content/image?path='.$path.'" class="btn btn-small"><i class="icon-arrow-left"></i> zurück</a>
							<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
							<button type="button" class="btn btn-small btn-danger" onclick="if(confirm(\'Wirklich?\'))document.location.href=\'./manage/content/image/removeFolder?path='.$path.'\';"><i class="icon-remove icon-white"></i> entfernen</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="span3">
		<h3>Anleitung</h3>
		<h4>Regeln für Ordnernamen</h4>
		<ul>
			<li>Keine Sonderzeichen und Umlaute.</li>
			<li>Keine Leerzeichen.</li>
		</ul>
		<br/>
		<div class="alert alert-error"><b>Achtung!</b></div>
		<p>
			Beim Umbennen oder Verschieben eines Ordners werden alle Bilder und Unterordner mit verschoben.
			Sollten Bilder aus diesem Ordner oder dessen Unterordner bereits verlinkt sein, muss deren Verlinkung korrigiert werden.
		</p>
	</div>
</div>
';
?>
