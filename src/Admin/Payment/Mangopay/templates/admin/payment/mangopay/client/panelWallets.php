<?php

use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment\Web;
use CeusMedia\HydrogenFramework\View;

/** @var Web $env */
/** @var View $view */
/** @var array<array<string,string>> $words */
/** @var object[] $clientWallets */

$iconWallet	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-briefcase'] );
$iconBank	= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-bank'] );

$helperMoney		= new View_Helper_Mangopay_Entity_Money( $env );
$helperMoney->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
$helperMoney->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );

$list	= [];
foreach( $clientWallets as $wallet ){
	$wallet->Description	= $wallet->Id;
	$balance	= HtmlTag::create( 'strong', $helperMoney->set( $wallet->Balance ) );
	$list[]	= HtmlTag::create( 'tr', [
		HtmlTag::create( 'td', $wallet->Id ),
		HtmlTag::create( 'td', $balance, ['style' => 'text-align: right'] ),
	] );
}
$cols	= HtmlElements::ColumnGroup( ['', '120'] );
$tbody	= HtmlTag::create( 'tbody', $list );
$list	= HtmlTag::create( 'table', $cols.$tbody, ['class' => 'table table-fixed'] );

return HtmlTag::create( 'div', [
	HtmlTag::create( 'h3', $iconWallet.'&nbsp;Client Wallets' ),
	HtmlTag::create( 'div', [
		$list
	], ['class' => 'content-panel-inner'] ),
], ['class' => 'content-panel'] );
