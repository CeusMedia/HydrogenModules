<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var View_Info_Workshop $view */
/** @var object $workshop */
/** @var string $pathImages */

$imageAlignH	= [
	Model_Workshop::IMAGE_ALIGN_H_AUTO		=> 'auto',
	Model_Workshop::IMAGE_ALIGN_H_LEFT		=> 'left',
	Model_Workshop::IMAGE_ALIGN_H_CENTER	=> 'center',
	Model_Workshop::IMAGE_ALIGN_H_RIGHT		=> 'right',
];
$imageAlignV	= [
	Model_Workshop::IMAGE_ALIGN_V_AUTO		=> 'auto',
	Model_Workshop::IMAGE_ALIGN_V_TOP		=> 'top',
	Model_Workshop::IMAGE_ALIGN_V_CENTER	=> 'center',
	Model_Workshop::IMAGE_ALIGN_V_BOTTOM	=> 'bottom',
];

$heading		= HtmlTag::create( 'h3', $workshop->title );
$buttonCancel	= HtmlTag::create( 'a', 'zur Übersicht', ['href' => './info/workshop', 'class' => 'btn'] );

$image		= '';
if( $workshop->image ){
	$image	= HtmlTag::create( 'div', '', ['class' => 'workshop-image'], [
		'url'		=> $pathImages.$workshop->image,
		'alignH'	=> $imageAlignH[$workshop->imageAlignH],
		'alignV'	=> $imageAlignV[$workshop->imageAlignV],
	] );
}
$facts		= HtmlTag::create( 'div', $workshop->description ).'<br/>';
$panel		= HtmlTag::create( 'div', array(
	$heading,
	$image,
	$facts,
	HtmlTag::create( 'div', [
		$buttonCancel
	], ['class' => 'buttonbar'] ),
), ['class' => 'workshop-view'] );

extract( $view->populateTexts( ['top', 'bottom'], 'html/info/workshop/view/' ) );

return $textTop.$panel.$textBottom;

