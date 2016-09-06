<?php

$rows		= array();
foreach( $bankAccounts as $item ){
	$link	= UI_HTML_Tag::create( 'a', $item->Id, array( 'href' => './manage/my/mangopay/bank/view/'.$item->Id ) );
	$rows[]	= UI_HTML_Tag::create( 'tr', array(
		UI_HTML_Tag::create(' td', $link, array( 'class' => 'cell-wallet-id' ) ),
		UI_HTML_Tag::create(' td', $item->Description, array( 'class' => 'cell-wallet-title' ) ),
		UI_HTML_Tag::create(' td', $this->formatMoney( $item->Balance ), array( 'class' => 'cell-wallet-balance' ) ),
	) );
}
$colgroup	= UI_HTML_Elements::ColumnGroup( "120", "", "120" );
$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'ID', 'Wallet Name', 'Betrag' ) ) );
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
