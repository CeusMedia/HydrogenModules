<?php
//$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list-alt' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );

$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setHeading( 'Ausgaben' );
$helper->setTransactions( $expenses );
$helper->setFilterUrl( './work/billing/corporation/expense/filter/'.$corporation->corporationId );
$helper->setFilterPrefix( $filterSessionPrefix );
$panelTransactions	= $helper->render();

$buttonSave	= UI_HTML_Tag::create( 'button', $iconSave.' buchen', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary'
) );

$tabs	= View_Work_Billing_Corporation::renderTabs( $env, $corporation->corporationId, 2 );

return '<h2 class="autocut"><span class="muted">Unternehmen</span> '.$corporation->title.'</h2>
'.$tabs.'
<div class="row-fluid">
	<div class="span8">
		'.$panelTransactions.'
	</div>
	<div class="span4">
		<div class="content-panel">
			<h3>Ausgabe buchen</h3>
			<div class="content-panel-inner">
				<form action="./work/billing/corporation/expense/add/'.$corporation->corporationId.'" method="post">
					<div class="row-fluid">
						<div class="span12">
							<label for="input_title">Bezeichnung</label>
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
							<input type="number" step="0.01" min="0.01" name="amount" id="input_amount" class="span10 input-number" required="required"/><span class="suffix">&euro;</span>
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
