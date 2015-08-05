<?php

$imageThumbnailUri	= $helperThumbnailer->get( $pathImages.$imagePath );

$panelFolders	= $view->loadTemplateFile( 'manage/content/image/folders.php' );
$panelFacts		= $view->loadTemplateFile( 'manage/content/image/editImage.facts.php' );
$panelMove		= $view->loadTemplateFile( 'manage/content/image/editImage.move.php' );
$panelScale		= $view->loadTemplateFile( 'manage/content/image/editImage.scale.php' );

extract( $view->populateTexts( array( 'top', 'bottom', 'edit.image.right' ), 'html/manage/content/image/' ) );

return $textTop.'
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
</div>
'.$textBottom;
?>
