<?php

$optFolder	= array( '.' => '' );
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
		<h4>Neuer Ordner</h4>
		<div class="row-fluid">
			<div class="span6">
				<form action="./manage/content/image/addFolder?path='.$path.'" method="post">
					<div class="row-fluid">
						<div class="span6">
							<label for="input_name">Name</label>
							<input class="span12" type="text" name="name" id="input_name" value=""/>
						</div>
						<div class="span6">
							<label for="input_folder">in Ordner</label>
							<select class="span12" name="folder" id="folder">'.$optFolder.'</select>
						</div>
						<div class="buttonbar">
							<a href="./manage/content/image?path='.$path.'" class="btn btn-small"><i class="icon-arrow-left"></i> zurück</a>
							<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
						</div>
					</div>
				</form>
			</div>
		</div>
	</div>
</div>
';
?>
