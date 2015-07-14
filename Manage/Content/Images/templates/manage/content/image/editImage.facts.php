<?php

$imageThumbnailUri	= $helperThumbnailer->get( $pathImages.$imagePath );

$panelFacts		= '
<div class="content-panel">
<!--	<h4>Informationen</h4>-->
	<h4><span class="muted">Bild: </span>'.$imageName.'</h4>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span8">
				<dl class="dl-horizontal" style="margin: 0px">
					<dt>Dateiname</dt>
					<dd><div class="autocut">'.$imageName.'</div></dd>
					<dt>Ordner</dt>
					<dd><div class="autocut">'.$imageFolder.'</div></dd>
					<dt>Datum</dt>
					<dd>'.date( 'd.m.Y', $imageFileTime ).' <small class="muted">'.date( 'H:i:s', $imageFileTime ).'</small></dd>
					<dt>Dateigröße</dt>
					<dd>'.Alg_UnitFormater::formatBytes( $imageFileSize ).'</dd>
					<dt>Dateityp <small class="muted">(MIME)</small></dt>
					<dd>'.$imageMimeType.'</dd>
					<dt>Bildgröße</dt>
					<dd>'.$imageMegaPixels.' <abbr title="Megapixel">MP</abbr> <small class="muted">('.$imageWidth.' x '.$imageHeight.' Pixel)</small></dd>
					<dt>URL</dt>
					<dd><small><a href="'.$imageUri.'" class="autocut">'.substr( $imageFolder, 2 ).$imageName.'</a></small></dd>
				</dl>
			</div>
			<div class="span4">
				<a href="./manage/content/image/view?path='.urlencode( $path ).'" class="fancybox-auto" target="_blank" title="'.htmlentities( $imageName, ENT_QUOTES, 'UTF-8' ).'">
					<img src="'.$imageThumbnailUri.'" class="thumbnail" title="'.htmlentities( $imageName, ENT_QUOTES, 'UTF-8' ).'"/>
				</a>
			</div>
		</div>
	</div>
</div>';

return $panelFacts;
?>
