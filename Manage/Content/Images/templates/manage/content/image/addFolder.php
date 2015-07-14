<?php

$optFolder	= array( '.' => '' );
foreach( $folders as $folder )
	$optFolder[$folder]	= $folder;
$optFolder	= UI_HTML_Elements::Options( $optFolder, $path );

extract( $view->populateTexts( array( 'add.folder.right' ), 'html/manage/content/image/' ) );
$panelFolders	= $view->loadTemplateFile( 'manage/content/image/folders.php' );

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFolders.'
	</div>
	<div class="span6">
		<div class="content-panel">
			<h4>Neuer Ordner</h4>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span10">
						<form action="./manage/content/image/addFolder?path='.$path.'" method="post">
							<div class="row-fluid">
								<div class="span12">
									<label for="input_name">Name</label>
									<input class="span12" type="text" name="name" id="input_name" value=""/>
								</div>
							</div>
							<div class="row-fluid">
								<div class="span12">
									<label for="input_folder">in Ordner</label>
									<select class="span12" name="folder" id="folder">'.$optFolder.'</select>
								</div>
							</div>
							<div class="buttonbar">
								<a href="./manage/content/image?path='.$path.'" class="btn btn-small"><i class="icon-arrow-left"></i> zurück</a>
								<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="span3">
		'.$textAddFolderRight.'
	</div>
</div>
';
?>
