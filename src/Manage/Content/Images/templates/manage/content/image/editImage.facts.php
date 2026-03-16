<?php

use CeusMedia\Common\Alg\UnitFormater;

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

$w	= (object) $words['editImage.facts'];

$panelFacts		= '
<div class="content-panel">
	<h3>'.sprintf( $w->heading, $imageName ).'</h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span9">
				<dl class="dl-horizontal" style="margin: 0px">
					<dt>'.$w->labelFile.'</dt>
					<dd><div class="autocut">'.$imageName.'</div></dd>
					<dt>'.$w->labelFolder.'</dt>
					<dd><div class="autocut">'.$imageFolder.'</div></dd>
					<dt>'.$w->labelTimestamp.'</dt>
					<dd>'.date( 'd.m.Y', $imageFileTime ).' <small class="muted">'.date( 'H:i:s', $imageFileTime ).'</small></dd>
					<dt>'.$w->labelFileSize.'</dt>
					<dd>'.UnitFormater::formatBytes( $imageFileSize ).'</dd>
					<dt>'.$w->labelFileType.'</dt>
					<dd>'.$imageMimeType.'</dd>
					<dt>'.$w->labelImageSize.'</dt>
					<dd>'.$imageMegaPixels.' <abbr title="Megapixel">MP</abbr> <small class="muted">('.$imageWidth.' x '.$imageHeight.' Pixel)</small></dd>
					<dt>'.$w->labelUrl.'</dt>
					<dd><small><a href="'.$imageUri.'" class="autocut">'.substr( $imageFolder, 2 ).$imageName.'</a></small></dd>
				</dl>
			</div>
			<div class="span3">
				<a href="./manage/content/image/view/'.base64_encode( $imagePath ).'?.jpg" class="fancybox-auto" target="_blank" title="'.htmlentities( $imageName, ENT_QUOTES, 'UTF-8' ).'">
					<img src="'.$imageThumbnailUri.'" class="thumbnail span12" title="'.htmlentities( $imageName, ENT_QUOTES, 'UTF-8' ).'"/>
				</a>
			</div>
		</div>
	</div>
</div>';

return $panelFacts;
