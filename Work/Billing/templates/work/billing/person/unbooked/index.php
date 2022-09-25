<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

$list	= HtmlTag::create( 'div', HtmlTag::create( 'em', 'Keine gefunden.', array( 'class' => 'muted' ) ), array( 'class' => 'alert alert-info' ) );
if( $unpayedBillShares ){
	$list	= [];
	foreach( $unpayedBillShares as $unpayedBillShare ){
		$link		= HtmlTag::create( 'a', $unpayedBillShare->bill->number, array(
			'href'	=> './work/billing/bill/edit/'.$unpayedBillShare->bill->billId
		) );
		$billTitle	= $unpayedBillShare->bill->title;
		$amount		= number_format( $unpayedBillShare->amount, 2, ',', '.' ).'&nbsp;&euro;';
		$list[]	= HtmlTag::create( 'tr', array(
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $billTitle ),
			HtmlTag::create( 'td', $amount ),
		) );
	}
	$colgroup	= HtmlElements::ColumnGroup( array( '60', '', '80' ) );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( array( 'RNr', 'Rechnung', 'Betrag' ) ) );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, array( 'class' => 'table table-fixed' ) );
}

$tabs		= View_Work_Billing_Person::renderTabs( $env, $person->personId, 5 );
$heading	= '<h2 class="autocut"><span class="muted">Person</span> '.$person->firstname.' '.$person->surname.'</h2>';

return $heading.$tabs.'
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
