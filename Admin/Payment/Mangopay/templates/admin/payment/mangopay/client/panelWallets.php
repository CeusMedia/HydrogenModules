<?php

$iconWallet	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-briefcase' ) );
$iconBank	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-bank' ) );

$helperMoney		= new View_Helper_Mangopay_Entity_Money( $this->env );
$helperMoney->setFormat( View_Helper_Mangopay_Entity_Money::FORMAT_AMOUNT_SPACE_CURRENCY );
$helperMoney->setNumberFormat( View_Helper_Mangopay_Entity_Money::NUMBER_FORMAT_COMMA );

$list	= array();
foreach( $clientWallets as $wallet ){
	$wallet->Description	= $wallet->Id;
	$balance	= UI_HTML_Tag::create( 'strong', $helperMoney->set( $wallet->Balance ) );
	$list[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $wallet->Id ),
		UI_HTML_Tag::create( 'td', $balance, array( 'style' => 'text-align: right' ) ),
	) );
}
$cols	= UI_HTML_Elements::ColumnGroup( array( '', '120' ) );
$tbody	= UI_HTML_Tag::create( 'tbody', $list );
$list	= UI_HTML_Tag::create( 'table', $cols.$tbody, array( 'class' => 'table table-fixed' ) );

return UI_HTML_Tag::create( 'div', array(
	UI_HTML_Tag::create( 'h3', $iconWallet.'&nbsp;Client Wallets' ),
	UI_HTML_Tag::create( 'div', array(
		$list
	), array( 'class' => 'content-panel-inner' ) ),
), array( 'class' => 'content-panel' ) );
