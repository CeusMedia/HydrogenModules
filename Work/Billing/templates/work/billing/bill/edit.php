<?php

$buttonCancel	= UI_HTML_Tag::create( 'a', 'zur Liste', array(
	'href'	=> './work/billing/bill',
	'class'	=> 'btn btn',
) );

if( $bill->status == 1 ){
	$panelFacts	= '
<div class="content-panel">
	<h3>Rechnung <small class="muted">(gebucht)</small></h3>
	<div class="content-panel-inner">
		<div class="row-fluid">
			<div class="span2">
				<label for="input_number">Nummer</label>
				<input type="text" name="number" id="input_number" class="span12" disabled="disabled" value="'.$bill->number.'"/>
			</div>
			<div class="span7">
				<label for="input_title">Titel</label>
				<input type="text" name="title" id="input_title" class="span12" disabled="disabled" value="'.htmlentities( $bill->title, ENT_QUOTES, 'UTF-8' ).'"/>
			</div>
			<div class="span3">
				<label for="input_dateBooked">gebucht am</label>
				<input type="date" name="dateBooked" id="input_dateBooked" class="span12" disabled="disabled" required="required" value="'.$bill->dateBooked.'"/>
			</div>
		</div>
		<div class="row-fluid">
			<div class="span3">
				<label for="input_taxRate">Steuersatz</label>
				<input type="number" step="0.01" min="0" name="taxRate" id="input_taxRate" class="span10 input-number" disabled="disabled" value="'.$bill->taxRate.'"/><span class="suffix">%</span>
			</div>
			<div class="span3">
				<label for="input_amountNetto">Nettobetrag</label>
				<input type="number" step="0.01" min="0" name="amountNetto" id="input_amountNetto" class="span10 input-number" disabled="disabled" value="'.number_format( $bill->amountNetto, 2 ).'"/><span class="suffix">&euro;</span>
			</div>
			<div class="span3">
				<label for="input_amountTaxed">Bruttobetrag</label>
				<input type="number" step="0.01" min="0" name="amountTaxed" id="input_amountTaxed" class="span10 input-number" disabled="disabled" value="'.number_format( $bill->amountTaxed, 2 ).'"/><span class="suffix">&euro;</span>
			</div>
			<div class="span3">
				<label>noch zu verteilen</label>
				<input type="number" step="0.01" min="0" disabled="disabled" class="span10 input-number" disabled="disabled" value="'.number_format( $bill->amountNetto - $bill->amountAssigned, 2 ).'"/><span class="suffix">&euro;</span>
			</div>
		</div>
		<div class="buttonbar">
			'.$buttonCancel.'
		</div>
	</div>
</div>';
}
else{

	$buttonSave		= UI_HTML_Tag::create( 'button', 'speichern aund aufteilen', array(
		'type'	=> 'submit',
		'name'	=> 'save',
		'class'	=> 'btn btn-primary',
	) );

	$panelFacts	= '
<div class="content-panel">
	<h3>Rechnung <small class="muted">(in Arbeit)</small></h3>
	<div class="content-panel-inner">
		<form action="./work/billing/bill/edit/'.$bill->billId.'" method="post" class="form-changes-auto">
			<div class="row-fluid">
				<div class="span2">
					<label for="input_number">Nummer</label>
					<input type="text" name="number" id="input_number" class="span12" required="required" value="'.$bill->number.'"/>
				</div>
				<div class="span7">
					<label for="input_title">Titel</label>
					<input type="text" name="title" id="input_title" class="span12" value="'.htmlentities( $bill->title, ENT_QUOTES, 'UTF-8' ).'"/>
				</div>
				<div class="span3">
					<label for="input_dateBooked">gebucht am</label>
					<input type="date" name="dateBooked" id="input_dateBooked" class="span12" required="required" value="'.$bill->dateBooked.'"/>
				</div>
			</div>
			<div class="row-fluid">
				<div class="span3">
					<label for="input_taxRate">Steuersatz</label>
					<input type="number" step="0.01" min="0" name="taxRate" id="input_taxRate" class="span10 input-number" required="required" value="'.number_format( $bill->taxRate, 2 ).'" oninput="WorkBilling.Bill.updateAmounts(this)"/><span class="suffix">%</span>
				</div>
				<div class="span3">
					<label for="input_amountNetto">Nettobetrag</label>
					<input type="number" step="0.01" min="0" name="amountNetto" id="input_amountNetto" class="span10 input-number"  value="'.number_format( $bill->amountNetto, 2 ).'" oninput="WorkBilling.Bill.updateAmounts(this)"/><span class="suffix">&euro;</span>
				</div>
				<div class="span3">
					<label for="input_amountTaxed">Bruttobetrag</label>
					<input type="number" step="0.01" min="0" name="amountTaxed" id="input_amountTaxed" class="span10 input-number" value="'.number_format( $bill->amountTaxed, 2 ).'" oninput="WorkBilling.Bill.updateAmounts(this)"/><span class="suffix">&euro;</span>
				</div>
				<div class="span3">
					<label>noch zu verteilen</label>
					<input type="number" step="0.01" min="0" disabled="disabled" class="span10 input-number" value="'.number_format( $bill->amountNetto - $bill->amountAssigned, 2 ).'"/><span class="suffix">&euro;</span>
				</div>
			</div>
			<div class="row-fluid">
			</div>
			<div class="buttonbar">
				'.$buttonCancel.'
				'.$buttonSave.'
			</div>
		</form>
	</div>
</div>';
}

$tabs	= View_Work_Billing_Bill::renderTabs( $env, $bill->billId, 0 );

return '<h2 class="autocut"><span class="muted">Rechnung</span> '.$bill->number.' - '.$bill->title.'</h2>
'.$tabs.'
<div class="row-fluid">
	<div class="span12">
		'.$panelFacts.'
	</div>
</div>';
?>
