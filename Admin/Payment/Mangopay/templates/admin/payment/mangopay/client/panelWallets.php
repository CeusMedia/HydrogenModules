<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$iconWallet	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-briefcase' ) );
$iconBank	= HtmlTag::create( 'i', '', array( 'class' => 'fa fa-fw fa-bank' ) );

$helperMoney		= new View_Helper_Mangopay_Entity_Money( $this->env );
$helperMoney->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
$helperMoney->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );

$list	= [];
foreach( $clientWallets as $wallet ){
	$wallet->Description	= $wallet->Id;
	$balance	= HtmlTag::create( 'strong', $helperMoney->set( $wallet->Balance ) );
	$list[]	= HtmlTag::create( 'tr', array(
		HtmlTag::create( 'td', $wallet->Id ),
		HtmlTag::create( 'td', $balance, array( 'style' => 'text-align: right' ) ),
	) );
}
$cols	= UI_HTML_Elements::ColumnGroup( array( '', '120' ) );
$tbody	= HtmlTag::create( 'tbody', $list );
$list	= HtmlTag::create( 'table', $cols.$tbody, array( 'class' => 'table table-fixed' ) );

return HtmlTag::create( 'div', array(
	HtmlTag::create( 'h3', $iconWallet.'&nbsp;Client Wallets' ),
	HtmlTag::create( 'div', array(
		$list
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
