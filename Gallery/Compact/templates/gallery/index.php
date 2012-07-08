<?php

$list	= array();
foreach( $folders as $entry )
{
	$name			= $entry->getFilename();
	$url			= './gallery/index/'.$source.urlencode( $name );
	$link			= UI_HTML_Elements::Link( $url, $name );
	$list[$name]	= UI_HTML_Elements::ListItem( $link );
}
ksort( $list );
$folders	= $list ? UI_HTML_Elements::unorderedList( $list ) : NULL;

$list		= array();
foreach( $files as $file )
{
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
$navigation	= $this->buildStepNavigation( $source );
		
return '
<style>
#licence {
	clear: left;
	margin-top 1em;
	border-top: 1px solid gray;
	padding: 1em;
	font-size: 0.8em;
	}
</style>
<div id="gallery">
	'.$navigation.'<br/>
	'.$title.'
	'.$desc.'
	'.$folders.'
	<div style="clear: left"></div>
	'.$files.'
	<div style="clear: left"></div>
	'.$textBottom.'
	'.$license.'
	<div style="clear: left"></div>
</div>
';
?>