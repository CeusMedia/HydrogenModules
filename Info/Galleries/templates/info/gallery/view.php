<?php

$w			= (object) $words['view'];

$linkClass	= $env->getConfig()->get( 'module.ui_js_fancybox.auto.class' );
$helper		= new View_Helper_Info_Gallery_Images( $env );
$helper->setGallery( $gallery->galleryId );
$list		= $helper->render();

$description	= '';;
if( strlen( trim( $gallery->description ) ) )
	$description	= UI_HTML_Tag::create( 'p', $view->renderContent( $gallery->description ) );

$linkNext	= '';
$linkPrev	= '';

$linkIndex	= UI_HTML_Tag::create( 'a', $w->buttonIndex, array(
	'href'	=> !empty( $referer ) ? $referer->get() : $baseUriPath,
	'class'	=> 'btn',
) );

if( $prevGallery ){
	$label		= UI_HTML_Tag::create( 'span', $w->buttonPrev, array( 'class' => 'muted' ) );
	$linkPrev	= $label.UI_HTML_Tag::create( 'a', $prevGallery->title, array(
		'href'	=> View_Helper_Info_Gallery::getGalleryUrl( $prevGallery, $baseUriPath )
	) );
}
if( $nextGallery ){
	$label		= UI_HTML_Tag::create( 'span', $w->buttonNext, array( 'class' => 'muted' ) );
	$linkNext	= $label.UI_HTML_Tag::create( 'a', $nextGallery->title, array(
		'href'	=> View_Helper_Info_Gallery::getGalleryUrl( $nextGallery, $baseUriPath )
	) );
}

extract( $view->populateTexts( array( 'view.top', 'view.bottom' ), 'html/info/gallery/' ) );

return $textViewTop.'
<div>
	<h3>'.$gallery->title.'</h3>
	'.$description.'<br/>
	'.$list.'
<!--	<hr/>
	<p>'.$linkPrev.'</p>
	<p>'.$linkNext.'</p>-->
	<div style="text-align: center">
		<br/>
		'.$linkIndex.'
	</div>
	<br/>
</div>
'.$textViewBottom;