<?php

$iconUpload     = UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-folder-open icon-white' ) );

$optFolder	= array();
foreach( $folders as $folder )
	$optFolder[$folder]	= $folder;
$optFolder	= UI_HTML_Elements::Options( $optFolder, $path );

$panelFolders   = $view->loadTemplateFile( 'manage/content/image/folders.php' );

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFolders.'
	</div>
	<div class="span9">
		<div class="content-panel">
			<h4>Neues Bild</h4>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span6">
						<form action="./manage/content/image/addImage?path='.$path.'" method="post" enctype="multipart/form-data">
							<div class="row-fluid">
								<div class="span12">
									<label for="input_file">lokale Datei</label>
									'.View_Helper_Input_File::render( 'file', $iconUpload, 'Datei auswählen...' ).'
								</div>
							</div>
							<div class="row-fluid">
								<div class="span12">
									<label for="input_folder">Ordner</label>
									<select class="span12" name="folder" id="input_folder">'.$optFolder.'</select>
								</div>
							</div>
							<div class="buttonbar">
								<a class="btn btn-small" href="./manage/content/image?path='.$path.'"><i class="icon-arrow-left"></i> zurück</a>
								<button type="submit" name="save" class="btn btn-small btn-success"><i class="icon-ok icon-white"></i> speichern</button>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
';
?>
