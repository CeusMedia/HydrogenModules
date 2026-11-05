<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;
use CeusMedia\HydrogenFramework\Environment;

/** @var Environment $env */
/** @var object $bill */
/** @var object[] $corporationTransactions */
/** @var object[] $personTransactions */

$w		= (object) $env->getLanguage()->getSection( 'work/billing', 'bill-transaction' );

$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setTransactions( $corporationTransactions );
$helper->setHeading( $w->headingCorporations );
$helper->setMode( View_Work_Billing_Helper_Transactions::MODE_CORPORATION );
$panelCorporationTransactions	= $helper->render();

$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setTransactions( $personTransactions );
$helper->setHeading( $w->headingPersons );
$helper->setMode( View_Work_Billing_Helper_Transactions::MODE_PERSON );
$panelPersonTransactions	= $helper->render();

$tabs	= View_Work_Billing_Bill::renderTabs( $env, $bill->billId, 2 );

return '<h2 class="autocut"><span class="muted">'.$w->headingPrefix.'</span> '.$bill->number.' - '.$bill->title.'</h2>
'.$tabs.'
<div class="row-fluid">
	<div class="span12">
		'.$panelCorporationTransactions.'
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		'.$panelPersonTransactions.'
	</div>
</div>
<div class="row-fluid">
	<div class="span12">
		<div class="content-panel">
			<h3>'.$w->headingNext.'</h3>
			<div class="content-panel-inner">
				<a href="./work/billing/bill/add" class="btn btn-success">'.$iconAdd.' '.$w->buttonAdd.'</a>
			</div>
		</div>
	</div>
</div>';
