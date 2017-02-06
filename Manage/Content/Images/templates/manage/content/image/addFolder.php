<?php


$optFolder	= array( '.' => '' );
foreach( $folders as $folder )
	$optFolder[$folder]	= preg_replace( "/\.\/?/", "", $folder );
$optFolder	= UI_HTML_Elements::Options( $optFolder, $path );


$panelFolders	= $view->loadTemplateFile( 'manage/content/image/folders.php' );
$w				= (object) $words['addFolder'];

extract( $view->populateTexts( array( 'top', 'bottom', 'add.folder.right' ), 'html/manage/content/image/' ) );

return $textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFolders.'
	</div>
	<div class="span6">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				<div class="row-fluid">
					<div class="span12">
						<form action="./manage/content/image/addFolder?path='.$path.'" method="post">
							<div class="row-fluid">
								<div class="span12">
									<label for="input_name">'.$w->labelName.'</label>
									<input class="span12" type="text" name="name" id="input_name" value="" required="required"/>
								</div>
							</div>
							<div class="row-fluid">
								<div class="span12">
									<label for="input_folder">'.$w->labelFolder.'</label>
									<select class="span12" name="folder" id="folder">'.$optFolder.'</select>
								</div>
							</div>
							<div class="buttonbar">
								<a href="./manage/content/image?path='.$path.'" class="btn btn-small"><i class="icon-arrow-left"></i> '.$w->buttonCancel.'</a>
								<button type="submit" name="save" class="btn btn-small btn-primary"><i class="icon-ok icon-white"></i> '.$w->buttonSave.'</button>
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
