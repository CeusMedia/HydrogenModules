<?php
$feedUrl	= View_Helper_Gallery::getFeedUrl( $env );

$dateFormat	= $env->getConfig()->get( 'module.gallery_compact.format.date' );
$dateFormat	= 'j.n.Y';

$list	= array();
foreach( $folders as $entry ){
	$folderName	= $entry->getFilename();
	$link		= View_Helper_Gallery::renderGalleryLink( $env, $source.$folderName, 2, $dateFormat );
	$list[$folderName]	= UI_HTML_Elements::ListItem( $link );
}
krsort( $list );
$folders	= '';
if( $list ){
	$width	= 50;
	$lists	= array( $list );
	if( count( $list ) > 5 ){
		if( count( $list ) > 10 ){
			$width		= 33;
			$cut		= ceil( count( $list ) / 3 );
			$lists[0]	= array_slice( $list, 0, $cut );
			$lists[1]	= array_slice( $list, $cut, $cut );
			$lists[2]	= array_slice( $list, 2 * $cut );
		}
		else{
			$cut		= ceil( count( $list ) / 2 );
			$lists[0]	= array_slice( $list, 0, $cut );
			$lists[1]	= array_slice( $list, $cut );
		}
	}
	foreach( $lists as $list ){
		$list		= UI_HTML_Elements::unorderedList( $list, 0, array( 'class' => 'folders' ) );
		$folders	.= UI_HTML_Tag::create( 'div', $list, array( 'class' => 'column-left-'.$width ) );
	}
}

$list		= array();
foreach( $files as $file ){
	$fileName	= $file->getFilename();
	if( preg_match( '/\.(small|medium)\.(jpg|jpeg|jpe|png|gif)$/i', $fileName ) )
		continue;

	$data		= pathinfo( $fileName );
	$title		= !empty( $info[$data['filename']] ) ? $info[$data['filename']] : $fileName;
	
	$attributes	= array(
		'src'			=> $path.$source.$data['filename'].'.small.'.$data['extension'],
		'title'			=> htmlentities( utf8_decode( strip_tags( $title ) ) ),
		'class'			=> 'thumbnail',
	);
	$image		= UI_HTML_Tag::create( 'image', NULL, $attributes );
//	$img		= new UI_Image( $path.$source.$data['filename'].'.'.$data['extension'] );
//	$exif		= new UI_Image_Exif( $path.$source.$data['filename'].'.'.$data['extension'] );
//	print_m( $exif->getAll() );
	$attributes	= array(
		'href'			=> $path.$source.$data['filename'].'.medium.'.$data['extension'],
		'class'			=> 'no-thickbox layer-image',
		'rel'			=> 'gallery',
		'target'		=> '_blank',
		'title'			=> $title,
		'data-original'	=> $source.$data['filename'].'.'.$data['extension'],
//		'data-size-x'	=> $img->getWidth(),
//		'data-size-y'	=> $img->getHeight(),
	);
	$image		= UI_HTML_Tag::create( 'a', $image, $attributes );
	$actions	= $this->buildActions( $source.$fileName, TRUE );
	$actions	= "";
	$list[$fileName]		= UI_HTML_Tag::create( 'div', $image.$actions, array( 'class' => 'thumbnail' ) );
}
ksort( $list );
$files		= $list ? implode( "", $list ) : NULL;
		
$title		= !empty( $info['title'] ) ? UI_HTML_Tag::create( "h3", $info['title'] ) : NULL;
$desc		= !empty( $info['description'] ) ? UI_HTML_Tag::create( "p", $info['description'] ) : NULL;
$navigation	= View_Helper_Gallery::renderStepNavigation( $env, $source );
		
return '
<script>
$(document).ready(function(){
	var galleryItemInfoButton = $("#gallery-item-info-button");
	$("div.thumbnail").bind("mouseenter",function(){
		galleryItemInfoButton.unbind("click").bind("click",function(){
			var url = $(this).parent().children("a").data("original");
			document.location.href = "./gallery/info/"+url;
		});
		$(this).append(galleryItemInfoButton.show());
	}).bind("mouseleave",function(){
		galleryItemInfoButton.hide();
	});
});
</script>
<div id="gallery">
	<div id="gallery-item-info-button" title="Informationen und Zoom">
		<img src="http://img.int1a.net/famfamfam/silk/information.png"/>
	</div>
	<div style="float: right"><a href="'.$feedUrl.'" class="link-feed">RSS Feed</a></div>
	'.$navigation.'<br/>
	'.$title.'
	'.$desc.'
	'.$folders.'
	<div class="column-clear"></div>
	<br/>
	'.$files.'
	<div style="clear: left"></div>
	<br/>
	'.$textBottom.'
	'.$license.'
	<div style="clear: left"></div>
</div>
';
?>