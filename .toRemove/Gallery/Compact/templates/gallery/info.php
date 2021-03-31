<?php
$folderPath	= dirname( $source ).'/';
$imageName	= basename( $source );

//  --  NAVI CONTROL  --  //
$linkNext	= '';
$linkPrev	= '';
$images		= array();
foreach( $files as $file )
	if( !preg_match( '/\.(medium|small)\./', $file->getFilename() ) )
		$images[]	= $file->getFilename();
sort( $images );
$index		= array_search( $imageName, $images );
if( isset( $images[$index-1] ) )
	$linkPrev	= View_Helper_Gallery::renderImageLink( $env, $folderPath.$images[$index-1] );
if( isset( $images[$index+1] ) )
	$linkNext	= View_Helper_Gallery::renderImageLink( $env, $folderPath.$images[$index+1] );

$naviControl	= '
<div class="navi-control">
	<div style="float: left; width: 30%; text-align: left">
		'.( $linkPrev ? $linkPrev : '&nbsp;' ).'
	</div>
	<div style="float: left; width: 5%; text-align: center">
		'.( $linkPrev ? '&laquo;' : '&nbsp;' ).'
	</div>
	<div style="float: left; width: 30%; text-align: center">
		<b>'.View_Helper_Gallery::renderImageLabel( $env, $imageName ).'</b>
	</div>
	<div style="float: left; width: 5%; text-align: center">
		'.( $linkNext ? '&raquo;' : '&nbsp;' ).'
	</div>
	<div style="float: left; width: 30%; text-align: right">
		'.( $linkNext ? $linkNext : '&nbsp;' ).'
	</div>
	<div class="column-clear"></div>
</div>';

$navigation	= View_Helper_Gallery::renderStepNavigation( $env, $source );
$feedUrl	= View_Helper_Gallery::getFeedUrl( $env );

$options	= new ADT_List_Dictionary( $config->getAll( 'module.gallery_compact.info.' ) );

$useMagnifier	= $options->get( 'magnifier' ) && $env->getModules ()->has( 'JS_cmImagnifier' );
$useFullscreen	= $options->get( 'fullscreen' );
$useWallpaper	= $options->get( 'wallpaper' ) && $env->getModules()->has( 'UI_Background' );
$useDownload	= $options->get( 'download' );
$useExif		= $options->get( 'exif' );

//  --  VIEW MODE CONTROLS  --  //
$modes		= array();
$hints		= array();
if( $useFullscreen ){
	$label		= UI_HTML_Tag::create( 'span', "Vollbild" );
	$icon		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-arrows-alt fa-fw' ) ).'&nbsp;';
	$attr		= array( 'type' => "button", 'class' => "btn btn-small", 'id' => "button-fullscreen" );
	$modes['fullscreen']	= UI_HTML_Tag::create( 'button', $icon.$label, $attr );
	$hints['fullscreen']	= 'Klicke auf das Bild für die Vollbildanzeige. <b>Tipp:</b> Drücke vorher <kbd>F11</kbd>';
}
if( $useMagnifier ){
	$label		= UI_HTML_Tag::create( 'span', "Lupe" );
	$icon		= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-search fa-fw' ) ).'&nbsp;';
	$attr		= array( 'type' => "button", 'class' => "btn btn-small", 'id' => "button-magnifier" );
	$modes['magnifier']		= UI_HTML_Tag::create( 'button', $icon.$label, $attr );
	$hints['magnifier']		= '<b>Tipp:</b> Die Lupe ist aktiviert. Fahre mit der Maus über das Bild!';
}
$viewMode	= '';
if( $modes ){
	$group		= UI_HTML_Tag::create( 'div', $modes, array( 'class' => 'btn-group' ) );
	$viewMode	= UI_HTML_Tag::create( 'div', 'Modus: '.$group, array( 'class' => 'gallery-image-view-modes' ) ).'<br/>';
}
foreach( $hints as $key => $value )
	$hints[$key]	= UI_HTML_Tag::create( 'div', $value, array( 'id' => 'hint-'.$key, 'class' => 'alert alert-info alert-center' ) );
$hints	= UI_HTML_Tag::create( 'div', $hints, array( 'class' => 'gallery-image-view-mode-hints' ) );


//  --  ACTION CONTROLS  --  //
$buttons	= array();
if( 1 ){
	$icon	= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-arrow-left fa-fw' ) ).'&nbsp;';
	$label	= UI_HTML_Tag::create( 'span', $icon.'zur Galerieansicht' );
	$attr	= array( 'type' => "button", 'class' => "not-button not-cancel btn btn-small", 'id' => "button-gallery" );
	$buttons[$label]	= $attr;
}
if( $useDownload ){
	$icon	= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-download fa-fw' ) ).'&nbsp;';
	$label	= UI_HTML_Tag::create( 'span', $icon.'Download der Bilddatei' );
	$attr	= array( 'type' => "button", 'class' => "not-button not-save not-download btn btn-small", 'id' => "button-download" );
	$buttons[$label]	= $attr;
}
if( $useWallpaper ){
	$icon	= UI_HTML_Tag::create( 'b', '', array( 'class' => 'fa fa-heart fa-fw' ) ).'&nbsp;';
	$label	= UI_HTML_Tag::create( 'span', $icon."als Wallpaper verwenden" );
	$attr	= array( 'type' => "button", 'class' => "not-button not-save btn btn-small", 'id' => "button-wallpaper" );
	$buttons[$label]	= $attr;
}
$list	= array();
foreach( $buttons as $label => $attributes )
	$list[]	= UI_HTML_Tag::create( 'div', UI_HTML_Tag::create( 'button', $label, $attributes ) );
$buttons	= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'buttons list-actions' ) );

//  --  IMAGE DATA / EXIF  --  //
$listExif	= '';
if( $useExif ){
	$list	= array();
	$mps	= round( $exif->get( 'COMPUTED.Width' ) * $exif->get( 'COMPUTED.Height' ) / 1024 / 1024, 1 );
	$data	= array();
	if( strlen( $exif->get( 'Make' ) ) && strlen( $exif->get( 'Model' ) ) ){
		$model	= preg_replace( '/^'.$exif->get( 'Make' ).' /', '', $exif->get( 'Model' ) );
		$data['Kamera']			= $exif->get( 'Make' ).' <b>'.$model.'</b>';
	}
	if( strlen( $exif->get( 'ExposureTime' ) ) )
		$data['Belichtungszeit']	= View_Helper_Gallery::calculateFraction( $exif->get( 'ExposureTime' ), array( ' Sekunde', ' Sekunden' ) );
	if( strlen( $exif->get( 'FNumber' ) ) )
		$data['Blende']			= eval( 'return '.$exif->get( 'FNumber' ).';' );
	if( strlen( $exif->get( 'ISOSpeedRatings' ) ) )
		$data['Empfindlichkeit']	= 'ISO '.$exif->get( 'ISOSpeedRatings' );

	$data['Auflösung']	= $mps.' <acronym title="Megapixel">MP</acronym> <small><em>('.$exif->get( 'COMPUTED.Width' ).' x '.$exif->get( 'COMPUTED.Height' ).')</em></small>';
//	$data['Größe']		= $mps.' <acronym title="Megapixel">MP</acronym>';
//	$data['Dimensionen']	= $exif->get( 'COMPUTED.Width' ).' x '.$exif->get( 'COMPUTED.Height' ).' Pixel';
	$data['Dateigröße']	= Alg_UnitFormater::formatBytes( $exif->get( 'FileSize' ) );
//	$data['Dateiname']	= $exif->get( 'FileName' );
//	$data['Gallerie']	= implode( ' / ', array_slice( explode( '/', $source ), 0, -1 ) );
	if( strlen( $exif->get( 'DateTimeOriginal' ) ) ){
		$formatDate	= $config->get( 'module.gallery_compact.format.date' );
		$formatTime	= $config->get( 'module.gallery_compact.format.time' );
		$timestamp	= strtotime( $exif->get( 'DateTimeOriginal' ) );
		$data['Datum <small>& Zeit</small>']	= date( $formatDate, $timestamp ).' <small><em>'.date( $formatTime, $timestamp ).'</em></small>';
	}
	foreach( $data as $label => $value )
		$list[]	= '<dt>'.$label.'</dt><dd>'.$value.'</dd>';
	$listExif	= '
<h4>Bild-Informationen</h4>
<div>
	<dl>'.join( $list ).'</dl>
	<div class="column-clear" style="clear: both"></div>
</div>';
}

//  --  IMAGE VIEW  --  //
$class	= array();
if( $useMagnifier )
	$class[]	= 'zoomable';
if( $useFullscreen )
	$class[]	= 'fullscreenable';
$image	= UI_HTML_Tag::create( 'img', NULL, array(
	'class'			=> $class,
	'src'			=> $path.preg_replace( '/(\.\w+)$/', '.medium\\1', $source ),
	'data-original'	=> $path.$source,
	'style'			=> 'width: 100%'
) );

#if( !$title )
#	$title	= basename( $source );

return '
<script>
$(document).ready(function(){
	Gallery.setupInfo('.json_encode( array_keys( $modes ) ).');
});
</script>
<div id="gallery" class="gallery-image-info" data-original="'.$source.'">
	<div style="float: right"><a href="'.$feedUrl.'" class="not-link-feed"><b class="fa fa-rss fa-fw"></b>&nbsp;RSS Feed</a></div>
	'.$navigation.'
	<div class="row-fluid">
		<div class="span8">
			<div style="width: 95%">
				'.$naviControl.'
			</div>
			<div class="row-fluid">
				<div style="width: 90%" class="image">
					'.$image.'
					'.( $title ? UI_HTML_Tag::create( 'div',$title, array( 'class' => 'image-title' ) ) : '' ).'
				</div>
			</div>
			<div class="row-fluid">
				<div class="span12">
					'.$viewMode.'
					'.$hints.'
				</div>
			</div>
		</div>
		<div class="span4">
			<br/>
			<br/>
			'.$listExif.'
			<br/>
			<div class="image-actions">
				'.$buttons.'
			</div>
		</div>
		<div class="column-clear"></div>
	</div>
	<br/>
	<br/>
	'.View_Helper_ContentConverter::render( $env, $textInfoBottom ).'
	'.View_Helper_ContentConverter::render( $env, $license ).'
</div>';
?>
