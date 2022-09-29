<?php
use CeusMedia\Common\UI\HTML\Tag as HtmlTag;

//$iconCancel		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-list-alt'] );
$iconSave		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-check'] );
$iconAdd		= HtmlTag::create( 'i', '', ['class' => 'fa fa-fw fa-plus'] );

$buttonAdd	= HtmlTag::create( 'a', $iconAdd.' neue Einzahlung', array(
	'href'			=> '#modal-add-payin',
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
$helper->setHeading( 'Einzahlungen' );
$helper->setTransactions( $payins );
$helper->setMode( View_Work_Billing_Helper_Transactions::MODE_PERSON );
$helper->setFilterUrl( './work/billing/person/payin/filter/'.$person->personId );
$helper->setFilterPrefix( $filterSessionPrefix );
$helper->setButtons( $buttonAdd );
$panelTransactions	= $helper->render();

$amount		= $person->balance < 0 ? number_format( -1 * $person->balance, 2, '.', '' ) : '';

$tabs		= View_Work_Billing_Person::renderTabs( $env, $person->personId, 3 );
$heading	= '<h2 class="autocut"><span class="muted">Person</span> '.$person->firstname.' '.$person->surname.'</h2>';

return $heading.$tabs.'
<div class="row-fluid">
	<div class="span12">
		'.$panelTransactions.'
	</div>
</div>

<div id="modal-add-payin" class="modal hide not-fade" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
	<form action="./work/billing/person/payin/add/'.$person->personId.'" method="post">
		<div class="modal-header">
			<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
			<h3 id="myModalLabel">Einzahlung buchen</h3>
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
					<input type="number" step="0.01" min="0.01" name="amount" id="input_amount" class="span10 input-number" required="required" placeholder="0.00" value="'.$amount.'"/><span class="suffix">&euro;</span>
				</div>
			</div>
		</div>
		<div class="modal-footer">
			'.$buttonSave.'
		</div>
	</form>
</div>';
