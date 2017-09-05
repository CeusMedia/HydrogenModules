<?php
//$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list-alt' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setHeading( 'Auszahlungen' );
$helper->setTransactions( $payouts );
$helper->setMode( View_Work_Billing_Helper_Transactions::MODE_PERSON );
$helper->setFilterUrl( './work/billing/person/payout/filter/'.$person->personId );
$helper->setFilterPrefix( $filterSessionPrefix );
$panelTransactions	= $helper->render();

$buttonSave	= UI_HTML_Tag::create( 'button', $iconSave.' buchen', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary'
) );
if( $person->balance <= 0 ){
	$buttonSave	= UI_HTML_Tag::create( 'button', $iconSave.' buchen', array(
		'type'	=> 'button',
		'disabled'	=> 'disabled',
		'class'	=> 'btn btn-primary'
	) );
}

$amount	= $person->balance > 0 ? floor( $person->balance * 100 ) / 100 : 0;

$filter	= new View_Work_Billing_Helper_Filter( $this->env );
$filter->setFilters( array( 'year', 'month' ) );
$filter->setSessionPrefix( $filterSessionPrefix );
$filter->setUrl( './work/billing/person/payout/filter/'.$person->personId );

$tabs	= View_Work_Billing_Person::renderTabs( $env, $person->personId, 4 );

return '<h2 class="autocut"><span class="muted">Person</span> '.$person->firstname.' '.$person->surname.'</h2>
'.$tabs.'
<div class="row-fluid">
	<div class="span8">
		'.$panelTransactions.'
	</div>
	<div class="span4">
		<div class="content-panel">
			<h3>Auszahlung buchen</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/person/payout/add/'.$personId.'" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">Titel</label>
							<input type="text" name="title" id="input_title" class="span12" required="required"/>
						</div>
					</div>
					<div class="row-fluid">
						<div class="span6">
							<label for="input_dateBooked">Datum</label>
							<input type="date" name="dateBooked" id="input_dateBooked" class="span12" required="required" value="'.date( 'Y-m-d' ).'"/>
						</div>
						<div class="span6">
							<label for="input_amount">Betrag</label>
							<input type="number" step="0.01" min="0.01" max="'.$amount.'" name="amount" id="input_amount" class="span10 input-number" required="required" placeholder="0,00" value="'.$amount.'"/><span class="suffix">&euro;</span>
						</div>
					</div>
					<div class="buttonbar">
						'.$buttonSave.'
					</div>
				</form>
			</div>
		</div>
	</div>
</div>';
?>
