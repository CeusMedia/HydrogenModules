<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

//$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list-alt'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' neue Auszahlung', array(
	'href'			=> '#modal-add-payout',
	'class'			=> 'btn btn-success',
	'role'			=> 'button',
	'data-toggle'	=> 'modal',
) );
$buttonSave	= HtmlTag::create( 'button', $iconSave.' buchen', array(
	'type'	=> 'submit',
	'name'	=> 'save',
	'class'	=> 'btn btn-primary'
) );

$helper	= new View_Work_Billing_Helper_Transactions( $env );
$helper->setHeading( 'Auszahlungen' );
$helper->setTransactions( $payouts );
$helper->setMode( View_Work_Billing_Helper_Transactions::MODE_PERSON );
$helper->setFilterUrl( './work/billing/person/payout/filter/'.$person->personId );
$helper->setFilterPrefix( $filterSessionPrefix );
$helper->setButtons( $buttonAdd );
$panelTransactions	= $helper->render();
if( 0 && $person->balance <= 0 ){							//  disabled for now
	$buttonSave	= HtmlTag::create( 'button', $iconSave.' buchen', array(
		'type'	=> 'button',
		'disabled'	=> 'disabled',
		'class'	=> 'btn btn-primary'
	) );
}

$amount		= $person->balance > 0 ? floor( $person->balance * 100 ) / 100 : 0;

$tabs		= View_Work_Billing_Person::renderTabs( $env, $person->personId, 4 );
$heading	= '<h2 class="autocut"><span class="muted">Person</span> '.$person->firstname.' '.$person->surname.'</h2>';

return $heading.$tabs.'
<div class="row-fluid">
	<div class="span12">
		'.$panelTransactions.'
	</div>
</div>

<div id="modal-add-payout" class="modal hide not-fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form action="./work/billing/person/payout/add/'.$personId.'" method="post">
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
					<input type="number" step="0.01" min="0.01"  name="amount" id="input_amount" class="span10 input-number" required="required" placeholder="0,00" value="'.$amount.'"/><span class="suffix">&euro;</span>
<!--					<input type="number" step="0.01" min="0.01" max="'.$amount.'" name="amount" id="input_amount" class="span10 input-number" required="required" placeholder="0,00" value="'.$amount.'"/><span class="suffix">&euro;</span>-->
				</div>
			</div>
		</div>
		<div class="modal-footer">
			'.$buttonSave.'
		</div>
	</form>
</div>';
