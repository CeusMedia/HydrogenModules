<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$feedUrl	= View_Helper_Gallery::getFeedUrl( $env );
function isMobile(){
	$userAgent	= getEnv( 'HTTP_USER_AGENT' );
	$patterns	= array(
		'iPhone'		=> "/(iPad|iPod|iPhone).* (([3]_[2-9](_| ))|([4-9]_[0-9](_| )))/",
		'Android'		=> "/(Android) ([0-9.]+).*Mobile/ ",
		'Backberry'		=> "/(BlackBerry).*Version\/([6-9])\./",
		'PlayBook'		=> "/(PlayBook).*OS ([1-9])\./",
		'Kindle'		=> "/(Kindle)\/([3-9])/",
		'KindleFire'	=> "/(Kindle Fire)/",
		'Windows'		=> "/(Windows Phone OS) ([7-9])/",
		'Opera'			=> "/(Opera)\/([0-9.]+) /"
	);
	foreach( $patterns as $pattern ){
		$matches	= [];
		if( preg_match_all( $pattern, $userAgent, $matches ) ){
			return (object) array(
				'browser'	=> $matches[1][0],
				'version'	=> isset( $matches[2][0] ) ? $matches[2][0] : NULL,
				'matches'	=> $matches
			);
		}
	}
}

$mobile	= isMobile();

$dateFormat	= $env->getConfig()->get( 'module.gallery_compact.format.date' );

$list	= [];
foreach( $folders as $entry ){
	$folderName	= $entry->getFilename();
	$link		= View_Helper_Gallery::renderGalleryLink( $env, $source.$folderName, 2, $dateFormat );
	$list[$folderName]	= HtmlElements::ListItem( $link );
}
krsort( $list );
$folders	= '';
if( $list ){
	$lists	= array( $list );
	$width	= 12;
	if( count( $list ) > 5 ){
/*		if( count( $list ) > 10 ){
			$width		= 33;
			$width		= 4;
			$cut		= ceil( count( $list ) / 3 );
			$lists[0]	= array_slice( $list, 0, $cut );
			$lists[1]	= array_slice( $list, $cut, $cut );
			$lists[2]	= array_slice( $list, 2 * $cut );
		}
		else{*/
			$width		= 6;
			$cut		= ceil( count( $list ) / 2 );
			$lists[0]	= array_slice( $list, 0, $cut );
			$lists[1]	= array_slice( $list, $cut );
//		}
	}
	foreach( $lists as $list ){
		$list		= HtmlElements::unorderedList( $list, 0, array( 'class' => 'folders' ) );
		$folders	.= HtmlTag::create( 'div', $list, array( 'class' => 'span'.$width ) );
	}
}

$list		= [];
foreach( $files as $file ){
	$fileName	= $file->getFilename();
	if( preg_match( '/\.(small|medium)\.(jpg|jpeg|jpe|png|gif)$/i', $fileName ) )
		continue;

	$data		= pathinfo( $fileName );
	$title		= !empty( $info[$data['filename']] ) ? $info[$data['filename']] : $fileName;
	
	$attributes	= array(
		'src'		=> $path.$source.$data['filename'].'.small.'.$data['extension'],
		'title'		=> htmlentities( utf8_decode( strip_tags( $title ) ) ),
		'class'		=> 'not-thumbnail',
	);
	$image		= HtmlTag::create( 'image', NULL, $attributes );
	if( $mobile )
		$attributes	= [
			'href'	=> './gallery/info/'.$source.$data['filename'].'.'.$data['extension'],
			'title'		=> $title,
		];
	else
		$attributes	= [
			'href'			=> $path.$source.$data['filename'].'.medium.'.$data['extension'],
			'data-fancybox'	=> '1',
			'class'			=> 'no-thickbox no-layer-image no-darkbox fancybox-auto',
			'rel'			=> 'gallery',
//			'target'		=> '_blank',
			'title'			=> $title,
			'data-original'	=> $source.$data['filename'].'.'.$data['extension'],
		];
	
	$image		= HtmlTag::create( 'a', $image, $attributes );
	$list[$fileName]		= HtmlTag::create( 'div', $image, array( 'class' => 'thumbnail' ) );
}
ksort( $list );
$files		= $list ? implode( "", $list ) : NULL;
		
$title		= !empty( $info['title'] ) ? HtmlTag::create( "h3", $info['title'] ) : NULL;
$desc		= !empty( $info['title'] ) ? View_Helper_ContentConverter::render( $env, $info['description'] ) : NULL;
$navigation	= View_Helper_Gallery::renderStepNavigation( $env, $source );

return '
<script>
$(document).ready(function(){
	Gallery.setupIndex('.( (integer) (bool) $mobile ).');
});
</script>
<div id="gallery">
	<div id="gallery-item-info-button" title="Informationen und Zoom">
		<b class="fa fa-info-circle fa-fw"></b>
<!--		<img src="https://cdn.ceusmedia.de/img/famfamfam/silk/information.png"/>-->
	</div>
	<div style="float: right"><a href="'.$feedUrl.'" class="not-link-feed"><b class="fa fa-rss fa-fw"></b>&nbsp;RSS Feed</a></div>
	'.$navigation.'<br/>
	'.$title.'
	<p>
		'.$desc.'
	</p>
	<div class="row-fluid">
		'.$folders.'
	</div>
	<br/>
	'.$files.'
	<div style="clear: left"></div>
	<br/>
	'.View_Helper_ContentConverter::render( $env, $textBottom ).'
	'.View_Helper_ContentConverter::render( $env, $license ).'
	<div style="clear: left"></div>
</div>
';
?>
