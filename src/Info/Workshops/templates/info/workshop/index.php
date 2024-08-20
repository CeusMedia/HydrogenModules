<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

/** @var View_Info_Workshop $view */
/** @var array<object> $workshops */
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

$list	= '<em>Momentan werden keine Workshops angeboten.</em>';
if( $workshops ){
	$list	= [];
	foreach( $workshops as $workshop ){
		$list[]	= HtmlTag::create( 'div', array(
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', '&nbsp;', [
					'class'	=> 'workshop-item-image',
				], [
					'url'		=> $pathImages.$workshop->image,
					'alignH'	=> $imageAlignH[$workshop->imageAlignH],
					'alignV'	=> $imageAlignV[$workshop->imageAlignV],
				] ),
			), ['class' => 'workshop-item-image-container'] ),
			HtmlTag::create( 'div', array(
				HtmlTag::create( 'div', array(
					HtmlTag::create( 'div', $workshop->title, ['class' => 'workshop-item-title'] ),
					HtmlTag::create( 'div', $workshop->abstract, ['class' => 'workshop-item-abstract'] ),
				) ),
			), ['class' => 'workshop-item-facts-container'] ),
		), [
			'class'	=> 'workshop-item',
		], [
			'url'		=> './info/workshop/view/'.$workshop->workshopId,
		] );
	}
	$list	= join( HtmlTag::create( 'hr' ), $list );
	$list	= HtmlTag::create( 'div', $list, ['class' => 'workshop-list'] );
}
$panel	= $list;

extract( $view->populateTexts( ['top', 'bottom'], 'html/info/workshop/index/' ) );

return $textTop.$panel.$textBottom;
