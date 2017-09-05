<?php

$list	= UI_HTML_Tag::create( 'div', UI_HTML_Tag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) ), array( 'class' => 'alert alert-info' ) );
if( $unpayedBillShares ){
	$list	= array();
	foreach( $unpayedBillShares as $unpayedBillShare ){
		$link		= UI_HTML_Tag::create( 'a', $unpayedBillShare->bill->number, array(
			'href'	=> './work/billing/bill/edit/'.$unpayedBillShare->bill->billId
		) );
		$billTitle	= $unpayedBillShare->bill->title;
		$amount		= number_format( $unpayedBillShare->amount, 2, ',', '.' ).'&nbsp;&euro;';
		$list[]	= UI_HTML_Tag::create( 'tr', array(
			UI_HTML_Tag::create( 'td', $link ),
			UI_HTML_Tag::create( 'td', $billTitle ),
			UI_HTML_Tag::create( 'td', $amount ),
		) );
	}
	$colgroup	= UI_HTML_Elements::ColumnGroup( array( '60', '', '80' ) );
	$thead		= UI_HTML_Tag::create( 'thead', UI_HTML_Elements::TableHeads( array( 'RNr', 'Rechnung', 'Betrag' ) ) );
	$tbody		= UI_HTML_Tag::create( 'tbody', $list );
	$list		= UI_HTML_Tag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}

$tabs	= View_Work_Billing_Person::renderTabs( $env, $person->personId, 5 );

return '<h2 class="autocut"><span class="muted">Person</span> '.$person->firstname.' '.$person->surname.'</h2>
'.$tabs.'
<div class="row-fluid">
	<div class="span8">
		<div class="content-panel">
			<h3>Ungebuchte Rechnungsanteile</h3>
			<div class="content-panel-inner">
				'.$list.'
			</div>
		</div>
	</div>
</div>';
