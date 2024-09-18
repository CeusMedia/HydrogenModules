<?php

use CeusMedia\Common\ADT\URL;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var array<string,array<string,string>> $words */
/** @var Environment $env */
/** @var View_Info_Gallery $view */
/** @var string $indexMode */
/** @var string $baseUriPath */
/** @var object $gallery */
/** @var ?URL $referer */
/** @var ?object $prevGallery */
/** @var ?object $nextGallery */

$w			= (object) $words['view'];

$linkClass	= $env->getConfig()->get( 'module.ui_js_fancybox.auto.class' );
$helper		= new View_Helper_Info_Gallery_Images( $env );
$helper->setGallery( $gallery->galleryId );
$list		= $helper->render();

$description	= '';
if( strlen( trim( $gallery->description ) ) )
	$description	= HtmlTag::create( 'p', $view->renderContent( $gallery->description ) );

$linkNext	= '';
$linkPrev	= '';

$linkIndex	= HtmlTag::create( 'a', $w->buttonIndex, array(
	'href'	=> !empty( $referer ) ? $referer->get() : $baseUriPath,
	'class'	=> 'btn',
) );

if( $prevGallery ){
	$label		= HtmlTag::create( 'span', $w->buttonPrev, ['class' => 'muted'] );
	$linkPrev	= $label.HtmlTag::create( 'a', $prevGallery->title, array(
		'href'	=> View_Helper_Info_Gallery::getGalleryUrl( $prevGallery, $baseUriPath )
	) );
}
if( $nextGallery ){
	$label		= HtmlTag::create( 'span', $w->buttonNext, ['class' => 'muted'] );
	$linkNext	= $label.HtmlTag::create( 'a', $nextGallery->title, array(
		'href'	=> View_Helper_Info_Gallery::getGalleryUrl( $nextGallery, $baseUriPath )
	) );
}

extract( $view->populateTexts( ['view.top', 'view.bottom'], 'html/info/gallery/' ) );

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
