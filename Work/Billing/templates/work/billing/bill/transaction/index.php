<?php
$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setTransactions( $personTransactions );
$helper->setHeading( 'Transaktionen an Personen' );
$helper->setMode( View_Work_Billing_Helper_Transactions::MODE_PERSON );
$panelPersonTransactions	= $helper->render();

$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setTransactions( $corporationTransactions );
$helper->setHeading( 'Transaktionen an Unternehmen' );
$helper->setMode( View_Work_Billing_Helper_Transactions::MODE_CORPORATION );
$panelCorporationTransactions	= $helper->render();

$tabs	= View_Work_Billing_Bill::renderTabs( $env, $bill->billId, 2 );

return '<h2 class="autocut"><span class="muted">Rechnung</span> '.$bill->number.' - '.$bill->title.'</h2>
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
			<h3>Weiter im Text...</h3>
			<div class="content-panel-inner">
				<a href="./work/billing/bill/add" class="btn btn-success">'.$iconAdd.' neue Rechnung</a>
			</div>
		</div>
	</div>
</div>


';
?>
