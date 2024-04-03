<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var \CeusMedia\HydrogenFramework\View $view */
/** @var array<string,array<string,string>> $words */
/** @var \CeusMedia\HydrogenFramework\Environment $env */
/** @var string $basePath */
/** @var string $sliderId */

$iconBack	= HtmlTag::create( 'i', '', ['class' => 'icon-arrow-left'] );

$helper		= new View_Helper_Image_Slider( $env );
$helper->setBasePath( $basePath );
$display	= $helper->render( $sliderId );

$buttonBack	= HtmlTag::create( 'a', $iconBack.'&nbsp;'.$words['demo']['buttonBack'], [
	'href'	=> './manage/image/slider/edit/'.$sliderId,
	'class'	=> 'btn btn-small',
] );

$code	= join( "\n", array(
	'$helper	= new View_Helper_Image_Slider( $env );',
	'$slider	= $helper->render( '.$sliderId.' );',
) );

extract( $view->populateTexts( ['top', 'bottom'], 'html/manage/image/slider' ) );

return $textTop.'
<h3>'.$words['demo']['heading'].'</h3>
'.$display.'
<br/>
<div class="row-fluid">
	<div class="span4">
		<h3>'.$words['demo']['labelHTML'].'</h3>
		<xmp class="html">[slider:'.$sliderId.']</xmp>
	</div>
	<div class="span8">
		<h3>'.$words['demo']['labelPHP'].'</h3>
		<xmp class="php">'.$code.'</xmp>
	</div>
</div>
<div class="buttonbar">
	'.$buttonBack.'
</div>
';
