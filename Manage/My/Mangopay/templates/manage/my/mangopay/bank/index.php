<?php

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
