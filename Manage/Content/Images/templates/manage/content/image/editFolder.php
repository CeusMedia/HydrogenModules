<?php
$panelFolders	= $view->loadTemplateFile( 'manage/content/image/folders.php' );

$w				= (object) $words['editFolder'];

$optFolder	= array( '.' => '' );
foreach( $folders as $folder )
	if( $folder !== $path )
		$optFolder[$folder]	= preg_replace( "/\.\/?/", "", $folder );
$optFolder	= UI_HTML_Elements::Options( $optFolder, $folderPath );

extract( $view->populateTexts( array( 'top', 'bottom', 'edit.folder.right' ), 'html/manage/content/image/' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFolders.'
	</div>
	<div class="span6">
		<div class="content-panel">
			<h3>'.sprintf( $w->heading, $path ).'</h3>
			<div class="content-panel-inner">
				<form action="./manage/content/image/editFolder" method="post">
					<div class="row-fluid">
						<div class="span6">
							<label for="input_folder">'.$w->labelFolder.'</label>
							<select class="span12" name="folder" id="input_folder">'.$optFolder.'</select>
						</div>
						<div class="span6">
							<label for="input_name">'.$w->labelName.'</label>
							<input class="span12" type="text" name="name" id="input_name" value="'.$folderName.'" required="required"/>
						</div>
					</div>
					<div class="buttonbar">
						<a href="./manage/content/image" class="btn btn-small"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
						<button type="submit" name="save" class="btn btn-primary"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
						<button type="button" class="btn btn-small btn-danger" onclick="if(confirm(\''.$w->buttonRemove_confirm.'\'))document.location.href=\'./manage/content/image/removeFolder\';"><i class="icon-remove icon-white"></i> '.$w->buttonRemove.'</button>
					</div>
				</form>
			</div>
		</div>
	</div>
	<div class="span3">
		'.$textEditFolderRight.'
	</div>
</div>
'.$textBottom;
?>
