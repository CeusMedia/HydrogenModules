<?php

$imageThumbnailUri	= $helperThumbnailer->get( $pathImages.$imagePath );

extract( $view->populateTexts( array( 'edit.image.right' ), 'html/manage/content/image/' ) );

$panelFolders	= $view->loadTemplateFile( 'manage/content/image/folders.php' );
$panelFacts		= $view->loadTemplateFile( 'manage/content/image/editImage.facts.php' );
$panelMove		= $view->loadTemplateFile( 'manage/content/image/editImage.move.php' );
$panelScale		= $view->loadTemplateFile( 'manage/content/image/editImage.scale.php' );

return '
<div class="row-fluid">
	<div class="span3">
		'.$panelFolders.'
	</div>
	<div class="span7">
		'.$panelFacts.'
		<br/>
		<div class="row-fluid">
			<div class="span7">
				'.$panelMove.'
			</div>
			<div class="span5">
				'.$panelScale.'
			</div>
		</div>
	</div>
	<div class="span2">
		'.$textEditImageRight.'
	</div>
</div>';
?>
