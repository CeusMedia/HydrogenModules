<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

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

$heading		= HtmlTag::create( 'h3', $workshop->title );
$buttonCancel	= HtmlTag::create( 'a', 'zur Übersicht', array( 'href' => './info/workshop', 'class' => 'btn' ) );

$image		= '';
if( $workshop->image ){
	$image	= HtmlTag::create( 'div', '', array( 'class' => 'workshop-image' ), array(
		'url'		=> $pathImages.$workshop->image,
		'alignH'	=> $imageAlignH[$workshop->imageAlignH],
		'alignV'	=> $imageAlignV[$workshop->imageAlignV],
	) );
}
$facts		= HtmlTag::create( 'div', $workshop->description ).'<br/>';
$panel		= HtmlTag::create( 'div', array(
	$heading,
	$image,
	$facts,
	HtmlTag::create( 'div', array(
		$buttonCancel
	), array( 'class' => 'buttonbar' ) ),
), array( 'class' => 'workshop-view' ) );

extract( $view->populateTexts( array( 'top', 'bottom' ), 'html/info/workshop/view/' ) );

return $textTop.$panel.$textBottom;

