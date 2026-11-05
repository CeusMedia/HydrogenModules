<?php
use CeusMedia\Common\UI\HTML\Elements as HtmlElements;
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var object $person */
/** @var object[] $unpayedBillShares */

$w		= (object) $env->getLanguage()->getSection( 'work/billing', 'person-unbooked' );

$list	= HtmlTag::create( 'div', HtmlTag::create( 'em', $w->noEntries, ['class' => 'muted'] ), ['class' => 'alert alert-info'] );
if( $unpayedBillShares ){
	$list	= [];
	foreach( $unpayedBillShares as $unpayedBillShare ){
		$link		= HtmlTag::create( 'a', $unpayedBillShare->bill->number, [
			'href'	=> './work/billing/bill/edit/'.$unpayedBillShare->bill->billId
		] );
		$billTitle	= $unpayedBillShare->bill->title;
		$amount		= number_format( $unpayedBillShare->amount, 2, ',', '.' ).'&nbsp;&euro;';
		$list[]	= HtmlTag::create( 'tr', [
			HtmlTag::create( 'td', $link ),
			HtmlTag::create( 'td', $billTitle ),
			HtmlTag::create( 'td', $amount ),
		] );
	}
	$colgroup	= HtmlElements::ColumnGroup( ['60', '', '80'] );
	$thead		= HtmlTag::create( 'thead', HtmlElements::TableHeads( [$w->headNumber, $w->headBill, $w->headAmount] ) );
	$tbody		= HtmlTag::create( 'tbody', $list );
	$list		= HtmlTag::create( 'table', $colgroup.$thead.$tbody, ['class' => 'table table-fixed'] );
}

$tabs		= View_Work_Billing_Person::renderTabs( $env, $person->personId, 5 );
$heading	= '<h2 class="autocut"><span class="muted">'.$w->headingPrefix.'</span> '.$person->firstname.' '.$person->surname.'</h2>';

return $heading.$tabs.'
<div class="row-fluid">
	<div class="span8">
		<div class="content-panel">
			<h3>'.$w->heading.'</h3>
			<div class="content-panel-inner">
				'.$list.'
			</div>
		</div>
	</div>
</div>';
