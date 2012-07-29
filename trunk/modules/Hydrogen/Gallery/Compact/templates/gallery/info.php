<?php

$list	= array();
$mps	= round( $exif->get( 'COMPUTED.Width' ) * $exif->get( 'COMPUTED.Height' ) / 1024 / 1024, 1 );
$timestamp	= strtotime( $exif->get( 'DateTimeOriginal' ) );
$model		= preg_replace( '/^'.$exif->get( 'Make' ).' /', '', $exif->get( 'Model' ) );
$data	= array(
	'Kamera'			=> $exif->get( 'Make' ).' <b>'.$model.'</b>',
	'Belichtungszeit'	=> $this->calculateFraction( $exif->get( 'ExposureTime' ) ),
	'Blende'			=> eval( 'return '.$exif->get( 'FNumber' ).';' ),
	'ISO-Wert'			=> $exif->get( 'ISOSpeedRatings' ),
	'Größe'				=> $mps.' Megapixel',
	'Dimensionen'		=> $exif->get( 'COMPUTED.Width' ).' x '.$exif->get( 'COMPUTED.Height' ).' Pixel',
	'Dateigröße'		=> Alg_UnitFormater::formatBytes( $exif->get( 'FileSize' ) ),
//	'Dateiname'			=> $exif->get( 'FileName' ),
//	'Gallerie'			=> implode( ' / ', array_slice( explode( '/', $source ), 0, -1 ) ),
	'Datum/Zeit'		=> date( 'Y-m-d', $timestamp ).' '.date( 'H:i:s', $timestamp ),
);
foreach( $data as $label => $value )
	$list[]	= '<dt>'.$label.'</dt><dd>'.$value.'</dd>';
$list	= '<dl>'.join( $list ).'</dl>';

$navigation	= $this->buildStepNavigation( $source );
$feedUrl	= View_Helper_Gallery::getFeedUrl( $env );

$jsBase	= 'http://js.int1a.net/jquery/';
$jsBase	= 'http://localhost/lib/cmScripts/jquery/';


return '
<link rel="stylesheet" href="'.$jsBase.'cmImagnifier/0.1.css"/>
<script src="'.$jsBase.'cmImagnifier/0.1.js"></script>
<script>
$(document).ready(function(){
	$("img.zoomable").cmImagnifier({
		showRatio: true,
	});
	$("#button-download").bind("click",function(){
		var source = $(".gallery-image-info").data("original");
		document.location.href = "./gallery/download/"+source;
	});
	$("#button-wallpaper").bind("click",function(){
		var source = $(".gallery-image-info").data("original");
		$.ajax({
			url: "./background/set?source="+source,
			dataType: "json",
			success: function(response){
				Background.images = response.images;
				Background.change(response.id);
			}
		})
	});
});
</script>
<div id="gallery" class="gallery-image-info" data-original="'.$source.'">
	<div style="float: right"><a href="'.$feedUrl.'" class="link-feed">RSS Feed</a></div>
	'.$navigation.'<br/>
	<div class="column-left-66">
		<div style="width: 90%" class="image">
			<img src="'.$path.preg_replace( '/(\.\w+)$/', '.medium\\1', $source ).'" style="width: 100%" data-original="'.$path.$source.'" class="zoomable"/>
		</div>
		<div class="column-clear hint" style="font-size: 0.9em; padding-left: 1em">
			<b>Tipp:</b> Fahre mit der Maus über das Bild um in das Bild zu zoomen.
		</div>
	</div>
	<div class="column-left-33">
		<h4>Bild-Informationen</h4>
		'.$title.'
		<div>
			'.$list.'
			<div class="column-clear"></div>
		</div>
		<br/>
		<h4>Aktionen</h4>
		<div class="image-actions">
			<ul class="list-actions">
				<li><button type="button" class="button save download" id="button-download"><span>Datei speichern</span></button></li>
				<li><button type="button" class="button save" id="button-wallpaper"><span>Seite tapezieren</span></button>.</li>
			</ul>
		</div>
	</div>
	<div class="column-clear"></div>
	'.$text['info.bottom'].'
	'.$license.'
</div>';
?>
