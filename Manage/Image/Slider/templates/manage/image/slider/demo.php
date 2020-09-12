<?php

$iconBack	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'icon-arrow-left' ) );

$helper		= new View_Helper_Image_Slider( $env );
$helper->setBasePath( $basePath );
$display	= $helper->render( $sliderId );

$buttonBack	= UI_HTML_Tag::create( 'a', $iconBack.'&nbsp;'.$words['demo']['buttonBack'], array(
	'href'	=> './manage/image/slider/edit/'.$sliderId,
	'class'	=> 'btn btn-small',
) );

$code	= join( "\n", array(
	'$helper	= new View_Helper_Image_Slider( $env );',
	'$slider	= $helper->render( '.$sliderId.' );',
) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/manage/image/slider' ) );

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
