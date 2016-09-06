<?php

$rows		= array();
foreach( $wallets as $wallet ){
	$link	= UI_HTML_Tag::create( 'a', $wallet->Id, array( 'href' => './manage/my/mangopay/wallet/view/'.$wallet->Id ) );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create(' td', $link, array( 'class' => 'cell-wallet-id' ) ),
		UI_HTML_Tag::create(' td', $wallet->Description, array( 'class' => 'cell-wallet-title' ) ),
		UI_HTML_Tag::create(' td', $this->formatMoney( $wallet->Balance ), array( 'class' => 'cell-wallet-balance' ) ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( "120", "", "120" );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'ID', 'Wallet Name', 'Betrag' ) ) );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
$panelWallets	= '
<div class="content-panel">
	<h3>Wallets</h3>
	<div class="content-panel-inner">
		'.$table.'
	</div>
</div>';

return $panelWallets;
