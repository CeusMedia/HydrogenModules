<?php
//$iconCancel		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-list-alt' ) );
$iconSave		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-check' ) );
$iconAdd		= UI_HTML_Tag::create( 'i', '', array( 'class' => 'fa fa-fw fa-plus' ) );

$buttonAdd	= UI_HTML_Tag::create( 'a', $iconAdd.' neue Auszahlung', array(
	'href'			=> '#modal-add-payout',
	'class'			=> 'btn btn-success',
	'role'			=> 'button',
	'data-toggle'	=> 'modal',
) );
$buttonSave	= UI_HTML_Tag::create( 'button', $iconSave.' buchen', array(
	'type'		=> 'submit',
	'name'		=> 'save',
	'class'		=> 'btn btn-primary',
	'disabled'	=> $corporation->balance < 1 ? 'disabled' : NULL,
) );

$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setHeading( 'Auszahlungen' );
$helper->setTransactions( $payouts );
$helper->setFilterUrl( './work/billing/corporation/payout/filter/'.$corporation->corporationId );
$helper->setFilterPrefix( $filterSessionPrefix );
$helper->setButtons( $buttonAdd );
$panelTransactions	= $helper->render();

$amount	= $corporation->balance > 0 ? floor( $corporation->balance * 100 ) / 100 : 0;

$tabs	= View_Work_Billing_Corporation::renderTabs( $env, $corporation->corporationId, 4 );

return '<h2 class="autocut"><span class="muted">Unternehmen</span> '.$corporation->title.'</h2>
'.$tabs.'
<div class="row-fluid">
	<div class="span12">
		'.$panelTransactions.'
	</div>
</div>

<div id="modal-add-payout" class="modal hide not-fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form action="./work/billing/corporation/payout/add/'.$corporationId.'" method="post">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel">Auszahlung buchen</h3>
		</div>
		<div class="modal-body">
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
		</div>
		<div class="modal-footer">
			'.$buttonSave.'
		</div>
	</form>
</div>';
?>
