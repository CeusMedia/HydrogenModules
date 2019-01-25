<?php

$imageAlignH	= array(
	Model_Workshop::IMAGE_ALIGN_H_AUTO		=> 'auto',
	Model_Workshop::IMAGE_ALIGN_H_LEFT		=> 'left',
	Model_Workshop::IMAGE_ALIGN_H_CENTER	=> 'center',
	Model_Workshop::IMAGE_ALIGN_H_RIGHT		=> 'right',
);
$imageAlignV	= array(
	Model_Workshop::IMAGE_ALIGN_V_AUTO		=> 'auto',
	Model_Workshop::IMAGE_ALIGN_V_TOP		=> 'top',
	Model_Workshop::IMAGE_ALIGN_V_CENTER	=> 'center',
	Model_Workshop::IMAGE_ALIGN_V_BOTTOM	=> 'bottom',
);

$heading	= UI_HTML_Tag::create( 'h3', $workshop->title );

$image		= '';
if( $workshop->image ){
	$image	= UI_HTML_Tag::create( 'div', '', array( 'class' => 'workshop-image' ), array(
		'url'		=> $pathImages.$workshop->image,
		'alignH'	=> $imageAlignH[$workshop->imageAlignH],
		'alignV'	=> $imageAlignV[$workshop->imageAlignV],
	) );
}
$facts		= UI_HTML_Tag::create( 'div', $workshop->description );
$panel	= UI_HTML_Tag::create( 'div', array( $image, $heading, $facts ), array( 'class' => 'workshop-view' ) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/info/workshop/view/' ) );

return $textTop.$panel.$textBottom;

