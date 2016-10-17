<?php

$list	= array();
foreach( $bankAccounts as $bankAccount ){
	$link	= UI_HTML_Tag::create( 'a', $bankAccount->Id, array( 'href' => './manage/my/mangopay/bank/view/'.$bankAccount->Id ) );
	$list[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $link ),
	) );
}
$tbody	= UI_HTML_Tag::create( 'tbody', $list );
$table	= UI_HTML_Tag::create( 'table', $tbody, array( 'class' => 'table table-striped' ) );
$panelBankAccounts	= '
<div class="content-panel">
	<h3>Bank Accounts</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			<a href="./manage/my/mangopay/bank/add" class="btn btn-success">add</a>
		</div>
	</div>
</div>';


$list	= new View_Helper_Accordion( 'user-transations' );
$list->setSingleOpen( TRUE );
foreach( $transactions as $item ){
	$id			= UI_HTML_Tag::create( 'small', $item->Id.':', array( 'class' => 'muted' ) );
	$title		= $id.'&nbsp;'.$this->formatMoney( $item->DebitedFunds );
	$content	= ltrim( print_m( $item, NULL, NULL, TRUE ), '<br/>' );
	$list->add( 'user-transation-'.$item->Id, $title, $content );
}
$panelTransactions	= '
<div class="content-panel">
	<h3>Transactions</h3>
	<div class="content-panel-inner">
		'.$list->render().'
	</div>
</div>';


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



return '
<div class="row-fluid">
	<div class="span6">
		<div class="content-panel">
			<h3>User </h3>
			<div class="content-panel-inner">
				'.print_m( $user, NULL, NULL, TRUE ).'
			</div>
		</div>
	</div>
	<div class="span6">
		'.$panelWallets.'
		'.$panelBankAccounts.'
		'.$panelTransactions.'
	</div>
</div>';
