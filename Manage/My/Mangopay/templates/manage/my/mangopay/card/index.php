<?php

$helperCardLogo		= new View_Helper_Mangopay_Entity_CardProviderLogo( $env );
$helperCardLogo->setSize( View_Helper_Mangopay_Entity_CardProviderLogo::SIZE_LARGE );
$helperCardNumber	= new View_Helper_Mangopay_Entity_CardNumber( $env );
$list	= array();
foreach( $cards as $card ){
//print_m( $card );die;
	$logo	= $helperCardLogo->setProvider( $card->CardProvider )->render();
	$number	= $helperCardNumber->set( $card->Alias )->render();
	$title	= UI_HTML_Tag::create( 'div', $card->Tag, array( 'class' => 'card-title' ) );
	$item	= $logo.$number.$title;
	$list[]	= UI_HTML_Tag::create( 'div', $item, array(
		'class'		=> 'card-list-item-large',
		'onclick'	=> 'document.location.href="./manage/my/mangopay/card/view/'.$card->Id.'";',
	) );
}
$logo	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus fa-4x' ) );
$number	= UI_HTML_Tag::create( 'div', 'Karte hinzufügen' );
$item	= $logo.$number;
$list[]	= UI_HTML_Tag::create( 'div', $item, array(
	'class'		=> 'card-list-item-large',
	'onclick'	=> 'document.location.href="./manage/my/mangopay/card/registration";',
) );
$list	= UI_HTML_Tag::create( 'div', $list );
return '<h2>Kreditkarten</h2>'.$list;

$panel	= new View_Helper_Panel_Mangopay_Cards( $env );
return $panel->setData( $cards )->render();
