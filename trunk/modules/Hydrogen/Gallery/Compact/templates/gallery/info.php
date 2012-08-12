<?php
$folderPath	= dirname( $source ).'/';
$imageName	= basename( $source );

//  --  NAVI CONTROL  --  //
$images		= array();
foreach( $files as $file )
	if( !preg_match( '/\.(medium|small)\./', $file->getFilename() ) )
		$images[]	= $file->getFilename();
sort( $images );


$linkNext	= '';
$linkPrev	= '';
$index		= array_search( $imageName, $images );
if( isset( $images[$index-1] ) ){
	$url		= './gallery/info/'.rawurlencode( $folderPath.$images[$index-1] );
	$linkPrev	= UI_HTML_Elements::Link( $url, $images[$index-1], 'link-image' );
}
if( isset( $images[$index+1] ) ){
	$url		= './gallery/info/'.rawurlencode( $folderPath.$images[$index+1] );
	$linkNext	= UI_HTML_Elements::Link( $url, $images[$index+1], 'link-image' );
}
$naviControl	= '
	<div class="navi-control">
		<div class="column-left-20" style="text-align: left">
			'.( $linkPrev ? $linkPrev : '&nbsp;' ).'
		</div>
		<div class="column-left-10" style="text-align: right">
			'.( $linkPrev ? '&laquo;' : '&nbsp;' ).'
		</div>
		<div class="column-left-40" style="text-align: center">
			<b>'.$imageName.'</b>
		</div>
		<div class="column-left-10" style="text-align: left">
			'.( $linkNext ? '&raquo;' : '&nbsp;' ).'
		</div>
		<div class="column-right-20" style="text-align: right">
			'.( $linkNext ? $linkNext : '&nbsp;' ).'
		</div>
		<div class="column-clear"></div>
	</div>';


$navigation	= View_Helper_Gallery::renderStepNavigation( $env, $source );
$feedUrl	= View_Helper_Gallery::getFeedUrl( $env );

$jsBase	= 'http://localhost/lib/cmScripts/jquery/';
$jsBase	= 'http://js.int1a.net/jquery/';

if( 1 ){
	$label	= UI_HTML_Tag::create( 'span', "Lupe" );
	$attr	= array( 'type' => "button", 'class' => "button search", 'id' => "button-magnifier" );
	$buttonZoom	= UI_HTML_Tag::create( 'button', $label, $attr );
}
if( 1 ){
	$label	= UI_HTML_Tag::create( 'span', "Vollbild" );
	$attr	= array( 'type' => "button", 'class' => "button search resize-max", 'id' => "button-fullscreen" );
	$buttonFull	= UI_HTML_Tag::create( 'button', $label, $attr );
}


$buttons	= array();
if( 1 ){
	$label	= UI_HTML_Tag::create( 'span', "zur Galerieansicht" );
	$attr	= array( 'type' => "button", 'class' => "button cancel", 'id' => "button-gallery" );
	$buttons[$label]	= $attr;
}
if( 1 ){
	$label	= UI_HTML_Tag::create( 'span', "Download der Bilddatei" );
	$attr	= array( 'type' => "button", 'class' => "button save download", 'id' => "button-download" );
	$buttons[$label]	= $attr;
}
if( $env->getModules()->has( 'UI_Background' ) ){
	$label	= UI_HTML_Tag::create( 'span', "als Wallpaper verwenden" );
	$attr	= array( 'type' => "button", 'class' => "button save", 'id' => "button-wallpaper" );
	$buttons[$label]	= $attr;
}
$list	= array();
foreach( $buttons as $label => $attributes )
	$list[]	= UI_HTML_Tag::create( 'li', UI_HTML_Tag::create( 'button', $label, $attributes ) );
$buttons	= UI_HTML_Tag::create( 'ul', $list, array( 'class' => 'buttons list-actions' ) );


//  --  IMAGE DATA / EXIF  --  //
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
	'Datum/Zeit'		=> date( 'Y-m-d', $timestamp ).' '.date( 'H:i:s', $timestamp ),
);
foreach( $data as $label => $value )
	$list[]	= '<dt>'.$label.'</dt><dd>'.$value.'</dd>';
$list	= '<dl>'.join( $list ).'</dl>';

return '
<link rel="stylesheet" href="'.$jsBase.'cmImagnifier/0.1.css"/>
<script src="'.$jsBase.'cmImagnifier/0.1.js"></script>
<script>
$(document).ready(function(){
	$("img.zoomable").cmImagnifier({
		autoEnable: false,
		showRatio: true,
	});
	$("#button-download").bind("click",function(){
		var source = $(".gallery-image-info").data("original");
		document.location.href = "./gallery/download/"+source;
	});
	$("#button-gallery").bind("click",function(){
		var path	= $("#gallery").data("original").split(/\//).slice(0, -1).join("/");
		document.location.href = "./gallery/index/"+encodeURI(path);
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
	$("#button-fullscreen").bind("click",function(){
		$("img.zoomable").addClass("fullscreenable").cmImagnifier("toggle");
		$("#button-magnifier").removeAttr("disabled");
		$("#hint-magnifier").hide();
		$("#hint-fullscreen").fadeIn(200);
		$(this).attr("disabled","disabled");
	}).attr("disabled","disabled");
	$("#button-magnifier").bind("click",function(){
		$("#hint-fullscreen").hide();
		$("#hint-magnifier").fadeIn(200);
		$("img.zoomable").cmImagnifier("toggle");
		$(this).attr("disabled","disabled");
		$("img.fullscreenable").removeClass("fullscreenable");
		$("#button-fullscreen").removeAttr("disabled");
	});
	$("img.fullscreenable").live("click",function(){
		var source = $(".gallery-image-info").data("original");
		$(this).addClass("loading");
		var layer = $("<div></div>").prependTo($("body"));
		layer.attr("id","gallery-image-fullscreen");
		layer.bind("click",function(){
			$(this).fadeOut(200,function(){
				$(this).remove();
			});
		});
		var image =  $("<img/>").appendTo(layer);
		image.bind("load",function(){
			$("#button-fullscreen").removeClass("loading");
			var ratioBody = $("body").width() / $("body").height();
			var ratioImage = $(this).get(0).width / $(this).get(0).height;
			$(this).css((ratioBody > ratioImage ? "width" : "height"), "100%");
			$(this).parent().fadeIn(300,function(){});
		});
		image.attr("src","images/gallery/"+source);
	});
});
</script>
<div id="gallery" class="gallery-image-info" data-original="'.$source.'">
	<div style="float: right"><a href="'.$feedUrl.'" class="link-feed">RSS Feed</a></div>
	'.$navigation.'
	'.$naviControl.'
	<br/>
	<div class="column-left-66">
		<div style="width: 90%" class="image">
			<img src="'.$path.preg_replace( '/(\.\w+)$/', '.medium\\1', $source ).'" style="width: 100%" data-original="'.$path.$source.'" class="zoomable fullscreenable"/>
		</div>
		<div id="hint-magnifier" class="column-clear hint" style="font-size: 0.9em; padding-left: 1em">
			<b>Tipp:</b> Die Lupe ist aktiviert. Fahre mit der Maus über das Bild!
		</div>
		<div id="hint-fullscreen" class="column-clear hint" style="font-size: 0.9em; padding-left: 1em">
			Klicke auf das Bild für die Vollbildanzeige. <b>Tipp:</b> Drücke vorher <kbd>F11</kbd>
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
		<div class="image-actions">
			<div>Klick-Modus:'.$buttonZoom.''.$buttonFull.'</div>
			<br/>
			'.$buttons.'
		</div>
	</div>
	<div class="column-clear"></div>
	<br/>
	<br/>
	'.$text['info.bottom'].'
	'.$license.'
</div>';
?>
