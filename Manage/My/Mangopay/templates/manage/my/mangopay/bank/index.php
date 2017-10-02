<?php

$logo	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-bank fa-4x' ) );

$list	= array();
foreach( $bankAccounts as $bankAccount ){
//print_m( $bankAccount );die;
	$number	= UI_HTML_Tag::create( 'tt', $bankAccount->Details->BIC );
	$title	= UI_HTML_Tag::create( 'div', $bankAccount->OwnerName, array( 'class' => 'bankaccount-title' ) );
	$item	= $logo.$title.$number;
	$list[]	= UI_HTML_Tag::create( 'div', $item, array(
		'class'		=> 'bankaccount-list-item',
		'onclick'	=> 'document.location.href="./manage/my/mangopay/bank/view/'.$bankAccount->Id.'";',
	) );
}
$logo	= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus fa-4x' ) );
$number	= UI_HTML_Tag::create( 'div', 'Konto hinzufügen' );
$item	= $logo.$number;
$list[]	= UI_HTML_Tag::create( 'div', $item, array(
	'class'		=> 'bankaccount-list-item',
	'onclick'	=> 'document.location.href="./manage/my/mangopay/bank/add";',
) );
$list	= UI_HTML_Tag::create( 'div', $list );
return '<h2>Bankkonten</h2>'.$list.'
<style>
div.bankaccount-list-item {
	float: left;
	width: 200px;
	height: 170px;
	padding: 1em;
	margin-right: 1em;
	margin-bottom: 1em;
	border: 1px solid rgba(191, 191, 191, 0.5);
	border-radius: 5px;
	background-color: rgba(191, 191, 191, 0.15);
	cursor: pointer;
	text-align: center;
	}
div.bankaccount-list-item tt {
	margin-top: 6px;
	font-size: 1em;
	display: block;
	}
div.bankaccount-list-item div.bankaccount-title {
	font-size: 1.1em;
	}
div.bankaccount-list-item i.fa {
	margin-top: 0.75em;
	margin-bottom: 0.25em;
	}
</style>';

$panel	= new View_Helper_Panel_Mangopay_Cards( $env );
return $panel->setData( $cards )->render();




$rows		= array();
foreach( $bankAccounts as $item ){
//print_m( $item );die;
	$link	= UI_HTML_Tag::create( 'a', $item->Id, array( 'href' => './manage/my/mangopay/bank/view/'.$item->Id ) );

	$iban	= UI_HTML_Tag::create( 'kbd', $item->Details->IBAN, array( 'class' => '' ) );
	$bic	= UI_HTML_Tag::create( 'tt', $item->Details->BIC, array( 'class' => 'muted' ) );
	$status	= UI_HTML_Tag::create( 'span', $item->Active ? 'aktiv' : 'gesperrt', array( 'class' => 'label label-'.( $item->Active ? 'success' : 'important' ) ) );
	$data	= UI_HTML_Tag::create( 'small', 'Date: '.date( 'Y-m-d H:i:s', $item->CreationDate ), array( 'class' => 'small' ) );

	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create( 'td', $link, array( 'class' => 'cell-bank-id' ) ),
		UI_HTML_Tag::create( 'td', $item->OwnerName.'<br/>'.$data, array( 'class-bank-owner' ) ),
		UI_HTML_Tag::create( 'td', $iban.'<br/>'.$bic, array( 'class' => 'cell-bank-numbers' ) ),
		UI_HTML_Tag::create( 'td', $status, array( 'class' => 'cell-bank-status' ) ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( '100', '', '240', '100' );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'ID', 'Bank Account Name', 'Bankdaten', 'Zustand' ) ) );
$tbody		= UI_HTML_Tag::create( 'tbody', $rows );
$table		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-striped' ) );
$panelBankAccounts	= '
<div class="content-panel">
	<h3>Bank Accounts</h3>
	<div class="content-panel-inner">
		'.$table.'
		<div class="buttonbar">
			<a href="./manage/my/mangopay/bank/add" class="btn btn-success"><b class="fa fa-plus"></b> add</a>
		</div>
	</div>
</div>';

return $panelBankAccounts;
