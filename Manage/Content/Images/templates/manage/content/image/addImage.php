<?php

$iconUpload     = UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-folder-open icon-white' ) );

$optFolder	= array();
foreach( $folders as $folder )
	$optFolder[$folder]	= $folder;
$optFolder	= UI_HTML_Elements::Options( $optFolder, $path );

$panelFolders   = $view->loadTemplateFile( 'manage/content/image/folders.php' );
$w				= (object) $words['addImage'];

extract( $view->populateTexts( array( 'top', 'bottom', 'add.image.right' ), 'html/manage/content/image/' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFolders.'
	</div>
	<div class="span6">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<form action="./manage/content/image/addImage" method="post" enctype="multipart/form-data">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_file">'.$w->labelFile.'</label>
							'.View_Helper_Input_File::render( 'file', $iconUpload, 'Datei auswählen...' ).'
						</div>
					</div>
					<div class="row-fluid">
						<div class="span12">
							<label for="input_folder">'.$w->labelFolder.'</label>
							<select class="span12" name="folder" id="input_folder">'.$optFolder.'</select>
						</div>
					</div>
					<div class="buttonbar">
						<a class="btn btn-small" href="./manage/content/image"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
						<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="row-fluid">
		<div class="span3">
			'.$textAddImageRight.'
		</div>
	</div>
</div>
'.$textBottom;
?>
