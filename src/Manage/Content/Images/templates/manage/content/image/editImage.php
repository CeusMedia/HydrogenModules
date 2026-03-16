<?php

/** @var View_Manage_Content_Image $view */
/** @var View_Helper_Thumbnailer $helperThumbnailer */
/** @var array $words */

/** @var Logic_Frontend $frontend */
/** @var string $pathImages */
/** @var string $imageFolder */
/** @var string $imagePath */
/** @var string $imageName */
/** @var int $imageWidth */
/** @var int $imageHeight */
/** @var string $imageName */
/** @var string $imageUri */
/** @var string $imageMimeType */
/** @var int $imageFileSize */
/** @var int $imageFileTime */
/** @var float $imageMegaPixels */

/** @var bool $canAddFolder */
/** @var bool $canAddImage */
/** @var bool $canEditFolder */
/** @var bool $canEditImage */
/** @var bool $canProcess */
/** @var bool $canRemoveFolder */
/** @var bool $canRemoveImage */
/** @var bool $canScale */

$imageThumbnailUri	= $helperThumbnailer->get( $pathImages.$imagePath );

$panelFolders	= $view->loadTemplateFile( 'manage/content/image/folders.php' );
$panelFacts		= $view->loadTemplateFile( 'manage/content/image/editImage.facts.php' );
$panelMove		= $canEditFolder ? $view->loadTemplateFile( 'manage/content/image/editImage.move.php' ) : '';
$panelScale		= $canScale ? $view->loadTemplateFile( 'manage/content/image/editImage.scale.php' ) : '';
$panelProcess	= $canProcess ? $view->loadTemplateFile( 'manage/content/image/editImage.process.php' ) : '';

extract( $view->populateTexts( ['top', 'bottom', 'edit.image.right'], 'html/manage/content/image/' ) );

if( !( $canEditFolder && $canScale && $canProcess ) )
	$textEditImageRight	= '';

return $textTop.'
<div class="row-fluid">
	<div class="span3">
		'.$panelFolders.'
	</div>
	<div class="span9">
		'.$panelFacts.'
		<hr/>
		<div class="row-fluid">
			<div class="span7">
				'.$panelMove.'
				'.$panelProcess.'
				'.$panelScale.'
			</div>
			<div class="span5">
				'.$textEditImageRight.'
			</div>
		</div>
	</div>
</div>
'.$textBottom;
