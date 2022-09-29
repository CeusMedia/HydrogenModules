<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconUpload     = HtmlTag::create( 'i', '', ['class' => 'icon-folder-open icon-white'] );

$optFolder	= [];
foreach( $folders as $folder )
	$optFolder[$folder]	= $folder;
$optFolder	= HtmlElements::Options( $optFolder, $path );

$panelFolders   = $view->loadTemplateFile( 'manage/content/image/folders.php' );
$w				= (object) $words['addImage'];

extract( $view->populateTexts( ['top', 'bottom', 'add.image.right'], 'html/manage/content/image/' ) );

$helperUpload	= new View_Helper_Input_File( $env );
$helperUpload->setName( 'file' );
$helperUpload->setLabel( $iconUpload );
$helperUpload->setRequired( TRUE );

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
							'.$helperUpload->render().'
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
