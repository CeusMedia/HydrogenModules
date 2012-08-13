<?php
$folderPath	= dirname( $source ).'/';
$imageName	= basename( $source );

//  --  NAVI CONTROL  --  //
$images		= array();
foreach( $files as $file )
	if( !preg_match( '/\.(medium|small)\./', $file->getFilename() ) )
		$images[]	= $file->getFilename();
sort( $images );

//  --  NAVI CONTROL  --  //
$linkNext	= '';
$linkPrev	= '';
$index		= array_search( $imageName, $images );
$folderUri	= './gallery/info/'.str_replace( '%2F', '/', rawurlencode( $folderPath ) );
if( isset( $images[$index-1] ) ){
	$url		= $folderUri.rawurlencode( $images[$index-1] );
	$linkPrev	= UI_HTML_Elements::Link( $url, $images[$index-1], 'link-image' );
}
if( isset( $images[$index+1] ) ){
	$url		= $folderUri.rawurlencode( $images[$index+1] );
	$linkNext	= UI_HTML_Elements::Link( $url, $images[$index+1], 'link-image' );
}
$naviControl	= '
<div class="navi-control">
	<div style="float: left; width: 30%; text-align: left">
		'.( $linkPrev ? $linkPrev : '&nbsp;' ).'
	</div>
	<div style="float: left; width: 5%; text-align: center">
		'.( $linkPrev ? '&laquo;' : '&nbsp;' ).'
	</div>
	<div style="float: left; width: 30%; text-align: center">
		<b>'.$imageName.'</b>
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

$jsBase	= 'http://localhost/lib/cmScripts/jquery/';
$jsBase	= 'http://js.int1a.net/jquery/';

$options	= new ADT_List_Dictionary( $config->getAll( 'module.gallery_compact.info.' ) );

if( $options->get( 'magnifier' ) ){
	$label		= UI_HTML_Tag::create( 'span', "Lupe" );
	$attr		= array( 'type' => "button", 'class' => "button search", 'id' => "button-magnifier" );
	$buttonZoom	= UI_HTML_Tag::create( 'button', $label, $attr );
}
if( $options->get( 'fullscreen' ) ){
	$label		= UI_HTML_Tag::create( 'span', "Vollbild" );
	$attr		= array( 'type' => "button", 'class' => "button search resize-max", 'id' => "button-fullscreen" );
	$buttonFull	= UI_HTML_Tag::create( 'button', $label, $attr );
}
$viewMode	= '';
if( $options->get( 'magnifier' ) && $options->get( 'fullscreen' ) ){
	$viewMode	= '<div>Modus:'.$buttonZoom.''.$buttonFull.'</div><br/>';
}


$buttons	= array();
if( 1 ){
	$label	= UI_HTML_Tag::create( 'span', "zur Galerieansicht" );
	$attr	= array( 'type' => "button", 'class' => "button cancel", 'id' => "button-gallery" );
	$buttons[$label]	= $attr;
}
if( $options->get( 'download' ) ){
	$label	= UI_HTML_Tag::create( 'span', "Download der Bilddatei" );
	$attr	= array( 'type' => "button", 'class' => "button save download", 'id' => "button-download" );
	$buttons[$label]	= $attr;
}
if( $env->getModules()->has( 'UI_Background' ) && $options->get( 'wallpaper' ) ){
	$label	= UI_HTML_Tag::create( 'span', "als Wallpaper verwenden" );
	$attr	= array( 'type' => "button", 'class' => "button save", 'id' => "button-wallpaper" );
	$buttons[$label]	= $attr;
}
$list	= array();
foreach( $buttons as $label => $attributes )
	$list[]	= UI_HTML_Tag::create( 'li', UI_HTML_Tag::create( 'button', $label, $attributes ) );
$buttons	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'buttons list-actions' ) );


//  --  IMAGE DATA / EXIF  --  //
$listExif	= '';
if( $options->get( 'exif' ) ){
	$formatDate	= $config->get( 'module.gallery_compact.format.date' );
	$formatTime	= $config->get( 'module.gallery_compact.format.time' );
	$list	= array();
	$mps	= round( $exif->get( 'COMPUTED.Width' ) * $exif->get( 'COMPUTED.Height' ) / 1024 / 1024, 1 );
	$timestamp	= strtotime( $exif->get( 'DateTimeOriginal' ) );
	$model		= preg_replace( '/^'.$exif->get( 'Make' ).' /', '', $exif->get( 'Model' ) );
	$data	= array(
		'Kamera'			=> $exif->get( 'Make' ).' <b>'.$model.'</b>',
		'Belichtungszeit'	=> View_Helper_Gallery::calculateFraction( $exif->get( 'ExposureTime' ), array( ' Sekunde', ' Sekunden' ) ),
		'Blende'			=> eval( 'return '.$exif->get( 'FNumber' ).';' ),
		'Empfindlichkeit'	=> 'ISO '.$exif->get( 'ISOSpeedRatings' ),
		'Auflösung'			=> $mps.' <acronym title="Megapixel">MP</acronym> <small><em>('.$exif->get( 'COMPUTED.Width' ).' x '.$exif->get( 'COMPUTED.Height' ).')</em></small>',
	//	'Größe'				=> $mps.' <acronym title="Megapixel">MP</acronym>',
	//	'Dimensionen'		=> $exif->get( 'COMPUTED.Width' ).' x '.$exif->get( 'COMPUTED.Height' ).' Pixel',
		'Dateigröße'		=> Alg_UnitFormater::formatBytes( $exif->get( 'FileSize' ) ),
	//	'Dateiname'			=> $exif->get( 'FileName' ),
	//	'Gallerie'			=> implode( ' / ', array_slice( explode( '/', $source ), 0, -1 ) ),
		'Datum/Zeit'		=> date( $formatDate, $timestamp ).' <small><em>'.date( $formatTime, $timestamp ).'</em></small>',
	);
	foreach( $data as $label => $value )
		$list[]	= '<dt>'.$label.'</dt><dd>'.$value.'</dd>';
	$listExif	= '
<h4>Bild-Informationen</h4>
<div>
	<dl>'.join( $list ).'</dl>
	<div class="column-clear"></div>
</div>';
}

$class	= array();
if( $options->get( 'magnifier' ) )
	$class[]	= 'zoomable';
if( $options->get( 'fullscreen' ) )
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
<link rel="stylesheet" href="'.$jsBase.'cmImagnifier/0.1.css"/>
<script src="'.$jsBase.'cmImagnifier/0.1.js"></script>
<script>
$(document).ready(function(){
	Gallery.setupInfo();
	if("'.$options->get( 'fullscreen' ).'")
		$("#hint-fullscreen").show();
});
</script>
<div id="gallery" class="gallery-image-info" data-original="'.$source.'">
	<div style="float: right"><a href="'.$feedUrl.'" class="link-feed">RSS Feed</a></div>
	'.$navigation.'
	<div class="column-left-66">
		<div style="width: 94%; margin-left: 1%">
			'.$naviControl.'
		</div>
		<div style="width: 90%" class="image">
			'.$image.'
			'.( $title ? UI_HTML_Tag::create( 'div',$title, array( 'class' => 'image-title' ) ) : '' ).'
		</div>
		<div id="hint-magnifier" class="column-clear hint">
			<b>Tipp:</b> Die Lupe ist aktiviert. Fahre mit der Maus über das Bild!
		</div>
		<div id="hint-fullscreen" class="column-clear hint">
			Klicke auf das Bild für die Vollbildanzeige. <b>Tipp:</b> Drücke vorher <kbd>F11</kbd>
		</div>
	</div>
	<div class="column-left-33">
		<br/>
		<br/>
		'.$listExif.'
		<br/>
		<div class="image-actions">
			'.$viewMode.'
			'.$buttons.'
		</div>
	</div>
	<div class="column-clear"></div>
	<br/>
	<br/>
	'.View_Helper_ContentConverter::render( $env, $text['info.bottom'] ).'
	'.View_Helper_ContentConverter::render( $env, $license ).'
</div>';
?>
